#!/bin/sh
set -eu
# Run once after Composer, environment configuration and migrations.
# Existing container replacement is an explicit deployment operation.
docker run -d --name city-php --restart unless-stopped \
 --memory=512m --pids-limit=100 --security-opt no-new-privileges:true \
 --read-only --tmpfs /tmp:rw,noexec,nosuid,size=64m \
 -p 127.0.0.1:9084:9000 \
 -v /var/www/city:/app:ro \
 -v /var/www/city/storage:/app/storage \
 -v /var/www/city/database:/app/database \
 -v /var/www/city/bootstrap/cache:/app/bootstrap/cache \
 -v /var/www/city/deploy/php.ini:/usr/local/etc/php/conf.d/city.ini:ro \
 -v /var/www/city/deploy/pool.conf:/usr/local/etc/php-fpm.d/zz-city.conf:ro \
 --log-opt max-size=10m --log-opt max-file=3 city-php:8.4
