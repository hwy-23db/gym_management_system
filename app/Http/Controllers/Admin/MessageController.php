<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MessageController extends Controller
{
    public function conversations(): Response
    {
        $admin = $this->adminUser();

        $messages = Message::with(['sender', 'recipient'])
            ->where(function ($query) use ($admin) {
                $query->where('sender_id', $admin->id)
                    ->orWhere('recipient_id', $admin->id);
            })
            ->orderByDesc('created_at')
            ->get();

        $conversations = [];
        $seen = [];

        foreach ($messages as $message) {
            $otherUser = $message->sender_id === $admin->id
                ? $message->recipient
                : $message->sender;

            if (!$otherUser) {
                continue;
            }

            if (isset($seen[$otherUser->id])) {
                continue;
            }

            $seen[$otherUser->id] = true;

            $conversations[] = [
                'user_id' => $otherUser->id,
                'user_name' => $otherUser->name,
                'user_email' => $otherUser->email,
                'user_role' => $otherUser->role,
                'preview' => $message->body,
                'updated_at' => $message->created_at,
            ];
        }

        return response([
            'conversations' => $conversations,
        ]);
    }

    public function thread(User $user): Response
    {
        $admin = $this->adminUser();

        $messages = Message::with(['sender'])
            ->where(function ($query) use ($admin, $user) {
                $query->where('sender_id', $user->id)
                    ->where('recipient_id', $admin->id);
            })
            ->orWhere(function ($query) use ($admin, $user) {
                $query->where('sender_id', $admin->id)
                    ->where('recipient_id', $user->id);
            })
            ->orderBy('created_at')
            ->get();

        Message::where('sender_id', $user->id)
            ->where('recipient_id', $admin->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $payload = $messages->map(function (Message $message) use ($admin) {
            return [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'is_admin' => $message->sender_id === $admin->id,
                'sender_name' => $message->sender?->name,
            ];
        });

        return response([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'messages' => $payload,
        ]);
    }

    public function store(Request $request, User $user): Response
    {
        $admin = $this->adminUser();

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        Message::create([
            'sender_id' => $admin->id,
            'recipient_id' => $user->id,
            'body' => $validated['body'],
        ]);

        return response(['status' => 'sent'], 201);
    }

    private function adminUser(): User
    {
        return auth()->user();
    }
}
