<?php 
	
/*
Läser in den kombinerade liftsJSON.php och bygger en lista med de kommande skjutsarna. Dessa görs om till en textsträng som sedan skickas i ett mejl till Facebook-gruppen. Observera att man behöver fylla i giltiga smtp-uppgifter för en användare i gruppen. Använder SwiftMailer, som ligger i lib-mappen.
	
Denna ska köras sist av alla.	
*/
header('Content-type: text/html; charset=utf-8');

require_once 'lib/swift_required.php';

$liftsTxt = file_get_contents('http://www.fjellandermedia.se/skjutsgruppen/liftsJSON.php');

//var_dump($liftsTxt);

$liftsTxt = substr($liftsTxt, 0, -1);
$liftsTxt = substr($liftsTxt, 1);

$lifts = json_decode($liftsTxt);
//var_dump($lifts);
//echo strtotime("+1 day"), "\n";
$timeframe = strtotime("+5 day");
$timeframe = (date('Y-m-d' , $timeframe) . ' 00:00:00' . date('O', $timeframe));
$tomorrow = strtotime("+2 day");
$tomorrow = (date('Y-m-d' , $tomorrow) . ' 00:00:00' . date('O', $tomorrow));


$liftlist = "";

//var_dump($lifts);

foreach ($lifts->offeredJourneys as $journey) {
	if (strtotime($journey->date) < strtotime($timeframe)) {
		if (date('d', strtotime($journey->date)) == date('d')) {
			$day = " idag ";
		} else if (strtotime($journey->date) < strtotime($tomorrow)) {
			$day = " imorgon ";
		} else {
			$day = " den " . substr($journey->dateNice, 0, 5) . " ";
		}
		$liftlist .= $journey->from . " -> " . $journey->to . $day . 'kl. ' . date('H:i', strtotime($journey->date)) . ': ' . $journey->url . PHP_EOL;
	}
}
$liftlist .= PHP_EOL . "Skjutsar som bes om de närmsta fem dagarna:" . PHP_EOL . PHP_EOL;
foreach ($lifts->wantedJourneys as $journey) {
	if (strtotime($journey->date) < strtotime($timeframe)) {
		if (date('d', strtotime($journey->date)) == date('d')) {
			$day = " idag ";
		} else if (strtotime($journey->date) < strtotime($tomorrow)) {
			$day = " imorgon ";
		} else {
			$day = " den " . substr($journey->dateNice, 0, 5) . " ";
		}
		$liftlist .= $journey->from . " -> " . $journey->to . $day . 'kl. ' . date('H:i', strtotime($journey->date)) . ': ' . $journey->url . PHP_EOL;
	}
}
$liftlist .= PHP_EOL . "Fler skjutsar på http://skjutsgruppen.se";
// Create the Transport
$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
  ->setUsername('ANVÄNDARNAMN')
  ->setPassword('LÖSENORD')
  ;

echo $liftlist;
// Create the Mailer using your created Transport
$mailer = Swift_Mailer::newInstance($transport);

// Create a message
$message = Swift_Message::newInstance('Skjutsar som erbjuds de närmsta fem dagarna:')
  ->setFrom(array('foljamed@gmail.com' => 'Föl Jamed'))
  ->setTo(array('skjutsgruppen@groups.facebook.com'))
  ->setBody($liftlist);

// Send the message
 $result = $mailer->send($message);
 var_dump($result);
?>