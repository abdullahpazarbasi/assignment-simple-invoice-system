<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function list(Request $request, User $user) {
        $invoices = $user->invoices();

        return view('invoices.index')->with('user', $user)->with('invoices', $invoices);
    }

    public function getBlankDetails(Request $request, User $user) {
        return view('invoices.details')->with('user', $user)->with('invoice', null)->with('invoiceItems', []);
    }

    public function getDetails(Request $request, User $user, Invoice $invoice) {
        $invoiceItems = $invoice->items();

        return view('invoices.details')->with('user', $user)->with('invoice', $invoice)->with('invoiceItems', $invoiceItems);
    }

    public function store(Request $request, User $user) {
        return redirect()->route('single-invoice', [ 'user' => $user, 'invoice' => 'new' ])->with('success', 'OK'); // todo:
    }

    public function save(Request $request, User $user, Invoice $invoice) {
        return back()->with('success', 'OK');
    }
}
