(function($) {
	var googleGeocoder = null;
	if (google) {
		googleGeocoder = new google.maps.Geocoder();
	}

	// Go through all LatLongFields and install the javascript. 
	$('div.latLongField').livequery(function() {
		var address = $(this).find('.latLongField_Address');
		var message = $(this).find('.latLongField_Message');
		var lat = $(this).find('.latLongField_LatLong input').first();
		var lon = $(this).find('.latLongField_LatLong input').last();
		var search = $(this).find('.latLongField_Search');
		var map = $(this).find('.latLongField_Map');
		var googleMap = null;
		var googleMarker = null;

		// Geocode the location as entered in the search box - affects map, marker, info box and both value fields.
		function handleSearch() {
			if (!google) {
				message.html('Geocoding server is not reachable. Please reload the page to try again.').show();
				message.addClass('failure');
				return;
			}

			googleGeocoder.geocode( { 'address': address.val() }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					message.html('Found: '+results[0].formatted_address).show();
					message.removeClass('failure');
					lat.val(results[0].geometry.location.lat());
					lon.val(results[0].geometry.location.lng());
						
					if (googleMarker && googleMap) {
						var latLng = new google.maps.LatLng(lat.val(), lon.val());
						googleMarker.setPosition(latLng);
						googleMap.setCenter(latLng);
					}
				}
				else {
					message.html('Location not found, please try again.').show();
					message.addClass('failure');
					address.select();
				}
			});
		}

		// Figure out the location name for given coordinates - affects the search box and both value fields.
		function handleReverseGeocoding(latLng) {
			googleGeocoder.geocode({'latLng': latLng}, function (results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (results[0]) {
						address.val(results[0].formatted_address);
						message.html('').hide();
						lat.val(latLng.lat());
						lon.val(latLng.lng());
					}
				}
			});
		}



		// Initialise the google map and marker
		if (!google) {
			map.html('Map server is not reachable. Please reload the page to try again.');
			message.addClass('failure');
			return;
		}

		var defaultLatLng = new google.maps.LatLng('-41.28648', '174.776217');

		var options = {
			zoom: 14,
			mapTypeControl: false,
			scrollwheel:false,
			center: defaultLatLng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		googleMap = new google.maps.Map(map[0], options);

		googleMarker = new google.maps.Marker({
			position: defaultLatLng,
			map: googleMap,
			draggable: true
		});

		if (lat.val()!=0 || lon.val()!=0 ) {
			var latLng = new google.maps.LatLng(lat.val(), lon.val());

			googleMap.setCenter(latLng);
			googleMarker.setPosition(latLng);
			handleReverseGeocoding(latLng);
		}
		else {
			lat.val('');
			lon.val('');
		}

		// Event bindings follow

		// Marker move
		google.maps.event.addListener(googleMarker, 'dragend', function () {
			handleReverseGeocoding(googleMarker.getPosition());
		});

		// "ENTER" in the searchbox
		address.live('keypress', function(e) {
			if(e.keyCode == 13) {
				handleSearch();
			}
		});

		// "Search" click
		search.live('click', function() {
			handleSearch();
		});

		// Change in the coordinate boxes
		$(this).find('.latLongField_LatLong input').live('change', function() {
			var latLng = new google.maps.LatLng(lat.val(), lon.val());
			handleReverseGeocoding(latLng);

			// Quickly update the map in case the geocoding takes long time
			googleMarker.setPosition(latLng);
			googleMap.setCenter(latLng);
		});
	});
})(jQuery);
