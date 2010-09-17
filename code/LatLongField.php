<?php

class LatLongField extends CompositeField {
	protected $latField = null;
	protected $longField = null;

	function __construct($nameLat, $nameLong){
		$this->latField = new TextField($nameLat, false);
		$this->longField = new TextField($nameLong, false);

		parent::__construct(array($this->latField, $this->longField));
	}

	function getLatField() {
		return $this->latField;
	}
	
	function getLongField() {
		return $this->longField;
	}

	function FieldHolder() {
		Requirements::javascript('geomod/javascript/jquery.entwine-dist.js');
		Requirements::javascript('http://maps.google.com/maps/api/js?sensor=false');
		Requirements::javascript('geomod/javascript/LatLongField.js');
		Requirements::css('geomod/css/LatLongField.css');

		$field = $this->customise(array(
			'LatField' => $this->latField->Field(),
			'LongField' => $this->longField->Field()
		))->renderWith('LatLongField');

		return $field;
	}

}
