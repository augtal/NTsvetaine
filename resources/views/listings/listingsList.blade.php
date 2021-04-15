@extends('layouts.app')

@section('content')
<div class="container">
    <div id='map' style="height: 675px; width: 100%;"></div>

    <div>Skelbimu sarasas</div>
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
                    <!--
                    <td><a href="{{$item['url']}}"><img src="{{$item['thumbnail']}}" style="width: 250px; height:175px"></td></a>
                    <td><a href="/listing/{{$item['id']}}">{{$item['title']}}</a></td>
                    <td>{{$item->getLastestPrice['price']}} €</td>
                    <td>{{$item->getCategory['title']}}</td>
                    <td>{{$item->getType['title']}}</td>
                    <td><img src="{{$item->getWebsite['logo']}}" style="width: 150px; height:100px"></td>
                    </a>
                    -->
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
            <h2>Neturime skelbimu!</h2>
        </div>
    @endif
</div>
@endsection

@section('script')
    <script async
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&region=LTU&language=lt">
    </script>
    <script src="https://unpkg.com/@googlemaps/markerclustererplus/dist/index.min.js"></script>
    <script>
        const mapData = @json($mapData);

        function initMap() {
            const centerMap = { lat: 55.329905, lng: 23.905512 };
            var mapOptions = {
                zoom: 8,
                minZoom: 7,
                maxZoom: 17,
                center: centerMap,
            }

            const map = new google.maps.Map(document.getElementById("map"), mapOptions);

            const markers = new Array();
            for(i in mapData)
            {
                place = mapData[i];
                if(place.lat && place.lng)
                {
                    var marker = new google.maps.Marker({
                        position: new google.maps.LatLng(place.lat, place.lng),
                        map: map,
                    });

                    var infowindow = new google.maps.InfoWindow();
                    google.maps.event.addListener(marker, 'click', (function (marker, place) {
                        return function () {
                            infowindow.setContent(generateContent(place))
                            infowindow.open(map, marker);
                        }
                    })(marker, place));

                    markers.push(marker);
                }
            }

            new MarkerClusterer(map, markers, {
                imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
            });
        }

        google.maps.event.addDomListener(window, 'load', initMap);

        function generateContent(place)
        {
            console.log(place);
            var content = `
                        <div>
                            <a href="/listing/`+place.id+`">Skelbimo issami info</a>
                        </div>'
                            `;
            return content;
        }
    </script>
@endsection
