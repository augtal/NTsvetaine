@extends('layouts.app')

@section('content')
<div class="container">
    Pranesimu sarasas
    @if($notifications->count() > 0)
        <div>
            <table class="table table-hover" style="width:100%">
                <tr>
                    <th>Pavadinimas</th>
                    <th>Aprašymas</th>
                    <th>Dažnumas</th>
                    <th>Veiksmai</th>
                </tr>
                @foreach ($notifications as $item)
                <tr>
                    <td><a href='/notification/{{$item['id']}}'>{{ $item['title'] }}</a></td>
                    <td>{{ $item['description'] }}</td>
                    <td>
                        @if ($item['frequency'] == 1)
                            <p>Kai atsiranda naujas skelbimas zonoje</p>
                        @elseif ($item['frequency'] == 2)
                            <p>Kada pasikeicia skelbimu zonoje kaina</p>
                        @endif
                    </td>
                    <td>
                    <a href='/notification/{{$item['id']}}/edit' class="btn btn-success">Redaguoti</a>
                    <a href="" data-toggle="modal" data-target="#ModalCenterConfirm" class="btn btn-danger" 
                    onclick="document.getElementById('buttonDeleteConfirm').setAttribute('href', '/notification/{{$item['id']}}/delete');">Pašalinti</a>
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
                        <h5 class="modal-title" id="ModalCenterConfirmTitle">Ar tikrai norite ištrinti?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="modal-body" class="modal-body">
                        <h4>Ištrinus pranešimo parametrą jo atkurti nebeišeis.</h4>
                        <br>
                        <h5>Ar tikrai norite jį ištrinti?</h5>
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
            <h2>Neturite pranesimu!</h2>
            <form id="saveShapes" action="/showSaveNotification" method="post">
                @csrf
                <button id="saveShapesButton" type="submit" class="btn btn-primary"><h4>Sukurkite nauja</h4></button>
            </form>
        </div>
    @endif
</div>
@endsection
