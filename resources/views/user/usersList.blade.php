@extends('layouts.app')

@section('content')
<div class="container">
    @if($users->count() > 0)
        <div>
            <table class="table table-hover" style="width:100%">
                <tr>
                    <th>ID</th>
                    <th>Slapyvardis</th>
                    <th>El. paštas</th>
                    <th>El. paštas patvirtintas</th>
                    <th>Naudotojo sukūrimo data</th>
                    <th>Rolė</th>
                    <th>Pakeisti naudotojo role</th>
                </tr>
                @foreach ($users as $item)
                <tr>
                    <td>{{ $item['id'] }}</td>
                    <td>{{ $item['user_name'] }}</td>
                    <td>{{ $item['email'] }}</td>
                    <td>{{ $item['email_verified_at'] }}</td>
                    <td>{{ $item['created_at'] }}</td>
                    <td>@if ($item['role'] == 1)
                            <h5>Naudotojas</h5>
                        @else
                            <h5>Administratorius</h5>
                        @endif
                    </td>
                    <td>@if ($item['id'] != auth()->user()->id)
                            <a href='/changeRole/{{$item['id']}}' class="btn btn-success">Pakeisti</a>
                            <a href="" data-toggle="modal" data-target="#ModalCenterConfirm" class="btn btn-danger" 
                            onclick="document.getElementById('buttonDeleteConfirm').setAttribute('href', '/deleteUser/{{$item['id']}}');">Pašalinti</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
            <br>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="ModalCenterConfirm" tabindex="-1" role="dialog" aria-labelledby="ModalCenterConfirmTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalCenterConfirmTitle">Ar tikrai norite pašalinti naudotoją?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="modal-body" class="modal-body">
                        <h4>Pašalinus naudotoją jo atkurti neišeis.</h4>
                        <br>
                        <h5>Ar tikrai norite jį pašalinti?</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Atšaukti</button>
                        <a href="" id="buttonDeleteConfirm" class="btn btn-primary">Patvirtinti</a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div>
            <h2>No users!</h2>
        </div>
    @endif
</div>
@endsection
