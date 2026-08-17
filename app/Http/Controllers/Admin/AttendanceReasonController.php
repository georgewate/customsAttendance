<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceReason;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceReasonController extends Controller
{
    public function index(Request $request): View
    {
        $reasons = AttendanceReason::query()
            ->with(['creator', 'updater'])
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->query('category')))
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('active', $request->query('status') === 'active');
            })
            ->when($request->filled('search'), fn ($q) => $q->search($request->query('search')))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance-reasons.index', compact('reasons'));
    }

    public function create(): View
    {
        return view('admin.attendance-reasons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateReason($request);

        $reason = AttendanceReason::create($data + ['created_by' => Auth::id()]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'attendance_reason_created',
            'description' => "Created attendance reason \"{$reason->name}\" ({$reason->categoryLabel()}).",
            'metadata' => [
                'attendance_reason_id' => $reason->id,
                'category' => $reason->category,
                'name' => $reason->name,
            ],
        ]);

        return redirect()->route('admin.attendance-reasons.index')
            ->with('success', 'Attendance reason created.');
    }

    public function edit(AttendanceReason $attendanceReason): View
    {
        return view('admin.attendance-reasons.edit', ['reason' => $attendanceReason]);
    }

    public function update(Request $request, AttendanceReason $attendanceReason): RedirectResponse
    {
        $old = $attendanceReason->only(['category', 'name', 'description', 'active']);
        $data = $this->validateReason($request);

        $attendanceReason->update($data + ['updated_by' => Auth::id()]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'attendance_reason_updated',
            'description' => "Updated attendance reason \"{$attendanceReason->name}\".",
            'metadata' => [
                'attendance_reason_id' => $attendanceReason->id,
                'old' => $old,
                'new' => $attendanceReason->only(['category', 'name', 'description', 'active']),
            ],
        ]);

        return redirect()->route('admin.attendance-reasons.index')
            ->with('success', 'Attendance reason updated.');
    }

    public function destroy(AttendanceReason $attendanceReason): RedirectResponse
    {
        if ($attendanceReason->inUse()) {
            return back()->with(
                'error',
                'This reason cannot be deleted because it is already associated with attendance records. Disable it instead.'
            );
        }

        $name = $attendanceReason->name;

        $attendanceReason->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'attendance_reason_deleted',
            'description' => "Deleted attendance reason \"{$name}\".",
            'metadata' => [
                'attendance_reason_id' => $attendanceReason->id,
                'category' => $attendanceReason->category,
                'name' => $name,
            ],
        ]);

        return redirect()->route('admin.attendance-reasons.index')
            ->with('success', 'Attendance reason deleted.');
    }

    public function toggle(AttendanceReason $attendanceReason): RedirectResponse
    {
        $attendanceReason->update([
            'active' => ! $attendanceReason->active,
            'updated_by' => Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $attendanceReason->active ? 'attendance_reason_enabled' : 'attendance_reason_disabled',
            'description' => ($attendanceReason->active ? 'Enabled' : 'Disabled')." attendance reason \"{$attendanceReason->name}\".",
            'metadata' => [
                'attendance_reason_id' => $attendanceReason->id,
                'active' => $attendanceReason->active,
            ],
        ]);

        return back()->with(
            'success',
            $attendanceReason->active ? 'Attendance reason enabled.' : 'Attendance reason disabled.'
        );
    }

    private function validateReason(Request $request): array
    {
        return Validator::make($request->all(), [
            'category' => ['required', Rule::in(array_keys(AttendanceReason::CATEGORIES))],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['sometimes', 'boolean'],
        ], [
            'category.required' => 'Please select a reason category.',
        ])->validate();
    }
}
