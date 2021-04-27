@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Skelbimo {{$data['title']}} informacija</h2>
    <br>
    @guest
    @else
        <div>
            @if ($favourite)
                <h1>Sis skelbimas YRA jusu megstamiausiu sarase</h1>
                <form action="/listing/{{$data['id']}}/fav" method="POST" >
                    @csrf
                    <input type="submit" value="Unfavourite">
                </form>
            @else
                <h1>Sis skelbimas NERA jusu megstamiausiu sarase</h1>
                <form action="/listing/{{$data['id']}}/fav" method="POST" >
                    @csrf
                    <input type="submit" value="Favourite">
                </form>
            @endif
        </div>
    @endguest
    <br>
    <div>
        <img src="{{$data['thumbnail']}}">
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
                <td>{!! $data->getDetails['description'] !!}</td>
            </tr>
        </table>
    </div>
    <div>
        <h2>Skelbimo orginali <a href="{{$data['url']}}">svetaine</a></h2>
    </div>

    <div>
        <h3>Kainu Istorija:</h3>
    </div>
</div>
@endsection

@section('chart')
    <!-- Chart's container -->
    <div id="chart" style="height: 300px;"></div>
    

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