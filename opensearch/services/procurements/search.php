<?php

include_once("procurements.php");

// type of requested results as defined in the opensearch document. The
// type will be the name of one of the facets, or the name of the result
// set
$type = null;
// number of results to be displayed in the result set
$limit = 10;
// offset of the current result set, used by the next and previous
// buttons
$offset = 0;
// what to use to sort items in the result set, currently not enabled
//$sort = null;

// array for input constraints
$constraints = array(); // array for input constraints

if (@$_GET['contract_numbers'] && @$_GET['contract_numbers'] != '') {
    $constraints['contract_numbers'] = explode(";",$_GET['contract_numbers']);
}

if (@$_GET['part_classes'] && @$_GET['part_classes'] != '') {
    $constraints['part_classes'] = explode(";",$_GET['part_classes']);
}

if (@$_GET['net_prices'] && @$_GET['net_prices'] != '') {
    $constraints['net_prices'] = explode(";",$_GET['net_prices']);
}

if (@$_GET['order_quantities'] && @$_GET['order_quantities'] != '') {
    $constraints['order_quantities'] = explode(";",$_GET['order_quantities']);
}

if (@$_GET['units_of_issue'] && @$_GET['units_of_issue'] != '') {
    $constraints['units_of_issue'] = explode(";",$_GET['units_of_issue']);
}

if (@$_GET['award_dates'] && @$_GET['award_dates'] != '') {
    $constraints['award_dates'] = $_GET['award_dates'];
}

if (@$_GET['purchase_order_change_dates'] && @$_GET['purchase_order_change_dates'] != '') {
    $constraints['purchase_order_change_dates'] = $_GET['purchase_order_change_dates'];
}

if (@$_GET['data_in_db_dates'] && @$_GET['data_in_db_dates'] != '') {
    $constraints['data_in_db_dates'] = $_GET['data_in_db_dates'];
}

if (@$_GET['limit'] && @$_GET['limit'] != '') {
    $limit = $_GET['limit'];
}

if (@$_GET['offset'] && @$_GET['offset'] != '') {
    $offset = $_GET['offset'];
}


// Get the result type from the request url
if (@$_GET['request'] && @$_GET['request'] != '') {
    $type = $_GET['request'];
}

// instantiate the Config class for the procurement browser (class
// definition in "procurements.php")
$s2s = new PartLink_Procurements_S2SConfig();

// get the response for the request given the type of request, the
// constraints list to constrain the result, the number of results to
// pull back, the offset into the result set, and what to sort the
// results by. For/ a facet the response will be a json object. For
// the result set the response will be an HTML document
$out = $s2s->getResponse(@$type, @$constraints, @$limit, @$offset, @$sort);

// for sending the response we want to know the number of characters in
// the result.
$size = strlen($out);

// set the size of the response in the response header
header("Content-length: $size");

// echo the response
echo $out;
