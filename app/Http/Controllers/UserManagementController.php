<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /** user list with search and pagination */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $users = User::query()
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")))
            ->sortable([
                'code' => 'code', 'name' => 'name', 'role' => 'role', 'position' => 'position',
                'join_date' => 'join_date', 'status' => 'status',
            ], 'name')
            ->paginateFromRequest(15);

        return view('usermanagement.list_users', compact('users', 'search'));
    }

    /** user view (URL uses the display code, e.g. /view/user/edit/USR-00001) */
    public function userView(string $code)
    {
        $users = User::where('code', $code)->firstOrFail();
        $roles = Role::cases();
        $statuses = UserStatus::cases();

        return view('usermanagement.user_update', compact('users', 'roles', 'statuses'));
    }

    /** User Update */
    public function userUpdate(Request $request)
    {
        if (Gate::denies('manage-users')) {
            Log::warning('Unauthorized user update attempt', [
                'code' => $request->input('code'),
                'role' => $request->user()->role?->value,
            ]);

            return redirect()->back()->with('error', "You don't have permission to update users.");
        }

        $user = User::where('code', $request->input('code'))->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::enum(Role::class)],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'position' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('avatar')) {
                $this->deleteAvatar($user->avatar);
                $file = $request->file('avatar');
                $validated['avatar'] = uniqid('avatar_', true).'.'.$file->extension();
                $file->move(public_path('images'), $validated['avatar']);
            } else {
                unset($validated['avatar']);
            }

            $user->update($validated);

            return redirect()->back()->with('success', 'User updated.');
        } catch (\Exception $e) {
            Log::error('User update failed', ['error' => $e->getMessage(), 'code' => $user->code]);

            return redirect()->back()->with('error', "Couldn't update the user. Please try again.");
        }
    }

    /** user delete */
    public function userDelete(Request $request)
    {
        if (Gate::denies('manage-users')) {
            return redirect()->back()->with('error', "You don't have permission to delete users.");
        }

        $user = User::where('code', $request->input('code'))->firstOrFail();

        try {
            $user->delete();
            // the avatar path comes from the database, never from the request
            $this->deleteAvatar($user->avatar);

            return redirect()->back()->with('success', 'User deleted.');
        } catch (\Exception $e) {
            Log::error('User delete failed', ['error' => $e->getMessage(), 'code' => $user->code]);

            return redirect()->back()->with('error', "Couldn't delete the user. Please try again.");
        }
    }

    private function deleteAvatar(?string $avatar): void
    {
        $path = public_path('images/'.$avatar);
        if ($avatar && $avatar !== 'photo_defaults.jpg' && File::exists($path)) {
            File::delete($path);
        }
    }

    /** change password */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', Password::min(8), 'different:current_password'],
            'new_confirm_password' => ['required', 'same:new_password'],
        ]);

        // the User model's "hashed" cast hashes the new password on save
        $request->user()->update(['password' => $request->new_password]);

        return redirect()->intended('home')->with('success', 'Password changed.');
    }
}
