<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\InvoiceRepository;
use App\Contracts\InvoiceServer;
use App\Contracts\MessagePublisher;
use App\Models\DataTransferModels\InvoiceDetails;
use App\Models\DataTransferModels\InvoiceSummary;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class InvoiceService implements InvoiceServer
{
    protected InvoiceRepository $invoiceRepository;

    /**
     * @var RedisFactory|RedisManager|MessagePublisher
     */
    protected RedisFactory $redisFactory;

    /**
     * @param InvoiceRepository $invoiceRepository
     * @param RedisFactory|RedisManager $redisFactory
     */
    public function __construct(InvoiceRepository $invoiceRepository, RedisFactory $redisFactory)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->redisFactory = $redisFactory;
    }

    /**
     * @param string $userId
     * @return InvoiceDetails[]
     * @throws Throwable
     */
    public function list(string $userId): array
    {
        $validator = Validator::make(
            [
                'userId' => $userId,
            ],
            [
                'userId' => 'required|string|min:1|max:8',
            ],
        );
        if ($validator->fails()) {
            throw new RuntimeException(implode(PHP_EOL, $validator->errors()->all()));
        }

        return $this->invoiceRepository->findAllBelongsToUser($userId);
    }

    public function get(string $invoiceId): InvoiceDetails
    {
        $validator = Validator::make(
            [
                'invoiceId' => $invoiceId,
            ],
            [
                'invoiceId' => 'required|string|min:1|max:8',
            ],
        );
        if ($validator->fails()) {
            throw new RuntimeException(implode(PHP_EOL, $validator->errors()->all()));
        }

        return $this->invoiceRepository->getSingleById($invoiceId);
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
                'userId' => 'required|string|min:1|max:8',
                'invoiceNumber' => 'required|string|min:2|max:20',
                'subtotalAmount0' => 'numeric',
                'subtotalCurrencyCode0' => 'required|string|min:3|max:3',
            ],
        );
        if ($validator->fails()) {
            throw new RuntimeException(implode(PHP_EOL, $validator->errors()->all()));
        }

        $invoiceId = $this->invoiceRepository->create(
            null,
            $userId,
            $invoiceNumber,
            (float)$subtotalAmount0,
            $subtotalCurrencyCode0,
        );

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

        $previousInvoiceDetails = $this->invoiceRepository->getSingleById($invoiceId);
        $removableInvoiceItemIds = array_keys(
            array_filter($removableItems, function ($value) {
                return $value === '1';
            })
        );
        $invoiceItems = [];
        foreach ($subtotalAmounts as $invoiceItemId => $subtotalAmount) {
            if (!$previousInvoiceDetails->doesItemExist((string)$invoiceItemId)) {
                throw new RuntimeException(sprintf('Unexpected item ID %s', $invoiceItemId));
            }
            $invoiceItem = [];
            $invoiceItem['id'] = $invoiceItemId;
            $invoiceItem['subtotalAmount'] = $subtotalAmount;
            if (empty($subtotalCurrencyCodes[$invoiceItemId])) {
                throw new RuntimeException('Inconsistency for given');
            }
            $invoiceItem['subtotalCurrencyCode'] = $subtotalCurrencyCodes[$invoiceItemId];
            $invoiceItem['removable'] = in_array($invoiceItemId, $removableInvoiceItemIds);
            $invoiceItems[] = $invoiceItem;
        }
        if ($subtotalAmount0 !== null) {
            $invoiceItem = [];
            $invoiceItem['id'] = null;
            $invoiceItem['subtotalAmount'] = $subtotalAmount0;
            $invoiceItem['subtotalCurrencyCode'] = $subtotalCurrencyCode0;
            $invoiceItem['removable'] = false;
            $invoiceItems[] = $invoiceItem;
        }
        if (self::getNumberOfItemsWhoseFieldIs($invoiceItems, 'removable', false) === 0) {
            throw new RuntimeException('No item will remain');
        }
        $this->invoiceRepository->update(
            $invoiceId,
            $userId,
            $invoiceNumber,
            $invoiceItems
        );

        $this->dispatchInvoiceSavedEvent($userId, $invoiceId);
    }

    /**
     * @param string $userId
     * @param string $invoiceId
     * @return InvoiceSummary
     * @throws RuntimeException
     */
    public function getSummary(string $userId, string $invoiceId): InvoiceSummary
    {
        $invoiceDetails = $this->invoiceRepository->getSingleById($invoiceId);
        if ($invoiceDetails->getUserId() !== $userId) {
            throw new RuntimeException('Desired invoice does not belong to the user');
        }
        $totals = [];
        $currencyCode = 'TRY';
        $totals[$currencyCode] = 0;
        foreach ($invoiceDetails->getItems() as $invoiceItemDetails) {
            $currencyCode = $invoiceItemDetails->getSubtotalCurrencyCode();
            if (!isset($totals[$currencyCode])) {
                $totals[$currencyCode] = 0;
            }
            $totals[$currencyCode] += $invoiceItemDetails->getSubtotalAmount();
        }

        return new InvoiceSummary(
            $invoiceDetails->getUserId(),
            $invoiceDetails->getId(),
            $invoiceDetails->getNumber(),
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

    /**
     * @param array[] $haystack
     * @param string $key
     * @param mixed $value
     * @return int
     * @throws RuntimeException
     */
    protected static function getNumberOfItemsWhoseFieldIs(array $haystack, string $key, $value): int
    {
        $i = 0;
        $c = 0;
        foreach ($haystack as $item) {
            if (!isset($item[$key])) {
                throw new RuntimeException(
                    sprintf(
                        'Item whose index is %d does not have field whose key is %s',
                        $i,
                        $key
                    )
                );
            }
            if ($item[$key] === $value) {
                $c++;
            }
            $i++;
        }

        return $c;
    }
}
