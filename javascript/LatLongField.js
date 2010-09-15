jQuery(function($) {

	googleGeocoder = new google.maps.Geocoder();

	/**
	 * Geocode the address
	 */
	function geocode(origin, callback) { 
		if (typeof(google)!=='undefined') {
			googleGeocoder.geocode( { 'address': origin }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					// Origin
					var lat1 = results[0].geometry.location.lat();
					var lon1 = results[0].geometry.location.lng();
					var originAddress = results[0].formatted_address;
				
					callback({ lat: lat1, lon: lon1, address: originAddress, message: "OK" });
				} else {
					callback({ lat: false, lon: false, address: "", message: "Location not found, please try again." });
				}
			});
		} else {
			callback({ lat: false, lon: false, address: "", message: "Could not connect to the location server, please specify the location manually." });
		}
	}	

	$('div.latlong').each(function() {
		var field = $(this).find('.middleColumn');
		var address = field.find('.latLongField_Address');
		var message = field.find('.latLongField_Message');
		var lat = field.find('input#Geotag-lat');
		var lon = field.find('input#Geotag-long');
		var search = field.find('.latLongField_Search');
		var map = field.find('.latLongField_Map');
		var googleMap = null;
		var googleMarker = null;

		function handleSearch() {
			geocode(address.val(), function(data) {
				if (data.lon) {
					message.html(data.address);
					lat.val(data.lat);
					lon.val(data.lon);
					
					if (googleMarker && googleMap) {
						var latlng = new google.maps.LatLng(data.lat, data.lon);
						googleMarker.setPosition(latlng);
						googleMap.setCenter(latlng);
					}
				}
			});
		}

		address.keypress(function(e) {
			if(e.keyCode == 13) {
				handleSearch();
			}
		});


		search.click(function() {
			handleSearch();
		});

		(function() {
			// Initialise the map
			var latlng = new google.maps.LatLng('-41.28648', '174.776217');

			var options = {
				zoom: 14,
				mapTypeControl: false,
				scrollwheel:false,
				center: latlng,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};

			googleMap = new google.maps.Map(map[0], options);

			googleMarker = new google.maps.Marker({
				position: latlng,
				map: googleMap,
				draggable: true
			});

			google.maps.event.addListener(googleMarker, 'dragend', function () {
				googleGeocoder.geocode({'latLng': googleMarker.getPosition()}, function (results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						if (results[0]) {
							address.val(results[0].formatted_address);
							message.html('');
							lat.val(googleMarker.getPosition().lat());
							lon.val(googleMarker.getPosition().lng());
						}
					}
				});
			});
		})();
	});

});
