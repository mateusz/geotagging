(function($) {
	var googleGeocoder = null;
	if (google) {
		googleGeocoder = new google.maps.Geocoder();
	}

	// Go through all LatLongFields and install the javascript. 
	$('div.latLongField').entwine({
		onmatch: function() {
			var address = $(this).find('.latLongField_Address');
			var message = $(this).find('.latLongField_Message');
			var lat = $(this).find('.latLongField_LatLong input').first();
			var lon = $(this).find('.latLongField_LatLong input').last();
			var search = $(this).find('.latLongField_Search');
			var map = $(this).find('.latLongField_Map');
			var googleMap = null;
			var googleMarker = null;

			function handleSearch() {
				if (!google) {
					message.html('Geocoding server is not reachable. Please reload the page to try again.');
					return;
				}

				googleGeocoder.geocode( { 'address': address.val() }, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						message.html( results[0].formatted_address);
						lat.val(results[0].geometry.location.lat());
						lon.val(results[0].geometry.location.lng());
							
						if (googleMarker && googleMap) {
							var latLng = new google.maps.LatLng(lat.val(), lon.val());
							googleMarker.setPosition(latLng);
							googleMap.setCenter(latLng);
						}
					}
					else {
						message.html('Location not found, please try again.');
					}
				});
			}

			function handleReverseGeocoding(latLng) {
				googleGeocoder.geocode({'latLng': latLng}, function (results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						if (results[0]) {
							address.val(results[0].formatted_address);
							message.html('');
							lat.val(latLng.lat());
							lon.val(latLng.lng());
						}
					}
				});
			}

			address.live('keypress', function(e) {
				if(e.keyCode == 13) {
					handleSearch();
				}
			});


			search.live('click', function() {
				handleSearch();
			});

			$(this).find('.latLongField_LatLong input').live('change', function() {
				var latLng = new google.maps.LatLng(lat.val(), lon.val());
				handleReverseGeocoding(latLng);
				googleMarker.setPosition(latLng);
				googleMap.setCenter(latLng);
			});

			if (!google) {
				map.html('Map server is not reachable. Please reload the page to try again.');
				return;
			}

			// Initialise the map
			if (lat.val() && lon.val()) var latLng = new google.maps.LatLng(lat.val(), lon.val());
			else var latLng = new google.maps.LatLng('-41.28648', '174.776217');

			handleReverseGeocoding(latLng);

			var options = {
				zoom: 14,
				mapTypeControl: false,
				scrollwheel:false,
				center: latLng,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};

			googleMap = new google.maps.Map(map[0], options);

			googleMarker = new google.maps.Marker({
				position: latLng,
				map: googleMap,
				draggable: true
			});

			google.maps.event.addListener(googleMarker, 'dragend', function () {
				handleReverseGeocoding(googleMarker.getPosition());
			});

		}
	});
})(jQuery);
