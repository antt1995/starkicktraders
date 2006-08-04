<?php
include("config.php");

$title="Clean Up Furangee";
include("header.php");

### Connect to the database.

connectdb();

### Print Title on Page.

bigtitle();
if($swordfish != $adminpass)
{
  echo "<FORM ACTION=cleanup_furangee.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
}
else
{
	// Copy over and delete furangee
	$res = $db->Execute("SELECT * FROM $dbtables[players],$dbtables[ships] WHERE email LIKE '%furangee' AND ship_id=currentship AND ship_destroyed='Y'") or die(mysql_error());
	while (!$res->EOF) {
		$row = $res->fields;
		echo "Deleting Furangee = $row[character_name]<br>";
		/*
		echo "DELETE FROM $dbtables[furangee] WHERE furangee_id=$row[email] LIMIT 1<br>";
		echo "DELETE FROM $dbtables[players] WHERE player_id=$row[player_id] LIMIT 1<br>";
		echo "DELETE FROM $dbtables[ships] WHERE ship_id=$row[ship_id] LIMIT 1<br>";		
		*/
		$db->Execute("DELETE FROM $dbtables[furangee] WHERE furangee_id='$row[email]' LIMIT 1") or die(mysql_error());
		$db->Execute("DELETE FROM $dbtables[players] WHERE player_id=$row[player_id] LIMIT 1") or die(mysql_error());
		$db->Execute("DELETE FROM $dbtables[ships] WHERE ship_id=$row[ship_id] LIMIT 1") or die(mysql_error());
		$res->MoveNext();
	}
}
include("footer.php");
?>
