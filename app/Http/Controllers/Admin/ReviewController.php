<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Support\Qr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Moderación de las reseñas que dejan los compradores, y el cartel con QR para pedir reseñas en Google. */
class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = array_key_exists($request->query('estado'), Review::STATUSES) ? $request->query('estado') : Review::PENDING;

        return view('admin.reviews.index', [
            'reviews' => Review::with('product', 'order')->where('status', $status)->latest()->paginate(30)->withQueryString(),
            'status' => $status,
            'counts' => Review::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in([Review::APPROVED, Review::REJECTED])]]);

        $review->update([
            'status' => $data['status'],
            'approved_at' => $data['status'] === Review::APPROVED ? ($review->approved_at ?? now()) : null,
        ]);

        return back()->with('status', $data['status'] === Review::APPROVED ? 'Reseña publicada.' : 'Reseña rechazada.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Reseña eliminada.');
    }

    /** Página para imprimir: cartel A4 y tarjetas con el QR a la reseña de Google del negocio. */
    public function poster(): View
    {
        $url = config('tnl.google_review');
        abort_unless($url, 404);

        return view('admin.reviews.poster', ['url' => $url, 'qr' => Qr::svg($url, 600)]);
    }
}
