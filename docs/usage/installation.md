# Installation

## From the WordPress Dashboard (Recommended)

This is the easiest way to install AI Image Renamer — no technical knowledge needed.

1. Log in to your WordPress admin area
2. Go to **Plugins → Add New**
3. Search for **"AI Image Renamer"**
4. Click **"Install Now"**, then **"Activate"**
5. Navigate to **Media → AI Image Renamer**
6. Paste your Groq API key (see [how to get one](#getting-a-groq-api-key) below)
7. Click **"Test Connection"** to make sure everything works
8. Hit **"Save Changes"** — you're done!

## Manual Upload

If you prefer to install the plugin manually:

1. Download the plugin ZIP file from [WordPress.org](https://wordpress.org/plugins/ai-image-renamer/) or [GitHub](https://github.com/thaikolja/wp-ai-image-renamer/releases)
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**
3. Select the ZIP file and click **"Install Now"**
4. Click **"Activate Plugin"**
5. Then follow steps 5–8 from above to configure it

## For Developers

If you manage your site's plugins through the command line:

```bash
cd wp-content/plugins/
git clone https://github.com/thaikolja/wp-ai-image-renamer.git ai-image-renamer
cd ai-image-renamer
composer install --no-dev --optimize-autoloader
```

Then activate the plugin from **Plugins → Installed Plugins** in your WordPress admin.

## Getting a Groq API Key

The plugin uses [Groq](https://groq.com/) to analyze your images. You'll need a free API key to get started:

1. Go to [console.groq.com](https://console.groq.com/keys)
2. Create a free account — it takes less than a minute
3. Click **"Create API Key"**
4. Copy the key — it starts with `gsk_`
5. Paste it into the plugin's settings page

::: tip No credit card needed
Groq's free plan is generous enough for most websites. You won't be asked for payment information.
:::

## Checking That It Works

After you've entered your API key and saved the settings:

1. Go to **Media → AI Image Renamer**
2. Click the **"Test Connection"** button
3. You should see a green success message
4. Now try uploading any image — its filename will be automatically renamed!

If the test fails, double-check that your API key is correct and that your server can make outgoing [HTTP requests](https://developer.wordpress.org/apis/making-http-requests/).
