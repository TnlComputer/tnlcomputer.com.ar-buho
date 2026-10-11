<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ProductCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductImportController extends Controller
{
    public function create(): View
    {
        return view('admin.products.import');
    }

    public function store(Request $request, ProductCsvImporter $importer): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'adjust' => ['nullable', 'numeric', 'between:-90,500'],
        ]);

        try {
            $result = $importer->import(
                $request->file('file')->getRealPath(),
                (float) $request->input('adjust', 0),
                $request->boolean('deactivate'),
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()->route('admin.products.index')
            ->with('status', "Importación lista: {$result['created']} nuevos, {$result['updated']} actualizados, {$result['skipped']} salteados, {$result['deactivated']} ocultados.")
            ->with('import_errors', array_slice($result['errors'], 0, 20));
    }
}
