<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller {
    public function store(Request $request) {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:100',
            'message' => 'required|string|max:2000',
        ]);

        ContactMessage::create($validated);

        return response()->json(['success' => true, 'message' => 'Message received. We will be in touch soon.'], 201);
    }
}
