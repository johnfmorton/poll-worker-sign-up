#!/usr/bin/env bash

# restore_db.sh - Restore database from backup file
# This script restores a database from a SQL dump backup file.
# It uses DDEV containers for database operations.

set -euo pipefail

# Get script directory and name
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPT_NAME="$(basename "${BASH_SOURCE[0]}")"

# Source shared functions
# shellcheck source=./shared.sh
source "${SCRIPT_DIR}/shared.sh"

# Function to find the most recent backup file
find_most_recent_backup() {
    local backup_dir="$1"
    
    # Find the most recent backup file (sorted by modification time)
    local most_recent
    most_recent=$(find "${backup_dir}" -name "backup_*.sql.gz" -o -name "backup_*.sql" 2>/dev/null | sort -r | head -n 1)
    
    if [[ -z "${most_recent}" ]]; then
        return 1
    fi
    
    echo "${most_recent}"
    return 0
}

# Main restore function
main() {
    log_message "Starting database restore..."
    
    # Load configuration
    load_config
    
    # Validate required variables for database restore
    validate_required_vars \
        "SITE_NAME" \
        "LOCAL_DB_NAME" \
        "LOCAL_DB_USER" \
        "LOCAL_DB_PASSWORD" \
        "LOCAL_DB_HOST" \
        "BACKUP_DIR"
    
    # Determine which backup file to use
    local backup_file=""
    
    if [[ $# -eq 0 ]]; then
        # No argument provided - find most recent backup
        log_message "No backup file specified, searching for most recent backup..."
        
        # Check if backup directory exists
        if [[ ! -d "${BACKUP_DIR}" ]]; then
            exit_with_error "${SCRIPT_NAME}" "Backup directory does not exist: ${BACKUP_DIR}" 4
        fi
        
        # Find most recent backup
        if ! backup_file=$(find_most_recent_backup "${BACKUP_DIR}"); then
            exit_with_error "${SCRIPT_NAME}" "No backup files found in ${BACKUP_DIR}. Please create a backup first using backup_db.sh" 4
        fi
        
        log_message "Using most recent backup: ${backup_file}"
    else
        # Argument provided - use specified file
        backup_file="$1"
        log_message "Using specified backup file: ${backup_file}"
    fi
    
    # Validate backup file exists
    if [[ ! -f "${backup_file}" ]]; then
        exit_with_error "${SCRIPT_NAME}" "Backup file not found: ${backup_file}" 4
    fi
    
    # Validate backup file is not empty
    if [[ ! -s "${backup_file}" ]]; then
        exit_with_error "${SCRIPT_NAME}" "Backup file is empty: ${backup_file}" 4
    fi
    
    # Determine if file is gzipped
    local is_gzipped=false
    if [[ "${backup_file}" == *.gz ]]; then
        is_gzipped=true
        log_message "Backup file is gzipped, will decompress during restore"
    fi
    
    # Get database port
    local db_port="${LOCAL_DB_PORT:-3306}"
    
    # Execute restore
    log_message "Restoring database ${LOCAL_DB_NAME}..."
    
    if [[ "${is_gzipped}" == true ]]; then
        # Decompress and pipe to mysql
        if ! gunzip -c "${backup_file}" | ddev exec mysql \
            --host="${LOCAL_DB_HOST}" \
            --port="${db_port}" \
            --user="${LOCAL_DB_USER}" \
            --password="${LOCAL_DB_PASSWORD}" \
            "${LOCAL_DB_NAME}"; then
            exit_with_error "${SCRIPT_NAME}" "Database restore failed for ${LOCAL_DB_NAME}" 3
        fi
    else
        # Pipe uncompressed file to mysql
        if ! ddev exec mysql \
            --host="${LOCAL_DB_HOST}" \
            --port="${db_port}" \
            --user="${LOCAL_DB_USER}" \
            --password="${LOCAL_DB_PASSWORD}" \
            "${LOCAL_DB_NAME}" < "${backup_file}"; then
            exit_with_error "${SCRIPT_NAME}" "Database restore failed for ${LOCAL_DB_NAME}" 3
        fi
    fi
    
    # Success!
    exit_with_success "${SCRIPT_NAME}" "Database restore completed successfully from: ${backup_file}"
}

# Run main function
main "$@"
