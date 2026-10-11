@extends('layouts.admin')

@section('title', 'Hola, '.auth()->user()->name)

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $label => [$value, $url])
            <a href="{{ $url }}" class="card p-5 transition hover:border-brand-300">
                <p class="text-sm text-zinc-500">{{ $label }}</p>
                <p class="mt-2 font-display text-3xl font-bold">{{ number_format($value, 0, ',', '.') }}</p>
            </a>
        @endforeach
    </div>

    <div class="card mt-8">
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-ink-700">
            <h2 class="font-sans text-base font-semibold">Últimos mensajes @if ($unread)<span class="ml-2 rounded-full bg-brand-600 px-2 py-0.5 text-xs text-white">{{ $unread }} sin leer</span>@endif</h2>
            <a href="{{ route('admin.messages.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">Ver todos</a>
        </div>
        @include('admin.messages.list', ['messages' => $messages])
    </div>
@endsection
