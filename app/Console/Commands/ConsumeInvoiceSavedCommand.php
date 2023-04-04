<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\ClientMovementServer;
use App\Models\ClientMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use RedisException;

class ConsumeInvoiceSavedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consume:invoice-saved';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consumer of event invoice_saved';

    /**
     * @var ClientMovementServer
     */
    protected ClientMovementServer $clientMovementService;

    /**
     * @param ClientMovementServer $clientMovementService
     */
    public function __construct(ClientMovementServer $clientMovementService)
    {
        parent::__construct();
        $this->clientMovementService = $clientMovementService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            Redis::subscribe(['invoice-saved'], function ($message) {
                echo $message . PHP_EOL;

                $eventPayload = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
                $userId = $eventPayload['user_id'];
                $invoiceId = $eventPayload['invoice_id'];
                $response = Http::timeout(10)
                    ->get(sprintf('http://127.0.0.1:80/api/users/%s/invoices/%s/summary', $userId, $invoiceId))
                    ->throw();
                $summary = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);

                $this->clientMovementService->save(
                    $summary['user_id'],
                    $summary['invoice_number'],
                    (float)$summary['total_amount'],
                    $summary['total_currency_code'],
                );
            });
        } catch (RedisException $e) {
            return 1;
        }

        return 0;
    }
}
