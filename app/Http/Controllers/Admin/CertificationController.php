<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificationController extends Controller
{
    use StoresUploads;

    public function index(Request $request): View
    {
        return view('admin.certifications.index', [
            'certifications' => Certification::when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
                ->orderByDesc('issued_at')->paginate(30)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Certification(['issued_at' => now()]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->save($request, new Certification());

        return redirect()->route('admin.certifications.index')->with('status', 'Certificación creada.');
    }

    public function edit(Certification $certification): View
    {
        return $this->form($certification);
    }

    public function update(Request $request, Certification $certification): RedirectResponse
    {
        $this->save($request, $certification);

        return redirect()->route('admin.certifications.index')->with('status', 'Certificación actualizada.');
    }

    public function destroy(Certification $certification): RedirectResponse
    {
        $this->deleteUpload($certification->file);
        $certification->delete();

        return back()->with('status', 'Certificación eliminada.');
    }

    private function form(Certification $certification): View
    {
        return view('admin.certifications.form', [
            'certification' => $certification,
            'issuers' => Certification::distinct()->orderBy('issuer')->pluck('issuer'),
        ]);
    }

    private function save(Request $request, Certification $certification): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'issuer' => ['required', 'string', 'max:120'],
            'issued_at' => ['nullable', 'date'],
            'document' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg,webp', 'max:8192'],
        ]);

        $data['file'] = $this->storeUpload($request->file('document'), 'certificados', $certification->file);
        $data['featured'] = $request->boolean('featured');
        unset($data['document']);

        $certification->fill($data)->save();
    }
}
