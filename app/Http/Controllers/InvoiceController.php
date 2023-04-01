<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\ViewModels\InvoiceSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function list(Request $request, User $user) {
        $invoices = $user->invoices()->getResults();

        return view('invoices.index')
               ->with('user', $user)
               ->with('invoices', $invoices);
    }

    public function getBlankDetails(Request $request, User $user) {
        return view('invoices.details')
               ->with('user', $user)
               ->with('invoice', null)
               ->with('invoiceItems', []);
    }

    public function getDetails(Request $request, User $user, Invoice $invoice) {
        $invoiceItems = $invoice->items()->getResults();

        return view('invoices.details')
               ->with('user', $user)
               ->with('invoice', $invoice)
               ->with('invoiceItems', $invoiceItems);
    }

    public function store(Request $request, User $user) {
        try {
            $request->validate([
                'number' => [ 'required', 'string' ],
                'subtotal_amount_new' => [ 'required', 'numeric' ],
                'subtotal_currency_code_new' => [ 'required_with:subtotal_amount_new', 'string', 'min:3', 'max:3' ],
            ]);
        } catch (ValidationException $e) {
            return redirect()
                   ->route('single-invoice', [ 'user' => $user, 'invoice' => 'new' ])
                   ->with('failure', sprintf('Bad request: %s', $e->getMessage()));
        }

        $user->getConnection()->beginTransaction();
        try {
            $invoice = new Invoice();
            $invoice->number = $request->input('number');
            $user->invoices()->save($invoice);
            $invoiceItem = new InvoiceItem();
            $invoiceItem->subtotal_amount = $request->input('subtotal_amount_new');
            $invoiceItem->subtotal_currency_code = $request->input('subtotal_currency_code_new');
            $invoice->items()->save($invoiceItem);
            $user->getConnection()->commit();
        } catch (\Throwable $th) {
            $user->getConnection()->rollBack();

            return redirect()
                   ->route('single-invoice', [ 'user' => $user, 'invoice' => 'new' ])
                   ->with('failure', 'Storing failed');
        }

        Redis::publish('invoice-saved', json_encode([
            'user_id' => $user->id,
            'invoice_id' => $invoice->id,
        ]));

        return redirect()
               ->route('single-invoice', [ 'user' => $user, 'invoice' => $invoice ])
               ->with('success', 'Created');
    }

    public function update(Request $request, User $user, Invoice $invoice) {
        try {
            $request->validate([
                'number' => [ 'required' ],
                'subtotal_amount' => [ 'required', 'array', 'min:1' ],
                'subtotal_amount.*' => [ 'required', 'numeric' ],
                'subtotal_currency_code' => [ 'required', 'array', 'min:1' ],
                'subtotal_currency_code.*' => [ 'required', 'string', 'min:3', 'max:3' ],
                'removable_item' => [ 'array' ],
                'removable_item.*' => [ 'boolean' ],
                'subtotal_amount_new' => [ 'sometimes', 'nullable', 'numeric' ],
                'subtotal_currency_code_new' => [ 'required_with:subtotal_amount_new', 'nullable', 'string', 'min:3', 'max:3' ],
            ]);
        } catch (ValidationException $e) {
            return back()
                   ->with('failure', sprintf('Bad request: %s', $e->getMessage()));
        }

        $user->getConnection()->beginTransaction();
        try {
            $invoice->number = $request->input('number');
            $invoice->save();
            $subtotalAmountNew = $request->input('subtotal_amount_new');
            $removableItems = (array)$request->input('removable_item', []);
            $removableInvoicItemIds = array_keys(array_filter($removableItems, function ($value) { return $value === '1'; }));
            /** @var \Illuminate\Database\Eloquent\Collection $previousInvoiceItems */
            $previousInvoiceItems = $invoice->items()->getResults();
            if ($subtotalAmountNew === null) {
                if (count($removableInvoicItemIds) >= count($previousInvoiceItems)) {
                    $user->getConnection()->rollBack();

                    return back()
                           ->with('failure', 'No item will remain');
                }
            } else {
                $newInvoiceItem = new InvoiceItem();
                $newInvoiceItem->subtotal_amount = $subtotalAmountNew;
                $newInvoiceItem->subtotal_currency_code = $request->input('subtotal_currency_code_new');
                $invoice->items()->save($newInvoiceItem);
            }
            $subtotalAmounts = $request->input('subtotal_amount', []);
            $subtotalCurrencyCodes = $request->input('subtotal_currency_code', []);
            foreach ($subtotalAmounts as $invoiceItemId => $subtotalAmount) {
                $invoiceItem = $previousInvoiceItems->find($invoiceItemId);
                if ($invoiceItem === null) {
                    $user->getConnection()->rollBack();

                    return back()
                           ->with('failure', sprintf('Unexpected item ID %s', $invoiceItemId));
                }
                if (in_array($invoiceItemId, $removableInvoicItemIds)) {
                    $invoiceItem->delete();
                } else {
                    $invoiceItem->subtotal_amount = $subtotalAmount;
                    $invoiceItem->subtotal_currency_code = $subtotalCurrencyCodes[$invoiceItemId];
                    $invoiceItem->save();
                }
            }
            $user->getConnection()->commit();
        } catch (\Throwable $th) {
            $user->getConnection()->rollBack();

            return back()
                   ->with('failure', 'Storing failed');
        }

        Redis::publish('invoice-saved', json_encode([
            'user_id' => $user->id,
            'invoice_id' => $invoice->id,
        ]));

        return back()
               ->with('success', 'Updated');
    }

    public function getSummary(Request $request, User $user, Invoice $invoice) {
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
