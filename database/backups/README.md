# Database Backups

This directory contains SQL backups of the RestaurFy databases.

## Files

- `restaurfy_central.sql` - Central database backup (tenants, domains, etc.)
- `restaurfy_tenant_ahmadtest.sql` - Demo tenant database backup (users, orders, etc.)

## Restoring Backups

To restore these backups on a fresh installation:

```bash
# Restore central database
psql -U your_username -d restaurfy_central < restaurfy_central.sql

# Restore tenant database
psql -U your_username -d restaurfy_tenant_ahmadtest < restaurfy_tenant_ahmadtest.sql
```

See the main [SETUP_GUIDE.md](../../SETUP_GUIDE.md) for complete setup instructions.
