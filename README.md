# Kiro + Laravel Skeleton Template

Welcome to the Kiro + Laravel Skeleton Template.

## Introduction

This repository is a fully-formed Laravel starter built from the experience of creating a real-world application with Kiro. Kiro provides a strong foundation for beginning any project, but  the steering documents and build-system structure included in this template provide a production-ready workspace to get your project started on the right foot. You don't have to fuss with the toolkit. You just start building.

You start your project with a complete development environment powered by DDEV, a working Vite setup with hot-module reloading, and a set of guiding documents that help shape conventions, workflows, and team collaboration from day one. Using this template saves at least an hour of initial setup time compared to assembling all of these pieces manually. It provides a consistent, fast, and opinionated starting point so you can focus on building features instead of wiring the project together.

## Features

* Laravel Ready: Comes pre-configured with a complete Laravel setup tailored for local development using DDEV.
* Vite Integration: Includes a Vite build process with hot module reloading, making front-end development smooth and efficient.
* Kiro Specs: Comes with highly tuned Kiro spec documents to ensure your code is human-readable and well-structured from the start.
* Makefile Included: Start your project simply by running make dev for an easy, no-fuss development experience.

## DDEV Requirements

Since the project uses DDEV for local enviroment of your Laravel project, you'll need to reference the [DDEV getting started section](https://ddev.com/get-started/) of the documenation. You'll find instructions for Mac, Windows and Linux. Basically, you'll need to be able to install Docker images, and, depending on your platform, a way for local URLs to resolve.

## Quick Start

1. **Clone the repo**: `git clone <https://github.com/johnfmorton/kiro-laravel-skeleton.git> your-project-name`
2. **Navigate to the directory**: `cd your-project-name`
3. **Start DDEV**: `ddev start`
4. **Run initial setup**: `make setup` (installs dependencies, generates app key, runs migrations, builds assets)
5. **Start development**: `make dev` (launches browser, runs migrations, starts Vite dev server)

That's it! Your Laravel app will be running at the URL shown by DDEV (typically `https://your-project-name.ddev.site`).

## Daily Development

After initial setup, just run:

```bash
ddev start    # Start the DDEV environment
make dev      # Launch your development environment
```

## Mail Configuration

This application uses Laravel's mail system for sending verification emails to poll worker applicants.

### Development Environment

By default, the application is configured to use the `log` mail driver, which writes emails to the log file instead of sending them. This is perfect for local development and testing.

To view emails in development:
1. Check the log file at `storage/logs/laravel.log`
2. Or use Laravel Pail: `ddev artisan pail`

### Production Setup

For production, you'll need to configure a real mail service. Update your `.env` file with one of the following options:

#### Option 1: SMTP (Generic)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="Poll Worker System"
```

#### Option 2: Mailtrap (Testing)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="Poll Worker System"
```

#### Option 3: Mailgun
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-api-key
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="Poll Worker System"
```

#### Option 4: Amazon SES
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="Poll Worker System"
```

### Queue Configuration

The application uses database-driven queues to send emails asynchronously. This prevents blocking the user interface while emails are being sent.

#### Running the Queue Worker

In development, the queue worker is automatically started when you run `make dev`.

For production, you should use a process manager like Supervisor to keep the queue worker running:

```bash
ddev artisan queue:work --tries=3 --timeout=90
```

#### Queue Management Commands

```bash
# View failed jobs
ddev artisan queue:failed

# Retry all failed jobs
ddev artisan queue:retry all

# Retry a specific failed job
ddev artisan queue:retry {job-id}

# Clear all failed jobs
ddev artisan queue:flush

# Monitor queue in real-time
ddev artisan queue:monitor
```

#### Production Queue Setup with Supervisor

Create a supervisor configuration file at `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/application/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/application/storage/logs/worker.log
stopwaitsecs=3600
```

Then reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Email Verification Flow

1. User submits registration form
2. Application creates application record and queues verification email
3. Queue worker processes the job and sends email
4. User clicks verification link in email
5. Application verifies email and creates user account

### Testing Email Configuration

To test your email configuration:

```bash
ddev artisan tinker
```

Then in the Tinker console:
```php
Mail::raw('Test email', function ($message) {
    $message->to('test@example.com')->subject('Test Email');
});
```

Check your mail service dashboard or logs to confirm the email was sent successfully.

## Contribution and License

This project is open source under the MIT License. We welcome contributions and suggestions!
