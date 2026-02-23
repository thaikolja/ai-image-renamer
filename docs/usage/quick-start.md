# Quick Start

Get AI Image Renamer up and running in under 2 minutes.

## Step 1: Activate the Plugin

After [installing](/usage/installation) the plugin, make sure it's activated in **Plugins → Installed Plugins**.

## Step 2: Enter Your API Key

1. Go to **Media → AI Image Renamer**
2. Paste your Groq API key (starts with `gsk_`) into the **Groq API Key** field
3. Click **"Test Connection"** to check it works
4. Click **"Save Changes"**

::: tip Don't have a key yet?
Get one for free at [console.groq.com/keys](https://console.groq.com/keys) — no credit card required.
:::

## Step 3: Enable Auto-Rename

Toggle the **"Enable Auto-Rename"** switch to **ON** and save.

## Step 4: Upload an Image

Go to **Media → Add New** and upload any image. The plugin will automatically:

1. Send the image to the AI for analysis
2. Generate a descriptive filename based on the image content
3. Save the file with the new name

**Before:** `IMG_20260215_143052.jpg`
**After:** `sunset-over-tropical-beach-palm-trees.jpg`

## What's Next?

- [**Settings**](/usage/settings) — Choose your AI model, file types, and keyword limits
- [**FAQ**](/support/faq) — Answers to common questions
- [**Filter Hooks**](/api/filter-hooks) *(for developers)* — Customize the plugin's behavior with code
