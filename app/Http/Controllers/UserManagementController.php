<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Concerns\HandlesDataTables;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    use HandlesDataTables;

    public function index()
    {
        return view('usermanagement.list_users');
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

            return redirect()->back()->with('error', 'User update failed: Unauthorized access.');
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

            return redirect()->back()->with('success', 'User updated successfully :)');
        } catch (\Exception $e) {
            Log::error('User update failed', ['error' => $e->getMessage(), 'code' => $user->code]);

            return redirect()->back()->with('error', 'User update failed. Please try again.');
        }
    }

    /** user delete */
    public function userDelete(Request $request)
    {
        if (Gate::denies('manage-users')) {
            return redirect()->back()->with('error', 'User deleted fail :(');
        }

        $user = User::where('code', $request->input('code'))->firstOrFail();

        try {
            $user->delete();
            // the avatar path comes from the database, never from the request
            $this->deleteAvatar($user->avatar);

            return redirect()->back()->with('success', 'User deleted successfully :)');
        } catch (\Exception $e) {
            Log::error('User delete failed', ['error' => $e->getMessage(), 'code' => $user->code]);

            return redirect()->back()->with('error', 'User deleted fail :(');
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

        return redirect()->intended('home')->with('success', 'User change successfully :)');
    }

    /** get users data (DataTables server-side) */
    public function getUsersData(Request $request)
    {
        $searchable = ['name', 'email', 'position', 'phone_number', 'status'];
        $sortable = ['code', 'name', 'email', 'position', 'phone_number', 'join_date', 'status'];

        return $this->dataTableResponse($request, User::query(), $searchable, $sortable, fn (User $user) => [
            'code' => e($user->code),
            'avatar' => '
                <td>
                    <h2 class="table-avatar">
                        <a class="avatar-sm me-2">
                            <img class="avatar-img rounded-circle avatar" data-avatar="'.e($user->avatar).'" src="'.e($user->avatar_url).'" alt="'.e($user->name).'">
                        </a>
                    </h2>
                </td>',
            'name' => e($user->name),
            'email' => e($user->email),
            'position' => e($user->position),
            'phone_number' => e($user->phone_number),
            'join_date' => e($user->join_date?->format('Y-m-d')),
            'status' => '<td><span class="badge '.($user->status?->badgeClass() ?? 'badge-secondary').'">'.e($user->status?->label()).'</span></td>',
            'modify' => '
                <td class="text-end">
                    <div class="actions">
                        <a href="'.e(url('view/user/edit/'.$user->code)).'" class="btn btn-sm bg-danger-light">
                            <i class="far fa-edit me-2"></i>
                        </a>
                        <a class="btn btn-sm bg-danger-light delete user_id" data-bs-toggle="modal" data-code="'.e($user->code).'" data-bs-target="#delete">
                            <i class="fe fe-trash-2"></i>
                        </a>
                    </div>
                </td>',
        ]);
    }
}
