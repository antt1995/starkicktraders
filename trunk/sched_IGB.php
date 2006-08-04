<?

  if (preg_match("/sched_IGB.php/i", $PHP_SELF)) {
      echo "You can not access this file directly!";
      die();
  }
  if (empty($multiplier)) {
  	$multiplier = 1;
  } 
  $exponinter = mypw($ibank_interest + 1, $multiplier);
  $expoloan = mypw($ibank_loaninterest + 1, $multiplier);

  //echo "<B>IBANK</B><p>";
  $igb_time=time();

  //echo "CHECKING FOR DELINQUENT LOANS<BR>";
  // Start to collect on loans
  $IGB_crate = 2880;
  $ctime=time(); // Debt collection time
  $res = $db->Execute("SELECT player_id, balance, loan, UNIX_TIMESTAMP(loantime) as epoch FROM $dbtables[ibank_accounts] WHERE loan>0");
  while(!$res->EOF)
  {
    $account=$res->fields;
	$difftime = ($ctime - $account[epoch])/60;
	//echo "Loan unpaid for $difftime minutes<br>";
	if ( $difftime > $IGB_crate) {
		//echo "Delinquent loan: $account[player_id] owes ".NUMBER($account[loan])."<br>";
		$res2 = $db->Execute("SELECT * from $dbtables[players] WHERE player_id=$account[player_id]");
		$playerinfo = $res2->fields;
		$logentry = "";
		$newsentry = "";
		// Pay off from account
		//echo "Try to pay off from IGB account. $playerinfo[character_name] had ".NUMBER($account[balance])."<br>";
		$account[loan] -= $account[balance];
		$account[balance] = 0;
		//echo "Loan is now $account[loan]<br>";
		if ($account[loan] > 0) {
			if ($playerDeath == true) {
				$logentry = $logentry."The IGB collected on its loan as you died before you could pay it off. It was ".NUMBER($account[loan])." credits.";
			} else {
				$logentry = "A massive ship appears in your sector with the letters IGB written on the side. As a blue laser beam sweeps the cabin you hear a booming voice say: 'The IGB is collecting on our loan to you which stood at ".NUMBER($account[loan])." credits.";
			}
			// Try to pay off by liquidating planets
			//echo "Try to pay off by liquidating planets<br>";
			$num_planets = 0;
			$calc_planet_goods = "organics*$organics_price+ore*$ore_price+goods*$goods_price+energy*$energy_price";
			$calc_planet_colonists = "colonists*$colonist_price";
			$calc_planet_defence = "fighters*$fighter_price+IF($dbtables[planets].base='Y', $base_credits+torps*$torpedo_price, 0)";
			$calc_planet_credits = "credits";
			$sql = "SELECT planet_id, name, sector_id,$calc_planet_goods+$calc_planet_colonists+$calc_planet_defence+$calc_planet_credits AS score FROM $dbtables[planets] WHERE owner=$account[player_id] ORDER BY score ASC";
			//echo $sql."<br>";
			$res3 = $db->Execute($sql);
			$planetlist = "";
			while (!$res3->EOF && $account[loan] > 0) {
			 	$row = $res3->fields;
				$account[loan] -= $row[score];
				$num_planets++;
				//echo "Planet #$num_planets ID#$row[planet_id] had a value of ".NUMBER($row[score])." credits<br>";
				//echo "Loan is now ".NUMBER($account[loan])."<br>";
				if ($row[name] == "") {
					$row[name] = "an unnamed planet";
				}
				$planetlist = $planetlist." ".$row[name]." in sector ".$row[sector_id].", ";
				// Destroy planet
				//echo "Liquidating Planet#$row[planet_id] ";
				$db->Execute("DELETE FROM $dbtables[planets] WHERE planet_id = $row[planet_id]");
				$res3->MoveNext();
			}
			if ($num_planets>0) {
				if ($num_planets == 1) {
					$logentry = $logentry." To do so we liquidated $planetlist.";
					$newsentry = "The IGB liquidated one of $playerinfo[character_name]'s planets to pay off a debt";
				} else {
					$logentry = $logentry." To do so we liquidated $planetlist for a grand total of $num_planets planets in all.'\n";
					$newsentry = "The IGB liquidated $num_planets of $playerinfo[character_name]'s planets to pay off a debt";
				}
				// Log the planet destruction and issue a news bulletin
				//echo "Destroyed $num_planets<br>";
			}	 			
			if ($account[loan] > 0) {
				if ($playerDeath == true) {
					//$res2=$db->Execute("UPDATE $dbtables[players] SET credits=0 WHERE player_id=$account[player_id]");
					$logentry = $logentry." 'Unfortunately, dying with a loan incurs a 500 turn penalty.\n";
				} else {
					$logentry = $logentry." 'Unfortunately, your IGB account and planet portfolio were not enough to repay the debt so we had to come to you, in person.' You really wish that they had not come and you try to remember how many credits you have stashed on your ship....\n";
					$newsentry = $newsentry." Unfortunately, the IGB had to visit $playerinfo[character_name] in person (500 turn penalty).";
				}
				$res2=$db->Execute("UPDATE $dbtables[players] SET credits=0, turns = turns-500, turns_used=turns_used+500 WHERE player_id=$account[player_id]");					
				// All planets liquidated now we move on to the ship
				//echo "All planets liquidated now we move on to the ship<br>";
				//echo "Loan is now ".NUMBER($account[loan])."<br>";
				// Get the credits off the ship (last chance)
				//echo "Get the credits off the ship (last chance)<br>";
				//echo "Ship had ".NUMBER($playerinfo[credits])." credits<br>";
				$account[loan] -= $playerinfo[credits];
				//echo "Loan is now ".NUMBER($account[loan])."<br>";
				
				if ($account[loan]>0) {
					  $calc_hull = "ROUND(pow($upgrade_factor,hull))";
					  $calc_engines = "ROUND(pow($upgrade_factor,engines))";
					  $calc_power = "ROUND(pow($upgrade_factor,power))";
					  $calc_computer = "ROUND(pow($upgrade_factor,computer))";
					  $calc_sensors = "ROUND(pow($upgrade_factor,sensors))";
					  $calc_beams = "ROUND(pow($upgrade_factor,beams))";
					  $calc_torp_launchers = "ROUND(pow($upgrade_factor,torp_launchers))";
					  $calc_shields = "ROUND(pow($upgrade_factor,shields))";
					  $calc_armour = "ROUND(pow($upgrade_factor,armour))";
					  $calc_cloak = "ROUND(pow($upgrade_factor,cloak))";
					  $calc_levels = "($calc_hull+$calc_engines+$calc_power+$calc_computer+$calc_sensors+$calc_beams+$calc_torp_launchers+$calc_shields+$calc_armour+$calc_cloak)*$upgrade_cost";
					
					  $calc_torps = "torps*$torpedo_price";
					  $calc_armour_pts = "armour_pts*$armour_price";
					  $calc_ship_ore = "ship_ore*$ore_price";
					  $calc_ship_organics = "ship_organics*$organics_price";
					  $calc_ship_goods = "ship_goods*$goods_price";
					  $calc_ship_energy = "ship_energy*$energy_price";
					  $calc_ship_colonists = "ship_colonists*$colonist_price";
					  $calc_ship_fighters = "ship_fighters*$fighter_price";
					  $calc_equip = "$calc_torps+$calc_armour_pts+$calc_ship_ore+$calc_ship_organics+$calc_ship_goods+$calc_ship_energy+$calc_ship_colonists+$calc_ship_fighters";
					
					  $calc_dev_warpedit = "dev_warpedit*$dev_warpedit_price";
					  $calc_dev_genesis = "dev_genesis*$dev_genesis_price";
					  $calc_dev_beacon = "dev_beacon*$dev_beacon_price";
					  $calc_dev_emerwarp = "dev_emerwarp*$dev_emerwarp_price";
					  $calc_dev_escapepod = "IF(dev_escapepod='Y', $dev_escapepod_price, 0)";
					  $calc_dev_fuelscoop = "IF(dev_fuelscoop='Y', $dev_fuelscoop_price, 0)";
					  $calc_dev_lssd = "IF(dev_lssd='Y', $dev_lssd_price, 0)";
					  $calc_dev_minedeflector = "dev_minedeflector*$dev_minedeflector_price";
					  $calc_dev = "$calc_dev_warpedit+$calc_dev_genesis+$calc_dev_beacon+$calc_dev_emerwarp+$calc_dev_escapepod+$calc_dev_fuelscoop+$calc_dev_minedeflector+$calc_dev_lssd";
					  $crushres = $db->Execute("SELECT ship_id,player_id,ship_name,($calc_levels+$calc_equip+$calc_dev) AS shipvalue FROM $dbtables[ships] WHERE player_id=$account[player_id] AND ship_destroyed='N' ORDER BY shipvalue DESC");
					////echo "DEBUG: SELECT player_id,ship_name,($calc_levels+$calc_equip+$calc_dev) AS shipvalue FROM $dbtables[ships] WHERE player_id=$account[player_id] AND ship_destroyed='N' ORDER BY shipvalue ASC<br>";
					  $deathFlag = false;
					  while (!$crushres->EOF && $account[loan] > 0) {
							// Oh dear your ship is crushed for scrap - log that can issue news bulletin
							$shipinfo = $crushres->fields;
							//echo "Oh dear a ship was crushed for scrap<br>";
							//echo"Ship value = ".NUMBER($shipinfo[shipvalue])."<br>";
							$account[loan] -= $shipinfo[shipvalue];
							//echo "Loan is now ".NUMBER($account[loan])."<br>";
							//echo "Crushing ship...";
							if ($playerinfo[currentship] == $shipinfo[ship_id]) {
								// The player is now dead
								$deathFlag = true;
								$logentry = $logentry."The booming voice speaks again: 'It seems that you do not have enough credits to repay the loan. This is a big disappointment $playerinfo[character_name]. Unfortunately, this means that we will have to repo this ship immediately!'\nYou feel the buzz of a transport beam and find yourself suddenly floating in space, without a spacesuit!\n";
								$newsentry = $newsentry." Sadly, $playerinfo[character_name] got left floating in space without a ship and spacesuit!";
							} else {
								$newsentry = $newsentry." They repossessed the $shipinfo[ship_name].";
								//echo "UPDATE $dbtables[ships] SET player_id=1 WHERE player_id=$shipinfo[player_id] AND ship_id=$shipinfo[ship_id] LIMIT 1<br>";
								$db->Execute("UPDATE $dbtables[ships] SET player_id=1, sector=0, ship_destroyed='Y', on_planet='N', tow=0, planet_id=0 WHERE ship_id=$shipinfo[ship_id] LIMIT 1");
								$db->Execute("UPDATE $dbtables[ships] SET tow=0 WHERE tow=$shipinfo[ship_id] LIMIT 1");
							}
							$crushres->MoveNext();
						}
					if ($deathFlag == true) {
						db_kill_player($account[player_id],$shipinfo[ship_id],-1); // IGB is player -1.
					}
				} else {
					// Log that all your credits were used to pay off loan
					//echo "Loan paid off using credits on ship (phew!)<br>";
					if ($playerDeath != true) {
						$logentry = $logentry."The booming voice speaks again: 'Excellent! We see that you have enough credits to repay the loan. Now why did you make us come all this way?'\n";
						$newsentry = $newsentry.". Thankfully, $playerinfo[character_name] was able to pay them off - phew!";
					}
				}
				//$newsentry = $newsentry.".";				
			} else {
				$logentry = $logentry." The huge ship then vanishes!";
			}
		} else {
			$logentry = "The IGB automatically paid off your loan with your IGB balance.";
		}
		//echo "<hr>LOG ENTRY = $logentry<br>News Entry = $newsentry<br><hr>";
		if ($logentry != "") {
			//echo "Entering log for $playerinfo[player_id] name $playerinfo[character_name]<br>";
			$logentry = addslashes($logentry);
			playerlog($playerinfo[player_id],LOG_RAW,$logentry);
		}
		if ($newsentry != "") {
			$headline = addslashes("IGB takes action on deadbeat ".$playerinfo[character_name]);
			$player_id = $playerinfo[player_id];
			$newstext=addslashes($newsentry);
			$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'IGB')");
		}
		//echo "Loan is now ".NUMBER($account[loan])."<br>";					
		$account[balance] = -$account[loan]; // Put back the difference
		$account[loan] = 0;
		if ($account[balance]<0) {
			$account[balance] = 0;
		}
		//echo "Balance is now ".NUMBER($account[balance])."<br>";
		//echo "Setting bank account. Balance =".NUMBER($account[balance])."  Loan=".NUMBER($account[loan])."....";
		$db->Execute("UPDATE $dbtables[ibank_accounts] SET loan=$account[loan],balance=$account[balance] WHERE player_id=$account[player_id]");	
	} // End delinquent loan check
	$res->MoveNext();
  }
   //echo "Calculating interest on all loans...";
   echo"<!-- DEBUG: balance=balance * $exponinter, loan=loan * $expoloan -->";
   $db->Execute("UPDATE $dbtables[ibank_accounts] SET balance=balance * $exponinter, loan=loan * $expoloan, loantime=loantime");
  //echo "All IGB accounts updated ($multiplier times).<p>";
  $igb_runtime= time() - $igb_time;
  //echo "<p>The IGB took $igb_runtime seconds to execute.<p>";

  $multiplier = 0;
?>
