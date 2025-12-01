#!/usr/bin/env bash

# shared.sh - Common functions for database backup scripts
# This file provides shared functionality for configuration loading,
# logging, error handling, and email notifications.

set -euo pipefail

# Script directory (absolute path)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Configuration file path
CONFIG_FILE="${SCRIPT_DIR}/.env.sh"

# Log message with timestamp
# Usage: log_message "message"
log_message() {
    local message="$1"
    local timestamp
    timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[${timestamp}] ${message}"
}

# Load configuration from .env.sh file
# Usage: load_config
load_config() {
    if [[ ! -f "${CONFIG_FILE}" ]]; then
        echo "[ERROR] Configuration file not found: ${CONFIG_FILE}"
        echo "Please create .env.sh from example.env.sh and configure it."
        echo "  cp ${SCRIPT_DIR}/example.env.sh ${SCRIPT_DIR}/.env.sh"
        echo "  chmod 600 ${SCRIPT_DIR}/.env.sh"
        echo "  # Edit .env.sh with your configuration"
        exit 1
    fi
    
    # Source the configuration file
    # shellcheck source=/dev/null
    source "${CONFIG_FILE}"
    
    log_message "Configuration loaded from ${CONFIG_FILE}"
}

# Validate that required environment variables are set
# Usage: validate_required_vars "VAR1" "VAR2" "VAR3"
validate_required_vars() {
    local missing_vars=()
    
    for var_name in "$@"; do
        if [[ -z "${!var_name:-}" ]]; then
            missing_vars+=("${var_name}")
        fi
    done
    
    if [[ ${#missing_vars[@]} -gt 0 ]]; then
        echo "[ERROR] Missing required configuration variables:"
        for var in "${missing_vars[@]}"; do
            echo "  - ${var}"
        done
        echo ""
        echo "Please set these variables in ${CONFIG_FILE}"
        exit 1
    fi
    
    # Validate EMAIL_NOTIFY_MODE if set
    if [[ -n "${EMAIL_NOTIFY_MODE:-}" ]]; then
        local valid_modes=("SUCCESS_AND_FAILURE" "FAILURE_ONLY" "NEVER")
        local mode_valid=false
        
        for valid_mode in "${valid_modes[@]}"; do
            if [[ "${EMAIL_NOTIFY_MODE}" == "${valid_mode}" ]]; then
                mode_valid=true
                break
            fi
        done
        
        if [[ "${mode_valid}" == "false" ]]; then
            echo "[ERROR] Invalid EMAIL_NOTIFY_MODE: ${EMAIL_NOTIFY_MODE}"
            echo "Valid values are: SUCCESS_AND_FAILURE, FAILURE_ONLY, NEVER"
            echo ""
            echo "Please correct this value in ${CONFIG_FILE}"
            exit 1
        fi
    fi
}

# Send email notification based on configuration
# Usage: send_notification "script_name" "status" "message" ["error_details"]
send_notification() {
    local script_name="$1"
    local status="$2"
    local message="$3"
    local error_details="${4:-}"
    
    # Check if notifications are enabled
    if [[ "${EMAIL_NOTIFY_MODE:-NEVER}" == "NEVER" ]]; then
        return 0
    fi
    
    # Check if we should send notification based on status and mode
    if [[ "${EMAIL_NOTIFY_MODE}" == "FAILURE_ONLY" && "${status}" == "SUCCESS" ]]; then
        return 0
    fi
    
    # Validate email configuration if we're sending
    local email_vars=("EMAIL_TO" "EMAIL_FROM" "SMTP_HOST" "SMTP_USER" "SMTP_PASSWORD")
    for var in "${email_vars[@]}"; do
        if [[ -z "${!var:-}" ]]; then
            log_message "WARNING: Email notification skipped - ${var} not configured"
            return 0
        fi
    done
    
    # Generate email content
    local timestamp
    timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    local hostname
    hostname=$(hostname)
    local subject="[${status}] ${SITE_NAME:-Unknown Site} - ${script_name} - ${timestamp}"
    
    local email_body
    email_body="Site: ${SITE_NAME:-Unknown Site}
Script: ${script_name}
Status: ${status}
Timestamp: ${timestamp}
Host: ${hostname}

${message}"
    
    if [[ -n "${error_details}" ]]; then
        email_body="${email_body}

Error Details:
${error_details}"
    fi
    
    # Create temporary file for email content
    local email_file
    email_file=$(mktemp)
    
    cat > "${email_file}" << EOF
From: ${EMAIL_FROM}
To: ${EMAIL_TO}
Subject: ${subject}

${email_body}
EOF
    
    # Send email using curl with SMTP
    local smtp_url="smtp://${SMTP_HOST}:${SMTP_PORT:-587}"
    local curl_opts=()
    
    if [[ "${SMTP_USE_TLS:-true}" == "true" ]]; then
        curl_opts+=("--ssl-reqd")
    fi
    
    if curl "${curl_opts[@]}" \
        --url "${smtp_url}" \
        --mail-from "${EMAIL_FROM}" \
        --mail-rcpt "${EMAIL_TO}" \
        --user "${SMTP_USER}:${SMTP_PASSWORD}" \
        --upload-file "${email_file}" \
        --silent \
        --show-error 2>&1; then
        log_message "Email notification sent to ${EMAIL_TO}"
    else
        log_message "WARNING: Failed to send email notification (continuing anyway)"
    fi
    
    # Clean up temporary file
    rm -f "${email_file}"
}

# Exit with error status, log error, and send notification
# Usage: exit_with_error "script_name" "error_message" [exit_code]
exit_with_error() {
    local script_name="$1"
    local error_message="$2"
    local exit_code="${3:-3}"
    
    log_message "ERROR: ${error_message}"
    
    # Send failure notification
    send_notification "${script_name}" "FAILURE" "Script execution failed" "${error_message}"
    
    exit "${exit_code}"
}

# Exit with success status, log success, and send notification
# Usage: exit_with_success "script_name" "success_message"
exit_with_success() {
    local script_name="$1"
    local success_message="$2"
    
    log_message "SUCCESS: ${success_message}"
    
    # Send success notification
    send_notification "${script_name}" "SUCCESS" "${success_message}"
    
    exit 0
}

# Clean up old backup files based on retention count
# Usage: cleanup_old_backups
cleanup_old_backups() {
    local retention_count="${BACKUP_RETENTION_COUNT:-0}"
    
    # Skip cleanup if retention count is 0 or not set
    if [[ "${retention_count}" -eq 0 ]]; then
        log_message "Backup retention disabled (BACKUP_RETENTION_COUNT=0), skipping cleanup"
        return 0
    fi
    
    # Validate retention count is a positive integer
    if ! [[ "${retention_count}" =~ ^[0-9]+$ ]] || [[ "${retention_count}" -lt 1 ]]; then
        log_message "WARNING: Invalid BACKUP_RETENTION_COUNT value '${retention_count}', skipping cleanup"
        return 0
    fi
    
    # Count existing backup files
    local backup_count
    backup_count=$(find "${BACKUP_DIR}" -name "backup_*.sql.gz" -type f 2>/dev/null | wc -l | tr -d ' ')
    
    if [[ "${backup_count}" -le "${retention_count}" ]]; then
        log_message "Current backup count (${backup_count}) is within retention limit (${retention_count}), no cleanup needed"
        return 0
    fi
    
    # Calculate how many files to delete
    local files_to_delete=$((backup_count - retention_count))
    
    log_message "Found ${backup_count} backups, retention limit is ${retention_count}, removing ${files_to_delete} old backup(s)"
    
    # Find and delete oldest backup files
    # Sort by filename (which includes timestamp) and delete the oldest ones
    # Using portable approach that works on both GNU and BSD (macOS) systems
    find "${BACKUP_DIR}" -name "backup_*.sql.gz" -type f 2>/dev/null | \
        sort | \
        head -n "${files_to_delete}" | \
        while IFS= read -r file; do
            log_message "Deleting old backup: $(basename "${file}")"
            rm -f "${file}"
        done
    
    log_message "Cleanup complete, ${retention_count} backup(s) retained"
}
