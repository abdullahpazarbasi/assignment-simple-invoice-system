@extends('layouts.app')

@section('title')
    Invoices
@endsection

@section('navbarlinks')
    <a href="/"><span class="navbar-brand mb-0 h1">Users</span></a>
    <a href="/users/{{$userId}}/invoices"><span class="navbar-brand mb-0 h2">Invoices</span></a>
    <a href="/users/{{$userId}}/invoices/new"><span class="btn btn-primary">New Invoice</span></a>
@endsection

@section('content')

    <div class="row mt-3">
        <div class="col-12 align-self-center">
            <ul class="list-group">
                @foreach($invoices as $invoice)
                    <li class="list-group-item">Invoice #{{$invoice->getId()}} with number "{{$invoice->getNumber()}}" <a href="/users/{{$userId}}/invoices/{{$invoice->getId()}}">details</a></li>
                @endforeach
            </ul>
        </div>
    </div>

@endsection
