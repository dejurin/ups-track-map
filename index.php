<?php 

$tracking = trim($_REQUEST['tracking']);

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

<?php } else { 

$upssite = file_get_contents('http://iship.com/trackit/track.aspx?t=1&src=_e&Track=' . $tracking );

//$upssite = str_replace(array("\r", "\t"), array("", ""), $upssite);
//$upssite = str_replace("\n", "---", $upssite);

preg_match('/Scan History:\<br\/\>\<pre\>\<font face=\"\" size=\"2\" color=\"\#000000\"\>(.*)\<\/font\>\<\/pre\>\<\/font\>/s', $upssite, $matches);

$table = explode("\n", $matches[1]);

$listOfCities = array();
$lastCitySeen = '';

$statuses = array('DEPARTURE SCAN', 'ARRIVAL SCAN', 'OUT FOR DELIVERY', 'DELIVERED');

foreach ($table as $line) {
  // get rid of tabs and \r and stuff like that
  $line = trim($line);
  
  $city = "";
  
  foreach ($statuses as $s) {
    if (strpos($line, $s) !== false) {
      $line_arr = explode($s, $line);
      $city = trim($line_arr[1]);
    }
  }
  
  if ($city === "") continue;

  // strip out double whitespaces
  while ( strpos($city, '  ') !== false ) {
    $city = str_replace('  ', ' ', $city);
  }
  
  // always buffer the city because it's only listed once for a batch of lines
  $lastCitySeen = $city;

  // if this departure scan is from a different city than the last one, record it
  if ( count($listOfCities) == 0 || $lastCitySeen != $listOfCities[count($listOfCities)-1] ) {
    $listOfCities[] = $lastCitySeen;
  }
  
}

//header('Location: ' . $url);

?>

<p>Locations:</p>

<pre><?php echo implode("\r\n", $listOfCities); ?></pre>

<?php
//gmaps url
$url = 'https://maps.google.com/maps?saddr=' . urlencode(array_pop($listOfCities)) . '&daddr=' . urlencode( implode(' to:', array_reverse($listOfCities)) );
?>

<p><a href="http://wwwapps.ups.com/tracking/tracking.cgi?tracknum=<?php echo $tracking; ?>">Track on UPS.com</a></p> 

<p><a href="<?php echo $url; ?>">View on Google Maps</a></p>

<?php } ?>