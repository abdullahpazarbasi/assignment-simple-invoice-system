<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Redis::subscribe(['invoice-saved'], function ($message) {
            echo $message . PHP_EOL;
        });

        return 0;
    }
}