@extends('admin.layouts.app')
@section('page', 'Locations')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li>Staff Management</li>
        <li><a href="{{ route('admin.staff.index') }}">Staff</a></li>
        <li>Store Visit</li>
        <li>Locations</li>
    </ul>
    <style>
        html,
        body,
        #map-canvas {
        height: 100%;
        width: 100%;
        margin: 0px;
        padding: 0px
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <div class="search__filter">
                <div class="row align-items-center justify-content-between">
                    <div class="row">
                        <div class="col">                
                            <div class="row g-3 align-items-center">                            
                                <div class="col-auto">
                                    <a href="{{ route('admin.visit.index', $data[0]->user_id) }}" class="btn btn-outline-danger btn-sm">Back</a>
                                </div>
                            </div>                    
                        </div>
                    </div>
                </div>
            </div>                        
            
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAkwlSsaYwOB0T79ZgKI7_vgQNbRxzD1xc"></script>
            <div id="map_canvas" style="border: 2px solid #3872ac; height:500px;"></div>
                       
        </div>
    </div>
</section>
<script>   
    var locations = [
        // ['Location 1 Name', 'New York, NY', 'Location 1 URL'],
        // ['Location 2 Name', 'Newark, NJ', 'Location 2 URL'],
        // ['Location 3 Name', 'Philadelphia, PA', 'Location 3 URL']
        ['Location 1', 22.570943,88.432554, 1],
        ['Location 2', 22.5706829,88.4327069, 2],
        ['Location 3', 22.5706978,88.4333667, 3],
        ['Location 4', 22.5707034,88.4351396, 4],
        ['Location 5', 22.5709504,88.4353334, 5],
        ['Location 6', 22.5712836,88.4355071, 6],
        ['Location 7', 22.5715764,88.435611, 7],
    ];

    var geocoder;
    var map;
    var bounds = new google.maps.LatLngBounds();

    function initialize() {
        map = new google.maps.Map(
            document.getElementById("map_canvas"), {
            center: new google.maps.LatLng(22.570943,88.432554),
            zoom: 5,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        geocoder = new google.maps.Geocoder();

        for (i = 0; i < locations.length; i++) {
            geocodeAddress(locations, i);
        }
    }
    google.maps.event.addDomListener(window, "load", initialize);

    function geocodeAddress(locations, i) {
        var title = locations[i][0];
        var address = locations[i][1]+', '+locations[i][2];
        
        geocoder.geocode({
            'address': locations[i][1]+', '+locations[i][2]
        },

        function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var marker = new google.maps.Marker({
                    icon: 'http://maps.google.com/mapfiles/ms/icons/blue.png',
                    map: map,
                    position: results[0].geometry.location,
                    title: title,
                    animation: google.maps.Animation.DROP,
                    address: address,
                    url: url
                })
                infoWindow(marker, map, title, address, url);
                bounds.extend(marker.getPosition());
                map.fitBounds(bounds);
            } else {
                alert("geocode of " + address + " failed:" + status);
            }
        });
    }

    function infoWindow(marker, map, title, address, url) {
        google.maps.event.addListener(marker, 'click', function() {
            var html = "<div><h3>" + title + "</h3><p>" + address + "<br></div></p></div>";
            iw = new google.maps.InfoWindow({
            content: html,
            maxWidth: 350
            });
            iw.open(map, marker);
        });
    }

    // function createMarker(results) {
    //     var marker = new google.maps.Marker({
    //         icon: 'http://maps.google.com/mapfiles/ms/icons/blue.png',
    //         map: map,
    //         position: results[0].geometry.location,
    //         title: title,
    //         animation: google.maps.Animation.DROP,
    //         address: address,
    //         url: ''
    //     })
    //     bounds.extend(marker.getPosition());
    //     map.fitBounds(bounds);
    //     infoWindow(marker, map, title, address, url);
    //     return marker;
    // }

</script>

@endsection
