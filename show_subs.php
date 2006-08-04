<?php
include("config.php");

$title="Show Subs";
include("header.php");

### Connect to the database.

connectdb();

### Print Title on Page.

bigtitle();
if($swordfish != $adminpass)
{
  echo "<FORM ACTION=show_subs.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
}
else
{
$res = $db->Execute("Select character_name from $dbtables[players] WHERE subscribed != ''");
$admininfo=$res->fields;
$i=1;
while (!$res->EOF) {
	$playerinfo=$res->fields;
	echo "$i. $playerinfo[character_name]<br>";
	$i++;
	$res->MoveNext();
}
include ("footer.php");
}
?>
