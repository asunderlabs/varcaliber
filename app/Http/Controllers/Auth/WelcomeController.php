<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\WelcomeNotification\WelcomeController as BaseWelcomeController;
use Symfony\Component\HttpFoundation\Response;

class WelcomeController extends BaseWelcomeController
{
    public function showWelcomeForm(Request $request, User $user)
    {
        return Inertia::render('AcceptInvite', [
            'email' => $request->email,
            'user' => $user,
        ]);
    }

    public function sendPasswordSavedResponse(): Response
    {
        return redirect()->route('dashboard');
    }
}
