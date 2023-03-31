@extends('layouts.app')

@section('title')
    Invoice 1
@endsection

@section('navbarlinks')
    <a href="/"><span class="navbar-brand mb-0 h1">Users</span></a>
    <a href="/users/1/invoices/new"><span class="btn btn-primary">Delete Invoice</span></a>
@endsection

@section('content')

    <form action="" method="post" class="mt-3">
        @csrf
        <div class="form-group">
            <label for="number">Invoice Number</label>
            <input type="text" class="form-control" name="number" id="number" value="1">
        </div>
        <div class="form-group row mt-1">
            <div class="col">
                <span class="control-label">Subtotal 1</span>
            </div>
            <div class="col">
                <label for="subtotal_amount_1" class="control-label">Amount</label>
                <input type="text" class="form-control" name="subtotal_amount[1]" id="subtotal_amount_1" value="100.00">
            </div>
            <div class="col">
                <label for="subtotal_currency_code_1" class="control-label">Currency Code</label>
                <input type="text" class="form-control" name="subtotal_currency_code[1]" id="subtotal_currency_code_1" value="USD">
            </div>
        </div>
        <div class="form-group row mt-5">
            <div class="col">
                <span class="control-label">New Subtotal</span>
            </div>
            <div class="col">
                <label for="subtotal_amount_new" class="control-label">Amount</label>
                <input type="text" class="form-control" name="subtotal_amount[new]" id="subtotal_amount_new" value="" placeholder="123.00">
            </div>
            <div class="col">
                <label for="subtotal_currency_code_new" class="control-label">Currency Code</label>
                <input type="text" class="form-control" name="subtotal_currency_code[new]" id="subtotal_currency_code_new" value="" placeholder="TRY">
            </div>
        </div>
        <div class="form-group mt-5">
            <input type="submit" class="btn btn-primary float-end" value="Update">
        </div>
    </form>

@endsection