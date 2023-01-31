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

    private static $handlers = [
        'subscription_created' => 'handleSubscription',
        'subscription_started' => 'handleSubscription',
        'subscription_activated' => 'handleSubscription',
        'subscription_changed' => 'handleSubscription',
        'subscription_trial_extended' => 'handleSubscription',
        'subscription_cancelled' => 'handleSubscription',
        'subscription_reactivated' => 'handleSubscription',
        'subscription_renewed' => 'handleSubscription',
        'subscription_deleted' => 'handleSubscription',
        'subscription_paused' => 'handleSubscription',
        'subscription_resumed' => 'handleSubscription',
        'payment_succeeded' => 'handlePayment',
        'payment_failed' => 'handlePayment',
        'payment_refunded' => 'handlePayment',
        'payment_initiated' => 'handlePayment',
    ];

    public function handleWebhook(Request $request)
    {
        $handler = self::$handlers[$request->event_type] ?? null;
        $webhookEvent = Str::studly($request->event_type);
        $payload = json_decode(json_encode($request->input('content')));

        if (isset($payload->subscription) && isset($payload->subscription->status)) {
            $this->handleSubscription($payload);
        } elseif (isset($payload->transaction) && isset($payload->transaction->status)) {
            $this->handlePayment($payload);
        }

        if (!empty($handler) && method_exists($this, $handler)) {
            $this->{self::$handlers[$request->event_type]}($payload);
        }

        return response("event " . $webhookEvent, 200);
    }

    /**
     * @param $payload
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function handleSubscription($payload)
    {
        /** @var ChargebeeSubscription $subscription */
        $subscription = $this->getSubscription($payload->subscription->id);

        if ($subscription) {
            if ($payload->subscription->status == ChargebeeSubscription::STATUS_CANCELED) {
                $subscription->ends_at = $payload->subscription->current_term_end;
            }
            $subscription->status = $payload->subscription->status;
            if ($payload->subscription->trial_ends_at) {
                $subscription->trial_ends_at = $payload->subscription->trial_end;
            }
            if ($payload->subscription->next_billing_at) {
                $subscription->next_billing_at = $payload->subscription->next_billing_at;
            }
            if ($payload->subscription->customer_id) {
                $subscription->owner_id = $payload->subscription->customer_id;
            }
            if ($payload->subscription->subscription_items) {
                foreach ($payload->subscription->subscription_items as $subscription_item) {
                    if ($subscription_item['item_type'] == 'plan') {
                        $subscription->plan_id = $subscription_item['item_price_id'];
                    }
                }
            }

            $subscription->save();
        }
    }

    /**
     * @param $payload
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function handlePayment($payload)
    {
        $subscriptionId = null;
        if (isset($payload->subscription) && isset($payload->subscription->id)) {
            $subscriptionId = $payload->subscription->id;
        }

        ChargebeeTransaction::updateOrCreate(
            [
                'transaction_id' => $payload->transaction->id,
            ],
            [
                'customer_id' => $payload->transaction->customer_id,
                'amount' => $payload->transaction->amount,
                'base_currency_code' => $payload->transaction->base_currency_code,
                'currency_code' => $payload->transaction->currency_code,
                'payment_date' => Carbon::parse($payload->transaction->date),
                'gateway' => $payload->transaction->gateway,
                'payment_method' => $payload->transaction->payment_method,
                'status' => $payload->transaction->status,
                'type' => $payload->transaction->type,
                'deleted' => $payload->transaction->deleted,
                'exchange_rate' => $payload->transaction->exchange_rate,
                'subscription_id' => $subscriptionId ?? null,
            ]
        );
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
