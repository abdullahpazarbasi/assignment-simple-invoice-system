<?php

namespace App\Contracts;

interface ClientMovementRepository
{
    /**
     * @param string $clientNumber
     * @param string $invoiceNumber
     * @param float $totalAmount
     * @param string $totalCurrencyCode
     * @return string Client Movement ID
     */
    public function upsert(
        string $clientNumber,
        string $invoiceNumber,
        float $totalAmount,
        string $totalCurrencyCode
    ): string;
}
