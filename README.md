# Aether AI

Aether AI is a Laravel-based AI workspace with an 18-page standalone HTML reference, a Blade application, account authentication, MySQL persistence, and server-side OpenAI integration.

## Requirements

- PHP 8.2+
- Composer
- XAMPP (Apache and MySQL/MariaDB)
- A MySQL database named `ai_software`

## Local setup with XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Create an empty database named `ai_software` in phpMyAdmin.
3. Copy `.env.example` to `.env` and set the local database values:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ai_software
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. From the project directory, run:

   ```powershell
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```

5. Serve the Laravel `public` directory with Apache. For local development, Laravel's built-in server is also available:

   ```powershell
   php artisan serve
   ```

   Then visit `http://127.0.0.1:8000`. Do not point Apache's document root at the project root; it must target `ai-software/public`.

The database seeder creates the AI tool catalog only. It does not create a default user or administrator. Register an account, then promote the intended administrator in phpMyAdmin by setting both `role` to `admin` and `is_admin` to `1`.

## OpenAI

Chat and text-generation requests use Laravel's HTTP client to call the OpenAI Responses API from the server. An API key is required; it cannot be supplied by the app and must not be added to JavaScript, chat messages, or source control. Add your own key directly in the ignored local `.env` file:

```dotenv
OPENAI_API_KEY=your_actual_key
OPENAI_MODEL=gpt-4.1-mini
OPENAI_IMAGE_MODEL=gpt-image-1
```

Replace `your_actual_key` locally with the key from your OpenAI account; do not share it in chat. Laravel maps this variable to `services.openai.api_key`, which the server-side `OpenAiService` reads. The key is never sent to the browser. Then reload Laravel's config and restart the local server:

```powershell
php artisan config:clear
php artisan serve
```

Chat, writing, code, website, document, automation, and data-analysis generation use the configured Responses API model. Image generation uses the configured image model. Without a key, the app displays actionable setup steps and does not fabricate a response. Authentication, quota, rate-limit, and network failures are reported without exposing provider details or credentials.

For password-reset delivery, configure `MAIL_MAILER=smtp` and the SMTP host, port, username, password, and sender address in `.env`. Laravel's default local mailer is `log`; reset messages are written to `storage/logs/laravel.log` until SMTP is configured.

## Frontend reference

The unconverted standalone pages are preserved under [`frontend-reference`](./frontend-reference). Open `index.html` directly or serve that directory with a local static server to preview the HTML-only phase. The Laravel app serves Blade views and uses its own public theme assets.

## Tests

Run the PHPUnit feature and unit suites with:

```powershell
php artisan optimize:clear
php artisan test
```

The tests use an in-memory SQLite database and do not alter the XAMPP MySQL database.
