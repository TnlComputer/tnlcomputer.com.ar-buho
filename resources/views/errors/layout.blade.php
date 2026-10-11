@extends('layouts.site')

@section('content')
    <div class="container-x grid place-items-center py-24 text-center">
        <p class="font-display text-7xl font-bold text-brand-200 dark:text-brand-900">@yield('code')</p>
        <h1 class="mt-4 text-3xl font-bold">@yield('heading')</h1>
        <p class="mt-3 max-w-md text-zinc-600 dark:text-zinc-400">@yield('text')</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ lroute('home') }}" class="btn btn-primary"><x-icon name="home" class="size-4" /> {{ __('Ir al inicio') }}</a>
            <a href="{{ lroute('shop.index') }}" class="btn btn-secondary">{{ __('Ver la tienda') }}</a>
        </div>
    </div>
@endsection
