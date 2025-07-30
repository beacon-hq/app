# Getting Started with Beacon

Welcome to Beacon! This guide will walk you through creating your first feature flag and using it in your Laravel application.


> [!TIP] Tip: Automatic Feature Flag Creation
> Beacon will automatically create Applications, Environments, and Feature Flags for you whenever you use the Pennant driver in your Laravel application. This means you can start using feature flags without manually creating them in the Beacon dashboard.
>
> However, if you want to create a Feature Flag ahead of time, you can do so manually in the Beacon dashboard.

## Prerequisites

Before you begin, make sure you have:

1. [Installed the Beacon Pennant driver](install.md) in your Laravel application
2. Configured your `BEACON_ACCESS_TOKEN` in your `.env` file
3. Access to your Beacon dashboard

## Create Your First Feature Flag

Before you can create a feature flag, you must first create an Application and Environment in Beacon to associate your feature flags with.

### Step 1: Create an Application

1. Log into your Beacon dashboard
2. Click on <kbd>**Applications**</kbd> in the navigation menu
3. Click the <kbd>**New Application**</kbd> button in the top right corner
   - A form will slide out from the right side of the screen
4. Fill in the required fields:
   - <kbd>**Name**</kbd>: A descriptive name for your application (e.g., "My Laravel App"). This should match your applications `APP_NAME` environment variable, and **cannot be changed.**
5. _Recommended_ Fill in the optional fields:
   - <kbd>**Display Name**</kbd>: A user-friendly name for your application (e.g., "My App") that can be changed
   - <kbd>**Color**</kbd>: Choose a color to represent your application in the dashboard
   - <kbd>**Description**</kbd>: A brief description of your application
6. Click the <kbd>**Create**</kbd> button to save your application.

### Step 2: Create an Environment

1. Click on <kbd>**Environments**</kbd> in the navigation menu
2. Click the <kbd>**New Environment**</kbd> button in the top right corner
   - A form will slide out from the right side of the screen
3. Fill in the required fields:
   - <kbd>**Name**</kbd>: A descriptive name for your environment (e.g., `production`, `staging`, `local`). This should match your application's `APP_ENV` environment variable, and **cannot be changed.**
   - <kbd>**Color**</kbd>: Choose a color to represent your environment in the dashboard
4. _Recommended_ Fill in the optional fields:
    - <kbd>**Description**</kbd>: A brief description of your environment
5. Click the <kbd>**Create**</kbd> button to save your environment.

### Step 3: Create a New Feature Flag

1. Log into your Beacon dashboard
2. Click on **Feature Flags** in the navigation menu
3. You'll see a list of your existing feature flags (if any)
4. Click the **New Feature Flag** button in the top right corner
   - A form will slide out from the right side of the screen
5. Fill in the required fields:
   - **Feature Name** Enter a descriptive name for your feature flag, this **cannot be changed**.
   - **Feature Type** Select the type of feature flag from the dropdown
6. _Recommended_ Fill in the optional fields:
   - **Tags**: Add tags to help organize and categorize your feature flags
   - **Description**: Provide a detailed description of what this feature flag controls
7. Click the <kbd>**Create**</kbd> button to save your Feature Flag

After creating your feature flag, you'll be redirected to the Feature Flag list.

## Configure Your Feature Flag

Now you have create a new Feature Flag, you can configure it for different environments. By default, all Feature Flags are disabled.

### Step 4: Set the Feature Flag to Active

1. Click on your newly created feature flag to open its details
2. Navigate to the <kbd>**Edit**</kbd> tab
3. Toggle the <kbd>**Enabled**</kbd> switch to enable the feature flag
   - This will allow the Feature Flag to be used in your application
4. Click the <kbd>**Update**</kbd> button to save your changes

### Step 5: Create an Application/Environment Configuration

1. Click the <kbd>**Configuration**</kbd> tab
   - You will see a list of applications and environments associated with your feature flag. By default, there will be no configurations.
2. Configure the feature flag status for your new Application and Environments:
   - Click the <kbd>Select application…</kbd> dropdown and select your application (e.g., "My Laravel App")
   - Click the <kbd>Select environment…</kbd> dropdown and select your environment (e.g., "production")
   - Set the <kbd>**Enabled**</kbd> toggle to `true` to enable the feature flag for this application and environment
3. Click the <kbd>**Save**</kbd> button to save your configuration

## Use Your Feature Flag in Code

Now that your feature flag is created in Beacon, you need to define and use it in your Laravel application.

### Step 6: Define the Feature Flag in Your Application

In your Laravel application, define the feature flag using Pennant's `Feature::define()` method. You can do this in a service provider or dedicated feature flag file:

```php
use Laravel\Pennant\Feature;

// In a service provider or dedicated file
Feature::define('new-feature-flag');
```

### Step 7: Check the Feature Flag Status

Use the feature flag in your application code:

```php
use Laravel\Pennant\Feature;

// In a controller or service
if (Feature::active('new-feature-flag')) {
    // The Feature Flag is active, execute the new feature logic
} else {
    // The Feature Flag is inactive, execute the old logic
}
```

#### Use in Blade Templates

You can also use feature flags directly in your Blade templates:

```blade
@feature('new-feature-flag')
     <!-- The Feature Flag is active, execute the new feature logic -->
@else
     <!-- The Feature Flag is inactive, execute the old logic -->
@endfeature
```

## Best Practices

### Naming Conventions
- Use descriptive, kebab-case names: `user-dashboard-redesign`
- Include the feature area: `checkout-one-click-purchase`
- Avoid generic names like `feature-1` or `test-flag`

### Organization
- Use tags to group related features: `checkout`, `ui-redesign`, `performance`
- Add meaningful descriptions that explain the business context
- Choose appropriate feature types for better categorization

### Code Organization
- Keep feature flag checks close to the code they control
- Avoid deeply nested feature flag conditions

## Next Steps

Now that you've created your first feature flag:

1. **Explore Advanced Features**: Learn about [user targeting and variants](using-feature-flags.md)
2. **Team Collaboration**: Invite team members and set up proper permissions

## Troubleshooting

**Feature flag not working?**
- Verify your `BEACON_ACCESS_TOKEN` is correct
- Check that the feature flag name matches exactly (case-sensitive)
- Ensure the feature flag is enabled in your current environment

**Need help?**
- Check the [Using Feature Flags](using-feature-flags.md) documentation
- Review your feature flag configuration in the Beacon dashboard
- Verify your Laravel Pennant integration is working correctly

Congratulations! You've successfully created and implemented your first feature flag with Beacon. 🎉


