<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 *
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany roles()
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ==========================================
    // ROLE SYSTEM METHODS
    // ==========================================

    /**
     * Relationship: User belongsToMany Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('assigned_at', 'assigned_by', 'expires_at')
            ->withTimestamps();
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($roleName)
    {
        if (is_array($roleName)) {
            return $this->roles()->whereIn('name', $roleName)->exists();
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user has all the given roles
     */
    public function hasAllRoles(array $roles)
    {
        return $this->roles()->whereIn('name', $roles)->count() === count($roles);
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission)
    {
        return $this->roles()->where('is_active', true)
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->contains($permission);
    }

    /**
     * Assign role to user
     */
    public function assignRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && ! $this->hasRole($roleName)) {
            $this->roles()->attach($role->id, [
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]);
        }

        return $this;
    }

    /**
     * Remove role from user
     */
    public function removeRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }

        return $this;
    }

    /**
     * Sync user roles
     */
    public function syncRoles(array $roleNames)
    {
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->toArray();
        $this->roles()->sync($roleIds);

        return $this;
    }

    /**
     * Get all role names for the user
     */
    public function getRoleNamesAttribute()
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Check if user is admin (has admin or super_admin role)
     */
    public function isAdmin()
    {
        return $this->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }

    // ==========================================
    // AVATAR METHODS
    // ==========================================

    /**
     * Get avatar URL with proper path handling
     */
    public function getAvatarUrlAttribute()
    {
        \Log::info('Getting avatar URL', [
            'user_id' => $this->id,
            'avatar_path' => $this->avatar,
        ]);

        if ($this->avatar) {
            // Clean path - remove any 'storage/' prefix if exists
            $cleanPath = str_replace('storage/', '', $this->avatar);

            // Check if file exists in storage
            if (Storage::disk('public')->exists($cleanPath)) {
                $url = asset('storage/'.$cleanPath);
                \Log::info('Avatar URL generated', ['url' => $url]);

                return $url;
            } else {
                \Log::warning('Avatar file not found', [
                    'path' => $cleanPath,
                    'full_path' => storage_path('app/public/'.$cleanPath),
                ]);
            }
        }

        // Default avatar using UI Avatars service
        $defaultUrl = 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=6366f1&color=ffffff&size=200';
        \Log::info('Using default avatar', ['url' => $defaultUrl]);

        return $defaultUrl;
    }

    // ==========================================
    // EMAIL VERIFICATION METHODS
    // ==========================================

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get user's primary role (first role)
     */
    public function getPrimaryRole()
    {
        return $this->roles()->first();
    }

    /**
     * Check if user account is active
     */
    public function isActive()
    {
        return $this->is_active ?? true;
    }

    /**
     * Activate user account
     */
    public function activate()
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    /**
     * Deactivate user account
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);

        return $this;
    }
}
