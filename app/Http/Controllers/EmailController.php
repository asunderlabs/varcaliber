<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Inertia\Inertia;

class EmailController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Emails', [
            'emails' => Email::with('organization:id,name')->orderBy('created_at', 'desc')->paginate(10),
            'mailgunLogsUrl' => config('mail.mailgun_logs_url'),
        ]);
    }
}
