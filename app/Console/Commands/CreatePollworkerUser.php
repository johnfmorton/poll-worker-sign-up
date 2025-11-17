<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreatePollworkerUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-pollworker
                            {--name= : The name of the pollworker}
                            {--email= : The email address of the pollworker}
                            {--password= : The password for the pollworker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new pollworker user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating pollworker user...');
        $this->newLine();

        // Get user input
        $name = $this->option('name') ?? $this->ask('Name');
        $email = $this->option('email') ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Password');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("  - {$error}");
            }
            return self::FAILURE;
        }

        // Create pollworker user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->newLine();
        $this->info("Pollworker user created successfully!");
        $this->table(
            ['ID', 'Name', 'Email', 'Admin'],
            [[$user->id, $user->name, $user->email, 'No']]
        );

        return self::SUCCESS;
    }
}
