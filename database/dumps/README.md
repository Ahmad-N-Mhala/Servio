# Database Dumps

This directory contains SQL dumps of all databases (central + tenant databases).

## Exporting Databases

To export all databases to this directory:

```bash
php artisan db:export
```

Or specify a custom output directory:

```bash
php artisan db:export --output=/path/to/dumps
```

This will create:
- `central_<dbname>.sql` - PostgreSQL dump of the central database
- `tenant_<tenant_id>.sql` - MySQL dump for each tenant database

## Importing Databases

To import all databases from this directory:

```bash
php artisan db:import
```

Or specify a custom input directory:

```bash
php artisan db:import --input=/path/to/dumps
```

**⚠️ Warning:** This will drop and recreate existing databases. Use `--force` to skip confirmation prompts:

```bash
php artisan db:import --force
```

## Manual Import (Alternative)

If you prefer to import manually:

### Central Database (PostgreSQL)

```bash
# Create database
createdb -U forge restaurfy

# Import
psql -U forge -d restaurfy < database/dumps/central_restaurfy.sql
```

### Tenant Databases (MySQL)

```bash
# For each tenant dump:
mysql -u forge -p <database_name> < database/dumps/tenant_<tenant_id>.sql
```

## Requirements

- **PostgreSQL**: `pg_dump` and `psql` commands must be available
- **MySQL**: `mysqldump` and `mysql` commands must be available
- Database credentials must be configured in `.env`

## Notes

- Dumps are created without database ownership/ACL information for portability
- Tenant database names are the tenant IDs (e.g., `foo`, `8e4d0e1a-cd54-4193-a3e2-12ab479fe992`)
- Make sure to backup your databases before importing if you have important data

