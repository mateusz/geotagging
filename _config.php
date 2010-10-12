<?php

Object::add_extension('SiteTree', 'Geotagged');

// Loading external scripts will not work on-demand.
Requirements::javascript(Director::protocol().'maps.google.com/maps/api/js?sensor=false');
