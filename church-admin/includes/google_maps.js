//used by shortcode and block church_admin_map
function load(lat,lng,xml_url,zoom,translation) {
	console.log('google_maps.js');
		console.log("Centre lat: "+lat);
		console.log("Centre lng: "+lng);
		console.log("xml_url: "+xml_url);
		console.log("zoom: "+zoom);
	var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var labelIndex = 0;
		var mapOptions = {
			zoom: zoom,
			center: new google.maps.LatLng(lat, lng),
			mapTypeId: 'roadmap'
		};
		infoWindow = new google.maps.InfoWindow;
		var map = new google.maps.Map(document.getElementById('church-admin-member-map'), mapOptions);
	
		
	
	
	jQuery.ajax({
		type: "GET",
		async: true,
		url: xml_url,
		dataType: "xml",
		success:
		function (xml) {
			map.markers=new Array();
			function bindInfoWindow(marker, map, infowindow, contentString) {
      			google.maps.event.addListener(marker, 'click', function() {
					infowindow.close(map, marker);
        			infowindow.setContent(contentString);
        			infowindow.open(map, marker);
         			google.maps.event.addListener(infowindow, 'domready', function()  {}); 
      			});
			};
			
			var group = new Array();
			var groups = new Array();
			
			var people = xml.documentElement.getElementsByTagName("marker");
			for (var i = 0; i < people.length; i++) {
				console.log("*** Person ***")
				var personlat = people[i].getAttribute('lat');
				console.log("Lat :"+ personlat);
				var personlng = people[i].getAttribute('lng');
				console.log("Lng :"+ personlng)
				var latLng = new google.maps.LatLng(personlat, personlng);
				
				var information= '<strong>'+ people[i].getAttribute('adults_names') + '</strong><br>'+translation[3]+': '+ people[i].getAttribute('smallgroup_name') +'<br>' + people[i].getAttribute('address');
				console.log("info: "+ information);
				var smallgroupid=people[i].getAttribute('smallgroup_id');
				if(smallgroupid && !groups.includes(smallgroupid) && people[i].getAttribute('smallgroup_lat') &&  people[i].getAttribute('smallgroup_lng') )
				{
					console.log('Creating group marker');
					//create a small group marker
					var grplatLng = new google.maps.LatLng(people[i].getAttribute('smallgroup_lat'), people[i].getAttribute('smallgroup_lng') );
					var grpmarker = new google.maps.Marker({
						position:  grplatLng,
						map: map,
						icon: 'https://maps.google.com/mapfiles/kml/paddle/blu-circle.png',
					});
					map.markers.push(grpmarker);
					var grpinformation='<strong>'+translation[0]+'</strong><br>'+people[i].getAttribute('smallgroup_name');
            		bindInfoWindow(grpmarker, map, infoWindow, grpinformation);
					groups.push(smallgroupid);
				}
				console.log("Groups Object")
				console.log(groups);
				var label = people[i].getAttribute('smallgroup_initials')
				var icon='https://maps.google.com/mapfiles/kml/paddle/red-circle.png';
				if(smallgroupid &&smallgroupid!=1)  {icon='https://maps.google.com/mapfiles/kml/paddle/grn-circle.png';}
				
				var marker = new google.maps.Marker({
					position:  latLng,
					map: map,
					icon: icon,
					title:people[i].adults_names
				});
				map.markers.push(marker);
            	bindInfoWindow(marker, map, infoWindow, information);
				
			}
			
		}
	});
}