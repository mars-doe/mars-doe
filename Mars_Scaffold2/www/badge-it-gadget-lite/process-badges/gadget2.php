<?php

//Badge-It Gadget Lite v0.5.0 - Simple scripted system to award and issue badges into Mozilla Open Badges Infrastructure
//Copyright (c) 2012 Kerri Lemoie, Codery - gocodery.com
//Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php

/*This script creates the badge for the user. and provides the link to get-my-badge.php which interacts with open badges*/

include 'gadget-settings.php';

function rand_string( $length ) { //this function just obscures the users name and badge in the url get string 
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}
	
	return $str;
}


if( isset($_POST) ){
	
	
	//set all variables
	
	$badgeId 							= $_POST['badge_info'];
	$badgeRecipientName 	= $_POST['badge_recipient_name'];
	$badgeRecipientEmail 	= $_POST['badge_recipient_email'];
	$badgeEvidenceURL 		= $_POST['badge_evidence_url'];
	$badgeName 						= $badges_array[$badgeId]['name'];
	$badgeImage						= $badges_array[$badgeId]['image'];
	$badgeDescription			= $badges_array[$badgeId]['description'];
	$badgeCriteria				= $badges_array[$badgeId]['criteria_url'];
	$badgeExpires					= $badges_array[$badgeId]['expires'];	
	$date = date('Y-m-d');
	$err = '';
	$msg = '';
	$imageURL = '';
	$msgURL = '';
	
	
	//salt email	
	$salt = rand_string(8); //randomized everytime
	$hashed_email = hash('sha256', $badgeRecipientEmail  . $salt);
	

	//creates JSON file - will write over an existing badge for same badge and same user.

	$filename = str_rot13($badgeId.'-'. preg_replace("/ /","_",$badgeRecipientName));
	
	$jsonFilePath = $json_dir . $filename .'.json';
	$hardIssuer = 'http://www.voiceovervinnie.com';

	$handle = fopen($jsonFilePath, 'w');
	$fileData = array(
		'recipient' => "sha256$".$hashed_email,
		'salt' => $salt,
		'evidence' => $badgeEvidenceURL,
		'issued_on'=> $date,
		'badge' => array(
			'version' => '0.5.0',
			'name' => $badgeName,
			'image' => $issuer_url.$badge_images_dir.$badgeImage,
			'description' => $badgeDescription,
			'criteria' => $badgeCriteria,
			'expires' => $badgeExpires,
			'issuer' => array(
				'origin' => $hardIssuer,
				'name' => $issuer_name,
				'org' => $issuer_org,
				'contact' => $issuer_contact,
			)
		)
	);
	
	//Writes JSON file
	
	if (fwrite($handle, json_encode($fileData)) === FALSE) {
        $err = '<div class="badge-error">Cannot write to file ($jsonFilePath). Please check your \$json_dir in gadget_settings.php</div>';
	} else { 
		//Sucess message and write badge to badge_records.txt file
		$imageURL = $issuer_url.'/badge-it-gadget-lite/digital-badges/images/'.$badgeImage;
		$msgURL = $issuer_url.'/badge-it-gadget-lite/digital-badges/get-my-badge.php?id='.$filename;
		$msg = '<div class="badge-link-success">Your badge is ready to be issued. Please provide this link to the badge earner: <a href="'.$issuer_url.'/badge-it-gadget-lite/digital-badges/get-my-badge.php?id='.$filename.'">'.$issuer_url.'/badge-it-gadget-lite/digital-badges/get-my-badge.php?id='.$filename.'</a></div>';
		fclose($handle);
		
	//Writes to badge_records.txt file
		
		$badgeRecordsFile = $root_path . $badge_records_file;
	
		$badgeHandle = fopen($badgeRecordsFile, 'a'); 
		$badge_data = "BADGE AWARDED: ".$date.", ".$badgeName.", ".$jsonFilePath.", ".$badgeRecipientName.", ".$badgeRecipientEmail.", ".$badgeCriteria;
	
		if (! empty($badgeEvidenceURL)) {
			$badge_data .= ", ".$badgeEvidenceURL;
		}
	
		$badge_data .= "\n";
	
		if (fwrite($badgeHandle, $badge_data) === FALSE) {
	  		$err .= '<div class="badge-error">Cannot write to file ('.$badgeRecordsFile.'). Please check your $root and $badge_records_file in gadget_settings.php. Your JSON file was created but the badge was not recorded.</div>';
	  	}
	
	 	fclose($badgeHandle);
				
	}

	//Set return messages
	$returndata = array(
	'posted_form_data' => array(
		'badgeId' => $badgeId,
		'badgeRecipient' => $badgeRecipientName,
		'badgeRecipientEmail' => $badgeRecipientEmail,
		'badgeEvidenceUrl' => $badgeEvidenceURL
		),
	'success' => $msg,
	'errors' => $err
	);
	
	//go back to Gadget Badger page with results
	/*
	if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'){
		
		//set session variables & return success or errors
		session_start();
		$_SESSION['bf_returndata'] = $returndata;
		
		//redirect back to form
		header('location: ' . $_SERVER['HTTP_REFERER']);
	}
	*/
	
	//instead of the Badgers session return, I'd like a new page!
	
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Badge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Badge-It Gadget Lite Badger">

<link rel="stylesheet" type="text/css" href="../../css/mars.css" />
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900,100italic,300italic,400italic,700italic,900italic' rel='stylesheet' type='text/css'>

<script src="../../js/libs/jquery-1.9.1.min.js"></script>
<script src="../../js/libs/jquery-ui-1.10.1.custom.min.js"></script>

<script src="http://beta.openbadges.org/issuer.js"></script>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function () {
	
	var backpackloginpopupcontinue = document.getElementById('backpackloginpopupcontinue');
    backpackloginpopupcontinue.onclick = function() {
    	window.location = '<?php echo $msgURL; ?>';
    };
    var backpackloginpopupkskip = document.getElementById('backpackloginpopupkskip');
    backpackloginpopupkskip.onclick = function() {
    	close();
    };
	
}, false);
</script>

  </head>

  <body>
		
		<div id="backpackloginpopup" class="modalmask" style="opacity:1.0;pointer-events:auto;">
			<div id="backpacklogindiv" class="badgecontenttarget">
				<h1>Mission To Mars</h1>
				<br>
				<?php echo $badgeDescription; ?>
				<br><br>
				<img src="<?php echo $imageURL; ?>">
				<br><br><br>
				<br><br>
				<div style="float:right;">
					<button id="backpackloginpopupkskip" class="squarebluebutton">Skip</button>
					<button id="backpackloginpopupcontinue" class="squarebluebutton">Continue</button>
				</div>
			</div>
		</div>
		
		
	
	</body>
	
</html>