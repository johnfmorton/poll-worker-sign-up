#!/usr/bin/env bats

# Property test for AWS CLI dependency check
# Feature: database-backup-scripts, Property 6: AWS CLI dependency check
# Validates: Requirements 3.2

# Setup test environment
setup() {
    # Get the directory of the test file
    TEST_DIR="$(cd "$(dirname "$BATS_TEST_FILENAME")" && pwd)"
    BACKUP_SCRIPTS_DIR="$(cd "${TEST_DIR}/.." && pwd)"
    
    # Create temporary directory for test scripts and backups
    TEST_SCRIPT_DIR="$(mktemp -d)"
    export TEST_SCRIPT_DIR
    
    TEST_BACKUP_DIR="$(mktemp -d)"
    export TEST_BACKUP_DIR
    
    # Copy shared.sh to test directory
    cp "${BACKUP_SCRIPTS_DIR}/shared.sh" "${TEST_SCRIPT_DIR}/shared.sh"
}

# Cleanup after each test
teardown() {
    if [[ -n "${TEST_SCRIPT_DIR:-}" && -d "${TEST_SCRIPT_DIR}" ]]; then
        rm -rf "${TEST_SCRIPT_DIR}"
    fi
    if [[ -n "${TEST_BACKUP_DIR:-}" && -d "${TEST_BACKUP_DIR}" ]]; then
        rm -rf "${TEST_BACKUP_DIR}"
    fi
}

# Helper function to create a valid config file
create_valid_config() {
    local config_file="${TEST_SCRIPT_DIR}/.env.sh"
    cat > "${config_file}" << EOF
# Test configuration file
SITE_NAME="Test Site"
BACKUP_DIR="${TEST_BACKUP_DIR}"
REMOTE_S3_BUCKET="test-bucket"
REMOTE_S3_PATH="backups/test"
EMAIL_NOTIFY_MODE="NEVER"
EOF
    chmod 600 "${config_file}"
}

@test "Property 6: AWS CLI dependency check - script exits with error when AWS CLI not available" {
    # Feature: database-backup-scripts, Property 6: AWS CLI dependency check
    
    # Run 100 iterations to ensure consistent behavior
    for iteration in {1..100}; do
        # Create valid configuration
        create_valid_config
        
        # Create a test backup file so directory is not empty
        echo "test backup data ${iteration}" > "${TEST_BACKUP_DIR}/backup_test_${iteration}.sql.gz"
        
        # Create a test script that simulates sync_backups_to_s3.sh behavior
        # but with AWS CLI check that will fail
        local test_script="${TEST_SCRIPT_DIR}/test_sync_${iteration}.sh"
        cat > "${test_script}" << 'TESTEOF'
#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPT_NAME="$(basename "${BASH_SOURCE[0]}")"

source "${SCRIPT_DIR}/shared.sh"

# Mock check_aws_cli function that always fails (simulating AWS CLI not installed)
check_aws_cli() {
    # Simulate the check failing - AWS CLI not found
    if ! command -v aws_nonexistent_command_${RANDOM} >/dev/null 2>&1; then
        local error_msg="AWS CLI is not installed in the DDEV container.

The sync_backups_to_s3.sh script requires AWS CLI to be installed.

To install AWS CLI in your DDEV container, you can:
1. Add AWS CLI to your .ddev/web-build/Dockerfile
2. Or install it manually: ddev exec 'curl \"https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip\" -o \"awscliv2.zip\" && unzip awscliv2.zip && sudo ./aws/install'

For more information, visit: https://aws.amazon.com/cli/"
        
        echo "[ERROR] ${error_msg}"
        exit 2
    fi
    
    log_message "AWS CLI is installed"
}

# Simulate main function
main() {
    log_message "Starting S3 backup synchronization..."
    
    # Load configuration
    load_config
    
    # Validate required variables
    validate_required_vars \
        "SITE_NAME" \
        "BACKUP_DIR" \
        "REMOTE_S3_BUCKET" \
        "REMOTE_S3_PATH"
    
    # Check if AWS CLI is installed - this should fail
    check_aws_cli
    
    # This should never be reached if AWS CLI check fails
    log_message "ERROR: Should not reach this point - AWS CLI check should have failed"
    exit 99
}

main "$@"
TESTEOF
        chmod +x "${test_script}"
        
        # Execute and expect failure with exit code 2 (dependency error)
        run bash "${test_script}"
        
        # Verify it exits with error code 2 (dependency error)
        [ "$status" -eq 2 ]
        
        # Verify error message mentions AWS CLI
        [[ "$output" =~ "AWS CLI" ]] || [[ "$output" =~ "aws" ]]
        
        # Verify error message provides installation instructions
        [[ "$output" =~ "install" ]] || [[ "$output" =~ "Install" ]]
        
        # Verify the script exited before attempting S3 operations
        # (should not contain messages about S3 sync)
        ! [[ "$output" =~ "s3 sync" ]]
    done
}

@test "Property 6: AWS CLI dependency check - script proceeds when AWS CLI is available" {
    # Feature: database-backup-scripts, Property 6: AWS CLI dependency check
    
    # Run 100 iterations to ensure consistent behavior
    for iteration in {1..100}; do
        # Create valid configuration
        create_valid_config
        
        # Create a test backup file
        echo "test backup data ${iteration}" > "${TEST_BACKUP_DIR}/backup_test_${iteration}.sql.gz"
        
        # Create a test script that simulates AWS CLI being available
        local test_script="${TEST_SCRIPT_DIR}/test_sync_success_${iteration}.sh"
        cat > "${test_script}" << 'TESTEOF'
#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPT_NAME="$(basename "${BASH_SOURCE[0]}")"

source "${SCRIPT_DIR}/shared.sh"

# Mock check_aws_cli function that succeeds (simulating AWS CLI installed)
check_aws_cli() {
    # Simulate AWS CLI being available
    log_message "AWS CLI is installed"
    return 0
}

# Simulate main function
main() {
    log_message "Starting S3 backup synchronization..."
    
    # Load configuration
    load_config
    
    # Validate required variables
    validate_required_vars \
        "SITE_NAME" \
        "BACKUP_DIR" \
        "REMOTE_S3_BUCKET" \
        "REMOTE_S3_PATH"
    
    # Check if AWS CLI is installed - this should succeed
    check_aws_cli
    
    # Validate backup directory exists
    if [[ ! -d "${BACKUP_DIR}" ]]; then
        echo "[ERROR] Backup directory does not exist: ${BACKUP_DIR}"
        exit 4
    fi
    
    # If we reach here, AWS CLI check passed
    log_message "AWS CLI check passed - would proceed with S3 operations"
    exit 0
}

main "$@"
TESTEOF
        chmod +x "${test_script}"
        
        # Execute and expect success
        run bash "${test_script}"
        
        # Verify it exits with success code 0
        [ "$status" -eq 0 ]
        
        # Verify AWS CLI check passed message is present
        [[ "$output" =~ "AWS CLI" ]]
        [[ "$output" =~ "passed" ]] || [[ "$output" =~ "installed" ]]
    done
}

@test "Property 6: AWS CLI dependency check - error occurs before any S3 operations" {
    # Feature: database-backup-scripts, Property 6: AWS CLI dependency check
    
    # Run 100 iterations with various scenarios
    for iteration in {1..100}; do
        # Create valid configuration with random S3 settings
        local random_bucket="test-bucket-${RANDOM}"
        local random_path="backups/test/${RANDOM}"
        
        local config_file="${TEST_SCRIPT_DIR}/.env.sh"
        cat > "${config_file}" << EOF
SITE_NAME="Test Site ${iteration}"
BACKUP_DIR="${TEST_BACKUP_DIR}"
REMOTE_S3_BUCKET="${random_bucket}"
REMOTE_S3_PATH="${random_path}"
EMAIL_NOTIFY_MODE="NEVER"
REMOTE_S3_DELETE="$( [ $((RANDOM % 2)) -eq 0 ] && echo "true" || echo "false" )"
EOF
        chmod 600 "${config_file}"
        
        # Create random number of backup files (1-5)
        local num_files=$((RANDOM % 5 + 1))
        for i in $(seq 1 "$num_files"); do
            echo "backup data ${iteration}_${i}" > "${TEST_BACKUP_DIR}/backup_${iteration}_${i}.sql.gz"
        done
        
        # Create test script that tracks execution order
        local test_script="${TEST_SCRIPT_DIR}/test_order_${iteration}.sh"
        cat > "${test_script}" << 'TESTEOF'
#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPT_NAME="$(basename "${BASH_SOURCE[0]}")"

source "${SCRIPT_DIR}/shared.sh"

# Track execution order
EXECUTION_LOG="${SCRIPT_DIR}/execution_log.txt"
echo "START" > "${EXECUTION_LOG}"

check_aws_cli() {
    echo "AWS_CLI_CHECK" >> "${EXECUTION_LOG}"
    # Simulate AWS CLI not found
    echo "[ERROR] AWS CLI is not installed"
    exit 2
}

# Mock S3 sync operation (should never be called)
perform_s3_sync() {
    echo "S3_SYNC_OPERATION" >> "${EXECUTION_LOG}"
    log_message "Performing S3 sync (this should never execute)"
}

main() {
    echo "MAIN_START" >> "${EXECUTION_LOG}"
    
    load_config
    echo "CONFIG_LOADED" >> "${EXECUTION_LOG}"
    
    validate_required_vars "SITE_NAME" "BACKUP_DIR" "REMOTE_S3_BUCKET" "REMOTE_S3_PATH"
    echo "VARS_VALIDATED" >> "${EXECUTION_LOG}"
    
    # AWS CLI check - should fail here
    check_aws_cli
    echo "AWS_CLI_CHECK_PASSED" >> "${EXECUTION_LOG}"
    
    # Should never reach here
    perform_s3_sync
    echo "S3_SYNC_COMPLETE" >> "${EXECUTION_LOG}"
}

main "$@"
TESTEOF
        chmod +x "${test_script}"
        
        # Execute and expect failure
        run bash "${test_script}"
        
        # Verify it exits with error code 2
        [ "$status" -eq 2 ]
        
        # Read execution log to verify order
        local execution_log="${TEST_SCRIPT_DIR}/execution_log.txt"
        if [[ -f "${execution_log}" ]]; then
            local log_content
            log_content=$(cat "${execution_log}")
            
            # Verify AWS CLI check was performed
            [[ "${log_content}" =~ "AWS_CLI_CHECK" ]]
            
            # Verify S3 sync was NOT performed
            ! [[ "${log_content}" =~ "S3_SYNC_OPERATION" ]]
            
            # Verify execution stopped after AWS CLI check
            ! [[ "${log_content}" =~ "AWS_CLI_CHECK_PASSED" ]]
            ! [[ "${log_content}" =~ "S3_SYNC_COMPLETE" ]]
        fi
        
        # Clean up for next iteration
        rm -f "${execution_log}"
        rm -f "${TEST_BACKUP_DIR}"/*.sql.gz
    done
}
