<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request, User $user) {
        return view('invoices.index');
    }

    public function emptyDetails(Request $request, User $user) {
        return view('invoices.details');
    }

    public function details(Request $request, User $user, Invoice $invoice) {
        return view('invoices.details');
    }
}
