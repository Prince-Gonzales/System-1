# Neon PostgreSQL Integration Guide

## Connection Parameters

- **Database Connection**: `pgsql`
- **Host**: `ep-billowing-wildflower-a1sdzq5c-pooler.ap-southeast-1.aws.neon.tech`
- **Port**: `5432`
- **Database**: `neondb`
- **Username**: `neondb_owner`
- **SSL Mode**: `require`
- **Driver Options**: `PDO::ATTR_EMULATE_PREPARES => true` (Required for transaction compatibility with Neon pooler)

## Connection String
You can use the following connection string to connect using `psql` or other tools:
```bash
psql 'postgresql://neondb_owner:npg_utXMQePK1pE7@ep-billowing-wildflower-a1sdzq5c-pooler.ap-southeast-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require'
```
*Note: `channel_binding=require` is optional for the Laravel application connection but recommended for `psql` if supported.*

## Configuration Files

### `.env`
Ensure your `.env` file contains the following:
```env
DB_CONNECTION=pgsql
DB_HOST=ep-billowing-wildflower-a1sdzq5c-pooler.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_utXMQePK1pE7
DB_SSLMODE=require

SESSION_DRIVER=database
SESSION_CONNECTION=pgsql
SESSION_LIFETIME=120
```

### `config/database.php`
Ensure the `pgsql` connection includes the SSL mode and options:
```php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => env('DB_SSLMODE', 'prefer'),
    'options' => [
        PDO::ATTR_EMULATE_PREPARES => true, // CRITICAL for Neon
    ],
],
```

## Troubleshooting

### Error: `SQLSTATE[HY000] [2002] No connection could be made...` (Connection: mysql)
**Cause:** The application is trying to connect to MySQL instead of PostgreSQL.
**Solution:**
1. Check `.env` to ensure `DB_CONNECTION=pgsql`.
2. Check `config/session.php` or `.env` to ensure `SESSION_CONNECTION=pgsql` (or unset so it uses default).
3. **Restart the development server** (`php artisan serve`). This is the most common cause if you just changed `.env`.
4. Run `php artisan config:clear`.

### Error: `SQLSTATE[25P02]: In failed sql transaction: 7 ERROR: current transaction is aborted`
**Cause:** Neon's connection pooler does not support certain prepared statement features in transaction mode without emulation.
**Solution:** Ensure `PDO::ATTR_EMULATE_PREPARES => true` is added to the `pgsql` options in `config/database.php`.

## Testing

1. **Standalone Script**: Run `php test_connection.php` to verify credentials and network connectivity independent of Laravel.
2. **Health Check**: Visit `/health-check` in your browser (after restarting server) to see the application's database connection status.
