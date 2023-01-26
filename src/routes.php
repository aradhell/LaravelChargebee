<?php

use TijmenWierenga\LaravelChargebee\Http\Controllers\WebhookController;

Route::post('/chargebee/webhook', [WebhookController::class, 'handleWebhook'])->name('chargebee.webhook');