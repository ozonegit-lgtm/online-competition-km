<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * ส่งผู้ใช้งานไป Dashboard ตาม Role
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $roleName = $request->user()->role?->role_name;

        return match ($roleName) {
            'Super Admin' => redirect()->route('superadmin.dashboard'),

            'Competition Admin' => redirect()
                ->route('competition-admin.dashboard'),

            'Judge' => redirect()->route('judge.dashboard'),

            default => abort(403, 'ไม่พบสิทธิ์การใช้งานของบัญชีนี้'),
        };
    }
}