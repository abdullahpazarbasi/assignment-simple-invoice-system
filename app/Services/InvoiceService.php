<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\InvoiceServer;
use App\Models\DataTransferModels\InvoiceDetails;
use App\Models\DataTransferModels\InvoiceItemDetails;
use App\Models\DataTransferModels\InvoiceSummary;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class InvoiceService implements InvoiceServer
{
    /**
     * @var RedisFactory|RedisManager
     */
    protected RedisFactory $redisFactory;

    /**
     * @param RedisFactory $redisFactory
     */
    public function __construct(RedisFactory $redisFactory)
    {
        $this->redisFactory = $redisFactory;
    }

    /**
     * @param string $userId
     * @return InvoiceDetails[]
     */
    public function list(string $userId): array
    {
        /** @var Invoice[] $invoices */
        $invoices = Invoice::query()->where('user_id', '=', $userId)->getModels();
        $invoiceDetailsCollection = [];
        foreach ($invoices as $invoice) {
            /** @var InvoiceItem $invoiceItems */
            $invoiceItems = $invoice->items()->getResults();
            $invoiceItemDetailsCollection = [];
            foreach ($invoiceItems as $invoiceItem) {
                $invoiceItemDetailsCollection[] = new InvoiceItemDetails(
                    (string)$invoiceItem->id,
                    $invoiceItem->subtotal_amount,
                    $invoiceItem->subtotal_currency_code,
                );
            }
            $invoiceDetailsCollection[] = new InvoiceDetails(
                (string)$invoice->id,
                (string)$invoice->user_id,
                $invoice->number,
                $invoiceItemDetailsCollection,
            );
        }

        return $invoiceDetailsCollection;
    }

    public function getById(string $invoiceId): InvoiceDetails
    {
        $invoice = Invoice::query()->findOrFail((int)$invoiceId);
        $invoiceItems = $invoice->items()->getResults();
        $invoiceItemDetailsCollection = [];
        foreach ($invoiceItems as $invoiceItem) {
            $invoiceItemDetailsCollection[] = new InvoiceItemDetails(
                (string)$invoiceItem->id,
                $invoiceItem->subtotal_amount,
                $invoiceItem->subtotal_currency_code,
            );
        }

        return new InvoiceDetails(
            (string)$invoice->id,
            (string)$invoice->user_id,
            $invoice->number,
            $invoiceItemDetailsCollection,
        );
    }

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
    ): string {
        $validator = Validator::make(
            [
                'userId' => $userId,
                'invoiceNumber' => $invoiceNumber,
                'subtotalAmount0' => $subtotalAmount0,
                'subtotalCurrencyCode0' => $subtotalCurrencyCode0,
            ],
            [
                'userId' => ['string', 'min:1', 'max:8'],
                'invoiceNumber' => ['string', 'min:2', 'max:20'],
                'subtotalAmount0' => ['numeric'],
                'subtotalCurrencyCode0' => ['string', 'min:3', 'max:3'],
            ],
        );
        if ($validator->fails()) {
            throw new RuntimeException(implode(PHP_EOL, $validator->errors()->all()));
        }

        $user = User::query()->findOrFail($userId);

        $user->getConnection()->beginTransaction();
        try {
            $invoice = new Invoice();
            $invoice->number = $invoiceNumber;
            $user->invoices()->save($invoice);
            $invoiceItem = new InvoiceItem();
            $invoiceItem->subtotal_amount = (float)$subtotalAmount0;
            $invoiceItem->subtotal_currency_code = $subtotalCurrencyCode0;
            $invoice->items()->save($invoiceItem);
            $user->getConnection()->commit();
        } catch (Throwable $e) {
            $user->getConnection()->rollBack();

            throw $e;
        }

        $invoiceId = (string)$invoice->id;

        $this->dispatchInvoiceSavedEvent($userId, $invoiceId);

        return $invoiceId;
    }

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
    ): void {
        $validator = Validator::make(
            [
                'userId' => $userId,
                'invoiceId' => $invoiceId,
                'invoiceNumber' => $invoiceNumber,
                'subtotalAmount0' => $subtotalAmount0,
                'subtotalCurrencyCode0' => $subtotalCurrencyCode0,
                'subtotalAmounts' => $subtotalAmounts,
                'subtotalCurrencyCodes' => $subtotalCurrencyCodes,
                'removableItems' => $removableItems,
            ],
            [
                'userId' => ['string', 'min:1', 'max:8'],
                'invoiceId' => ['string', 'min:1', 'max:8'],
                'invoiceNumber' => ['string', 'min:2', 'max:20'],
                'subtotalAmount0' => ['nullable', 'numeric'],
                'subtotalCurrencyCode0' => ['nullable', 'string', 'min:3', 'max:3'],
                'subtotalAmounts.*' => ['required', 'numeric'],
                'subtotalCurrencyCodes.*' => ['required', 'string', 'min:3', 'max:3'],
                'removableItems.*' => ['boolean'],
            ],
        );
        if ($validator->fails()) {
            throw new RuntimeException(implode(PHP_EOL, $validator->errors()->all()));
        }

        $invoice = Invoice::query()->findOrFail((int)$invoiceId);

        $invoice->getConnection()->beginTransaction();
        try {
            $invoice->number = $invoiceNumber;
            $invoice->save();
            $removableInvoiceItemIds = array_keys(
                array_filter($removableItems, function ($value) {
                    return $value === '1';
                })
            );
            /** @var Collection $previousInvoiceItems */
            $previousInvoiceItems = $invoice->items()->getResults();
            if ($subtotalAmount0 === null) {
                if (count($removableInvoiceItemIds) >= count($previousInvoiceItems)) {
                    $invoice->getConnection()->rollBack();

                    throw new RuntimeException('No item will remain');
                }
            } else {
                $newInvoiceItem = new InvoiceItem();
                $newInvoiceItem->subtotal_amount = (float)$subtotalAmount0;
                $newInvoiceItem->subtotal_currency_code = $subtotalCurrencyCode0;
                $invoice->items()->save($newInvoiceItem);
            }
            foreach ($subtotalAmounts as $invoiceItemId => $subtotalAmount) {
                $invoiceItem = $previousInvoiceItems->find((int)$invoiceItemId);
                if ($invoiceItem === null) {
                    $invoice->getConnection()->rollBack();

                    throw new RuntimeException(sprintf('Unexpected item ID %s', $invoiceItemId));
                }
                if (in_array($invoiceItemId, $removableInvoiceItemIds)) {
                    $invoiceItem->delete();
                } else {
                    $invoiceItem->subtotal_amount = (float)$subtotalAmount;
                    $invoiceItem->subtotal_currency_code = $subtotalCurrencyCodes[$invoiceItemId];
                    $invoiceItem->save();
                }
            }
            $invoice->getConnection()->commit();
        } catch (Throwable $e) {
            $invoice->getConnection()->rollBack();

            throw $e;
        }

        $this->dispatchInvoiceSavedEvent($userId, $invoiceId);
    }

    public function getSummaryById(string $userId, string $invoiceId): InvoiceSummary
    {
        $invoice = Invoice::query()->findOrFail($invoiceId);
        $invoiceItems = $invoice->items()->getResults();
        $totals = [];
        /** @var InvoiceItem $invoiceItem */
        foreach ($invoiceItems as $invoiceItem) {
            $currencyCode = $invoiceItem->subtotal_currency_code;
            if (!isset($totals[$currencyCode])) {
                $totals[$currencyCode] = 0;
            }
            $totals[$currencyCode] += $invoiceItem->subtotal_amount;
        }

        return new InvoiceSummary(
            (string)$invoice->user_id,
            (string)$invoice->id,
            $invoice->number,
            (float)$totals[$currencyCode],
            $currencyCode
        );
    }

    protected function dispatchInvoiceSavedEvent(string $userId, string $invoiceId)
    {
        $this->redisFactory->publish(
            'invoice-saved',
            json_encode([
                'user_id' => $userId,
                'invoice_id' => $invoiceId,
            ])
        );
    }
}
