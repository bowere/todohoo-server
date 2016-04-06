<?php
$from = $_POST['from'];
$to = $_POST['to'];
$carrier = $_POST['carrier'];
$message = stripslashes($_POST['message']);

$formatted_number = $to;

if ((empty($from)) || (empty($to)) || (empty($message))) {
  header ("Location: sms_error.php");
}

else {
  if ($carrier == "verizon") {
    $formatted_number = $formatted_number."@vtext.com";
	}

	else if ($carrier == "tmobile") {
    $formatted_number = $formatted_number."@tmomail.net";
	}

	else if ($carrier == "sprint") {
    $formatted_number = $to."@messaging.sprintpcs.com";
	}

	else if ($carrier == "att") {
    $formatted_number = $to."@txt.att.net";
	}

	else if ($carrier == "virgin") {
    $formatted_number = $to."@vmobl.com";
	}

  $subject = "Msg Sent From PHP";
  mail($formatted_number, $subject, $message, "From: ". $from);
  
	header ("Location: ../../txt-msg-form.html");
}
?>