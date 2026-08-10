<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = $this->scopedEmployees($request)
            ->with('homeLocation')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.employees.index', compact('employees'));
    }

    public function create(Request $request)
    {
        $locations = $this->availableLocations($request);

        return view('admin.employees.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_code' => ['required', 'string', 'max:20', 'unique:employees,employee_code'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'pin' => ['required', 'string', 'min:4', 'max:8'],
            'home_location_id' => ['required', 'exists:locations,id'],
        ]);

        $this->authorizeLocation($request, (int) $data['home_location_id']);

        Employee::create([
            'employee_code' => $data['employee_code'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'photo' => $request->hasFile('photo') ? $request->file('photo')->store('employees', 'public') : null,
            'pin_hash' => Hash::make($data['pin']),
            'home_location_id' => $data['home_location_id'],
            'active' => true,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee added.');
    }

    public function edit(Request $request, Employee $employee)
    {
        $this->authorizeLocation($request, $employee->home_location_id);

        $locations = $this->availableLocations($request);

        return view('admin.employees.edit', compact('employee', 'locations'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeLocation($request, $employee->home_location_id);

        $data = $request->validate([
            'employee_code' => ['required', 'string', 'max:20', 'unique:employees,employee_code,'.$employee->id],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'pin' => ['nullable', 'string', 'min:4', 'max:8'],
            'home_location_id' => ['required', 'exists:locations,id'],
            'active' => ['nullable', 'boolean'],
        ]);

        $this->authorizeLocation($request, (int) $data['home_location_id']);

        if ($request->hasFile('photo')) {
            $this->deletePhoto($employee);
            $employee->photo = $request->file('photo')->store('employees', 'public');
        }

        $employee->fill([
            'employee_code' => $data['employee_code'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'home_location_id' => $data['home_location_id'],
            'active' => $request->boolean('active'),
        ]);

        if (! empty($data['pin'])) {
            $employee->pin_hash = Hash::make($data['pin']);
        }

        $employee->save();

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated.');
    }

    public function destroy(Request $request, Employee $employee)
    {
        $this->authorizeLocation($request, $employee->home_location_id);

        $this->deletePhoto($employee);

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee removed.');
    }

    private function deletePhoto(Employee $employee): void
    {
        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }
    }

    private function scopedEmployees(Request $request)
    {
        $user = $request->user();

        return $user->isAdmin()
            ? Employee::query()
            : Employee::where('home_location_id', $user->location_id);
    }

    private function availableLocations(Request $request)
    {
        $user = $request->user();

        return $user->isAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('id', $user->location_id)->get();
    }

    private function authorizeLocation(Request $request, ?int $locationId): void
    {
        $user = $request->user();

        abort_if(! $user->isAdmin() && $locationId !== $user->location_id, 403);
    }
}
