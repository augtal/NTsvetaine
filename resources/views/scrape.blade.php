@extends('layouts.app')

@section('content')
<div class="container">
    Scrapper
    <table style="width:80%">
        <tr>
            <th>Nuotrauka</th>
            <th>Kaina</th>
            <th>Aprasas</th>
        </tr>
        @foreach ($data as $item)
        <tr>
            <td><a href="{{$item['url']}}"><img src="{{$item['img']}}"></td>
            <td>{{$item['price']}}</td>
            <td>{{$item['description']}}</td>
        </tr>
        @endforeach
    </table>
</div>
@endsection
