<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    use StoresUploads;

    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->search($request->query('q'))
            ->when($request->query('rubro'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->boolean('ofertas'), fn ($q) => $q->where('featured', true))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Product(['active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $product = new Product();
        $this->save($request, $product);

        return redirect()->route('admin.products.index')->with('status', 'Producto creado.');
    }

    public function edit(Product $product): View
    {
        return $this->form($product);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        // Atajo desde el listado: solo cambia "en oferta" o "activo".
        if ($request->has('toggle')) {
            $field = $request->input('toggle');
            abort_unless(in_array($field, ['featured', 'active'], true), 422);
            $product->update([$field => ! $product->{$field}]);

            return back();
        }

        $this->save($request, $product);

        return redirect()->route('admin.products.index')->with('status', 'Producto actualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ([$product->image, ...($product->images ?? [])] as $path) {
            $this->deleteUpload($path);
        }
        $product->delete();

        return back()->with('status', 'Producto eliminado.');
    }

    private function form(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    private function save(Request $request, Product $product): void
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('products')->ignore($product)],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:5000'],
            'specs' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'max:4096'],
            'remove_images' => ['nullable', 'array'],
        ]);

        $data['specs'] = Product::parseSpecs($data['specs'] ?? null);
        $data['image'] = $this->storeUpload($request->file('photo'), 'productos', $product->image);
        $data['images'] = $this->saveExtraImages($request, $product->images ?? []);
        $data['featured'] = $request->boolean('featured');
        $data['active'] = $request->boolean('active');
        unset($data['photo'], $data['photos'], $data['remove_images']);

        $product->fill($data)->save();
    }

    /** Quita las fotos adicionales marcadas y agrega las nuevas al final. */
    private function saveExtraImages(Request $request, array $images): ?array
    {
        $remove = (array) $request->input('remove_images', []);
        foreach (array_intersect($images, $remove) as $path) {
            $this->deleteUpload($path);
        }
        $images = array_values(array_diff($images, $remove));

        foreach ($request->file('photos', []) as $file) {
            $images[] = $this->storeUpload($file, 'productos');
        }

        return $images ?: null;
    }
}
