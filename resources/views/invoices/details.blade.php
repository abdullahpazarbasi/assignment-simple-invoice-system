@extends('layouts.app')

@section('title')
    @if(isset($invoice))
        Invoice {{$invoice->getId()}}
    @else
        New Invoice
    @endif
@endsection

@section('navbarlinks')
    <a href="/"><span class="navbar-brand mb-0 h1">Users</span></a>
    <a href="/users/{{$userId}}/invoices"><span class="navbar-brand mb-0 h2">Invoices</span></a>
    @isset($invoice)
        <a href="/users/{{$userId}}/invoices/new"><span class="btn btn-primary">New Invoice</span></a>
    @endisset
@endsection

@section('content')

    <form action="/users/{{$userId}}/invoices{{isset($invoice) ? '/' . $invoice->getId() : ''}}" method="post" class="mt-3">
        @isset($invoice)
            <input type="hidden" name="_method" value="put" />
        @endisset
        @csrf
        <div class="form-group">
            <label for="number">Invoice Number</label>
            <input type="text" class="form-control" name="number" id="number" value="{{isset($invoice) ? $invoice->getNumber() : ''}}">
        </div>
        @isset($invoice)
            @foreach($invoice->getItems() as $invoiceItem)
                <div class="form-group row mt-1">
                    <div class="col">
                        <span class="control-label">Subtotal #{{$invoiceItem->getId()}}</span>
                    </div>
                    <div class="col">
                        <label for="subtotal_amount_{{$invoiceItem->getId()}}" class="control-label">Amount</label>
                        <input type="text" class="form-control" name="subtotal_amount[{{$invoiceItem->getId()}}]" id="subtotal_amount_{{$invoiceItem->getId()}}" value="{{$invoiceItem->getSubtotalAmount()}}">
                    </div>
                    <div class="col">
                        <label for="subtotal_currency_code_{{$invoiceItem->getId()}}" class="control-label">Currency Code</label>
                        <input type="text" class="form-control" name="subtotal_currency_code[{{$invoiceItem->getId()}}]" id="subtotal_currency_code_{{$invoiceItem->getId()}}" value="{{$invoiceItem->getSubtotalCurrencyCode()}}">
                    </div>
                    <div class="col">
                        <label for="removable_item_{{$invoiceItem->getId()}}" class="control-label">Will be Removed?</label>
                        <input type="checkbox" class="form-control" name="removable_item[{{$invoiceItem->getId()}}]" id="removable_item_{{$invoiceItem->getId()}}" value="1">
                    </div>
                </div>
            @endforeach
        @endisset
        <div class="form-group row mt-5">
            <div class="col">
                <span class="control-label">New Subtotal</span>
            </div>
            <div class="col">
                <label for="subtotal_amount_new" class="control-label">Amount</label>
                <input type="text" class="form-control" name="subtotal_amount_new" id="subtotal_amount_new" value="" placeholder="">
            </div>
            <div class="col">
                <label for="subtotal_currency_code_new" class="control-label">Currency Code</label>
                <input type="text" class="form-control" name="subtotal_currency_code_new" id="subtotal_currency_code_new" value="" placeholder="">
            </div>
        </div>
        <div class="form-group mt-5">
            <input type="submit" class="btn btn-primary" value="Update">
        </div>
    </form>

@endsection
