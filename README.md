# Data-Analytics-Platform

Symfony application for lead management and analytics. The platform combines CRUD views, dashboards, role-based access control, seeded data, and OpenAI-assisted chart analysis for internal business users.

## What It Does

- Displays and filters lead data
- Renders dashboard charts for campaign and source performance
- Restricts analytics features to elevated roles
- Uses OpenAI to generate narrative analysis from chart data
- Includes fixtures for local demo data

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

### 3. Start the app

```bash
symfony server:start
```

or with Docker:

```bash
docker compose up --build
```

## Key Features

- lead dashboard with multiple chart views
- date-range filtering
- role-based access checks
- OpenAI-backed chart interpretation
- fixtures for local setup

## Current Status

This is the strongest business-oriented repository in the portfolio. It already shows real domain thinking, but it still needs:

- installation screenshots or a short demo GIF
- tests around analytics and security flows
- clearer setup instructions for database schema and fixtures
- a production-safe OpenAI integration layer

## Suggested Next Improvements

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```
