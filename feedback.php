<?

	include("config.php");
	updatecookie();

  include("languages/$lang");
	$title=$l_feedback_title;
	include("header.php");

	connectdb();

	if (checklogin()) {die();}

	$result = $db->Execute ("SELECT * FROM $dbtables[players] WHERE email='$username'");
	$playerinfo=$result->fields;
        bigtitle();
	if (empty($content))
	{
		echo "<h1>Stop!</h1>If you have a problem, or think someone is cheating, etc. POST IT TO THE FORUMS!<br><br>";
		/*
		echo "<form action=feedback.php method=post>";
		echo "<table>";
		echo "<tr><td>$l_feedback_to</td><td><input disabled type=text name=dummy size=40 maxlength=40 value=GameAdmin></td></tr>";
		echo "<tr><td>$l_feedback_from</td><td><input disabled type=text name=dummy size=40 maxlength=40 value=\"$playerinfo[character_name] - $playerinfo[email]\"></td></tr>";
		echo "<tr><td>$l_feedback_topi</td><td><input disabled type=text name=dummy size=40 maxlength=40 value=$l_feedback_feedback></td></tr>";
		echo "<tr><td>$l_feedback_message</td><td><textarea name=content rows=5 cols=40></textarea></td></tr>";
		echo "<tr><td></td><td><input type=submit value=$l_submit><input type=reset value=$l_reset></td>";
		echo "</table>";
		echo "</form>";
		echo "Please use the forums to report bugs or ask for support. Feedback is not a way to get support! Thank you.<br>";
		echo "<br>$l_feedback_info<br>";
	} else {
		echo "$l_feedback_messent<BR><BR>";
		mail("$admin_mail", $l_feedback_subj, "SKT LE Feedback\r\nIP address - $ip\r\nPlayer Name - $playerinfo[character_name] - ID# $playerinfo[player_id] \r\n\r\n$content","From: $playerinfo[email]\r\nX-Mailer: PHP/" . phpversion());
		*/
		
	}

    TEXT_GOTOMAIN();
	include("footer.php");

?>
