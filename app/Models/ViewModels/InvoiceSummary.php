<?php

namespace App\Models\ViewModels;

class InvoiceSummary {
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
    )
    {
        $this->userId = $userId;
        $this->invoiceId = $invoiceId;
        $this->invoiceNumber = $invoiceNumber;
        $this->totalAmount = $totalAmount;
        $this->totalCurrencyCode = $totalCurrencyCode;
    }

    public function normalize(): array
    {
        return [
            'user_id' => $this->userId,
            'invoice_id' => $this->invoiceId,
            'invoice_number' => $this->invoiceNumber,
            'total_amount' => $this->totalAmount,
            'total_currency_code' => $this->totalCurrencyCode,
        ];
    }
}