@extends('layouts.app')

@section('content')
<div class="container">
    Advertisement List
    <table style="width:80%">
        <tr>
            <th>Nuotrauka</th>
            <th>Kaina</th>
            <th>Kategorija</th>
            <th>Tipas</th>
            <th>Svetainės logo</th>
        </tr>
        @foreach ($data as $item)
        <tr>
            <a href="{{$item['url']}}"><td><img src="{{$item['thumbnail']}}"></td></a>
            <td>{{$item->lastestPrice['price']}} €</td>
            <td>{{$item->getCategory['title']}}</td>
            <td>{{$item->getType['title']}}</td>
            <td><img src="{{$item->getWebsite['logo']}}"></td>
        </tr>
        @endforeach
    </table>
    <div>
    {{ $data->links() }}
    </div>
</div>
@endsection
