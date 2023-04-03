<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\ViewModels\InvoiceSummary;
use Illuminate\Http\Request;

class InvoiceWebApiController extends Controller
{
    public function getSummary(Request $request, User $user, Invoice $invoice)
    {
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
        $summary = new InvoiceSummary(
            $user->id,
            $invoice->id,
            $invoice->number,
            $totals[$currencyCode],
            $currencyCode
        );

        return response()->json($summary->normalize());
    }
}
