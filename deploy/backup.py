#!/usr/bin/env python3
"""Create a consistent local SQLite backup and archive business uploads."""
from pathlib import Path
from datetime import datetime, timezone
import os
import sqlite3
import tarfile

os.umask(0o077)
root = Path('/var/www/city')
destination = Path('/var/backups/city-data')
destination.mkdir(parents=True, exist_ok=True)
stamp = datetime.now(timezone.utc).strftime('%Y%m%d-%H%M%S')
db_path = destination / (stamp + '.sqlite')
with sqlite3.connect('file:' + str(root / 'database/city.sqlite') + '?mode=ro', uri=True) as source:
    with sqlite3.connect(db_path) as target:
        source.backup(target)
        if target.execute('PRAGMA integrity_check').fetchone()[0] != 'ok':
            raise RuntimeError('Backup integrity check failed')
with tarfile.open(destination / (stamp + '-photos.tar.gz'), 'w:gz') as archive:
    archive.add(root / 'storage/app/public', arcname='photos')
# Retain the latest 14 complete generations. Only this script's known names are considered.
for old in sorted(destination.glob('????????-??????.sqlite'), reverse=True)[14:]:
    old.unlink()
    old.with_name(old.stem + '-photos.tar.gz').unlink(missing_ok=True)
print('City backup completed:', stamp)
