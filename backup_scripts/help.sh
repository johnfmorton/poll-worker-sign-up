#!/usr/bin/env bash

# Help script for Database Backup System
# Displays usage documentation for all backup scripts

set -euo pipefail

cat << 'EOF'
================================================================================
                      Database Backup Scripts
================================================================================

A collection of shell scripts for automating database backup, restoration,
and synchronization to S3-compatible storage.

--------------------------------------------------------------------------------
AVAILABLE SCRIPTS
--------------------------------------------------------------------------------

  backup_db.sh
    Create a timestamped backup of the database to the local backup directory.
    The backup is compressed with gzip for efficient storage.

  restore_db.sh [backup_file]
    Restore the database from a backup file. If no file is specified, restores
    from the most recent backup in the backup directory.

  sync_backups_to_s3.sh
    Synchronize local backup files to S3-compatible storage (AWS S3,
    DigitalOcean Spaces, MinIO, etc.) for off-site disaster recovery.

  help.sh
    Display this help message.

--------------------------------------------------------------------------------
USAGE EXAMPLES
--------------------------------------------------------------------------------

  Manual Execution:

    ./backup_db.sh
      Creates a new backup with timestamp: backup_YYYYMMDD_HHMMSS.sql.gz

    ./restore_db.sh
      Restores from the most recent backup file

    ./restore_db.sh backup_20250130_143022.sql.gz
      Restores from a specific backup file

    ./sync_backups_to_s3.sh
      Uploads all local backups to configured S3 bucket

--------------------------------------------------------------------------------
CONFIGURATION
--------------------------------------------------------------------------------

  Before using these scripts, you must create a configuration file:

    1. Copy the example configuration:
       cp example.env.sh .env.sh

    2. Edit .env.sh with your credentials and settings

    3. Set restrictive permissions:
       chmod 600 .env.sh

  Required Configuration Variables:

    SITE_NAME
      Descriptive name for this site (used in email notifications)

    LOCAL_DB_NAME
      Database name to backup/restore

    LOCAL_DB_USER
      Database username

    LOCAL_DB_PASSWORD
      Database password

    LOCAL_DB_HOST
      Database host (default: localhost)

    LOCAL_DB_PORT
      Database port (default: 3306)

    BACKUP_DIR
      Local directory path for storing backup files

    BACKUP_RETENTION_COUNT
      Number of backup files to retain (default: 7)
      Set to 0 to disable automatic cleanup and keep all backups

  S3 Configuration (required for sync_backups_to_s3.sh):

    REMOTE_S3_BUCKET
      S3 bucket name

    REMOTE_S3_PATH
      Path within the S3 bucket

    REMOTE_S3_DELETE
      Set to "true" to delete remote files that don't exist locally
      Set to "false" to keep all remote files (default: false)

    AWS_PROFILE
      AWS CLI profile name (optional, if using named profiles)

  Email Notification Configuration (optional):

    EMAIL_NOTIFY_MODE
      Controls when email notifications are sent (see below)

    EMAIL_TO
      Recipient email address

    EMAIL_FROM
      Sender email address

    SMTP_HOST
      SMTP server hostname

    SMTP_PORT
      SMTP server port (default: 587)

    SMTP_USER
      SMTP username

    SMTP_PASSWORD
      SMTP password

    SMTP_USE_TLS
      Use TLS encryption: "true" or "false" (default: true)

--------------------------------------------------------------------------------
EMAIL NOTIFICATION MODES
--------------------------------------------------------------------------------

  SUCCESS_AND_FAILURE
    Send email notifications for both successful and failed script executions.
    Use this mode for comprehensive monitoring of all backup operations.

  FAILURE_ONLY
    Send email notifications only when script execution fails.
    Use this mode to reduce email volume while staying informed of issues.

  NEVER
    Disable all email notifications.
    Use this mode if you prefer to monitor backups through other means
    (e.g., cron job logs, external monitoring tools).

--------------------------------------------------------------------------------
CRON JOB EXAMPLES
--------------------------------------------------------------------------------

  To automate backups, add entries to your crontab (crontab -e):

  # Daily backup at 2:00 AM
  0 2 * * * /path/to/backup_scripts/backup_db.sh

  # Backup every 6 hours
  0 */6 * * * /path/to/backup_scripts/backup_db.sh

  # Sync to S3 every 6 hours (offset by 30 minutes after backup)
  30 */6 * * * /path/to/backup_scripts/sync_backups_to_s3.sh

  # Weekly backup on Sunday at 3:00 AM
  0 3 * * 0 /path/to/backup_scripts/backup_db.sh

  # Daily backup with log output
  0 2 * * * /path/to/backup_scripts/backup_db.sh >> /var/log/backup.log 2>&1

  Note: Use absolute paths in cron jobs and ensure the scripts have
  executable permissions (chmod +x *.sh)

--------------------------------------------------------------------------------
EXIT CODES
--------------------------------------------------------------------------------

  0 - Success
  1 - Configuration error
  2 - Dependency error (missing required tool)
  3 - Operation error (backup/restore/sync failed)
  4 - File not found error

--------------------------------------------------------------------------------
PREREQUISITES
--------------------------------------------------------------------------------

  Required Tools:
    - bash (version 4.0+)
    - mysql/mysqldump (for backup_db.sh and restore_db.sh)
    - gzip (for compression)
    - aws CLI (for sync_backups_to_s3.sh only)
    - sendmail or curl (for email notifications, if enabled)

  Installation:
    - AWS CLI: https://aws.amazon.com/cli/
    - MySQL Client: Install via your package manager (apt, yum, brew, etc.)

--------------------------------------------------------------------------------
DETAILED DOCUMENTATION
--------------------------------------------------------------------------------

  For comprehensive documentation including:
    - Step-by-step setup instructions
    - Detailed configuration examples
    - S3-compatible storage provider examples
    - Troubleshooting guide
    - Security best practices

  Please see: DOCUMENTATION.md

================================================================================
EOF
