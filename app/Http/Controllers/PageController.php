<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\HostingPlan;
use App\Models\Product;
use App\Models\Project;
use App\Models\ShippingZone;
use App\Models\Skill;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'featured' => Product::with('category')->visible()->where('featured', true)->latest('updated_at')->take(8)->get(),
            'projects' => Project::where('active', true)->orderByDesc('featured')->orderBy('sort')->take(3)->get(),
        ]);
    }

    public function hosting(): View
    {
        return view('pages.hosting', [
            'plans' => HostingPlan::where('active', true)->orderBy('sort')->get()->groupBy('panel'),
        ]);
    }

    public function design(): View
    {
        return view('pages.design', [
            'projects' => Project::where('active', true)->orderByDesc('featured')->orderBy('sort')->take(3)->get(),
        ]);
    }

    public function service(): View
    {
        return view('pages.service');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    /** Envíos, arrepentimiento, cambios y garantía (la pide Google Merchant Center y la ley de Defensa del Consumidor). */
    public function policies(): View
    {
        return view('pages.policies', ['zones' => ShippingZone::active()->get()]);
    }

    public function portfolio(): View
    {
        return view('pages.portfolio', $this->portfolioData());
    }

    /** El mismo contenido del portfolio en un PDF para descargar (en el idioma actual). */
    public function portfolioPdf(): Response
    {
        $name = config('tnl.profile.name');

        return Pdf::loadView('pdf.portfolio', $this->portfolioData())
            ->setPaper('a4')
            ->setOption(['chroot' => public_path(), 'isRemoteEnabled' => false, 'isFontSubsettingEnabled' => true])
            ->download(Str::slug($name).'-portfolio'.(is_en() ? '-en' : '').'.pdf');
    }

    private function portfolioData(): array
    {
        $certifications = Certification::orderByDesc('issued_at')->get();

        return [
            'projects' => Project::where('active', true)->orderByDesc('featured')->orderBy('sort')->get(),
            'skills' => Skill::orderBy('id')->orderBy('sort')->get()->groupBy('group'),
            'certifications' => $certifications,
            'issuers' => $certifications->countBy('issuer')->sortDesc(),
        ];
    }
}
