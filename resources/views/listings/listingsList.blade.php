@extends('layouts.app')

@section('content')
<div class="container">
    <div>
        <form action="/" method="GET">
            <input type="text" name="search" id="search" value="{{ $searchTerm }}">
            <button type="submit" class="btn btn-primary">
                Ieškoti
            </button>
        </form>
    </div>
    <br>

    <div id="map" style="height: 675px; width: 100%;"></div>
    <br>
    <div id="map-save" style="display: none">
        <form id="saveShapes" action="/showSaveNotification" method="post">
            @csrf
            <input type="hidden" name="saveShapesValues" id="saveShapesValues">
            <button id="saveShapesButton" type="submit" class="btn btn-warning">Užsaugoti zona</button>
        </form>
        <br>
    </div>

    <div>
        <button id="showFiltersButton" onclick="showFilters()" class="btn btn-success">Filtrai</button>

        
        <div id="filter-settings" style="display: none">
            <section class="border border-light p-3 mb-4">
                <form action="/" method="GET">
                    <div>
                        <label for="min_price">Skelbimo mažiausia kaina: </label>
                        <input type="number" id="min_price" name="filters[min_price]">
                    </div>

                    <div>
                        <label for="max_price">Skelbimo didžiausia kaina: </label>
                        <input type="number" id="max_price" name="filters[max_price]">
                    </div>

                    <div>
                        <label for="type">Skelbimo tipas: </label>
                        <select id="type" name="filters[type]">
                            <option value="" selected>-- Pasirinkite tipa --</option>

                            @foreach ($filterInfo['types'] as $type)
                            <option value="{{$type['id']}}">{{$type['title']}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="category">Skelbimo kategorija: </label>
                        <select id="category" name="filters[category]">
                            <option value="" selected>-- Pasirinkite kategorija --</option>

                            @foreach ($filterInfo['categories'] as $category)
                            <option value="{{$category['id']}}">{{$category['title']}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="REwebsites">Nekilnojamo turto svetainės: </label>
                        <select id="REwebsites" name="filters[REwebsites]">
                            <option value="" selected>-- Pasirinkite svetainę --</option>

                            @foreach ($filterInfo['REwebsites'] as $website)
                            <option value="{{$website['id']}}">{{$website['title']}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="archived" name="filters[archived]">
                        <label class="custom-control-label" for="archived">Nerodyti archyvuotu skelbimu:</label>
                    </div>
                    
                    <br>
                    <button type="submit" class="btn btn-info">Filtruoti</button>
                </form>
            </section>
        </div>
    </div>
    <br>    
    @if($data->count() > 0)
        <div>
            {{ $data->links() }}
            <h4> Rasta skelbimu: {{$data->total()}} </h4>
            <table class="table table-hover" style="width:100%">
                <tr>
                    <th>Nuotrauka</th>
                    <th>Pavadinimas</th>
                    <th>Kaina</th>
                    <th>Kategorija</th>
                    <th>Tipas</th>
                    <th>Svetainės logo</th>
                    @auth
                        @if (auth()->user()->isAdmin())
                            <th>Veiksmai</th>
                        @endif
                    @endauth
                </tr>
                @foreach ($data as $item)
                <tr>
                    @if ($item['archived'] == 1)
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
                    <td><a href="/listing/{{$item['id']}}">{{$item['title']}}</a></td>
                    <td>
                        @if ($item->getType['id'] == 2)
                        {{$item->getLastestPrice['price']}} € / mėn.
                    @else
                        {{$item->getLastestPrice['price']}} €
                    @endif
                    </td>
                    <td>{{$item->getCategory['title']}}</td>
                    <td>{{$item->getType['title']}}</td>
                    <td>
                        <img src="{{url('images/RealEstateWebsiteLogos/'.$item->getWebsite['id'].".png")}}" style="width: 150px; height:30px">
                    </td>
                    @auth
                        @if (auth()->user()->isAdmin())
                            @if ($item['archived'] == 1)
                                <td><a href="/archive/{{$item['id']}}" class="btn btn-info">Išarchyvuoti</a></td>
                            @else
                                <td><a href="/archive/{{$item['id']}}" class="btn btn-info">Archyvuoti</a></td>
                            @endif
                        @endif
                    @endauth
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
    <script type="text/javascript">
        $(window).on('load', function() {
            $('#myModal').modal('show');
        });
    </script>
    <script>
        function showFilters() {
            var x = document.getElementById("filter-settings");
            if (x.style.display === "none") {
                x.style.display = "block";
            } 
            else {
                x.style.display = "none";
            }
        }
    </script>
    <script>
        const mapData = @json($mapData);

        google.maps.event.addDomListener(window, 'load', initMap);

        function initMap() {
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
                        'radius': event.overlay.getRadius(),
                        'bounds': event.overlay.getBounds()
                    };
                    shapes.push({'type':'circle','cords': cordArray });
                }
                else if(event.type == 'rectangle'){
                    let bounds = event.overlay.getBounds();
                    let cordArray = bounds.toJSON()
                    shapes.push({'type':'rectangle','cords': cordArray });
                }

                document.getElementById("saveShapesValues").value = JSON.stringify(shapes);
                document.getElementById("map-save").style.display = "block";
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
