<?php
/* Läser in skjutsar från Skjutsgruppens hemsida, lagrar skjutsar som ännu inte hänt i liftsJSON1.php. Dessa kombineras sedan i combineJSON.php.  Läser in var man ska börja från startLift.txt som är den första giltiga skjutsen förra körningen. Sparar var man slutar i startLift3.txt som sedan läses av buildJSON3.Servern kan inte hantera för många anrop i samma fil, därav uppdelningen i tre. Sannolikt behöver detta utökas till ännu fler, skulle jag tro. 
	
Observera att man behöver fylla i en giltig användares epost och lösenordet i $url-variabeln, f.n. rad 26.
	
Den här filen ska köras först av alla, därefter buildJSON2, buildJSON3, combineJSON och sist facebookboten.
*/


header('Content-type: text/html; charset=utf-8');

$continue = 1;
$now = date("c");
$i = 900;
$liftsArray = array();

$filename = 'startLift.txt';
$f = fopen($filename, "r"); 
$i = fread($f, filesize($filename));
fclose($f);

echo "starting";
$calls = 0;

while($continue == 1) {
	$url = 'http://skjutsgruppen.nu/api/v1/journeys/' . $i . '?email=EPOST&password=LÖSENORD';
	echo $url;

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$json = '';
	$json = curl_exec($ch);
	
	echo curl_errno($ch);
	
	$lift = json_decode($json);
	
	if($lift->message == "not found") {
		$notfound++;
	} else {
		$notfound = 0;
	}
	
	$liftDate =  $lift->journey->dateTime;
	if (count($liftsArray) == 0 && $liftDate > $now) {
		$filename = 'startLift.txt';
		$f = fopen($filename, "w");
		fwrite($f, $i);
		fclose($f);
	}

	if ($liftDate > $now) {
		$liftDateNice = substr($liftDate, 8, 2) . "/" . substr($liftDate, 5, 2) . " " . substr($liftDate, 0, 4) . " kl. " . substr($liftDate, 11, 5);
		$liftsArray[] = array('date' => $liftDate, 'url' => $lift->journey->journeyLinkUrl, "from" => $lift->journey->from, "to" => $lift->journey->to, 'dateNice' => $liftDateNice, 'type' => $lift->journey->journeyType, "seats" => $lift->journey->seats, "who" => $lift->journey->comments[0]->author);
		echo ("<a href='" . $lift->journeyLinkUrl . "' target='_blank'>" . $lift->journey->from . " -> " . $lift->journey->to . " den " . $liftDateNice . "</a><br/>");
	} 
	// Close handle
	curl_close($ch);
	echo "resa " . $i . "<br/>";
	$i++;
	if ($notfound == 20 || $calls++ == 2000) { //$notfound == 10) {
		$filename = 'startLift2.txt';
		$f = fopen($filename, "w");
		fwrite($f, $i);
		fclose($f);
		$continue = 0;
	}
}
echo $i;
	//var_dump($liftsArray);
foreach ($liftsArray as $key => $row) {
    $type[$key]  = $row['type'];
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
array_multisort($type, SORT_ASC, $date, SORT_ASC, $from, SORT_ASC, $to, SORT_ASC, $liftUrl, SORT_ASC, $dateNice, SORT_ASC, $seats, SORT_ASC, $who, SORT_ASC, $liftsArray);

$out = '<?php header(\'content-type: application/json; charset=utf-8\'); $out = \'';
$out .= '{"offeredJourneys":[';

$i = 0;
while ($type[$i] == "offered") {
	$out .= '{"url":"' . $liftUrl[$i] . '" , "from":"'  . $from[$i] . '" , "to":"' . $to[$i] . '" , "date":"' . $date[$i] . '" , "dateNice":"' . $dateNice[$i] . '" , "seats":"' . $seats[$i] . '" , "who":"' . preg_replace('~"~', '\"', trim($who[$i])) . '"}';	
	if ($type[$i + 1] == "offered") {
		$out .= ' , ';
	}	
	$i++;
}

$out .= '] , "wantedJourneys":[';

for($i; $i < count($date); $i++) {
	$out .= '{"url":"' . $liftUrl[$i] . '" , "from":"'  . $from[$i] . '" , "to":"' . $to[$i] . '" , "date":"' . $date[$i] . '" , "dateNice":"' . $dateNice[$i] . '" , "seats":"' . $seats[$i] . '" , "who":"' . preg_replace('~"~', '\"', trim($who[$i])) . '"}';
	if ($i != (count($date) - 1)) {
		$out .= ' , ';
	}	 
}

$out .= '] , "lastBuild":"' . date("U") . '"}';

$out .= '\';echo $_GET[\'callback\'] . \'(\' . $out . \')\';?>';

$filename = 'liftsJSON1.php';
$f = fopen($filename, "w");
fwrite($f, $out);
fclose($f);

echo "end";
?>


