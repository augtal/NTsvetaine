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
        {{dd($data2)}}
        {{dd($item)}}
        <tr>
            <td><a href="{{$item['url']}}"><img src="{{$item['thumbnail']}}"></td>
            <td>{{$item['price']}}</td>
            <td>{{$item['category']}}</td>
            <td>{{$item['type']}}</td>
            <td>{{$item['url']}}</td>
        </tr>
        @endforeach
    </table>
    <div>
    {{ $data->links() }}
    </div>
</div>
@endsection
