# Overview

Beacon is a comprehensive feature flag management platform built specifically for Laravel applications. 
It provides a centralized way to manage feature flags, A/B tests, and gradual rollouts across your applications and environments.

## What is Beacon?

Beacon is a platform that integrates seamlessly with Laravel's [Pennant](https://laravel.com/docs/pennant) package to provide remote feature flag management. 
Instead of hardcoding feature flag logic in your application, Beacon allows you to define and manage feature flags through a web interface and evaluate them remotely.

## Key Features

### 🚀 **Centralized Feature Flag Management**
- Define and manage feature flags in a single location
- Real-time updates without code deployments

### 🎯 **Advanced Targeting**
- Target users based on custom attributes
- Support for complex policy rules and expressions
- Gradual rollouts
- Support for weighted A/B (multi-variant) testing

### 🏢 **Multiple Organizations, Teams, Applications, and Environments**
- Manage feature flags across multiple applications and environments
- Application/Environment-specific configurations (development, staging, production)

### 📊 **Usage Analytics**
- Track feature flag evaluations and usage
- Monitor usage and evaluation results
- Track A/B test segmentation

### ⚡ **Performance Optimized**
- Built-in caching with configurable TTL
- Minimal latency impact on your applications

## How It Works

Beacon works as a driver for Laravel Pennant, extending its capabilities with remote policy evaluation:

1. **Define Feature Flags**: Create feature flags in your Laravel application using Pennant's familiar API
2. **Configure Policies**: Set up targeting rules and policies through Beacon's web interface
3. **Evaluate Remotely**: When your application checks a feature flag, Beacon evaluates the policy and returns the result
4. **Cache Results**: Results are cached locally to minimize API calls and improve performance

## Core Concepts

### Applications
Applications represent your Laravel projects within Beacon.

### Environments
Environments allow you to have different configurations for development, staging, and production deployments for each Application.

### Feature Flags
Feature flags are the toggles that control feature availability. They can be simple boolean flags or support multiple variants for A/B testing.

### Feature Flag Types
Feature flag types provide a way to categorize and organize your feature flags.

### Policies
Policies define the rules that determine when a feature flag should be active. They support complex expressions and can target users based on various attributes.

### Context
Context includes all the information available when evaluating a feature flag, including user data, request information, and custom attributes.

### Teams & Organizations
Teams are the primary organizational unit for feature flags in Beacon. Each team owns and manages their feature flags, with each flag having one instance per team that can be configured across unlimited application-environment combinations.
A user can belong to multiple teams and multiple organizations.

## Integration with Laravel Pennant

Beacon is designed to work seamlessly with Laravel Pennant:

```php
// Define a feature flag, no need to specify a closure for resolving the value
Feature::define('new-dashboard');

// Check if active (Beacon evaluates remotely)
if (Feature::active('new-dashboard')) {
    // Show new dashboard
}
```

The key difference is that instead of defining classes or passing in closures for feature evaluation, Beacon handles the logic remotely through its policy engine.

## Getting Started

Ready to get started with Beacon? Check out our [Get Started](./get-started) guide for a step-by-step walkthrough of setting up Beacon in your Laravel application.

For installation instructions, see the [Installation](./install) guide.
