<?php
namespace TijmenWierenga\LaravelChargebee;

use ChargeBee\ChargeBee\Environment;
use ManagesCustomer;
use ManagesSubscriptions;

/**
 * Class Billable
 * @package TijmenWierenga\LaravelChargebee
 */
trait Billable
{
    use ManagesCustomer;
    use ManagesSubscriptions;

    public function __construct()
    {
        Environment::configure(
            env('CHARGEBEE_SITE'),
            env('CHARGEBEE_KEY')
        );
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
