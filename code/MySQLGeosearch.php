<?php
/**
 * Mimics MySQLDatabase's searchEngine, but for geosearch. 
 * Should be applied as a decorator, but the SS_Database does not extend Object, and does not support decorators.
 */

class MySQLGeosearch {
	/**
	 * @param $lat Latitude in degrees
	 * @param $long Longitude in degrees
	 * @param $window maximum degree distance from target to take into consideration
	 */
	static public function geosearchEngine($classesToSearch, $lat, $long, $window = 1.0, $start = 0, $pageLength = 10, $sortBy = "Distance ASC", $extraFilter = "", $booleanSearch = false, $alternativeFileFilter = "") {

		$fileFilter = '';	 	
		$extraFilters = array('SiteTree' => '1', 'File' => '1');
	 	
	 	if($extraFilter) {
	 		$extraFilters['SiteTree'] = " AND $extraFilter";
	 		
	 		if($alternativeFileFilter) $extraFilters['File'] = " AND $alternativeFileFilter";
	 		else $extraFilters['File'] = $extraFilters['SiteTree'];
	 	}
	 	
		// Always ensure that only pages with ShowInSearch = 1 can be searched
		$extraFilters['SiteTree'] .= " AND ShowInSearch <> 0";

		$limit = $start . ", " . (int) $pageLength;
		
		// Generate initial queries and base table names
		$baseClasses = array('SiteTree' => '', 'File' => '');
		foreach($classesToSearch as $class) {
			$queries[$class] = singleton($class)->extendedSQL($extraFilters[$class], "");
			$baseClasses[$class] = reset($queries[$class]->from);
		}
		
		// Make column selection lists
		$select = array(
			'SiteTree' => array("ClassName","$baseClasses[SiteTree].ID","ParentID","Title","MenuTitle","URLSegment","Content","LastEdited","Created","_utf8'' AS Filename", "_utf8'' AS Name", "CanViewType"),
			'File' => array("ClassName","$baseClasses[File].ID","_utf8'' AS ParentID","Title","_utf8'' AS MenuTitle","_utf8'' AS URLSegment","Content","LastEdited","Created","Filename","Name", "NULL AS CanViewType"),
		);

		// Add geolocation parameters
		$geowhere = "(\"Lat\" BETWEEN ".($lat-$window)." AND ".($lat+$window).") AND (\"Long\" BETWEEN ".($long-$window)." AND ".($long+$window).")";
		$geoselect = "IF(\"Lat\"<>0 AND \"Long\"<>0, 3956 * 2 * ASIN ( SQRT ( POWER(SIN((\"Lat\" - $lat)*pi()/180 / 2), 2) + 
				COS(\"Lat\" * pi()/180) * COS($lat * pi()/180) * POWER(SIN((\"Long\" - $long) * pi()/180 / 2), 2) ) ), NULL) AS \"Distance\"";

		
		// Process queries
		foreach($classesToSearch as $class) {
			// There's no need to do all that joining
			$queries[$class]->from = array(str_replace('`','',$baseClasses[$class]) => $baseClasses[$class]);
			$queries[$class]->select = $select[$class];
			$queries[$class]->orderby = null;
			$queries[$class]->select[] = $geoselect;
			$queries[$class]->where[] = $geowhere;
		}

		// Combine queries
		$querySQLs = array();
		$totalCount = 0;
		foreach($queries as $query) {
			$querySQLs[] = $query->sql();
			$totalCount += $query->unlimitedRowCount();
		}
		$fullQuery = implode(" UNION ", $querySQLs) . " ORDER BY $sortBy LIMIT $limit";
		
		// Get records
		$records = DB::query($fullQuery);

		foreach($records as $record)
			$objects[] = new $record['ClassName']($record);
		
		if(isset($objects)) $doSet = new DataObjectSet($objects);
		else $doSet = new DataObjectSet();
		
		$doSet->setPageLimits($start, $pageLength, $totalCount);
		return $doSet;
	}
}
