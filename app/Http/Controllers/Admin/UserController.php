<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
            $users = User::withTrashed()
            ->select('id','user_id', 'name', 'email', 'phone', 'role', 'email_verified_at', 'notifications_enabled', 'created_at', 'updated_at', 'deleted_at')
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
            ->select('id', 'user_id',  'name', 'email','phone', 'role', 'email_verified_at', 'created_at', 'updated_at', 'deleted_at')
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
            'user_id' => ['required', 'string', 'regex:/^\d{5}$/', 'unique:users,user_id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required_unless:role,administrator', 'nullable', 'string', 'max:20', 'unique:users,phone'],
                   'password' => [
                'required',
                'string',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
            'role' => ['required', Rule::in(['administrator', 'trainer', 'user'])],
             ], [
            'user_id.required' => 'The user id is required.',
            'user_id.regex' => 'The user id must be exactly 5 digits.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.letters' => 'The password must contain at least one letter.',
            'password.numbers' => 'The password must contain at least one number.',
            'password.symbols' => 'The password must contain at least one symbol.',
        ]);

        // ✅ HASH PASSWORD
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();

        $user = User::create($data);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->only(['id','user_id',  'name', 'email', 'phone', 'role', 'created_at']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->resolveUser($id);

        if (!$user) {
            return response()->json(['message' => 'The specified user does not exist.'], 404);
        }

        if ($user->role === 'administrator') {
            return response()->json(['message' => 'Administrator account cannot be modified.'], 403);
        }

        $data = $request->validate([
            'user_id' => ['sometimes', 'nullable', 'string', 'regex:/^\d{5}$/', Rule::unique('users', 'user_id')->ignore($user->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['sometimes', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
             'password' => [
                'nullable',
                'string',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
            'role' => ['sometimes', Rule::in(['administrator', 'trainer', 'user'])],
            'notifications_enabled' => ['sometimes', 'boolean'],
             ], [
            'user_id.regex' => 'The user id must be exactly 5 digits.',
            'password.min' => 'The password must be at least 4 characters.',
            'password.letters' => 'The password must contain at least one letter.',
            'password.numbers' => 'The password must contain at least one number.',
            'password.symbols' => 'The password must contain at least one symbol.',
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
            'user' => $user->only(['id', 'user_id',  'name', 'email', 'phone', 'role', 'notifications_enabled', 'updated_at']),
        ]);
    }

    public function destroy($id)
    {
        $user = $this->resolveUser($id, true);

        if (!$user) {
            return response()->json(['message' => 'The specified user does not exist.'], 404);
        }

        if ($user->role === 'administrator') {
            return response()->json(['message' => 'Administrator account cannot be deleted.'], 403);
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->forceDelete();
        return response()->json(['message' => 'User permanently deleted successfully']);
    }

    public function forceDestroy($id)
    {
        $user = $this->resolveUser($id, true);

        if (!$user) {
            return response()->json(['message' => 'The specified user does not exist.'], 404);
        }

        if ($user->role === 'administrator') {
            return response()->json(['message' => 'Administrator account cannot be deleted.'], 403);
        }


        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->forceDelete();

     return response()->json(['message' => 'User permanently deleted successfully']);
    }

    public function restore($id)
    {
        $user = $this->resolveUser($id, true);

        if (!$user) {
            return response()->json(['message' => 'The specified user does not exist.'], 404);
        }

        if ($user->role === 'administrator') {
            return response()->json(['message' => 'Administrator account cannot be restored here.'], 403);
        }

        if (!$user->trashed()) {
            return response()->json(['message' => 'User is not deleted.'], 400);
        }

        $user->restore();
        return response()->json(['message' => 'User restored successfully']);
    }

    private function resolveUser($identifier, bool $withTrashed = false): ?User
    {
        $query = User::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        $identifier = (string) $identifier;

        if (preg_match('/^\d{5}$/', $identifier)) {
            $user = $query->where('user_id', $identifier)->first();

            if ($user) {
                return $user;
            }
        }

        if (is_numeric($identifier)) {
            return $query->find((int) $identifier);
        }

        return null;
    }

}
