@extends('layouts.app')

@section('title')
    @if(isset($invoice))
        Invoice {{$invoice->id}}
    @else
        New Invoice
    @endif
@endsection

@section('navbarlinks')
    <a href="/"><span class="navbar-brand mb-0 h1">Users</span></a>
    <a href="/users/{{$user->id}}/invoices"><span class="navbar-brand mb-0 h2">Invoices</span></a>
    @isset($invoice)
        <a href="/users/{{$user->id}}/invoices/new"><span class="btn btn-primary">New Invoice</span></a>
    @endisset
@endsection

@section('content')

    <form action="/users/{{$user->id}}/invoices{{isset($invoice) ? '/' . $invoice->id : ''}}" method="post" class="mt-3">
        @isset($invoice)
            <input type="hidden" name="_method" value="put" />
        @endisset
        @csrf
        <div class="form-group">
            <label for="number">Invoice Number</label>
            <input type="text" class="form-control" name="number" id="number" value="{{isset($invoice) ? $invoice->number : ''}}">
        </div>
        @foreach($invoiceItems as $invoiceItem)
            <div class="form-group row mt-1">
                <div class="col">
                    <span class="control-label">Subtotal #{{$invoiceItem->id}}</span>
                </div>
                <div class="col">
                    <label for="subtotal_amount_{{$invoiceItem->id}}" class="control-label">Amount</label>
                    <input type="text" class="form-control" name="subtotal_amount[{{$invoiceItem->id}}]" id="subtotal_amount_{{$invoiceItem->id}}" value="{{$invoiceItem->subtotal_amount}}">
                </div>
                <div class="col">
                    <label for="subtotal_currency_code_{{$invoiceItem->id}}" class="control-label">Currency Code</label>
                    <input type="text" class="form-control" name="subtotal_currency_code[{{$invoiceItem->id}}]" id="subtotal_currency_code_{{$invoiceItem->id}}" value="{{$invoiceItem->subtotal_currency_code}}">
                </div>
                <div class="col">
                    <label for="removable_item_{{$invoiceItem->id}}" class="control-label">Will be Removed?</label>
                    <input type="checkbox" class="form-control" name="removable_item[{{$invoiceItem->id}}]" id="removable_item_{{$invoiceItem->id}}" value="1">
                </div>
            </div>
        @endforeach
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
