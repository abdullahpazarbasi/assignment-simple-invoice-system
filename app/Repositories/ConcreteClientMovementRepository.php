<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ClientMovementRepository;
use App\Models\ClientMovement;
use Throwable;

class ConcreteClientMovementRepository implements ClientMovementRepository
{
    /**
     * @param string $clientNumber
     * @param string $invoiceNumber
     * @param float $totalAmount
     * @param string $totalCurrencyCode
     * @return string Client Movement ID
     * @throws Throwable
     */
    public function upsert(
        string $clientNumber,
        string $invoiceNumber,
        float $totalAmount,
        string $totalCurrencyCode
    ): string {
        $clientMovement = ClientMovement::query()->firstOrNew([
            'invoice_number' => $invoiceNumber,
        ]);
        $clientMovement->client_number = $clientNumber;
        $clientMovement->total = sprintf(
            '%s %s',
            $totalAmount,
            $totalCurrencyCode
        );
        $clientMovement->saveOrFail();

        return (string)$clientMovement->id;
    }
}
