# User Management Commands

This document describes the Artisan commands available for creating and managing users in the application.

## Overview

The application provides two custom Artisan commands for creating users with different permission levels:

- `user:create-admin` - Create administrator users with full system access
- `user:create-pollworker` - Create pollworker users with standard access

## Commands

### Create Admin User

Creates a new administrator user with elevated privileges.

**Command:**
```bash
ddev artisan user:create-admin
```

**Options:**
- `--name` - The name of the admin user
- `--email` - The email address of the admin user
- `--password` - The password for the admin user

**Interactive Usage:**
```bash
ddev artisan user:create-admin
```

The command will prompt you for:
1. Name
2. Email address
3. Password (hidden input)

**Non-Interactive Usage:**
```bash
ddev artisan user:create-admin \
  --name="Admin User" \
  --email="admin@example.com" \
  --password="SecurePassword123"
```

**Example Output:**
```
Creating admin user...

 Name:
 > Admin User

 Email:
 > admin@example.com

 Password:
 > 

Admin user created successfully!
+----+------------+-------------------+-------+
| ID | Name       | Email             | Admin |
+----+------------+-------------------+-------+
| 1  | Admin User | admin@example.com | Yes   |
+----+------------+-------------------+-------+
```

### Create Pollworker User

Creates a new pollworker user with standard access privileges.

**Command:**
```bash
ddev artisan user:create-pollworker
```

**Options:**
- `--name` - The name of the pollworker
- `--email` - The email address of the pollworker
- `--password` - The password for the pollworker

**Interactive Usage:**
```bash
ddev artisan user:create-pollworker
```

The command will prompt you for:
1. Name
2. Email address
3. Password (hidden input)

**Non-Interactive Usage:**
```bash
ddev artisan user:create-pollworker \
  --name="Jane Smith" \
  --email="jane.smith@example.com" \
  --password="SecurePassword123"
```

**Example Output:**
```
Creating pollworker user...

 Name:
 > Jane Smith

 Email:
 > jane.smith@example.com

 Password:
 > 

Pollworker user created successfully!
+----+------------+-------------------------+-------+
| ID | Name       | Email                   | Admin |
+----+------------+-------------------------+-------+
| 2  | Jane Smith | jane.smith@example.com  | No    |
+----+------------+-------------------------+-------+
```

## Validation Rules

Both commands enforce the following validation rules:

### Name
- Required
- Must be a string
- Maximum 255 characters

### Email
- Required
- Must be a valid email format
- Must be unique (cannot already exist in the database)

### Password
- Required
- Must be a string
- Minimum 8 characters

## User Properties

### Admin Users
- `is_admin`: `true`
- `email_verified_at`: Set to current timestamp
- Full system access

### Pollworker Users
- `is_admin`: `false`
- `email_verified_at`: Set to current timestamp
- Standard access level

## Error Handling

If validation fails, the command will display error messages and exit with a failure status:

```
Validation failed:
  - The email has already been taken.
  - The password must be at least 8 characters.
```

## Security Considerations

1. **Password Input**: When using interactive mode, passwords are hidden during input for security
2. **Password Hashing**: All passwords are automatically hashed using Laravel's secure hashing before storage
3. **Email Verification**: Users are created with `email_verified_at` set, marking them as verified
4. **Unique Emails**: The system prevents duplicate email addresses

## Batch User Creation

For creating multiple users, you can use a shell script:

```bash
#!/bin/bash

# Create multiple pollworkers
ddev artisan user:create-pollworker --name="John Doe" --email="john@example.com" --password="SecurePass123"
ddev artisan user:create-pollworker --name="Jane Smith" --email="jane@example.com" --password="SecurePass123"
ddev artisan user:create-pollworker --name="Bob Johnson" --email="bob@example.com" --password="SecurePass123"
```

## Checking User Roles

To verify a user's admin status in your application code:

```php
if ($user->isAdmin()) {
    // User has admin privileges
}
```

## Related Files

- Command implementations: `app/Console/Commands/CreateAdminUser.php`, `app/Console/Commands/CreatePollworkerUser.php`
- User model: `app/Models/User.php`
- User migration: `database/migrations/0001_01_01_000000_create_users_table.php`

## Troubleshooting

### Command Not Found
If the commands don't appear, clear the application cache:
```bash
ddev artisan config:clear
ddev artisan cache:clear
```

### Database Connection Error
Ensure DDEV is running and the database is accessible:
```bash
ddev start
ddev artisan migrate
```

### Email Already Exists
If you receive a "The email has already been taken" error, the email address is already in use. Choose a different email or delete the existing user first.
