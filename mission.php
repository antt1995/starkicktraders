<?
function subvars($in) {
	global $playerinfo,$dbtables, $missioninfo;
	$sql = str_replace("playerinfo[player_id]",$playerinfo[player_id],$in);
	$sql = str_replace("playerinfo[character_name]",$playerinfo[character_name],$sql);
	$sql = str_replace("playerinfo[currentship]",$playerinfo[currentship],$sql);
	$sql = str_replace("playerinfo[sector]",$playerinfo[sector],$sql);
	$sql = str_replace("playerinfo[score]",$playerinfo[score],$sql);
	$sql = str_replace("dbtables[players]",$dbtables[players],$sql);
	$sql = str_replace("dbtables[planets]",$dbtables[planets],$sql);
	$sql = str_replace("dbtables[ships]",$dbtables[ships],$sql);
	$sql = str_replace("dbtables[furangee]",$dbtables[furangee],$sql);
	$sql = str_replace("dbtables[logs]",$dbtables[logs],$sql);
	$sql = str_replace("dbtables[kills]",$dbtables[kills],$sql);
	$sql = str_replace("[var1]",$missioninfo[var1],$sql);
	$sql = str_replace("[var2]",$missioninfo[var2],$sql);
	$sql = str_replace("[var3]",$missioninfo[var3],$sql);
	//echo "<!-- DEBUG: $sql -->";
	return $sql;
}
function showmiss($title,$step,$info) {
	global $missioninfo,$color_line2;
	$info = str_replace("[message]",$missioninfo[message],$info);  // Put the unique mission message in there if required
	$info = str_replace("[var1]",$missioninfo[var1],$info);
	$info = str_replace("[var2]",$missioninfo[var2],$info);
	$info = str_replace("[var3]",$missioninfo[var3],$info);
	if ($step == "-1") {
		$step = "Unlimited";
	} else if ($step == "1") {
		$step = "1 - Mission Ended";
	}
	echo "<table border=1 cellspacing=0 cellpadding=5 bgcolor=$color_line2 width=75% align=center>";
	echo "<tr><td align=center><font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size=2><b>Mission:</b><br>$info</font></td></tr>";
	echo "<tr><td align=center><p><font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size=2><b>$title Turns Left: $step</b></font></td></tr>";
	echo "<tr><td align=center><font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size=2><a href=main.php?cmd=quitmission&kk=".date("U").">Quit 
		Mission</a></font></td></tr></table>";
}
// Get the current mission and state for this person
if ($cmd == "quitmission") {
	$res = $db->Execute("UPDATE $dbtables[mstatus] SET completed='Y' WHERE player_id=$playerinfo[player_id] AND completed='N' LIMIT 1");
}
$res = $db->Execute("SELECT * FROM $dbtables[mstatus],$dbtables[missions] WHERE $dbtables[mstatus].state =$dbtables[missions].state AND $dbtables[mstatus].mission_id =$dbtables[missions].mission_id AND player_id=$playerinfo[player_id] AND completed='N' LIMIT 1");
if ($res->EOF) {
	//echo " DEBUG: Not in mission<br>";
	// We are not in a mission so let's check to see if any missions have been triggered
	// Get a list of all the triggers for missions
	$res4 = $db->Execute("SELECT * FROM $dbtables[missions] WHERE state=0");
	// See if we have done them or not
	$newmission = false;
	while (!$res4->EOF && !$newmission) {
		$trigger = $res4->fields;
		// See if we have passed the trigger
		$sql = subvars($trigger[sql]);
		//echo "<!-- Trigger check: $sql -->";
		$res2 = $db->Execute($sql);
		if (!$res2->EOF) {
			// Check if we have done this already or not
			$res3 = $db->Execute("SELECT * FROM $dbtables[mstatus] WHERE mission_id=$trigger[mission_id] AND player_id=$playerinfo[player_id]");
			if ($res3->EOF) {
				//echo "\n<!-- New mission -->";
				// *************  New mission! ******************
				// How many turns do we have to finish this?
				$turnlimit = -1; // Unlimited turns
				if ($trigger[maxturns] != -1) {
					$turnlimit = $playerinfo[turns_used] + $trigger[maxturns];
				}	
				$db->Execute("INSERT INTO $dbtables[mstatus] SET mission_id = $trigger[mission_id], player_id=$playerinfo[player_id], state=0, completed='N', turns = $turnlimit");
				// ******** INCLUDE MISSION SPECIFIC FILE  *************
				@include("missions/mission".$trigger[mission_id].".php");
				$res = $db->Execute("SELECT * FROM $dbtables[mstatus],$dbtables[missions] WHERE $dbtables[mstatus].state =$dbtables[missions].state AND $dbtables[mstatus].mission_id =$dbtables[missions].mission_id AND player_id=$playerinfo[player_id] AND completed='N' LIMIT 1");
				$newmission = true;
			}
		}
		$res4->MoveNext();
	}
}
if (!$res->EOF) {
	// We are in a mission, get the mission info
	$missioninfo = $res->fields;
	// See if we have run out of turns or not to complete mission
	if ($missioninfo[turns] == -1) {
		$turnsleft = -1;
	} else {
		$turnsleft = $missioninfo[turns] - $playerinfo[turns_used];
	}
	if ($turnsleft < 1 && $missioninfo[turns] != -1) {
		// Oh dear, cancel the mission
		$res = $db->Execute("UPDATE $dbtables[mstatus] SET completed='Y' WHERE player_id=$playerinfo[player_id] AND mission_id=$missioninfo[mission_id] LIMIT 1");
		showmiss("Ran Out Of Time!",0,"You failed the mission because you ran out of time, better luck next time.");
	} else {
		// Check to see what we need to show in or out
		$sql = subvars($missioninfo[sql]);
		$res2 = $db->Execute($sql);
		// Check to see if they have moved to the next state
		$res3 = $db->Execute("SELECT infoin,sql FROM $dbtables[missions] WHERE state=$missioninfo[nextstate] AND mission_id=$missioninfo[mission_id] LIMIT 1");
		$nextCheck = $res3->fields;
		$sql = subvars($nextCheck[sql]);
		//echo "\n\n<!-- Next check = $sql -->\n\n";
		$res4 = $db->Execute($sql);
		// If the next state is -1 then it is executed no matter what and we end the mission
		if (!$res4->EOF || $missioninfo[nextstate] == -1) {
			// They have successfully moved to the next state or it's the end
			$res = $db->Execute("UPDATE $dbtables[mstatus] SET state = $missioninfo[nextstate] WHERE player_id=$playerinfo[player_id] AND mission_id = $missioninfo[mission_id]");
			// Only show a mission box when we are not done
			if ($missioninfo[nextstate] != -1) {
				showmiss("Mission",$turnsleft,$nextCheck[infoin]);
			}
		} else {
			//echo "Not on next state, show out of current info<br>";
			if (!$res2->EOF) {
				showmiss("Mission",$turnsleft,$missioninfo[infoin]);
			} else {
				// Don't show anything until we have got into the mission
				if ($missioninfo[state]!=0) {				
					showmiss("Mission",$turnsleft,$missioninfo[infoout]);
				}
			}
		}
	}
}		
// Mission ended check
if ($missioninfo[nextstate] == -1) {
	$res = $db->Execute("UPDATE $dbtables[mstatus] SET completed='Y' WHERE state=-1 AND player_id=$playerinfo[player_id] AND mission_id = $missioninfo[mission_id]");
}
?>