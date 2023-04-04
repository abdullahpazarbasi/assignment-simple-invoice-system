<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClientMovementRepository;
use App\Contracts\ClientMovementServer;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JsonException;

class ClientMovementService implements ClientMovementServer
{
    protected ClientMovementRepository $clientMovementRepository;
    protected string $invoiceServiceAuthority;

    /**
     * @param ClientMovementRepository $clientMovementRepository
     * @param string $invoiceServiceAuthority
     */
    public function __construct(ClientMovementRepository $clientMovementRepository, string $invoiceServiceAuthority)
    {
        $this->clientMovementRepository = $clientMovementRepository;
        $this->invoiceServiceAuthority = $invoiceServiceAuthority;
    }

    /**
     * @param string $message
     * @return string Client Movement ID
     * @throws JsonException
     * @throws RequestException
     */
    public function consumeInvoiceSavedEvent(string $message): string
    {
        $eventPayload = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
        $userId = $eventPayload['user_id'];
        $invoiceId = $eventPayload['invoice_id'];

        $summary = $this->retrieveInvoiceSummary($userId, $invoiceId);

        return $this->clientMovementRepository->upsert(
            $summary['user_id'],
            $summary['invoice_number'],
            (float)$summary['total_amount'],
            $summary['total_currency_code']
        );
    }

    /**
     * @param string $userId
     * @param string $invoiceId
     * @return array
     * @throws JsonException
     * @throws RequestException
     */
    protected function retrieveInvoiceSummary(string $userId, string $invoiceId): array
    {
        $response = Http::timeout(10)
            ->get(
                sprintf(
                    'http://%s/api/users/%s/invoices/%s/summary',
                    $this->invoiceServiceAuthority,
                    $userId,
                    $invoiceId
                )
            )
            ->throw();

        return json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);
    }
}
