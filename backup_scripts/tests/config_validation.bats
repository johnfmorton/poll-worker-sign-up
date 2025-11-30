#!/usr/bin/env bats

# Property test for configuration validation
# Feature: database-backup-scripts, Property 2: Configuration validation completeness
# Validates: Requirements 5.2, 5.3

# Setup test environment
setup() {
    # Get the directory of the test file
    TEST_DIR="$(cd "$(dirname "$BATS_TEST_FILENAME")" && pwd)"
    BACKUP_SCRIPTS_DIR="$(cd "${TEST_DIR}/.." && pwd)"
    
    # Create temporary directory for test scripts
    TEST_SCRIPT_DIR="$(mktemp -d)"
    export TEST_SCRIPT_DIR
    
    # Copy shared.sh to test directory so SCRIPT_DIR resolves correctly
    cp "${BACKUP_SCRIPTS_DIR}/shared.sh" "${TEST_SCRIPT_DIR}/shared.sh"
}

# Cleanup after each test
teardown() {
    if [[ -n "${TEST_SCRIPT_DIR:-}" && -d "${TEST_SCRIPT_DIR}" ]]; then
        rm -rf "${TEST_SCRIPT_DIR}"
    fi
}

# Helper function to create a config file with specific variables
create_config() {
    local config_file="${TEST_SCRIPT_DIR}/.env.sh"
    cat > "${config_file}" << EOF
# Test configuration file
$1
EOF
    chmod 600 "${config_file}"
}

@test "Property 2: Configuration validation completeness - missing config file exits with error" {
    # Feature: database-backup-scripts, Property 2: Configuration validation completeness
    
    # Run 100 iterations with different scenarios
    for iteration in {1..100}; do
        # Ensure no config file exists
        rm -f "${TEST_SCRIPT_DIR}/.env.sh"
        
        # Create a test script that tries to load config
        local test_script="${TEST_SCRIPT_DIR}/test_load_${iteration}.sh"
        cat > "${test_script}" << 'TESTEOF'
#!/usr/bin/env bash
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${SCRIPT_DIR}/shared.sh"
load_config
TESTEOF
        chmod +x "${test_script}"
        
        # Execute and expect failure (exit code 1)
        run bash "${test_script}"
        
        # Verify it exits with error code 1 (configuration error)
        [ "$status" -eq 1 ]
        
        # Verify error message mentions missing config file
        [[ "$output" =~ "Configuration file not found" ]] || \
        [[ "$output" =~ "ERROR" ]]
    done
}

@test "Property 2: Configuration validation completeness - missing required variables exits with error" {
    # Feature: database-backup-scripts, Property 2: Configuration validation completeness
    
    # Run 100 iterations with different combinations of missing variables
    for iteration in {1..100}; do
        # Generate random number of required variables (1-10)
        local num_vars=$((RANDOM % 10 + 1))
        local required_vars=()
        
        # Generate variable names
        for i in $(seq 1 "$num_vars"); do
            required_vars+=("TEST_VAR_${iteration}_${i}")
        done
        
        # Randomly decide how many variables to provide (0 to num_vars-1)
        local num_provided=$((RANDOM % num_vars))
        
        # Build config content with only some variables
        local config_content=""
        if [ "$num_provided" -gt 0 ]; then
            for i in $(seq 1 "$num_provided"); do
                config_content+="TEST_VAR_${iteration}_${i}=\"value_${i}\"\n"
            done
        fi
        
        # Create config file
        echo -e "${config_content}" > "${TEST_SCRIPT_DIR}/.env.sh"
        chmod 600 "${TEST_SCRIPT_DIR}/.env.sh"
        
        # Create test script that validates required vars
        local test_script="${TEST_SCRIPT_DIR}/test_validate_${iteration}.sh"
        local vars_string="${required_vars[*]}"
        cat > "${test_script}" << TESTEOF
#!/usr/bin/env bash
SCRIPT_DIR="\$(cd "\$(dirname "\${BASH_SOURCE[0]}")" && pwd)"
source "\${SCRIPT_DIR}/shared.sh"
load_config
validate_required_vars ${vars_string}
TESTEOF
        chmod +x "${test_script}"
        
        # Execute and expect failure if not all variables are provided
        run bash "${test_script}"
        
        if [ "$num_provided" -lt "$num_vars" ]; then
            # Should fail with exit code 1 (configuration error)
            [ "$status" -eq 1 ]
            
            # Verify error message mentions missing variables
            [[ "$output" =~ "Missing required configuration variables" ]] || \
            [[ "$output" =~ "ERROR" ]]
        else
            # Should succeed if all variables provided
            [ "$status" -eq 0 ]
        fi
    done
}

@test "Property 2: Configuration validation completeness - invalid EMAIL_NOTIFY_MODE exits with error" {
    # Feature: database-backup-scripts, Property 2: Configuration validation completeness
    
    # Valid modes
    local valid_modes=("SUCCESS_AND_FAILURE" "FAILURE_ONLY" "NEVER")
    
    # Run 100 iterations with different invalid values
    for iteration in {1..100}; do
        # Generate random invalid mode value
        local invalid_modes=("INVALID_${RANDOM}" "always" "sometimes" "true" "false" "yes" "no" "1" "0")
        local invalid_mode="${invalid_modes[$((RANDOM % ${#invalid_modes[@]}))]}"
        
        # Skip if we accidentally generated a valid mode
        local is_valid=false
        for valid_mode in "${valid_modes[@]}"; do
            if [[ "${invalid_mode}" == "${valid_mode}" ]]; then
                is_valid=true
                break
            fi
        done
        
        if [[ "${is_valid}" == "true" ]]; then
            continue
        fi
        
        # Create config with invalid EMAIL_NOTIFY_MODE
        create_config "EMAIL_NOTIFY_MODE=\"${invalid_mode}\""
        
        # Create test script
        local test_script="${TEST_SCRIPT_DIR}/test_email_mode_${iteration}.sh"
        cat > "${test_script}" << 'TESTEOF'
#!/usr/bin/env bash
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${SCRIPT_DIR}/shared.sh"
load_config
validate_required_vars EMAIL_NOTIFY_MODE
TESTEOF
        chmod +x "${test_script}"
        
        # Execute and expect failure
        run bash "${test_script}"
        
        # Should fail with exit code 1 (configuration error)
        [ "$status" -eq 1 ]
        
        # Verify error message mentions invalid EMAIL_NOTIFY_MODE
        [[ "$output" =~ "Invalid EMAIL_NOTIFY_MODE" ]] || \
        [[ "$output" =~ "ERROR" ]]
    done
}

@test "Property 2: Configuration validation completeness - valid configuration succeeds" {
    # Feature: database-backup-scripts, Property 2: Configuration validation completeness
    
    # Run 100 iterations with valid configurations
    for iteration in {1..100}; do
        # Generate random number of variables (1-20)
        local num_vars=$((RANDOM % 20 + 1))
        
        # Build config content with all variables
        local config_content=""
        local required_vars=()
        
        for i in $(seq 1 "$num_vars"); do
            local var_name="TEST_VAR_${iteration}_${i}"
            local var_value="value_${RANDOM}"
            config_content+="${var_name}=\"${var_value}\"\n"
            required_vars+=("${var_name}")
        done
        
        # Randomly add a valid EMAIL_NOTIFY_MODE
        local valid_modes=("SUCCESS_AND_FAILURE" "FAILURE_ONLY" "NEVER")
        local mode="${valid_modes[$((RANDOM % 3))]}"
        config_content+="EMAIL_NOTIFY_MODE=\"${mode}\"\n"
        
        # Create config file
        echo -e "${config_content}" > "${TEST_SCRIPT_DIR}/.env.sh"
        chmod 600 "${TEST_SCRIPT_DIR}/.env.sh"
        
        # Create test script with proper variable expansion
        local test_script="${TEST_SCRIPT_DIR}/test_valid_${iteration}.sh"
        local vars_string="${required_vars[*]}"
        cat > "${test_script}" << TESTEOF
#!/usr/bin/env bash
SCRIPT_DIR="\$(cd "\$(dirname "\${BASH_SOURCE[0]}")" && pwd)"
source "\${SCRIPT_DIR}/shared.sh"
load_config
validate_required_vars ${vars_string} EMAIL_NOTIFY_MODE
TESTEOF
        chmod +x "${test_script}"
        
        # Execute and expect success
        run bash "${test_script}"
        
        # Should succeed with exit code 0
        [ "$status" -eq 0 ]
    done
}
