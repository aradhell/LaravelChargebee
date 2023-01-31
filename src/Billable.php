<?php
namespace TijmenWierenga\LaravelChargebee;

use ChargeBee\ChargeBee\Environment;
use TijmenWierenga\LaravelChargebee\Concerns\ManagesCustomer;
use TijmenWierenga\LaravelChargebee\Concerns\ManagesSubscriptions;
use TijmenWierenga\LaravelChargebee\Concerns\ManagesTransactions;

/**
 * Class Billable
 * @package TijmenWierenga\LaravelChargebee
 */
trait Billable
{
    use ManagesCustomer;
    use ManagesSubscriptions;
    use ManagesTransactions;

    public function __construct()
    {
    }

    /**
     * @param null $plan
     * @return Subscriber
     */
    public function subscription($plan = null)
    {
        return new Subscriber($this, $plan);
    }

    /**
     * @return mixed
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
