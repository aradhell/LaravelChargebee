<?php
namespace TijmenWierenga\LaravelChargebee;

use Illuminate\Support\Carbon;

/**
 * This trait handles webhooks coming from Chargebee
 *
 * Class HandlesWebhooks
 * @package TijmenWierenga\LaravelChargebee
 */
trait HandlesWebhooks
{
    /**
     * Cancel a subscription instantly.
     *
     * Compatible with the following webhooks:
     * subscription_cancelled
     *
     * @return $this
     */
    public function updateCancellationDate($date = null)
    {
        $this->ends_at = ($date) ?: Carbon::now();
        $this->save();

        return $this;
    }

    public function updateStatus(string $status)
    {
        $this->status = $status;
        $this->save();

        return $this;
    }
}
