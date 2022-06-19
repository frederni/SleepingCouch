<?php

//$firstname is the first name of target
//$lastname is the last name of target
//$email is the targets email address
//$meeting_date is straight from a DATETIME mysql field and assumes UTC.
//$meeting_name is the name of your meeting
//$meeting_duretion is the duration of your meeting in seconds (3600 = 1 hour)

function sendIcalEmail($firstname,$lastname,$email,$meeting_date,$meeting_name,$meeting_duration) {

	$from_name = "Sleeping couch booking";
	$from_address = "ceo@google.com";
	$subject = $firstname . " sleeps on the couch"; //Doubles as email subject and meeting subject in calendar
	$meeting_description = "Innsjekkingstidspunkt for den som skal overnatte. Melding fra gjest: " . Trim(stripslashes($_POST['Message'])) . "\n\n";
	$meeting_location = "Mountain View, California"; //Where will your meeting take place
	
	
	//Convert MYSQL datetime and construct iCal start, end and issue dates
	$meetingstamp = strtotime($meeting_date . " UTC");    
	$dtstart= gmdate("Ymd\THis\Z",$meetingstamp);
	$dtend= gmdate("Ymd\THis\Z",$meetingstamp+$meeting_duration);
	$todaystamp = gmdate("Ymd\THis\Z");
	
	//Create unique identifier
	$cal_uid = date('Ymd').'T'.date('His')."-".rand()."@mydomain.com";
	
	//Create Mime Boundry
	$mime_boundary = "----Meeting Booking----".md5(time());
		
	//Create Email Headers
	$headers = "From: ".$from_name." <".$from_address.">\n";
	$headers .= "Reply-To: ".$from_name." <".$from_address.">\n";
	
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
	$headers .= "Content-class: urn:content-classes:calendarmessage\n";
	
	//Create Email Body (HTML)
	$message .= "--$mime_boundary\n";
	$message .= "Content-Type: text/html; charset=UTF-8\n";
	$message .= "Content-Transfer-Encoding: 8bit\n\n";
	
	$message .= "<html>\n";
	$message .= "<body>\n";
	$message .= '<p>Ny sleeping couch booking by '.$firstname.'.</p>';
	$message .= '<p>Please find attached calendar invite (works the best in the Gmail-app or on your computer)</p>';    
	$message .= "</body>\n";
	$message .= "</html>\n";
	$message .= "--$mime_boundary\n";
	
	//Create ICAL Content (Google rfc 2445 for details and examples of usage) 
	$ical =    'BEGIN:VCALENDAR
PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN
VERSION:2.0
METHOD:REQUEST
BEGIN:VTIMEZONE
TZID:Europe/Oslo
BEGIN:STANDARD
DTSTART:20171010T000000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
TZOFFSETFROM:+0000
TZOFFSETTO:+0000
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:20170310T000000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
TZOFFSETFROM:+0000
TZOFFSETTO:+0000
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
ORGANIZER:MAILTO:'.$from_address.'
ATTENDEE;ROLE=REQ-PARTICIPANT;RSVP=TRUE;CN=SEAN CODY:MAILTO:sean.cody@seancody.com
DTSTART:'.$dtstart.'
DTEND:'.$dtend.'
LOCATION:'.$meeting_location.'
TRANSP:OPAQUE
SEQUENCE:0
UID:'.$cal_uid.'
DTSTAMP:'.$todaystamp.'
DESCRIPTION:'.$meeting_description.'
SUMMARY:'.$subject.'
PRIORITY:5
CLASS:PUBLIC
END:VEVENT
END:VCALENDAR';   
	
	$message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST;charset=utf-8\n';
	$message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST\n';
	$message .= "Content-Transfer-Encoding: 8bit\n\n";
	$message .= $ical;            
	
	//SEND MAIL
	$mail_sent = @mail( $email, $subject, $message, $headers );
	
	if($mail_sent)     {
		return true;
	} else {
		return false;
	}   

}


?>