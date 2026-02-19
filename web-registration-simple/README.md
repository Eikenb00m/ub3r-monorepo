# Simple PHP Account Website

A minimal PHP + HTML account portal with registration, login, downloads, activation, forgot-password, in-session change-password flows, and Discord account linking with verified-role assignment compatible with the current Ub3r password-hash flow.

## Getting started

1. Copy `config.example.php` to `config.php`.
2. Fill in your database credentials.
3. Configure these required activation settings in `config.php`:
   - `app.base_url` (public URL of the registration page)
   - `app.client_jar_url` (direct link to your single game client `.jar`)
   - `app.java_download_url` (Java download page, defaults to java.com)
   - `app.discord_url` (invite link for your Discord community)
   - `brevo.api_key`
   - `brevo.sender_email`
   - `discord.client_id`
   - `discord.client_secret`
   - `discord.redirect_uri`
   - `discord.oauth_scopes` (default `identify guilds.join`)
   - `discord.guild_id`
   - `discord.verified_role_id`
   - `discord.bot_token`
   - `turnstile.site_key`
   - `turnstile.secret_key`
4. Start locally:

```bash
php -S 0.0.0.0:8080 -t web-registration-simple
```

5. Open `http://localhost:8080`.

## Compatible password hash

This demo uses the same flow as the game server:

- `passM = md5(password)`
- `stored = md5(passM + salt)`

## Account activation, login, and password recovery

- Registrations start as `usergroupid = 3` (inactive).
- An activation token is stored in `user_activation_tokens`.
- Brevo sends an activation email with a link (`/?page=activate&token=...`) that is valid for 2 hours.
- Clicking the link changes the account to `usergroupid = 40` (active), which is required for login.
- Expired activation links auto-ban the account (`usergroupid = 8`).
- Registration blocks duplicate usernames and duplicate email addresses.
- Successful login redirects users to `?page=download` with one game client `.jar` download button, one Java download button, and Discord actions.
- Users can link Discord through `?page=discord-link` (OAuth2 `identify guilds.join`) and the site stores links in `user_discord_links`, including Discord username and Discord display name.
- The new `DiscordRoleManager` module assigns/refreshes the verified Discord role using the configured bot token.
- Forgot password stores reset tokens in `user_password_reset_tokens` and emails `?page=reset-password&token=...` links for active accounts.
- Signed-in users can change their password from `?page=change-password` by confirming their current password.

## Security notes

- Use HTTPS in production.
- Cloudflare Turnstile is integrated and validated server-side for each registration.
- Add rate limiting as a second layer.
- Never commit `config.php` to git.


## Discord OAuth troubleshooting

If Discord shows **Invalid OAuth2 redirect_uri** while linking:

- Ensure `discord.redirect_uri` in `config.php` is **exactly** the same as your Discord app Redirect URL (including protocol, host, trailing slash, and query string).
- Add the exact same URL under **Discord Developer Portal → OAuth2 → Redirects**.
- Default scopes are `identify guilds.join`; you can override via `discord.oauth_scopes`.
