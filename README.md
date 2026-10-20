# Telegram Alert Relay for Render

A small PHP service that sends messages directly through the Telegram Bot API.

## Security
Never put your bot token or relay secret in source code or commit them to GitHub. Configure both as Render environment variables. Anyone with your bot token can control your bot.

## Deploy
1. Create a new **public GitHub repository** (for example `telegram-alert-relay`).
2. Upload `Dockerfile`, `router.php`, and `README.md` to the repository root.
3. In Render choose **New → Web Service → Public Git Repository**, paste the public repo URL, and deploy using the detected Dockerfile.
4. In Render → your service → **Environment**, add `TELEGRAM_BOT_TOKEN` (the bot token from BotFather) and `RELAY_SECRET` (a long random secret you create).
5. Wait for redeploy, then open `https://YOUR-SERVICE.onrender.com/health`. Expected: `{"ok":true,"service":"telegram-alert-relay"}`.

## Endpoint
Send `POST https://YOUR-SERVICE.onrender.com/notify` with headers `Content-Type: application/json` and `Authorization: Bearer YOUR_RELAY_SECRET`.

JSON body: `{"chat_id":"YOUR_NUMERIC_CHAT_ID","text":"Telegram relay test"}`

Your website's `telegram-config.php` must point to `/notify` and use the exact same relay secret. This service accepts only numeric chat IDs and returns success only when Telegram confirms delivery.

## Free tier
Render free services may sleep while idle and may have availability or usage limits. Check Render's current plan terms.
