<?

  if (preg_match("/sched_tow.php/i", $PHP_SELF)) {
      echo "You can not access this file directly!";
      die();
  }

  echo "<B>ZONES</B><BR><BR>";
  echo "Towing bigger players out of restricted zones...";
  $num_to_tow = 0;
$res = $db->Execute("SELECT $dbtables[players].player_id,character_name,hull,$dbtables[ships].sector,$dbtables[universe].zone_id,max_hull,tow,ship_id FROM $dbtables[players],$dbtables[universe],$dbtables[zones],$dbtables[ships] WHERE $dbtables[ships].sector=sector_id AND $dbtables[universe].zone_id=$dbtables[zones].zone_id AND max_hull<>0 AND (($dbtables[ships].hull + $dbtables[ships].engines + $dbtables[ships].power + $dbtables[ships].computer + $dbtables[ships].sensors + $dbtables[ships].beams + $dbtables[ships].torp_launchers + $dbtables[ships].shields + $dbtables[ships].armour + $dbtables[ships].cloak)/10) >max_hull AND ship_destroyed='N' AND ship_id=currentship");
$num_to_tow = $res->RecordCount();
echo "<BR>$num_to_tow players to tow:<BR>";
      while(!$res->EOF)
      {
        $row = $res->fields;
		if ($row[player_id] > 1) {
			echo "...towing $row[character_name] out of $row[sector] ...";
			$newsector = rand(0, $sector_max);
			echo " to sector $newsector.<BR>";
			echo "Moving player<br>";
			//echo "UPDATE $dbtables[players] SET sector=$newsector,cleared_defences=' ' where player_id=$row[player_id]<br>";
			$query = $db->Execute("UPDATE $dbtables[players] SET sector=$newsector,cleared_defences=' ' where player_id=$row[player_id]");
			echo "Moving ship<br>";
			//echo "UPDATE $dbtables[ships] SET sector=$newsector where ship_id=$row[ship_id]<br>";
			$query = $db->Execute("UPDATE $dbtables[ships] SET sector=$newsector where ship_id=$row[ship_id]");
			if ($row[tow] > 0) {
				echo "Moving towed ship<br>";
				//echo "UPDATE $dbtables[ships] SET sector=$newsector where ship_id=$row[tow]";
				$query = $db->Execute("UPDATE $dbtables[ships] SET sector=$newsector where ship_id=$row[tow]");
			}
			playerlog($row[player_id], LOG_TOW, "$row[sector]|$newsector|$row[max_hull]");
			log_move($row[player_id],$newsector);
		}
        $res->MoveNext();
      }
  echo "<BR>";
  
  $multiplier = 0; //no use to run this again
?>
