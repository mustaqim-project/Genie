<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasRolesAndPermissions
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->using(\App\Models\UserRole::class)
                    ->withTimestamps();
    }

    /**
     * Cek apakah user memiliki peran tertentu (bisa string atau array)
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return $this->roles->pluck('code')->intersect($roles)->isNotEmpty();
    }

    /**
     * Cek apakah user memiliki salah satu dari daftar peran
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Cek apakah user adalah Super Admin / Owner
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(['R-ADM', 'R-OWN']);
    }

    /**
     * Cek apakah user memiliki izin / permission tertentu
     */
    public function hasPermission(string $permissionCode): bool
    {
        // Admin & Owner bypass all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah user memiliki salah satu dari daftar permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $perm) {
            if ($this->hasPermission($perm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah user memiliki semua permissions yang diminta
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $perm) {
            if (!$this->hasPermission($perm)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Dapatkan semua permission unik yang dimiliki pengguna
     */
    public function allPermissions()
    {
        if ($this->isSuperAdmin()) {
            return Permission::all();
        }

        return $this->roles->flatMap(function ($role) {
            return $role->permissions;
        })->unique('id');
    }

    /**
     * Berikan peran ke pengguna
     */
    public function assignRole(Role|string $role): void
    {
        if (is_string($role)) {
            $role = Role::where('code', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    /**
     * Sinkronkan daftar peran pengguna
     */
    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }

    /**
     * Dapatkan nama peran utama untuk badge UI
     */
    public function getPrimaryRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }
}
