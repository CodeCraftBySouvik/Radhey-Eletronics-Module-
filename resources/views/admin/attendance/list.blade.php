@extends('admin.layouts.app')
@section('page', 'Salesman Locations')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Salesman Locations</li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="ledgerForm">
    <div class="row">       
        <div class="col-12">
            <div class="row g-3 align-items-end">                
                <div class="col-6 col-sm-4">
                    <div class="form-group">
                        <label for="" id=""> Salesman</label> <span class="text-danger">*</span>                           
                        <select name="staff_id" class="form-control select-md dates" id="">
                            <option value="" selected hidden>Select a salesman</option>
                            @forelse ($staff as $user)
                            <option value="{{$user->id}}" @if($user->id == $staff_id) selected @endif>{{$user->name}}</option>
                            @empty
                            <option value="">No salesman found ... </option> 
                            @endforelse                            
                        </select>    
                    </div>
                </div>                
                <div class="col-6 col-sm-4">
                    <div class="form-group">
                        <label for="">Date</label><span class="text-danger">*</span>  
                        <input type="date" name="entry_date"  id="entry_date" class="form-control select-md dates" value="{{ $entry_date }}" placeholder="To" max="{{ date('Y-m-d') }}"  autocomplete="off">  
                    </div>
                </div>                
                <div class="col-6 col-sm-4">
                    <button type="submit" class="btn btn-success btn-custom">Search</button>
                </div>          
            </div>
        </div>               
    </div>
    </form>  
    @if (!empty($data) && count($data) > 3 )
    <div class="row">
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
        <div class="col-sm-3">
            <h6 class="title-d">
                Total Distance Covered: <span>{{$user_attendances->total_distance_text}}</span>
            </h6>
            <h6 class="table-title">Store Visits</h6>
            <table class="table">
                <thead>
                    <tr>
                        <th width="20%">Time</th>
                        {{-- <th width="70%">Location</th> --}}
                        <th width="80%">Store</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($store_visited_locations as $item)
                    <tr>
                        <td><span>{{date('H:i', strtotime($item->created_at))}}</span></td>
                        {{-- <td>
                            <span>{{$item->latitude}},{{$item->longitude}}</span>
                        </td>                         --}}
                        <td>
                            <span>{{$item->store_name}}</span>
                        </td>                        
                    </tr>
                    @empty
                    <tr>
                        <span>No location found</span>
                    </tr>
                    @endforelse
                    
                </tbody>
            </table>
        </div>
        <div class="col-sm-9">                             
            <div id="map-layer"></div>        
        </div>   
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
                            stopover: true,
                            // time: '10:30'
                        })
                    }
                    console.log(wapointLast)
                    var start = "{{$user_attendances->start_latitude}},{{$user_attendances->start_longitude}}" ;
                    var end = "{{$user_attendances->end_latitude}},{{$user_attendances->end_longitude}}" ;
                    console.log(start)                      
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
        <script async defer src="https://maps.googleapis.com/maps/api/js?key={{$google_api_key}}&callback=initMap"></script>   
    </div> 
    @else
    <div class="row">
        <p>No recorde found</p>
    </div>
    @endif            
</section>




@endsection
