@extends('layouts.admin')

@section('title', 'Proyectos del portfolio')
@section('actions')
    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Nuevo proyecto</a>
@endsection

@section('content')
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="border-b border-zinc-100 text-left text-xs text-zinc-500 uppercase dark:border-ink-700"><tr><th class="px-4 py-3">#</th><th>Proyecto</th><th>Tipo</th><th>Año</th><th>Links</th><th></th></tr></thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                @foreach ($projects as $project)
                    <tr @class(['opacity-50' => ! $project->active])>
                        <td class="px-4 py-2.5 text-zinc-500">{{ $project->sort }}</td>
                        <td class="font-medium">{{ $project->title }} @if ($project->featured)<x-icon name="star" class="inline size-3.5 text-brand-600" />@endif</td>
                        <td class="text-zinc-500">{{ $project->kind }}</td>
                        <td class="text-zinc-500">{{ $project->year }}</td>
                        <td class="space-x-2 text-xs">
                            @if ($project->demo_url)<a href="{{ $project->demo_url }}" target="_blank" class="text-brand-600 hover:underline">demo</a>@endif
                            @if ($project->code_url)<a href="{{ $project->code_url }}" target="_blank" class="text-brand-600 hover:underline">código</a>@endif
                        </td>
                        <td class="px-4"><x-admin.row-actions :edit="route('admin.projects.edit', $project)" :destroy="route('admin.projects.destroy', $project)" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
