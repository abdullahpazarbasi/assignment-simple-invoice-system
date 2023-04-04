<?php

namespace Tests\Unit\App\Services;

use App\Contracts\InvoiceRepository;
use App\Contracts\MessagePublisher;
use App\Models\DataTransferModels\InvoiceDetails;
use App\Services\InvoiceService;
use RuntimeException;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    public function testListAgainstValidUserId()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceRepositoryMock
            ->expects($this->once())
            ->method('findAllBelongsToUser')
            ->with($this->identicalTo('123'))
            ->willReturn(
                [
                    new InvoiceDetails('1', '123', '456', []),
                ]
            );

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);

        // when
        $invoiceDetailsCollection = $invoiceService->list('123');

        // then
        $this->assertEquals(
            [
                new InvoiceDetails('1', '123', '456', []),
            ],
            $invoiceDetailsCollection
        );
    }

    public function testListAgainstEmptyUserId()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);

        $this->expectException(RuntimeException::class);

        // when
        $invoiceService->list('');

        // then
    }

    public function testListAgainstTooLongUserId()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);

        $this->expectException(RuntimeException::class);

        // when
        $invoiceService->list('123456789');

        // then
    }

    public function testGetAgainstValidInvoiceId()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceRepositoryMock
            ->expects($this->once())
            ->method('getSingleById')
            ->with($this->identicalTo('1'))
            ->willReturn(
                new InvoiceDetails('1', '123', '456', []),
            );

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);

        // when
        $invoiceDetails = $invoiceService->get('1');

        // then
        $this->assertEquals(
            new InvoiceDetails('1', '123', '456', []),
            $invoiceDetails
        );
    }

    public function testGetAgainstEmptyInvoiceId()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);

        $this->expectException(RuntimeException::class);

        // when
        $invoiceService->get('');

        // then
    }

    public function testGetAgainstTooLongInvoiceId()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);

        $this->expectException(RuntimeException::class);

        // when
        $invoiceService->get('123456789');

        // then
    }
}
