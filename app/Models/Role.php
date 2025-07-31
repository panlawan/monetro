<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('assigned_at', 'assigned_by', 'expires_at')
            ->withTimestamps();
    }

    // Helper Methods
    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions);
    }

    public function addPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        if (! in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }

        return $this;
    }

    public function removePermission($permission)
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, function ($p) use ($permission) {
            return $p !== $permission;
        });
        $this->update(['permissions' => array_values($permissions)]);

        return $this;
    }
}
