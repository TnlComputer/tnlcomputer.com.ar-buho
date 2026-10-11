@if ($messages->isEmpty())
    <p class="px-5 py-10 text-center text-sm text-zinc-500">Todavía no hay mensajes.</p>
@else
    <ul class="divide-y divide-zinc-100 dark:divide-ink-700">
        @foreach ($messages as $m)
            <li>
                <a href="{{ route('admin.messages.show', $m) }}" class="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-3.5 hover:bg-zinc-50 dark:hover:bg-ink-800/60">
                    <span @class(['size-2 shrink-0 rounded-full', 'bg-brand-600' => ! $m->read_at, 'bg-transparent' => $m->read_at])></span>
                    <span class="chip">{{ \App\Models\Message::TYPES[$m->type] ?? $m->type }}</span>
                    <span @class(['min-w-0 flex-1 truncate', 'font-semibold text-zinc-900 dark:text-white' => ! $m->read_at])>{{ $m->name }} — {{ $m->subject }}</span>
                    @if ($m->total)<span class="text-sm font-semibold">{{ money($m->total) }}</span>@endif
                    <span class="text-xs text-zinc-500">{{ $m->created_at->diffForHumans() }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
