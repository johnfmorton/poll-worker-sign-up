# Implementation Plan

- [x] 1. Set up project structure and shared library
  - Create `backup_scripts/` directory in project root
  - Create `shared.sh` with common functions for configuration loading, logging, and notifications
  - Create `example.env.sh` with all configuration variables documented
  - Add `.gitignore` entry for `.env.sh` to prevent credential commits
  - _Requirements: 5.4_

- [x] 2. Implement configuration system
  - Write `load_config()` function to source `.env.sh` file
  - Write `validate_required_vars()` function to check required variables
  - Implement configuration file existence check with helpful error message
  - Add validation for email notification mode values
  - _Requirements: 5.1, 5.2, 5.3_

- [x] 2.1 Write property test for configuration validation
  - **Property 2: Configuration validation completeness**
  - **Validates: Requirements 5.2, 5.3**

- [x] 3. Implement logging and error handling
  - Write `log_message()` function with timestamp formatting
  - Write `exit_with_error()` function with error code support
  - Write `exit_with_success()` function
  - Implement error message formatting with context and suggestions
  - _Requirements: 1.4_

- [x] 4. Implement email notification system
  - Write `send_notification()` function with mode checking (SUCCESS_AND_FAILURE, FAILURE_ONLY, NEVER)
  - Implement email content generation with script name, status, timestamp, and error details
  - Implement SMTP email sending using curl or sendmail
  - Add error handling for notification failures that doesn't affect script exit code
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

- [ ]* 4.1 Write property test for notification mode compliance
  - **Property 7: Email notification mode compliance**
  - **Validates: Requirements 4.1, 4.2, 4.3**

- [ ]* 4.2 Write property test for notification content completeness
  - **Property 10: Email notification content completeness**
  - **Validates: Requirements 4.4**

- [x] 5. Implement backup_db.sh script, remembering to use DDEV containers for the work
  - Create script file with proper shebang and set options (`set -euo pipefail`)
  - Source shared.sh and load configuration
  - Validate database-specific required variables
  - Implement backup directory creation if it doesn't exist
  - Generate timestamped filename using format `backup_YYYYMMDD_HHMMSS.sql.gz`
  - Execute mysqldump with credentials from configuration
  - Compress output with gzip
  - Call notification system based on result
  - Exit with appropriate status code
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [ ]* 5.1 Write property test for backup file creation
  - **Property 1: Backup file creation consistency**
  - **Validates: Requirements 1.1, 1.3**

- [ ]* 5.2 Write property test for backup directory creation
  - **Property 9: Backup directory creation**
  - **Validates: Requirements 1.5**

- [ ]* 5.3 Write property test for exit code consistency
  - **Property 8: Exit code consistency**
  - **Validates: Requirements 1.3, 1.4**

- [x] 6. Implement restore_db.sh script, remembering to use DDEV containers for the work
  - Create script file with proper shebang and set options
  - Source shared.sh and load configuration
  - Validate database-specific required variables
  - Implement argument parsing for optional backup file parameter
  - Implement logic to find most recent backup if no argument provided
  - Validate backup file exists with helpful error message
  - Check if backup directory is empty with appropriate error
  - Decompress backup file if gzipped
  - Execute mysql to restore database
  - Call notification system based on result
  - Exit with appropriate status code
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [ ]* 6.1 Write property test for restore file selection
  - **Property 3: Restore file selection correctness**
  - **Validates: Requirements 2.1**

- [ ]* 6.2 Write property test for restore file argument handling
  - **Property 4: Restore file argument handling**
  - **Validates: Requirements 2.2, 2.3**

- [x] 7. Implement sync_backups_to_s3.sh script, remembering to use DDEV containers for the work
  - Create script file with proper shebang and set options
  - Source shared.sh and load configuration
  - Validate S3-specific required variables
  - Check if AWS CLI is installed with helpful error message and installation link
  - Build aws s3 sync command with backup directory and S3 path
  - Add --delete flag conditionally based on REMOTE_S3_DELETE configuration
  - Add --profile flag if AWS_PROFILE is configured
  - Execute aws s3 sync command
  - Handle S3 connection errors with descriptive messages
  - Call notification system based on result
  - Exit with appropriate status code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 7.1 Write property test for AWS CLI dependency check
  - **Property 6: AWS CLI dependency check**
  - **Validates: Requirements 3.2**

- [ ]* 7.2 Write property test for S3 delete flag consistency
  - **Property 5: S3 delete flag consistency**
  - **Validates: Requirements 3.3, 3.4**

- [x] 8. Implement help.sh script, remembering to use DDEV containers for the work
  - Create script file with formatted help output
  - Display available scripts with descriptions
  - Show usage examples for each script
  - Document configuration variables with descriptions
  - Explain email notification modes
  - Provide cron job examples
  - Reference DOCUMENTATION.md for detailed information
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [x] 9. Create comprehensive documentation
  - Write DOCUMENTATION.md with introduction and overview
  - Document prerequisites and system requirements
  - Provide installation instructions with file permissions
  - Write detailed configuration guide for all variables
  - Include usage examples for manual execution
  - Provide cron job setup examples with recommended schedules
  - Document email notification configuration and testing
  - Add troubleshooting section with common errors
  - Include S3-compatible storage examples (AWS S3, DigitalOcean Spaces, MinIO)
  - Document security best practices
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

- [x] 10. Set file permissions and finalize
  - Set executable permissions on all .sh scripts (chmod +x)
  - Set restrictive permissions on example.env.sh (chmod 644)
  - Add README note about setting .env.sh to 600 after creation
  - Verify all scripts have proper shebang lines
  - Test that scripts fail gracefully when .env.sh is missing

- [ ]* 11. Write integration tests
  - Test full backup-restore cycle with test database
  - Test backup-sync-restore cycle with S3
  - Test cron job simulation with email notifications
  - Test error scenarios (missing config, missing dependencies, invalid credentials)

- [x] 12. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
