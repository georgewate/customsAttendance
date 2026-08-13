<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::withCount('employees')->orderBy('name')->paginate(20);

        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $location = Location::create($this->validated($request));

        return redirect()->route('admin.locations.show', $location)->with('success', 'Location created.');
    }

    public function show(Location $location)
    {
        return view('admin.locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $location->update($this->validated($request));

        return redirect()->route('admin.locations.show', $location)->with('success', 'Location updated.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', 'Location deleted.');
    }

    public function qr(Location $location)
    {
        $svg = QrCode::format('svg')->size(400)->generate($location->scanUrl());

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    public function regenerateToken(Location $location)
    {
        $location->update(['qr_token' => Str::random(32)]);

        return redirect()->route('admin.locations.show', $location)
            ->with('success', 'QR code regenerated — the old code no longer works.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:64'],
        ]) + ['active' => $request->boolean('active')];
    }
}
