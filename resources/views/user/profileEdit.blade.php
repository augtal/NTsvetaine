@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Notification Dashboard') }}</div>

                <div class="card-body">

                @if ( !empty($message) )
                    <h3> {{$message}} </h3>
                @else

                    {{Form::open(array('url' => '/profile/password-change'))}}
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">

                        <div class="row">
                            <div class="col">
                                <label for="password" class="control-label">Current Password</label>
                            </div>
                            <div class="col">
                                {{Form::password('password', array('id' => 'password', 'class' => 'form-control', 'placeholder' => 'Password'))}}
                            </div>
                        </div>
                    </div>
                    @if($errors->has('password'))
                        <div>
                            <p class="text-danger">{{ $errors->first('password') }}</p> 
                        </div>
                    @endif

                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <label for="new_password" class="control-label">New Password</label>
                            </div>
                            <div class="col">
                                {{Form::password('new_password', array('id' => 'new_password', 'class' => 'form-control', 'placeholder' => 'New Password'))}}
                            </div>
                        </div>
                    </div>
                    @if($errors->has('new_password'))
                        <div>
                            <p class="text-danger">{{ $errors->first('new_password') }}</p> 
                        </div>
                    @endif

                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <label for="new_password-confirmation" class="control-label">Re-enter Password</label>
                            </div>
                            <div class="col">
                                {{Form::password('new_password-confirmation', array('id' => 'new_password-confirmation', 'class' => 'form-control', 'placeholder' => 'Confirm Password'))}}
                            </div>
                        </div>
                    </div>
                    @if($errors->has('new_password-confirmation'))
                        <div>
                            <p class="text-danger">{{ $errors->first('new_password-confirmation') }}</p> 
                        </div>
                    @endif

                    <div class="form-group">
                        <button type="submit" class="btn btn-danger">Change Password</button>
                    </div>
                    {{Form::close()}}
                @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
