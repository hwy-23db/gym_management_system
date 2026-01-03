<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'phone', 'role', 'email_verified_at', 'notifications_enabled', 'created_at', 'updated_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => 'Users retrieved successfully',
            'users' => $users,
        ]);
    }

    public function deleted()
    {
        $users = User::onlyTrashed()
            ->select('id', 'name', 'email','phone', 'role', 'email_verified_at', 'created_at', 'updated_at', 'deleted_at')
            ->orderByDesc('deleted_at')
            ->get();

        return response()->json([
            'message' => 'Deleted users retrieved successfully',
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required_unless:role,administrator', 'nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['administrator', 'trainer', 'user'])],
        ]);

        // ✅ HASH PASSWORD
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->only(['id', 'name', 'email', 'phone', 'role', 'created_at']),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        if ($user->role === 'administrator') {
            return response()->json(['message' => 'Administrator account cannot be modified.'], 403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['sometimes', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', Rule::in(['administrator', 'trainer', 'user'])],
            'notifications_enabled' => ['sometimes', 'boolean'],
        ]);

        // ✅ Only hash if password provided
        if (array_key_exists('password', $data)) {
            if (filled($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
        }

        $user->fill($data)->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->only(['id', 'name', 'email', 'phone', 'role', 'notifications_enabled', 'updated_at']),
        ]);
    }

    public function destroy($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'administrator') {
        return response()->json(['message' => 'Administrator account cannot be deleted.'], 403);
    }

    $user->delete(); // soft delete
    return response()->json(['message' => 'User deleted successfully']);
}

public function restore($id)
{
    $user = User::withTrashed()->findOrFail($id);

    if ($user->role === 'administrator') {
        return response()->json(['message' => 'Administrator account cannot be restored here.'], 403);
    }

    if (!$user->trashed()) {
        return response()->json(['message' => 'User is not deleted.'], 400);
    }

    $user->restore();
    return response()->json(['message' => 'User restored successfully']);
}

}
