<?php
include("config.php");

$title="Move Ships";
include("header.php");

### Connect to the database.

connectdb();

### Print Title on Page.

bigtitle();
if($swordfish != $adminpass)
{
  echo "<FORM ACTION=moveships.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
}
else
{
$res = $db->Execute("Select currentship from $dbtables[players] WHERE player_id=1");
$admininfo=$res->fields;
$res2 = $db->Execute("SELECT ship_id, ship_name, sector FROM $dbtables[ships] WHERE sector=0 AND player_id=1 AND on_planet='Y' AND ship_id != $admininfo[currentship] ORDER BY RAND()");
$res3= $db->Execute("SELECT sector_id FROM $dbtables[universe] WHERE port_type='special' ORDER BY RAND()");

while (!$res2->EOF && !$res3->EOF) {
	$ship=$res2->fields;
	
	$port=$res3->fields;
	echo "Moving ship $ship[ship_name] ($ship[ship_id]) from Sector $ship[sector] to Sector $port[sector_id]<br>";
	//echo "UPDATE $dbtables[ships] SET sector=$port[sector_id] WHERE ship_id=$ship[ship_id] AND player_id=1 LIMIT 1<br>";
$res4=$db->Execute("UPDATE $dbtables[ships] SET sector=$port[sector_id] WHERE ship_id=$ship[ship_id] AND player_id=1 LIMIT 1");
	$res2->MoveNext();
	$res3->MoveNext();
}
include ("footer.php");
}
?>
