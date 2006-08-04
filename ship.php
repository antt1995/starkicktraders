<?
include("config.php");
updatecookie();

include("languages/$lang");

$title=$l_ship_title;
include("header.php");

connectdb();

if(checklogin())
{
  die();
}


$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;
$res4 = $db->Execute("SELECT * FROM $dbtables[ships] WHERE ship_id=$ship_id");
$othership = $res4->fields;
$res2 = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id=$othership[player_id]");
$otherplayer = $res2->fields;
//echo "DEBUG: The other ship's sector is $othership[sector]";
$res3 = $db->Execute("SELECT * FROM $dbtables[furangee] WHERE furangee_id='$otherplayer[email]' AND orders='4'");
$furangee = $res3->fields;
$playerscore = gen_score($playerinfo[player_id]);
$targetscore = gen_score($otherplayer[player_id]);

bigtitle();
// Check if in Sector 0
if ($playerinfo[sector] == 0) {
	echo "There are too many ships in Sector 0 and your sensors almost go into an overloaded state!<br>This unauthorized usage has been reported to Cirius Cybernetics Corp.<br>";
} else if ($othership[player_id] == 0) {
	if (isset($cmd)) {
		if (!strstr($playerinfo[subscribed],"subscr_payment") && ($othership[hull] > 8 | $othership[engines] > 8 | $othership[power] > 8 | $othership[computer] > 8 | $othership[sensors] > 8 | $othership[beams] > 8 | $othership[armour] > 8 | $othership[cloak] > 8 | $othership[torp_launchers] > 8 | $othership[shields] > 8)) {
			echo "You need to subscribe to get a ship with greater than level 8 techs.<p>";
			include("subscribe.php");
			die();
		}
		// Try to capture this ship
		$res4 = $db->Execute("LOCK TABLES $dbtables[ships] write,$dbtables[players] write");
		$res5 = $db->Execute("UPDATE $dbtables[ships] SET player_id=$playerinfo[player_id],on_planet='Y',planet_id=0,tow=0 WHERE ship_id=$ship_id");
		$res6 = $db->Execute("UPDATE $dbtables[ships] SET tow=$ship_id WHERE ship_id=$playerinfo[currentship]");
		$res7 = $db->Execute("UNLOCK TABLES");
		if (!$res4 | !$res5 | !$res6) {
			echo "The ship could not be captured!<br>";
			echo $db->ErrorMsg() . "<br>";
		} else {
			echo "You captured the ship in your tractor beam! It now belongs to you.<br>";
		}
	} else {
		echo "$l_ship_youc unowned ship <font color=white>", $othership[ship_name]."</font>";
		echo "<br><br>";
		echo "<a href=ship.php?ship_id=$ship_id&cmd=cap&kk=".date("U").">You can capture this ship and tow it with a tractor beam!</a><br>";
	}
} else {
	if($othership[sector] != $playerinfo[sector])
	{
	  echo "$l_ship_the <font color=white>", $othership[ship_name],"</font> $l_ship_nolonger ", $playerinfo[sector], "<BR>";
	}
	else
	{
		echo "$l_ship_youc <font color=white>", $othership[ship_name], "</font>, $l_ship_owned <font color=white>";
		if (strpos($otherplayer[email],"@furangee") ==0) {
			echo "<a href=ranking.php?detail=".urlencode($otherplayer[character_name]).">". $otherplayer[character_name]."</a>";
		} else {
			echo $otherplayer[character_name];
		}
		if ($othership[tow] > 0) {
			$res4 = $db->Execute("SELECT * FROM $dbtables[ships],$dbtables[ship_types] WHERE ship_id=$othership[tow] AND type_id=type");
			$towedship = $res4->fields;
			echo "</font><br>The $othership[ship_name] is towing the $towedship[ship_name].<br>";
		}
	 
		echo "</font>.<br><br>";
		echo "$l_ship_perform<BR><BR>";
		if (!$res3->EOF) {
			// We have a special trader!
			echo $otherplayer[character_name]." is a Special Trader! <font color=red>Their upgrades are illegal but cheap.</font> Trade quickly if you dare!<br>";
			echo "<a href=trport.php?kk=".date("U").">Trade</a><br>";
		}
		echo "<a href=scan.php?ship_id=$ship_id>$l_planet_scn_link</a><br>";
		echo "<a href=attack.php?ship_id=$ship_id>$l_planet_att_link</a>";
		if(($targetscore / $playerscore < $bounty_ratio || $otherplayer[turns_used] < $bounty_minturns) && 
           !("furangee" == substr($otherplayer[email], -8) && ($targetscore < 500000)) ) {
		   	echo "  <font color=red><b>WARNING: Attacking this ship may incur a fine! Check by scanning to be sure!</b></font>";
		}
		echo "<br>";
	
		echo "<a href=mailto.php?to=$otherplayer[player_id]>$l_send_msg</a><br>";
	}
}
echo "<BR>";
TEXT_GOTOMAIN();

include("footer.php");

?>
