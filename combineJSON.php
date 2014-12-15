<?php
/* Kombinerar skjutsarna från liftsJSON1, liftsJSON2, liftsJSON3 i en och samma JSON-fil. Servern kan inte hantera för många anrop i samma fil, därav uppdelningen i tre. Sannolikt behöver detta utökas till ännu fler, skulle jag tro. 
	
Den här filen ska köras som nummer fyra, efter buildJSON1, buildJSON2 och buildJSON3. Sist ska facebookboten köras.
*/

header('Content-type: text/html; charset=utf-8');

$url = 'http://www.fjellandermedia.se/skjutsgruppen/liftsJSON1.php';
//echo $url;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$json = '';
$json = curl_exec($ch);

//echo curl_errno($ch);

$json = substr($json, 1);
$json = substr($json, 0, -1);

$lifts1 = json_decode($json, true);

$url = 'http://www.fjellandermedia.se/skjutsgruppen/liftsJSON2.php';
//echo $url;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$json = '';
$json = curl_exec($ch);

//echo curl_errno($ch);

$json = substr($json, 1);
$json = substr($json, 0, -1);

$lifts2 = json_decode($json, true);

$url = 'http://www.fjellandermedia.se/skjutsgruppen/liftsJSON3.php';
//echo $url;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$json = '';
$json = curl_exec($ch);

//echo curl_errno($ch);

$json = substr($json, 1);
$json = substr($json, 0, -1);

$lifts3 = json_decode($json, true);

$combinedLifts = array('offeredJourneys' => '', 'wantedJourneys' => '', 'lastBuild' => date("U"));

$combinedLifts['offeredJourneys'] = array_merge($lifts1['offeredJourneys'], $lifts2['offeredJourneys'], $lifts3['offeredJourneys']);
$combinedLifts['wantedJourneys'] = array_merge($lifts1['wantedJourneys'], $lifts2['wantedJourneys'], $lifts3['wantedJourneys']);

foreach ($combinedLifts['offeredJourneys'] as $key => $row) {
   $date[$key]  = $row['date'];
   $from[$key] = $row['from'];
   $to[$key] = $row['to'];
   $dateNice[$key] = $row['dateNice'];
	$liftUrl[$key] = $row['url'];
	$seats[$key] = $row['seats'];
	$who[$key] = $row['who'];
}

// Sort the data with volume descending, edition ascending
// Add $data as the last parameter, to sort by the common key
array_multisort($date, SORT_ASC, $from, SORT_ASC, $to, SORT_ASC, $liftUrl, SORT_ASC, $dateNice, SORT_ASC, $seats, SORT_ASC, $who, SORT_ASC, $combinedLifts['offeredJourneys']);

for ($i = 0; $i < count($date); $i++) {
	$combinedLifts['offeredJourneys'][$i]['date'] = $date[$i];
	$combinedLifts['offeredJourneys'][$i]['from'] = $from[$i];
	$combinedLifts['offeredJourneys'][$i]['to'] = $to[$i];
	$combinedLifts['offeredJourneys'][$i]['dateNice'] = $dateNice[$i];
	$combinedLifts['offeredJourneys'][$i]['url'] = $liftUrl[$i];
	$combinedLifts['offeredJourneys'][$i]['seats'] = $seats[$i];
	$combinedLifts['offeredJourneys'][$i]['who'] = $who[$i];
}

unset($date, $from, $to, $dateNice, $liftUrl, $seats, $who);

foreach ($combinedLifts['wantedJourneys'] as $key => $row) {
   $date[$key]  = $row['date'];
   $from[$key] = $row['from'];
   $to[$key] = $row['to'];
   $dateNice[$key] = $row['dateNice'];
	$liftUrl[$key] = $row['url'];
	$seats[$key] = $row['seats'];
	$who[$key] = $row['who'];
}

// Sort the data with volume descending, edition ascending
// Add $data as the last parameter, to sort by the common key
array_multisort($date, SORT_ASC, $from, SORT_ASC, $to, SORT_ASC, $liftUrl, SORT_ASC, $dateNice, SORT_ASC, $seats, SORT_ASC, $who, SORT_ASC, $combinedLifts['wantedJourneys']);

for ($i = 0; $i < count($date); $i++) {
	$combinedLifts['wantedJourneys'][$i]['date'] = $date[$i];
	$combinedLifts['wantedJourneys'][$i]['from'] = $from[$i];
	$combinedLifts['wantedJourneys'][$i]['to'] = $to[$i];
	$combinedLifts['wantedJourneys'][$i]['dateNice'] = $dateNice[$i];
	$combinedLifts['wantedJourneys'][$i]['url'] = $liftUrl[$i];
	$combinedLifts['wantedJourneys'][$i]['seats'] = $seats[$i];
	$combinedLifts['wantedJourneys'][$i]['who'] = $who[$i];
}


$out = '<?php header(\'content-type: application/json; charset=utf-8\'); $out = \'';
$out .= json_encode($combinedLifts);
$out .= '\';echo $_GET[\'callback\'] . \'(\' . $out . \')\';?>';


$filename = 'liftsJSON.php';
$f = fopen($filename, "w");
fwrite($f, $out);
fclose($f);

echo('end');
?>
