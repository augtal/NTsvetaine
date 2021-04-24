@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Pranesimas') }}</div>

                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-md-4 col-form-label text-md-right"> {{ __('Pavadinimas') }} </div>

                        <div class="col-md-6"> {{$data->title}} </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-4 col-form-label text-md-right"> {{ __('Aprasymas') }} </div>

                        <div class="col-md-6"> {{$data->description}} </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-4 col-form-label text-md-right"> {{ __('Pavadinimas') }} </div>

                        <div class="col-md-6">
                            @if ($data->frequency == 1)
                                Kiekviena diena
                            @elseif ($data->frequency == 2)
                                Kai atsiranda naujas skelbimas zonoje
                            @elseif ($data->frequency == 3)
                                Kada pasikeicia skelbimu zonoje kaina
                            @endif
                        </div>
                    </div>
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
                if(place.lat && place.lng)
                {
                    let marker = new google.maps.Marker({
                        position: new google.maps.LatLng(place.lat, place.lng),
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
        }
    </script>
@endsection