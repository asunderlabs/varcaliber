<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailgunWebhookController extends Controller
{
    private $payload;

    private $event;

    private $eventData;

    public function __construct(Request $request)
    {
        $this->payload = json_decode($request->getContent(), true);
        info($request->getContent());

        if (! $this->payload) {
            return;
        }

        $this->event = $this->payload['event-data']['event'] ?? '';
        $this->eventData = $this->payload['event-data'];

        if (! $this->verify()) {
            Log::error('Failed to verify mailgun webhook signature');
            abort(403, 'Forbidden');
        }
    }

    public function index(Request $request)
    {
        /*

        Request Payload Example

        {
            “signature”:
            {
                "timestamp": "1529006854",
                "token": "abcdef",
                "signature": "abcdef"
            }
            “event-data”:
            {
                "event": "delivered",
                "timestamp": 1529006854.329574,
                "id": "XYZ",
                "user-variables": {
                    "first_name": "John",
                    "last_name:" "Smith",
                    "my_message_id": "123"
                }
                "envelope": {
                    "transport": "smtp",
                    "sender": "postmaster@mg.example.com",
                    "sending-ip": "127.0.0.1",
                    "targets": "info@example.com"
                },
                "message": {
                    "headers": {
                        "to": "info@example.com",
                        "message-id": "1234.abcdef@mg.example.com",
                        "from": "Varcaliber <info@example.com>",
                        "subject": "Varcaliber Daily Digest"
                    },
                    "attachments": [],
                    "size": 13078
                },
                "recipient": "info@example.com",
            }

        }

        */

        switch ($this->event) {
            case 'delivered':
                $email = Email::find(intval($this->getUserVariable('email_id')));
                if ($email && $email->to === $this->eventData['recipient']) {
                    $email->status = 'delivered';
                    $email->mailgun_message_id = $this->eventData['message']['headers']['message-id'];
                    $email->save();
                }
                break;
        }

        return response('success', 200);

    }

    private function verify()
    {
        $timestamp = $this->payload['signature']['timestamp'];
        $token = $this->payload['signature']['token'];
        $signature = $this->payload['signature']['signature'];

        return hash_equals(hash_hmac('sha256', $timestamp . $token, config('app.mailgun_webhook_signing_key')), $signature);
    }

    private function getUserVariable($key)
    {
        return $this->payload['event-data']['user-variables'][$key] ?? null;
    }
}
