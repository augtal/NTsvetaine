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
                    <td>@if ($item['archived'] == 1)
                        <td class="align-middle">
                            <div class="d-flex justify-content-center">
                                <h4>Archyvuota</h4>
                            </div>
                        </td>
                    @else
                        <td>
                            @if (file_exists('images/AdvertisementsThumbnails/'.$item['id'].".jpg"))
                                <a href="{{$item['url']}}"><img src="{{url('images/AdvertisementsThumbnails/'.$item['id'].".jpg")}}" style="width: 250px; height:175px"></a>
                            @else
                                <a href="{{$item['url']}}"><img src="{{$item['thumbnail']}}" style="width: 250px; height:175px"></a>
                            @endif
                        </td>
                    @endif
                    </td>
                    <td><a href="/listing/{{$item['id']}}">{{$item['title']}}</td></a>
                    <td>{{$item->getLastestPrice['price']}} €</td>
                    <td>{{$item->getCategory['title']}}</td>
                    <td>{{$item->getType['title']}}</td>
                    <td><img src="{{url('images/RealEstateWebsiteLogos/'.$item->getWebsite['id'].".png")}}" style="width: 150px; height:30px"></td>
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
