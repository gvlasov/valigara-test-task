<?php

namespace App;

use App\Data\AbstractOrder;

class MockOrder extends AbstractOrder
{

    public function __construct()
    {
        parent::__construct(16400);
    }

    protected function loadOrderData(int $id): array
    {
        return json_decode(
            file_get_contents(
                __DIR__ . "/../mock/order.$id.json"
            ),
            true
        );
    }
}
