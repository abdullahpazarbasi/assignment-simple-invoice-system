@extends('layouts.app')

@section('title')
    Clients
@endsection

@section('navbarlinks')
    <a href="/"><span class="navbar-brand mb-0 h1">Users</span></a>
    <a href="/invoices/create"><span class="btn btn-primary">Create Invoice</span></a>
@endsection

@section('content')

    <div class="row mt-3">
        <div class="col-12 align-self-center">
            <ul class="list-group">
                <li class="list-group-item">User 1 <a href="/users/1/invoices">View Invoices</a> | <a href="/users/1/invoices/new">Create Invoice</a></li>
            </ul>
        </div>
    </div>

@endsection