<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('location')->orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $locations = Location::orderBy('name')->get();

        return view('admin.users.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, passwordRequired: true);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'location_id' => $data['role'] === 'manager' ? $data['location_id'] : null,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        $locations = Location::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'locations'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, passwordRequired: false, ignoreUserId: $user->id);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'location_id' => $data['role'] === 'manager' ? $data['location_id'] : null,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, "You can't remove your own account.");

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User removed.');
    }

    private function validated(Request $request, bool $passwordRequired, ?int $ignoreUserId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'.($ignoreUserId ? ",{$ignoreUserId}" : '')],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,manager'],
            'location_id' => ['required_if:role,manager', 'nullable', 'exists:locations,id'],
        ]);
    }
}
