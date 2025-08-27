<p align="center"><img src="https://gist.githubusercontent.com/dshafik/0a864a990eca92f41bdfb12a46814c6b/raw/651884d633e2eacd3664978e5caeb6bf03a5c6f3/beacon.svg" width="400" alt="Beacon Logo"></p>
<h1 style="text-align: center">Feature Management Platform for Laravel</h1>

## About Beacon

Beacon is an open-source feature management platform built specifically for Laravel applications using Pennant. Designed with simplicity and scalability in mind, Beacon centralizes control over feature flags, making it easier than ever to manage rollouts across environments. Beacon empowers teams to ship confidently while staying flexible.

### Features

- Gradual Rollout
- Limited Availability
- Kill Switches
- Lifecycle Management
- User Segmentation (A/B Testing)
- Manage multiple Applications and Environments

## Installation

1. **Clone the Repository**

```bash
git clone git@github.com:beacon-hq/app.git
cd beacon
```

2. **Install Dependencies**

```bash
composer install
npm install
```

3. **Set Up Environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure Database**

Update your `.env` file with your database credentials.

5. **Run Migrations and Seeders**

```bash
php artisan migrate --seed
```

6. **Serve the Application**

```bash
php artisan serve
```
