<p align="center">
  <img src="public/assets/anamorphic-logo.svg" alt="Anamorphic" width="360">
</p>

<p align="center">
  A minimalist PHP framework for building things fast, without the ceremony.
</p>

<p align="center">
  <a href="https://github.com/abdurzuhri/anamorphic"><img src="https://img.shields.io/badge/version-1.0-black" alt="version"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-black" alt="license"></a>
  <img src="https://img.shields.io/badge/php-%3E%3D8.2-black" alt="php version">
</p>

---

**Anamorphic** is a small, dependency-light PHP framework built for developers who want to understand every line of the framework running their app. No magic, no hidden reflection tricks beyond what's needed for simple dependency injection, and no bloated abstractions.

It ships with its own tiny router, a PDO-based query builder + ActiveRecord-style models, a plain-PHP view engine, and a custom command-line tool called **`ana`** — the Anamorphic equivalent of Laravel's `artisan`.

## Requirements

- PHP >= 8.2 (developed and tested on PHP 8.4.22)
- Composer >= 2.x
- MySQL 8.0 (or any PDO-compatible database)
- The `pdo`, `mbstring` PHP extensions enabled

## Installation (Windows)

```bash
git clone https://github.com/abdurzuhri/anamorphic.git my-app
cd my-app
composer install
copy .env.example .env
```

Edit `.env` and set your database credentials, then generate the schema:

```bash
php ana move --gen
```

Start the development server:

```bash
php ana hallo
```

Visit **http://127.0.0.1:8000** — you should see the Anamorphic welcome screen.

## The `ana` command line tool

| Command | What it does | Laravel equivalent |
|---|---|---|
| `php ana hallo` | Boots the local dev server on `public/` | `php artisan serve` |
| `php ana move --gen` | Runs every pending migration | `php artisan migrate` |
| `php ana make:controller Name` | Scaffolds `app/Http/Controllers/NameController.php` | `php artisan make:controller` |
| `php ana make:model Name` | Scaffolds `app/Models/Name.php` | `php artisan make:model` |
| `php ana make:migration create_x_table` | Scaffolds a migration file | `php artisan make:migration` |
| `php ana list` | Lists all available commands | `php artisan list` |

## Project structure

```
anamorphic/
├── ana                       # CLI entry point
├── app/
│   ├── Http/Controllers/     # Your controllers
│   ├── Http/Middleware/      # Your middleware
│   └── Models/               # Your models
├── bootstrap/app.php         # Builds the Application container
├── config/                   # app.php, database.php, ...
├── database/migrations/      # Migration files
├── public/                   # Web root (index.php, favicon, assets)
├── resources/views/          # Plain-PHP views
├── routes/
│   ├── web.php               # Browser-facing routes
│   └── api.php               # Routes automatically prefixed with /api
├── src/Framework/            # The framework core itself
└── storage/                  # Logs and cache
```

## Routing

```php
// routes/web.php
$route->get('/guests/{id}', [GuestController::class, 'show']);
$route->post('/guests', [GuestController::class, 'store']);
```

## Models

```php
class Guest extends Model
{
    protected string $table = 'guests';
    protected array $fillable = ['name', 'phone'];
}

$guest = Guest::create(['name' => 'Ana', 'phone' => '08123xxxxx']);
$all = Guest::all();
$one = Guest::find(1);
```

## Views

```php
return view('welcome', ['name' => 'World']);
```

## License

Anamorphic is open-sourced software licensed under the [MIT license](LICENSE).

---

<p align="center">Built by <a href="https://github.com/abdurzuhri">Abdurrahman Zuhri</a></p>
