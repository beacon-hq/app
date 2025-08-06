# Environments

The Environments section manages different deployment environments (development, staging, production, etc.) where feature flags are evaluated and used.

## Overview

![Environments Overview](../screenshots/environments-initial.png){.light-only}
![Environments Overview](../screenshots/dark/environments-initial.png){.dark-only}

## Creating Environments

![New Environment Form](../screenshots/environments-form-create.png){.light-only}
![New Environment Form](../screenshots/dark/environments-form-create.png){.dark-only}

**Form Fields:**
- **Name**: Unique environment identifier, typically the same as your `APP_ENV`.
- **Color**: A color to help identify the Environment elsewhere in Beacon
- **Description**: Description of the environment

After creating a new environment, it will show up in the Environment list:

![Environment Card](../screenshots/environments-environment-card.png){.light-only}
![Environment Card](../screenshots/dark/environments-environment-card.png){.dark-only}

## Editing Environments

To edit an environment, click the card in the list.

![Edit Environment Form](../screenshots/environments-edit.png){.light-only}
![Edit Environment Form](../screenshots/dark/environments-edit.png){.dark-only}

> [!NOTE]
> The Environment Name cannot be changed, and must match your applications
> `pennant.stores.beacon.environment_name` configuration setting.


