<?php

use App\MockBuyer;
use App\MockOrder;
use App\ShippingService;
use App\ShippingServiceMockHttpClient;

require __DIR__ . '/vendor/autoload.php';

$order = new MockOrder();
$order->load();

$buyer = new MockBuyer();

$shippingService = new ShippingService(
    new ShippingServiceMockHttpClient()
);

$trackingNumber = $shippingService->ship($order, $buyer);

echo 'Tracking number: ' . $trackingNumber . "\n";
