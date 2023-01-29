<?php

namespace TijmenWierenga\LaravelChargebee\Concerns;

use ChargeBee\ChargeBee\Models\HostedPage;
use ChargeBee\ChargeBee\Models\Subscription as ChargebeeSubscription;
use TijmenWierenga\LaravelChargebee\Cashier;
use TijmenWierenga\LaravelChargebee\Exceptions\MissingPlanException;
use TijmenWierenga\LaravelChargebee\Subscription;

trait ManagesSubscriptions
{
    public function updateSubscription(Subscription $subscription, array $params = []): ChargebeeSubscription
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

    public function getHostedPageForNewSubscription(array $params = [])
    {
        return HostedPage::checkoutNewForItems($params)->hostedPage()->url;
    }

    public function getHostedPageToUpdateSubscription(array $params = [])
    {
        return HostedPage::checkoutExistingForItems($params)->hostedPage();
    }
}
