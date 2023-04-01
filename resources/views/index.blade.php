@extends('layouts.app')

@section('title')
    Clients
@endsection

@section('navbarlinks')
    <a href="/"><span class="navbar-brand mb-0 h1">Users</span></a>
@endsection

@section('content')

    <div class="row mt-3">
        <div class="col-12 align-self-center">
            <ul class="list-group">
                @foreach($users as $user)
                    <li class="list-group-item">"{{$user->name}}" &lt;{{$user->email}}&gt; <a href="/users/{{$user->id}}/invoices">List Invoices</a> | <a href="/users/{{$user->id}}/invoices/new">New Invoice</a></li>
                @endforeach
            </ul>
        </div>
    </div>

@endsection