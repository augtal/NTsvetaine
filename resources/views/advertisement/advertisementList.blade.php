@extends('layouts.app')

@section('content')
<div class="container">
    Advertisement List
    @if($data->count() > 0)
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
                    <td><a href="/ads/{{$item['id']}}">{{$item['title']}}</td></a>
                    <td>{{$item->getLastestPrice['price']}} €</td>
                    <td>{{$item->getCategory['title']}}</td>
                    <td>{{$item->getType['title']}}</td>
                    <td><img src="{{$item->getWebsite['logo']}}" style="width: 150px; height:100px"></td>
                    </a>
                </tr>
                @endforeach
            </table>
            <br>
            <div>
            {{ $data->links() }}
            </div>
        </div>
    @else
        <div>
            <h2>No Advertisements yet!</h2>
        </div>
    @endif
</div>
@endsection
