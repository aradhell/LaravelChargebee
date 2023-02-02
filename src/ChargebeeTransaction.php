<?php

namespace TijmenWierenga\LaravelChargebee;

use Illuminate\Database\Eloquent\Model;

class ChargebeeTransaction extends Model
{
    /**
     * TijmenWierenga\LaravelChargebee\ChargebeeTransaction
     *
     * @mixin \Eloquent
     * @property int $id
     * @property int $customer_id
     * @property int $amount
     * @property string $base_currency_code
     * @property string $currency_code
     * @property string $payment_date
     * @property string $gateway
     * @property string $payment_method
     * @property string $status
     * @property string $type
     * @property bool $deleted
     * @property float $exchange_rate
     * @property string $transaction_id
     * @property string $subscription_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     */

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'amount',
        'base_currency_code',
        'currency_code',
        'payment_date',
        'gateway',
        'payment_method',
        'status',
        'type',
        'deleted',
        'exchange_rate',
        'payment_id',
        'subscription_id',
        'created_at',
        'updated_at',
    ];

    public function customer()
    {
        return $this->belongsTo(Cashier::$customerModel, 'customer_id', 'chargebee_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Cashier::$subscriptionModel, 'subscription_id', 'subscription_id');
    }
}
