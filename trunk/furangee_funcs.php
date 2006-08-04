<?
function cleanupFur()
{
	global $db,$dbtables;
	// Clean up furangee
	/*
	$res = $db->Execute("SELECT * FROM $dbtables[players],$dbtables[ships] WHERE email LIKE '%furangee' AND ship_id=currentship AND ship_destroyed='Y'") or die(mysql_error());
	while (!$res->EOF) {
		$row = $res->fields;
		echo "Deleting Furangee = $row[character_name]<br>";
		$db->Execute("DELETE FROM $dbtables[furangee] WHERE furangee_id='$row[email]' LIMIT 1") or die(mysql_error());
		$db->Execute("DELETE FROM $dbtables[players] WHERE player_id=$row[player_id] LIMIT 1") or die(mysql_error());
		$db->Execute("DELETE FROM $dbtables[ships] WHERE ship_id=$row[ship_id] LIMIT 1") or die(mysql_error());
		$res->MoveNext();
	}
	*/
	$res=$db->Execute("SELECT COUNT(*) AS FNUM FROM $dbtables[ships] WHERE ship_name LIKE '%furangee%' AND ship_destroyed='N'");
    $row=$res->fields;
	// Return how many there are active
	return $row[FNUM];
}

function createFur($orders)
{
	global $db,$dbtables,$sector_max;
      // Create A New Furangee
     // Create Furangee Name
	$nametry = 1;
	$namecheck = false;
	while ((!$namecheck) and ($nametry <= 9)) {
		$Sylable1 = array("Ak","Al","Ar","B","Br","D","F","Fr","G","Gr","K","Kr","N","Ol","Om","P","Qu","R","S","Z");
		$Sylable2 = array("a","ar","aka","aza","e","el","i","in","int","ili","ish","ido","ir","o","oi","or","os","ov","u","un");
		$Sylable3 = array("ag","al","ak","ba","dar","g","ga","k","ka","kar","kil","l","n","nt","ol","r","s","ta","til","x");
		$sy1roll = rand(0,19);
    	$sy2roll = rand(0,19);
		$sy3roll = rand(0,19);
		$character = $Sylable1[$sy1roll] . $Sylable2[$sy2roll] . $Sylable3[$sy3roll];
		$emailname = str_replace(" ","_",$character) . "@furangee";
		$resultnm = $db->Execute ("select email from $dbtables[players] where email='$emailname'");
		if (!$resultnm->EOF) {
			echo "Email exists for $character [$emailname]<br>";
			$nametry++;
		} else {
			$namecheck=true;
		}
	}
	if ($namecheck == false) {
		echo "Failed to create Furangee!<br>";
		return;
	}	
	// Create Ship Name
	$shipname = "Furangee- " . $character; 
	// Select Random Sector
	$sector = rand(1,$sector_max);
	$furLevelArray = array (3,3,3,3,3,3,4,4,4,4,4,4,5,5,5,5,5,5,6,6,6,6,6,6,7,7,7,7,7,7,8,8,8,8,8,8,8,9,9,9,9,9,9,9,9,9,9,10,10,10,10,10,10,10,10,10,10,11,11,11,11,11,11,11,11,11,11,11,12,12,12,12,12,12,12,12,12,12,12,12,12,13,13,13,13,13,13,13,13,13,13,13,13,13,14,14,14,14,14,14,14,14,14,14,14,14,15,15,15,15,15,15,15,15,15,15,15,15,16,16,16,16,16,16,16,16,16,16,16,17,17,17,17,17,17,17,17,17,17,17,18,18,18,18,18,18,18,18,19,19,19,19,19,19,20,21,22,23,24,25,26,27,28,29,30);
	$furlevel = $furLevelArray[rand(0,(count($furLevelArray)-1))];
    $active="on";
	$aggression=0; // 0 = Peaceful 1 = Attack Sometimes 2=Attack Always
	$makepass="jhsdf34asdfasl$!@";
   	$maxenergy = NUM_ENERGY($furlevel);
    $maxarmour = NUM_ARMOUR($furlevel);
    $maxfighters = NUM_FIGHTERS($furlevel);
    $maxtorps = NUM_TORPEDOES($furlevel);
    $maxcloak = min(22,$furlevel);
    $stamp=date("Y-m-d H:i:s");
	// *****************************************************************************
	// *** ADD FURANGEE RECORD TO ships TABLE ... MODIFY IF ships SCHEMA CHANGES ***
	// *****************************************************************************
	$result2 = $db->Execute("INSERT INTO $dbtables[players] (`player_id`, `character_name`, `password`, `email`,  `credits`, `sector`,  `on_planet`,`turns_used`, `last_login`, `rating`, `score`, `team`, `team_invite`, `interface`, `ip_address`, `planet_id`, `preset1`, `preset2`, `preset3`, `trade_colonists`, `trade_fighters`, `trade_torps`, `trade_energy`, `cleared_defences`, `lang`, `alerts`,  `alert2`, `subscribed`, `ore_price`, `organics_price`, `goods_price`, `energy_price`, `currentship`,`preset4`,`preset5`,`preset6`) VALUES ('', '$character', '$makepass', '$emailname','10000', '$sector','N', '3000', '$stamp', '0', '0', '0', '0', 'N', '127.0.0.1', '0', '0', '0', '0', 'N', 'N', 'N', 'N', NULL, '$default_lang', 'N', 'N', NULL, '0', '0', '0', '0', '1',0,0,0)");
	$res = $db->Execute("SELECT player_id from $dbtables[players] WHERE email='$emailname'");
  	$player_id = $res->fields[player_id]; 
	$shiptype=20; // We have a special furangee ship now.
	if ($furlevel > 16) {
		$ewd = 1; // Only one
	} else {
		$ewd = 0;
	}
	$result3 = $db->Execute("INSERT INTO $dbtables[ships] (`ship_id`, `player_id`, `type`, `ship_name`, `ship_destroyed`, `hull`, `engines`, `power`, `computer`, `sensors`, `beams`, `torp_launchers`, `torps`, `shields`, `armour`, `armour_pts`, `cloak`, `sector`, `ship_ore`, `ship_organics`, `ship_goods`, `ship_energy`, `ship_colonists`, `ship_fighters`, `tow`, `on_planet`, `dev_warpedit`, `dev_genesis`, `dev_beacon`, `dev_emerwarp`, `dev_escapepod`, `dev_fuelscoop`, `dev_minedeflector`, `planet_id`, `cleared_defences`, `dev_lssd`, `dev_sectorwmd`) VALUES ('', $player_id, '$shiptype', '$shipname', 'N', $furlevel+3,$furlevel,$furlevel-1,$furlevel,$furlevel-3,$furlevel,$furlevel,$maxtorps,$furlevel,$furlevel,$maxarmour,$maxcloak, $sector,0,0,0,$maxenergy,0,$maxfighters, '0', 'N', '0', '0', '0', '$ewd', 'N', 'N', '0', '0', NULL, 'N', 'N')"); 
    $result4 = $db->Execute("UPDATE $dbtables[players] SET currentship=LAST_INSERT_ID() WHERE player_id=$player_id");
    if(!$result2 | !result3) {
       	echo $db->ErrorMsg() . "<br>";
    } else {
       	echo "Level $furlevel Furangee has been created.<BR>";
		// Choose a preferance
		$commods = array("ore","goods","organics");
		$prefer = $commods[rand(0,2)];
   		$result3 = $db->Execute("INSERT INTO $dbtables[furangee] (furangee_id,active,aggression,orders,prefer) VALUES('$emailname','Y','$aggression','$orders','$prefer')");
   		if(!$result3) {
    	   	echo $db->ErrorMsg() . "<br>";
  		} else {
    	  	echo "$emailname with orders $orders<br>";
   		}
	}
}

function furangeetoship($player_id)
{
  // *********************************
  // *** SETUP GENERAL VARIABLES  ****
  // *********************************
  global $attackerbeams;
  global $attackerfighters;
  global $attackershields;
  global $attackertorps;
  global $attackerarmor;
  global $attackertorpdamage;
  global $start_energy;
  global $playerinfo;
  global $shipinfo;
  global $rating_combat_factor;
  global $upgrade_cost;
  global $upgrade_factor;
  global $sector_max;
  global $furangeeisdead;
  global $db, $dbtables;

  // *********************************
  // *** LOOKUP TARGET DETAILS    ****
  // *********************************
  //$db->Execute("LOCK TABLES $dbtables[players] WRITE, $dbtables[ships] WRITE, $dbtables[universe] WRITE, $dbtables[zones] READ, $dbtables[planets] READ, $dbtables[news] WRITE, $dbtables[logs] WRITE");
  $resultt = $db->Execute ("SELECT * FROM $dbtables[players] WHERE player_id='$player_id'");
  $targetinfo=$resultt->fields;
  $res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$targetinfo[player_id] AND ship_id=$targetinfo[currentship] LIMIT 1");
  $targetshipinfo = $res->fields;
  // *********************************
  // ** VERIFY SECTOR ALLOWS ATTACK **
  // *********************************
  $sectres = $db->Execute ("SELECT sector_id,zone_id FROM $dbtables[universe] WHERE sector_id='$targetinfo[sector]'");
  $sectrow = $sectres->fields;
  $zoneres = $db->Execute ("SELECT zone_id,allow_attack FROM $dbtables[zones] WHERE zone_id=$sectrow[zone_id]");
  $zonerow = $zoneres->fields;
  if ($zonerow[allow_attack]=="N")                        //*** DEST LINK MUST ALLOW ATTACKING ***
  {
    playerlog($playerinfo[player_id], LOG_RAW, "Attack failed, you are in a sector that prohibits attacks."); 
    return;
  }

  // *********************************
  // *** USE EMERGENCY WARP DEVICE ***
  // *********************************
  		// news
		$headline="Furangee Attacks $targetinfo[character_name]!";
		$newstext=$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." attacked ".$targetinfo[character_name]." in a vicious attempt to infuriate the Federation!";
		$player_id = $playerinfo[player_id];
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");

  if ($targetshipinfo[dev_emerwarp]>0)
  {
    playerlog($targetinfo[player_id], LOG_ATTACK_EWD, "Furangee $playerinfo[character_name]");
    $dest_sector=rand(0,$sector_max);
    $result_warp = $db->Execute ("UPDATE $dbtables[players] SET sector=$dest_sector WHERE player_id=$targetinfo[player_id]");
	$result_warp = $db->Execute ("UPDATE $dbtables[ships] SET sector=$dest_sector, dev_emerwarp=dev_emerwarp-1 WHERE player_id=$targetinfo[player_id] AND ship_id=$targetinfo[currentship]");
    return;
  }

  // *********************************
  // *** SETUP ATTACKER VARIABLES ****
  // *********************************
  $attackerbeams = NUM_BEAMS($shipinfo[beams]);
  if ($attackerbeams > $shipinfo[ship_energy]) $attackerbeams = $shipinfo[ship_energy];
  $shipinfo[ship_energy] = $shipinfo[ship_energy] - $attackerbeams;
  $attackershields = NUM_SHIELDS($shipinfo[shields]);
  if ($attackershields > $shipinfo[ship_energy]) $attackershields = $shipinfo[ship_energy];
  $shipinfo[ship_energy] = $shipinfo[ship_energy] - $attackershields;
  $attackertorps = round(mypw($level_factor, $shipinfo[torp_launchers])) * 2;
  if ($attackertorps > $shipinfo[torps]) $attackertorps = $shipinfo[torps];
  $shipinfo[torps] = $shipinfo[torps] - $attackertorps;
  $attackertorpdamage = $torp_dmg_rate * $attackertorps;
  $attackerarmor = $shipinfo[armour_pts];
  $attackerfighters = $shipinfo[ship_fighters];
  $playerdestroyed = 0;

  // *********************************
  // **** SETUP TARGET VARIABLES *****
  // *********************************
  $targetbeams = NUM_BEAMS($targetshipinfo[beams]);
  if ($targetbeams>$targetshipinfo[energy]) $targetbeams=$targetshipinfo[energy];
  $targetshipinfo[energy]=$targetshipinfo[energy]-$targetbeams;
  $targetshields = NUM_SHIELDS($targetshipinfo[shields]);
  if ($targetshields>$targetshipinfo[energy]) $targetshields=$targetshipinfo[energy];
  $targetshipinfo[energy]=$targetshipinfo[energy]-$targetshields;
  $targettorpnum = round(mypw($level_factor,$targetshipinfo[torp_launchers]))*2;
  if ($targettorpnum > $targetshipinfo[torps]) $targettorpnum = $targetshipinfo[torps];
  $targetshipinfo[torps] = $targetshipinfo[torps] - $targettorpnum;
  $targettorpdmg = $torp_dmg_rate*$targettorpnum;
  $targetarmor = $targetshipinfo[armour_pts];
  $targetfighters = $targetshipinfo[fighters];
  $targetdestroyed = 0;

  // *********************************
  // **** BEGIN COMBAT PROCEDURES ****
  // *********************************
  if($attackerbeams > 0 && $targetfighters > 0)
  {                         //******** ATTACKER HAS BEAMS - TARGET HAS FIGHTERS - BEAMS VS FIGHTERS ********
    if($attackerbeams > round($targetfighters / 2))
    {                                  //****** ATTACKER BEAMS GT HALF TARGET FIGHTERS ******
      $lost = $targetfighters-(round($targetfighters/2));
      $targetfighters = $targetfighters-$lost;                 //**** T LOOSES HALF ALL FIGHTERS ****
      $attackerbeams = $attackerbeams-$lost;                   //**** A LOOSES BEAMS EQ TO HALF T FIGHTERS ****
    } else
    {                                  //****** ATTACKER BEAMS LE HALF TARGET FIGHTERS ******
      $targetfighters = $targetfighters-$attackerbeams;        //**** T LOOSES FIGHTERS EQ TO A BEAMS ****
      $attackerbeams = 0;                                      //**** A LOOSES ALL BEAMS ****
    }   
  }
  if($attackerfighters > 0 && $targetbeams > 0)
  {                         //******** TARGET HAS BEAMS - ATTACKER HAS FIGHTERS - BEAMS VS FIGHTERS ********
    if($targetbeams > round($attackerfighters / 2))
    {                                  //****** TARGET BEAMS GT HALF ATTACKER FIGHTERS ******
      $lost=$attackerfighters-(round($attackerfighters/2));
      $attackerfighters=$attackerfighters-$lost;               //**** A LOOSES HALF ALL FIGHTERS ****
      $targetbeams=$targetbeams-$lost;                         //**** T LOOSES BEAMS EQ TO HALF A FIGHTERS ****
    } else
    {                                  //****** TARGET BEAMS LE HALF ATTACKER FIGHTERS ******
      $attackerfighters=$attackerfighters-$targetbeams;        //**** A LOOSES FIGHTERS EQ TO T BEAMS **** 
      $targetbeams=0;                                          //**** T LOOSES ALL BEAMS ****
    }
  }
  if($attackerbeams > 0)
  {                         //******** ATTACKER HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS SHIELDS ********
    if($attackerbeams > $targetshields)
    {                                  //****** ATTACKER BEAMS GT TARGET SHIELDS ******
      $attackerbeams=$attackerbeams-$targetshields;            //**** A LOOSES BEAMS EQ TO T SHIELDS ****
      $targetshields=0;                                        //**** T LOOSES ALL SHIELDS ****
    } else
    {                                  //****** ATTACKER BEAMS LE TARGET SHIELDS ******
      $targetshields=$targetshields-$attackerbeams;            //**** T LOOSES SHIELDS EQ TO A BEAMS ****
      $attackerbeams=0;                                        //**** A LOOSES ALL BEAMS ****
    }
  }
  if($targetbeams > 0)
  {                         //******** TARGET HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS SHIELDS ********
    if($targetbeams > $attackershields)
    {                                  //****** TARGET BEAMS GT ATTACKER SHIELDS ******
      $targetbeams=$targetbeams-$attackershields;              //**** T LOOSES BEAMS EQ TO A SHIELDS ****
      $attackershields=0;                                      //**** A LOOSES ALL SHIELDS ****
    } else
    {                                  //****** TARGET BEAMS LE ATTACKER SHIELDS ****** 
      $attackershields=$attackershields-$targetbeams;          //**** A LOOSES SHIELDS EQ TO T BEAMS ****
      $targetbeams=0;                                          //**** T LOOSES ALL BEAMS ****
    }
  }
  if($attackerbeams > 0)
  {                         //******** ATTACKER HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS ARMOR ********
    if($attackerbeams > $targetarmor)
    {                                  //****** ATTACKER BEAMS GT TARGET ARMOR ******
      $attackerbeams=$attackerbeams-$targetarmor;              //**** A LOOSES BEAMS EQ TO T ARMOR ****
      $targetarmor=0;                                          //**** T LOOSES ALL ARMOR (T DESTROYED) ****
    } else
    {                                  //****** ATTACKER BEAMS LE TARGET ARMOR ******
      $targetarmor=$targetarmor-$attackerbeams;                //**** T LOOSES ARMORS EQ TO A BEAMS ****
      $attackerbeams=0;                                        //**** A LOOSES ALL BEAMS ****
    } 
  }
  if($targetbeams > 0)
  {                        //******** TARGET HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS ARMOR ******** 
    if($targetbeams > $attackerarmor)
    {                                 //****** TARGET BEAMS GT ATTACKER ARMOR ******
      $targetbeams=$targetbeams-$attackerarmor;                //**** T LOOSES BEAMS EQ TO A ARMOR ****
      $attackerarmor=0;                                        //**** A LOOSES ALL ARMOR (A DESTROYED) ****
    } else
    {                                 //****** TARGET BEAMS LE ATTACKER ARMOR ******
      $attackerarmor=$attackerarmor-$targetbeams;              //**** A LOOSES ARMOR EQ TO T BEAMS ****
      $targetbeams=0;                                          //**** T LOOSES ALL BEAMS ****
    } 
  }
  if($targetfighters > 0 && $attackertorpdamage > 0)
  {                        //******** ATTACKER FIRES TORPS - TARGET HAS FIGHTERS - TORPS VS FIGHTERS ********
    if($attackertorpdamage > round($targetfighters / 2))
    {                                 //****** ATTACKER FIRED TORPS GT HALF TARGET FIGHTERS ******
      $lost=$targetfighters-(round($targetfighters/2));
      $targetfighters=$targetfighters-$lost;                   //**** T LOOSES HALF ALL FIGHTERS ****
      $attackertorpdamage=$attackertorpdamage-$lost;           //**** A LOOSES FIRED TORPS EQ TO HALF T FIGHTERS ****
    } else
    {                                 //****** ATTACKER FIRED TORPS LE HALF TARGET FIGHTERS ******
      $targetfighters=$targetfighters-$attackertorpdamage;     //**** T LOOSES FIGHTERS EQ TO A TORPS FIRED ****
      $attackertorpdamage=0;                                   //**** A LOOSES ALL TORPS FIRED ****
    }
  }
  if($attackerfighters > 0 && $targettorpdmg > 0)
  {                        //******** TARGET FIRES TORPS - ATTACKER HAS FIGHTERS - TORPS VS FIGHTERS ********
    if($targettorpdmg > round($attackerfighters / 2))
    {                                 //****** TARGET FIRED TORPS GT HALF ATTACKER FIGHTERS ******
      $lost=$attackerfighters-(round($attackerfighters/2));
      $attackerfighters=$attackerfighters-$lost;               //**** A LOOSES HALF ALL FIGHTERS ****
      $targettorpdmg=$targettorpdmg-$lost;                     //**** T LOOSES FIRED TORPS EQ TO HALF A FIGHTERS ****
    } else
    {                                 //****** TARGET FIRED TORPS LE HALF ATTACKER FIGHTERS ******
      $attackerfighters=$attackerfighters-$targettorpdmg;      //**** A LOOSES FIGHTERS EQ TO T TORPS FIRED ****
      $targettorpdmg=0;                                        //**** T LOOSES ALL TORPS FIRED ****
    }
  }
  if($attackertorpdamage > 0)
  {                        //******** ATTACKER FIRES TORPS - CONTINUE COMBAT - TORPS VS ARMOR ********
    if($attackertorpdamage > $targetarmor)
    {                                 //****** ATTACKER FIRED TORPS GT HALF TARGET ARMOR ******
      $attackertorpdamage=$attackertorpdamage-$targetarmor;    //**** A LOOSES FIRED TORPS EQ TO T ARMOR ****
      $targetarmor=0;                                          //**** T LOOSES ALL ARMOR (T DESTROYED) ****
    } else
    {                                 //****** ATTACKER FIRED TORPS LE HALF TARGET ARMOR ******
      $targetarmor=$targetarmor-$attackertorpdamage;           //**** T LOOSES ARMOR EQ TO A TORPS FIRED ****
      $attackertorpdamage=0;                                   //**** A LOOSES ALL TORPS FIRED ****
    } 
  }
  if($targettorpdmg > 0)
  {                        //******** TARGET FIRES TORPS - CONTINUE COMBAT - TORPS VS ARMOR ********
    if($targettorpdmg > $attackerarmor)
    {                                 //****** TARGET FIRED TORPS GT HALF ATTACKER ARMOR ******
      $targettorpdmg=$targettorpdmg-$attackerarmor;            //**** T LOOSES FIRED TORPS EQ TO A ARMOR ****
      $attackerarmor=0;                                        //**** A LOOSES ALL ARMOR (A DESTROYED) ****
    } else
    {                                 //****** TARGET FIRED TORPS LE HALF ATTACKER ARMOR ******
      $attackerarmor=$attackerarmor-$targettorpdmg;            //**** A LOOSES ARMOR EQ TO T TORPS FIRED ****
      $targettorpdmg=0;                                        //**** T LOOSES ALL TORPS FIRED ****
    } 
  }
  if($attackerfighters > 0 && $targetfighters > 0)
  {                        //******** ATTACKER HAS FIGHTERS - TARGET HAS FIGHTERS - FIGHTERS VS FIGHTERS ********
    if($attackerfighters > $targetfighters)
    {                                 //****** ATTACKER FIGHTERS GT TARGET FIGHTERS ******
      $temptargfighters=0;                                     //**** T WILL LOOSE ALL FIGHTERS ****
    } else
    {                                 //****** ATTACKER FIGHTERS LE TARGET FIGHTERS ******
      $temptargfighters=$targetfighters-$attackerfighters;     //**** T WILL LOOSE FIGHTERS EQ TO A FIGHTERS ****
    }
    if($targetfighters > $attackerfighters)
    {                                 //****** TARGET FIGHTERS GT ATTACKER FIGHTERS ******
      $tempplayfighters=0;                                     //**** A WILL LOOSE ALL FIGHTERS ****
    } else
    {                                 //****** TARGET FIGHTERS LE ATTACKER FIGHTERS ******
      $tempplayfighters=$attackerfighters-$targetfighters;     //**** A WILL LOOSE FIGHTERS EQ TO T FIGHTERS ****
    }     
    $attackerfighters=$tempplayfighters;
    $targetfighters=$temptargfighters;
  }
  if($attackerfighters > 0)
  {                        //******** ATTACKER HAS FIGHTERS - CONTINUE COMBAT - FIGHTERS VS ARMOR ********
    if($attackerfighters > $targetarmor)
    {                                 //****** ATTACKER FIGHTERS GT TARGET ARMOR ******
      $targetarmor=0;                                          //**** T LOOSES ALL ARMOR (T DESTROYED) ****
    } else
    {                                 //****** ATTACKER FIGHTERS LE TARGET ARMOR ******
      $targetarmor=$targetarmor-$attackerfighters;             //**** T LOOSES ARMOR EQ TO A FIGHTERS **** 
    }
  }
  if($targetfighters > 0)
  {                        //******** TARGET HAS FIGHTERS - CONTINUE COMBAT - FIGHTERS VS ARMOR ********
    if($targetfighters > $attackerarmor)
    {                                 //****** TARGET FIGHTERS GT ATTACKER ARMOR ******
      $attackerarmor=0;                                        //**** A LOOSES ALL ARMOR (A DESTROYED) ****
    } else
    {                                 //****** TARGET FIGHTERS LE ATTACKER ARMOR ******
      $attackerarmor=$attackerarmor-$targetfighters;           //**** A LOOSES ARMOR EQ TO T FIGHTERS ****
    }
  }

  // *********************************
  // **** FIX NEGATIVE VALUE VARS ****
  // *********************************
  if ($attackerfighters < 0) $attackerfighters = 0;
  if ($attackertorps    < 0) $attackertorps = 0;
  if ($attackershields  < 0) $attackershields = 0;
  if ($attackerbeams    < 0) $attackerbeams = 0;
  if ($attackerarmor    < 0) $attackerarmor = 0;
  if ($targetfighters   < 0) $targetfighters = 0;
  if ($targettorpnum    < 0) $targettorpnum = 0;
  if ($targetshields    < 0) $targetshields = 0;
  if ($targetbeams      < 0) $targetbeams = 0;
  if ($targetarmor      < 0) $targetarmor = 0;

  // *********************************
  // *** DEAL WITH DESTROYED SHIPS ***
  // *********************************

  // *********************************
  // *** TARGET SHIP WAS DESTROYED ***
  // *********************************
  if(!$targetarmor>0)
  {
    if($targetshipinfo[dev_escapepod] == "Y")
    // ****** TARGET HAD ESCAPE POD ******
    {
      $rating=round($targetinfo[rating]/2);
      $db->Execute("UPDATE $dbtables[players] SET sector=0, on_planet='N', planet_id=0 WHERE player_id=$targetinfo[player_id]");
	  $db->Execute("UPDATE $dbtables[ships] SET type=1, hull=0, engines=0, power=0, computer=0,sensors=0, beams=0, torp_launchers=0, torps=0, armour=0, armour_pts=100, cloak=0, shields=0, sector=0, ore=0, organics=0, energy=1000, colonists=0, goods=0, fighters=100, on_planet='N', planet_id=0, dev_warpedit=0, dev_genesis=0, dev_beacon=0, dev_emerwarp=0, dev_escapepod='N', dev_fuelscoop='N', dev_minedeflector=0, destroyed='N',dev_lssd='N' WHERE player_id=$targetinfo[player_id] AND ship_id=$targetinfo[currentship]");
      playerlog($targetinfo[player_id], LOG_ATTACK_LOSE, "Furangee $playerinfo[character_name]|Y"); 
    } else
    // ****** TARGET HAD NO POD ******
    {
      playerlog($targetinfo[player_id], LOG_ATTACK_LOSE, "Furangee $playerinfo[character_name]|N"); 
      db_kill_player($targetinfo[player_id],$targetinfo[currentship],$playerinfo[player_id]);
    }   
    if($attackerarmor>0)
    {
      // ****** ATTACKER STILL ALIVE TO SALVAGE TRAGET ******
      $rating_change=round($targetinfo[rating]*$rating_combat_factor);
      $free_ore = round($targetshipinfo[ore]/2);
      $free_organics = round($targetshipinfo[organics]/2);
      $free_goods = round($targetshipinfo[goods]/2);
      $free_holds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[colonists];
      if($free_holds > $free_goods) 
      {                                                        //****** FIGURE OUT WHAT WE CAN CARRY ******
        $salv_goods=$free_goods;
        $free_holds=$free_holds-$free_goods;
      } elseif($free_holds > 0)
      {
        $salv_goods=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_goods=0;
      }
      if($free_holds > $free_ore)
      {
        $salv_ore=$free_ore;
        $free_holds=$free_holds-$free_ore;
      } elseif($free_holds > 0)
      {
        $salv_ore=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_ore=0;
      }
      if($free_holds > $free_organics)
      {
        $salv_organics=$free_organics;
        $free_holds=$free_holds-$free_organics;
      } elseif($free_holds > 0)
      {
        $salv_organics=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_organics=0;
      }
      $ship_value=$upgrade_cost*(round(mypw($upgrade_factor, $targetshipinfo[hull]))+round(mypw($upgrade_factor, $targetshipinfo[engines]))+round(mypw($upgrade_factor, $targetshipinfo[power]))+round(mypw($upgrade_factor, $targetshipinfo[computer]))+round(mypw($upgrade_factor, $targetshipinfo[sensors]))+round(mypw($upgrade_factor, $targetshipinfo[beams]))+round(mypw($upgrade_factor, $targetshipinfo[torp_launchers]))+round(mypw($upgrade_factor, $targetshipinfo[shields]))+round(mypw($upgrade_factor, $targetshipinfo[armor]))+round(mypw($upgrade_factor, $targetshipinfo[cloak])));
      $ship_salvage_rate=rand(10,20);
      $ship_salvage=$ship_value*$ship_salvage_rate/100;
      playerlog($playerinfo[player_id], LOG_RAW, "Attack successful, $targetinfo[character_name] was defeated and salvaged for $ship_salvage credits."); 

      $armor_lost = $shipinfo[armour_pts] - $attackerarmor;
      $fighters_lost = $shipinfo[ship_fighters] - $attackerfighters;
      $energy=$shipinfo[ship_energy];
      $db->Execute ("UPDATE $dbtables[players] SET credits=credits+$ship_salvage WHERE player_id=$playerinfo[player_id]");
      $db->Execute ("UPDATE $dbtables[ships] SET ore=ore+$salv_ore, organics=organics+$salv_organics, goods=goods+$salv_goods, energy=$energy,fighters=fighters-$fighters_lost, torps=torps-$attackertorps,armour_pts=armour_pts-$armor_lost WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    }
  }

  // *********************************
  // *** TARGET AND ATTACKER LIVE  ***
  // *********************************
  if($targetarmor>0 && $attackerarmor>0)
  {
    $rating_change=round($targetinfo[rating]*.1);
    $armor_lost = $shipinfo[armour_pts] - $attackerarmor;
    $fighters_lost = $shipinfo[ship_fighters] - $attackerfighters;
    $energy=$shipinfo[ship_energy];
    $target_rating_change=round($targetinfo[rating]/2);
    $target_armor_lost = $targetshipinfo[armour_pts] - $targetarmor;
    $target_fighters_lost = $targetshipinfo[fighters] - $targetfighters;
    $target_energy=$targetshipinfo[energy];
    playerlog($playerinfo[player_id], LOG_RAW, "Attack failed, $targetinfo[character_name] survived."); 
    playerlog($targetinfo[player_id], LOG_ATTACK_WIN, "Furangee $playerinfo[character_name]|$target_armor_lost|$target_fighters_lost");
    
    $db->Execute ("UPDATE $dbtables[players] SET rating=rating-$target_rating_change WHERE player_id=$targetinfo[player_id]");
	$db->Execute ("UPDATE $dbtables[players] SET rating=rating-$rating_change WHERE player_id=$playerinfo[player_id]");
    $db->Execute ("UPDATE $dbtables[ships] SET energy=$target_energy,fighters=fighters-$target_fighters_lost, armour_pts=armour_pts-$target_armor_lost, torps=torps-$targettorpnum WHERE player_id=$targetinfo[player_id] AND ship_id=$targetinfo[currentship]");
	$db->Execute ("UPDATE $dbtables[ships] SET energy=$energy,fighters=fighters-$fighters_lost, torps=torps-$attackertorps,armour_pts=armour_pts-$armor_lost WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
  }

  // *********************************
  // *** ATTACKER SHIP DESTROYED   ***
  // *********************************
  if(!$attackerarmor>0)
  {
    playerlog($playerinfo[player_id], LOG_RAW, "$targetinfo[character_name] destroyed your ship!"); 
    db_kill_player($playerinfo[player_id],$playerinfo[currentship],$targetinfo[player_id]);
    $furangeeisdead = 1;
    if($targetarmor>0)
    {
      // ****** TARGET STILL ALIVE TO SALVAGE ATTACKER ******
      $rating_change=round($playerinfo[rating]*$rating_combat_factor);
      $free_ore = round($shipinfo[ship_ore]/2);
      $free_organics = round($shipinfo[ship_organics]/2);
      $free_goods = round($shipinfo[ship_goods]/2);
      $free_holds = NUM_HOLDS($targetshipinfo[hull]) - $targetshipinfo[ore] - $targetshipinfo[organics] - $targetshipinfo[goods] - $targetshipinfo[colonists];
      if($free_holds > $free_goods) 
      {                                                        //****** FIGURE OUT WHAT TARGET CAN CARRY ******
        $salv_goods=$free_goods;
        $free_holds=$free_holds-$free_goods;
      } elseif($free_holds > 0)
      {
        $salv_goods=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_goods=0;
      }
      if($free_holds > $free_ore)
      {
        $salv_ore=$free_ore;
        $free_holds=$free_holds-$free_ore;
      } elseif($free_holds > 0)
      {
        $salv_ore=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_ore=0;
      }
      if($free_holds > $free_organics)
      {
        $salv_organics=$free_organics;
        $free_holds=$free_holds-$free_organics;
      } elseif($free_holds > 0)
      {
        $salv_organics=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_organics=0;
      }
      $ship_value=$upgrade_cost*(round(mypw($upgrade_factor, $shipinfo[hull]))+round(mypw($upgrade_factor, $shipinfo[engines]))+round(mypw($upgrade_factor, $shipinfo[power]))+round(mypw($upgrade_factor, $shipinfo[computer]))+round(mypw($upgrade_factor, $shipinfo[sensors]))+round(mypw($upgrade_factor, $shipinfo[beams]))+round(mypw($upgrade_factor, $shipinfo[torp_launchers]))+round(mypw($upgrade_factor, $shipinfo[shields]))+round(mypw($upgrade_factor, $shipinfo[armor]))+round(mypw($upgrade_factor, $shipinfo[cloak])));
      $ship_salvage_rate=rand(10,20);
      $ship_salvage=$ship_value*$ship_salvage_rate/100;
      playerlog($targetinfo[player_id], LOG_ATTACK_WIN, "Furangee $playerinfo[character_name]|$armor_lost|$fighters_lost");
      playerlog($targetinfo[player_id], LOG_RAW, "You destroyed the Furangee ship and salvaged $salv_ore units of ore, $salv_organics units of organics, $salv_goods units of goods, and salvaged $ship_salvage_rate% of the ship for $ship_salvage credits.");
      $armor_lost = $targetshipinfo[armour_pts] - $targetarmor;
      $fighters_lost = $targetshipinfo[fighters] - $targetfighters;
      $energy=$targetshipinfo[energy];
      $db->Execute ("UPDATE $dbtables[players] SET credits=credits+$ship_salvage, rating=rating-$rating_change WHERE player_id=$targetinfo[player_id]");
	  $db->Execute ("UPDATE $dbtables[ships] SET ore=ore+$salv_ore, organics=organics+$salv_organics, goods=goods+$salv_goods, energy=$energy,fighters=fighters-$fighters_lost, torps=torps-$targettorpnum,armour_pts=armour_pts-$armor_lost WHERE player_id=$targetinfo[player_id] AND ship_id=$targetinfo[currentship]");
    }
  }

  // *********************************
  // *** END OF FURANGEETOSHIP SUB ***
  // *********************************
  //$db->Execute("UNLOCK TABLES");
}

function furangeetosecdef()
{
  // **********************************
  // *** FURANGEE TO SECTOR DEFENCE ***
  // **********************************

  // *********************************
  // *** SETUP GENERAL VARIABLES  ****
  // *********************************
  global $playerinfo;
  global $shipinfo;
  global $targetlink;

  global $l_sf_sendlog;
  global $l_sf_sendlog2;
  global $l_chm_hehitminesinsector;
  global $l_chm_hewasdestroyedbyyourmines;

  global $furangeeisdead;
  global $db, $dbtables;

  // *********************************
  // *** CHECK FOR SECTOR DEFENCE ****
  // *********************************
  if ($targetlink>0)
  {
    $resultf = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id='$targetlink' and defence_type ='F' ORDER BY quantity DESC");
    $i = 0;
    $total_sector_fighters = 0;
    if($resultf > 0)
    {
      while(!$resultf->EOF)
      {
        $defences[$i] = $resultf->fields;
        $total_sector_fighters += $defences[$i]['quantity'];
        $i++;
        $resultf->MoveNext();
      }
    }
    $resultm = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id='$targetlink' and defence_type ='M'");
    $i = 0;
    $total_sector_mines = 0;
    if($resultm > 0)
    {
      while(!$resultm->EOF)
      {
        $defences[$i] = $resultm->fields;
        $total_sector_mines += $defences[$i]['quantity'];
        $i++;
        $resultm->MoveNext();
      }
    }
    if ($total_sector_fighters>0 || $total_sector_mines>0 || ($total_sector_fighters>0 && $total_sector_mines>0))
    //*** DEST LINK HAS DEFENCES SO LETS ATTACK THEM***
    {
      playerlog($playerinfo[player_id], LOG_RAW, "ATTACKING SECTOR DEFENCES $total_sector_fighters fighters and $total_sector_mines mines."); 
      // ************************************
      // *** LETS GATHER COMBAT VARIABLES ***
      // ************************************
      $targetfighters = $total_sector_fighters;
      $playerbeams = NUM_BEAMS($shipinfo[beams]);
      if($playerbeams>$shipinfo[ship_energy]) {
        $playerbeams=$shipinfo[ship_energy];
      }
      $shipinfo[ship_energy]=$shipinfo[ship_energy]-$playerbeams;
      $playershields = NUM_SHIELDS($shipinfo[shields]);
      if($playershields>$shipinfo[ship_energy]) {
        $playershields=$shipinfo[ship_energy];
      }
      $playertorpnum = round(mypw($level_factor,$shipinfo[torp_launchers]))*2;
      if($playertorpnum > $shipinfo[torps]) {
        $playertorpnum = $shipinfo[torps];
      }
      $playertorpdmg = $torp_dmg_rate*$playertorpnum;
      $playerarmour = $shipinfo[armour_pts];
      $playerfighters = $shipinfo[ship_fighters];
      $totalmines = $total_sector_mines;
      if ($totalmines>1) {
        $roll = rand(1,$totalmines);
      } else {
        $roll = 1;
      }
      $totalmines = $totalmines - $roll;
      $playerminedeflect = $shipinfo[ship_fighters]; // *** Furangee keep as many deflectors as fighters ***

      // *****************************
      // *** LETS DO SOME COMBAT ! ***
      // *****************************
      // *** BEAMS VS FIGHTERS ***
      if($targetfighters > 0 && $playerbeams > 0) {
        if($playerbeams > round($targetfighters / 2))
        {
          $temp = round($targetfighters/2);
          $targetfighters = $temp;
          $playerbeams = $playerbeams-$temp;
        } else {
          $targetfighters = $targetfighters-$playerbeams;
          $playerbeams = 0;
        }   
      }
      // *** TORPS VS FIGHTERS ***
      if($targetfighters > 0 && $playertorpdmg > 0) {
        if($playertorpdmg > round($targetfighters / 2)) {
          $temp=round($targetfighters/2);
          $targetfighters=$temp;
          $playertorpdmg=$playertorpdmg-$temp;
        } else {
          $targetfighters=$targetfighters-$playertorpdmg;
          $playertorpdmg=0;
        }
      }
      // *** FIGHTERS VS FIGHTERS ***
      if($playerfighters > 0 && $targetfighters > 0) {
       if($playerfighters > $targetfighters) {
         echo $l_sf_destfightall;
         $temptargfighters=0;
        } else {
          $temptargfighters=$targetfighters-$playerfighters;
        }
        if($targetfighters > $playerfighters) {
          $tempplayfighters=0;
        } else {
          $tempplayfighters=$playerfighters-$targetfighters;
        }     
        $playerfighters=$tempplayfighters;
        $targetfighters=$temptargfighters;
      }
      // *** OH NO THERE ARE STILL FIGHTERS **
      // *** ARMOUR VS FIGHTERS ***
      if($targetfighters > 0) {
        if($targetfighters > $playerarmour) {
          $playerarmour=0;
        } else {
          $playerarmour=$playerarmour-$targetfighters;
        } 
      }
      // *** GET RID OF THE SECTOR FIGHTERS THAT DIED ***
      $fighterslost = $total_sector_fighters - $targetfighters;
      destroy_fighters($targetlink,$fighterslost);

      // *** LETS LET DEFENCE OWNER KNOW WHAT HAPPENED *** 
      $l_sf_sendlog = str_replace("[player]", "Furangee $playerinfo[character_name]", $l_sf_sendlog);
      $l_sf_sendlog = str_replace("[lost]", $fighterslost, $l_sf_sendlog);
      $l_sf_sendlog = str_replace("[sector]", $targetlink, $l_sf_sendlog);
      message_defence_owner($targetlink,$l_sf_sendlog);

      // *** UPDATE FURANGEE AFTER COMBAT ***
      $armour_lost=$shipinfo[armour_pts]-$playerarmour;
      $fighters_lost=$shipinfo[ship_fighters]-$playerfighters;
      $energy=$shipinfo[ship_energy];
      $update1 = $db->Execute ("UPDATE $dbtables[ships] SET energy=$energy,fighters=fighters-$fighters_lost, armour_pts=armour_pts-$armour_lost, torps=torps-$playertorpnum WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");

      // *** CHECK TO SEE IF FURANGEE IS DEAD ***
      if($playerarmour < 1) {
        $l_sf_sendlog2 = str_replace("[player]", "Furangee " . $playerinfo[character_name], $l_sf_sendlog2);
        $l_sf_sendlog2 = str_replace("[sector]", $targetlink, $l_sf_sendlog2);
        message_defence_owner($targetlink,$l_sf_sendlog2);
        cancel_bounty($playerinfo[player_id]);
        db_kill_player($playerinfo[player_id],$playerinfo[currentship]);
        $furangeeisdead = 1;
        return;
      }

      // *** OK FURANGEE MUST STILL BE ALIVE ***

      // *** NOW WE HIT THE MINES ***

      // *** LETS LOG THE FACT THAT WE HIT THE MINES ***
      $l_chm_hehitminesinsector = str_replace("[chm_playerinfo_character_name]", "Furangee " . $playerinfo[character_name], $l_chm_hehitminesinsector);
      $l_chm_hehitminesinsector = str_replace("[chm_roll]", $roll, $l_chm_hehitminesinsector);
      $l_chm_hehitminesinsector = str_replace("[chm_sector]", $targetlink, $l_chm_hehitminesinsector);
      message_defence_owner($targetlink,"$l_chm_hehitminesinsector");

      // *** DEFLECTORS VS MINES ***
      if($playerminedeflect >= $roll) {
        // Took no mine damage due to virtual mine deflectors
      } else {
        $mines_left = $roll - $playerminedeflect;

        // *** SHIELDS VS MINES ***
        if($playershields >= $mines_left) {
          $update2 = $db->Execute("UPDATE $dbtables[ships] set energy=energy-$mines_left WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
        } else {
          $mines_left = $mines_left - $playershields;

          // *** ARMOUR VS MINES ***
          if($playerarmour >= $mines_left)
          {
            $update2 = $db->Execute("UPDATE $dbtables[ships] SET armour_pts=armour_pts-$mines_left,energy=0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
          } else {
            // *** OH NO WE DIED ***
            // *** LETS LOG THE FACT THAT WE DIED *** 
            $l_chm_hewasdestroyedbyyourmines = str_replace("[chm_playerinfo_character_name]", "Furangee " . $playerinfo[character_name], $l_chm_hewasdestroyedbyyourmines);
            $l_chm_hewasdestroyedbyyourmines = str_replace("[chm_sector]", $targetlink, $l_chm_hewasdestroyedbyyourmines);
            message_defence_owner($targetlink,"$l_chm_hewasdestroyedbyyourmines");
            // *** LETS ACTUALLY KILL THE FURANGEE NOW ***
            cancel_bounty($playerinfo[player_id]);
            db_kill_player($playerinfo[player_id],$playerinfo[currentship],0);
            $furangeeisdead = 1;
            // *** LETS GET RID OF THE MINES NOW AND RETURN OUT OF THIS FUNCTION ***
            explode_mines($targetlink,$roll);
            return;
          }
        }
      }
      // *** LETS GET RID OF THE MINES NOW ***
      explode_mines($targetlink,$roll);
    } else {
      //*** FOR SOME REASON THIS WAS CALLED WITHOUT ANY SECTOR DEFENCES TO ATTACK ***
      return;
    }
  }
}


function furangeemove()
{
  // *********************************
  // *** SETUP GENERAL VARIABLES  ****
  // *********************************
  global $playerinfo;
  global $shipinfo;
  global $sector_max;
  global $targetlink;
  global $furangeeisdead;
  global $db, $dbtables;
  // *********************************
  // *** Random real space move    ***
  // *********************************
  if (rand(0,100) < 10) {
  		// Move the ship to a new sector
		$new_sector = rand(0,$sector_max);
		$stamp = date("Y-m-d H-i-s");
		$db->Execute("UPDATE $dbtables[players] SET sector='$new_sector',turns_used=turns_used+1,last_login='$stamp' WHERE player_id='$playerinfo[player_id]'");
		$db->Execute("UPDATE $dbtables[ships] SET sector='$new_sector' WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		echo "Furangee $playerinfo[email] real spaced to sector $new_sector.<br>";
		return;
  }
  // *********************************
  // ***** OBTAIN A TARGET LINK ******
  // *********************************
  if ($targetlink==$playerinfo[sector]) $targetlink=0;
  $linkres = $db->Execute ("SELECT * FROM $dbtables[links] WHERE link_start='$playerinfo[sector]'");
  if ($linkres>0)
  {
    while (!$linkres->EOF)
    {
      $row = $linkres->fields;
      // *** OBTAIN SECTOR INFORMATION ***
      $sectres = $db->Execute ("SELECT sector_id,zone_id FROM $dbtables[universe] WHERE sector_id='$row[link_dest]'");
      $sectrow = $sectres->fields;
      $zoneres = $db->Execute("SELECT zone_id,allow_attack FROM $dbtables[zones] WHERE zone_id=$sectrow[zone_id]");
      $zonerow = $zoneres->fields;
      if ($zonerow[allow_attack]=="Y")                        //*** DEST LINK MUST ALLOW ATTACKING ***
      {
        $setlink=rand(0,2);                        //*** 33% CHANCE OF REPLACING DEST LINK WITH THIS ONE ***
        if ($setlink==0 || !$targetlink>0)          //*** UNLESS THERE IS NO DEST LINK, CHHOSE THIS ONE ***
        {
          $targetlink=$row[link_dest];
        }
      }
      $linkres->MoveNext();
    }
  }

  // *********************************
  // ***** IF NO ACCEPTABLE LINK *****
  // *********************************
  // **** TIME TO USE A WORM HOLE ****
  // *********************************
  if (!$targetlink>0)
  {
    // *** GENERATE A RANDOM SECTOR NUMBER ***
    $wormto=rand(1,($sector_max-15));
    $limitloop=1;                        // *** LIMIT THE NUMBER OF LOOPS ***
    while (!$targetlink>0 && $limitloop<15)
    {
      // *** OBTAIN SECTOR INFORMATION ***
      $sectres = $db->Execute ("SELECT sector_id,zone_id FROM $dbtables[universe] WHERE sector_id='$wormto'");
      $sectrow = $sectres->fields;
      $zoneres = $db->Execute ("SELECT zone_id,allow_attack FROM $dbtables[zones] WHERE zone_id=$sectrow[zone_id]");
      $zonerow = $zoneres->fields;
      if ($zonerow[allow_attack]=="Y")
      {
        $targetlink=$wormto;
        playerlog($playerinfo[player_id], LOG_RAW, "Used a wormhole to warp to a zone where attacks are allowed."); 
      }
      $wormto++;
      $wormto++;
      $limitloop++;
    }
  } 

  // *********************************
  // *** CHECK FOR SECTOR DEFENCE ****
  // *********************************
  if ($targetlink>0)
  {
    $resultf = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id='$targetlink' and defence_type ='F' ORDER BY quantity DESC");
    $i = 0;
    $total_sector_fighters = 0;
    if($resultf > 0)
    {
      while(!$resultf->EOF)
      {
        $defences[$i] = $resultf->fields;
        $total_sector_fighters += $defences[$i]['quantity'];
        $i++;
        $resultf->MoveNext();
      }
    }
    $resultm = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id='$targetlink' and defence_type ='M'");
    $i = 0;
    $total_sector_mines = 0;
    if($resultm > 0)
    {
      while(!$resultm->EOF)
      {
        $defences[$i] = $resultm->fields;
        $total_sector_mines += $defences[$i]['quantity'];
        $i++;
        $resultm->MoveNext();
      }
    }
    if ($total_sector_fighters>0 || $total_sector_mines>0 || ($total_sector_fighters>0 && $total_sector_mines>0))
    // ********************************
    // **** DEST LINK HAS DEFENCES ****
    // ********************************
    {
      if ($playerinfo[aggression] == 2 || $playerinfo[aggression] == 1) {
        // *********************************
        // **** DO MOVE TO TARGET LINK *****
        // *********************************
        $stamp = date("Y-m-d H-i-s");
        $query="UPDATE $dbtables[players] SET last_login='$stamp', turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]";
        $move_result = $db->Execute ("$query");
        $db->Execute("UPDATE $dbtables[players] SET sector=$targetlink WHERE player_id=$playerinfo[player_id]");
		$db->Execute("UPDATE $dbtables[ships] SET sector=$targetlink WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
        if (!$move_result)
        {
          $error = $db->ErrorMsg();
          playerlog($playerinfo[player_id], LOG_RAW, "Move failed with error: $error "); 
          $targetlink = $playerinfo[sector];         //*** RESET TARGET LINK SO IT IS NOT ZERO ***
          return;
        }
        // ********************************
        // **** ATTACK SECTOR DEFENCES ****
        // ********************************
        furangeetosecdef();
        return;
      } else {
        playerlog($playerinfo[player_id], LOG_RAW, "Move failed, the sector is defended by $total_sector_fighters fighters and $total_sector_mines mines."); 
        return;
      }
    } else
    // ********************************
    // **** DEST LINK IS UNDEFENDED ***
    // ********************************
    {
      // *********************************
      // **** DO MOVE TO TARGET LINK *****
      // *********************************
      $stamp = date("Y-m-d H-i-s");
      $query="UPDATE $dbtables[players] SET last_login='$stamp', turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]";
      $move_result = $db->Execute ("$query");
      $db->Execute("UPDATE $dbtables[players] SET sector=$targetlink WHERE player_id=$playerinfo[player_id]");
	  $db->Execute("UPDATE $dbtables[ships] SET sector=$targetlink WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
      if (!$move_result)
      {
        $error = $db->ErrorMsg();
        playerlog($playerinfo[player_id], LOG_RAW, "Move failed with error: $error "); 
        $targetlink = $playerinfo[sector];         //*** RESET TARGET LINK SO IT IS NOT ZERO ***
        return;
      } else
      {
        // playerlog($playerinfo[player_id], LOG_RAW, "Moved to $targetlink without incident."); 
      }
    }
  } else
  {                                            //*** WE HAVE NO TARGET LINK FOR SOME REASON ***
    playerlog($playerinfo[player_id], LOG_RAW, "Move failed due to lack of target link.");
    $targetlink = $playerinfo[sector];         //*** RESET TARGET LINK SO IT IS NOT ZERO ***
  }
}

function furangeeregen()
{
  // *******************************
  // *** SETUP GENERAL VARIABLES ***
  // *******************************
  global $playerinfo;
  global $shipinfo;
  global $furangeeisdead;
  global $db, $dbtables;

  // *******************************
  // *** LETS REGENERATE ENERGY ****
  // *******************************
  $maxenergy = NUM_ENERGY($shipinfo[power]);
  if ($shipinfo[ship_energy] <= ($maxenergy - 50))  // *** STOP REGEN WHEN WITHIN 50 OF MAX ***
  {                                                   // *** REGEN HALF OF REMAINING ENERGY ***
    $shipinfo[ship_energy] = $shipinfo[ship_energy] + round(($maxenergy - $shipinfo[ship_energy])/2);
    $gene = "regenerated Energy to $shipinfo[ship_energy] units,";
  }

  // *******************************
  // *** LETS REGENERATE ARMOUR ****
  // *******************************
  $maxarmour = NUM_ARMOUR($shipinfo[armour]);
  if ($shipinfo[armour_pts] <= ($maxarmour - 50))  // *** STOP REGEN WHEN WITHIN 50 OF MAX ***
  {                                                  // *** REGEN HALF OF REMAINING ARMOUR ***
    $shipinfo[armour_pts] = $shipinfo[armour_pts] + round(($maxarmour - $shipinfo[armour_pts])/2);
    $gena = "regenerated Armour to $shipinfo[armour_pts] points,";
  }

  // *******************************
  // *** LETS BUY FIGHTERS/TORPS ***
  // *******************************

  // *******************************
  // *** FURANGEE PAY 6/FIGHTER ****
  // *******************************
  if ($shipinfo[ship_fighters] < 0) {
  	playerlog($playerinfo[player_id], LOG_RAW, "Furangee had ".NUMBER($shipinfo[ship_fighters])." FIGHTERS!");
	$shipinfo[ship_fighters] = 0;
  }
  
  $available_fighters = NUM_FIGHTERS($shipinfo[computer]) - $shipinfo[ship_fighters];
  if ($available_fighters > 100000) {
    $available_fighters = round($available_fighters/6);
  }
  if (($playerinfo[credits]>5) && ($available_fighters>0))
  {
    if (round($playerinfo[credits]/50)>$available_fighters)
    {
      $purchase = ($available_fighters*50);
      $playerinfo[credits] = $playerinfo[credits] - $purchase;
      $shipinfo[ship_fighters] = $shipinfo[ship_fighters] + $available_fighters;
      $genf = "purchased $available_fighters fighters for $purchase credits,";
    }
    if (round($playerinfo[credits]/50)<=$available_fighters)
    {
      $purchase = round($playerinfo[credits]/50);
      $shipinfo[ship_fighters] = $shipinfo[ship_fighters] + $purchase/50;
      $genf = "purchased $purchase fighters for $playerinfo[credits] credits,";
      $playerinfo[credits] = 0;
    }
  } 

  // *******************************
  // *** FURANGEE PAY 3/TORPEDO ****
  // *******************************
  $available_torpedoes = (NUM_TORPEDOES($shipinfo[torp_launchers]) - $shipinfo[torps]);
  if ($available_torpedoes > 10000) {
	$available_torpedoes = round($available_torpedoes/6);
  }
  if (($playerinfo[credits]>24) && ($available_torpedoes>0))
  {
    if (round($playerinfo[credits]/25)>$available_torpedoes)
    {
      $purchase = $available_torpedoes*25;
      $playerinfo[credits] = $playerinfo[credits] - $purchase;
      $shipinfo[torps] = $shipinfo[torps] + $available_torpedoes;
      $gent = "purchased $available_torpedoes torpedoes for $purchase credits,";
    }
    if (round($playerinfo[credits]/25)<=$available_torpedoes)
    {
      $purchase = (round($playerinfo[credits]/25));
      $shipinfo[torps] = $shipinfo[torps] + $purchase;
      $gent = "purchased $purchase torpedoes for $playerinfo[credits] credits,";
      $playerinfo[credits] = 0;
    }
  } 

  // *********************************
  // *** UPDATE FURANGEE RECORD ******
  // *********************************
  $db->Execute ("UPDATE $dbtables[players] SET credits=$playerinfo[credits] WHERE player_id=$playerinfo[player_id]");
  $db->Execute ("UPDATE $dbtables[ships] SET ship_energy=$shipinfo[ship_energy], armour_pts=$shipinfo[armour_pts], ship_fighters=$shipinfo[ship_fighters], torps=$shipinfo[torps] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
 if (!$genf=='' || !$gent=='')
 {
    playerlog($playerinfo[player_id], LOG_RAW, "Furangee $genf $gent and has been updated."); 
 }

}

function furangeetrade()
{
  // *********************************
  // *** SETUP GENERAL VARIABLES  ****
  // *********************************
  global $playerinfo;
  global $shipinfo;
  global $inventory_factor;
  global $ore_price;
  global $ore_delta;
  global $ore_limit;
  global $goods_price;
  global $goods_delta;
  global $goods_limit;
  global $organics_price;
  global $organics_delta;
  global $organics_limit;
  global $furangeeisdead;
  global $db, $dbtables;
  //echo "Name: $playerinfo[character_name] Credits: $playerinfo[credits] Ore:$shipinfo[ship_ore] Goods:$shipinfo[ship_goods] Organics:$shipinfo[ship_organics]<br>";
  //echo "<pre>";
  //print_r($shipinfo);
  //echo "</pre>";
  // *************************************
  // *** OBTAIN PREFERANCE INFORMATION ***
  // *************************************
  $furanres = $db->Execute ("SELECT * FROM $dbtables[furangee] WHERE furangee_id='$playerinfo[email]'");
  $furangeeinfo = $furanres->fields;
  $prefer = $furangeeinfo[prefer];
  //echo "Furangee likes $prefer<br>";
  $prefer2 = "ship_".$prefer;
  $prefer3 = $prefer."_price";
  // *********************************
  // *** OBTAIN SECTOR INFORMATION ***
  // *********************************
  $sectres = $db->Execute ("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]'");
  $sectorinfo = $sectres->fields;
  // *********************************
  // *** OBTAIN PLANET INFORMATION ***
  // *********************************
  // Select the planet with the most of what I want
  //echo "I am in sector $playerinfo[sector]<br>";
  $planres = $db->Execute ("SELECT * FROM $dbtables[planets] WHERE sector_id='$playerinfo[sector]' AND sells='Y' AND $prefer>0 ORDER BY $prefer ASC LIMIT 1");
  // See if there are any planets to trade with and I don't have what I want already
  $planet_traded = false;
  while (($prefer != "none") && (!$planres->EOF) && $playerinfo[$prefer2] == 0) {
    //echo "There is a planet to trade with here!<br>";
  	$planet_traded = true;
  	$planetinfo = $planres->fields;
	// Find out prices for the commodities
	//echo "DEBUG: SELECT $prefer3 AS price FROM $dbtables[players] WHERE player_id=$planetinfo[owner] LIMIT 1<br>";
	$sellres = $db->Execute("SELECT $prefer3 AS price FROM $dbtables[players] WHERE player_id=$planetinfo[owner] LIMIT 1");
	$sellinfo = $sellres->fields;
	//echo "The seller has the following price for $prefer: $sellinfo[price]<br>";
	$quantity = 0;
	if ($sellinfo[price]>=0.1) {
		// Find out how much I can afford, what's available and what I can carry
		$quantity = min(floor($playerinfo[credits]/$sellinfo[price]),$planetinfo[$prefer],NUM_HOLDS($shipinfo[hull]));
	}
	//echo "I can buy $quantity of $prefer<br>";
	// Check to see if I can afford to buy anything
	if ($quantity==0) {
		$planet_traded = false;
	} else {
		// The planet has some of what I want
		//echo "My holds have ore $shipinfo[ship_ore], goods $shipinfo[ship_goods], organics $shipinfo[ship_organics]<br>";
		// Find out what I need to dump to make room, dump it and load up
		$amount_ore = 0;
		$amount_goods = 0;
		$amount_organics = 0;
		$dump_value = rand(1,10);
		if ($prefer == "ore") {
			if ($shipinfo[ship_goods] > $quantity) {
				// We have more goods than what I want. Just drop off the some of goods on the planet and dump the rest in space
				$shipinfo[ship_goods] -= $quantity;
				$planetinfo[goods] += $quantity/$dump_value;
				$amount_goods = $quantity/$dump_value;
			} else {
				// I need to drop off more than just the goods
				// Drop off all the goods
				$planetinfo[goods] += $shipinfo[ship_goods];
				$quantityleft = $quantity - $shipinfo[ship_goods];
				$amount_goods = $shipinfo[ship_goods];
				$shipinfo[ship_goods] = 0;
				if ($shipinfo[ship_organics] > $quantityleft) {
					// We have enough orgs to cover what needs to be dropped off
					$shipinfo[ship_organics] -= $quantityleft;
					$planetinfo[organics] += $quantityleft/$dump_value;
					$amount_organics = $quantityleft/$dump_value;
				} else {
					// All the organics need to go too
					$planetinfo[organics] += $shipinfo[ship_organics]/$dump_value;
					$amount_organics = $shipinfo[ship_organics]/$dump_value;
					$shipinfo[ship_organics] = 0;
					// Anything left after this must be just remainder I hope
				}
			}
		} else if ($prefer == "organics") {
			if ($shipinfo[ship_goods] > $quantity) {
				// We have more goods than what I want. Just drop off the same number of goods on the planet
				$shipinfo[ship_goods] -= $quantity;
				$planetinfo[goods] += $quantity/$dump_value;
				$amount_goods = $quantity/$dump_value;
			} else {
				// I need to drop off more than just the goods
				// Drop off all the goods
				$planetinfo[goods] += $shipinfo[ship_goods]/$dump_value;
				$quantityleft = $quantity - $shipinfo[ship_goods];
				$amount_goods = $shipinfo[ship_goods]/$dump_value;
				$shipinfo[ship_goods] = 0;
				if ($shipinfo[ship_ore] > $quantityleft) {
					// We have enough ore to cover what needs to be dropped off
					$shipinfo[ship_ore] -= $quantityleft;
					$planetinfo[ore] += $quantityleft/$dump_value;
					$amount_ore = $quantityleft/$dump_value;
				} else {
					// All the ore need to go too
					$planetinfo[ore] += $shipinfo[ship_ore]/$dump_value;
					$amount_ore = $shipinfo[ship_ore]/$dump_value;
					$shipinfo[ship_ore] = 0;
					// Anything left after this must be just remainder I hope
				}
			}
		} else if ($prefer == "goods") {
			if ($shipinfo[ship_ore] > $quantity) {
				// We have more ore than what I want. Just drop off the same number of ore on the planet
				$shipinfo[ship_ore] -= $quantity;
				$planetinfo[ore] += $quantity/$dump_value;
				$amount_ore = $quantity/$dump_value;
			} else {
				// I need to drop off more than just the ore
				// Drop off all the ore
				$planetinfo[ore] += $shipinfo[ship_ore]/$dump_value;
				$quantityleft = $quantity - $shipinfo[ship_ore];
				$amount_ore = $shipinfo[ship_ore]/$dump_value;
				$shipinfo[ship_ore] = 0;
				if ($shipinfo[ship_organics] > $quantityleft) {
					// We have enough orgs to cover what needs to be dropped off
					$shipinfo[ship_organics] -= $quantityleft;
					$planetinfo[organics] += $quantityleft/$dump_value;
					$amount_organics = $quantityleft/$dump_value;
				} else {
					// All the organics need to go too
					$planetinfo[organics] += $shipinfo[ship_organics]/$dump_value;
					$amount_organics = $shipinfo[ship_organics]/$dump_value;
					$shipinfo[ship_organics] = 0;
					// Anything left after this must be just remainder I hope
				}
			}
		}
		// Move the wanted items to the ship and deduct from the planet
		$planetinfo[$prefer] -= $quantity;
		$shipinfo[$prefer2] += $quantity;
		//echo "I bought $prefer from $planetinfo[name] and now my holds have ore $shipinfo[ship_ore], goods $shipinfo[ship_goods], organics $shipinfo[ship_organics]<br>";
		//echo "I transfered $amount_ore ore, $amount_goods goods and $amount_organics organics.<br>";
		// Buy that amount and put it on the ship
		//echo "I have $playerinfo[credits] credits<br>";
		$playerinfo[credits]-= ($sellinfo[price]*$quantity);
		//echo "I now have $playerinfo[credits] credits<br>";
		// Update the database tables
		// playerlog($playerinfo[player_id], LOG_FURANGEE_TRADE, "Furangee Traded At Planet: $planetinfo[name] and bought $quantity of $prefer and transfered $amount_ore ore, $amount_goods goods and $amount_organics organics.");
		// The rating of the furangee increases when he trades, I guess to make it bad to kill him 
	    $trade_result = $db->Execute("UPDATE $dbtables[players] SET rating=rating+1, credits=credits-($sellinfo[price]*$quantity) WHERE player_id=$playerinfo[player_id]");
    	$trade_result = $db->Execute("UPDATE $dbtables[ships] SET ship_ore=$shipinfo[ship_ore], ship_organics=$shipinfo[ship_organics], ship_goods=$shipinfo[ship_goods] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		$trade_result2 = $db->Execute("UPDATE $dbtables[planets] SET ore=$planetinfo[ore], organics=$planetinfo[organics], goods=$planetinfo[goods], credits=credits+($sellinfo[price]*$quantity) WHERE planet_id=$planetinfo[planet_id]");
		$message = "Furangee $playerinfo[character_name] bought ".NUMBER($quantity)." of $prefer from your planet $planetinfo[name] in sector $planetinfo[sector_id]. You made ".NUMBER(($sellinfo[price]*$quantity))." credits!";
		if ($amount_ore > 0 | $amount_goods > 0 | $amount_organics > 0) {
			$message = $message." Furangee $playerinfo[character_name] also dumped";
			if ($amount_ore > 0) {
				$message = $message." ".NUMBER($amount_ore)." ore";
			}
			if ($amount_goods > 0) {
				if ($amount_ore > 0)
					$message = $message.",";					
				$message = $message." ".NUMBER($amount_goods)." goods";
			}
			if ($amount_organics > 0) {
				if ($amount_ore > 0 | $amount_goods > 0)
					$message = $message." and";
				$message = $message." ".NUMBER($amount_organics)." organics";
			}
			$message = $message." on the planet.";
		}
    	playerlog($planetinfo[owner], LOG_FURANGEE_TRADE, $message); 
	}
	$planres->MoveNext();
  }
  if ($planet_traded) {
  	//echo "I traded at a planet so I've finished<br>";
	return;
  } else {
    //echo "I did not trade at a planet so I continue.<br>";
  }
  // *********************************
  // **** OBTAIN ZONE INFORMATION ****
  // *********************************
  $zoneres = $db->Execute ("SELECT zone_id,allow_attack,allow_trade FROM $dbtables[zones] WHERE zone_id='$sectorinfo[zone_id]'");
  $zonerow = $zoneres->fields;

  // Debug info
  //playerlog($playerinfo[player_id], LOG_RAW, "PORT $sectorinfo[port_type] ALLOW_TRADE $zonerow[allow_trade] PORE $sectorinfo[port_ore] PORG $sectorinfo[port_organics] PGOO $sectorinfo[port_goods] ORE $shipinfo[ship_ore] ORG $shipinfo[ship_organics] GOO $shipinfo[ship_goods] CREDITS $playerinfo[credits] "); 

  // *********************************
  // ** MAKE SURE WE CAN TRADE HERE **
  // *********************************
  //echo "Check if trade ok<br>";
  if ($zonerow[allow_trade]=="N") return;
  // *********************************
  // ** CHECK FOR A PORT WE CAN USE **
  // *********************************
  //echo "Check if port ok<br>";
  if($sectorinfo[port_type] == "none") return;
  // *** FURANGEE DO NOT TRADE AT SPECIAL PORTS ***
  if($sectorinfo[port_type] == "special") return;
  // *** FURANGEE DO NOT TRADE AT ENERGY PORTS SINCE THEY REGEN ENERGY ***
  if($sectorinfo[port_type] == "energy") return;
  //echo "port ok. check for neg credit or cargo<br>";
  // *********************************
  // ** CHECK FOR NEG CREDIT/CARGO ***
  // *********************************
  if($shipinfo[ship_ore] <= 0) $shipinfo[ship_ore]=$shipore=0;
  if($shipinfo[ship_organics] <= 0) $shipinfo[ship_organics]=$shiporganics=0;
  if($shipinfo[ship_goods] <= 0) $shipinfo[ship_goods]=$shipgoods=0;
  if($playerinfo[credits] <= 0) $playerinfo[credits]=$shipcredits=0;
  if($sectorinfo[port_ore] <= 0) return;
  if($sectorinfo[port_organics] <= 0) return;
  if($sectorinfo[port_goods] <= 0) return;
  //echo "check furangee credit cargo<br>";
  // *********************************
  // ** CHECK FURANGEE CREDIT/CARGO **
  // *********************************
  if($shipinfo[ship_ore]>0) $shipore=$shipinfo[ship_ore];
  if($shipinfo[ship_organics]>0) $shiporganics=$shipinfo[ship_organics];
  if($shipinfo[ship_goods]>0) $shipgoods=$shipinfo[ship_goods];
  if($playerinfo[credits]>0) $shipcredits=$playerinfo[credits];
  // *** MAKE SURE WE HAVE CARGO OR CREDITS **
  //echo "Make sure we have cargo or credits<br>";
  if(!$playerinfo[credits]>0 && !$shipinfo[ship_ore]>0 && !$shipinfo[ship_goods]>0 && !$shipinfo[ship_organics]>0) return;
  // **************************************
  // ** MAKE SURE WE HAVE CARGO TO TRADE **
  // **************************************
  //echo "Make sure we have cargo to trade<br>";
  /*
  if($sectorinfo[port_type]=="ore" && $shiporganics==0 && $shipgoods==0) return;
  if($sectorinfo[port_type]=="organics" && $shipore==0 && $shipgoods==0) return;
  if($sectorinfo[port_type]=="goods" && $shipore==0 && $shiporganics==0) return;
  */
  // *********************************
  // ***** LETS TRADE SOME CARGO *****
  // *********************************
  //echo "Let's trade<br>";
  if($sectorinfo[port_type]=="ore")
  // *********************
  // ***** PORT ORE ******
  // *********************
  {
    // ************************
    // **** SET THE PRICES ****
    // ************************
    $ore_price1 = $ore_price - $ore_delta * $sectorinfo[port_ore] / $ore_limit * $inventory_factor;
    $organics_price1 = $organics_price + $organics_delta * $sectorinfo[port_organics] / $organics_limit * $inventory_factor;
    $goods_price1 = $goods_price + $goods_delta * $sectorinfo[port_goods] / $goods_limit * $inventory_factor;
    // ************************
    // ** SET CARGO BUY/SELL **
    // ************************
    $amount_organics = $shipinfo[ship_organics];
    $amount_goods = $shipinfo[ship_goods];
    // *** SINCE WE SELL ALL OTHER HOLDS WE SET AMOUNT TO BE OUR TOTAL HOLD LIMIT *** 
    $amount_ore = NUM_HOLDS($shipinfo[hull]);
    // *** WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEED WHAT THE PORT HAS TO SELL ***
    $amount_ore = min($amount_ore, $sectorinfo[port_ore]);
    // *** WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEES WHAT WE CAN AFFORD TO BUY ***
    $amount_ore = min($amount_ore, floor(($playerinfo[credits] + $amount_organics * $organics_price1 + $amount_goods * $goods_price1) / $ore_price1));
    // ************************
    // **** BUY/SELL CARGO ****
    // ************************
    $total_cost = round(($amount_ore * $ore_price1) - ($amount_organics * $organics_price1 + $amount_goods * $goods_price1));
    $newcredits = max(0,$playerinfo[credits]-$total_cost);
    $newore = $shipinfo[ship_ore]+$amount_ore;
    $neworganics = max(0,$shipinfo[ship_organics]-$amount_organics);
    $newgoods = max(0,$shipinfo[ship_goods]-$amount_goods);
    if ($newore < 0 || $neworganics < 0 || $newgoods < 0) {
    playerlog($playerinfo[player_id], LOG_RAW, "Furangee Trade Negative ERROR: Port $sectorinfo[port_type] ORE player $newore port $sectorinfo[port_ore] price $ore_price1 delta $ore_delta ORG player $neworganics port $sectorinfo[port_organics] price $organics_price1 delta $organics_delta GOOD player $newgoods port $sectorinfo[port_goods] price $goods_price1 delta $goods_delta"); 
    }
    $trade_result = $db->Execute("UPDATE $dbtables[players] SET rating=rating+1, credits=$newcredits WHERE player_id=$playerinfo[player_id]");
	$trade_result = $db->Execute("UPDATE $dbtables[ships] SET ship_ore=$newore, ship_organics=$neworganics, ship_goods=$newgoods WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    $trade_result2 = $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore-$amount_ore, port_organics=port_organics+$amount_organics, port_goods=port_goods+$amount_goods WHERE sector_id=$sectorinfo[sector_id]");
    //playerlog($playerinfo[player_id], LOG_RAW, "Furangee Trade Results: Sold $amount_organics Organics Sold $amount_goods Goods Bought $amount_ore Ore Cost $total_cost"); 
  }
  if($sectorinfo[port_type]=="organics")
  // *********************
  // *** PORT ORGANICS ***
  // *********************
  {
    // ************************
    // **** SET THE PRICES ****
    // ************************
    $organics_price1 = $organics_price - $organics_delta * $sectorinfo[port_organics] / $organics_limit * $inventory_factor;
    $ore_price1 = $ore_price + $ore_delta * $sectorinfo[port_ore] / $ore_limit * $inventory_factor;
    $goods_price1 = $goods_price + $goods_delta * $sectorinfo[port_goods] / $goods_limit * $inventory_factor;
    // ************************
    // ** SET CARGO BUY/SELL **
    // ************************
    $amount_ore = $shipinfo[ship_ore];
    $amount_goods = $shipinfo[ship_goods];
    // *** SINCE WE SELL ALL OTHER HOLDS WE SET AMOUNT TO BE OUR TOTAL HOLD LIMIT *** 
    $amount_organics = NUM_HOLDS($shipinfo[hull]);
    // *** WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEED WHAT THE PORT HAS TO SELL ***
    $amount_organics = min($amount_organics, $sectorinfo[port_organics]);
    // *** WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEES WHAT WE CAN AFFORD TO BUY ***
    $amount_organics = min($amount_organics, floor(($playerinfo[credits] + $amount_ore * $ore_price1 + $amount_goods * $goods_price1) / $organics_price1));
    // ************************
    // **** BUY/SELL CARGO ****
    // ************************
    $total_cost = round(($amount_organics * $organics_price1) - ($amount_ore * $ore_price1 + $amount_goods * $goods_price1));
    $newcredits = max(0,$playerinfo[credits]-$total_cost);
    $newore = max(0,$shipinfo[ship_ore]-$amount_ore);
    $neworganics = $shipinfo[ship_organics]+$amount_organics;
    $newgoods = max(0,$shipinfo[ship_goods]-$amount_goods);
    if ($newore < 0 || $neworganics < 0 || $newgoods < 0) {
    playerlog($playerinfo[player_id], LOG_RAW, "Furangee Trade Negative ERROR: Port $sectorinfo[port_type] ORE player $newore port $sectorinfo[port_ore] price $ore_price1 delta $ore_delta ORG player $neworganics port $sectorinfo[port_organics] price $organics_price1 delta $organics_delta GOOD player $newgoods port $sectorinfo[port_goods] price $goods_price1 delta $goods_delta"); 
    }
    $trace_result = $db->Execute("UPDATE $dbtables[players] SET rating=rating+1, credits=$newcredits WHERE player_id=$playerinfo[player_id]");
	$trade_result = $db->Execute("UPDATE $dbtables[ships] SET ship_ore=$newore, ship_organics=$neworganics, ship_goods=$newgoods WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    $trade_result2 = $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore+$amount_ore, port_organics=port_organics-$amount_organics, port_goods=port_goods+$amount_goods where sector_id=$sectorinfo[sector_id]");
    //playerlog($playerinfo[player_id], LOG_RAW, "Furangee Trade Results: Sold $amount_goods Goods Sold $amount_ore Ore Bought $amount_organics Organics Cost $total_cost"); 
  }
  if($sectorinfo[port_type]=="goods")
  // *********************
  // **** PORT GOODS *****
  // *********************
  {
    // ************************
    // **** SET THE PRICES ****
    // ************************
    $goods_price1 = $goods_price - $goods_delta * $sectorinfo[port_goods] / $goods_limit * $inventory_factor;
    $ore_price1 = $ore_price + $ore_delta * $sectorinfo[port_ore] / $ore_limit * $inventory_factor;
    $organics_price1 = $organics_price + $organics_delta * $sectorinfo[port_organics] / $organics_limit * $inventory_factor;
    // ************************
    // ** SET CARGO BUY/SELL **
    // ************************
    $amount_ore = $shipinfo[ship_ore];
    $amount_organics = $shipinfo[ship_organics];
    // *** SINCE WE SELL ALL OTHER HOLDS WE SET AMOUNT TO BE OUR TOTAL HOLD LIMIT *** 
    $amount_goods = NUM_HOLDS($shipinfo[hull]);
    // *** WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEED WHAT THE PORT HAS TO SELL ***
    $amount_goods = min($amount_goods, $sectorinfo[port_goods]);
    // *** WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEES WHAT WE CAN AFFORD TO BUY ***
    $amount_goods = min($amount_goods, floor(($playerinfo[credits] + $amount_ore * $ore_price1 + $amount_organics * $organics_price1) / $goods_price1));
    // ************************
    // **** BUY/SELL CARGO ****
    // ************************
    $total_cost = round(($amount_goods * $goods_price1) - ($amount_organics * $organics_price1 + $amount_ore * $ore_price1));
	//echo "Total cost at goods port was $total_cost<br>";
    $newcredits = max(0,$playerinfo[credits]-$total_cost);
	//echo "New credits = $newcredits<br>";
    $newore = max(0,$shipinfo[ship_ore]-$amount_ore);
    $neworganics = max(0,$shipinfo[ship_organics]-$amount_organics);
    $newgoods = $shipinfo[ship_goods]+$amount_goods;
    if ($newore < 0 || $neworganics < 0 || $newgoods < 0) {
    playerlog($playerinfo[player_id], LOG_RAW, "Furangee Trade Negative ERROR: Port $sectorinfo[port_type] ORE player $newore port $sectorinfo[port_ore] price $ore_price1 delta $ore_delta ORG player $neworganics port $sectorinfo[port_organics] price $organics_price1 delta $organics_delta GOOD player $newgoods port $sectorinfo[port_goods] price $goods_price1 delta $goods_delta"); 
    }
    $trade_result = $db->Execute("UPDATE $dbtables[players] SET rating=rating+1, credits=$newcredits WHERE player_id=$playerinfo[player_id]");
	$trade_result = $db->Execute("UPDATE $dbtables[ships] SET ship_ore=$newore, ship_organics=$neworganics, ship_goods=$newgoods WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    $trade_result2 = $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore+$amount_ore, port_organics=port_organics+$amount_organics, port_goods=port_goods-$amount_goods where sector_id=$sectorinfo[sector_id]");
    //playerlog($playerinfo[player_id], LOG_RAW, "Furangee Trade Results: Sold $amount_ore Ore Sold $amount_organics Organics Bought $amount_goods Goods Cost $total_cost"); 
  }

}

function furangeehunter()
{
  // *********************************
  // *** SETUP GENERAL VARIABLES  ****
  // *********************************
  global $playerinfo;
  global $shipinfo;
  global $targetlink;
  global $furangeeisdead;
  global $db, $dbtables;

  $rescount = $db->Execute("SELECT COUNT(*) AS num_players FROM $dbtables[players] WHERE ship_destroyed='N' and email NOT LIKE '%@furangee' and player_id > 1");
  $rowcount = $rescount->fields;
  echo "There are $rowcount[num_players] eligable players<br>";
  $topnum = min(10,$rowcount[num_players]);
	//echo "Topnum = $topnum<br>";
	//$topnum =1;
  // *** IF WE HAVE KILLED ALL THE PLAYERS IN THE GAME THEN THERE IS LITTLE POINT IN PROCEEDING ***
  if ($topnum<1) {
  	echo "Furange have WON!!<br>";
	return;
  }

  $res = $db->Execute("SELECT * FROM $dbtables[players] WHERE ship_destroyed='N' and email NOT LIKE '%@furangee' AND player_id > 1 ORDER BY score DESC LIMIT $topnum");

  // *** LETS CHOOSE A TARGET FROM THE TOP PLAYER LIST ***
  $i=1;
  $targetnum=rand(1,$topnum);
  while (!$res->EOF)
  {
    if ($i==$targetnum)
    { 
    $targetinfo=$res->fields;
    }
    $i++;
    $res->MoveNext();
  }

  // *** Make sure we have a target ***
  if (!$targetinfo)
  {
    playerlog($playerinfo[player_id], LOG_RAW, "Hunt Failed: No Target ");
	echo "Hunt failed: No target<br>";
    return;
  } else {
  	echo "We have a target!<br>";
  }

  // *********************************
  // *** WORM HOLE TO TARGET SECTOR **
  // *********************************
  $sectres = $db->Execute ("SELECT sector_id,zone_id FROM $dbtables[universe] WHERE sector_id='$targetinfo[sector]'");
  $sectrow = $sectres->fields;
  $zoneres = $db->Execute ("SELECT zone_id,allow_attack FROM $dbtables[zones] WHERE zone_id=$sectrow[zone_id]");
  $zonerow = $zoneres->fields;
  // *** ONLY WORM HOLE TO TARGET IF WE CAN ATTACK IN TARGET SECTOR ***
  if ($zonerow[allow_attack]=="Y")
  {
    $stamp = date("Y-m-d H-i-s");
    $query="UPDATE $dbtables[players] SET last_login='$stamp', turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]";
    $move_result = $db->Execute ("$query");
    $db->Execute("UPDATE $dbtables[players] SET sector=$targetinfo[sector] WHERE player_id=$playerinfo[player_id]");
    playerlog($playerinfo[player_id], LOG_RAW, "Furangee used a wormhole to warp to sector $targetinfo[sector] where he is hunting player $targetinfo[character_name]."); 
	echo "Furangee used a wormhole to warp to sector $targetinfo[sector_id] where he is hunting player $targetinfo[character_name].<br>";
    if (!$move_result)
    {
      $error = $db->ErrorMsg();
      playerlog($playerinfo[player_id], LOG_RAW, "Move failed with error: $error ");
	  echo  "Move failed with error: $error<br> ";
      return;
    }
  // *********************************
  // *** CHECK FOR SECTOR DEFENCE ****
  // *********************************
    $resultf = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id=$targetinfo[sector_id] and defence_type ='F' ORDER BY quantity DESC");
    $i = 0;
    $total_sector_fighters = 0;
    if($resultf > 0)
    {
      while(!$resultf->EOF)
      {
        $defences[$i] = $resultf->fields;
        $total_sector_fighters += $defences[$i]['quantity'];
        $i++;
        $resultf->MoveNext();
      }
    }
    $resultm = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id=$targetinfo[sector_id] and defence_type ='M'");
    $i = 0;
    $total_sector_mines = 0;
    if($resultm > 0)
    {
      while(!$resultm->EOF)
      {
        $defences[$i] = $resultm->fields;
        $total_sector_mines += $defences[$i]['quantity'];
        $i++;
        $resultm->MoveNext();
      }
    }

    if ($total_sector_fighters>0 || $total_sector_mines>0 || ($total_sector_fighters>0 && $total_sector_mines>0))
    //*** DEST LINK HAS DEFENCES ***
    {
      // *** ATTACK SECTOR DEFENCES ***
      $targetlink = $targetinfo[sector_id];
      furangeetosecdef();
    }
    if ($furangeeisdead>0) {
      // *** SECTOR DEFENSES KILLED US ***
      return;
    }

    // *** TIME TO ATTACK THE TARGET ***
    playerlog($playerinfo[player_id], LOG_RAW, "Furangee launching an attack on $targetinfo[character_name].");
	echo "Furangee launching an attack on $targetinfo[character_name].<br>"; 

    // *** SEE IF TARGET IS ON A PLANET ***
    if ($targetinfo[on_planet]=='Y') {
      // *** ON A PLANET ***
      furangeetoplanet($targetinfo[player_id]);
    } else {
      // *** NOT ON A PLANET ***
      furangeetoship($targetinfo[player_id]);
    }
  } else
  {
    playerlog($playerinfo[player_id], LOG_RAW, "Furangee hunt failed, target $targetinfo[character_name] was in a no attack zone (sector $targetinfo[sector]).");
	echo "Furangee hunt failed, target $targetinfo[character_name] was in a no attack zone (sector $targetinfo[sector]).<br>";
  }
}

function furangeetoplanet($planet_id)
{
  // ***********************************
  // *** Furangee Planet Attack Code ***
  // ***********************************

  // *********************************
  // *** SETUP GENERAL VARIABLES  ****
  // *********************************
  global $playerinfo;
  global $shipinfo;
  global $planetinfo;

  global $torp_dmg_rate;
  global $level_factor;
  global $rating_combat_factor;
  global $upgrade_cost;
  global $upgrade_factor;
  global $sector_max;
  global $furangeeisdead;
  global $db, $dbtables;

  // *** LOCKING TABLES ****
  //$db->Execute("LOCK TABLES $dbtables[players] WRITE, $dbtables[ships] WRITE, $dbtables[universe] WRITE, $dbtables[planets] WRITE, $dbtables[news] WRITE, $dbtables[logs] WRITE");

  // ********************************
  // *** LOOKUP PLANET DETAILS   ****
  // ********************************
  //$resultp = $db->Execute ("SELECT * FROM $dbtables[planets] WHERE planet_id='$planet_id'");
  echo "The planet ID (ship id) = $planet_id<br>";
  $resultp = $db->Execute ("SELECT * FROM $dbtables[planets] WHERE owner='$planet_id'");
  if ($resultp->EOF) {
  	echo "Asked to get info on $planet_id and failed<br>";
	return;
  }
  $planetinfo=$resultp->fields;

  // ********************************
  // *** LOOKUP OWNER DETAILS    ****
  // ********************************
  $resulto = $db->Execute ("SELECT * FROM $dbtables[players] WHERE player_id='$planet_id'");
  $ownerinfo=$resulto->fields;

  // **********************************
  // *** SETUP PLANETARY VARIABLES ****
  // **********************************
  $base_factor = ($planetinfo[base] == 'Y') ? $basedefense : 0;

  // *** PLANET BEAMS ***
  $targetbeams = NUM_BEAMS($ownerinfo[beams] + $base_factor);
  if ($targetbeams > $planetinfo[energy]) $targetbeams = $planetinfo[energy];
  $planetinfo[energy] -= $targetbeams;
    
  // *** PLANET SHIELDS ***
  $targetshields = NUM_SHIELDS($ownerinfo[shields] + $base_factor);
  if ($targetshields > $planetinfo[energy]) $targetshields = $planetinfo[energy];
  $planetinfo[energy] -= $targetshields;
    
  // *** PLANET TORPS ***
  $torp_launchers = round(mypw($level_factor, ($ownerinfo[torp_launchers])+ $base_factor)) * 10;
  $torps = $planetinfo[torps];
  $targettorps = $torp_launchers;
  if ($torp_launchers > $torps) $targettorps = $torps;
  $planetinfo[torps] -= $targettorps;
  $targettorpdmg = $torp_dmg_rate * $targettorps;

  // *** PLANET FIGHTERS ***
  $targetfighters = $planetinfo[fighters];

  // *********************************
  // *** SETUP ATTACKER VARIABLES ****
  // *********************************

  // *** ATTACKER BEAMS ***
  $attackerbeams = NUM_BEAMS($shipinfo[beams]);
  if ($attackerbeams > $shipinfo[ship_energy]) $attackerbeams = $shipinfo[ship_energy];
  $shipinfo[ship_energy] -= $attackerbeams;

  // *** ATTACKER SHIELDS ***
  $attackershields = NUM_SHIELDS($shipinfo[shields]);
  if ($attackershields > $shipinfo[ship_energy]) $attackershields = $shipinfo[ship_energy];
  $shipinfo[ship_energy] -= $attackershields;

  // *** ATTACKER TORPS ***
  $attackertorps = round(mypw($level_factor, $shipinfo[torp_launchers])) * 2;
  if ($attackertorps > $shipinfo[torps]) $attackertorps = $shipinfo[torps]; 
  $shipinfo[torps] -= $attackertorps;
  $attackertorpdamage = $torp_dmg_rate * $attackertorps;

  // *** ATTACKER FIGHTERS ***
  $attackerfighters = $shipinfo[ship_fighters];

  // *** ATTACKER ARMOUR ***
  $attackerarmor = $shipinfo[armour_pts];

  // *********************************
  // **** BEGIN COMBAT PROCEDURES ****
  // *********************************
  if($attackerbeams > 0 && $targetfighters > 0)
  {                         //******** ATTACKER HAS BEAMS - TARGET HAS FIGHTERS - BEAMS VS FIGHTERS ********
    if($attackerbeams > $targetfighters)
    {                                  //****** ATTACKER BEAMS GT TARGET FIGHTERS ******
      $lost = $targetfighters;
      $targetfighters = 0;                                     //**** T LOOSES ALL FIGHTERS ****
      $attackerbeams = $attackerbeams-$lost;                   //**** A LOOSES BEAMS EQ TO T FIGHTERS ****
    } else
    {                                  //****** ATTACKER BEAMS LE TARGET FIGHTERS ******
      $targetfighters = $targetfighters-$attackerbeams;        //**** T LOOSES FIGHTERS EQ TO A BEAMS ****
      $attackerbeams = 0;                                      //**** A LOOSES ALL BEAMS ****
    }   
  }
  if($attackerfighters > 0 && $targetbeams > 0)
  {                         //******** TARGET HAS BEAMS - ATTACKER HAS FIGHTERS - BEAMS VS FIGHTERS ********
    if($targetbeams > round($attackerfighters / 2))
    {                                  //****** TARGET BEAMS GT HALF ATTACKER FIGHTERS ******
      $lost=$attackerfighters-(round($attackerfighters/2));
      $attackerfighters=$attackerfighters-$lost;               //**** A LOOSES HALF ALL FIGHTERS ****
      $targetbeams=$targetbeams-$lost;                         //**** T LOOSES BEAMS EQ TO HALF A FIGHTERS ****
    } else
    {                                  //****** TARGET BEAMS LE HALF ATTACKER FIGHTERS ******
      $attackerfighters=$attackerfighters-$targetbeams;        //**** A LOOSES FIGHTERS EQ TO T BEAMS **** 
      $targetbeams=0;                                          //**** T LOOSES ALL BEAMS ****
    }
  }
  if($attackerbeams > 0)
  {                         //******** ATTACKER HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS SHIELDS ********
    if($attackerbeams > $targetshields)
    {                                  //****** ATTACKER BEAMS GT TARGET SHIELDS ******
      $attackerbeams=$attackerbeams-$targetshields;            //**** A LOOSES BEAMS EQ TO T SHIELDS ****
      $targetshields=0;                                        //**** T LOOSES ALL SHIELDS ****
    } else
    {                                  //****** ATTACKER BEAMS LE TARGET SHIELDS ******
      $targetshields=$targetshields-$attackerbeams;            //**** T LOOSES SHIELDS EQ TO A BEAMS ****
      $attackerbeams=0;                                        //**** A LOOSES ALL BEAMS ****
    }
  }
  if($targetbeams > 0)
  {                         //******** TARGET HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS SHIELDS ********
    if($targetbeams > $attackershields)
    {                                  //****** TARGET BEAMS GT ATTACKER SHIELDS ******
      $targetbeams=$targetbeams-$attackershields;              //**** T LOOSES BEAMS EQ TO A SHIELDS ****
      $attackershields=0;                                      //**** A LOOSES ALL SHIELDS ****
    } else
    {                                  //****** TARGET BEAMS LE ATTACKER SHIELDS ****** 
      $attackershields=$attackershields-$targetbeams;          //**** A LOOSES SHIELDS EQ TO T BEAMS ****
      $targetbeams=0;                                          //**** T LOOSES ALL BEAMS ****
    }
  }
  if($targetbeams > 0)
  {                        //******** TARGET HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS ARMOR ******** 
    if($targetbeams > $attackerarmor)
    {                                 //****** TARGET BEAMS GT ATTACKER ARMOR ******
      $targetbeams=$targetbeams-$attackerarmor;                //**** T LOOSES BEAMS EQ TO A ARMOR ****
      $attackerarmor=0;                                        //**** A LOOSES ALL ARMOR (A DESTROYED) ****
    } else
    {                                 //****** TARGET BEAMS LE ATTACKER ARMOR ******
      $attackerarmor=$attackerarmor-$targetbeams;              //**** A LOOSES ARMOR EQ TO T BEAMS ****
      $targetbeams=0;                                          //**** T LOOSES ALL BEAMS ****
    } 
  }
  if($targetfighters > 0 && $attackertorpdamage > 0)
  {                        //******** ATTACKER FIRES TORPS - TARGET HAS FIGHTERS - TORPS VS FIGHTERS ********
    if($attackertorpdamage > $targetfighters)
    {                                 //****** ATTACKER FIRED TORPS GT TARGET FIGHTERS ******
      $lost=$targetfighters;
      $targetfighters=0;                                       //**** T LOOSES ALL FIGHTERS ****
      $attackertorpdamage=$attackertorpdamage-$lost;           //**** A LOOSES FIRED TORPS EQ TO T FIGHTERS ****
    } else
    {                                 //****** ATTACKER FIRED TORPS LE HALF TARGET FIGHTERS ******
      $targetfighters=$targetfighters-$attackertorpdamage;     //**** T LOOSES FIGHTERS EQ TO A TORPS FIRED ****
      $attackertorpdamage=0;                                   //**** A LOOSES ALL TORPS FIRED ****
    }
  }
  if($attackerfighters > 0 && $targettorpdmg > 0)
  {                        //******** TARGET FIRES TORPS - ATTACKER HAS FIGHTERS - TORPS VS FIGHTERS ********
    if($targettorpdmg > round($attackerfighters / 2))
    {                                 //****** TARGET FIRED TORPS GT HALF ATTACKER FIGHTERS ******
      $lost=$attackerfighters-(round($attackerfighters/2));
      $attackerfighters=$attackerfighters-$lost;               //**** A LOOSES HALF ALL FIGHTERS ****
      $targettorpdmg=$targettorpdmg-$lost;                     //**** T LOOSES FIRED TORPS EQ TO HALF A FIGHTERS ****
    } else
    {                                 //****** TARGET FIRED TORPS LE HALF ATTACKER FIGHTERS ******
      $attackerfighters=$attackerfighters-$targettorpdmg;      //**** A LOOSES FIGHTERS EQ TO T TORPS FIRED ****
      $targettorpdmg=0;                                        //**** T LOOSES ALL TORPS FIRED ****
    }
  }
  if($targettorpdmg > 0)
  {                        //******** TARGET FIRES TORPS - CONTINUE COMBAT - TORPS VS ARMOR ********
    if($targettorpdmg > $attackerarmor)
    {                                 //****** TARGET FIRED TORPS GT HALF ATTACKER ARMOR ******
      $targettorpdmg=$targettorpdmg-$attackerarmor;            //**** T LOOSES FIRED TORPS EQ TO A ARMOR ****
      $attackerarmor=0;                                        //**** A LOOSES ALL ARMOR (A DESTROYED) ****
    } else
    {                                 //****** TARGET FIRED TORPS LE HALF ATTACKER ARMOR ******
      $attackerarmor=$attackerarmor-$targettorpdmg;            //**** A LOOSES ARMOR EQ TO T TORPS FIRED ****
      $targettorpdmg=0;                                        //**** T LOOSES ALL TORPS FIRED ****
    } 
  }
  if($attackerfighters > 0 && $targetfighters > 0)
  {                        //******** ATTACKER HAS FIGHTERS - TARGET HAS FIGHTERS - FIGHTERS VS FIGHTERS ********
    if($attackerfighters > $targetfighters)
    {                                 //****** ATTACKER FIGHTERS GT TARGET FIGHTERS ******
      $temptargfighters=0;                                     //**** T WILL LOOSE ALL FIGHTERS ****
    } else
    {                                 //****** ATTACKER FIGHTERS LE TARGET FIGHTERS ******
      $temptargfighters=$targetfighters-$attackerfighters;     //**** T WILL LOOSE FIGHTERS EQ TO A FIGHTERS ****
    }
    if($targetfighters > $attackerfighters)
    {                                 //****** TARGET FIGHTERS GT ATTACKER FIGHTERS ******
      $tempplayfighters=0;                                     //**** A WILL LOOSE ALL FIGHTERS ****
    } else
    {                                 //****** TARGET FIGHTERS LE ATTACKER FIGHTERS ******
      $tempplayfighters=$attackerfighters-$targetfighters;     //**** A WILL LOOSE FIGHTERS EQ TO T FIGHTERS ****
    }     
    $attackerfighters=$tempplayfighters;
    $targetfighters=$temptargfighters;
  }
  if($targetfighters > 0)
  {                        //******** TARGET HAS FIGHTERS - CONTINUE COMBAT - FIGHTERS VS ARMOR ********
    if($targetfighters > $attackerarmor)
    {                                 //****** TARGET FIGHTERS GT ATTACKER ARMOR ******
      $attackerarmor=0;                                        //**** A LOOSES ALL ARMOR (A DESTROYED) ****
    } else
    {                                 //****** TARGET FIGHTERS LE ATTACKER ARMOR ******
      $attackerarmor=$attackerarmor-$targetfighters;           //**** A LOOSES ARMOR EQ TO T FIGHTERS ****
    }
  }

  // *********************************
  // **** FIX NEGATIVE VALUE VARS ****
  // *********************************
  if ($attackerfighters < 0) $attackerfighters = 0;
  if ($attackertorps    < 0) $attackertorps = 0;
  if ($attackershields  < 0) $attackershields = 0;
  if ($attackerbeams    < 0) $attackerbeams = 0;
  if ($attackerarmor    < 0) $attackerarmor = 0;
  if ($targetfighters   < 0) $targetfighters = 0;
  if ($targettorps      < 0) $targettorps = 0;
  if ($targetshields    < 0) $targetshields = 0;
  if ($targetbeams      < 0) $targetbeams = 0;

  // ******************************************
  // *** CHECK IF ATTACKER SHIP DESTROYED   ***
  // ******************************************
  if(!$attackerarmor>0)
  {
    playerlog($playerinfo[player_id], LOG_RAW, "Ship destroyed by planetary defenses on planet $planetinfo[name]");
    $furangeeisdead = 1;
// news
	$headline="Furangee Attacks ".$planetinfo[name]."!";
	$newstext="Furangee ".$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." led an attack today against ".$ownerinfo[character_name]."s planet ".$planetinfo[name]." but was brought down by heavy planetary defenses. ";
	if ($playerinfo[score] < $ownerinfo[score]) {
		$newstext = $newstext . $playerinfo[character_name]." was blinded by the huge riches stored on the planet.";
	} else {
		$newstext = $newstext . $playerinfo[character_name]." thought it would be easy money but underestimated the planet defenses.";
	}
	$player_id = $playerinfo[player_id];
	$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");

    $free_ore = round($shipinfo[ship_ore]/2);
    $free_organics = round($shipinfo[ship_organics]/2);
    $free_goods = round($shipinfo[ship_goods]/2);
    $ship_value=$upgrade_cost*(round(mypw($upgrade_factor, $shipinfo[hull]))+round(mypw($upgrade_factor, $shipinfo[engines]))+round(mypw($upgrade_factor, $shipinfo[power]))+round(mypw($upgrade_factor, $shipinfo[computer]))+round(mypw($upgrade_factor, $shipinfo[sensors]))+round(mypw($upgrade_factor, $shipinfo[beams]))+round(mypw($upgrade_factor, $shipinfo[torp_launchers]))+round(mypw($upgrade_factor, $shipinfo[shields]))+round(mypw($upgrade_factor, $shipinfo[armor]))+round(mypw($upgrade_factor, $shipinfo[cloak])));
    $ship_salvage_rate=rand(10,20);
    $ship_salvage=$ship_value*$ship_salvage_rate/100;
    $fighters_lost = $planetinfo[fighters] - $targetfighters;

    db_kill_player($playerinfo[player_id],$playerinfo[currentship],$planetinfo[owner]);

    // *** LOG ATTACK TO PLANET OWNER ***
    playerlog($planetinfo[owner], LOG_PLANET_NOT_DEFEATED, "$planetinfo[name]|$planetinfo[sector_id]|Furangee $playerinfo[character_name]|".NUMBER($free_ore)."|".NUMBER($free_organics)."|".NUMBER($free_goods)."|".NUMBER($ship_salvage_rate)."|".NUMBER($ship_salvage));

    // *** UPDATE PLANET ***
    $db->Execute("UPDATE $dbtables[planets] SET energy=$planetinfo[energy],fighters=fighters-$fighters_lost, torps=torps-$targettorps, ore=ore+$free_ore, goods=goods+$free_goods, organics=organics+$free_organics, credits=credits+$ship_salvage WHERE planet_id=$planetinfo[planet_id]");
  
  }
  // **********************************************
  // *** MUST HAVE MADE IT PAST PLANET DEFENSES ***
  // **********************************************
  else
  {
    $armor_lost = $shipinfo[armour_pts] - $attackerarmor;
    $fighters_lost = $shipinfo[ship_fighters] - $attackerfighters;
    $target_fighters_lost = $planetinfo[fighters] - $targetfighters;
    playerlog($playerinfo[player_id], LOG_RAW, "Made it past defenses on planet $planetinfo[name]");

    // *** UPDATE ATTACKER ***
    $db->Execute ("UPDATE $dbtables[ships] SET energy=$shipinfo[ship_energy],fighters=fighters-$fighters_lost, torps=torps-$attackertorps, armour_pts=armour_pts-$armor_lost WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    $shipinfo[ship_fighters] = $attackerfighters;
    $shipinfo[torps] = $attackertorps;
    $shipinfo[armour_pts] = $attackerarmor;


    // *** UPDATE PLANET ***
    $db->Execute ("UPDATE $dbtables[planets] SET energy=$planetinfo[energy], fighters=$targetfighters, torps=torps-$targettorps WHERE planet_id=$planetinfo[planet_id]");
    $planetinfo[fighters] = $targetfighters;
    $planetinfo[torps] = $targettorps;

    // *** NOW WE MUST ATTACK ALL SHIPS ON THE PLANET ONE BY ONE ***
	echo "Planet Info Bug tracker<br>";
	echo "Planet info planet ID = $planetinfo[planet_id]<br>";
    $resultps = $db->Execute("SELECT * FROM $dbtables[players] WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    $shipsonplanet = $resultps->RecordCount();
    if ($shipsonplanet > 0)
    {
      while (!$resultps->EOF && $furangeeisdead < 1)
      {
        $onplanet = $resultps->fields;
        furangeetoship($onplanet[player_id]);
        $resultps->MoveNext();
      }
    }
    $resultps = $db->Execute("SELECT * FROM $dbtables[players] WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    $shipsonplanet = $resultps->RecordCount();
    if ($shipsonplanet == 0 && $furangeeisdead < 1)
    {
      // *** MUST HAVE KILLED ALL SHIPS ON PLANET ***
      playerlog($playerinfo[player_id], LOG_RAW, "Defeated all ships on planet $planetinfo[name]");
      // *** LOG ATTACK TO PLANET OWNER ***
      playerlog($planetinfo[owner], LOG_PLANET_DEFEATED, "$planetinfo[name]|$planetinfo[sector_id]|Furangee $playerinfo[character_name]");
	  // news
	  $headline="Furangee Defeat Planet ".$planetinfo[name]."!";
	  $newstext="Furangee ".$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." defeated ".$ownerinfo[character_name]."s planet ".$planetinfo[name].". ";
		if ($playerinfo[score] < $ownerinfo[score]) {
			$newstext = $newstext . $playerinfo[character_name]." mentioned that the bigger they are, the easier the fall.";
		} else {
			$newstext = $newstext . $playerinfo[character_name]." took the weak out and stamped on them.";
		}
		$player_id = $playerinfo[player_id];
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");

      // *** UPDATE PLANET ***
      $db->Execute("UPDATE $dbtables[planets] SET fighters=0, torps=0, base='N', owner=0, corp=0 WHERE planet_id=$planetinfo[planet_id]"); 
      calc_ownership($planetinfo[sector_id]);

    } else {
      // *** MUST HAVE DIED TRYING ***
      playerlog($playerinfo[player_id], LOG_RAW, "We were KILLED by ships defending planet $planetinfo[name]");
		// news
		$headline="Furangee burns up in attack!";
		$newstext="Furangee ".$playerinfo[character_name]." got intimate with deep space today when he tried to attack ".$ownerinfo[character_name]."s planet ".$planetinfo[name].". ";
		if ($playerinfo[score] < $ownerinfo[score]) {
			$newstext = $newstext . $playerinfo[character_name]." felt like his time had come and in a note left on the Intergalactic Bulletin Board he said that he wanted to go out in at least a modicum of style.";
		} else {
			$newstext = $newstext . $playerinfo[character_name]." was blinded by his own feelings of superiority.";
		}
		$player_id = $playerinfo[player_id];
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");
      // *** LOG ATTACK TO PLANET OWNER ***
      playerlog($planetinfo[owner], LOG_PLANET_NOT_DEFEATED, "$planetinfo[name]|$planetinfo[sector_id]|Furangee $playerinfo[character_name]|0|0|0|0|0");

      // *** NO SALVAGE FOR PLANET BECAUSE WENT TO SHIP WHO WON **
    }

  }


  // *** END OF FURANGEE PLANET ATTACK CODE ***
  //$db->Execute("UNLOCK TABLES");

}


?>
