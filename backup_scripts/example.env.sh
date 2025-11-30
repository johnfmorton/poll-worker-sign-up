#!/usr/bin/env bash

# example.env.sh - Example configuration file for database backup scripts
# 
# INSTRUCTIONS:
# 1. Copy this file to .env.sh:
#    cp example.env.sh .env.sh
# 
# 2. Set restrictive permissions on .env.sh:
#    chmod 600 .env.sh
# 
# 3. Edit .env.sh with your actual configuration values
# 
# 4. Never commit .env.sh to version control (it's in .gitignore)

# ==============================================================================
# SITE CONFIGURATION
# ==============================================================================

# Descriptive name for this site (used in email notifications)
# Example: "Production Database" or "Staging Environment"
SITE_NAME=""

# ==============================================================================
# DATABASE CONFIGURATION
# ==============================================================================

# Database name to backup/restore
LOCAL_DB_NAME=""

# Database username
LOCAL_DB_USER=""

# Database password
LOCAL_DB_PASSWORD=""

# Database host (default: localhost)
LOCAL_DB_HOST="localhost"

# Database port (default: 3306 for MySQL)
LOCAL_DB_PORT="3306"

# Database schema (optional, for specific database systems)
# Leave empty if not needed
LOCAL_DB_SCHEMA=""

# ==============================================================================
# BACKUP CONFIGURATION
# ==============================================================================

# Local directory where backup files will be stored
# This directory will be created if it doesn't exist
# Example: "/var/backups/mysql" or "${HOME}/backups/database"
BACKUP_DIR=""

# ==============================================================================
# S3 CONFIGURATION (for sync_backups_to_s3.sh)
# ==============================================================================

# S3 bucket name (without s3:// prefix)
# Example: "my-company-backups"
REMOTE_S3_BUCKET=""

# Path within the S3 bucket
# Example: "database/production" or "backups/mysql"
REMOTE_S3_PATH=""

# Enable deletion of remote files that don't exist locally
# Options: "true" or "false"
# WARNING: Setting to "true" will delete files from S3 that are not in your local backup directory
REMOTE_S3_DELETE="false"

# AWS CLI profile to use (optional)
# Leave empty to use default credentials
# Example: "production" or "backup-user"
AWS_PROFILE=""

# ==============================================================================
# EMAIL NOTIFICATION CONFIGURATION
# ==============================================================================

# Email notification mode
# Options:
#   - SUCCESS_AND_FAILURE: Send email for both successful and failed executions
#   - FAILURE_ONLY: Send email only when script execution fails
#   - NEVER: Disable all email notifications
EMAIL_NOTIFY_MODE="NEVER"

# Recipient email address
# Example: "admin@example.com" or "alerts@company.com"
EMAIL_TO=""

# Sender email address
# Example: "backups@example.com" or "noreply@company.com"
EMAIL_FROM=""

# SMTP server hostname
# Example: "smtp.gmail.com" or "mail.example.com"
SMTP_HOST=""

# SMTP server port
# Common ports: 587 (TLS), 465 (SSL), 25 (unencrypted)
SMTP_PORT="587"

# SMTP username (usually your email address)
SMTP_USER=""

# SMTP password or app-specific password
SMTP_PASSWORD=""

# Use TLS encryption for SMTP connection
# Options: "true" or "false"
# Recommended: "true" for security
SMTP_USE_TLS="true"

# ==============================================================================
# NOTES
# ==============================================================================
#
# S3-Compatible Storage Providers:
# - AWS S3: Use standard AWS credentials and bucket names
# - DigitalOcean Spaces: Set AWS_PROFILE with Spaces credentials
# - MinIO: Configure endpoint in AWS CLI config
# - Other S3-compatible: Configure via AWS CLI config file
#
# For detailed configuration examples, see DOCUMENTATION.md
#
