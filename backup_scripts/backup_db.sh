#!/usr/bin/env bash

# backup_db.sh - Create timestamped database backup
# This script creates a compressed SQL dump of the database using mysqldump.
# It uses DDEV containers for database operations.

set -euo pipefail

# Get script directory and name
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPT_NAME="$(basename "${BASH_SOURCE[0]}")"

# Source shared functions
# shellcheck source=./shared.sh
source "${SCRIPT_DIR}/shared.sh"

# Main backup function
main() {
    log_message "Starting database backup..."
    
    # Load configuration
    load_config
    
    # Validate required variables for database backup
    validate_required_vars \
        "SITE_NAME" \
        "LOCAL_DB_NAME" \
        "LOCAL_DB_USER" \
        "LOCAL_DB_PASSWORD" \
        "LOCAL_DB_HOST" \
        "BACKUP_DIR"
    
    # Create backup directory if it doesn't exist
    if [[ ! -d "${BACKUP_DIR}" ]]; then
        log_message "Creating backup directory: ${BACKUP_DIR}"
        if ! mkdir -p "${BACKUP_DIR}"; then
            exit_with_error "${SCRIPT_NAME}" "Failed to create backup directory: ${BACKUP_DIR}" 3
        fi
    fi
    
    # Generate timestamped filename
    local timestamp
    timestamp=$(date '+%Y%m%d_%H%M%S')
    local backup_file="${BACKUP_DIR}/backup_${timestamp}.sql.gz"
    
    log_message "Backup file: ${backup_file}"
    
    # Execute mysqldump through DDEV and compress with gzip
    # Using DDEV to access the database container
    local db_port="${LOCAL_DB_PORT:-3306}"
    
    if ! ddev exec mysqldump \
        --host="${LOCAL_DB_HOST}" \
        --port="${db_port}" \
        --user="${LOCAL_DB_USER}" \
        --password="${LOCAL_DB_PASSWORD}" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --no-tablespaces \
        "${LOCAL_DB_NAME}" | gzip > "${backup_file}"; then
        exit_with_error "${SCRIPT_NAME}" "Database backup failed for ${LOCAL_DB_NAME}" 3
    fi
    
    # Verify backup file was created and has content
    if [[ ! -f "${backup_file}" ]]; then
        exit_with_error "${SCRIPT_NAME}" "Backup file was not created: ${backup_file}" 3
    fi
    
    if [[ ! -s "${backup_file}" ]]; then
        exit_with_error "${SCRIPT_NAME}" "Backup file is empty: ${backup_file}" 3
    fi
    
    # Get backup file size for logging
    local file_size
    file_size=$(du -h "${backup_file}" | cut -f1)
    
    # Success!
    exit_with_success "${SCRIPT_NAME}" "Database backup completed successfully: ${backup_file} (${file_size})"
}

# Run main function
main "$@"
