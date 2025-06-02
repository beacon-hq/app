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

## To-Do

- [ ] Policies
    - [x] CRUD
    - [x] Active/Inactive per Application per Enviroment
    - [ ] Limited Availability 
      - [x] Date Range
      - [x] Context & Scope
    - [ ] Gradual Rollout (sticky)
      - [ ] Percentage
      - [ ] Context & Scope
- [ ] API
  - [x] Prototype
  - [ ] Rate Limiting for Trial accounts
  - [ ] Rate Limiting for Paid accounts (based on tier?)
  - [ ] Disable when subscription not active (with rate limited grace period + notification for failed payments)
- [ ] Feature Flags
  - [ ] Lifetime Configuration
  - [x] Editing UI
  - [x] Table UI
    - [x] Filter by Tag
    - [x] Filter by Type
    - [x] Filter by Environment
    - [x] Filter by Application
    - [x] Filter by Status
  - [ ] Lifecycle Tracking
- [x] TOTP Two-Factor Authentication
- [x] Teams
  - [x] Invitations
  - [x] Roles/Permissions
- [x] Settings
- [x] Dashboard
- [ ] Audit Log
- [ ] Notifications
- [x] Refactor
  - [x] Value Objects
  - [x] Services Pattern
  - [x] Repository Pattern
- [ ] Billing
  - [ ] Stripe Configuration
    - [x] Products
    - [x] Pricing
    - [x] Migrate to Production
  - [ ] Stripe Subscription Integration (Cashier)
    - [x] Create Subscription
      - [x] Free Trial
        - [x] Don't allow multiple free trials (e.g. when Downgrading)
        - [x] Allow resuming free trial if they still have time left after cancelling
      - [x] Fraud Limit
      - [x] Webhooks (dev)
      - [ ] Webhooks (prod)
    - [ ] UI should be locked down until they create a subscription
    - [ ] UI should be locked down if their subscription lapses until they update payment (API has grace period)
  - [ ] Fraud limits
  - [ ] Meter Reporting
  - [ ] Plan Management
    -  [ ] Upgrade/Downgrade (pro-rated)
    -  [ ] Cancellation
  - [ ] Quota Usage Reporting
    - [ ] Warning when reaching end of free quota
    - [ ] Upsell when reaching price of next tier
  - [ ] Current Bill/Invoicing
