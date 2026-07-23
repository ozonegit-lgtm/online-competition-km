<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * จำนวนครั้งที่อนุญาตให้ Login ผิด
     * ก่อนถูกล็อกชั่วคราว
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * ระยะเวลาล็อกชั่วคราว หน่วยเป็นวินาที
     */
    private const LOCKOUT_SECONDS = 60;

    /**
     * แสดงหน้า Login
     */
    public function index(): View
    {
        return view('auth.login');
    }

    /**
     * ตรวจสอบและดำเนินการ Login
     */
    public function postLogin(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | ตรวจสอบข้อมูลจากแบบฟอร์ม
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'email' => [
                'required',
                'email',
                'max:150',
            ],
            'password' => [
                'required',
                'string',
            ],
        ]);

        $throttleKey = $this->throttleKey($request);

        /*
        |--------------------------------------------------------------------------
        | ตรวจสอบ Rate Limit
        |--------------------------------------------------------------------------
        */

        if (
            RateLimiter::tooManyAttempts(
                $throttleKey,
                self::MAX_ATTEMPTS
            )
        ) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->logAttempt(
                $request,
                null,
                'blocked_rate_limit'
            );

            throw ValidationException::withMessages([
                'email' => "พยายามเข้าสู่ระบบผิดหลายครั้งเกินไป กรุณาลองใหม่ในอีก {$seconds} วินาที",
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | เตรียมข้อมูล Login
        |--------------------------------------------------------------------------
        */

        $email = Str::lower(
            trim((string) $request->input('email'))
        );

        $password = (string) $request->input('password');

        /*
        |--------------------------------------------------------------------------
        | ค้นหาผู้ใช้งาน
        |--------------------------------------------------------------------------
        */

        $user = User::where('email', $email)->first();

        /*
        |--------------------------------------------------------------------------
        | ตรวจสอบ Password
        |--------------------------------------------------------------------------
        |
        | ใช้ Dummy Hash ในกรณีไม่พบ User หรือข้อมูล Password ในฐานข้อมูล
        | ไม่ใช่ Bcrypt เพื่อป้องกัน Timing Attack และป้องกัน RuntimeException
        | หากมีการเพิ่มข้อมูลผ่าน phpMyAdmin โดยตรง
        |
        */

        $dummyHash =
            '$2y$12$eImiTXuWVxfM37uY4JANjQhZUFBQQV9wq2LmZZ7YXsA9U3f2E1CkK';

        $storedPassword = $user?->password;

        $passwordUsesBcrypt = $storedPassword !== null
            && Hash::info($storedPassword)['algoName'] === 'bcrypt';

        $passwordHash = $passwordUsesBcrypt
            ? $storedPassword
            : $dummyHash;

        $passwordOk = Hash::check(
            $password,
            $passwordHash
        );

        $passwordOk = $passwordOk
            && $user !== null
            && $passwordUsesBcrypt;

        /*
        |--------------------------------------------------------------------------
        | ไม่พบ User หรือ Password ไม่ถูกต้อง
        |--------------------------------------------------------------------------
        */

        if (!$user || !$passwordOk) {
            RateLimiter::hit(
                $throttleKey,
                self::LOCKOUT_SECONDS
            );

            $reason = !$user
                ? 'user_not_found'
                : 'invalid_password';

            $this->logAttempt(
                $request,
                $user,
                $reason
            );
            return redirect()->route('login')->withInput(['email' => $email,])->with('error','อีเมลหรือรหัสผ่านไม่ถูกต้อง');
        }
        /*
        |--------------------------------------------------------------------------
        | Password ถูกต้อง แต่บัญชีถูกระงับ
        |--------------------------------------------------------------------------
        */
        if (!(bool) $user->is_active) {
            /*
             * รหัสผ่านถูกต้องแล้ว
             * จึงไม่ควรนับเป็นความพยายาม Login ผิด
             */
            RateLimiter::clear($throttleKey);

            $this->logAttempt(
                $request,
                $user,
                'inactive_account'
                );

            return redirect()->route('login')->withInput(['email' => $email,])->with('error','บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
        }

        /*
        |--------------------------------------------------------------------------
        | Login สำเร็จ
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear($throttleKey);
        Auth::login($user,$request->boolean('remember'));
        /*
         * ป้องกัน Session Fixation
         */
        $request->session()->regenerate();

        /*
         * บันทึกวันและเวลาที่ Login ล่าสุด
         */
        $user->update([
            'last_login_at' => now(),
        ]);

        $this->logAttempt(
            $request,
            $user,
            'success'
        );
        return redirect()->intended(route('dashboard'))->with('success','เข้าสู่ระบบสำเร็จ');
    }

    /**
     * ออกจากระบบ
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::logout();

        /*
         * ทำลาย Session เดิมและสร้าง CSRF Token ใหม่
         */
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user) {
            $this->logAttempt(
                $request,
                $user,
                'logout'
            );
        }

        return redirect()->route('login')->with('success','ออกจากระบบเรียบร้อย');
    }

    /**
     * สร้าง Key สำหรับ Rate Limiter
     * จาก Email และ IP Address
     */
    private function throttleKey(Request $request): string
    {
        $email = Str::lower(
            trim((string) $request->input('email'))
        );

        return $email . '|' . $request->ip();
    }

    /**
     * บันทึกประวัติการ Login ลง Activity Logs
     */
    private function logAttempt(
        Request $request,
        ?User $user,
        string $action
    ): void {
        try {
            $email = Str::lower(
                trim((string) $request->input('email'))
            );

            /*
             * ตอน Logout ไม่มี Email อยู่ใน Request
             * จึงใช้ Email ของ User แทน
             */
            if ($email === '' && $user) {
                $email = $user->email;
            }

            DB::table('activity_logs')->insert([
                'user_id' => $user?->id,
                'module' => 'auth',
                'action' => $action,
                'model_type' => User::class,
                'model_id' => $user?->id,
                'description' =>
                    "Login activity: {$action} (email: {$email})",
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            /*
             * หากบันทึก Activity Log ไม่สำเร็จ
             * ห้ามทำให้กระบวนการ Login ล้มเหลว
             */
            Log::warning(
                'Failed to write activity log',
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}