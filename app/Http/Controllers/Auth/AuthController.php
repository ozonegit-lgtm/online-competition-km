<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * จำนวนครั้งที่ยอมให้ล็อกอินผิดต่อ "1 ชุด key" (email+IP) ก่อนถูกล็อกชั่วคราว
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * ระยะเวลาล็อก (วินาที) หลังพยายามผิดครบจำนวน
     */
    private const LOCKOUT_SECONDS = 60;

    public function index(): View
    {
        return view('auth.login');
    }

    public function postLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email', 'max:150'],
            'password' => ['required', 'string',],
        ]);

        $throttleKey = $this->throttleKey($request);

        // 1) เช็ค rate limit ก่อนแตะฐานข้อมูลเลย
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->logAttempt($request, null, 'blocked_rate_limit');

            throw ValidationException::withMessages([
                'email' => "พยายามเข้าสู่ระบบผิดหลายครั้งเกินไป กรุณาลองใหม่ในอีก {$seconds} วินาที",
            ]);
        }

        $email    = $request->input('email');
        $password = $request->input('password');

        // ดึง user แบบไม่ผูกกับ Auth::attempt ตรงๆ เพื่อคุมทุกเงื่อนไข (status ฯลฯ) เอง
        $user = User::where('email', $email)->first();

        // 2) constant-time-ish behaviour: แม้ไม่เจอ user ก็ยัง hash เทียบ dummy
        //    เพื่อไม่ให้เวลาตอบสนองบอกใบ้ว่า email มีอยู่จริงหรือไม่ (กัน user enumeration ทาง timing)
        // hash "หลอก" ที่เป็น bcrypt รูปแบบถูกต้อง ใช้ตอนไม่พบ user เพื่อให้ Hash::check()
        // ยังคงใช้เวลาประมวลผลใกล้เคียงกับกรณีพบ user จริง (ลด timing side-channel)
        $dummyHash    = '$2y$12$eImiTXuWVxfM37uY4JANjQhZUFBQQV9wq2LmZZ7YXsA9U3f2E1CkK';
        $passwordHash = $user->password ?? $dummyHash;
        $passwordOk   = Hash::check($password, $passwordHash) && $user !== null;

        $isActive = $user && $user->status === 'active';

        if (!$user || !$passwordOk || !$isActive) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

            $reason = match (true) {
                !$user       => 'user_not_found',
                !$passwordOk => 'invalid_password',
                !$isActive   => 'inactive_account',
                default      => 'unknown',
            };
            $this->logAttempt($request, $user, $reason);

            // ข้อความเดียวกันทุกกรณี ไม่บอกว่า "ไม่พบอีเมล" หรือ "รหัสผ่านผิด" แยกกัน
            return redirect()
                ->route('login')
                ->withInput($request->only('email'))
                ->with('error', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง');
        }

        // 3) ล็อกอินสำเร็จ -> เคลียร์ rate limiter ของ key นี้
        RateLimiter::clear($throttleKey);

        Auth::login($user, $request->boolean('remember'));

        // 4) ป้องกัน session fixation: สร้าง session id ใหม่หลัง login เสมอ
        $request->session()->regenerate();

        // 5) อัปเดตข้อมูลการเข้าสู่ระบบล่าสุด
        $user->update([
            'last_login_at' => now(),
        ]); 

        $this->logAttempt($request, $user, 'success');

        return redirect()->intended(route('dashboard'))->with('success', 'เข้าสู่ระบบสำเร็จ');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::logout();

        // ทำลาย session เดิมทั้งหมดและสร้าง CSRF token ใหม่ กัน session reuse หลัง logout
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user) {
            $this->logAttempt($request, $user, 'logout');
        }

        return redirect()->route('login')->with('success', 'ออกจากระบบเรียบร้อย');
    }

    /**
     * สร้าง key สำหรับ rate limiter จาก email (lowercase) + IP
     * รวม IP ด้วยเพื่อไม่ให้คนอื่นโดนล็อกร่วมกันถ้า attacker สุ่ม email เดียวจากหลาย IP
     * และไม่ให้ attacker เดียวสลับ email ไปเรื่อยๆ จาก IP เดิมเพื่อหนี rate limit
     */
    private function throttleKey(Request $request): string
    {
        $email = Str::lower((string) $request->input('email'));

        return $email . '|' . $request->ip();
    }

    /**
     * บันทึกทุกความพยายามล็อกอิน (สำเร็จ/ล้มเหลว) ลง activity_logs เพื่อ audit trail
     */
    private function logAttempt(Request $request, ?User $user, string $action): void
    {
        try {
            DB::table('activity_logs')->insert([
                'user_id'    => $user?->id,
                'module'     => 'auth',
                'action'     => $action,
                'model_type' => User::class,
                'model_id'   => $user?->id,
                'description' => "Login attempt: {$action} (email: {$request->input('email')})",
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // การ log ห้ามทำให้ flow login ล้มเหลว แค่บันทึก error ไว้ใน Laravel log แทน
            Log::warning('Failed to write activity_logs for auth attempt', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}