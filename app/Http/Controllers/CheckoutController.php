<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StripePaymentMethod;
use App\Services\OrganizationService;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function paymentMethods(Request $request)
    {
        Gate::authorize('viewAnyInOrganization', Invoice::class);

        $organization = OrganizationService::getOrganization($request);
        $organization->load('stripePaymentMethods');

        return Inertia::render('PaymentMethods', [
            'organization' => $organization,
        ]);
    }

    public function pay(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $paymentMethod = StripePaymentMethod::findOrFail(request()->payment_method_id);

        if (
            $invoice->paid
            || $invoice->payment_is_pending
            || $paymentMethod->organization_id !== $invoice->organization_id
            || $invoice->amount_due <= 0
        ) {
            abort(400);
        }

        $localDate = (new Carbon($invoice->issue_at))->setTimezone('America/Chicago')->toDateString();

        $paymentAmount = $paymentMethod->type === 'us_bank_account' ? $invoice->amount_due - $invoice->pay_by_bank_discount : $invoice->amount_due + intval(request()->processing_fee);

        try {
            $stripe = new \Stripe\StripeClient(config('app.stripe_key'));
            $paymentIntent = $stripe->paymentIntents->create([
                'customer' => $invoice->organization->stripe_customer_id,
                'description' => "Invoice #{$invoice->number}",
                'amount' => $paymentAmount,
                'currency' => 'usd',
                'payment_method' => $paymentMethod->payment_method_id,
                'payment_method_types' => [
                    'card',
                    'us_bank_account',
                ],
                'receipt_email' => $invoice->organization->email,
            ]);

            $stripe->paymentIntents->confirm($paymentIntent->id);

            $invoice->payments()->create([
                'paid_at' => $paymentMethod->type === 'card' ? now() : null,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $paymentAmount,
                'description' => "Invoice #{$invoice->number}",
                'organization_id' => $invoice->organization_id,
            ]);

            $invoice->processing_fee = request()->processing_fee;
            $invoice->amount_discounted = $paymentMethod->type === 'us_bank_account' ? $invoice->pay_by_bank_discount : 0;
            $invoice->total = $paymentAmount;

            if ($paymentMethod->type === 'card') {
                $invoice->paid = true;
                $invoice->payment_is_pending = false;
            } else {
                $invoice->payment_is_pending = true;
            }

            $invoice->save();

            return redirect()->route('invoices.show', $invoice->id)->with('message', $paymentMethod->type === 'card' ? 'Thank you for your payment!' : 'Thank you for submitting your payment. Please allow 4 business days to complete the payment.');
        } catch (Error $e) {
            return redirect()->route('invoices.show', $invoice->id)->with('error', 'Unable to complete payment.');
        }
    }

    public function stripe(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        if ($invoice->paid) {
            abort(400);
        }

        $amountDue = $invoice->total;

        if ($invoice->payments->count()) {
            foreach ($invoice->payments as $payment) {
                $amountDue -= $payment->getRelationValue('pivot')->amount;
            }
        }

        if ($amountDue <= 0) {
            abort(500);
        }

        \Stripe\Stripe::setApiKey(config('app.stripe_key'));
        header('Content-Type: application/json');

        $checkout_session = \Stripe\Checkout\Session::create([
            'customer' => $invoice->organization->stripe_customer_id,
            'line_items' => [[
                // Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Invoice #' . str_pad((string) $invoice->number, 4, '0', STR_PAD_LEFT),
                        'description' => "Invoice for {$invoice->organization->name} (" . (new Carbon($invoice->issue_at))->setTimezone('America/Chicago')->format('F j, Y') . ')',
                        'metadata' => [
                            'invoice_id' => $invoice->id,
                        ],
                    ],
                    'unit_amount' => $amountDue,
                ],
                'quantity' => 1,
            ]],
            'payment_method_types' => [
                // 'card',
                'us_bank_account',
            ],
            'payment_method_options' => [
                'us_bank_account' => [
                    'financial_connections' => [
                        'permissions' => [
                            'payment_method',
                        ],
                    ],
                ],
            ],
            'mode' => 'payment',
            'success_url' => route('invoices.paymentSuccess', ['invoice' => $invoice->id, 'session_id' => '']) . '{CHECKOUT_SESSION_ID}',
            // 'success_url' => "http://example.com/order/success?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => url()->previous(),
        ]);

        // header("HTTP/1.1 303 See Other");
        // header("Location: " . $checkout_session->url);
        return redirect($checkout_session->url);
    }

    public function paymentSuccess(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $stripe = new \Stripe\StripeClient(config('app.stripe_key'));

        try {
            $session = $stripe->checkout->sessions->retrieve(request()->session_id);
            $customer = $stripe->customers->retrieve($session->customer);
        } catch (Error $e) {
            abort(500, $e->getMessage());
        }

        $payment = Payment::create([
            'status' => 'pending',
            'payment_intent_id' => $session->payment_intent,
            'amount' => $session->amount_total,
            'organization_id' => $invoice->organization_id,
        ]);

        $payment->invoices()->attach($invoice->id);
        $payment->invoices()->updateExistingPivot($invoice->id, ['amount' => $session->amount_total]);

        $invoice->payment_is_pending = true;
        $invoice->save();

        $localDate = (new Carbon($invoice->issue_at))->setTimezone('America/Chicago')->toDateString();
        $message = 'Thank you! Your payment was submitted successfully. Please allow up to 4 days for your payment complete.';

        return redirect()->route('invoices.show', $invoice->id)->with('message', $message);
    }

    public function createPaymentMethod()
    {
        Gate::authorize('viewAnyInOrganization', Invoice::class);

        $organization = auth()->user()->organizations->first();

        if (! $organization->stripe_customer_id) {
            abort(400);
        }

        \Stripe\Stripe::setApiKey(config('app.stripe_key'));
        header('Content-Type: application/json');

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['us_bank_account', 'card'],
            'mode' => 'setup',
            'customer' => $organization->stripe_customer_id,
            'success_url' => route('paymentMethods.createSuccess', ['redirectAfter' => request()->redirectAfter, 'session_id' => '']) . '{CHECKOUT_SESSION_ID}',
            'cancel_url' => url()->previous(),
        ]);

        return redirect($session->url);
    }

    public function createPaymentMethodSuccess()
    {
        Gate::authorize('viewAnyInOrganization', Invoice::class);

        $organization = auth()->user()->organizations->first();

        if (! $organization->stripe_customer_id) {
            abort(400);
        }

        $stripe = new \Stripe\StripeClient(config('app.stripe_key'));

        try {
            $session = $stripe->checkout->sessions->retrieve(request()->session_id, ['expand' => ['setup_intent', 'setup_intent.payment_method']]);
            $paymentMethod = $session->setup_intent->payment_method; // Use for charging customer in the future;

            $organization->stripePaymentMethods()->create([
                'payment_method_id' => $paymentMethod->id,
                'name' => $paymentMethod->type === 'card' ? ucwords($paymentMethod->card->brand) : $paymentMethod->us_bank_account->bank_name,
                'exp_month' => $paymentMethod->type === 'card' ? $paymentMethod->card->exp_month : null,
                'exp_year' => $paymentMethod->type === 'card' ? $paymentMethod->card->exp_year : null,
                'last4' => $paymentMethod->type === 'card' ? $paymentMethod->card->last4 : $paymentMethod->us_bank_account->last4,
                'type' => $paymentMethod->type,
            ]);

            if (request()->redirectAfter) {
                return redirect(request()->redirectAfter)->with('message', 'Payment method added successfully!');
            }

            return redirect()->route('paymentMethods.index')->with('message', 'Payment method added successfully!');
        } catch (Error $e) {
            // abort(500, $e->getMessage());
            if (request()->redirectAfter) {
                return redirect(request()->redirectAfter)->with('error', 'Unable to add payment method!');
            }

            return redirect()->route('paymentMethods.index')->with('error', 'Unable to add payment method!');
        }

    }

    public function deletePaymentMethod(StripePaymentMethod $paymentMethod)
    {
        Gate::authorize('viewAnyInOrganization', Invoice::class);

        $organization = auth()->user()->organizations->first();

        if ($paymentMethod->organization_id !== $organization->id) {
            abort(403);
        }

        $stripe = new \Stripe\StripeClient(config('app.stripe_key'));

        try {
            $stripe->paymentMethods->detach($paymentMethod->payment_method_id);
            $paymentMethod->delete();

            return redirect()->route('paymentMethods.index')->with('message', 'Payment method deleted successfully!');
        } catch (Error $e) {
            return redirect()->route('paymentMethods.index')->with('error', 'Unable to delete payment method');
        }
    }
}
