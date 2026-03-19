jQuery(document).ready(function( $)  {

	//create map at beginLat,beginLng
	console.log(beginLat + ' '+ beginLng);
	var map;
		var marker=null;
  	var latlng = new google.maps.LatLng(beginLat, beginLng);
  	var zoom=13;
  	//if(beginLat!=51.50351129583287)  {zoom=17;}
  	var myOptions = {zoom: zoom,center: latlng,mapTypeId: google.maps.MapTypeId.ROADMAP     };
  	map = new google.maps.Map(document.getElementById("map"), myOptions);

  	if(beginLat!=51.50351129583287)
  	{//already geolocated so pop a marker on
  		var location = latlng;
      		console.log("Location "+location);
      		map.setCenter(location);
      		// clear previous markers
    		if(marker!=null)marker.setMap(null);
      		marker = new google.maps.Marker({
  				draggable: false,
  				position: location,
  				map: map,
  				title: "Your location"
  			});
  			map.setZoom(17);//set the coordinates once map has centred
  			var coords = marker.getPosition();

  	}

	//look for click on #geocode_address
	$('body').on('click','#geocode_address',function(e)  {
		e.preventDefault();//don't reload page

		$('.address').each(function() {
				var household_id=$(this).attr('id');
				var currentAddress=$(this).val();
				console.log('Look up '+ currentAddress);
				if(currentAddress)
				{
					var geocoder = new google.maps.Geocoder();
					setTimeout(geocoder.geocode({'address' : currentAddress}, function(result, status)  {
						if(status!='ZERO_RESULTS' && result)
	      		       { // this returns a latlng

	      			      var location = result[0].geometry.location;
							marker = new google.maps.Marker({
	  					draggable: false,
	  					position: location,
	  					map: map,
	  					title: "Your location"
	  					});
								map.setCenter(location);
							var coords = marker.getPosition();
							$("#lat"+household_id).val(coords.lat() );
							$("#lng"+household_id).val(coords.lng() );
						}else {console.log("error: "+status)};
					}),250);
				};




});
$('#geocode_address').removeClass('button-primary').addClass('button-secondary');
$('#submit_batch_geocode').addClass('button-primary');
$('#submit_batch_geocode').prop('disabled', false);
});
});
