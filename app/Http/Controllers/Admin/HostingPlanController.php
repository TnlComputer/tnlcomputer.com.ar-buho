<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HostingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HostingPlanController extends Controller
{
    public function index(): View
    {
        return view('admin.plans.index', [
            'plans' => HostingPlan::orderBy('panel')->orderBy('sort')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.plans.form', ['plan' => new HostingPlan(['active' => true, 'domains' => 1, 'panel' => 'directadmin'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        HostingPlan::create($this->validated($request));

        return redirect()->route('admin.plans.index')->with('status', 'Plan creado.');
    }

    public function edit(HostingPlan $plan): View
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, HostingPlan $plan): RedirectResponse
    {
        $plan->update($this->validated($request));

        return redirect()->route('admin.plans.index')->with('status', 'Plan actualizado.');
    }

    public function destroy(HostingPlan $plan): RedirectResponse
    {
        $plan->delete();

        return back()->with('status', 'Plan eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'panel' => ['required', Rule::in(array_keys(HostingPlan::PANELS))],
            'domains' => ['required', 'integer', 'min:1', 'max:999'],
            'disk' => ['required', 'string', 'max:40'],
            'transfer' => ['required', 'string', 'max:40'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_semester' => ['nullable', 'numeric', 'min:0'],
            'price_annual' => ['nullable', 'numeric', 'min:0'],
            'price_biannual' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['sort'] ??= 0;

        return $data + [
            'highlighted' => $request->boolean('highlighted'),
            'active' => $request->boolean('active'),
        ];
    }
}
