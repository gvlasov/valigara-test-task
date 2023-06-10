<?php

namespace App;


use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ShippingServiceMockHttpClient extends Client
{

    public function __construct()
    {
        parent::__construct([
            'handler' => HandlerStack::create(
                new MockHandler([
                    new Response(200, [], ''),
                    new Response(
                        200,
                        [],
                        '{"payload":{"fulfillmentShipments":[{"fulfillmentShipmentPackage":[{"trackingNumber":"12345"}]}]}}'
                    ),
                ])
            )
        ]);
    }

}