<?php

namespace TijmenWierenga\LaravelChargebee\Concerns;

use ChargeBee\ChargeBee\Models\HostedPage;
use ChargeBee\ChargeBee\Models\Subscription as ChargebeeSubscription;
use TijmenWierenga\LaravelChargebee\Cashier;

trait ManagesSubscriptions
{
    public function updateSubscription(string $subscriptionId, array $params = []): ChargebeeSubscription
    {
        return ChargebeeSubscription::update($subscriptionId, $params)->subscription();
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
        return HostedPage::checkoutNewForItems($params)->hostedPage();
    }

    public function getHostedPageToUpdateSubscription(array $params = [])
    {
        return HostedPage::checkoutExistingForItems($params)->hostedPage();
    }
}
