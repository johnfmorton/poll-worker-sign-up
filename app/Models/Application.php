<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'street_address',
        'email_verified_at',
        'verification_token',
        'verification_token_expires_at',
        'residency_status',
        'residency_validated_at',
        'residency_validated_by',
        'party_affiliation',
        'party_assigned_at',
        'party_assigned_by',
        'user_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_token_expires_at' => 'datetime',
        'residency_validated_at' => 'datetime',
        'party_assigned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function residencyValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'residency_validated_by');
    }

    public function partyAssigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'party_assigned_by');
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function isResidencyApproved(): bool
    {
        return $this->residency_status === 'approved';
    }

    public function hasPartyAssignment(): bool
    {
        return $this->party_affiliation !== null;
    }
}
