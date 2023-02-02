<?php
namespace TijmenWierenga\LaravelChargebee;


use Illuminate\Database\Eloquent\Model;

class ChargebeeAddon extends Model
{
    protected $fillable = ['addon_id', 'quantity', 'name', 'chargebee_subscription_id'];
}
