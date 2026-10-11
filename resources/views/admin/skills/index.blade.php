@extends('layouts.admin')

@section('title', 'Skills')
@section('actions')
    <a href="{{ route('admin.skills.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Nuevo skill</a>
@endsection

@section('content')
    <p class="mb-6 text-sm text-zinc-500">Se muestran en el portfolio agrupados. El primer grupo creado se destaca en color.</p>
    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ($skills as $group => $items)
            <div class="card">
                <h2 class="border-b border-zinc-100 px-5 py-3 font-sans text-sm font-semibold dark:border-ink-700">{{ $group }}</h2>
                <ul class="divide-y divide-zinc-100 dark:divide-ink-700">
                    @foreach ($items as $skill)
                        <li class="flex items-center gap-3 px-5 py-1.5 text-sm">
                            <span class="w-6 text-zinc-400">{{ $skill->sort }}</span>
                            <span class="flex-1">{{ $skill->name }}</span>
                            <x-admin.row-actions :edit="route('admin.skills.edit', $skill)" :destroy="route('admin.skills.destroy', $skill)" />
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@endsection
