<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\ClientMovementServer;
use Illuminate\Console\Command;
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

                $this->clientMovementService->consumeInvoiceSavedEvent($message);
            });
        } catch (RedisException $e) {
            return 1;
        }

        return 0;
    }
}
