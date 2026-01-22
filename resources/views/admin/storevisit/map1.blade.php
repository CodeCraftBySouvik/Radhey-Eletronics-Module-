@extends('admin.layouts.app')
@section('page', 'Locations')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li>Staff Management</li>
        <li><a href="{{ route('admin.staff.index') }}">Staff</a></li>
        <li><a href="{{ route('admin.visit.index',$store_visits->user_id) }}">Store Visit</a></li>
        <li>Locations</li>
    </ul>
    <style>
        #map-layer {
            max-width: 100%;
            min-height: 550px;
        }
        .lbl-locations {
            font-weight: bold;
            margin-bottom: 15px;
        }
        .locations-option {
            display:inline-block;
            margin-right: 15px;
        }
        .btn-draw {
            background: green;
            color: #ffffff;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">            
            <div id="map-layer"></div>                       
        </div>
    </div>
</section>

<script>   
    var map;
    var waypoints;
    function initMap() {
        var mapLayer = document.getElementById("map-layer"); 
        var centerCoordinates = new google.maps.LatLng(28.6139, 77.2090);
        var defaultOptions = { center: centerCoordinates, zoom: 4 }
        map = new google.maps.Map(mapLayer, defaultOptions);

        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        directionsDisplay.setMap(map);
        // var locations = [            
        //     ['Location 1', '22.570943','88.432554'],
        //     ['Location 2', '22.5706829','88.4327069'],
        //     ['Location 3', '22.5706978','88.4333667'],
        //     ['Location 4', '22.5707034','88.4351396'],
        //     ['Location 5', '22.5709504','88.4353334'],
        //     ['Location 6', '22.5712836','88.4355071'],
        //     ['Location 7', '22.5715764','88.435611'],
        // ];
               
        let wapointLast = [];
        $(document).ready(function() {   
            var wapoints = [];
            wapoints = <?php echo json_encode($locationArr); ?>;
            console.log(wapoints);
            
            for(var i=0; i< wapoints.length; i++){
                wapointLast.push({
                    location: wapoints[i],
                    stopover: true
                })
            }
            console.log(wapointLast)
            var start = "{{$store_visits->start_latitude}},{{$store_visits->start_longitude}}" ;
            var end = "{{$store_visits->end_latitude}},{{$store_visits->end_longitude}}" ;
            // console.log(end)                      
            // var start = '22.570943,88.432554';
            // var end = '22.5715764,88.435611';              
            drawPath(directionsService, directionsDisplay,start,end);            
        });

        function drawPath(directionsService, directionsDisplay,start,end) {
            
            directionsService.route({
                origin: start,
                destination: end,
                optimizeWaypoints: true,
                travelMode: 'DRIVING',
                waypoints: wapointLast
            }, function(response, status) {
                if (status === 'OK') {
                    directionsDisplay.setDirections(response);
                } else {
                    window.alert('Problem in showing direction due to ' + status);
                }
            });
        }
        
    }
    

</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAkwlSsaYwOB0T79ZgKI7_vgQNbRxzD1xc&callback=initMap"></script>

@endsection
