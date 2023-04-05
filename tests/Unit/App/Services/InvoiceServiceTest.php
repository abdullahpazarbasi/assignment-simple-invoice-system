<?php

namespace Tests\Unit\App\Services;

use App\Contracts\InvoiceRepository;
use App\Contracts\MessagePublisher;
use App\Models\DataTransferModels\InvoiceDetails;
use App\Models\DataTransferModels\InvoiceItemDetails;
use App\Models\DataTransferModels\InvoiceSummary;
use App\Services\InvoiceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $givenUserId = '123';

        // when
        $invoiceDetailsCollection = $invoiceService->list($givenUserId);

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

        $givenUserId = '';
        $this->expectException(RuntimeException::class);

        // when
        $invoiceService->list($givenUserId);

        // then
    }

    public function testListAgainstTooLongUserId()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);

        $givenUserId = '123456789';
        $this->expectException(RuntimeException::class);

        // when
        $invoiceService->list($givenUserId);

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
        $givenInvoiceId = '1';

        // when
        $invoiceDetails = $invoiceService->get($givenInvoiceId);

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

        $givenInvoiceId = '';
        $this->expectException(RuntimeException::class);

        // when
        $invoiceService->get($givenInvoiceId);

        // then
    }

    public function testGetAgainstTooLongInvoiceId()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);

        $givenInvoiceId = '123456789';
        $this->expectException(RuntimeException::class);

        // when
        $invoiceService->get($givenInvoiceId);

        // then
    }

    public function testStoreAgainstValidContext()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                null,
                '1',
                '123',
                150.0,
                'TRY'
            )
            ->willReturn('1');

        $redisMock
            ->expects($this->once())
            ->method('publish')
            ->with(
                'invoice-saved',
                '{"user_id":"1","invoice_id":"1"}'
            );

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);

        $givenUserId = '1';
        $givenInvoiceNumber = '123';
        $givenSubtotalAmount0 = '150.0';
        $givenSubtotalCurrencyCode0 = 'TRY';

        // when
        $invoiceId = $invoiceService->store(
            $givenUserId,
            $givenInvoiceNumber,
            $givenSubtotalAmount0,
            $givenSubtotalCurrencyCode0
        );

        // then
        $this->assertEquals(
            '1',
            $invoiceId
        );
    }

    public function testGetSummaryAgainstNormalUserInvoices()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceRepositoryMock
            ->expects($this->once())
            ->method('getSingleById')
            ->with('1')
            ->willReturn(
                new InvoiceDetails(
                    '1',
                    '1',
                    '123',
                    [
                        new InvoiceItemDetails('1', 150.0, 'TRY'),
                        new InvoiceItemDetails('1', 50.0, 'TRY'),
                    ]
                ),
            );

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);
        $givenUserId = '1';
        $givenInvoiceId = '1';

        // when
        $invoiceDetails = $invoiceService->getSummary($givenUserId, $givenInvoiceId);

        // then
        $this->assertEquals(
            new InvoiceSummary('1', '1', '123', 200.0, 'TRY'),
            $invoiceDetails
        );
    }

    public function testGetSummaryAgainstNonexistentInvoice()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceRepositoryMock
            ->expects($this->once())
            ->method('getSingleById')
            ->with('1')
            ->willThrowException(new ModelNotFoundException());

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);
        $givenUserId = '1';
        $givenInvoiceId = '1';
        $this->expectException(ModelNotFoundException::class);

        // when
        $invoiceService->getSummary($givenUserId, $givenInvoiceId);

        // then
    }

    public function testGetSummaryAgainstUserConflict()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceRepositoryMock
            ->expects($this->once())
            ->method('getSingleById')
            ->with('1')
            ->willReturn(
                new InvoiceDetails(
                    '1',
                    '2',
                    '123',
                    [
                        new InvoiceItemDetails('1', 150.0, 'TRY'),
                    ]
                ),
            );

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);
        $givenUserId = '1';
        $givenInvoiceId = '1';
        $this->expectException(RuntimeException::class);

        // when
        $invoiceService->getSummary($givenUserId, $givenInvoiceId);

        // then
    }

    public function testGetSummaryAgainstUserWithNoInvoice()
    {
        // given
        $invoiceRepositoryMock = $this->createMock(InvoiceRepository::class);
        $redisMock = $this->createMock(MessagePublisher::class);

        $invoiceRepositoryMock
            ->expects($this->once())
            ->method('getSingleById')
            ->with('1')
            ->willReturn(
                new InvoiceDetails(
                    '1',
                    '1',
                    '123',
                    []
                ),
            );

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $redisMock);
        $givenUserId = '1';
        $givenInvoiceId = '1';

        // when
        $invoiceDetails = $invoiceService->getSummary($givenUserId, $givenInvoiceId);

        // then
        $this->assertEquals(
            new InvoiceSummary('1', '1', '123', 0.0, 'TRY'),
            $invoiceDetails
        );
    }

    public function testGetNumberOfItemsWhoseFieldIsAgainstNormalHaystack()
    {
        // given
        $reflectedInvoiceService = new \ReflectionClass(InvoiceService::class);
        $reflectedMethodGetNumberOfItemsWhoseFieldIs = $reflectedInvoiceService->getMethod(
            'getNumberOfItemsWhoseFieldIs'
        );
        $reflectedMethodGetNumberOfItemsWhoseFieldIs->setAccessible(true);
        $givenHaystack = [
            [
                'mykey' => 'yourvalue',
            ],
            [
                'mykey' => 'myvalue',
            ],
            [
                'mykey' => '',
            ],
        ];
        $givenKey = 'mykey';
        $givenValue = 'myvalue';

        // when
        $actualResult = $reflectedMethodGetNumberOfItemsWhoseFieldIs->invoke(
            $reflectedInvoiceService,
            $givenHaystack,
            $givenKey,
            $givenValue
        );

        // then
        $this->assertEquals(
            1,
            $actualResult
        );
    }

    public function testGetNumberOfItemsWhoseFieldIsAgainstSometimesNoKeyHaystack()
    {
        // given
        $reflectedInvoiceService = new \ReflectionClass(InvoiceService::class);
        $reflectedMethodGetNumberOfItemsWhoseFieldIs = $reflectedInvoiceService->getMethod(
            'getNumberOfItemsWhoseFieldIs'
        );
        $reflectedMethodGetNumberOfItemsWhoseFieldIs->setAccessible(true);
        $givenHaystack = [
            [
            ],
            [
                'mykey' => 'myvalue',
            ],
        ];
        $givenKey = 'mykey';
        $givenValue = 'myvalue';
        $this->expectException(RuntimeException::class);

        // when
        $reflectedMethodGetNumberOfItemsWhoseFieldIs->invoke(
            $reflectedInvoiceService,
            $givenHaystack,
            $givenKey,
            $givenValue
        );

        // then
    }
}
