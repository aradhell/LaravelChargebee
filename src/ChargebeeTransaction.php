<?php

namespace TijmenWierenga\LaravelChargebee;

use Illuminate\Database\Eloquent\Model;

class ChargebeeTransaction extends Model
{
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
