<?
include("config.php");
updatecookie();

include("languages/$lang");

$title="Tractor Beam";
include("header.php");

connectdb();

if (checklogin())
{
  die();
}

$result = $db->Execute ("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo=$result->fields;
// Find out what my ship is
$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship] LIMIT 1");
$shipinfo = $res->fields;

bigtitle();

if($shipinfo[tow] > 0)
{
	// Releasing ships to float away in sector zero is not allowed
	if ($playerinfo[sector] == 0) {
		echo "Releasing ships in Sector 0 is not allowed!";
	} else if ($playerinfo[on_planet] == "Y") { // Are we in space or on a planet?
		$update = $db->Execute("UPDATE $dbtables[ships] SET tow=0 WHERE ship_id=$shipinfo[ship_id]");
		echo "Tractor beam released. The towed ship settles into the Space Dock";
	} else {
		$sector = rand(1,5000);
		$update = $db->Execute("UPDATE $dbtables[ships] SET sector=$sector, player_id=0, on_planet='N' WHERE ship_id=$shipinfo[tow]");		
		$update = $db->Execute("UPDATE $dbtables[ships] SET tow=0 WHERE ship_id=$shipinfo[ship_id]");
		echo "Tractor beam released! The towed ship slips into a wormhole and disappears into the void...";
	}
}
else
{
  echo "Your tractor beam is not active!";
}
echo "<br><br>";
TEXT_GOTOMAIN();

include("footer.php");

?>
