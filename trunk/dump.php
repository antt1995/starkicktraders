<?
	include("config.php");
	updatecookie();

  include("languages/$lang");
	$title=$l_dump_title;
	include("header.php");

	connectdb();

	if (checklogin()) {die();}

	$result = $db->Execute ("SELECT * FROM $dbtables[players] WHERE email='$username'");
	$playerinfo=$result->fields;
	$result = $db->Execute ("SELECT ship_colonists FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
	$shipinfo=$result->fields;
	$result2 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$playerinfo[sector]");
	$sectorinfo=$result2->fields;
        bigtitle();

	if ($playerinfo[turns]<1)
	{
		echo "$l_dump_turn<BR><BR>";
		TEXT_GOTOMAIN();
		include("footer.php");
		die();
	}
	if ($shipinfo[ship_colonists]==0)
	{
		echo "$l_dump_nocol<BR><BR>";
	} elseif ($sectorinfo[port_type]=="special") {
		$update = $db->Execute("UPDATE $dbtables[players] SET turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
		$update = $db->Execute("UPDATE $dbtables[ships] SET ship_colonists=0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		echo "$l_dump_dumped<BR><BR>";
	} else {
		echo "$l_dump_nono<BR><BR>";
	}
	TEXT_GOTOMAIN();
	include("footer.php");

?>
