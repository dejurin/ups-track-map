<?php 

$tracking = trim($_REQUEST['tracking']);
$upssite = urldecode($_REQUEST['sitecontent']);

if (! ctype_alnum($tracking) ) { 

?>

<p>No alphanumeric tracking no provided.</p>

<form action="" method="GET">
	<label for="tracking">Tracking No</label>
	<input type="text" value="" name="tracking" id="tracking" />
	
	<br>
	
	<input type="submit" value="map it!">
</form>

<hr>

<!-- <a href="javascript:window.location = 'http://www.jonemo.de/stuff/upsmaptrack/?tracking=' + $('#trkNum input').val() + '&sitecontent=' + escape(document.documentElement.innerHTML);">UPS Map Track Bookmarklet</a> -->

<?php } else { 

if (! $upssite ) {
	$upssite = file_get_contents('http://wwwapps.ups.com/tracking/tracking.cgi?tracknum=' . $tracking );
}
$upssite = str_replace(array("\r", "\t", "\n"), array(" ", " ", " "), $upssite);

preg_match('/\<\!\-\- START: Standard 1Z Tracking Package Progress Box \-\-\>(.*)\<\!\-\- END: Standard 1Z Tracking Package Progress Box \-\-\>/', $upssite, $matches);
$x = new SimpleXMLElement($matches[1]);
$table = $x->fieldset->div[1]->table;

$listOfCities = array();
$lastCitySeen = '';

foreach ($table->tr as $line) {
		
	if (! $line->td ) { continue; };
	if ( count($line->td) != 4 )  { continue; };
	
	// always buffer the city because it's only listed once for a batch of lines
	$city = trim($line->td[0]);
	while ( strpos($city, '  ') !== false ) {
		$city = str_replace('  ', ' ', $city);
	}
	if ( $city != '' ) $lastCitySeen = $city;
	
	if ( trim($line->td[3]) != 'Departure Scan' && trim($line->td[3]) != 'Arrival Scan') { continue; };
	
	// if this departure scan is from a different city than the last one, record it
	if ( count($listOfCities) == 0 || $lastCitySeen != $listOfCities[count($listOfCities)-1] ) {
		$listOfCities[] = $lastCitySeen;
	}
	
}

// var_dump($listOfCities);

$url = 'https://maps.google.com/maps?saddr=' . urlencode(array_pop($listOfCities)) . '&daddr=' . urlencode( implode(' to:', array_reverse($listOfCities)) );

header('Location: ' . $url);

?>

<?php } ?>