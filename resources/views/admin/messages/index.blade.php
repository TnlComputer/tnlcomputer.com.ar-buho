@extends('layouts.admin')

@section('title', 'Mensajes')

@section('content')
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.messages.index') }}" @class(['chip !px-3.5 !py-1.5', '!bg-brand-600 !text-white' => ! $type])>Todos</a>
        @foreach (\App\Models\Message::TYPES as $key => $label)
            <a href="{{ route('admin.messages.index', ['tipo' => $key]) }}" @class(['chip !px-3.5 !py-1.5', '!bg-brand-600 !text-white' => $type === $key])>{{ $label }}</a>
        @endforeach
    </div>
    <div class="card">
        @include('admin.messages.list', ['messages' => $messages])
    </div>
    <div class="mt-6">{{ $messages->links() }}</div>
@endsection
