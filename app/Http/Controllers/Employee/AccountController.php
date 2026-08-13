<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function showPinForm()
    {
        return view('employee.pin', ['employee' => Auth::guard('employee')->user()]);
    }

    public function updatePin(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        abort_unless($employee, 403);

        $validated = $request->validate([
            'current_pin' => ['required', 'string'],
            'new_pin' => ['required', 'digits_between:4,8', 'confirmed', 'different:current_pin'],
        ]);

        if (! Hash::check($validated['current_pin'], $employee->pin_hash)) {
            throw ValidationException::withMessages([
                'current_pin' => 'Your current PIN is incorrect.',
            ]);
        }

        $employee->pin_hash = Hash::make($validated['new_pin']);
        $employee->save();

        return back()->with('success', 'Your PIN has been updated successfully.');
    }

    public function showProfileForm()
    {
        return view('employee.profile', ['employee' => Auth::guard('employee')->user()]);
    }

    public function updateProfile(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        abort_unless($employee, 403);

        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ]);

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        $employee->photo = $request->file('photo')->store('employees', 'public');
        $employee->save();

        return back()->with('success', 'Your profile picture has been updated.');
    }

    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
