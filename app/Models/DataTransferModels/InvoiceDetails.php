<?php

declare(strict_types=1);

namespace App\Models\DataTransferModels;

class InvoiceDetails
{
    protected string $id;
    protected string $userId;
    protected string $number;

    /**
     * @var InvoiceItemDetails[]
     */
    protected array $items;

    /**
     * @param string $id
     * @param string $userId
     * @param string $number
     * @param InvoiceItemDetails[] $items
     */
    public function __construct(string $id, string $userId, string $number, array $items)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->number = $number;
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return InvoiceItemDetails[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getNumberOfItems(): int
    {
        return count($this->items);
    }

    public function doesItemExist(string $itemId): bool
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $itemId) {
                return true;
            }
        }

        return false;
    }
}
