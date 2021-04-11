@extends('layouts.app')

@section('content')
<div class="container">
    Megstamiausiu skelbimu sarasas
    @if(count($data) > 0)
        <div>
            <table style="width:100%">
                <tr>
                    <th>Nuotrauka</th>
                    <th>Pavadinimas</th>
                    <th>Kaina</th>
                    <th>Kategorija</th>
                    <th>Tipas</th>
                    <th>Svetainės logo</th>
                </tr>
                @foreach ($data as $item)
                <tr>
                    <td><a href="{{$item['url']}}"><img src="{{$item['thumbnail']}}" style="width: 250px; height:175px"></td></a>
                    <td><a href="/listing/{{$item['id']}}">{{$item['title']}}</td></a>
                    <td>{{$item->getLastestPrice['price']}} €</td>
                    <td>{{$item->getCategory['title']}}</td>
                    <td>{{$item->getType['title']}}</td>
                    <td><img src="{{$item->getWebsite['logo']}}" style="width: 150px; height:100px"></td>
                    </a>
                </tr>
                @endforeach
            </table>
            <br>
        </div>
    @else
        <div>
            <h2>Neturite megstamiausiu skelbimu!</h2>
        </div>
    @endif
</div>
@endsection
