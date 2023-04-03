<?php

declare(strict_types=1);

namespace App\Models\DataTransferModels;

class InvoiceItemDetails
{
    protected string $id;
    protected float $subtotalAmount;
    protected string $subtotalCurrencyCode;

    /**
     * @param string $id
     * @param float $subtotalAmount
     * @param string $subtotalCurrencyCode
     */
    public function __construct(string $id, float $subtotalAmount, string $subtotalCurrencyCode)
    {
        $this->id = $id;
        $this->subtotalAmount = $subtotalAmount;
        $this->subtotalCurrencyCode = $subtotalCurrencyCode;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getSubtotalAmount(): float
    {
        return $this->subtotalAmount;
    }

    /**
     * @return string
     */
    public function getSubtotalCurrencyCode(): string
    {
        return $this->subtotalCurrencyCode;
    }
}
