<?php

namespace TijmenWierenga\LaravelChargebee\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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
        'customer_created' => 'handleCustomer',
        'customer_changed' => 'handleCustomer',
    ];

    public function handleWebhook(Request $request)
    {
        $handler = self::$handlers[$request->event_type] ?? null;
        $webhookEvent = Str::studly($request->event_type);
        $payload = json_decode(json_encode($request->input('content')));

//        if (isset($payload->subscription) && isset($payload->subscription->status)) {
//            $this->handleSubscription($payload);
//        }
//        if (isset($payload->transaction) && isset($payload->transaction->status)) {
//            $this->handlePayment($payload);
//        }
//        if (isset($payload->customer) && isset($payload->customer->id)) {
//            $this->handleCustomer($payload);
//        }

        try {
            if (!empty($handler) && method_exists($this, $handler)) {
                $this->{self::$handlers[$request->event_type]}($payload);
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            throw $e;
        }


        return response("event " . $webhookEvent, 200);
    }

    /**
     * @param $payload
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function handleSubscription($payload)
    {
        Log::info("handleSubscriotion");
        /** @var ChargebeeSubscription $subscription */
        $subscription = $this->getSubscription($payload->subscription->id);

        if ($subscription) {
            if ($payload->subscription->status == ChargebeeSubscription::STATUS_CANCELED) {
                $subscription->ends_at = $payload->subscription->current_term_end;
            }
            $subscription->status = $payload->subscription->status;
            if ($payload->subscription->trial_end) {
                $subscription->trial_ends_at = $payload->subscription->trial_end;
            }
            if ($payload->subscription->next_billing_at) {
                $subscription->next_billing_at = $payload->subscription->next_billing_at;
            }
            if ($payload->subscription->customer_id) {
                $subscription->customer_id = $payload->subscription->customer_id;
            }
            if ($payload->subscription->subscription_items) {
                foreach ($payload->subscription->subscription_items as $subscription_item) {
                    if ($subscription_item->item_type == 'plan') {
                        $subscription->plan_id = $subscription_item->item_price_id;
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
        Log::info("handlePayment");
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

    private function handleCustomer($payload)
    {
        $customerId = null;
        if (isset($payload->customer) && isset($payload->customer->id)) {
            $customerId = $payload->customer->id;
        }

        Log::info("handleCustomer $customerId");

        $customer = (new Cashier::$customerModel)->where('chargebee_id', $customerId)->firstOrFail();

        if (isset($payload->customer->billing_address->email)) {
            $customer->email = $payload->customer->billing_address->email;
        }
        if (isset($payload->customer->billing_address->company)) {
            $customer->name = $payload->customer->billing_address->company;
        }
        if (isset($payload->customer->vat_number)) {
            $customer->vat_number = $payload->customer->vat_number;
            $customer->chargebee_vat_number = $payload->customer->vat_number;
        }
        if (isset($payload->customer->vat_number_status)) {
            $customer->vat_number_status = $payload->customer->vat_number_status;
        }
        if (isset($payload->customer->billing_address->first_name)) {
            $customer->first_name = $payload->customer->billing_address->first_name;
        }
        if (isset($payload->customer->billing_address->last_name)) {
            $customer->last_name = $payload->customer->billing_address->last_name;
        }
        if (isset($payload->customer->billing_address->line1)) {
            $customer->address_line_1 = $payload->customer->billing_address->line1;
        }
        if (isset($payload->customer->billing_address->line2)) {
            $customer->address_line_2 = $payload->customer->billing_address->line2;
        }
        if (isset($payload->customer->billing_address->city)) {
            $customer->city = $payload->customer->billing_address->city;
        }
        if (isset($payload->customer->billing_address->country)) {
            $customer->country_iso2 = $payload->customer->billing_address->country;
        }
        if (isset($payload->customer->billing_address->zip)) {
            $customer->postal_code = $payload->customer->billing_address->zip;
        }
        if (isset($payload->customer->billing_address->validation_status)) {
            $customer->address_validation_status = $payload->customer->billing_address->validation_status;
        }
        if (isset($payload->customer->billing_address->state)) {
            $customer->state = $payload->customer->billing_address->state;
        }
        if (isset($payload->customer->vat_number_prefix)) {
            $customer->chargebee_vat_number_prefix = $payload->customer->vat_number_prefix;
        }

        $customer->save();
    }
}
