@extends('layouts.app')

@section('content')
<div class="container">
    Advertisement List
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
            <td><a href="{{$item['url']}}"><img src="{{$item['thumbnail']}}"></td></a>
            <td><a href="/ads/{{$item['id']}}">{{$item['title']}}</td></a>
            <td>{{$item->getLastestPrice['price']}} €</td>
            <td>{{$item->getCategory['title']}}</td>
            <td>{{$item->getType['title']}}</td>
            <td><img src="{{$item->getWebsite['logo']}}"></td>
            </a>
        </tr>
        @endforeach
    </table>
    <div>
    {{ $data->links() }}
    </div>
</div>
@endsection
