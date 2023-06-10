<?php

namespace App;

use App\Data\BuyerInterface;
use ArrayObject;

class MockBuyer extends ArrayObject implements BuyerInterface
{

    public function __construct()
    {
        parent::__construct(
            json_decode(
                file_get_contents(
                    __DIR__ . "/../mock/buyer.29664.json"
                ),
                true
            )
        );
    }

    public function __get(string $name)
    {
        return $this[$name];
    }

}
