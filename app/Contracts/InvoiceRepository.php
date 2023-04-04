<?php

namespace App\Contracts;

use App\Models\DataTransferModels\InvoiceDetails;

interface InvoiceRepository
{
    /**
     * @param string $userId
     * @return InvoiceDetails[]
     */
    public function findAllBelongsToUser(string $userId): array;

    /**
     * @param string $id
     * @return InvoiceDetails
     */
    public function getSingleById(string $id): InvoiceDetails;

    /**
     * @param string|null $id
     * @param string $userId
     * @param string $number
     * @param float $firstItemSubtotalAmount
     * @param string $firstItemSubtotalCurrencyCode
     * @return string Invoice ID
     */
    public function create(
        ?string $id,
        string $userId,
        string $number,
        float $firstItemSubtotalAmount,
        string $firstItemSubtotalCurrencyCode
    ): string;

    /**
     * @param string $id
     * @param string $userId
     * @param string $number
     * @param array $items
     * @return void
     */
    public function update(
        string $id,
        string $userId,
        string $number,
        array $items
    ): void;
}
