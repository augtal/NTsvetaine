@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Profilio redagavimas</div>

                <div class="card-body">
                    {{Form::open(array('url' => '/profile/password-change'))}}
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">

                        <div class="row">
                            <div class="col">
                                <label for="password" class="control-label">Dabartinis slaptažodis</label>
                            </div>
                            <div class="col">
                                {{Form::password('password', array('id' => 'password', 'class' => 'form-control', 'placeholder' => 'Slaptaždis'))}}
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
                                <label for="new_password" class="control-label">Naujas slaptažodis</label>
                            </div>
                            <div class="col">
                                {{Form::password('new_password', array('id' => 'new_password', 'class' => 'form-control', 'placeholder' => 'Naujas slaptažodis'))}}
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
                                <label for="new_password-confirmation" class="control-label">Pakartoti nauja slaptažodį</label>
                            </div>
                            <div class="col">
                                {{Form::password('new_password-confirmation', array('id' => 'new_password-confirmation', 'class' => 'form-control', 'placeholder' => 'Pakartoti slaptažodį'))}}
                            </div>
                        </div>
                    </div>
                    @if($errors->has('new_password-confirmation'))
                        <div>
                            <p class="text-danger">{{ $errors->first('new_password-confirmation') }}</p>
                        </div>
                    @endif

                    <div class="form-group">
                        <a href="/" class="btn btn-secondary">Atšaukti</a>

                        <button type="submit" class="btn btn-success">Pakeisti slaptažodį</button>
                    </div>
                    {{Form::close()}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
