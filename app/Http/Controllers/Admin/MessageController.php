<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $type = array_key_exists($request->query('tipo'), Message::TYPES) ? $request->query('tipo') : null;

        return view('admin.messages.index', [
            'messages' => Message::when($type, fn ($q) => $q->where('type', $type))->latest()->paginate(30)->withQueryString(),
            'type' => $type,
        ]);
    }

    public function show(Message $message): View
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return view('admin.messages.show', compact('message'));
    }

    public function destroy(Message $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'Mensaje eliminado.');
    }
}
