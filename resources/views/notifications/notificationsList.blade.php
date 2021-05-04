@extends('layouts.app')

@section('content')
<div class="container">
    Pranesimu sarasas
    @if($notifications->count() > 0)
        <div>
            <table style="width:100%">
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
                            <h5>Kiekviena diena</h5>
                        @elseif ($item['frequency'] == 2)
                            <h5>Kai atsiranda naujas skelbimas zonoje</h5>
                        @elseif ($item['frequency'] == 3)
                            <h5>Kada pasikeicia skelbimu zonoje kaina</h5>
                        @endif
                    </td>
                    <td><a href='/notification/{{$item['id']}}/edit' class="btn btn-warning">Redaguoti</a>
                    <a href='/notification/{{$item['id']}}/delete' class="btn btn-danger">Naikinti</a></td>
                </tr>
                @endforeach
            </table>
            <br>
        </div>
    @else
        <div>
            <h2>Neturite pranesimu!</h2>
            <h4><a href="/showSaveNotification">Sukurkite nauja</a></h4>
        </div>
    @endif
</div>
@endsection
