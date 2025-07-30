> [!TIP]
> Prefer video? Watch the [Beacon installation video](#video-guide).

# Installing the Beacon Client

To use Beacon with Pennant in your application, you need to:

1. Install the Pennant driver
2. Configure your Application

## Install the Pennant Driver

To install the Pennant driver, run the following command in your terminal:

```bash
composer require beacon-hq/pennant-driver
```

## Configure Your Application

Add the `PENNANT_STORE` and `BEACON_ACCESS_TOKEN` environment variables to your `.env` file:

```dotenv
PENNANT_STORE=beacon
BEACON_ACCESS_TOKEN=<token>
```

Create your access token in the [API Token Settings](/settings/api).

## Video Guide

<lite-youtube class="mt-4" videoid="lNuCNkJ9-xE" />
