<?php

declare(strict_types=1);

namespace App\Models\DataTransferModels;

class InvoiceSummary
{
    protected string $userId;
    protected string $invoiceId;
    protected string $invoiceNumber;
    protected float $totalAmount;
    protected string $totalCurrencyCode;

    public function __construct(
        string $userId,
        string $invoiceId,
        string $invoiceNumber,
        float $totalAmount,
        string $totalCurrencyCode
    ) {
        $this->userId = $userId;
        $this->invoiceId = $invoiceId;
        $this->invoiceNumber = $invoiceNumber;
        $this->totalAmount = $totalAmount;
        $this->totalCurrencyCode = $totalCurrencyCode;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getInvoiceId(): string
    {
        return $this->invoiceId;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    /**
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * @return string
     */
    public function getTotalCurrencyCode(): string
    {
        return $this->totalCurrencyCode;
    }
}
