@extends('layouts.app')

@section('content')
<div class="container">
    <div>
        <table style="width:100%">
            <tr>
                <th>Pavadinimas</th>
                <th>Aprašymas</th>
                <th>Dažnumas</th>
            </tr>
            <tr>
                <td>{{$notificationData->title}}</td>
                <td>{{$notificationData->description}}</td>
                <td>@if ($notificationData->frequency == 1)
                    Kai atsiranda naujas skelbimas zonoje
                @elseif ($notificationData->frequency == 2)
                    Kada pasikeicia skelbimu zonoje kaina
                @endif
                </td>
            </tr>
        </table>
        <br>
    </div>
    <br>
    <div id='map' style="height: 675px; width: 100%;"></div>
    <br>
    @if($advertisements->count() > 0)
        <div class="table-responsive">
            <h4> Rasta skelbimų pagal parametrus: {{$advertisements->total()}} </h4>
            <br>
            <table class="table table-hover" style="width:100%">
                <tr>
                    <th>Nuotrauka</th>
                    <th>Pavadinimas</th>
                    <th>Kaina</th>
                    <th>Kainos pokytis</th>
                    <th>Kategorija</th>
                    <th>Tipas</th>
                    <th>Svetainės logo</th>
                </tr>
                @foreach ($advertisements as $item)
                <tr>
                    <td>
                    @if (file_exists('images/AdvertisementsThumbnails/'.$item['id'].".jpg"))
                        <a href="{{$item['url']}}"><img src="{{url('images/AdvertisementsThumbnails/'.$item['id'].".jpg")}}" style="width: 250px; height:175px"></a>
                    @else
                        <a href="{{$item['url']}}"><img src="{{$item['thumbnail']}}" style="width: 250px; height:175px"></a>
                    @endif
                    </td>
                    <td><a href="/listing/{{$item['id']}}">{{$item['title']}}</a></td>
                    <td>{{$item->getLastestPrice['price']}} €</td>
                    <th><p style="color: green"> +0%</p></th>
                    <td>{{$item->getCategory['title']}}</td>
                    <td>{{$item->getType['title']}}</td>
                    <td>
                        <img src="{{url('images/RealEstateWebsiteLogos/'.$item->getWebsite['id'].".png")}}" style="width: 150px; height:30px">
                    </td>
                </tr>
                @endforeach
            </table>
            <br>
            <div>
            {{ $advertisements->links() }}
            </div>
        </div>
    @else
        <div>
            <h2>Nerasta skelbimų pagal pažymėta zona!</h2>
        </div>
    @endif
</div>
@endsection

@section('script')
    <script async
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=drawing&region=LTU&language=lt">
    </script>
    <script src="https://unpkg.com/@googlemaps/markerclustererplus/dist/index.min.js"></script>
    <script>
        const mapData = @json($mapData);

        google.maps.event.addDomListener(window, 'load', initMap);

        function initMap() {
            var markers = new Array();
            var shapesData = @json($shapesData);

            const centerMap = { lat: 55.329905, lng: 23.905512 };
            let mapOptions = {
                zoom: 8,
                minZoom: 7,
                maxZoom: 17,
                center: centerMap,
            }
            const map = new google.maps.Map(document.getElementById("map"), mapOptions);

            for(i in mapData)
            {
                place = mapData[i];
                if(place.get_location.lat && place.get_location.lng)
                {
                    let marker = new google.maps.Marker({
                        position: new google.maps.LatLng(place.get_location.lat, place.get_location.lng),
                        map: map,
                    });

                    let infowindow = new google.maps.InfoWindow();
                    google.maps.event.addListener(marker, 'click', (function (marker, place) {
                        return function () {
                            infowindow.setContent(infoWindowContent(place))
                            infowindow.open(map, marker);
                        }
                    })(marker, place));

                    markers.push(marker);
                }
            }

            new MarkerClusterer(map, markers, {
                imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
            });

            drawSavedShapes(map, shapesData);
        }

        function infoWindowContent(place)
        {
            let content = `
                        <div>
                            <img src="`+place.thumbnail+`" style="width: 100px; height:75px">
                        </div>
                        </br>
                        <div>
                            <a href="/listing/`+place.id+`">Skelbimo issami info</a>
                        </div>
                            `;
            return content;
        }

        function drawSavedShapes(map, shapesData){
            for(i in shapesData)
            {
                shape = shapesData[i];
                if(shape['type'] == 'polygon'){
                    let drawShape = new google.maps.Polygon({
                        paths: shape['cords'],
                        fillColor: "#a0ff7a",
                        fillOpacity: 0.35,
                        strokeWeight: 2,
                    });

                    drawShape.setMap(map);
                }
                else if(shape['type'] == 'circle'){
                    let drawShape = new google.maps.Circle({
                        center: shape['cords']['center'],
                        radius: shape['cords']['radius'],
                        fillColor: "#a0ff7a",
                        fillOpacity: 0.35,
                        strokeWeight: 2,
                    });

                    drawShape.setMap(map);
                }
                else if(shape['type'] == 'rectangle'){
                    let drawShape = new google.maps.Circle({
                        bounds: shape['cords'],
                        fillColor: "#a0ff7a",
                        fillOpacity: 0.35,
                        strokeWeight: 2,
                    });

                    drawShape.setMap(map);
                }
            }
        }
    </script>
@endsection