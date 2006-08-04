<?
	include("config.php");
  include("languages/$lang");

	$title="Mass Mail";
	include("header.php");

  connectdb();


if($swordfish != $adminpass)
{
  echo "<FORM ACTION=admin.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20>&nbsp;&nbsp;";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
 
} else {
	bigtitle();
	echo "<FORM ACTION=massmail.php METHOD=POST>";
	$message = stripslashes($message);
    echo "<INPUT TYPE=hidden NAME=message VALUE=\"$message\")";
	echo "Sending message $subject <br> $message<br>";
    echo "<INPUT TYPE=hidden NAME=subject VALUE=\"$subject\">";
	if (!$from) {
		$from =0;
	}
	$result = $db->Execute ("select character_name, email, password from $dbtables[players] ORDER BY 'character_name' LIMIT $from,10");
	while(!$result->EOF) {
		$playerinfo=$result->fields;
		if (!strstr($playerinfo['email'],"@furangee")) {
			$optout = "\r\nWe sent this to you because you registered for SKT LE. If you want off the list just reply to this email with your game email address: $playerinfo[email] and we will delete your account.\r\n";
			echo "Message sent to ".$playerinfo['character_name']." at address ".$playerinfo['email']."<br>";
			$body = "Hi ".$playerinfo['character_name']." (".$playerinfo['email'].")!\r\n\r\n$message\r\nYour login is: $playerinfo[email]\r\nYour password is: $playerinfo[password]\r\n\r\nOr click this URL to play now! http://www.mpgames.com/skt/login2.php?email=".urlencode($playerinfo[email])."&pass=".urlencode($playerinfo[password])."\r\n\r\nBest regards,\r\nStarkick Traders LE\r\n".$optout;
			//echo $body;
			//echo "</pre>";
			mail($playerinfo['email'], "$subject", $body, "From: Starkick Traders LE <sktleadmin@berigames.com>\r\nReply-To: sktleadmin@berigames.com\r\nX-Mailer: PHP/" . phpversion());
			//mail("bengibbs@tmail.com", "$subject", $body, "From: Starkick Traders <sktadmin@berigames.com>\r\nReply-To: sktadmin@berigames.com\r\nX-Mailer: PHP/" . phpversion());
		}
		$result->MoveNext();
	}
	echo "Send mail from # $from <br>";
	$from += 10;
	echo "<INPUT TYPE=hidden NAME=from VALUE=$from>";
	echo "<INPUT TYPE=hidden NAME=swordfish VALUE=$swordfish>";

  echo "<INPUT TYPE=SUBMIT VALUE=\"Send More\">";
  echo "</FORM>";
}
include("footer.php");
?>

