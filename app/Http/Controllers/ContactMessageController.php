<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactMessageController extends Controller
{
    public function store(PublicContactMessageRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['website']);

        ContactMessage::create([
            'name' => $validated['name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => 'unread',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'ส่งข้อความเรียบร้อยแล้ว'], 201);
        }

        return redirect()
            ->to(route('knowledge.index').'#contact')
            ->with('success', 'ส่งข้อความเรียบร้อยแล้ว');
    }
}
