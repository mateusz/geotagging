<?php

class Geotagged extends DataObjectDecorator {
	function extraStatics() {
		return array(
			'db'=>array(
				'Lat' => 'Decimal(8,6)',
				'Long' => 'Decimal(9,6)'
			)
		);
	}

	function updateCMSFields(&$fields) {
		$fields->addFieldToTab('Root.Content.Geotag', new LatLongField('Lat', 'Long', 'Geotag'));
	}

	/**
	 * Get DataObjects within a degree range, calculating geo-distance. Slow and tested only on MySQL
	 * 
	 * @param $class Class to get
	 * @param $lat Latitude in degrees
	 * @param $long Longitude in degrees
	 * @param $window maximum degree distance from target to take into consideration
	 * 
	 * @returns DataObjectSet Augmented with "Distance" field denominated in km [float]
	 */
	static function get_with_distance($class, $lat, $long, $window = 1.0, $filter = "", $sort = "", $limit = "", $join = "", $having = "") {
		$where = "(\"Lat\" BETWEEN ".($lat-$window)." AND ".($lat+$window).") AND (\"Long\" BETWEEN ".($long-$window)." AND ".($long+$window).")";

		$query = singleton($class)->extendedSQL($filter, $sort, $limit, $join, $having);
		$query->where[] = $where;
		$query->select[] = "IF(\"Lat\"<>0 AND \"Long\"<>0, 3956 * 2 * ASIN ( SQRT ( POWER(SIN((\"Lat\" - $lat)*pi()/180 / 2), 2) + 
				COS(\"Lat\" * pi()/180) * COS($lat * pi()/180) * POWER(SIN((\"Long\" - $long) * pi()/180 / 2), 2) ) ), NULL) AS \"Distance\"";

		$ret = singleton($class)->buildDataObjectSet($query->execute(), 'DataObjectSet', $query, $class);
		if($ret) $ret->parseQueryLimit($query);

		return $ret;
	}
}
