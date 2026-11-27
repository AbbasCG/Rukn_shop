<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter') === 'unread' ? 'unread' : 'all';

        $messages = ContactMessage::query()
            ->when($filter === 'unread', fn ($q) => $q->unread())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = ContactMessage::unread()->count();

        return view('admin.messages.index', compact('messages', 'unreadCount', 'filter'));
    }

    /**
     * Mark a message as read or unread.
     */
    public function update(Request $request, string $locale, ContactMessage $message)
    {
        $request->validate(['read' => ['required', 'boolean']]);

        $message->update(['read_at' => $request->boolean('read') ? now() : null]);

        return back()->with('success', __('admin.contact_messages.updated'));
    }

    public function destroy(string $locale, ContactMessage $message)
    {
        $message->delete();

        return back()->with('success', __('admin.contact_messages.deleted'));
    }
}
