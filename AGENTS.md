# Project instructions

- One codebase and one category taxonomy for all city domains. Do not create city-specific template or schema forks.
- Database records determine city content. Resolve and scope by the trusted request hostname. Unknown hosts return 404.
- Free launch only: no paid ranking, affiliate tracking, booking integrations or advertising unless Anthony asks for them.
- Use real, source-linked listings. Initial seed data is capped at ten records per category. Do not invent businesses, ratings, prices, hours or business photos.
- Public places are never claimable. Operator claims and public content edits require administrator review.
- Preserve existing data, photos and financial/other website deployments. Never use production DBs for automated tests.
- Dependencies use latest stable Laravel 13 with exact deployed versions in composer.lock; do not ignore platform requirements.
- Keep credentials, database files, logs and private submissions out of git. GitHub is the source repository; the website database is authoritative for live content.
