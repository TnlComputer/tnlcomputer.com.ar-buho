@extends('layouts.admin')

@section('title', 'Certificaciones')
@section('actions')
    <a href="{{ route('admin.certifications.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Nueva certificación</a>
@endsection

@section('content')
    <form method="GET" class="mb-4 flex gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por título" class="input flex-1" aria-label="Buscar">
        <button class="btn btn-secondary">Buscar</button>
    </form>
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="border-b border-zinc-100 text-left text-xs text-zinc-500 uppercase dark:border-ink-700"><tr><th class="px-4 py-3">Fecha</th><th>Título</th><th>Emisor</th><th>Archivo</th><th></th></tr></thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                @foreach ($certifications as $cert)
                    <tr>
                        <td class="px-4 py-2.5 whitespace-nowrap text-zinc-500">{{ $cert->issued_at?->format('d/m/Y') }}</td>
                        <td class="font-medium">{{ $cert->title }} @if ($cert->featured)<x-icon name="star" class="inline size-3.5 text-brand-600" />@endif</td>
                        <td class="text-zinc-500">{{ $cert->issuer }}</td>
                        <td>
                            @if ($cert->file)
                                <a href="{{ $cert->fileUrl() }}" target="_blank" @class(['text-xs hover:underline', 'text-brand-600' => is_file(public_path($cert->file)), 'text-red-600' => ! is_file(public_path($cert->file))])>
                                    {{ is_file(public_path($cert->file)) ? 'ver' : 'falta el archivo' }}
                                </a>
                            @endif
                        </td>
                        <td class="px-4"><x-admin.row-actions :edit="route('admin.certifications.edit', $cert)" :destroy="route('admin.certifications.destroy', $cert)" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $certifications->links() }}</div>
@endsection
