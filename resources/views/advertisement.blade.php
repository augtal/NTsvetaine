@extends('layouts.app')

@section('content')
<div class="container">
    Advertisement {{$data['title']}} info
    <div>
        <img src="{{$data['thumbnail']}}">
    </div>
    <div>
        <table style="width:100%">
            <tr>
                <th>Kaina:</th>
                <td>{{$data->getLastestPrice['price']}}</td>
            </tr>
            <tr>
                <th>Adresas:</th>
                <td>{{$data->getDetails['adress']}}</td>
            </tr>
            <tr>
                <th>Kambariai:</th>
                <td>{{$data->getDetails['rooms']}}</td>
            </tr>
            <tr>
                <th>Aukstas:</th>
                <td>{{$data->getDetails['floor']}}</td>
            </tr>
            <tr>
                <th>Namo tipas:</th>
                <td>{{$data->getDetails['buildingType']}}</td>
            </tr>
            <tr>
                <th>Sildymas:</th>
                <td>{{$data->getDetails['heating']}}</td>
            </tr>
            <tr>
                <th>Pasatytmo metai:</th>
                <td>{{$data->getDetails['year']}}</td>
            </tr>
            <tr>
                <th>Skelbimo apibudinimas:</th>
                <td>{{$data->getDetails['description']}}</td>
            </tr>
        </table>
    </div>

    <div>
        Kainu Istorija:
    </div>
</div>
@endsection
