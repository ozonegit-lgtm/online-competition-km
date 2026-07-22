<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * ตรวจสอบสิทธิ์การเข้าใช้งานตาม Role
     *
     * ตัวอย่างการใช้งาน
     *
     * Route::middleware(['auth', 'role:Super Admin'])
     *
     * Route::middleware(['auth', 'role:Competition Admin'])
     *
     * Route::middleware(['auth', 'role:Judge'])
     *
     * Route::middleware(['auth', 'role:Super Admin,Competition Admin'])
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {

        /**
         * ยังไม่ได้ Login
         */
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        /**
         * ไม่มีข้อมูล Role
         */
        if (!$user->role) {
            abort(403, 'Role not found.');
        }

        /**
         * Role ของผู้ใช้ปัจจุบัน
         */
        $currentRole = $user->role->role_name;

        /**
         * อนุญาตหลาย Role
         *
         * เช่น
         * role:Super Admin,Competition Admin
         */
        $allowedRoles = [];

        foreach ($roles as $role) {

            $allowedRoles = array_merge(
                $allowedRoles,
                array_map('trim', explode(',', $role))
            );

        }

        /**
         * ไม่มีสิทธิ์
         */
        if (!in_array($currentRole, $allowedRoles, true)) {

            abort(403, 'You do not have permission to access this page.');

        }

        /**
         * ผ่านการตรวจสอบ
         */
        return $next($request);
    }
}