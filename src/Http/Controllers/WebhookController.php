<?php

namespace TijmenWierenga\LaravelChargebee\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use TijmenWierenga\LaravelChargebee\Cashier;
use TijmenWierenga\LaravelChargebee\ChargebeeSubscription;
use TijmenWierenga\LaravelChargebee\ChargebeeTransaction;

/**
 * Class WebhookController
 * @package TijmenWierenga\LaravelChargebee\Http\Controllers
 */
class WebhookController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function handleWebhook(Request $request)
    {
        $webhookEvent = Str::studly($request->event_type);

        $payload = json_decode(json_encode($request->input('content')));

        if (method_exists($this, 'handle' . $webhookEvent)) {

            if (isset($payload->subscription) && isset($payload->subscription->status)) {
                /** @var ChargebeeSubscription $subscription */
                $subscription = $this->getSubscription($payload->subscription->id);

                if ($subscription) {
                    $subscription->updateStatus($payload->subscription->status);
                }
            }

            return $this->{'handle' . $webhookEvent}($payload);
        } else {
            return response("No event handler for " . $webhookEvent, 200);
        }
    }

    /**
     * @param $payload
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function handleSubscriptionCancelled($payload)
    {
        /** @var ChargebeeSubscription $subscription */
        $subscription = $this->getSubscription($payload->subscription->id);

        if ($subscription) {
            $subscription->updateCancellationDate($payload->subscription->cancelled_at);
        }

        return response("Webhook handled successfully.", 200);
    }

    /**
     * @param $payload
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function handlePaymentSucceeded($payload)
    {
        $subscriptionId = null;
        if (isset($payload->subscription) && isset($payload->subscription->id)) {
            $subscriptionId = $payload->subscription->id;
        }
        $subscription = $this->getSubscription($subscriptionId);

        if ($subscription) {
            $subscription->ends_at = $payload->subscription->current_term_end;
            $subscription->save();
        }

        ChargebeeTransaction::updateOrCreate(
            [
                'transaction_id' => $payload->transaction->id,
            ],
            [
                'customer_id' => $payload->transaction->customer_id,
                'amount' => $payload->transaction->amount,
                'base_currency_code' => $payload->transaction->base_currency_code,
                'currency_code' => $payload->transaction->base_currcurrency_codeency_code,
                'payment_date' => Carbon::parse($payload->transaction->date),
                'gateway' => $payload->transaction->gateway,
                'payment_method' => $payload->transaction->payment_method,
                'status' => $payload->transaction->status,
                'type' => $payload->transaction->type,
                'deleted' => $payload->transaction->deleted,
                'exchange_rate' => $payload->transaction->exchange_rate,
                'subscription_id' => $payload->transaction->subscription_id ?? null,
            ]
        );

        return response("Webhook handled successfully.", 200);
    }

    /**
     * @param string|null $subscriptionId
     * @return mixed
     */
    protected function getSubscription(?string $subscriptionId): ?Model
    {
        if (empty($subscriptionId)) {
            return null;
        }
        $subscription = (new Cashier::$subscriptionModel)->where('subscription_id', $subscriptionId)->first();

        return $subscription;
    }
}
