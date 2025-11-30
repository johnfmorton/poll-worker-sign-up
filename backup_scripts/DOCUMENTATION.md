# Database Backup Scripts Documentation

## Introduction

The Database Backup Scripts system provides a complete solution for automating database backup, restoration, and off-site synchronization operations. This collection of shell scripts is designed to be simple, portable, and suitable for both manual execution and automated scheduling via cron jobs.

### Overview

The system consists of four main scripts:

- **backup_db.sh** - Creates timestamped database backups
- **restore_db.sh** - Restores database from backup files
- **sync_backups_to_s3.sh** - Synchronizes backups to S3-compatible storage
- **help.sh** - Displays usage documentation

All scripts share a common configuration file and notification system, ensuring consistent behavior across operations.

### Key Features

- Automated database backup with timestamp-based naming
- Flexible restore options (specific file or most recent)
- S3-compatible storage synchronization
- Configurable email notifications (success, failure, or never)
- Comprehensive error handling with helpful messages
- Support for multiple S3-compatible providers (AWS S3, DigitalOcean Spaces, MinIO, etc.)
- Cron-ready for automated scheduling

## Prerequisites

### System Requirements

- **Operating System**: Linux or macOS
- **Shell**: Bash 4.0 or higher
- **Database**: MySQL or MariaDB
- **Required Utilities**:
  - `bash` (version 4.0+)
  - `mysql` client
  - `mysqldump`
  - `gzip`
  - `date`
  - `curl` (for email notifications)

### Optional Requirements

- **AWS CLI** - Required only for S3 synchronization (`sync_backups_to_s3.sh`)
  - Installation: https://aws.amazon.com/cli/
- **SMTP Server Access** - Required only for email notifications

### DDEV Environment

This project uses DDEV for local development. All database operations should be executed through DDEV containers:

```bash
# Access DDEV web container
ddev ssh

# Run backup scripts from within the container
cd /var/www/html/backup_scripts
./backup_db.sh
```

## Installation

### Step 1: Script Placement

The scripts are located in the `backup_scripts/` directory in your project root. If you're setting up a new installation:

```bash
# Ensure the backup_scripts directory exists
mkdir -p backup_scripts
cd backup_scripts
```

### Step 2: Set File Permissions

Make all shell scripts executable:

```bash
chmod +x backup_db.sh
chmod +x restore_db.sh
chmod +x sync_backups_to_s3.sh
chmod +x help.sh
chmod +x shared.sh
```

### Step 3: Create Configuration File

Copy the example configuration file and customize it:

```bash
cp example.env.sh .env.sh
chmod 600 .env.sh  # Restrict permissions for security
```

**Important**: The `.env.sh` file contains sensitive credentials and should have restrictive permissions (600) to prevent unauthorized access.

### Step 4: Create Backup Directory

Create the directory where backup files will be stored:

```bash
mkdir -p backups
chmod 755 backups
```

## Configuration

### Configuration File Setup

Edit `.env.sh` with your specific settings. All configuration is centralized in this file.

### Site Configuration

```bash
# Descriptive name for this site (used in notifications)
SITE_NAME="My Production Site"
```

### Database Configuration

For DDEV environments, use these settings:

```bash
LOCAL_DB_NAME="db"              # DDEV default database name
LOCAL_DB_USER="db"              # DDEV default username
LOCAL_DB_PASSWORD="db"          # DDEV default password
LOCAL_DB_HOST="db"              # DDEV database container hostname
LOCAL_DB_PORT="3306"            # Standard MySQL port
LOCAL_DB_SCHEMA=""              # Optional: specific schema name
```

For production environments:

```bash
LOCAL_DB_NAME="production_db"
LOCAL_DB_USER="backup_user"
LOCAL_DB_PASSWORD="secure_password_here"
LOCAL_DB_HOST="localhost"       # Or remote host IP/domain
LOCAL_DB_PORT="3306"
LOCAL_DB_SCHEMA=""
```

### Backup Directory Configuration

```bash
# Absolute path to backup directory
BACKUP_DIR="/var/www/html/backup_scripts/backups"

# Or relative to script location
BACKUP_DIR="./backups"
```

### S3 Configuration

#### AWS S3

```bash
REMOTE_S3_BUCKET="my-backup-bucket"
REMOTE_S3_PATH="database-backups/production"
REMOTE_S3_DELETE="false"        # Set to "true" to delete remote files not in local
AWS_PROFILE=""                  # Optional: AWS CLI profile name
```

#### DigitalOcean Spaces

```bash
REMOTE_S3_BUCKET="my-space-name"
REMOTE_S3_PATH="database-backups"
REMOTE_S3_DELETE="false"
AWS_PROFILE="digitalocean"      # Configure this profile in AWS CLI
```

Configure AWS CLI for DigitalOcean Spaces:

```bash
aws configure --profile digitalocean
# AWS Access Key ID: Your Spaces access key
# AWS Secret Access Key: Your Spaces secret key
# Default region name: nyc3 (or your region)
# Default output format: json
```

Then add to `~/.aws/config`:

```ini
[profile digitalocean]
region = nyc3
endpoint_url = https://nyc3.digitaloceanspaces.com
```

#### MinIO (Self-Hosted)

```bash
REMOTE_S3_BUCKET="backups"
REMOTE_S3_PATH="database"
REMOTE_S3_DELETE="false"
AWS_PROFILE="minio"
```

Configure AWS CLI for MinIO:

```bash
aws configure --profile minio
# AWS Access Key ID: Your MinIO access key
# AWS Secret Access Key: Your MinIO secret key
```

Then add to `~/.aws/config`:

```ini
[profile minio]
endpoint_url = http://minio.example.com:9000
```

### Email Notification Configuration

#### Notification Modes

- **SUCCESS_AND_FAILURE** - Send email for all script executions
- **FAILURE_ONLY** - Send email only when scripts fail
- **NEVER** - Disable all email notifications

#### SMTP Settings

```bash
EMAIL_NOTIFY_MODE="FAILURE_ONLY"
EMAIL_TO="admin@example.com"
EMAIL_FROM="backups@example.com"
SMTP_HOST="smtp.gmail.com"
SMTP_PORT="587"
SMTP_USER="your-email@gmail.com"
SMTP_PASSWORD="your-app-password"
SMTP_USE_TLS="true"
```

#### Gmail Configuration Example

For Gmail, you need to use an App Password:

1. Enable 2-Factor Authentication on your Google account
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Use the generated password in `SMTP_PASSWORD`

```bash
SMTP_HOST="smtp.gmail.com"
SMTP_PORT="587"
SMTP_USER="your-email@gmail.com"
SMTP_PASSWORD="xxxx xxxx xxxx xxxx"  # 16-character app password
SMTP_USE_TLS="true"
```

### Configuration Variables Reference

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| SITE_NAME | Yes | - | Descriptive site name for notifications |
| LOCAL_DB_NAME | Yes* | - | Database name to backup/restore |
| LOCAL_DB_USER | Yes* | - | Database username |
| LOCAL_DB_PASSWORD | Yes* | - | Database password |
| LOCAL_DB_HOST | Yes* | localhost | Database host |
| LOCAL_DB_PORT | No | 3306 | Database port |
| LOCAL_DB_SCHEMA | No | - | Specific schema name (optional) |
| BACKUP_DIR | Yes | - | Local backup directory path |
| REMOTE_S3_BUCKET | Yes** | - | S3 bucket name |
| REMOTE_S3_PATH | Yes** | - | Path within S3 bucket |
| REMOTE_S3_DELETE | No | false | Enable S3 --delete flag |
| AWS_PROFILE | No | - | AWS CLI profile name |
| EMAIL_NOTIFY_MODE | No | NEVER | Notification mode |
| EMAIL_TO | Yes*** | - | Recipient email address |
| EMAIL_FROM | Yes*** | - | Sender email address |
| SMTP_HOST | Yes*** | - | SMTP server hostname |
| SMTP_PORT | No | 587 | SMTP server port |
| SMTP_USER | Yes*** | - | SMTP username |
| SMTP_PASSWORD | Yes*** | - | SMTP password |
| SMTP_USE_TLS | No | true | Use TLS for SMTP |

\* Required for `backup_db.sh` and `restore_db.sh`  
\*\* Required for `sync_backups_to_s3.sh`  
\*\*\* Required only if `EMAIL_NOTIFY_MODE` is not "NEVER"

## Usage

### Manual Execution

#### Creating a Backup

```bash
cd backup_scripts
./backup_db.sh
```

**Output**: Creates a timestamped backup file in the format `backup_YYYYMMDD_HHMMSS.sql.gz`

**Example**:
```
[2025-11-30 14:30:22] Starting database backup...
[2025-11-30 14:30:25] Backup completed: backups/backup_20251130_143022.sql.gz
[2025-11-30 14:30:25] Backup successful
```

#### Restoring from Backup

Restore from the most recent backup:

```bash
./restore_db.sh
```

Restore from a specific backup file:

```bash
./restore_db.sh backups/backup_20251130_143022.sql.gz
```

**Example**:
```
[2025-11-30 14:35:10] Starting database restore...
[2025-11-30 14:35:10] Using backup file: backups/backup_20251130_143022.sql.gz
[2025-11-30 14:35:15] Restore completed successfully
```

#### Synchronizing to S3

```bash
./sync_backups_to_s3.sh
```

**Example**:
```
[2025-11-30 14:40:00] Starting S3 sync...
[2025-11-30 14:40:05] Synced 5 files to s3://my-bucket/database-backups/
[2025-11-30 14:40:05] S3 sync successful
```

#### Viewing Help

```bash
./help.sh
```

### Exit Codes

All scripts use consistent exit codes:

| Code | Meaning |
|------|---------|
| 0 | Success |
| 1 | Configuration error |
| 2 | Dependency error |
| 3 | Operation error |
| 4 | File not found error |

You can check the exit code in your shell:

```bash
./backup_db.sh
echo $?  # Prints the exit code
```

## Cron Job Setup

### Recommended Schedules

#### Daily Backup at 2 AM

```cron
0 2 * * * cd /var/www/html/backup_scripts && ./backup_db.sh >> /var/log/backup.log 2>&1
```

#### Backup Every 6 Hours

```cron
0 */6 * * * cd /var/www/html/backup_scripts && ./backup_db.sh >> /var/log/backup.log 2>&1
```

#### Sync to S3 Every 12 Hours

```cron
0 */12 * * * cd /var/www/html/backup_scripts && ./sync_backups_to_s3.sh >> /var/log/s3-sync.log 2>&1
```

#### Weekly Backup on Sunday at 3 AM

```cron
0 3 * * 0 cd /var/www/html/backup_scripts && ./backup_db.sh >> /var/log/backup.log 2>&1
```

### Complete Cron Configuration Example

Edit your crontab:

```bash
crontab -e
```

Add the following entries:

```cron
# Database backup scripts
SHELL=/bin/bash
PATH=/usr/local/bin:/usr/bin:/bin

# Daily backup at 2 AM
0 2 * * * cd /var/www/html/backup_scripts && ./backup_db.sh >> /var/log/backup.log 2>&1

# Sync to S3 at 4 AM daily
0 4 * * * cd /var/www/html/backup_scripts && ./sync_backups_to_s3.sh >> /var/log/s3-sync.log 2>&1

# Clean up old backups (keep last 30 days)
0 5 * * * find /var/www/html/backup_scripts/backups -name "backup_*.sql.gz" -mtime +30 -delete
```

### Cron Best Practices

1. **Use absolute paths** - Always specify full paths to scripts and log files
2. **Set environment variables** - Define SHELL and PATH in crontab
3. **Redirect output** - Capture stdout and stderr to log files
4. **Test manually first** - Verify scripts work before scheduling
5. **Monitor logs** - Regularly check log files for errors
6. **Use email notifications** - Configure EMAIL_NOTIFY_MODE for alerts

### Log Rotation

To prevent log files from growing too large, set up log rotation:

Create `/etc/logrotate.d/backup-scripts`:

```
/var/log/backup.log /var/log/s3-sync.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
    create 0644 root root
}
```

## Email Notifications

### Testing Email Configuration

Test email notifications by running a script with `EMAIL_NOTIFY_MODE="SUCCESS_AND_FAILURE"`:

```bash
# Edit .env.sh temporarily
EMAIL_NOTIFY_MODE="SUCCESS_AND_FAILURE"

# Run a backup to test
./backup_db.sh
```

Check your email for a notification with the subject:
```
[SUCCESS] My Production Site - backup_db.sh - 2025-11-30 14:30:22
```

### Email Content Format

**Success Email**:
```
Subject: [SUCCESS] My Production Site - backup_db.sh - 2025-11-30 14:30:22

Site: My Production Site
Script: backup_db.sh
Status: SUCCESS
Timestamp: 2025-11-30 14:30:22
Host: web-container

Backup completed successfully
Backup file: backups/backup_20251130_143022.sql.gz
```

**Failure Email**:
```
Subject: [FAILURE] My Production Site - backup_db.sh - 2025-11-30 14:30:22

Site: My Production Site
Script: backup_db.sh
Status: FAILURE
Timestamp: 2025-11-30 14:30:22
Host: web-container

[ERROR] Database connection failed
Unable to connect to database 'production_db' on host 'localhost'
Please verify database credentials in .env.sh
```

### Troubleshooting Email Delivery

#### Emails Not Being Received

1. **Check SMTP credentials**:
   ```bash
   # Test SMTP connection
   curl --url "smtp://${SMTP_HOST}:${SMTP_PORT}" \
        --ssl-reqd \
        --mail-from "${EMAIL_FROM}" \
        --mail-rcpt "${EMAIL_TO}" \
        --user "${SMTP_USER}:${SMTP_PASSWORD}" \
        -v
   ```

2. **Verify EMAIL_NOTIFY_MODE**:
   - Ensure it's not set to "NEVER"
   - For testing, use "SUCCESS_AND_FAILURE"

3. **Check spam folder** - Emails may be filtered as spam

4. **Review script logs** - Look for email-related error messages

#### Gmail-Specific Issues

- **"Username and Password not accepted"** - Use an App Password, not your regular password
- **"Less secure app access"** - Enable 2FA and generate an App Password
- **Rate limiting** - Gmail may limit automated emails; consider using a dedicated SMTP service

## Troubleshooting

### Common Errors and Solutions

#### Error: Configuration file not found

```
[ERROR] Configuration file not found: .env.sh
Please copy example.env.sh to .env.sh and configure it.
```

**Solution**: Create the configuration file:
```bash
cp example.env.sh .env.sh
chmod 600 .env.sh
# Edit .env.sh with your settings
```

#### Error: Required variables missing

```
[ERROR] Required configuration variables are missing:
  - LOCAL_DB_NAME
  - LOCAL_DB_USER
  - LOCAL_DB_PASSWORD
Please check your .env.sh file.
```

**Solution**: Edit `.env.sh` and ensure all required variables are set:
```bash
LOCAL_DB_NAME="db"
LOCAL_DB_USER="db"
LOCAL_DB_PASSWORD="db"
```

#### Error: Database connection failed

```
[ERROR] Database connection failed
Unable to connect to database 'db' on host 'localhost'
```

**Solution**:
- Verify database credentials in `.env.sh`
- Ensure database server is running
- Check network connectivity to database host
- For DDEV: Use `LOCAL_DB_HOST="db"` (not "localhost")

#### Error: AWS CLI not installed

```
[ERROR] AWS CLI not installed
The sync_backups_to_s3.sh script requires AWS CLI to be installed.
Please install AWS CLI: https://aws.amazon.com/cli/
```

**Solution**: Install AWS CLI:
```bash
# macOS
brew install awscli

# Linux (Ubuntu/Debian)
sudo apt-get install awscli

# Or use pip
pip install awscli
```

#### Error: Backup directory not writable

```
[ERROR] Cannot write to backup directory: /path/to/backups
```

**Solution**: Check directory permissions:
```bash
mkdir -p /path/to/backups
chmod 755 /path/to/backups
```

#### Error: No backup files found

```
[ERROR] No backup files found in directory: backups/
Please run backup_db.sh first to create a backup.
```

**Solution**: Create a backup before attempting to restore:
```bash
./backup_db.sh
```

#### Error: S3 access denied

```
[ERROR] S3 sync failed
Access denied to bucket 'my-bucket'
```

**Solution**:
- Verify AWS credentials are configured correctly
- Check IAM permissions for the S3 bucket
- Ensure bucket name and path are correct in `.env.sh`

### Debugging Tips

#### Enable Verbose Output

Add `-x` flag to script shebang for detailed execution trace:

```bash
#!/bin/bash -x
```

Or run with bash -x:

```bash
bash -x ./backup_db.sh
```

#### Check Script Logs

Review log files for detailed error information:

```bash
tail -f /var/log/backup.log
tail -f /var/log/s3-sync.log
```

#### Test Database Connection

Manually test database connectivity:

```bash
mysql -h "${LOCAL_DB_HOST}" \
      -P "${LOCAL_DB_PORT}" \
      -u "${LOCAL_DB_USER}" \
      -p"${LOCAL_DB_PASSWORD}" \
      "${LOCAL_DB_NAME}" \
      -e "SELECT 1;"
```

#### Test S3 Access

Manually test S3 connectivity:

```bash
aws s3 ls s3://${REMOTE_S3_BUCKET}/${REMOTE_S3_PATH}/ --profile ${AWS_PROFILE}
```

#### Verify File Permissions

Check that scripts are executable:

```bash
ls -la backup_scripts/
# Should show -rwxr-xr-x for .sh files
```

Check configuration file permissions:

```bash
ls -la backup_scripts/.env.sh
# Should show -rw------- (600)
```

## S3-Compatible Storage Examples

### AWS S3

**Configuration**:
```bash
REMOTE_S3_BUCKET="my-backup-bucket"
REMOTE_S3_PATH="database-backups/production"
REMOTE_S3_DELETE="false"
AWS_PROFILE=""  # Uses default profile
```

**AWS CLI Setup**:
```bash
aws configure
# AWS Access Key ID: Your access key
# AWS Secret Access Key: Your secret key
# Default region name: us-east-1
# Default output format: json
```

**IAM Policy** (minimum required permissions):
```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:ListBucket"
      ],
      "Resource": "arn:aws:s3:::my-backup-bucket"
    },
    {
      "Effect": "Allow",
      "Action": [
        "s3:PutObject",
        "s3:GetObject",
        "s3:DeleteObject"
      ],
      "Resource": "arn:aws:s3:::my-backup-bucket/database-backups/*"
    }
  ]
}
```

### DigitalOcean Spaces

**Configuration**:
```bash
REMOTE_S3_BUCKET="my-space-name"
REMOTE_S3_PATH="database-backups"
REMOTE_S3_DELETE="false"
AWS_PROFILE="digitalocean"
```

**AWS CLI Setup**:
```bash
# Configure profile
aws configure --profile digitalocean

# Edit ~/.aws/config
[profile digitalocean]
region = nyc3
endpoint_url = https://nyc3.digitaloceanspaces.com
```

**Available Regions**:
- nyc3 (New York)
- sfo3 (San Francisco)
- ams3 (Amsterdam)
- sgp1 (Singapore)
- fra1 (Frankfurt)

### Wasabi

**Configuration**:
```bash
REMOTE_S3_BUCKET="my-wasabi-bucket"
REMOTE_S3_PATH="backups"
REMOTE_S3_DELETE="false"
AWS_PROFILE="wasabi"
```

**AWS CLI Setup**:
```bash
aws configure --profile wasabi

# Edit ~/.aws/config
[profile wasabi]
region = us-east-1
endpoint_url = https://s3.wasabisys.com
```

### MinIO (Self-Hosted)

**Configuration**:
```bash
REMOTE_S3_BUCKET="backups"
REMOTE_S3_PATH="database"
REMOTE_S3_DELETE="false"
AWS_PROFILE="minio"
```

**AWS CLI Setup**:
```bash
aws configure --profile minio

# Edit ~/.aws/config
[profile minio]
endpoint_url = http://minio.example.com:9000
```

**MinIO Server Setup** (for testing):
```bash
# Run MinIO in Docker
docker run -p 9000:9000 -p 9001:9001 \
  -e "MINIO_ROOT_USER=minioadmin" \
  -e "MINIO_ROOT_PASSWORD=minioadmin" \
  minio/minio server /data --console-address ":9001"

# Create bucket
aws --endpoint-url http://localhost:9000 \
    s3 mb s3://backups \
    --profile minio
```

### Backblaze B2

**Configuration**:
```bash
REMOTE_S3_BUCKET="my-b2-bucket"
REMOTE_S3_PATH="database-backups"
REMOTE_S3_DELETE="false"
AWS_PROFILE="backblaze"
```

**AWS CLI Setup**:
```bash
aws configure --profile backblaze

# Edit ~/.aws/config
[profile backblaze]
region = us-west-002
endpoint_url = https://s3.us-west-002.backblazeb2.com
```

## Security Best Practices

### File Permissions

**Configuration File**:
```bash
chmod 600 .env.sh  # Owner read/write only
```

**Script Files**:
```bash
chmod 755 *.sh  # Owner read/write/execute, others read/execute
```

**Backup Directory**:
```bash
chmod 755 backups/  # Owner full access, others read/execute
chmod 644 backups/*.sql.gz  # Backup files readable by owner and group
```

### Credential Management

1. **Never commit `.env.sh`** - Ensure it's in `.gitignore`
2. **Use environment-specific configs** - Separate configs for dev/staging/production
3. **Rotate credentials regularly** - Change passwords and access keys periodically
4. **Use IAM roles when possible** - For AWS EC2 instances, use IAM roles instead of access keys
5. **Limit database user permissions** - Create a dedicated backup user with minimal privileges:

```sql
-- Create backup user with read-only access
CREATE USER 'backup_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER ON database_name.* TO 'backup_user'@'localhost';
FLUSH PRIVILEGES;
```

### Backup Encryption

For sensitive data, encrypt backups before uploading to S3:

**Encrypt backup**:
```bash
# After backup_db.sh creates the file
gpg --symmetric --cipher-algo AES256 backups/backup_20251130_143022.sql.gz
```

**Decrypt backup**:
```bash
# Before restore_db.sh
gpg --decrypt backups/backup_20251130_143022.sql.gz.gpg > backups/backup_20251130_143022.sql.gz
```

**Automated encryption** (modify backup_db.sh):
```bash
# Add after backup creation
if [ "${ENCRYPT_BACKUPS}" = "true" ]; then
    gpg --batch --yes --passphrase "${GPG_PASSPHRASE}" \
        --symmetric --cipher-algo AES256 "${backup_file}"
    rm "${backup_file}"  # Remove unencrypted file
fi
```

### S3 Security

1. **Enable bucket encryption** - Use server-side encryption (SSE-S3 or SSE-KMS)
2. **Use bucket policies** - Restrict access to specific IP addresses or IAM roles
3. **Enable versioning** - Protect against accidental deletion
4. **Enable MFA delete** - Require multi-factor authentication for deletions
5. **Use private buckets** - Never make backup buckets public

**Example S3 Bucket Policy**:
```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Deny",
      "Principal": "*",
      "Action": "s3:*",
      "Resource": [
        "arn:aws:s3:::my-backup-bucket",
        "arn:aws:s3:::my-backup-bucket/*"
      ],
      "Condition": {
        "Bool": {
          "aws:SecureTransport": "false"
        }
      }
    }
  ]
}
```

### Network Security

1. **Use TLS for SMTP** - Always set `SMTP_USE_TLS="true"`
2. **Use HTTPS for S3** - AWS CLI uses HTTPS by default
3. **Restrict database access** - Use firewall rules to limit database connections
4. **Use VPN for remote backups** - When backing up over the internet

### Monitoring and Auditing

1. **Enable S3 access logging** - Track all access to backup files
2. **Monitor email notifications** - Ensure you receive expected notifications
3. **Review backup logs regularly** - Check for failed backups or anomalies
4. **Set up alerts** - Use CloudWatch or similar for S3 access patterns
5. **Test restore procedures** - Regularly verify backups can be restored

### Backup Retention

Implement a retention policy to manage storage costs:

```bash
# Keep daily backups for 7 days
find backups/ -name "backup_*.sql.gz" -mtime +7 -delete

# Keep weekly backups for 30 days
# Keep monthly backups for 1 year
```

**Automated retention** (add to crontab):
```cron
# Daily cleanup - keep last 30 days locally
0 6 * * * find /var/www/html/backup_scripts/backups -name "backup_*.sql.gz" -mtime +30 -delete

# S3 lifecycle policy (configure in AWS console)
# - Transition to Glacier after 90 days
# - Delete after 365 days
```

## Additional Resources

### Official Documentation

- **MySQL Documentation**: https://dev.mysql.com/doc/
- **AWS CLI Documentation**: https://docs.aws.amazon.com/cli/
- **AWS S3 Documentation**: https://docs.aws.amazon.com/s3/
- **Bash Scripting Guide**: https://www.gnu.org/software/bash/manual/

### Related Tools

- **mysqldump**: https://dev.mysql.com/doc/refman/8.0/en/mysqldump.html
- **AWS CLI S3 Commands**: https://docs.aws.amazon.com/cli/latest/reference/s3/
- **Cron Expression Generator**: https://crontab.guru/

### Support

For issues or questions:

1. Check the troubleshooting section above
2. Review script logs for error messages
3. Run `./help.sh` for quick reference
4. Consult the design document at `.kiro/specs/database-backup-scripts/design.md`

## Version History

- **v1.0.0** (2025-11-30) - Initial release
  - Database backup and restore functionality
  - S3 synchronization support
  - Email notification system
  - Comprehensive documentation

---

**Last Updated**: 2025-11-30  
**Maintained By**: Development Team
