<?php

include_once("opensearch/utils.php");

$request = null;
$procurement = null;
$cage = null;

if (@$_GET['request'] && @$_GET['request'] != '') {
	$request = $_GET['request'];
}

if (@$_GET['procurement'] && @$_GET['procurement'] != '') {
	$procurement = $_GET['procurement'];
}

if (@$_GET['cage'] && @$_GET['cage'] != '') {
	$cage = $_GET['cage'];
}

$results = array();
switch($request) {
	case "order_lines":
		$results = json_encode(getOrderLinesByProcurement($procurement));
		break;
	case "cage_info":
		$results = json_encode(getCageInfo($cage));
		break;
	default:
		break;
}
echo $results;

function getCageInfo($cage) {
	$query = getPrefixes();
	$query .= "SELECT DISTINCT ?code ?name ?cao ?adp ?woman_owned ?duns ?associated_cage ?street_address ?locality ?region ?country ?postal_code WHERE { ";
	$query .= "<$cage> log:hasCageCode ?code . ";
	$query .= "<$cage> log:hasCageName ?name . ";
	$query .= "OPTIONAL { <$cage> log:hasCAO ?cao } . ";
	$query .= "OPTIONAL { <$cage> log:hasADP ?adp } . ";
	$query .= "OPTIONAL { <$cage> log:isWomanOwned ?woman_owned } . ";
	$query .= "OPTIONAL { <$cage> log:hasDUNSNum ?duns } . ";
	$query .= "OPTIONAL { <$cage> log:hasAssociatedCage [log:hasCageName ?associated_cage] } . ";
	$query .= "OPTIONAL { <$cage> vcard:hasAddress ?addr }. ";
	$query .= "OPTIONAL { ?addr vcard:street-address ?street_address } . "; 
	$query .= "OPTIONAL { ?addr vcard:locality ?locality } . "; 
	$query .= "OPTIONAL { ?addr vcard:region ?region } . "; 
	$query .= "OPTIONAL { ?addr vcard:country-name ?country } . "; 
	$query .= "OPTIONAL { ?addr vcard:postal-code ?postal_code } . }";
	return sparqlSelect($query);	
}

function getOrderLinesByProcurement($procurement) {
        $query = getPrefixes();
        $query .= "SELECT DISTINCT ?order_line ?line_number ?niin ?contract_number ?net_price ?order_quantity ?unit_of_issue ?award_date ?purchase_order_change_date ?data_in_db_date ?cage WHERE { ";
        $query .= "<$procurement> log:hasPurchaseOrderLineNum ?order_line . ";
        $query .= "?order_line log:hasLineNumber ?line_number . ";
        $query .= "OPTIONAL { ?order_line log:hasNIIN ?niin } . ";
        $query .= "OPTIONAL { ?order_line log:hasContractNumber ?contract_number } . ";
        $query .= "OPTIONAL { ?order_line log:hasNetPrice ?net_price } . ";
        $query .= "OPTIONAL { ?order_line log:hasOrderQuantity ?order_quantity } . ";
        $query .= "OPTIONAL { ?order_line log:hasUnitOfIssue ?unit_of_issue } . ";
        $query .= "OPTIONAL { ?order_line log:hasAwardDate ?award_date } . ";
        $query .= "OPTIONAL { ?order_line log:hasPurchaseOrderChangeDate ?purchase_order_change_date } . ";
        $query .= "OPTIONAL { ?order_line log:hasDataInDbDate ?data_in_db_date } . ";
        $query .= "OPTIONAL { ?order_line log:hasCage ?cage . } . } ORDER BY ?line_number";
        return sparqlSelect($query);
}


function sparqlSelect($query) {
	$endpoint = "http://api.xsb.com/sparql/query";
	$options = array(
		CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 120
         );
         $encoded_query = 'query=' . urlencode($query) . '&output=' . urlencode('application/xml');
         return execSelect($endpoint, $encoded_query, $options);
}

function getPrefixes() {
	$namespaces = array(
		'swiss' => "http://xsb.com/swiss#",
		'mat'   => "http://xsb.com/swiss/material#",
		'log'   => "http://xsb.com/swiss/logistics#",
		'prod'  => "http://xsb.com/swiss/product#",
		'proc'  => "http//xsb.com/swiiss/process#",
		'type'  => "http://xsb.com/swiss/types#",
		'foaf'  => "http://xmlns.com/foaf/0.1/",
		'vcard' => "http://www.w3.org/2006/vcard/ns#",
		'rdfs'  => "http://www.w3.org/2000/01/rdf-schema#",
		'time'  => "http://www.w3.org/2006/time#",
		'xsd'   => "http://www.w3.org/2001/XMLSchema#",
		'skos'  => "http://www.w3.org/2004/02/skos/core#",
		'owl'   => "http://www.w3.org/2002/07/owl#",
		'dct'   => "http://purl.org/dc/terms/",
		'dc'    => "http://purl.org/dc/elements/1.1/"
	);
	$output = "";
        foreach ($namespaces as $prefix => $uri) {
        	$output .= "PREFIX $prefix: <$uri> ";
        }
        return $output;
}
