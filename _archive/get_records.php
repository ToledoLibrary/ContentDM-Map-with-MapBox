<?php
error_reporting(E_ALL); ini_set('display_errors','1');
// collection alias
$COLLALIAS = "p16007coll33";

// connection info
$CDM_WEB_SERVICES = "https://server16007.contentdm.oclc.org";

// open file for json
$myFile = "records_".$COLLALIAS.".txt";
//echo $myFile;
$fh = fopen($myFile, 'w') or die("can't open file");

// max for CONTENTdm result set
$CHUNK = 10000;
// default = 1
$START = "1";
// search string: field^terms^mode^operator
// 0 in place of search string = "get all records"
$SEARCH_STRING = "CISOSEARCHALL^all^and";
// fields to return
$RETURN_FIELDS = "time";
// sort fields 
$SORT_FIELDS = "title";

// get total alias|searchstring|return fields|sort
// e.g https://server16007.contentdm.oclc.org/dmwebservices/index.php?q=dmQueryTotalRecs/p16007coll58|0/xml
$dmQueryTotalRecs_criteria = $COLLALIAS . "|0";

// e.g https://server16007.contentdm.oclc.org/dmwebservices/index.php?q=dmQuery/p16007coll44/0/dmrecord!search/dmrecord/1024/1/1/0/0/0/json
$dmQuery_criteria =  $COLLALIAS . "/" . $SEARCH_STRING . "/" . $RETURN_FIELDS . "/" . $SORT_FIELDS;

$curl_url = "dmQueryTotalRecs/" . $dmQueryTotalRecs_criteria . "/xml"; // total number of records
$total_array = doCurl($curl_url);
$total = $total_array['totalrecs']['total'];

$results_arrays = array();
// iterator is a $CHUNK of a collection's $total, where 10000 is the CDM max for $CHUNK, but can be less if desired.
for ($i = $START; $i < $total; $i = $i + $CHUNK) {
	$dmQuery_url = "dmQuery/" . $dmQuery_criteria . "/" . $CHUNK . "/" . $i . "/1/0/0/0/xml";
	echo $dmQuery_url; 
	//dmQuery/p16007coll33/0/time/0/10000/1/1/0/0/0/json
	//dmQuery/p16007coll33/CISOSEARCHALL^all^and/time/title/10000/1/1/0/0/0/xml
	//dmQuery/p16007coll33/CISOSEARCHALL^all^and/time/title/1000/1/1/0/0/0/xml
	$results_array = doCurl($dmQuery_url);
	array_push($results_arrays, $results_array);
}

// if more than 10000, there will be more than one array
if (count($results_arrays) > 1) {
	$merged_arrays = array();
	foreach ($results_arrays as $ra) {
		array_merge($merged_arrays, $ra);
	}
	// encode and save
	fwrite( $fh, json_encode($merged_arrays) );
// only one array
} else {
	// encode and save
	fwrite( $fh, json_encode($results_arrays[0]) );
}

fclose($fh);

function doCurl($curl_url) {
	
	// https://server16007.contentdm.oclc.org/dmwebservices/index.php?q=dmQuery/p15005coll5/title^Dannon^all^and/title/dmrecord/1024/1/0/0/0/0/json
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://server16007.contentdm.oclc.org/dmwebservices/index.php?q=" . $curl_url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
	curl_setopt($ch, CURLOPT_TIMEOUT, 90); // same as php.ini max_execution_time
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	if (!strpos($curl_url, 'xml')) {
    	$cdm_data_json = curl_exec($ch);
	} else {
		$cdm_data_xml = curl_exec($ch);
		$xml = simplexml_load_string($cdm_data_xml);
		$cdm_data_json = json_encode($xml);
	}
	curl_close($ch);

	$record_array = json_decode($cdm_data_json, true);
	
	return $record_array;
	
}

?>