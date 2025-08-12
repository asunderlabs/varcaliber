<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;

class StripeWebhookController extends Controller
{
    private $event;

    public function __construct()
    {
        info('stripe webhook');
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/apikeys
        \Stripe\Stripe::setApiKey(config('app.stripe_key'));

        // If you are testing your webhook locally with the Stripe CLI you
        // can find the endpoint's secret by running `stripe listen`
        // Otherwise, find your endpoint's secret in your webhook settings in the Developer Dashboard
        $endpointSecret = config('app.stripe_webhook_secret');

        $payload = @file_get_contents('php://input');

        if (! $payload) {
            return;
        }

        $signatureHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $this->event = null;

        try {
            $this->event = \Stripe\Webhook::constructEvent(
                $payload, $signatureHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            logger()->error($e->getMessage());
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            logger()->error($e->getMessage());
            http_response_code(400);
            exit();
        }

    }

    public function index()
    {
        // TODO: Log each event to avoid duplication
        $event = $this->event;
        switch ($event->type) {
            case 'payment_intent.processing':
                info('payment_intent.processing ' . $event->data->object->id);
                $this->updatePaymentStatus($event->data->object->id, 'processing');
                $user = User::where('is_admin', true)->first();
                $user->sendPaymentIntentNotification($event->type, $event->data->object);
                break;
            case 'payment_intent.payment_failed':
                info('payment_intent.payment_failed ' . $event->data->object->id);
                $this->updatePaymentStatus($event->data->object->id, 'failed');
                break;
            case 'payment_intent.succeeded':
                info('payment_intent.succeeded ' . $event->data->object->id);
                $this->updatePaymentStatus($event->data->object->id, 'succeeded');
                $user = User::where('is_admin', true)->first();
                $user->sendPaymentIntentNotification($event->type, $event->data->object);
                break;
                // case 'charge.pending':
                //     info('charge.pending ' . $event->data->object->id);
                //     $this->updatePaymentStatus($event->data->object->id, 'pending');
                //     break;
                // case 'charge.failed':
                //     info('charge.failed ' . $event->data->object->id);
                //     $this->updatePaymentStatus($event->data->object->id, 'failed');
                //     break;
                // case 'charge.succeeded':
                //     info('charge.succeeded ' . $event->data->object->id);
                //     $this->updatePaymentStatus($event->data->object->id, 'succeeded');
                //     break;
            default:
                info($event->type . ' was not handled');
        }

        http_response_code(200);
    }

    private function updatePaymentStatus($paymentIntentId, $status)
    {
        $payment = Payment::where('payment_intent_id', $paymentIntentId)->first();

        if (! $payment) {
            info('Unable to find matching payment for ' . $paymentIntentId);

            return;
        }

        if ($status === 'failed') {
            $payment->status = $status;
            $payment->save();

            return;
        }

        if ($status === 'succeeded') {

            if ($payment->paid_at) {
                return;
            }

            $payment->paid_at = now();
            $payment->status = $status;
            $payment->save();

            // There should only be one invoice because electronic payments are per invoice and cannot have a custom amount.
            $payment->invoices->each(function ($invoice) {
                $invoice->paid = true;
                $invoice->payment_is_pending = false;
                $invoice->save();
            });
        }
    }
}
