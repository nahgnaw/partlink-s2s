<?php

include_once("../opensearch/utils.php");
// parent class S2SConfig
include_once("../opensearch/config.php");

class PartLink_Procurements_S2SConfig extends S2SConfig {

	private $namespaces = array(
		'swiss'	=> "http://xsb.com/swiss#",
		'mat'	=> "http://xsb.com/swiss/material#",
		'log'	=> "http://xsb.com/swiss/logistics#",
		'prod'	=> "http://xsb.com/swiss/product#",
		'proc'  => "http//xsb.com/swiiss/process#",
		'type'	=> "http://xsb.com/swiss/types#",
		'foaf'	=> "http://xmlns.com/foaf/0.1/",
		'vcard'	=> "http://www.w3.org/2006/vcard/ns#",
		'rdfs'	=> "http://www.w3.org/2000/01/rdf-schema#",
		'time'	=> "http://www.w3.org/2006/time#",
		'xsd'	=> "http://www.w3.org/2001/XMLSchema#",
		'skos'	=> "http://www.w3.org/2004/02/skos/core#",
		'owl'	=> "http://www.w3.org/2002/07/owl#",
		'dct'	=> "http://purl.org/dc/terms/",
		'dc'	=> "http://purl.org/dc/elements/1.1/"
	);

	/**
	* Return SPARQL endpoint URL
	* @return string SPARQL endpoint URL
	*/
	public function getEndpoint() {
		return "http://api.xsb.com/sparql/query";
	}

	/**
	* Return array of prefix, namespace key-value pairs
	* @return array of prefix, namespace key-value pairs
	*/
	public function getNamespaces() {
		return $this->namespaces;
	}
	
	/**
	* Execute SPARQL select query
	* @param string $query SPARQL query to execute
	* @return array an array of associative arrays containing the bindings of the query results
	*/
	public function sparqlSelect($query) {
	
		$options = array(
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 120
		);
				
		$encoded_query = 'query=' . urlencode($query) . '&output=' . urlencode('application/xml');
		return execSelect($this->getEndpoint(), $encoded_query, $options);
	}

	private function getPartClassesByParent($parent) {
	
		//$query = $this->getPrefixes();
		$query = "SELECT DISTINCT ?id ?label ?parent WHERE { ";
		$query .= "?id rdfs:subClassOf+ <$parent> . ";
		$query .= "?id rdfs:label ?l . ";
		$query .= "?id rdfs:subClassOf ?parent . ";
		$query .= "FILTER (!isBlank(?parent)) . ";
		$query .= "BIND(str(?l) AS ?label) . } ";

		$cacheKey = md5($parent . '_SUBCLASSES');
		$result = apc_fetch($cacheKey);
		if ($result == null) {	
			$result = $this->sparqlSelect($query);
			apc_store($cacheKey, $result);
		}
		return $result;
	}

	private function getProcurementCountByConstraint($constraint, $query) {
		
		$cacheKey = md5($constraint . '_COUNT');
		$result = apc_fetch($cacheKey);
		if ($result == null) {	
			$result = $this->sparqlSelect($query);
			apc_store($cacheKey, $result);
		}
		return $result;
	}

	/**
	* Return count of total search results for specified constraints
	* @param array $constraints array of arrays with search constraints
	* @result int search result count
	*/
	public function getSearchResultCount(array $constraints) {
	
		$cacheKey = md5(serialize($constraints));
		$result = apc_fetch($cacheKey);
		if ($result == null) {	
			$query = $this->getSelectQuery("count", $constraints);
			$results = $this->sparqlSelect($query);
			$result = $results[0]['count'];
			apc_add($cacheKey, $result);
		}
		return $result;
	}

	public function getPartClassFacetOutput(array &$results) {
		
		header("Access-Control-Allow-Origin: *");
                header("Content-Type: application/json");

                if($this->setCacheControlHeader()) {
                	header($this->getFacetCacheControlHeader());
                }
		
		$new_results = array();
                foreach ($results as $i => $result) {
			$partCls = $result['id'];
			if ($partCls != "http://xsb.com/swiss/product#node00:UNCLASSIFIED_INCS") {
				$subClasses = $this->getPartClassesByParent($partCls);
				$new_results = array_merge($new_results, $subClasses); 
			}
                }
		$results = array_merge($results, $new_results);
		//$this->addSearchResultCountForFacet($results, "part_classes");

                return json_encode($results);
	}

	/**
	* Create HTML of search result
	* @param array $result query result to be processed into HTML
	* @return string HTML div of search result entry
	*/
	public function getSearchResultOutput(array $result) {

		$html = "<div class=\"result-list-item\" id=\"" . $result['procurement'] . "\">";
		$html .= "<div><span>Order URI</span>: " . $result['procurement'] . "</div>";
	
		// Purchase order number 
		$html .= "<div><span>Order number</span>: " . $result['order_number'] . "</div>";

		// Purchase order lines 
		$html .= "<div><div class=\"expander-head\"  style=\"cursor:pointer\"><span>Order lines (click to <span class=\"expander-action\">expand</span>)</span></div>";
		$html .= "<div class=\"expander-content\" style=\"display:none\"></div></div>";
	
		$html .= "</div>";
		return $html;
	}

	/**
	* Return SPARQL query header component
	* @param string $type search type (e.g. 'datasets', 'authors', 'keywords')
	* @return string query header component (e.g. 'SELECT ?id ?label')
	*/
	public function getQueryHeader($type) {
	
		$header = "";
		switch($type) {
			case "procurements":
				$header .= "?procurement ?order_number ";
				break;
			case "part_classes":
				$header .= "?id ?label ?parent ";
				break;
			case "award_dates":
			case "purchase_order_change_dates":
			case "data_in_db_dates":
				$header .= '?date';
				break;
			case "net_prices":
			case "order_quantities":
				$header .= "(MAX(?number) AS ?max) (MIN(?number) AS ?min) ";
				break;
			case "count":
				$header .= "(COUNT(DISTINCT ?procurement) AS ?count)";
				break;
			default:
				$header .= "?id ?label ";
				//$header .= "?id ?label (COUNT(DISTINCT ?procurement) AS ?count)";
				break;
		}
		return $header;
	}
	
	/**
	* Return SPARQL query footer component
	* @param string $type search type (e.g. 'datasets', 'authors', 'keywords')
	* @param int $limit size of result set
	* @param int $offset offset into result set
	* @param string $sort query result sort parameter
	* @return string query footer component (e.g. 'GROUP BY ?label ?id')
	*/
	public function getQueryFooter($type, $limit=null, $offset=0, $sort=null) {
	
		$footer = "";
		switch($type) {
			case "procurements":
				if ($type == 'procurements') { 
					if ($limit) $footer .= " LIMIT $limit OFFSET $offset";
				}
				break;
			/*
			case "count":
			case "net_prices":
			case "order_quantities":
			case "award_dates":
			case "purchase_order_change_dates":
			case "data_in_db_dates":
				break;
			*/
			default:
				break;
		}
		return $footer;
	}
	
	/**
	  * Return SPARQL query WHERE clause minus constraint clauses for specified search type
	  * @param string $type search type (e.g. 'datasets', 'authors', 'keywords')
	  * @return string WHERE clause component minus constraint clauses (e.g. '?dataset a dcat:Dataset . ')
	  */
	public function getQueryBody($type) {
		
		$body = "";
		switch($type) {
			case "net_prices":
				$body .= "?order_line log:hasNetPrice ?number . ";
				break;

			case "order_quantities":
				$body .= "?order_line log:hasOrderQuantity ?number . ";
				break;

			case "units_of_issue":
				$body .= "?order_line log:hasUnitOfIssue ?id . ";
				$body .= "BIND(str(?id) AS ?label) . ";
				break;

			case "part_classes":
				$body .= "?id rdfs:subClassOf prod:PART . ";
				$body .= "?id rdfs:label ?l . ";
				$body .= "?id rdfs:subClassOf ?parent . ";
				$body .= "FILTER (!isBlank(?parent)) . ";
				$body .= "BIND(str(?l) AS ?label) . ";
				break;

			case "award_dates":
				$body .= "?order_line log:hasAwardDate ?d . "; 
				$body .= "BIND(str(?d) AS ?date) . ";
				break; 

			case "purchase_order_change_dates":
				$body .= "?order_line log:hasPurchaseOrderChangeDate ?d . "; 
				$body .= "BIND(str(?d) AS ?date) . ";
				break; 
				
			case "data_in_db_dates":
				$body .= "?order_line log:hasDataInDbDate ?d . "; 
				$body .= "BIND(str(?d) AS ?date) . ";
				break; 
				
			case "count":
				$body .= "?procurement a log:PurchaseOrder . ";
				break;
				
			case "procurements":
				$body .= "?procurement a log:PurchaseOrder . ";
				$body .= "?procurement log:hasPurchaseOrderNumber ?order_number . ";
				break;
		}
				
		return $body;
	}

	/**
	* Return constraints component of SPARQL query
	* @param array $constraints array of arrays with search constraints
	* @return string constraints component of SPARQL query
	*/
	public function getQueryConstraints(array $constraints) {
		
		$body = "";		
		foreach($constraints as $constraint_type => $constraint_values) {
			if ($constraint_type == "startDate") {
				$body .= "?field_study vivo:dateTimeInterval [vivo:start [vivo:dateTime ?startDate]] . FILTER (?startDate >= \"" . $constraint_values . "T00:00:00\"^^xsd:dateTime) . ";
			}
			else if ($constraint_type == "endDate") {
				$body .= "?field_study vivo:dateTimeInterval [vivo:end [vivo:dateTime ?endDate]] . FILTER (?endDate <= \"" . $constraint_values . "T00:00:00\"^^xsd:dateTime) . ";
			}
			else {		
				$arr = array();	
				foreach($constraint_values as $i => $constraint_value) {
					$constraint_clause = $this->getQueryConstraint($constraint_type, $constraint_value);
					array_push($arr, $constraint_clause);
				}
				$body .= implode(' UNION ', $arr) . ' ';
			}
		}
		return $body;
	}
	
	/**
	* Return constraint clause to be included in SPARQL query
	* @param string $constraint_type constraint type (e.g. 'keywords')
	* @param string $constraint_value constraint value (e.g. 'Toxic')
	* @return string constraint clause to be included in SPARQL query
	*/	
	public function getQueryConstraint($constraint_type, $constraint_value) {
		
		$body = "";
		switch($constraint_type) {
			case "part_classes":
				$body .= "{ ?procurement log:hasPurchaseOrderLineNum ?order_line . ?order_line log:hasNIIN [log:hasProductNIIN [rdfs:subClassOf* <$constraint_value>]] . }";
				break;
			case "net_prices":
				$bounds = explode("~", $constraint_value);
				$lower_bound = trim($bounds[0]);
				$upper_bound = trim($bounds[1]);
				$body .= "{ ?procurement log:hasPurchaseOrderLineNum ?order_line . ?order_line log:hasNetPrice ?price . }";
				$body .= "FILTER (?price >= $lower_bound && ?price <= $upper_bound) . ";
				break;
			case "order_quantities":
				$bounds = explode("~", $constraint_value);
				$lower_bound = trim($bounds[0]);
				$upper_bound = trim($bounds[1]);
				$body .= "{ ?procurement log:hasPurchaseOrderLineNum ?order_line . ?order_line log:hasOrderQuantity ?quantity . }";
				$body .= "FILTER (?quantity >= $lower_bound && ?quantity <= $upper_bound) . ";
				break;
			case "units_of_issue":
				$body .= "{ ?procurement log:hasPurchaseOrderLineNum ?order_line . ?order_line log:hasUnitOfIssue \"$constraint_value\" }";
				break;
			default:
				break;
		}
		return $body;
	}
	
    /**
     * For each selection in a facet add a link to the context for that selection
     *
     * using the individual link for the different types as the context
     * for the selection
     *
     * @param array $results selections to add context to
	 * @param string $type search type (e.g. 'datasets', 'authors', 'keywords')
     */
	private function addContextLinks(&$results, $type) {
		
		if ($type == "communities" || $type == "groups" || $type == "participants") {
			foreach ( $results as $i => $result ) {
				$results[$i]['context'] = $result['id']; 
			}
		}
	}

	private function addSearchResultCountForFacet(&$results, $type) {

		switch($type) {
			case "units_of_issue":
				foreach ($results as $i => $result) {
					$unit = $result['label'];
					//$query = $this->getPrefixes();
					$query = "SELECT (COUNT(DISTINCT ?procurement) AS ?count) WHERE { ";
					$query .= "?procurement log:hasPurchaseOrderLineNum [log:hasUnitOfIssue \"$unit\"] . } ";
					$count = $this->getProcurementCountByConstraint($unit, $query);
					$results[$i]['count'] = $count[0]['count'];
				}
				break;
			case "part_classes":
				foreach ($results as $i => $result) {
					$partClass = $result['id'];
					//$query = $this->getPrefixes();
					$query = "SELECT (COUNT(DISTINCT ?procurement) AS ?count) WHERE { ";
					$query .= "?procurement log:hasPurchaseOrderLineNum [log:hasNIIN [log:hasProductNIIN [rdfs:subClassOf* <$partClass>]]] . } ";
					$count = $this->getProcurementCountByConstraint($partClass, $query);
					$results[$i]['count'] = $count[0]['count'];
				}
				break;
			default:
				break;
		}
	}
	
	/**
	* Return representation (HTML or JSON) of response to send to client
	* @param array $results array of associative arrays with bindings from query execution
	* @param string $type search type (e.g. 'datasets', 'authors', 'keywords')
	* @param array $constraints array of arrays with search constraints
	* @param int $limit size of result set
	* @param int $offset offset into result set
	* @return string representation of response to client
	*/
	public function getOutput(array $results, $type, array $constraints, $limit=0, $offset=0) {
		
		// Output for the request type "field_studies"			
		if ($type == "procurements") {
			$count = $this->getSearchResultCount($constraints);						
			return $this->getSearchResultsOutput($results, $limit, $offset, $count);
		}
		else if ($type == "part_classes") {
			return $this->getPartClassFacetOutput($results);
		}
		else {		
			//$this->addContextLinks($results, $type);
			//$this->addSearchResultCountForFacet($results, $type);
			return $this->getFacetOutput($results);
		}
	}
		
}
