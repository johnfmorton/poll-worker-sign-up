#!/usr/bin/env bash

# sync_backups_to_s3.sh - Synchronize local backups to S3-compatible storage
# This script uploads backup files from the local backup directory to S3.
# It uses DDEV containers for AWS CLI operations.

set -euo pipefail

# Get script directory and name
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPT_NAME="$(basename "${BASH_SOURCE[0]}")"

# Source shared functions
# shellcheck source=./shared.sh
source "${SCRIPT_DIR}/shared.sh"

# Check if AWS CLI is installed
check_aws_cli() {
    if ! ddev exec which aws >/dev/null 2>&1; then
        local error_msg="AWS CLI is not installed in the DDEV container.

The sync_backups_to_s3.sh script requires AWS CLI to be installed.

To install AWS CLI in your DDEV container, you can:
1. Add AWS CLI to your .ddev/web-build/Dockerfile
2. Or install it manually: ddev exec 'curl \"https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip\" -o \"awscliv2.zip\" && unzip awscliv2.zip && sudo ./aws/install'

For more information, visit: https://aws.amazon.com/cli/"
        
        exit_with_error "${SCRIPT_NAME}" "${error_msg}" 2
    fi
    
    log_message "AWS CLI is installed"
}

# Main sync function
main() {
    log_message "Starting S3 backup synchronization..."
    
    # Load configuration
    load_config
    
    # Validate required variables for S3 sync
    validate_required_vars \
        "SITE_NAME" \
        "BACKUP_DIR" \
        "REMOTE_S3_BUCKET" \
        "REMOTE_S3_PATH"
    
    # Check if AWS CLI is installed
    check_aws_cli
    
    # Validate backup directory exists
    if [[ ! -d "${BACKUP_DIR}" ]]; then
        exit_with_error "${SCRIPT_NAME}" "Backup directory does not exist: ${BACKUP_DIR}" 4
    fi
    
    # Check if backup directory has any files
    if [[ -z "$(ls -A "${BACKUP_DIR}" 2>/dev/null)" ]]; then
        log_message "WARNING: Backup directory is empty: ${BACKUP_DIR}"
        log_message "No files to synchronize"
        exit_with_success "${SCRIPT_NAME}" "No backup files to synchronize (backup directory is empty)"
    fi
    
    # Build S3 destination path
    local s3_destination="s3://${REMOTE_S3_BUCKET}/${REMOTE_S3_PATH}"
    log_message "S3 destination: ${s3_destination}"
    
    # Build aws s3 sync command
    local aws_cmd="aws s3 sync"
    local aws_args=("${BACKUP_DIR}" "${s3_destination}")
    
    # Add --delete flag if configured
    if [[ "${REMOTE_S3_DELETE:-false}" == "true" ]]; then
        log_message "Delete flag enabled: Remote files not in local directory will be deleted"
        aws_args+=("--delete")
    else
        log_message "Delete flag disabled: Remote files will be preserved"
    fi
    
    # Add --profile flag if configured
    if [[ -n "${AWS_PROFILE:-}" ]]; then
        log_message "Using AWS profile: ${AWS_PROFILE}"
        aws_args+=("--profile" "${AWS_PROFILE}")
    fi
    
    # Execute aws s3 sync command through DDEV
    log_message "Executing: ${aws_cmd} ${aws_args[*]}"
    
    local sync_output
    local sync_exit_code=0
    
    # Capture output and exit code
    if ! sync_output=$(ddev exec ${aws_cmd} "${aws_args[@]}" 2>&1); then
        sync_exit_code=$?
        
        # Check for common S3 connection errors
        if echo "${sync_output}" | grep -qi "could not connect\|connection\|timed out\|unable to locate credentials"; then
            local error_msg="Failed to connect to S3 storage.

Possible causes:
- Invalid AWS credentials
- Network connectivity issues
- Incorrect S3 bucket name or path
- Missing AWS credentials configuration

S3 Destination: ${s3_destination}
AWS Profile: ${AWS_PROFILE:-default}

Error output:
${sync_output}

Please verify:
1. AWS credentials are configured correctly
2. S3 bucket exists and is accessible
3. Network connection is available
4. Bucket permissions allow write access"
            
            exit_with_error "${SCRIPT_NAME}" "${error_msg}" 3
        else
            # Generic S3 sync error
            local error_msg="S3 synchronization failed.

S3 Destination: ${s3_destination}

Error output:
${sync_output}"
            
            exit_with_error "${SCRIPT_NAME}" "${error_msg}" 3
        fi
    fi
    
    # Log sync output
    if [[ -n "${sync_output}" ]]; then
        log_message "Sync output:"
        echo "${sync_output}"
    fi
    
    # Count files in backup directory for success message
    local file_count
    file_count=$(find "${BACKUP_DIR}" -type f | wc -l)
    
    # Success!
    exit_with_success "${SCRIPT_NAME}" "S3 synchronization completed successfully: ${file_count} file(s) synchronized to ${s3_destination}"
}

# Run main function
main "$@"
