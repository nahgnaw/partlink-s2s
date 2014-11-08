<?php

include_once("opensearch/utils.php");

$request = null;
$procurement = null;
$cage = null;
$niin = null;
$uri = null;

if (@$_GET['request'] && @$_GET['request'] != '') {
	$request = $_GET['request'];
}

if (@$_GET['procurement'] && @$_GET['procurement'] != '') {
	$procurement = $_GET['procurement'];
}

if (@$_GET['cage'] && @$_GET['cage'] != '') {
	$cage = $_GET['cage'];
}

if (@$_GET['niin'] && @$_GET['niin'] != '') {
	$niin = $_GET['niin'];
}

if (@$_GET['uri'] && @$_GET['uri'] != '') {
	$uri = $_GET['uri'];
}

$results = array();
switch($request) {
	case "order_lines":
		$results = json_encode(getOrderLinesByProcurement($procurement));
		break;
	case "cage_info":
		$results = json_encode(getCageInfo($cage));
		break;
	case "niin_hierarchy":
		$results = json_encode(getNiinHierarchy($niin));
		break;
	case "niin_info":
		$results = json_encode(getNiinInfo($niin));
		break;
	case "product_info":
		$results = json_encode(getProductInfoByNiin($niin));
		break;
	case "label":
		$results = json_encode(getLabel($uri));
		break;
	default:
		break;
}
echo $results;

function getLabel($uri) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?label WHERE { <$uri> rdfs:label ?label }";
	return sparqlSelect($query);
}

function getProductInfoByNiin($niin) {
	$query .= '';
	//$query .= getPrefixes();
	$query .= 'SELECT DISTINCT ?property_label ?property_description (GROUP_CONCAT(DISTINCT ?v ; SEPARATOR=";") AS ?value) WHERE { ';
	$query .= "<$niin> log:hasProductNIIN ?product . ";
	$query .= "?product rdfs:subClassOf ?res . ";
	$query .= "?res a owl:Restriction . ";
	$query .= "?res owl:onProperty [rdfs:label ?property_label; dcterms:description ?property_description] . ";
	$query .= "?res owl:hasValue ?v . ";
	$query .= "} GROUP BY ?property_label ?property_description ";
	return sparqlSelect($query);
}

function getNiinInfo($niin) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT ?fsc ?inc ?tiic ?pinc ?status_code ?status_code_def ?acquisition_advice_code ?acquisition_advice_code_def ?acquisition_method_code ?acquisition_method_code_def ?acquisition_method_suffix_code ?acquisition_method_suffix_code_def ?criticality_code ?criticality_code_def ?demilitarization_code ?demilitarization_code_def ?hazardous_material_indicator_code ?hazardous_material_indicator_code_def ?item_standardization_code ?item_standardization_code_def ?precious_metal_indicator_code ?precious_metal_indicator_code_def ?administrative_lead_time ?production_lead_time ?assignment_date ?last_demand_date ?unit_price WHERE { ";
	$query .= "<$niin> rdfs:label ?label . ";
	$query .= "OPTIONAL { <$niin> log:hasFSC [rdfs:label ?fsc] . } ";
	$query .= "OPTIONAL { <$niin> log:hasINC [rdfs:label ?inc] . } ";
	$query .= "OPTIONAL { <$niin> log:hasTiic ?tiic . } ";
	$query .= "OPTIONAL { <$niin> log:hasPinc ?pinc . } ";
	$query .= "OPTIONAL { <$niin> log:hasStatusCode [log:hasCode ?status_code; log:hasDefinition ?status_code_def] . } ";
	$query .= "OPTIONAL { <$niin> log:hasAcquisitionAdviceCode [log:hasCode ?acquisition_advice_code; log:hasDefinition ?acquisition_advice_code_def] . } ";
	$query .= "OPTIONAL { <$niin> log:hasAcquisitionMethodCode [log:hasCode ?acquisition_method_code; log:hasDefinition ?acquisition_method_code_def] . } ";
	$query .= "OPTIONAL { <$niin> log:hasAcquisitionMethodSuffixCode [log:hasCode ?acquisition_method_suffix_code; log:hasDefinition ?acquisition_method_suffix_code_def] . } ";
	$query .= "OPTIONAL { <$niin> log:hasCriticalityCode [log:hasCode ?criticality_code; log:hasDefinition ?criticality_code_def] . } ";
	$query .= "OPTIONAL { <$niin> log:hasDemilitarizationCode [log:hasCode ?demilitarization_code; log:hasDefinition ?demilitarization_code_def] . } ";
	$query .= "OPTIONAL { <$niin> log:hasHazardousMaterialIndicatorCode [log:hasCode ?hazardous_material_indicator_code; log:hasDefinition ?hazardous_material_indicator_code_def] . } ";
	$query .= "OPTIONAL { <$niin> log:hasItemStandardizationCode [log:hasCode ?item_standardization_code; log:hasDefinition ?item_standardization_code_def] . } ";
	$query .= "OPTIONAL { <$niin> log:hasPreciousMetalIndicatorCode [log:hasCode ?precious_metal_indicator_code; log:hasDefinition ?precious_metal_indicator_code_def] . } ";
	$query .= "OPTIONAL { <$niin> log:hasAdministrativeLeadTime ?administrative_lead_time . } ";
	$query .= "OPTIONAL { <$niin> log:hasProductionLeadTime ?production_lead_time . } ";
	$query .= "OPTIONAL { <$niin> log:assignmentDate ?assignment_date . } ";
	$query .= "OPTIONAL { <$niin> log:hasLastDemandDate ?last_demand_date . } ";
	$query .= "OPTIONAL { <$niin> log:hasProfitCenter ?profit_center . } ";
	$query .= "OPTIONAL { <$niin> log:hasSupplyChain ?supply_chain . } ";
	$query .= "OPTIONAL { <$niin> log:hasUnitPrice ?unit_price . } ";
	$query .= "}";
	return sparqlSelect($query);
}

function getNiinHierarchy($niin) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?parent WHERE { ";
	$query .= "<$niin> rdfs:subClassOf+ ?parent . ";
	$query .= "FILTER (!isBlank(?parent) && ?parent != owl:Thing) . }";
	return sparqlSelect($query);
}

function getCageInfo($cage) {
	$query = '';
	//$query .= getPrefixes();
	$query .= "SELECT DISTINCT ?code ?name ?status ?cao ?adp ?woman_owned ?duns ?associated_cage ?street_address ?locality ?region ?country ?postal_code WHERE { ";
	$query .= "<$cage> log:hasCageCode ?code . ";
	$query .= "<$cage> log:hasCageName ?name . ";
	$query .= "OPTIONAL { <$cage> log:hasCageStatus ?status } . ";
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
	$query = '';
        //$query .= getPrefixes();
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
