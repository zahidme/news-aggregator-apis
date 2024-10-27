# News Aggregator API

A RESTful API for aggregating news articles from multiple sources, enabling user-specific preferences and secure API consumption.

---

## Features

1. *User Authentication*: Registration, login, logout, and password reset using Laravel Sanctum.
2. *Article Management*: Fetch articles with pagination, search, and filter by keyword, date, category, and source.
3. *User Preferences*: Set and retrieve preferred news sources, categories, and authors. Personalized news feed based on preferences.
4. *Data Aggregation*: Regular data fetching from selected news APIs with efficient storage and indexing.

---

## Requirements

- PHP 8.1+
- Laravel 11
- Docker & Docker Compose
- MySQL or PostgreSQL
- API keys for selected news sources

---

## Setup Instructions

1. *Clone the Repository*
   ```bash
   git clone https://github.com/zahidme/news-aggregator-apis.git
   cd news-aggregator-api

   
##commands 

docker-compose up --build -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan schedule:work
docker-compose exec app php artisan serve
docker-compose exec app php artisan test
