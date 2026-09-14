<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Concerns\EnsuresSuperAdmin;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    use EnsuresSuperAdmin;

    public function index(Request $request): View
    {
        $this->ensureSuperAdmin();
        $query = ContactMessage::query()->latest();
        if (in_array($request->status, ContactMessage::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        return view('superadmin.contact-messages.index', ['messages' => $query->paginate(30)->withQueryString()]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        $this->ensureSuperAdmin();
        if ($contactMessage->status === 'unread') {
            $contactMessage->update(['status' => 'read', 'read_at' => now()]);
        }

        return view('superadmin.contact-messages.show', compact('contactMessage'));
    }

    public function status(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate(['status' => ['required', Rule::in(ContactMessage::STATUSES)]]);
        $contactMessage->update([
            'status' => $validated['status'],
            'read_at' => $validated['status'] === 'read' ? ($contactMessage->read_at ?? now()) : null,
        ]);

        return back()->with('success', 'ปรับสถานะข้อความแล้ว');
    }
}
