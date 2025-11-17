<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('street_address', 500);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_token', 64)->nullable()->unique();
            $table->timestamp('verification_token_expires_at')->nullable();
            $table->enum('residency_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('residency_validated_at')->nullable();
            $table->foreignId('residency_validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('party_affiliation', ['democrat', 'republican', 'independent', 'unaffiliated'])->nullable();
            $table->timestamp('party_assigned_at')->nullable();
            $table->foreignId('party_assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('email');
            $table->index('verification_token');
            $table->index('residency_status');
            $table->index('party_affiliation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
