<script setup>
// @ts-ignore
import { CirclePlus } from 'lucide-vue-next';
</script>

# Organizations

Beacon supports multiple organizations, allowing you to model even the most complex company layouts.

Each organization can have multiple [Teams](./teams), and a user can belong to multiple Organizations and Teams.

## Overview

![Organizations Overview](../../screenshots/organizations-initial.png)

## Creating Organizations

To create a new Organization, click the <kbd><CirclePlus /> New Organization</kbd> button.

![New Organization Form](../../screenshots/organizations-form-create.png)

**Form Fields:**

- **Name**: Unique organization identifier.
- **Team Name**: The default team name for the organization.
- **Color**: A color to help identify the Team elsewhere in Beacon.
- **Icon**: An icon to help identify the Team elsewhere in Beacon.

After creating a new organization, it will show up in the Organization list:

![Organization Card](../../screenshots/organizations-list.png)

## Editing Organizations

To edit an organization, click the card in the list.

![Edit Organization Form](../../screenshots/organizations-edit.png)

## Deleting Organizations

To delete an organization, click the <kbd>Delete Organization</kbd> button at the bottom of the edit form.

![Delete Organization](../../screenshots/organizations-delete.png)

You will be prompted to confirm the deletion. This action is irreversible and will delete all associated data.

![Delete Organization Confirmation](../../screenshots/organizations-confirm-delete.png)

> [!NOTE]
> Users with access to another organization will still have access, while those that do not will not have access until
> assigned to another organization.


