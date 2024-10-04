//used for address form drop and drag

jQuery(document).ready(function( $)  {
	console.log("maps.js");
    var mapDiv=document.getElementById("map");
    if(!mapDiv)
	{
		console.log('No div with ID map ');
		return;
	}

	//create map at beginLat,beginLng
	var map;
    var marker=null;
  	var latlng = new google.maps.LatLng(beginLat, beginLng);
  
  	
  	var myOptions = {zoom: zoom,center: latlng,mapTypeId: google.maps.MapTypeId.ROADMAP     };
  	var map = new google.maps.Map(mapDiv, myOptions); 
  	
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
  			map.setZoom(zoom);//set the coordinates once map has centred
  			var coords = marker.getPosition();
  	
  	}
  
	function geolocate(e)  {
		if ( e)e.preventDefault();//don't reload page
		console.log("geolocate fired");
		
		var geocoder = new google.maps.Geocoder();
  		var address = $('#address').val();//grab entered address
  		geocoder.geocode({'address' : address}, function(result, status)
		{
			if(status!='ZERO_RESULTS')
			{// this returns a latlng
				console.log("line 47 "+ca_method);
				console.log("line 48 id:"+ ID);
				var location = result[0].geometry.location;
				console.log("Location "+location);
				map.setCenter(location);
				// clear previous markers
				if(marker!=null)marker.setMap(null);
				marker = new google.maps.Marker({
					draggable: true,
					position: location, 
					map: map,
					title: "Your location"
				});
				map.setZoom(17);//set the coordinates once map has centred
				var coords = marker.getPosition();
				$("#lat").val(coords.lat() );
				$("#lng").val(coords.lng() );
				sendData();
				//listen for marker being dragged and set new coords
				google.maps.event.addListener(marker,'dragend',function(overlay,point)  {
					coords = marker.getPosition();
					$("#lat").val(coords.lat() );
					$("#lng").val(coords.lng() );
					sendData();
				});
				
				function sendData(){
					$(".church-admin-response-errors").html('');
					var data= {"action":"church_admin","method":ca_method,"what":"geocode","id":ID,"nonce":nonce,"lat":coords.lat(),"lng":coords.lng()};
						console.log(data);
						$.getJSON({
							url: ajaxurl,
							type: "post",
							data:  data,
							success: function(response) {
								console.log(response);
									$("#"+response.div).val();
									$("#"+response.div).append("'.esc_html( __('Updated','church-admin' ) ).'");
									if(response.errors){$(".church-admin-response-errors").html(response.errors);}
								}
						});
				}
				//what three words
				if(typeof useW3W !== 'undefined')
				{
					var w3w="https://api.what3words.com/v3/convert-to-3wa?key=7F5FVM60&coordinates="+coords.lat()+"%2C"+coords.lng()+"&language="+w3wLanguage+"&format=json";
					$.ajax({
						dataType: "json",
						url: w3w,
						success: function(data)  {
							$("#what-three-words").val(data.words);
							console.log(data.words)
						}
					});
				}
			}else{alert("Google maps couldn't find the address, please adjust and try again");}
				
    	});
	};
  		
	//look for click on #geocode_address
	$('body').on('click','#geocode_address',function(e)  { e.preventDefault();geolocate()});
	$('body').on('blur','#address',function()  {geolocate()});



});
