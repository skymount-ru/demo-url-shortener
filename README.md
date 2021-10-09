# URL Shortener

## Deploy and start with Docker
Steps:
- Define DB credentials and DB Name in the .env and docker-compose.yml#mysql files
- Migrate with ``./artisan migrate``
- Start services with ``docker-compose up -d``
- Try web-service at http://localhost

## Deploy on Shared hosting
Steps:
- Define DB credentials and DB Name in .env and docker-compose.yml#mysql
- Migrate with ``./artisan migrate``
- Link public folder (public_html) to ./public with ``ln -s public_html public``
- Define ``APP_URL=<YourURL>`` in the .env file
- Try web-service
