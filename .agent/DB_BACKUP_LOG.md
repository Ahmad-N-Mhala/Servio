# Database Backup & Git Sync
**Date:** 2025-12-28
**Action:** DB Backup & Code Push
**Status:** ✅ COMPLETE

## Actions
1.  **Database Dump:**
    - Tool: `mongodump`
    - Target: `Servio_central`
    - Output: `./db_backup/` containing BSON/JSON metadata for all collections.
    
2.  **Git Sync:**
    - Added `./db_backup` directory to Git (not ignored).
    - Added all recent changes (Admin sync, email config, etc.).
    - Committed with message: "Backup: Database dump and Admin/Onboarding sync updates".
    - Pushed to `origin 26-12-25`.

## Notes
- To restore this DB: `mongorestore --db Servio_central --drop db_backup/Servio_central`
