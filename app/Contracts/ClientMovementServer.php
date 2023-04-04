<?php

namespace App\Contracts;

interface ClientMovementServer
{
    /**
     * @param string $userId
     * @param string $invoiceNumber
     * @param float $totalAmount
     * @param string $totalCurrencyCode
     * @return string Client Movement ID
     */
    public function save(
        string $userId,
        string $invoiceNumber,
        float $totalAmount,
        string $totalCurrencyCode
    ): string;
}
