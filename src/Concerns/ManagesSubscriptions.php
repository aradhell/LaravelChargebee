<?php

use ChargeBee\ChargeBee\Models\Subscription as ChargebeeSubscription;
use TijmenWierenga\LaravelChargebee\Cashier;
use TijmenWierenga\LaravelChargebee\Subscription;

trait ManagesSubscriptions
{
    public function update(Subscription $subscription, array $params = []): ChargebeeSubscription
    {
        return ChargebeeSubscription::update($subscription->subscription_id, $params)->subscription();
    }

    /**
     * Get all of the subscriptions for the Stripe model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Cashier::$subscriptionModel, $this->getForeignKey())->orderBy('created_at', 'desc');
    }
}
