<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * แสดงหน้าแก้ไขโปรไฟล์
     */
    public function edit(Request $request)
    {
        $user = $request->user()->load([
            'role',
            'adminProfile',
        ]);

        return view(
            'profile.edit',
            compact('user')
        );
    }

    /**
     * บันทึกข้อมูลโปรไฟล์
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $profile = $user->adminProfile;

        $validated = $request->validate([
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]{8,20}$/',
            ],
            'position' => [
                'nullable',
                'string',
                'max:150',
            ],
            'organization' => [
                'nullable',
                'string',
                'max:255',
            ],
            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'first_name.required' => 'กรุณากรอกชื่อ',
            'first_name.max' => 'ชื่อต้องไม่เกิน 100 ตัวอักษร',
            'last_name.required' => 'กรุณากรอกนามสกุล',
            'last_name.max' => 'นามสกุลต้องไม่เกิน 100 ตัวอักษร',
            'phone.regex' => 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง',
            'phone.max' => 'เบอร์โทรศัพท์ต้องไม่เกิน 20 ตัวอักษร',
            'position.max' => 'ตำแหน่งต้องไม่เกิน 150 ตัวอักษร',
            'organization.max' => 'ชื่อหน่วยงานต้องไม่เกิน 255 ตัวอักษร',
            'profile_image.image' => 'ไฟล์ที่เลือกต้องเป็นรูปภาพ',
            'profile_image.mimes' =>
                'รองรับเฉพาะ JPG, JPEG, PNG และ WEBP',
            'profile_image.max' =>
                'รูปโปรไฟล์ต้องมีขนาดไม่เกิน 5 MB',
        ]);

        $oldAvatar = $profile?->avatar;

        if ($request->hasFile('profile_image')) {
            $validated['avatar'] = $request
                ->file('profile_image')
                ->store('profile-images', 'public');
        }

        /*
         * profile_image เป็นชื่อ Input จากหน้าเว็บ
         * แต่คอลัมน์ในฐานข้อมูลชื่อ avatar
         */
        unset($validated['profile_image']);

        $profile = $user->adminProfile()->updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            $validated
        );

        /*
         * ลบรูปเก่าหลังบันทึกรูปใหม่สำเร็จ
         */
        if (
            isset($validated['avatar']) &&
            $oldAvatar &&
            $oldAvatar !== $profile->avatar
        ) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return redirect()
            ->route('profile.edit')
            ->with(
                'success',
                'บันทึกข้อมูลโปรไฟล์สำเร็จ'
            );
    }
}