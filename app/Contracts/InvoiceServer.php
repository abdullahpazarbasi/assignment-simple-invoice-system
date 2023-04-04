<?php

namespace App\Contracts;

use App\Models\DataTransferModels\InvoiceDetails;
use App\Models\DataTransferModels\InvoiceSummary;
use Throwable;

interface InvoiceServer
{
    /**
     * @param string $userId
     * @return InvoiceDetails[]
     */
    public function list(string $userId): array;

    public function get(string $invoiceId): InvoiceDetails;

    /**
     * @param string $userId
     * @param string $invoiceNumber
     * @param string $subtotalAmount0
     * @param string $subtotalCurrencyCode0
     * @return string Invoice ID
     * @throws Throwable
     */
    public function store(
        string $userId,
        string $invoiceNumber,
        string $subtotalAmount0,
        string $subtotalCurrencyCode0
    ): string;

    /**
     * @param string $userId
     * @param string $invoiceId
     * @param string $invoiceNumber
     * @param string|null $subtotalAmount0
     * @param string|null $subtotalCurrencyCode0
     * @param array $subtotalAmounts
     * @param array $subtotalCurrencyCodes
     * @param array $removableItems
     * @return void
     * @throws Throwable
     */
    public function update(
        string $userId,
        string $invoiceId,
        string $invoiceNumber,
        ?string $subtotalAmount0,
        ?string $subtotalCurrencyCode0,
        array $subtotalAmounts,
        array $subtotalCurrencyCodes,
        array $removableItems
    ): void;

    public function getSummary(string $userId, string $invoiceId): InvoiceSummary;
}
