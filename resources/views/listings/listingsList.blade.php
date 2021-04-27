@extends('layouts.app')

@section('content')
<div class="container">
    <div id='map' style="height: 675px; width: 100%;"></div>
    <br>
    <form id="saveShapes" action="/showSaveNotification" method="post">
        @csrf

        <input type="hidden" name="saveShapesValues" id="saveShapesValues">
        <button id="saveShapesButton" type="submit" class="btn btn-danger">Save</button>
    </form>

    <div>Skelbimu sarasas</div>
    <form action="/" method="GET">
        <input type="text" name="search" id="search" value="{{ $searchTerm }}">
        <input type="hidden" name="search-lat" id="search-lat" value="{{ old('latitude') ?? '0' }}" />
        <input type="hidden" name="search-lng" id="search-lng" value="{{ old('longitude') ?? '0' }}" />

        <button type="submit">Paieska</button>
    </form>
    @if($data->count() > 0)
        <div>
            {{ $data->links() }}
            <h4> Rasta skelbimu: {{$data->total()}} </h4>

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
                    <td><a href="/listing/{{$item['id']}}">{{$item['title']}}</a></td>
                    <td>{{$item->getLastestPrice['price']}} €</td>
                    <td>{{$item->getCategory['title']}}</td>
                    <td>{{$item->getType['title']}}</td>
                    <td><img src="{{$item->getWebsite['logo']}}" style="width: 150px; height:100px"></td>
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
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=drawing,places&region=LTU&language=lt">
    </script>
    <script src="https://unpkg.com/@googlemaps/markerclustererplus/dist/index.min.js"></script>
    <script>
        const mapData = @json($mapData);

        google.maps.event.addDomListener(window, 'load', initMap);

        function initMap() {
            document.getElementById("saveShapesButton").style.visibility = "hidden";

            var markers = new Array();
            var cancelShape = false;
            var shapes = new Array();

            const centerMap = { lat: 55.329905, lng: 23.905512 };
            let mapOptions = {
                zoom: 7,
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


            let drawingManager = new google.maps.drawing.DrawingManager({
                drawingControl: true,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.RIGHT_CENTER,
                    drawingModes: [
                        google.maps.drawing.OverlayType.POLYGON,
                        google.maps.drawing.OverlayType.CIRCLE,
                        google.maps.drawing.OverlayType.RECTANGLE,
                    ],
                },
                polygonOptions: {
                    fillColor: "#7adcff",
                    fillOpacity: 0.35,
                    strokeWeight: 2,
                    clickable: true,
                    editable: true,
                },
                circleOptions: {
                    fillColor: "#7adcff",
                    fillOpacity: 0.35,
                    strokeWeight: 2,
                    clickable: true,
                    editable: true,
                },
                rectangleOptions: {
                    fillColor: "#7adcff",
                    fillOpacity: 0.35,
                    strokeWeight: 2,
                    clickable: true,
                    editable: true,
                }
            });
            drawingManager.setMap(map);

            $(document).keydown(function (event) {
                if (event.keyCode === 27) { 
                    cancelShape = true;
                    drawingManager.setDrawingMode(null);
                }
            });

            google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
                drawingManager.setDrawingMode(null);

                let lastDrawnShape = event.overlay;
                if (cancelShape) {
                    cancelShape = false;
                    lastDrawnShape.setMap(null);
                    return;
                }

                if (event.type == 'polygon') {
                    let path = event.overlay.getPath();
                    let cordArray = new Array();
                    path.forEach(function(cord, i){
                        cordArray[i] = {'lat':cord.lat(), 'lng':cord.lng()};
                    });
                    shapes.push({'type':'polygon','cords': cordArray });
                }
                else if(event.type == 'circle'){
                    let center = event.overlay.getCenter();
                    let cordArray = new Array();
                    cordArray = {
                        'center': {'lat':center.lat(), 'lng':center.lng()},
                        'radius': event.overlay.getRadius()
                    };
                    shapes.push({'type':'circle','cords': cordArray });
                }
                else if(event.type == 'rectangle'){
                    let bounds = event.overlay.getBounds();
                    let cordArray = bounds.toJSON()
                    shapes.push({'type':'rectangle','cords': cordArray });
                }

                document.getElementById("saveShapesValues").value = JSON.stringify(shapes);
                document.getElementById("saveShapesButton").style.visibility = "visible";
            });

            initAutocomplete(map);
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

        function initAutocomplete(map) {
            const input = document.getElementById("search");
            const searchBox = new google.maps.places.SearchBox(input);
            
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });
            let markers = [];
            
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                return;
                }
                
                markers.forEach((marker) => {
                marker.setMap(null);
                });
                markers = [];
                
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                if (!place.geometry || !place.geometry.location) {
                    console.log("Returned place contains no geometry");
                    return;
                }
                const icon = {
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(25, 25),
                };
                
                markers.push(
                    new google.maps.Marker({
                    map,
                    icon,
                    title: place.name,
                    position: place.geometry.location,
                    })
                );

                if (place.geometry.viewport) {
                    
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
                });
                map.fitBounds(bounds);
            });
        }
    </script>
@endsection
