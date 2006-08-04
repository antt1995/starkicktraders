<?

include("config.php");
updatecookie();

include("languages/$lang");
$title=$l_move_title;

include("header.php");

//Connect to the database
connectdb();

//Check to see if the user is logged in
if (checklogin())
{
    die();
}

//Retrieve the user and ship information
$result = $db->Execute ("SELECT * FROM $dbtables[players] WHERE email='$username'");
//Put the player information into the array: "playerinfo"
$playerinfo=$result->fields;
if ($playerinfo[on_planet] == 'Y') {
	echo "You cannot move sector when you are on a planet!<br><br>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
}

$result = $db->Execute ("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo=$result->fields;
//Check to see if the player has less than one turn available
//and if so return to the main menu
if ($playerinfo[turns]<1)
{
	echo "$l_move_turn<BR><BR>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
}

//Retrieve all the sector information about the current sector
$result2 = $db->Execute ("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]'");
//Put the sector information into the array "sectorinfo"
$sectorinfo=$result2->fields;

// Escape the sector variable
if (isset($sector)) {
	$sector = mysql_escape_string($sector);
}

//Retrieve all the warp links out of the current sector
//echo "\n\n\n<!-- DEBUG: SELECT * FROM $dbtables[links] WHERE link_start='$playerinfo[sector]' AND link_dest='$sector' -->\n\n\n";
$result3 = $db->Execute ("SELECT * FROM $dbtables[links] WHERE link_start='$playerinfo[sector]' AND link_dest='$sector'");
if ($result3 || ($playerinfo[sector]==$sector)) {
	if (!$result3->EOF || ($playerinfo[sector]==$sector)) {
		$ok=1;
		$calledfrom = "move.php";
		include("check_fighters.php");
		echo "\n\n\n<!-- DEBUG: ok=$ok\n\n\n-->";
		if($ok>0 && $playerinfo[sector] != $sector){
		   $stamp = date("Y-m-d H-i-s");
		   log_move($playerinfo[player_id],$sector);
		   $move_result = $db->Execute ("UPDATE $dbtables[players] SET last_login='$stamp',turns=turns-1, turns_used=turns_used+1, sector='$sector' where player_id=$playerinfo[player_id]");
			// Move ship as well and any ship being towed too
		   $db->Execute ("UPDATE $dbtables[ships] SET sector='$sector' where player_id=$playerinfo[player_id] AND (ship_id=$playerinfo[currentship] OR ship_id='$shipinfo[tow]')");
		}
		/* enter code for checking dangers in new sector */
		include("check_mines.php");
		if ($ok==1) {
			if ($browser == "treo") {
				include("treomain.php");
			} else if ($browser == "up") {
				include("upbrow.php");
			} else {
				include("metamain.php");
			}
			die();
		} else {
			TEXT_GOTOMAIN();
		}
	} else {
		echo "<h1>Move</h1>";
		echo "There does not seem to be a warp link to that sector from here!<br><br>";
		TEXT_GOTOMAIN();
	}
}
else
{
    // Move failed
    $db->Execute("UPDATE $dbtables[players] SET cleared_defences=' ' where player_id=$playerinfo[player_id]");
	if ($browser == "treo") {
		include("treomain.php");
	} else {
		include("metamain.php");
	}
	die();
}


  include("footer.php");

?>
