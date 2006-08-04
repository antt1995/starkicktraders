<?
/*
Plan

1. Check for sector fighters
2. If there are sector fighters find out what type they are and how many
3. If there is a command then act on that command else display the command menu

*/


    if (preg_match("/check_fighters.php/i", $PHP_SELF)) {
        echo "You can not access this file directly!";
        die();
    }

    include("languages/$lang");
	// Check for attack fighters
	$res = $db->Execute("SELECT SUM(quantity) AS total FROM $dbtables[players] LEFT JOIN $dbtables[sector_defence] ON ( $dbtables[players].player_id = $dbtables[sector_defence].player_id ) WHERE sector_id = $sector AND ( team = 0 OR team != $playerinfo[team] ) AND $dbtables[sector_defence].player_id != $playerinfo[player_id] AND defence_type='F' AND fm_setting='attack'");
	if (!$res->EOF) {
		$row = $res->fields;
		if ($row[total] > 0) {
			$attack_fighters = $row[total];
			$l_chf_therearetotalfightersindest = str_replace("[chf_total_sector_fighters]", NUMBER($row[total]), $l_chf_therearetotalfightersindest);
		}
	}
	// Check for toll fighters
	$res = $db->Execute("SELECT SUM(quantity) AS total FROM $dbtables[players] LEFT JOIN $dbtables[sector_defence] ON ( $dbtables[players].player_id = $dbtables[sector_defence].player_id ) WHERE sector_id = $sector AND ( team = 0 OR team != $playerinfo[team] ) AND $dbtables[sector_defence].player_id != $playerinfo[player_id] AND defence_type='F' AND fm_setting='toll'");
	if (!$res->EOF) {
		$row = $res->fields;
		if ($row[total] > 0) {
			$toll_fighters = $row[total];
			$fighterstoll = $row[total] * $fighter_price * 0.6;
			$l_chf_therearetotalfightersindest = str_replace("[chf_total_sector_fighters]", NUMBER($row[total]), $l_chf_therearetotalfightersindest);
			$l_chf_creditsdemanded = str_replace("[chf_number_fighterstoll]", NUMBER($fighterstoll), $l_chf_creditsdemanded);
		}
	}
	// What shall we do?
	// Attack fighters take precedence.
	if ($attack_fighters > 0) {
		$total_sector_fighters = $attack_fighters;
		echo "\n\n\n<!-- DEBUG: $l_chf_therearetotalfightersindest -->\n\n\n";
	} else {
		$total_sector_fighters = $toll_fighters;
		echo "\n\n\n<!-- DEBUG: $l_chf_therearetotalfightersindest -->\n";
		echo "<!-- DEBUG: $l_chf_creditsdemanded -->\n\n\n";
	}
/*
    $result2 = $db->Execute ("SELECT * FROM $dbtables[universe] WHERE sector_id='$sector'");
    //Put the sector information into the array "sectorinfo"
    $sectorinfo=$result2->fields;
    $result3 = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id='$sector' and defence_type ='F' ORDER BY quantity DESC");
    //Put the defence information into the array "defenceinfo"
    $i = 0;
    $total_sector_fighters = 0;
    $owner = true;
	$toll_pay = false;
    if($result3 > 0)
    {
       while(!$result3->EOF)
       {
          $row = $result3->fields;
          $defences[$i] = $row;
           $total_sector_fighters += $defences[$i]['quantity'];
          if($defences[$i][player_id] != $playerinfo[player_id])
          {
             $owner = false;
          }
          $i++;
          $result3->MoveNext();
       }
    }
    $num_defences = $i;
    if ($num_defences > 0 && $total_sector_fighters > 0 && !$owner)
*/	
	if ($total_sector_fighters > 0)
    {
	/*
        // find out if the fighter owner and player are on the same team
        // All sector defences must be owned by members of the same team
        $fm_owner = $defences[0]['player_id'];
	    $result2 = $db->Execute("SELECT * from $dbtables[players] where player_id=$fm_owner");
        $fighters_owner = $result2->fields;
		// Find out what my ship is
		$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$fighters_owner[player_id] AND ship_id=$fighters_owner[currentship] LIMIT 1");
		$fighters_owner_ship = $res->fields;
        if ($fighters_owner[team] != $playerinfo[team] || $playerinfo[team]==0)
        {
	*/
           switch($response) {
              case "fight":
                 $db->Execute("UPDATE $dbtables[players] SET cleared_defences = ' ' WHERE player_id = $playerinfo[player_id]");
                 //bigtitle();
                 include("sector_fighters.php");

                 break;
              case "retreat":
                 $db->Execute("UPDATE $dbtables[players] SET cleared_defences = ' ' WHERE player_id = $playerinfo[player_id]");
                 $stamp = date("Y-m-d H-i-s");
                 $db->Execute("UPDATE $dbtables[players] SET last_login='$stamp',turns=turns-2, turns_used=turns_used+2, sector=$playerinfo[sector] where player_id=$playerinfo[player_id]");
                 //bigtitle();
                 echo "$l_chf_youretreatback<BR>";
                 TEXT_GOTOMAIN();
                 die();
                 break;
              case "pay":
			  	// Check that this is a valid option
				if ($toll_fighters > 0) {
					//$fighterstoll = $total_sector_fighters * $fighter_price * 0.6;
					if($playerinfo[credits] < $fighterstoll)
					{
					   	echo "$l_chf_notenoughcreditstoll<BR>";
					   	//echo "$l_chf_movefailed<BR>";
					   	// undo the move
					   	//$db->Execute("UPDATE $dbtables[players] SET sector=$playerinfo[sector] where player_id=$playerinfo[player_id]");
					   	$ok=0;
					}
					else
					{
						$tollstring = NUMBER($fighterstoll);
						$l_chf_youpaidsometoll = str_replace("[chf_tollstring]", $tollstring, $l_chf_youpaidsometoll);
						//echo "$l_chf_youpaidsometoll<BR>";
						$db->Execute("UPDATE $dbtables[players] SET credits=credits-$fighterstoll, cleared_defences = '' WHERE player_id=$playerinfo[player_id]");
						distribute_toll($sector,$fighterstoll,$total_sector_fighters);
						playerlog($playerinfo[player_id], LOG_TOLL_PAID, "$tollstring|$sector");
					   	$ok=1;
						$toll_pay=true;
					}
				} else {
					echo "That is not a valid option!<br>";
					$ok=0;
				}
                break;
              case "sneak":
                 {
                    $db->Execute("UPDATE $dbtables[players] SET cleared_defences = ' ' WHERE player_id = $playerinfo[player_id]");
					// Now we need to decide how goot the sensor network is for the fighters. The answer is, it is the AVERAGE of all fighter owners
					$res = $db->Execute("SELECT (SUM(sensors)/COUNT(*)) AS sensor_average FROM $dbtables[ships], $dbtables[players], $dbtables[sector_defence] WHERE ( $dbtables[players].player_id = $dbtables[sector_defence].player_id ) AND ($dbtables[players].currentship = $dbtables[ships].ship_id) AND $dbtables[sector_defence].sector_id = $sector AND ( team = 0 OR team != $playerinfo[team] ) AND $dbtables[sector_defence].player_id != $playerinfo[player_id] AND defence_type='F'");
					$sensor_average = $res->fields;
                    $success = SCAN_SUCCESS($sensor_average[sensor_average], $shipinfo[cloak]);
					echo "<br>DEBUG: $success = SCAN_SUCCESS($sensor_average[sensor_average], $shipinfo[cloak]);<br>";
                    if($success < 5)
                    {
                       $success = 5;
                    }
                    if($success > 95)
                    {
                       $success = 95;
                    }
                    $roll = rand(1, 100);
                    if($roll < $success)
                    {
                        // sector defences detect incoming ship
                        bigtitle();
                        echo "$l_chf_thefightersdetectyou<BR>";
                        include("sector_fighters.php");
                        break;
                    }
                    else
                    {
                       // sector defences don't detect incoming ship
                       $ok=1;
                    }
                 }
                 break;
			  case "sectorwmd":
				 {
				 	if ($shipinfo[dev_sectorwmd] == 'Y' && $shipinfo[dev_genesis] > 0) {
						$db->Execute("UPDATE $dbtables[players] SET cleared_defences = ' ' WHERE player_id = $playerinfo[player_id]");
						$ok = 1;
						$db->Execute("UPDATE $dbtables[ships] SET dev_genesis=dev_genesis-1 WHERE player_id = $playerinfo[player_id] AND ship_id = $playerinfo[currentship]");
						// Destroy fighters
						$fighterslost = $total_sector_fighters;
						$l_sf_sendlog = str_replace("[player]", $playerinfo[character_name], $l_sf_sendlog);
						$l_sf_sendlog = str_replace("[lost]", NUMBER($fighterslost), $l_sf_sendlog);
						$l_sf_sendlog = str_replace("[sector]", $sector, $l_sf_sendlog);
						echo "<h1>Sector WMD Attack</h1>";
						echo "You obliterate ".NUMBER($fighterslost)." sector defense fighters!<br>";
						destroy_fighters($sector,$fighterslost);
						message_defence_owner($sector,$l_sf_sendlog);
						playerlog($playerinfo[player_id], LOG_DEFS_DESTROYED_F, NUMBER($fighterslost)."|$sector");
					} else {
						$ok = 0;
					}
				}
				break;
              default:
                 $interface_string = $calledfrom . '?sector='.$sector.'&destination='.$destination.'&engage='.$engage;
                 $db->Execute("UPDATE $dbtables[players] SET cleared_defences = '$interface_string' WHERE player_id = $playerinfo[player_id]");
                 //$fighterstoll = $total_sector_fighters * $fighter_price * 0.6;
                 //bigtitle();
				 $ok=0;
				 //echo "<!-- DEBUG2: OK=$ok-->";
                 echo "<FORM ACTION=$calledfrom METHOD=POST>";
                 //$l_chf_therearetotalfightersindest = str_replace("[chf_total_sector_fighters]", NUMBER($total_sector_fighters), $l_chf_therearetotalfightersindest);
                 echo "$l_chf_therearetotalfightersindest<br>";
                 if($toll_fighters > 0)
                 {
                    //$l_chf_creditsdemanded = str_replace("[chf_number_fighterstoll]", NUMBER($fighterstoll), $l_chf_creditsdemanded);
                    echo "$l_chf_creditsdemanded<BR>";
				    if($playerinfo[credits] < $fighterstoll)
                 	{
					 	echo "You do not have enough money to pay the toll!<br>";
				 	}
                 }
                 echo "$l_chf_youcanretreat";
                 echo "$l_chf_inputfight";
				 if ($shipinfo[dev_sectorwmd] == 'Y' && $shipinfo[dev_genesis] > 0) {
				 	echo "<INPUT TYPE=RADIO NAME=response CHECKED VALUE=sectorwmd><B>Use Sector-WMD</B> - Smart Bomb the Sector Fighters with a Genesis Torpedo.<BR></INPUT>";
				 }
                 echo "$l_chf_inputcloak";
				 if($toll_fighters>0 && $playerinfo[credits] >= $fighterstoll)
                 {
                    echo "$l_chf_inputpay";
                 }
				 echo "<br>";
                 echo "<INPUT TYPE=SUBMIT VALUE=$l_chf_go><BR><BR>";
                 echo "<input type=hidden name=sector value=$sector>";
                 echo "<input type=hidden name=engage value=1>";
                 echo "<input type=hidden name=destination value=$destination>";
                 echo "</FORM>";
                 die();
                 break;
            }


           // clean up any sectors that have used up all mines or fighters
           $db->Execute("delete from $dbtables[sector_defence] where quantity <= 0 ");
        //}

    }

?>
