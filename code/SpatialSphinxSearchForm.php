<?php

class SpatialSphinxSearchForm extends SphinxSearchForm {
	public function getSphinxResult() {
		$data = $_REQUEST;

		$pageLength = $this->pageLength;
		$keywords = $data['Search'];
		$start = isset($data['start']) ? (int)$data['start'] : 0;
	
		// SetGeoAnchor: http://www.sphinxsearch.com/docs/manual-0.9.9.html#api-func-setgeoanchor
		// Geodist is in km
		if (isset($data['lat']) && isset($data['long'])) {
			$lat = deg2rad((double)$data['lat']);
			$long = deg2rad((double)$data['long']);
			$query = array("@weight+IF(Latitude<>0 AND Longitude<>0, GEODIST(Latitude, Longitude, $lat, $long), 0) AS DistanceWeight");
		}
		else {
			$query = array("@weight AS DistanceWeight");
		}

		$cachekey = $keywords.':'.$start;
		if (!isset($this->search_cache[$cachekey])) {
			$this->search_cache[$cachekey] = SphinxSearch::search($this->classesToSearch, $keywords, array_merge_recursive(array(
				'query'=>$query,
				'exclude' => array('_classid' => SphinxSearch::unsignedcrc('Folder')),
				'start' => $start,
				'pagesize' => $pageLength,
				'sortmode'=>'raw',
				'sortarg'=>'DistanceWeight DESC'
			), $this->args));
		}
		
		return $this->search_cache[$cachekey];
	}
}
