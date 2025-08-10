<script setup>
// @ts-ignore
import { CirclePlus, Send, Trash2 } from "lucide-vue-next";
</script>

# Access Tokens

The Access Tokens section manages API authentication tokens that allow applications and services to securely interact with the Beacon feature flag system.

## Overview

Access tokens provide secure, programmatic access to the Beacon API.

![Access Token List](../../screenshots/access-tokens-initial.png){.light-only}
![Access Token List Dark](../../screenshots/dark/access-tokens-initial.png){.dark-only}

## Creating a Token

To create a new access token, click the <kbd><CirclePlus /> Create Token</kbd> button. This opens a form to define the new token:

![Create Access Token Form](../../screenshots/access-tokens-form-create.png){.light-only}
![Create Access Token Form Dark](../../screenshots/dark/access-tokens-form-create.png){.dark-only}

Once you create the token, it will be displayed in the token list with its details.

![New Access Token](../../screenshots/access-tokens-after-create.png){.light-only}
![New Access Token Dark](../../screenshots/dark/access-tokens-after-create.png){.dark-only}

> [!WARNING]
> This will be your only chance to copy the token value. Make sure to save it securely, as it will not be shown again.

Once you leave the page, you will only be able to view the token name, and last few characters to identify it:

![Token List](../../screenshots/access-tokens-before-delete.png){.light-only}
![Token List](../../screenshots/dark/access-tokens-before-delete.png){.dark-only}

## Deleting a Token

To delete an access token, click the **Delete** <Trash2 /> button next to the token you wish to remove. A confirmation dialog will appear to confirm the deletion.

![Token Delete Confirmation](../../screenshots/access-tokens-delete.png){.light-only}
![Token Delete Confirmation Dark](../../screenshots/dark/access-tokens-delete.png){.dark-only}


Confirm the deletion to remove the token permanently.
