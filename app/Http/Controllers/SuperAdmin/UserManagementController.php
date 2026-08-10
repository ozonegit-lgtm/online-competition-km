<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesConstrainedDeletes;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UserManagementController extends Controller
{
    use HandlesConstrainedDeletes;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return view('createUser');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('createUser');
        $users = User::all();
        $roles = Role::orderBy('id')->get();
        return view('superadmin.createUsers', compact('roles','users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'role_id' => ['required','integer','exists:roles,id'],
            'is_active' => ['required', Rule::in([0,1])],
        ],
         [
            'username.required' => 'กรุณากรอกชื่อผู้ใช้งาน',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้มีผู้ใช้งานแล้ว',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน',
            'role_id.required' => 'กรุณาเลือกบทบาท',
            'role_id.exists' => 'ไม่พบบทบาทที่เลือก',
            'is_active.required' => 'กรุณาเลือกสถานะผู้ใช้งาน',
        ]);
        

        User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->route('superadmin.createUser')->with('success', 'สร้างผู้ใช้งานสำเร็จ');
            
        }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('role')->findOrFail($id);
        return view('superadmin.showUser', compact('user'));   
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::orderBy('id')->get();
        return view('superadmin.editeUser', compact('user','roles'));   
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ((int) $user->getKey() === (int) $request->user()->getAuthIdentifier()) {
            return redirect()
                ->route('superadmin.showUser', ['id' => $user->id])
                ->with('error', 'ไม่สามารถแก้ไขบัญชีที่กำลังเข้าสู่ระบบอยู่ได้');
        }

        $validated = $request->validate([
            'username' => ['required','string','max:255',Rule::unique('users','username')->ignore($user->id),],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id),],
            'password' => ['nullable','string','min:8','confirmed'],
            'role_id' => ['required','integer','exists:roles,id'],
            'is_active' => ['required', Rule::in([0,1])],
        ],
         [
            'username.unique' => 'ชื่อผู้ใช้งานนี้มีอยู่ในระบบแล้ว',
            'username.required' => 'กรุณากรอกชื่อผู้ใช้งาน',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้มีผู้ใช้งานแล้ว',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน',
            'role_id.required' => 'กรุณาเลือกบทบาท',
            'role_id.exists' => 'ไม่พบบทบาทที่เลือก',
            'is_active.required' => 'กรุณาเลือกสถานะผู้ใช้งาน',
        ]);
        

        $validated['is_active'] = (bool) $validated['is_active'];
        if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }
            $user->update($validated);

        return redirect()->route('superadmin.showUser', ['id' => $user->id])->with('success', 'แก้ไขข้อมูลผู้ใช้งานสำเร็จ');
            
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ((int) $user->getKey() === (int) request()->user()->getAuthIdentifier()) {
            return redirect()
                ->route('superadmin.showUser', ['id' => $user->id])
                ->with('error', 'ไม่สามารถลบบัญชีที่กำลังเข้าสู่ระบบอยู่ได้');
        }

        if (! $this->deleteUnlessReferenced($user)) {
            return redirect()
                ->route('superadmin.showUser', ['id' => $user->id])
                ->with('error', 'ไม่สามารถลบบัญชีนี้ได้ เนื่องจากมีข้อมูลการแข่งขันอ้างอิงอยู่');
        }

        return redirect()->route('superadmin.createUser')->with('success', 'ลบผู้ใช้งานสำเร็จ');
    }
}