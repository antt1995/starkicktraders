<?
	include("config.php");
	updatecookie();

  include("languages/$lang");
	$title=$l_ewd_title;
	include("header.php");

	connectdb();

	if (checklogin()) {die();}
	bigtitle();
	$result = $db->Execute ("SELECT * FROM $dbtables[players] WHERE email='$username'");
	$playerinfo=$result->fields;
	// Check to see if they are on a planet or not
	if ($playerinfo[on_planet] == 'Y') {
		echo "You cannot try to use an EWD when you are on a planet!<br><br>";
	} else {
		$result = $db->Execute ("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		$shipinfo=$result->fields;
		srand((double)microtime()*1000000);
		if ($shipinfo[dev_emerwarp]>0)
		{
			$dest_sector=rand(0,$sector_max);
			$result_warp = $db->Execute ("UPDATE $dbtables[players] SET sector=$dest_sector WHERE player_id=$playerinfo[player_id]");
			$result_warp = $db->Execute ("UPDATE $dbtables[ships] SET sector=$dest_sector, dev_emerwarp=dev_emerwarp-1 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
			if ($shipinfo[tow] != 0) {
				$result_warp = $db->Execute ("UPDATE $dbtables[ships] SET sector=$dest_sector WHERE player_id=$playerinfo[player_id] AND ship_id=$shipinfo[tow]");
			}
			log_move($playerinfo[player_id],$dest_sector);
			$l_ewd_used=str_replace("[sector]",$dest_sector,$l_ewd_used);
			echo "$l_ewd_used<BR><BR>";
		} else {
			echo "$l_ewd_none<BR><BR>";
		}
	}
    TEXT_GOTOMAIN();

	include("footer.php");

?>
