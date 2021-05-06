@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Skelbimo {{$data['title']}} informacija</h2>
    <br>
    @guest
    @else
        <div>
            @if ($favourite)
                <form action="/listing/{{$data['id']}}/fav" method="POST" >
                    @csrf
                    <input type="submit" value="Nepatinka" class="btn btn-danger">
                </form>
            @else
                <form action="/listing/{{$data['id']}}/fav" method="POST" >
                    @csrf
                    <input type="submit" value="Patinka" class="btn btn-success">
                </form>
            @endif
        </div>
    @endguest
    <br>
    <div>
        @if ($data['archived'] == 1)
            <h2>Archyvuota</h2>
        @else
            <img src="{{$data['thumbnail']}}">
        @endif
    </div>
    <div>
        <table style="width:100%">
            <tr>
                <th>Kaina:</th>
                <td>{{$data->getLastestPrice['price']}} €</td>
            </tr>
            <tr>
                <th>Adresas:</th>
                <td>{{$data->adress}}</td>
            </tr>
            <tr>
                <th>Kambariai:</th>
                <td>{{$data->getDetails['rooms']}}</td>
            </tr>
            <tr>
                <th>Aukštas:</th>
                <td>{{$data->getDetails['floor']}}</td>
            </tr>
            <tr>
                <th>Namo tipas:</th>
                <td>{{$data->getDetails['buildingType']}}</td>
            </tr>
            <tr>
                <th>Šildymas:</th>
                <td>{{$data->getDetails['heating']}}</td>
            </tr>
            <tr>
                <th>Pasatytmo metai:</th>
                <td>{{$data->getDetails['year']}}</td>
            </tr>
            <tr>
                <th>Skelbimo apibūdinimas:</th>
                <td>{!! $data->getDetails['description'] !!}</td>
            </tr>
        </table>
    </div>
    <br>
    <div>
        <h2>Skelbimo orginali @if ($data['id'] == 26)
            <a href="http://www.ntportalas.lt/">
        @elseif ($data['id'] == 25)
            <a href="http://www.ntportalas.lt/">
        @elseif ($data['id'] == 81)
            <a href="http://www.ntportalas.lt/">
        @else
            <a href="{{$data['url']}}">
        @endif
        svetainė</a></h2>
    </div>

    <div>
        <h3>Kainų istorija:</h3>
    </div>
</div>
@endsection

@section('chart')
    <!-- Chart's container -->
    <div id="chart" style="height: 300px;"></div>
    <br>
    

    <!-- Charting library -->
    <script src="https://unpkg.com/chart.js@2.9.3/dist/Chart.min.js"></script>
    <!-- Chartisan -->
    <script src="https://unpkg.com/@chartisan/chartjs@^2.1.0/dist/chartisan_chartjs.umd.js"></script>

    <!-- Your application script -->
    <script>
        const chart = new Chartisan({
            el: '#chart',
            url: "@chart('price_chart')" + "?id={{ $data['id'] }}",
            hooks: new ChartisanHooks()
                .beginAtZero()
                .colors()
                .datasets([{ type: 'line', fill: false, borderColor: "Blue" }]),
        });
    </script>
@endsection