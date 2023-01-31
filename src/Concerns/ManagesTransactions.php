<?php

namespace TijmenWierenga\LaravelChargebee\Concerns;

use ChargeBee\ChargeBee\Models\Customer;
use ChargeBee\ChargeBee\Models\Customer as ChargebeeCustomer;
use ChargeBee\ChargeBee\Models\Transaction;
use TijmenWierenga\LaravelChargebee\Exceptions\CustomerAlreadyCreated;
use TijmenWierenga\LaravelChargebee\Exceptions\InvalidCustomer;

trait ManagesTransactions
{
    public function chargebeeTransactions(array $options = []): ChargebeeCustomer
    {
        return Transaction::all($options);
    }
}
