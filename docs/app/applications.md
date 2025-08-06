<script setup>
// @ts-ignore
import { CirclePlus } from 'lucide-vue-next';
</script>
# Applications

The Applications section allows users to manage and configure your Laravel applications.

## Overview

![Applications Overview](../screenshots/applications-initial.png){.light-only}
![Applications Overview](../screenshots/dark/applications-initial.png){.dark-only}

## Creating Applications

To create a new Application, click the <kbd><CirclePlus /> New Application</kbd> button.

![New Application Form](../screenshots/applications-form-create.png){.light-only}
![New Application Form](../screenshots/dark/applications-form-create.png){.dark-only}

**Form Fields:**
- **Name**: Unique application identifier, typically the same as your `APP_NAME`.
- **Display Name**: A friendly display name that can be changed
- **Description**: Description of the application
- **Color**: A color to help identify the Application elsewhere in Beacon

After creating a new application, it will show up in the Application list:

![Application Card](../screenshots/applications-app-card.png){.light-only}
![Application Card](../screenshots/dark/applications-app-card.png){.dark-only}

The application card will display any environments for which feature flag configurations exist.

## Editing Applications

To edit an application, click the card in the list.

![Edit Application Form](../screenshots/applications-edit.png){.light-only}
![Edit Application Form](../screenshots/dark/applications-edit.png){.dark-only}


> [!NOTE]
> The Application Name cannot be changed, and must match your applications 
> `pennant.stores.beacon.app_name` configuration setting.
