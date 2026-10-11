<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingZoneController extends Controller
{
    public function index(): View
    {
        return view('admin.zones.index', ['zones' => ShippingZone::orderBy('sort')->get()]);
    }

    public function create(): View
    {
        return view('admin.zones.form', ['zone' => new ShippingZone(['active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        ShippingZone::create($this->validated($request));

        return redirect()->route('admin.zones.index')->with('status', 'Zona creada.');
    }

    public function edit(ShippingZone $zone): View
    {
        return view('admin.zones.form', compact('zone'));
    }

    public function update(Request $request, ShippingZone $zone): RedirectResponse
    {
        $zone->update($this->validated($request));

        return redirect()->route('admin.zones.index')->with('status', 'Zona actualizada.');
    }

    public function destroy(ShippingZone $zone): RedirectResponse
    {
        $zone->delete();

        return back()->with('status', 'Zona eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'eta' => ['nullable', 'string', 'max:120'],
            'eta_en' => ['nullable', 'string', 'max:120'],
            'postal_ranges' => ['nullable', 'string', 'max:2000', 'regex:/^[\d\s\-,;]*$/'],
            'price' => ['required', 'numeric', 'min:0'],
            'free_from' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ], ['postal_ranges.regex' => 'Usá rangos de números, uno por línea: 1000-1499']);
        $data['sort'] ??= 0;

        return $data + ['is_pickup' => $request->boolean('is_pickup'), 'active' => $request->boolean('active')];
    }
}
