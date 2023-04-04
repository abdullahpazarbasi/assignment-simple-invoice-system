<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\InvoiceRepository;
use App\Models\DataTransferModels\InvoiceDetails;
use App\Models\DataTransferModels\InvoiceItemDetails;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;
use Throwable;

class ConcreteInvoiceRepository implements InvoiceRepository
{
    /**
     * @param string $userId
     * @return InvoiceDetails[]
     */
    public function findAllBelongsToUser(string $userId): array
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

    /**
     * @param string $id
     * @return InvoiceDetails
     */
    public function getSingleById(string $id): InvoiceDetails
    {
        $invoice = Invoice::query()->findOrFail((int)$id);
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
     * @param string|null $id
     * @param string $userId
     * @param string $number
     * @param float $firstItemSubtotalAmount
     * @param string $firstItemSubtotalCurrencyCode
     * @return string Invoice ID
     * @throws Throwable
     */
    public function create(
        ?string $id,
        string $userId,
        string $number,
        float $firstItemSubtotalAmount,
        string $firstItemSubtotalCurrencyCode
    ): string {
        $user = User::query()->findOrFail($userId);

        $user->getConnection()->beginTransaction();
        try {
            $invoice = new Invoice();
            $invoice->number = $number;
            $user->invoices()->save($invoice);
            $invoiceItem = new InvoiceItem();
            $invoiceItem->subtotal_amount = $firstItemSubtotalAmount;
            $invoiceItem->subtotal_currency_code = $firstItemSubtotalCurrencyCode;
            $invoice->items()->save($invoiceItem);
            $user->getConnection()->commit();
        } catch (Throwable $e) {
            $user->getConnection()->rollBack();

            throw $e;
        }

        return (string)$invoice->id;
    }

    /**
     * @param string $id
     * @param string $userId
     * @param string $number
     * @param array $items
     * @return void
     * @throws Throwable
     */
    public function update(string $id, string $userId, string $number, array $items): void
    {
        $invoice = Invoice::query()->findOrFail((int)$id);
        if ((string)$invoice->user_id !== $userId) {
            throw new RuntimeException('Desired invoice does not belong to the user');
        }

        $invoice->getConnection()->beginTransaction();
        try {
            $invoice->number = $number;
            $invoice->save();
            foreach ($items as $item) {
                if (empty($item['id'])) {
                    $newInvoiceItem = new InvoiceItem();
                    $newInvoiceItem->subtotal_amount = (float)$item['subtotalAmount'];
                    $newInvoiceItem->subtotal_currency_code = $item['subtotalCurrencyCode'];
                    $invoice->items()->save($newInvoiceItem);
                } else {
                    $invoiceItem = InvoiceItem::query()->findOrFail($item['id']);
                    if ($item['removable']) {
                        $invoiceItem->delete();
                    } else {
                        $invoiceItem->subtotal_amount = (float)$item['subtotalAmount'];
                        $invoiceItem->subtotal_currency_code = $item['subtotalCurrencyCode'];
                        $invoiceItem->save();
                    }
                }
            }
            $invoice->getConnection()->commit();
        } catch (Throwable $e) {
            $invoice->getConnection()->rollBack();

            throw $e;
        }
    }
}
