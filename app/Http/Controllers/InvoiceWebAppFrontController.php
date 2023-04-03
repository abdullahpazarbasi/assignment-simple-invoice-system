<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\InvoiceServer;
use App\Models\ViewModels\InvoiceDetails as InvoiceDetailsView;
use App\Models\ViewModels\InvoiceItemDetails as InvoiceItemDetailsView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class InvoiceWebAppFrontController extends Controller
{
    public function list(
        Request $request,
        string $userId,
        InvoiceServer $invoiceService
    ) {
        $invoiceDetailsCollection = $invoiceService->list($userId);
        $invoiceDetailsCollectionView = [];
        foreach ($invoiceDetailsCollection as $invoiceDetails) {
            $invoiceItemDetailsCollection = $invoiceDetails->getItems();
            $invoiceItemDetailsCollectionView = [];
            foreach ($invoiceItemDetailsCollection as $invoiceItemDetails) {
                $invoiceItemDetailsCollectionView[] = new InvoiceItemDetailsView(
                    $invoiceItemDetails->getId(),
                    $invoiceItemDetails->getSubtotalAmount(),
                    $invoiceItemDetails->getSubtotalCurrencyCode(),
                );
            }
            $invoiceDetailsCollectionView[] = new InvoiceDetailsView(
                $invoiceDetails->getId(),
                $invoiceDetails->getUserId(),
                $invoiceDetails->getNumber(),
                $invoiceItemDetailsCollectionView,
            );
        }

        return view('invoices.index')
            ->with('userId', $userId)
            ->with('invoices', $invoiceDetailsCollectionView);
    }

    public function getDetails(
        Request $request,
        string $userId,
        string $invoiceId,
        InvoiceServer $invoiceService
    ) {
        if ($invoiceId === 'new') {
            $invoiceDetailsView = null;
        } else {
            $invoiceDetails = $invoiceService->getById($invoiceId);
            $invoiceItemDetailsCollection = $invoiceDetails->getItems();
            $invoiceItemDetailsCollectionView = [];
            foreach ($invoiceItemDetailsCollection as $invoiceItemDetails) {
                $invoiceItemDetailsCollectionView[] = new InvoiceItemDetailsView(
                    $invoiceItemDetails->getId(),
                    $invoiceItemDetails->getSubtotalAmount(),
                    $invoiceItemDetails->getSubtotalCurrencyCode(),
                );
            }
            $invoiceDetailsView = new InvoiceDetailsView(
                $invoiceDetails->getId(),
                $invoiceDetails->getUserId(),
                $invoiceDetails->getNumber(),
                $invoiceItemDetailsCollectionView,
            );
        }

        return view('invoices.details')
            ->with('userId', $userId)
            ->with('invoice', $invoiceDetailsView);
    }

    public function store(Request $request, string $userId, InvoiceServer $invoiceService)
    {
        $validator = Validator::make(
            [
                'invoiceNumber' => $request->input('number'),
                'subtotalAmount0' => $request->input('subtotal_amount_new'),
                'subtotalCurrencyCode0' => $request->input('subtotal_currency_code_new'),
            ],
            [
                'invoiceNumber' => ['required'],
                'subtotalAmount0' => ['required'],
                'subtotalCurrencyCode0' => ['required_with:subtotal_amount_new'],
            ],
        );
        if ($validator->fails()) {
            return redirect()
                ->route('single-invoice', ['user' => $userId, 'invoice' => 'new'])
                ->with('failure', implode(PHP_EOL, $validator->errors()->all()));
        }
        $validatedParameters = $validator->validated();
        try {
            $invoiceId = $invoiceService->store(
                $userId,
                $validatedParameters['invoiceNumber'],
                $validatedParameters['subtotalAmount0'],
                $validatedParameters['subtotalCurrencyCode0']
            );
        } catch (Throwable $e) {
            return redirect()
                ->route('single-invoice', ['user' => $userId, 'invoice' => 'new'])
                ->with('failure', $e->getMessage());
        }

        return redirect()
            ->route('single-invoice', ['user' => $userId, 'invoice' => $invoiceId])
            ->with('success', 'Created');
    }

    public function update(Request $request, string $userId, string $invoiceId, InvoiceServer $invoiceService)
    {
        $validator = Validator::make(
            [
                'invoiceNumber' => $request->input('number'),
                'subtotalAmount0' => $request->input('subtotal_amount_new'),
                'subtotalCurrencyCode0' => $request->input('subtotal_currency_code_new'),
                'subtotalAmounts' => $request->input('subtotal_amount', []),
                'subtotalCurrencyCodes' => $request->input('subtotal_currency_code', []),
                'removableItems' => $request->input('removable_item', []),
            ],
            [
                'invoiceNumber' => ['required'],
                'subtotalAmount0' => ['sometimes', 'nullable'],
                'subtotalCurrencyCode0' => ['required_with:subtotalAmount0', 'nullable'],
                'subtotalAmounts' => ['required', 'array', 'min:1'],
                'subtotalCurrencyCodes' => ['required', 'array', 'min:1'],
                'removableItems' => ['array'],
            ],
        );
        if ($validator->fails()) {
            return back()
                ->with('failure', implode(PHP_EOL, $validator->errors()->all()));
        }
        $validatedParameters = $validator->validated();

        try {
            $invoiceService->update(
                $userId,
                $invoiceId,
                $validatedParameters['invoiceNumber'],
                $validatedParameters['subtotalAmount0'],
                $validatedParameters['subtotalCurrencyCode0'],
                $validatedParameters['subtotalAmounts'],
                $validatedParameters['subtotalCurrencyCodes'],
                $validatedParameters['removableItems'],
            );
        } catch (Throwable $e) {
            return back()
                ->with('failure', $e->getMessage());
        }

        return back()
            ->with('success', 'Updated');
    }
}
