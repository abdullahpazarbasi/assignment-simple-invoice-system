<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClientMovementRepository;
use App\Contracts\ClientMovementServer;

class ClientMovementService implements ClientMovementServer
{
    protected ClientMovementRepository $clientMovementRepository;

    /**
     * @param ClientMovementRepository $clientMovementRepository
     */
    public function __construct(ClientMovementRepository $clientMovementRepository)
    {
        $this->clientMovementRepository = $clientMovementRepository;
    }

    /**
     * @param string $userId
     * @param string $invoiceNumber
     * @param float $totalAmount
     * @param string $totalCurrencyCode
     * @return string Client Movement ID
     */
    public function save(string $userId, string $invoiceNumber, float $totalAmount, string $totalCurrencyCode): string
    {
        return $this->clientMovementRepository->upsert(
            $userId,
            $invoiceNumber,
            $totalAmount,
            $totalCurrencyCode
        );
    }
}
