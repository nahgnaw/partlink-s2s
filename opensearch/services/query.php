<?php

include_once("opensearch/utils.php");

$request = null;
$input = null;

if (@$_GET['request'] && @$_GET['request'] != '') {
	$request = $_GET['request'];
}

if (@$_GET['input'] && @$_GET['input'] != '') {
	$input = $_GET['input'];
}


$results = array();
switch($request) {
	case "order_lines":
		$results = json_encode(getOrderLinesByProcurement($input));
		break;
	case "cage_info":
		$results = json_encode(getCageInfo($input));
		break;
	case "niin_info":
		$results = json_encode(getNiinInfo($input));
		break;
	case "niin_hierarchy_info":
		$results = json_encode(getNiinHierarchyInfo($input));
		break;
	case "niin_product_info":
		$results = json_encode(getNiinProductInfo($input));
		break;
	case "niin_logistics_info":
		$results = json_encode(getNiinLogisticsInfo($input));
		break;
	case "niin_reference_number":
		$results = json_encode(getNiinReferenceNumber($input));
		break;
	case "product_property_value_info":
		$results = json_encode(getNiinProductPropertyValueInfo($input));
		break;
	case "part_class_info":
		$results = json_encode(getPartClassInfo($input));
		break;
	case "part_class_properties":
		$results = json_encode(getPartClassProperties($input));
		break;
	case "label":
		$results = json_encode(getLabel($input));
		break;
	default:
		break;
}
echo $results;

function getLabel($input) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?label ?comment ?description WHERE { ";
	$query .= "OPTIONAL {<$input> rdfs:label ?label . } ";
	$query .= "OPTIONAL {<$input> rdfs:comment ?comment . } ";
	$query .= "OPTIONAL {<$input> dcterms:description ?description . } ";
	$query .= "}";
	return sparqlSelect($query);
}

function getNiinInfo($input) {
	$results = array(
		'hierarchy' => getNiinHierarchyInfo($input),
		'logistics' => getNiinLogisticsInfo($input),
		'ref_num'   => getNiinReferenceNumber($input),
		'product'   => getNiinProductInfo($input)
	);
	return $results;
}

function getNiinProductInfo($input) {
	$query .= '';
	//$query .= getPrefixes();
	$query .= 'SELECT DISTINCT ?property ?property_label ?property_description (GROUP_CONCAT(DISTINCT ?v ; SEPARATOR=";") AS ?value) WHERE { ';
	$query .= "<$input> log:hasProductNIIN ?product . ";
	$query .= "?product rdfs:subClassOf ?res . ";
	$query .= "?res a owl:Restriction . ";
	$query .= "?res owl:onProperty ?property . ";
	$query .= "OPTIONAL {?property rdfs:label ?property_label . } "; 
	$query .= "OPTIONAL {?property dcterms:description ?property_description . }";
	$query .= "?res owl:hasValue ?v . ";
	$query .= "} GROUP BY ?property ?property_label ?property_description ";
	return sparqlSelect($query);
}

function getNiinProductPropertyValueInfo($input) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT * WHERE { ";
	$query .= "<$input> ?property ?value . ";
	$query .= "FILTER (?property != rdf:type) . ";
	$query .= "OPTIONAL {?property rdfs:label ?property_label . } ";
	$query .= "OPTIONAL {?value rdfs:label ?value_label . } ";
	$query .= "}";
	return sparqlSelect($query);
}

function getNiinLogisticsInfo($input) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?property ?property_label ?property_comment ?value ?value_label ?value_code ?value_definition WHERE { ";
	$query .= "<$input> ?property ?value . ";
	$query .= "FILTER (?property != log:hasReferenceNumber && ?property != rdfs:label && ?property != rdf:type && ?property != log:hasProductNIIN) . ";
	$query .= "OPTIONAL {?property rdfs:label ?property_label . } ";
	$query .= "OPTIONAL {?property rdfs:comment ?property_comment . } ";
	$query .= "OPTIONAL {?value rdfs:label ?value_label . } ";
	$query .= "OPTIONAL {?value log:hasCode ?value_code . } ";
	$query .= "OPTIONAL {?value log:hasDefinition ?value_definition . } ";
	$query .= "}";
	return sparqlSelect($query);
}

function getNiinReferenceNumber($input) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?ref_num ?cage ?cage_name ?part_number ?rnccrnvc ?rnccrnvc_comment WHERE { ";
	$query .= "<$input> log:hasReferenceNumber ?ref_num . ";
	$query .= "?ref_num log:hasCage ?cage; log:hasPartNumber ?part_number; log:hasRNCCRNVC [log:hasDefinition ?rnccrnvc]. ";
	$query .= "?cage log:hasCageName ?cage_name . ";
	$query .= "log:hasRNCCRNVC rdfs:comment ?rnccrnvc_comment . ";
	$query .= "}";
	return sparqlSelect($query);
}

function getNiinHierarchyInfo($input) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?parent WHERE { ";
	$query .= "<$input> rdfs:subClassOf+ ?parent . ";
	$query .= "FILTER (!isBlank(?parent) && ?parent != owl:Thing) . }";
	return sparqlSelect($query);
}

function getCageInfo($input) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?code ?name ?status ?business_type_code ?business_size_code ?primary_business_code ?cao ?adp ?woman_owned ?duns ?associated_cage ?street_address ?locality ?region ?country ?postal_code WHERE { ";
	$query .= "<$input> log:hasCageCode ?code . ";
	$query .= "<$input> log:hasCageName ?name . ";
	$query .= "OPTIONAL { <$input> log:hasCageStatus ?status } . ";
	$query .= "OPTIONAL { <$input> log:hasBusinessTypeCode ?business_type_code } . ";
	$query .= "OPTIONAL { <$input> log:hasBusinessSizeCode ?business_size_code } . ";
	$query .= "OPTIONAL { <$input> log:hasPrimaryBusinessCode ?primary_business_code } . ";
	$query .= "OPTIONAL { <$input> log:hasCAO ?cao } . ";
	$query .= "OPTIONAL { <$input> log:hasADP ?adp } . ";
	$query .= "OPTIONAL { <$input> log:isWomanOwned ?woman_owned } . ";
	$query .= "OPTIONAL { <$input> log:hasDUNSNum ?duns } . ";
	$query .= "OPTIONAL { <$input> log:hasAssociatedCage [log:hasCageName ?associated_cage] } . ";
	$query .= "<$input> vcard:hasAddress ?addr . ";
	$query .= "OPTIONAL { ?addr vcard:street-address ?street_address } . "; 
	$query .= "OPTIONAL { ?addr vcard:locality ?locality } . "; 
	$query .= "OPTIONAL { ?addr vcard:region ?region } . "; 
	$query .= "OPTIONAL { ?addr vcard:country-name ?country } . "; 
	$query .= "OPTIONAL { ?addr vcard:postal-code ?postal_code } . "; 
	$query .= "}";
	return sparqlSelect($query);	
}

function getOrderLinesByProcurement($input) {
	$query = '';
        //$query .= getPrefixes();
        $query .= "SELECT DISTINCT ?order_line ?line_number ?niin ?contract_number ?net_price ?order_quantity ?unit_of_issue ?award_date ?purchase_order_change_date ?data_in_db_date ?cage WHERE { ";
        $query .= "<$input> log:hasPurchaseOrderLineNum ?order_line . ";
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


function getPartClassInfo($input) {
	$query .= '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?label ?native_id ?inc ?description "; 
	$query .= "WHERE { ";
	$query .= "<$input> rdfs:label ?label . ";
	$query .= "OPTIONAL { <$input> <http://xsb.com/swiss#Native_ID> ?native_id . } "; 
	$query .= "OPTIONAL { <$input> prod:itemNameCode ?inc . } "; 
	$query .= "OPTIONAL { <$input> dcterms:description ?description . } "; 
	$query .= "}";
	return sparqlSelect($query);
}

function getPartClassProperties($input) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?property ?property_label ?property_description ?value ?value_label WHERE { ";
	$query .= "<$input> rdfs:subClassOf ?res . ?res a owl:Restriction; owl:onProperty ?property; owl:allValuesFrom ?value . ";
	$query .= "OPTIONAL {?property rdfs:label ?property_label . } ";
	$query .= "OPTIONAL {?property dcterms:description ?property_description . } ";
	$query .= "OPTIONAL {?value rdfs:label ?value_label . } ";
	$query .= "}";
	return sparqlSelect($query);
}

function sparqlSelect($query) {
	//echo $query;
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
