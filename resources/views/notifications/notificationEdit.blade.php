@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Pranesimo redagavimas') }}</div>

                <div class="card-body">
                    <form method="POST" action="/notification/{{$data->id}}/saveEdit">
                        @csrf
                        <div class="form-group row">
                            <label for="title" class="col-md-4 col-form-label text-md-right">{{ __('Pavadinimas') }}</label>

                            <div class="col-md-6">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ $data->title }}" required autocomplete="title" required autofocus>

                                @error('title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="description" class="col-md-4 col-form-label text-md-right">{{ __('Aprasymas') }}</label>

                            <div class="col-md-6">
                                <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ $data->description }}" autocomplete="description" autofocus>
                                </textarea>

                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="frequency" class="col-md-4 col-form-label text-md-right">{{ __('Daznumas') }}</label>

                            <div class="col-md-6">
                                <select name="frequency" id="frequency" >
                                    @if ($data->frequency == 1)
                                        <option value="1" selected="selected">Kai atsiranda naujas skelbimas zonoje</option>
                                        <option value="2">Kada pasikeicia skelbimu zonoje kaina</option>
                                    @elseif ($data->frequency == 2)
                                        <option value="1">Kai atsiranda naujas skelbimas zonoje</option>
                                        <option value="2" selected="selected">Kada pasikeicia skelbimu zonoje kaina</option>
                                    @endif
                                    
                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="confirmShapesValues" id="confirmShapesValues">

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Uzsaugoti') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div id='map' style="height: 675px; width: 100%;"></div>
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
            var cancelShape = false;
            var shapes = new Array();

            
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

            drawSavedShapes(map, shapesData);

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
                    shapesData.push({'type':'polygon','cords': cordArray });
                }
                else if(event.type == 'circle'){
                    let center = event.overlay.getCenter();
                    let cordArray = new Array();
                    cordArray = {
                        'center': {'lat':center.lat(), 'lng':center.lng()},
                        'radius': event.overlay.getRadius()
                    };
                    shapesData.push({'type':'circle','cords': cordArray });
                }
                else if(event.type == 'rectangle'){
                    let bounds = event.overlay.getBounds();
                    let cordArray = bounds.toJSON()
                    shapesData.push({'type':'rectangle','cords': cordArray });
                }

                document.getElementById("confirmShapesValues").value = JSON.stringify(shapesData);
            });
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
                        clickable: true,
                        editable: true,
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
                        clickable: true,
                        editable: true,
                    });

                    drawShape.setMap(map);
                }
                else if(shape['type'] == 'rectangle'){
                    let drawShape = new google.maps.Circle({
                        bounds: shape['cords'],
                        fillColor: "#a0ff7a",
                        fillOpacity: 0.35,
                        strokeWeight: 2,
                        clickable: true,
                        editable: true,
                    });

                    drawShape.setMap(map);
                }
            }

            document.getElementById("confirmShapesValues").value = JSON.stringify(shapesData);
        }
    </script>
@endsection