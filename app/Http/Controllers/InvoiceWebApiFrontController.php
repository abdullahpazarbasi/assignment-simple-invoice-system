<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\InvoiceServer;
use App\Models\ViewModels\InvoiceSummary;
use Illuminate\Http\Request;

class InvoiceWebApiFrontController extends Controller
{
    public function getSummary(Request $request, string $userId, string $invoiceId, InvoiceServer $invoiceService)
    {
        $summary = $invoiceService->getSummaryById($userId, $invoiceId);
        $summaryView = new InvoiceSummary(
            $summary->getUserId(),
            $summary->getInvoiceId(),
            $summary->getInvoiceNumber(),
            $summary->getTotalAmount(),
            $summary->getTotalCurrencyCode()
        );

        return response()->json($summaryView->normalize());
    }
}
