<?php

class LatLongField extends FormField {
	protected $latField = null;
	protected $longField = null;

	function __construct($name, $title = null, $value = ""){
		$this->latField = new TextField($name . '[lat]', false);
		$this->longField = new TextField($name . '[long]', false);
		
		parent::__construct($name, $title, $value);
	}

	function Field() {
		Requirements::javascript('http://maps.google.com/maps/api/js?sensor=false');
		Requirements::javascript('geomod/javascript/LatLongField.js');
		Requirements::css('geomod/css/LatLongField.css');

		$field = $this->customise(array(
			'LatField' => $this->latField->FieldHolder(),
			'LongField' => $this->longField->FieldHolder()
		))->renderWith('LatLongField');

		return $field;
	}

}
