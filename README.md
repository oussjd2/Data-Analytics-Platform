# Data Analytics Platform

Symfony application for lead management and analytics. The platform combines CRUD views, dashboards, role-based access control, seeded data, and OpenAI-assisted chart analysis for internal business users.

## Features

- Lead listing, filtering, creation, and update flows
- Dashboard charts for campaign and source performance
- Role-based access checks for analytics features
- OpenAI-backed narrative analysis from chart data
- Fixtures for local demo data

## Tech Stack

- PHP 8.1
- Symfony 6
- Doctrine ORM
- Twig
- Symfony UX Chart.js
- Docker Compose

## Project Structure

```text
src/
  Controller/
  Entity/
  Repository/
  Security/
templates/
config/
assets/
public/
```

## Getting Started

### 1. Install dependencies

```bash
composer install
```

### 2. Configure environment variables

Create `.env.local` and set at least:

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/app"
OPENAI_API_KEY="replace-with-your-openai-api-key"
APP_ENV=dev
```

### 3. Prepare the database

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 4. Start the app

```bash
symfony server:start
```

or with Docker:

```bash
docker compose up --build
```

## Useful Commands

```bash
php bin/console cache:clear
php bin/phpunit
```

## AI Configuration

Chart analysis uses `OPENAI_API_KEY` from the local environment. Do not commit `.env.local` or API credentials.
