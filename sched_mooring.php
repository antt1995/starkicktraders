<?
  if (preg_match("/sched_mooring.php/i", $PHP_SELF)) {
      echo "You can not access this file directly!";
      die();
  }
  echo "<br><br><B>Space Dock Mooring Fees</B><BR><BR>";
  $temp_time = time();
  echo "Charging fees and taking ships that don't pay...<br>";
  $res = $db->Execute("SELECT ship_id,ship_name,player_id,sector FROM $dbtables[ships],$dbtables[universe] WHERE sector=$dbtables[universe].sector_id AND port_type='special' AND $dbtables[ships].on_planet='Y' AND $dbtables[ships].player_id!=1 AND $dbtables[ships].ship_destroyed='N'");
  while (!$res->EOF) {
  	$parked = $res->fields;
	// Check to make sure that this is not just a ship that is being towed
	$res3 = $db->Execute("SELECT tow FROM $dbtables[ships] WHERE player_id=$parked[player_id] AND tow=$parked[ship_id]");
	if ($res3->EOF) {
	  $res4=$db->Execute("SELECT * FROM $dbtables[players] WHERE player_id=$parked[player_id] AND currentship=$parked[ship_id]");
	  if ($res4->EOF) {
		// Get bank account of those who are parked
		$res2 = $db->Execute("SELECT balance FROM $dbtables[ibank_accounts] WHERE player_id=$parked[player_id] ");
		$row = $res2->fields;
		$row[balance] -= $mooringFee;
		if ($row[balance] <0) {
			echo "We are taking a ship of player $parked[player_id]!<br>";
			$db->Execute("UPDATE $dbtables[ibank_accounts] SET balance=0, loantime=loantime WHERE player_id=$parked[player_id]");
			$db->Execute("UPDATE $dbtables[ships] SET player_id=1 WHERE ship_id=$parked[ship_id]");
			echo "The Federation took your ship $parked[ship_name] for failure to pay the space dock mooring fee.<br>";
			playerlog($parked[player_id], LOG_RAW, "The Federation took your ship $parked[ship_name] for failure to pay the space dock mooring fee.");
			gen_score($parked[player_id]);
		} else {
			echo "Deducting $mooringFee credits from $parked[player_id] player<br>";
			//echo "UPDATE $dbtables[ibank_accounts] SET balance=balance-$mooringFee, loantime=loantime WHERE player_id=$parked[player_id]<br>";
			$db->Execute("UPDATE $dbtables[ibank_accounts] SET balance=balance-$mooringFee, loantime=loantime WHERE player_id=$parked[player_id]");
		}
		if ($row[balance] < 10000) {
			// Send out warning
			echo "Your IGB account is running dangerously low and your ship $parked[ship_name] is in danger of being taken by the Federation for failure to pay the Sector $parked[sector] space dock fee!<br>";
			playerlog($parked[player_id], LOG_RAW, "Your IGB account is running dangerously low and your ship $parked[ship_name] is in danger of being taken by the Federation for failure to pay the Sector $parked[sector] space dock fee! You only have $row[balance] credits left in your IGB account.");
		}
	   }
	}
    $res->MoveNext();
  }
echo "<BR>";
   $temp_runtime= time() - $temp_time;
  echo "<p>Mooring took $temp_runtime seconds to execute.<p>";
// Check for lost ships

$res = $db->Execute("SELECT ship_id,ship_name,player_id,sector FROM $dbtables[ships], $dbtables[universe] WHERE sector=sector_id AND port_type!='special' AND $dbtables[ships].on_planet='Y' AND $dbtables[ships].planet_id = '0' AND $dbtables[ships].ship_destroyed='N' AND player_id > 1");
  while (!$res->EOF) {
  		$lost = $res->fields;
		// Find out if the ship is just being towed or not
		$res2 = $db->Execute("SELECT ship_id,sector FROM $dbtables[ships] WHERE tow=$lost[ship_id]");
		if ($res2->RowCount() == 0) {
			playerlog($lost[player_id], LOG_RAW, "The Federation found your ship $lost[ship_name] floating sub-space and moved it to the Sector 0 spacedock.");
			echo "The Federation ship $lost[ship_name] ID#$lost[ship_id] floating in sector $lost[sector] and moved it to the Sector 0 spacedock.<br>";
			$update=$db->Execute("UPDATE $dbtables[ships] SET sector='0' WHERE ship_id='$lost[ship_id]' LIMIT 1");
		}
  $res->MoveNext();
}

$multiplier = 0; //no use to run this again
?>
