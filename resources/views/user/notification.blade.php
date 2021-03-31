@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Notification Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h1>Welcome back {{ auth()->user()->user_name }}</h1>

                    {{ __('You are logged as:') }}
                    <br>
                    @if (auth()->user()->isAdmin())
                        <h4>Admin</h4>
                    @else
                        <h4>User</h4>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
