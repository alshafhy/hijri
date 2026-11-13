<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing.index');
    }

    public function contact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:3000'],
            'website' => ['prohibited'],
        ]);
        unset($validated['website']);

        ContactMessage::query()->create([...$validated, 'ip_address' => $request->ip()]);

        return redirect()->to(route('home-page').'#contact')->with('contact_sent', true);
    }

    public function messages(): View
    {
        $messages = ContactMessage::query()->latest()->paginate(25);

        return view('landing.messages', compact('messages'));
    }

    public function markRead(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['read_at' => $contactMessage->read_at ? null : now()]);

        return back();
    }
}
