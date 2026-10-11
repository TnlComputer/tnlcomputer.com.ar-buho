<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category(['active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validated($request, new Category()));

        return redirect()->route('admin.categories.index')->with('status', 'Rubro creado.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('status', 'Rubro actualizado.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Los productos quedan sin rubro (nullOnDelete), no se borran.
        $category->delete();

        return back()->with('status', 'Rubro eliminado.');
    }

    private function validated(Request $request, Category $category): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', Rule::unique('categories')->ignore($category)],
        ]) + ['active' => $request->boolean('active')];
    }
}
