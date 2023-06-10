<?php

declare(strict_types=1);

namespace App;

class Address
{
    protected string $name;
    protected string $address;
    protected string $city;

    protected string $state;
    protected string $postalCode;
    protected string $country;

    public function __construct(string $multilineAddress)
    {
        $parts = explode("\n", $multilineAddress);

        $this->name = $parts[0];
        $this->address = $parts[1];
        $this->city = $parts[2];
        $this->state = $parts[3];

        $line5 = explode(' ', $parts[4]);

        $this->postalCode = $line5[0];
        $this->country = implode(' ', array_slice($line5, 1, count($line5)));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}
