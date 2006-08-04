<?
if (preg_match("/mission/mission11.php/i", $PHP_SELF)) {
    echo "You can not access this file directly!";
    die();
}
// This is mission specific code used to setup the mission
// Mission Description
// Get a fur_tech ship to level 22 engines
// Make sure that they use the same ship
$var1 = $playerinfo[currentship];
$var2 = rand(25,5000); // Start sector
$var3 = rand(25,5000); // End sector
	// Let's put this into the database
$misres = $db->Execute("UPDATE $dbtables[mstatus] SET var1='$var1',var2='$var2',var3='$var3' WHERE player_id=$playerinfo[player_id] AND mission_id=11");

?>