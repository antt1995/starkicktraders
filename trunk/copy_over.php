<?php
include("config.php");

$title="Copy Over";
include("header.php");

### Connect to the database.

connectdb();

### Print Title on Page.

bigtitle();
if($swordfish != $adminpass)
{
  echo "<FORM ACTION=copy_over.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
}
else
{
	// Copy over and delete furangee
	$res = $db->Execute("SELECT * FROM bak_players,bak_ships WHERE email NOT LIKE '%furangee' AND ship_id=currentship") or die(mysql_error());
	while (!$res->EOF) {
		$row = $res->fields;
		$player_id=newplayer($row[email], $row[character_name], $row[password], $row[ship_name]);
		echo "Player ID = $player_id<br>";
		if ($player_id>0) {
			echo "Added $row[character_name]<br>";
			$db->Execute("UPDATE $dbtables[players] SET ip_address='$row[ip_address]' WHERE email = '$row[email]'") or die(mysql_error());
			// Profiles
			$res2 = $db->Execute("SELECT * FROM bak_profile WHERE player_id=$row[player_id]");
			$profile = $res2->fields;
			$profile[story] =addslashes($profile[story]);
			$profile[pic_url] =addslashes($profile[pic_url]);
			//echo "UPDATE $dbtables[profile] SET skill='$profile[skill]',alignment='$profile[alignment]',story='$profile[story]',pic_url='$profile[pic_url]' WHERE player_id=$player_id<br>";
			$db->Execute("UPDATE $dbtables[profile] SET skill='$profile[skill]',alignment='$profile[alignment]',story='$profile[story]',pic_url='$profile[pic_url]' WHERE player_id=$player_id") or die(mysql_error()); 	
		}
		$res->MoveNext();
	}
}
include("footer.php");
?>
