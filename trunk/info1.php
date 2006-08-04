<?php
include("config.php");

$title="Info 1";
include("header.php");

### Connect to the database.

connectdb();

### Print Title on Page.

bigtitle();
if($swordfish != $adminpass)
{
  echo "<FORM ACTION=info1.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
}
else
{
	$res = $db->Execute("Select character_name,$dbtables[players].player_id,browser from $dbtables[players],$dbtables[browser] WHERE $dbtables[players].player_id=$dbtables[browser].player_id ORDER BY $dbtables[players].player_id");
	$admininfo=$res->fields;
	echo "<table>";
	while (!$res->EOF) {
		$row=$res->fields;
		echo "<tr><td>".$row[player_id]."</td><td>".$row[character_name]."</td><td>".$row[browser]."</td></tr>";
		$res->MoveNext();
	}
	echo "</table>";
}
include ("footer.php");
?>
