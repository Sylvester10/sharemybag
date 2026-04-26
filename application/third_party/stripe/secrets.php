<?php
// Stripe publishable key — loaded from environment.
// Set STRIPE_PUBLISHABLE_KEY in your .env file (never hardcode here).
$stripeSecretKey = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '';