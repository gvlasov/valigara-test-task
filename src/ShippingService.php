<?php

declare(strict_types=1);

namespace App;

use App\Data\AbstractOrder;
use App\Data\BuyerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use function Functional\map;

class ShippingService implements ShippingServiceInterface
{
    public function __construct(
        protected ClientInterface $client
    ) {
    }

    /**
     * @throws RuntimeException
     */
    public function ship(AbstractOrder $order, BuyerInterface $buyer): string
    {
        try {
            $createFulfillmentOrderResp = $this->client->post(
                '/fba/outbound/2020-07-01/fulfillmentOrders',
                [
                    'headers' => ['content-type' => 'application/json'],
                    'json' => $this->getFulfilmentOrderPayload($order, $buyer)
                ],
            );
        } catch (GuzzleException $e) {
            throw new RuntimeException('Could not create fulfilment order', 0, $e);
        }

        if ($createFulfillmentOrderResp->getStatusCode() !== 200) {
            throw new RuntimeException(sprintf(
                'POST fulfillmentOrders orderID=%s, http status: %s, body: %s',
                $order->getOrderId(),
                $createFulfillmentOrderResp->getStatusCode(),
                $createFulfillmentOrderResp->getBody()
            ));
        }

        try {
            $getFulfillmentOrderResp = $this->client->get(sprintf(
                '/fba/outbound/2020-07-01/fulfillmentOrders/%s',
                $order->getOrderId(),
            ));
        } catch (GuzzleException $e) {
            throw new RuntimeException('Could not get fulfillment order', 0, $e);
        }

        if ($getFulfillmentOrderResp->getStatusCode() !== 200) {
            throw new RuntimeException(sprintf(
                'GET fulfillmentOrders orderID=%s, http status: %s, body: %s',
                $order->getOrderId(),
                $getFulfillmentOrderResp->getStatusCode(),
                $getFulfillmentOrderResp->getBody()
            ));
        }

        $jsonBody = json_decode((string) $getFulfillmentOrderResp->getBody(), true);
        if ($jsonBody === null) {
            throw new RuntimeException(sprintf(
                'GET fulfillmentOrders orderID=%s invalid response body : %s',
                $order->getOrderId(),
                $jsonBody
            ));
        }

        return $jsonBody['payload']['fulfillmentShipments'][0]['fulfillmentShipmentPackage'][0]['trackingNumber'];
    }

    protected function getFulfilmentOrderPayload(AbstractOrder $order, BuyerInterface $buyer): array
    {
        $destination = new Address($order->data['shipping_adress']);
        return [
            'items' => map(
                $order->data['products'],
                function($product) {
                    return [
                        'sellerSku' => $product['sku'],
                        'sellerFulfillmentOrderItemId' => $product['sku'],
                        'quantity' => (int) $product['ammount'],
                    ];
                }
            ),
            'sellerFulfillmentOrderId' => $order->data['order_unique'],
            'displayableOrderId' => (string) $order->getOrderId(),
            'displayableOrderDate' => $order->data['order_date'],
            'displayableOrderComment' => $order->data['comments'],
            'shippingSpeedCategory' => match ((int)$order->data['shipping_type_id']) {
                1 => 'Standard',
                2 => 'Expedited',
                3 => 'Priority',
                7 => 'ScheduledDelivery',
                default => throw new RuntimeException('Invalid shipping_speed_type_id ' . var_export($order->data['shipping_type_id'], true))
            },
            'destinationAddress' => [
                'name' => $destination->getName(),
                'addressLine1' => $destination->getAddress(),
                'city' => $destination->getCity(),
                'districtOrCounty' => $destination->getCountry(),
                'stateOrRegion' => $destination->getState(),
                'postalCode' => $destination->getPostalCode(),
                'countryCode' => $order->data['shipping_country'],
                'phone' => $buyer->phone,
            ],
            'fulfillmentAction' => 'Ship',
            'notificationEmails' => [
                $buyer->email,
            ],
        ];
    }

}
