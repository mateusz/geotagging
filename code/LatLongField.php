<?php
/**
 * Geomod
 * Form field for setting latitude and longitude on a DataObject. Composite field over two database fields.
 */

class LatLongField extends CompositeField {
	// Subfields
	protected $latField = null;
	protected $longField = null;

	/**
	 * We are a composite field, but pretend we are a normal field.
	 */
	function __construct($nameLat, $nameLong, $name) {
		$this->latField = new TextField($nameLat, false);
		$this->longField = new TextField($nameLong, false);
		$this->name = $name;

		parent::__construct(array($this->latField, $this->longField));
	}

	function getLatField() {
		return $this->latField;
	}
	
	function getLongField() {
		return $this->longField;
	}

	/**
	 * Render the field using custom template. It includes Google map and geocoding scripts.
	 */
	function FieldHolder() {
		Requirements::javascript('sapphire/thirdparty/jquery-livequery/jquery.livequery.js');
		Requirements::javascript('http://maps.google.com/maps/api/js?sensor=false');
		Requirements::javascript('geomod/javascript/LatLongField.js');
		Requirements::css('geomod/css/LatLongField.css');

		$field = $this->customise(array(
			'LatField' => $this->latField->Field(),
			'LongField' => $this->longField->Field(),
			'Name' => $this->name
		))->renderWith('LatLongField');

		return $field;
	}

}
