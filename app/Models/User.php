<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * User role constants
     */
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_MODERATOR = 'moderator';
    const ROLE_USER = 'user';

    /**
     * User status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_PENDING = 'pending';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'avatar',
        'timezone',
        'last_login_at',
        'login_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'terms_accepted_at' => 'datetime',
        'privacy_accepted_at' => 'datetime',
        'last_login_at' => 'datetime',
        'login_count' => 'integer',
    ];

    protected $attributes = [
        'role' => self::ROLE_USER,
        'status' => self::STATUS_ACTIVE,
        'login_count' => 0,
    ];

    // ========================================
    // ADMIN & ROLE METHODS
    // ========================================

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user is an admin (admin or super admin)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Check if user is a moderator or higher
     */
    public function isModerator(): bool
    {
        return in_array($this->role, [self::ROLE_MODERATOR, self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Check if user is a regular user
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Get user role display name
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_USER => 'User',
            default => 'Unknown'
        };
    }

    /**
     * Get user role badge color
     */
    public function getRoleBadgeColor(): string
    {
        return match($this->role) {
            self::ROLE_SUPER_ADMIN => 'danger',
            self::ROLE_ADMIN => 'warning',
            self::ROLE_MODERATOR => 'info',
            self::ROLE_USER => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get all available roles
     */
    public static function getAllRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_USER => 'User',
        ];
    }

    // ========================================
    // STATUS METHODS
    // ========================================

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Check if user is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SUSPENDED => 'Suspended',
            self::STATUS_PENDING => 'Pending',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            self::STATUS_SUSPENDED => 'danger',
            self::STATUS_PENDING => 'warning',
            default => 'dark'
        };
    }

    /**
     * Activate user
     */
    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Deactivate user
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => self::STATUS_INACTIVE]);
    }

    /**
     * Suspend user
     */
    public function suspend(): bool
    {
        return $this->update(['status' => self::STATUS_SUSPENDED]);
    }

    // ========================================
    // LEGAL DOCUMENTS METHODS
    // ========================================

    /**
     * Check if user has accepted terms of service
     */
    public function hasAcceptedTerms(): bool
    {
        return !is_null($this->terms_accepted_at);
    }

    /**
     * Check if user has accepted privacy policy
     */
    public function hasAcceptedPrivacy(): bool
    {
        return !is_null($this->privacy_accepted_at);
    }

    /**
     * Check if user has accepted all legal documents
     */
    public function hasAcceptedAllLegal(): bool
    {
        return $this->hasAcceptedTerms() && $this->hasAcceptedPrivacy();
    }

    /**
     * Get the date when user accepted terms
     */
    public function getTermsAcceptedDate(): ?string
    {
        return $this->terms_accepted_at ? $this->terms_accepted_at->format('F d, Y \a\t g:i A') : null;
    }

    /**
     * Get the date when user accepted privacy policy
     */
    public function getPrivacyAcceptedDate(): ?string
    {
        return $this->privacy_accepted_at ? $this->privacy_accepted_at->format('F d, Y \a\t g:i A') : null;
    }

    /**
     * Accept terms of service
     */
    public function acceptTerms(): bool
    {
        return $this->update(['terms_accepted_at' => now()]);
    }

    /**
     * Accept privacy policy
     */
    public function acceptPrivacy(): bool
    {
        return $this->update(['privacy_accepted_at' => now()]);
    }

    // ========================================
    // LOGIN TRACKING METHODS
    // ========================================

    /**
     * Record user login
     */
    public function recordLogin(): bool
    {
        return $this->update([
            'last_login_at' => now(),
            'login_count' => $this->login_count + 1,
        ]);
    }

    /**
     * Get last login formatted
     */
    public function getLastLoginFormatted(): ?string
    {
        return $this->last_login_at ? $this->last_login_at->diffForHumans() : null;
    }

    /**
     * Check if user is online (logged in within last 15 minutes)
     */
    public function isOnline(): bool
    {
        return $this->last_login_at && $this->last_login_at->diffInMinutes() <= 15;
    }

    // ========================================
    // AVATAR & PROFILE METHODS
    // ========================================

    /**
     * Get user avatar URL
     */
    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }

        // Generate Gravatar fallback
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=150";
    }

    /**
     * Get user initials
     */
    public function getInitials(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    /**
     * Get user full name or email
     */
    public function getDisplayName(): string
    {
        return $this->name ?: $this->email;
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope for admin users
     */
    public function scopeAdmins($query)
    {
        return $query->whereIn('role', [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Scope for regular users
     */
    public function scopeUsers($query)
    {
        return $query->where('role', self::ROLE_USER);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for verified users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope for online users
     */
    public function scopeOnline($query)
    {
        return $query->where('last_login_at', '>=', now()->subMinutes(15));
    }

    /**
     * Scope for users who accepted legal documents
     */
    public function scopeAcceptedLegal($query)
    {
        return $query->whereNotNull('terms_accepted_at')
                    ->whereNotNull('privacy_accepted_at');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Check if user can access admin panel
     */
    public function canAccessAdmin(): bool
    {
        return $this->isAdmin() && $this->isActive() && $this->email_verified_at;
    }

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /**
     * Check if user can edit specific user
     */
    public function canEdit(User $user): bool
    {
        // Super admin can edit anyone
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin cannot edit super admin or other admins
        if ($this->isAdmin()) {
            return !$user->isAdmin();
        }

        // Users can only edit themselves
        return $this->id === $user->id;
    }

    /**
     * Check if user can delete specific user
     */
    public function canDelete(User $user): bool
    {
        // Cannot delete yourself
        if ($this->id === $user->id) {
            return false;
        }

        // Super admin can delete anyone except other super admins
        if ($this->isSuperAdmin()) {
            return !$user->isSuperAdmin();
        }

        // Admin can delete users and moderators only
        if ($this->isAdmin()) {
            return $user->isUser() || $user->isModerator();
        }

        return false;
    }

    /**
     * Get user timezone or default
     */
    public function getTimezone(): string
    {
        return $this->timezone ?: config('app.timezone', 'UTC');
    }

    /**
     * Convert time to user timezone
     */
    public function toUserTimezone($time): \Carbon\Carbon
    {
        return $time->setTimezone($this->getTimezone());
    }
}