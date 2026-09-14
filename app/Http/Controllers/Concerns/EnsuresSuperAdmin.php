<?php

namespace App\Http\Controllers\Concerns;

trait EnsuresSuperAdmin
{
    private function ensureSuperAdmin(): void
    {
        $user = auth()->user();
        abort_unless(
            $user && $user->is_active && $user->role?->role_name === 'Super Admin',
            403
        );
    }
}
