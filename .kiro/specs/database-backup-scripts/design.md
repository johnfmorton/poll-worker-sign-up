# Design Document

## Overview

The database backup system consists of four shell scripts that provide a complete solution for database backup, restoration, and off-site synchronization. The system is designed to be simple, portable, and suitable for both manual execution and automated cron jobs. All scripts share a common configuration file and notification system, ensuring consistent behavior across operations.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Backup Scripts System                     │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  backup_db   │  │ restore_db   │  │sync_backups  │      │
│  │    .sh       │  │    .sh       │  │  _to_s3.sh   │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                  │                  │              │
│         └──────────────────┴──────────────────┘              │
│                            │                                 │
│                   ┌────────▼────────┐                        │
│                   │  Common Library │                        │
│                   │   (shared.sh)   │                        │
│                   └────────┬────────┘                        │
│                            │                                 │
│              ┌─────────────┼─────────────┐                  │
│              │             │             │                  │
│      ┌───────▼──────┐ ┌───▼────┐ ┌─────▼──────┐           │
│      │ Config Loader│ │Notifier│ │Error Handler│           │
│      └──────────────┘ └────────┘ └────────────┘           │
│                                                               │
└─────────────────────────────────────────────────────────────┘
         │                    │                    │
         ▼                    ▼                    ▼
   ┌──────────┐        ┌──────────┐        ┌──────────┐
   │ MySQL DB │        │Local Disk│        │ S3 Bucket│
   └──────────┘        └──────────┘        └──────────┘
```

### Component Interaction Flow

1. **Script Execution**: User or cron job invokes a script
2. **Configuration Loading**: Script sources `.env.sh` and validates required variables
3. **Operation Execution**: Script performs its primary function (backup/restore/sync)
4. **Notification**: On completion or failure, notification system sends email if configured
5. **Exit**: Script exits with appropriate status code

## Components and Interfaces

### 1. Configuration System

**File**: `backup_scripts/.env.sh` (user-created from `example.env.sh`)

**Purpose**: Centralized configuration for all scripts

**Variables**:

```bash
# Site Configuration
SITE_NAME=""               # Descriptive name for this site (used in notifications)

# Database Configuration
LOCAL_DB_NAME=""           # Database name
LOCAL_DB_USER=""           # Database username
LOCAL_DB_PASSWORD=""       # Database password
LOCAL_DB_HOST="localhost"  # Database host
LOCAL_DB_PORT="3306"       # Database port
LOCAL_DB_SCHEMA=""         # Schema name (optional, for specific DB systems)

# Backup Configuration
BACKUP_DIR=""              # Local directory for backup files

# S3 Configuration
REMOTE_S3_BUCKET=""        # S3 bucket name
REMOTE_S3_PATH=""          # Path within bucket
REMOTE_S3_DELETE="false"   # Enable --delete flag (true/false)
AWS_PROFILE=""             # AWS CLI profile (optional)

# Email Notification Configuration
EMAIL_NOTIFY_MODE="NEVER"  # Options: SUCCESS_AND_FAILURE, FAILURE_ONLY, NEVER
EMAIL_TO=""                # Recipient email address
EMAIL_FROM=""              # Sender email address
SMTP_HOST=""               # SMTP server hostname
SMTP_PORT="587"            # SMTP server port
SMTP_USER=""               # SMTP username
SMTP_PASSWORD=""           # SMTP password
SMTP_USE_TLS="true"        # Use TLS (true/false)
```

### 2. Common Library (`shared.sh`)

**Purpose**: Shared functions used across all scripts

**Functions**:

- `load_config()`: Sources `.env.sh` and validates required variables
- `validate_required_vars()`: Checks that required environment variables are set
- `log_message()`: Outputs timestamped log messages
- `send_notification()`: Sends email notifications based on configuration
- `exit_with_error()`: Logs error, sends notification if configured, and exits with error code
- `exit_with_success()`: Logs success, sends notification if configured, and exits with code 0

### 3. Backup Script (`backup_db.sh`)

**Purpose**: Create timestamped database backup

**Interface**:
```bash
./backup_db.sh
```

**Behavior**:
1. Load configuration
2. Validate database credentials
3. Create backup directory if it doesn't exist
4. Generate timestamped filename: `backup_YYYYMMDD_HHMMSS.sql`
5. Execute `mysqldump` with credentials from config
6. Compress backup file with gzip
7. Send notification based on result
8. Exit with appropriate status code

**Output**: `${BACKUP_DIR}/backup_YYYYMMDD_HHMMSS.sql.gz`

### 4. Restore Script (`restore_db.sh`)

**Purpose**: Restore database from backup file

**Interface**:
```bash
./restore_db.sh [backup_file]
```

**Arguments**:
- `backup_file` (optional): Specific backup file to restore. If omitted, uses most recent backup.

**Behavior**:
1. Load configuration
2. Validate database credentials
3. Determine backup file:
   - If argument provided, use specified file
   - Otherwise, find most recent file in backup directory
4. Validate backup file exists
5. Decompress if gzipped
6. Execute `mysql` to restore database
7. Send notification based on result
8. Exit with appropriate status code

### 5. S3 Sync Script (`sync_backups_to_s3.sh`)

**Purpose**: Synchronize local backups to S3-compatible storage

**Interface**:
```bash
./sync_backups_to_s3.sh
```

**Behavior**:
1. Load configuration
2. Check if AWS CLI is installed
3. Validate S3 credentials and bucket configuration
4. Build `aws s3 sync` command with appropriate flags
5. Add `--delete` flag if `REMOTE_S3_DELETE=true`
6. Execute sync operation
7. Send notification based on result
8. Exit with appropriate status code

**AWS CLI Command**:
```bash
aws s3 sync ${BACKUP_DIR} s3://${REMOTE_S3_BUCKET}/${REMOTE_S3_PATH} [--delete]
```

### 6. Help Script (`help.sh`)

**Purpose**: Display usage documentation

**Interface**:
```bash
./help.sh
```

**Output**: Formatted help text including:
- Script descriptions
- Usage examples
- Configuration variable descriptions
- Email notification mode explanations
- Cron job examples

## Data Models

### Backup File Naming Convention

**Format**: `backup_YYYYMMDD_HHMMSS.sql.gz`

**Components**:
- `backup_`: Static prefix
- `YYYYMMDD`: Date (e.g., 20250130)
- `HHMMSS`: Time (e.g., 143022)
- `.sql.gz`: Extension indicating gzipped SQL dump

**Example**: `backup_20250130_143022.sql.gz`

### Email Notification Structure

**Subject Line Format**:
- Success: `[SUCCESS] {site_name} - {script_name} - {timestamp}`
- Failure: `[FAILURE] {site_name} - {script_name} - {timestamp}`

**Body Content**:
```
Site: {site_name}
Script: {script_name}
Status: {SUCCESS|FAILURE}
Timestamp: {YYYY-MM-DD HH:MM:SS}
Host: {hostname}

{status_message}

{error_details_if_applicable}
```

### Configuration Validation Rules

**Required Variables by Script**:

| Variable | backup_db.sh | restore_db.sh | sync_backups_to_s3.sh |
|----------|--------------|---------------|------------------------|
| SITE_NAME | ✓ | ✓ | ✓ |
| LOCAL_DB_NAME | ✓ | ✓ | - |
| LOCAL_DB_USER | ✓ | ✓ | - |
| LOCAL_DB_PASSWORD | ✓ | ✓ | - |
| LOCAL_DB_HOST | ✓ | ✓ | - |
| BACKUP_DIR | ✓ | ✓ | ✓ |
| REMOTE_S3_BUCKET | - | - | ✓ |
| REMOTE_S3_PATH | - | - | ✓ |

**Email Variables** (required only if EMAIL_NOTIFY_MODE != "NEVER"):
- EMAIL_TO
- EMAIL_FROM
- SMTP_HOST
- SMTP_USER
- SMTP_PASSWORD

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Backup file creation consistency

*For any* successful execution of backup_db.sh, a new backup file with a valid timestamp format should exist in the backup directory, and the file should contain valid SQL data.

**Validates: Requirements 1.1, 1.3**

### Property 2: Configuration validation completeness

*For any* script execution, if required configuration variables are missing, the script should exit with a non-zero status code before attempting any operations.

**Validates: Requirements 5.2, 5.3**

### Property 3: Restore file selection correctness

*For any* execution of restore_db.sh without arguments, the script should select the backup file with the most recent timestamp from the backup directory.

**Validates: Requirements 2.1**

### Property 4: Restore file argument handling

*For any* execution of restore_db.sh with a file argument, if the specified file exists, it should be used for restoration; if it does not exist, the script should exit with an error before attempting restoration.

**Validates: Requirements 2.2, 2.3**

### Property 5: S3 delete flag consistency

*For any* execution of sync_backups_to_s3.sh, the --delete flag should be present in the aws s3 sync command if and only if REMOTE_S3_DELETE is set to "true".

**Validates: Requirements 3.3, 3.4**

### Property 6: AWS CLI dependency check

*For any* execution of sync_backups_to_s3.sh, if the aws command is not available in the system PATH, the script should exit with an error message before attempting any S3 operations.

**Validates: Requirements 3.2**

### Property 7: Email notification mode compliance

*For any* script execution with EMAIL_NOTIFY_MODE set to "SUCCESS_AND_FAILURE", an email should be sent regardless of execution outcome; with "FAILURE_ONLY", an email should be sent only on failure; with "NEVER", no email should be sent.

**Validates: Requirements 4.1, 4.2, 4.3**

### Property 8: Exit code consistency

*For any* script execution, the exit code should be 0 if and only if the primary operation completed successfully.

**Validates: Requirements 1.3, 1.4, 2.5**

### Property 9: Backup directory creation

*For any* execution of backup_db.sh, if the backup directory does not exist at the start of execution, it should exist after successful completion.

**Validates: Requirements 1.5**

### Property 10: Email notification content completeness

*For any* email notification sent, the message should include the site name, script name, execution status, timestamp, and error details if the execution failed.

**Validates: Requirements 4.4**

## Error Handling

### Error Categories

1. **Configuration Errors**
   - Missing `.env.sh` file
   - Missing required variables
   - Invalid variable values

2. **Dependency Errors**
   - MySQL client not installed
   - AWS CLI not installed (for S3 sync)
   - Missing system utilities (gzip, date, etc.)

3. **Operation Errors**
   - Database connection failures
   - Backup file write failures
   - Restore operation failures
   - S3 sync failures

4. **Notification Errors**
   - SMTP connection failures
   - Invalid email credentials

### Error Handling Strategy

**Fail Fast**: Scripts should validate all prerequisites before beginning operations.

**Clear Error Messages**: All error messages should:
- Clearly state what went wrong
- Provide actionable guidance for resolution
- Include relevant context (file paths, variable names, etc.)

**Error Message Format**:
```
[ERROR] {error_description}
{additional_context}
{suggested_action}
```

**Example**:
```
[ERROR] AWS CLI not installed
The sync_backups_to_s3.sh script requires AWS CLI to be installed.
Please install AWS CLI: https://aws.amazon.com/cli/
```

### Exit Codes

| Code | Meaning |
|------|---------|
| 0 | Success |
| 1 | Configuration error |
| 2 | Dependency error |
| 3 | Operation error |
| 4 | File not found error |

### Notification Error Handling

Email notification failures should NOT cause script failure. The script should:
1. Log the notification error
2. Continue with normal exit behavior
3. Return exit code based on primary operation result

## Testing Strategy

### Unit Testing Approach

Since these are shell scripts, unit testing will use the BATS (Bash Automated Testing System) framework.

**Test Categories**:

1. **Configuration Loading Tests**
   - Test loading valid configuration
   - Test missing configuration file
   - Test missing required variables
   - Test variable validation

2. **Backup Operation Tests**
   - Test backup file creation
   - Test backup directory creation
   - Test timestamp format
   - Test gzip compression

3. **Restore Operation Tests**
   - Test restore with specific file
   - Test restore with most recent file
   - Test restore with missing file
   - Test restore with empty backup directory

4. **S3 Sync Tests**
   - Test AWS CLI detection
   - Test delete flag handling
   - Test S3 credential validation

5. **Notification Tests**
   - Test notification mode handling
   - Test email content generation
   - Test notification failure handling

### Property-Based Testing Approach

Property-based testing for shell scripts will use generated test data and validate properties across multiple executions.

**Testing Framework**: BATS with custom property test helpers

**Test Configuration**: Each property test should run a minimum of 100 iterations with varied inputs.

**Property Test Implementation**:

Each correctness property will be implemented as a property-based test that:
1. Generates random valid inputs (timestamps, file names, configuration values)
2. Executes the script or function
3. Validates the property holds true
4. Reports any counterexamples

**Example Property Test Structure**:
```bash
@test "Property 1: Backup file creation consistency" {
  # Feature: database-backup-scripts, Property 1: Backup file creation consistency
  for i in {1..100}; do
    # Generate random database state
    # Execute backup_db.sh
    # Verify backup file exists with valid timestamp
    # Verify file contains valid SQL
  done
}
```

### Integration Testing

Integration tests will validate end-to-end workflows:

1. **Full Backup-Restore Cycle**
   - Create test database with known data
   - Execute backup
   - Modify database
   - Execute restore
   - Verify data matches original

2. **Backup-Sync-Restore Cycle**
   - Execute backup
   - Execute S3 sync
   - Delete local backup
   - Download from S3
   - Execute restore
   - Verify data integrity

3. **Cron Job Simulation**
   - Execute scripts in cron-like environment
   - Verify email notifications
   - Verify exit codes

### Test Environment Requirements

- MySQL/MariaDB test instance
- S3-compatible storage (MinIO for local testing)
- SMTP test server (MailHog or similar)
- BATS testing framework
- AWS CLI

## Implementation Notes

### Shell Script Best Practices

1. **Use `set -euo pipefail`**: Exit on error, undefined variables, and pipe failures
2. **Quote all variables**: Prevent word splitting and glob expansion
3. **Use `local` for function variables**: Prevent variable leakage
4. **Validate inputs**: Check all arguments and environment variables
5. **Use absolute paths**: Avoid relying on current working directory
6. **Log all operations**: Provide visibility into script execution

### Security Considerations

1. **Credential Protection**
   - `.env.sh` should have restrictive permissions (600)
   - Never log passwords or sensitive credentials
   - Use AWS CLI profiles when possible instead of embedding credentials

2. **SQL Injection Prevention**
   - Database name and credentials should be validated
   - Avoid constructing SQL queries from user input

3. **File System Security**
   - Backup directory should have appropriate permissions
   - Validate file paths to prevent directory traversal

### Portability Considerations

**Target Platforms**: Linux, macOS

**Required Utilities**:
- bash (version 4.0+)
- mysql/mysqldump
- gzip
- date
- aws (for S3 sync only)
- sendmail or equivalent (for email notifications)

**Platform-Specific Handling**:
- Date formatting may differ between GNU and BSD date
- Use portable date format: `date +%Y%m%d_%H%M%S`

### Email Notification Implementation

**Method**: Use `sendmail` command or `mail` utility

**Alternative**: If sendmail is not available, use `curl` with SMTP:

```bash
curl --url "smtp://${SMTP_HOST}:${SMTP_PORT}" \
     --ssl-reqd \
     --mail-from "${EMAIL_FROM}" \
     --mail-rcpt "${EMAIL_TO}" \
     --user "${SMTP_USER}:${SMTP_PASSWORD}" \
     --upload-file email.txt
```

### Timestamp Handling

**Format**: `YYYYMMDD_HHMMSS`

**Generation**: `date +%Y%m%d_%H%M%S`

**Sorting**: Lexicographic sorting of filenames will correctly order by timestamp

**Finding Most Recent**:
```bash
ls -t ${BACKUP_DIR}/backup_*.sql.gz | head -n 1
```

## Documentation Structure

### DOCUMENTATION.md Contents

1. **Introduction**
   - Purpose of the backup system
   - Overview of available scripts

2. **Prerequisites**
   - Required software and tools
   - System requirements

3. **Installation**
   - Where to place scripts
   - Setting file permissions

4. **Configuration**
   - Copying example.env.sh to .env.sh
   - Detailed explanation of each configuration variable
   - Examples for different scenarios (local MySQL, RDS, DigitalOcean Spaces, etc.)

5. **Usage**
   - Manual execution examples for each script
   - Command-line arguments
   - Expected output

6. **Cron Job Setup**
   - Example crontab entries
   - Recommended backup schedules
   - Log file management

7. **Email Notifications**
   - Configuring SMTP settings
   - Testing email notifications
   - Troubleshooting email delivery

8. **Troubleshooting**
   - Common errors and solutions
   - Debugging tips
   - Log file locations

9. **S3-Compatible Storage Examples**
   - AWS S3 configuration
   - DigitalOcean Spaces configuration
   - MinIO configuration
   - Other S3-compatible providers

10. **Security Best Practices**
    - File permissions
    - Credential management
    - Backup encryption recommendations

### help.sh Output Structure

```
Database Backup Scripts
=======================

AVAILABLE SCRIPTS:
  backup_db.sh           - Create database backup
  restore_db.sh          - Restore database from backup
  sync_backups_to_s3.sh  - Sync backups to S3 storage
  help.sh                - Display this help message

USAGE:
  ./backup_db.sh
  ./restore_db.sh [backup_file]
  ./sync_backups_to_s3.sh

CONFIGURATION:
  Copy example.env.sh to .env.sh and configure:
  - Database credentials
  - Backup directory path
  - S3 bucket and credentials
  - Email notification settings

EMAIL NOTIFICATION MODES:
  SUCCESS_AND_FAILURE - Send email on all executions
  FAILURE_ONLY        - Send email only on failures
  NEVER               - Disable email notifications

CRON EXAMPLES:
  # Daily backup at 2 AM
  0 2 * * * /path/to/backup_scripts/backup_db.sh

  # Sync to S3 every 6 hours
  0 */6 * * * /path/to/backup_scripts/sync_backups_to_s3.sh

For detailed documentation, see DOCUMENTATION.md
```
