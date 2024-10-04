
function sgload(lat,lng,xml_url,zoom) {

		console.log("Using smallgroup_maps.js");
		console.log('Lat:'+ lat +' Lng: '+lng);
		console.log('XML URL '+ xml_url);
        var myOptions = {center: new google.maps.LatLng(lat, lng),zoom: zoom,mapTypeId: google.maps.MapTypeId.ROADMAP};
        var map = new google.maps.Map(document.getElementById("group-map"),myOptions);
		// Change this depending on the name of your PHP file
		downloadUrl(xml_url, function(data)
		{
			var xml = data.responseXML;
			var markers = xml.documentElement.getElementsByTagName("marker");
			var smallgroup=new Array();
			var smallgroupinfo = new Array();
			var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			var labelIndex = 0;
			for (var i = 0; i < markers.length; i++)
			{
				var infowindow;
				var point = new google.maps.LatLng(parseFloat(markers[i].getAttribute("lat") ),parseFloat(markers[i].getAttribute("lng") ));
				var pinColor =markers[i].getAttribute("pinColor");
				var id =markers[i].getAttribute("smallgroup_id");
				var details='<span class="ca-names">'+markers[i].getAttribute("when")+ ' ' +markers[i].getAttribute("address")+'</span>';
				var group_name=markers[i].getAttribute("smallgroup_name");
				var information ='<strong>'+ group_name + '</strong><br>'+ details;
				smallgroup[i]='<strong>'+ labels[i% labels.length]+') ' +markers[i].getAttribute("smallgroup_name") + '</strong>: ' + details +'<br>';


				function createMarker(information, point)
				{
					var marker = new google.maps.Marker({map: map,position: point});
					google.maps.event.addListener(marker, "click", function()
					{
						if (infowindow) infowindow.close();
						console.log('Info:'+information);
						infowindow = new google.maps.InfoWindow({content:'<span style="color:black">'+ information+'</span>'});
						infowindow.open(map, marker);
					});
					return marker;
				}
				var marker = createMarker(information, point);
			}
			


		});
		function downloadUrl(url, callback)
		{
			var request = window.ActiveXObject ?
			new ActiveXObject('Microsoft.XMLHTTP') :
			new XMLHttpRequest;
			request.onreadystatechange = function()
			{
				if (request.readyState == 4)
				{
					request.onreadystatechange = doNothing;
				callback(request, request.status);
				}
			};
			request.open('GET', url, true);
			request.send(null);
		}
		function doNothing() {}
}
