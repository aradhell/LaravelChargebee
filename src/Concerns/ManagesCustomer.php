<?php

namespace TijmenWierenga\LaravelChargebee\Concerns;

use ChargeBee\ChargeBee\Models\Customer as ChargebeeCustomer;
use TijmenWierenga\LaravelChargebee\Exceptions\CustomerAlreadyCreated;
use TijmenWierenga\LaravelChargebee\Exceptions\InvalidCustomer;

trait ManagesCustomer
{
    public function createOrGetStripeCustomer(array $options = []): ChargebeeCustomer
    {
        if ($this->hasChargebeeId()) {
            return $this->asChargebeeCustomer();
        }

        return $this->createAsChargebeeCustomer($options);
    }

    public function asChargebeeCustomer(array $expand = []): ChargebeeCustomer
    {
        $this->assertCustomerExists();

        return ChargebeeCustomer::retrieve($this->chargebee_id)->customer();
    }

    protected function assertCustomerExists()
    {
        if (! $this->hasChargebeeId()) {
            throw InvalidCustomer::notYetCreated($this);
        }
    }

    public function createAsChargebeeCustomer(array $options): ChargebeeCustomer
    {
        if ($this->hasChargebeeId()) {
            throw CustomerAlreadyCreated::exists($this);
        }

        if (! array_key_exists('firstName', $options) && $name = $this->chargebeeFirstName()) {
            $options['firstName'] = $name;
        }

        if (! array_key_exists('lastName', $options) && $lastName = $this->chargebeeLastName()) {
            $options['lastName'] = $lastName;
        }

        if (! array_key_exists('email', $options) && $email = $this->chargebeeEmail()) {
            $options['email'] = $email;
        }

        if (! array_key_exists('phone', $options) && $phone = $this->chargebeePhone()) {
            $options['phone'] = $phone;
        }

        if (! array_key_exists('billingAddress', $options) && $address = $this->chargebeeAddress()) {
            $options['billingAddress'] = $address;
        }

        if (! array_key_exists('locale', $options) && $locales = $this->chargebeeLocale()) {
            $options['locale'] = $locales;
        }

        // Here we will create the customer instance on Stripe and store the ID of the
        // user from Stripe. This ID will correspond with the Stripe user instances
        // and allow us to retrieve users from Stripe later when we need to work.
        $customer = ChargebeeCustomer::create($options);

        $this->chargebee_id = $customer->customer()->id;

        $this->save();

        return $customer->customer();
    }

    public function chargebeeFirstName()
    {
        $this->first_name;
    }

    public function chargebeeLastName()
    {
        return $this->last_name;
    }

    public function chargebeeEmail()
    {
        return $this->email;
    }

    public function chargebeePhone()
    {
        return $this->phone;
    }

    public function chargebeeAddress()
    {
//        return array(
//            "firstName" => "John",
//            "lastName" => "Doe",
//            "line1" => "PO Box 9999",
//            "city" => "Walnut",
//            "state" => "California",
//            "zip" => "91789",
//            "country" => "US"
//        );
    }

    public function chargebeeLocale()
    {
        return $this->chargebeeLocale;
    }

    public function hasChargebeeId()
    {
        return $this->chargebee_id;
    }
}
