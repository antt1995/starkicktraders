<?
  
include("config.php");
updatecookie();
include("languages/$lang");

connectdb();



$title=$l_att_title;
include("header.php");

if(checklogin())
{
  die();
}

//-------------------------------------------------------------------------------------------------
//$db->Execute("LOCK TABLES $dbtables[players] WRITE, $dbtables[ships] WRITE, $dbtables[universe] WRITE, $dbtables[bounty] WRITE, $dbtables[zones] READ, $dbtables[planets] WRITE, $dbtables[news] WRITE, $dbtables[logs] WRITE, $dbtables[ship_types] READ");
$result = $db->Execute ("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo=$result->fields;
// Find out what my ship is
$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship] LIMIT 1");
$shipinfo = $res->fields;

$ship_id = stripnum($ship_id);

$res = $db->Execute("SELECT * FROM $dbtables[ship_types],$dbtables[ships] WHERE $dbtables[ship_types].type_id=$dbtables[ships].type AND  $dbtables[ships].ship_id=$ship_id LIMIT 1");
$targetshipinfo = $res->fields;
$result2 = $db->Execute ("SELECT * FROM $dbtables[players] WHERE player_id=$targetshipinfo[player_id]");
$targetinfo=$result2->fields;

$playerscore = gen_score($playerinfo[player_id]);
$targetscore = gen_score($targetinfo[player_id]);

//$playerscore = $playerscore * $playerscore;
//$targetscore = $targetscore * $targetscore;

bigtitle();

srand((double)microtime()*1000000);

/* check to ensure target is in the same sector as player */
if($targetinfo[sector] != $playerinfo[sector] || $targetinfo[on_planet] == "Y" || $targetshipinfo[sector] != $playerinfo[sector])
{
  echo "$l_att_notarg<BR><BR>";
}
elseif($playerinfo[turns] < 1)
{
  echo "$l_att_noturn<BR><BR>";
}
else
{
  /* determine percent chance of success in detecting target ship - based on player's sensors and opponent's cloak */
  // Cloak and engines are degraded if the target is towing something
  if ($targetshipinfo[tow]>0) {
  	$targetshipinfo[cloak] = min($targetshipinfo[cloak]-1,0);
	$targetshipinfo[engines] = min($targetshipinfo[engines]-1,0);
  }
  // My engines are degraded if I am towing something
  if ($shipinfo[tow]>0) {
	$shipinfo[engines] = min($shipinfo[engines]-1,0);
  }
  $success = (10 - $targetshipinfo[cloak] + $shipinfo[sensors]) * 5;
  if($success < 5)
  {
    $success = 5;
  }
  if($success > 95)
  {
    $success = 95;
  }
  $flee = (10 - $targetshipinfo[engines] + $shipinfo[engines]) * 5;
  $roll = rand(1, 100);
  $roll2 = rand(1, 100);

  $res = $db->Execute("SELECT allow_attack,$dbtables[universe].zone_id FROM $dbtables[zones],$dbtables[universe] WHERE sector_id='$targetinfo[sector]' AND $dbtables[zones].zone_id=$dbtables[universe].zone_id");
  $zoneinfo = $res->fields;
  if($zoneinfo[allow_attack] == 'N')
  {
    echo "$l_att_noatt<BR><BR>";
  }
  elseif($flee < $roll2)
  {
    echo "$l_att_flee<BR><BR>";
    $db->Execute("UPDATE $dbtables[players] SET turns=turns-1,turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
    playerlog($targetinfo[player_id], LOG_ATTACK_OUTMAN, "$playerinfo[character_name]");
  }
  elseif($roll > $success)
  {
    /* if scan fails - inform both player and target. */
    echo "$l_planet_noscan<BR><BR>";
    $db->Execute("UPDATE $dbtables[players] SET turns=turns-1,turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
    playerlog($targetinfo[player_id], LOG_ATTACK_OUTSCAN, "$playerinfo[character_name]");
  }
  else
  {
    /* if scan succeeds, show results and inform target. */
	$shipavg = $targetshipinfo[hull] + $targetshipinfo[engines] + $targetshipinfo[power] + $targetshipinfo[computer] + $targetshipinfo[sensors] + $targetshipinfo[armour] + $targetshipinfo[shields] + $targetshipinfo[beams] + $targetshipinfo[torp_launchers] + $targetshipinfo[cloak];
	$shipavg /= 10;
	// Add the extra power of the "Emergency Warp Vice" - this is part of the WMD and makes it even less likely an EWD will fire.
	if ($shipinfo[dev_sectorwmd] == 'Y') {
		echo "Emergency Warp Vice activated!<br>";
		$shipavg += 5;
	}	
    if($shipavg > $ewd_maxhullsize)
    {
       $chance = ($shipavg - $ewd_maxhullsize) * 10;
    }
    else
    {
       $chance = 0;
    }
    $random_value = rand(1,100);
	//echo "\n\n<!-- DEBUG $targetshipinfo[dev_emerwarp], $random_value, $chance = ($shipavg - $ewd_maxhullsize) * 10; END -->\n\n";
    if($targetshipinfo[dev_emerwarp] > 0 && $random_value > $chance)
    {
      /* need to change warp destination to random sector in universe */
	  $dest_sector=rand(1,$sector_max);
	  $result_warp = $db->Execute ("UPDATE $dbtables[players] SET sector=$dest_sector,cleared_defences=' ' WHERE player_id=$targetinfo[player_id] AND sector=$playerinfo[sector]");
	  // Move ship and towed ship too
	  $result_warp = $db->Execute ("UPDATE $dbtables[ships] SET sector=$dest_sector, dev_emerwarp=dev_emerwarp-1 WHERE player_id=$targetinfo[player_id] AND (ship_id=$targetinfo[currentship] OR ship_id=$targetshipinfo[tow]) AND sector=$playerinfo[sector]");
	  $db->Execute("UPDATE $dbtables[players] SET turns=turns-1,turns_used=turns_used+1,rating=rating-$rating_change WHERE player_id=$playerinfo[player_id]");
	  $l_att_ewdlog=str_replace("[name]",$playerinfo[character_name],$l_att_ewdlog);
	  $l_att_ewdlog=str_replace("[sector]",$playerinfo[sector],$l_att_ewdlog);
	  log_move($targetinfo[player_id],$dest_sector);
	  $rating_change=floor($targetinfo[rating]*.1);
	  playerlog($targetinfo[player_id], LOG_ATTACK_EWD, "$playerinfo[character_name]");
      echo "$l_att_ewd<BR><BR>";
    }
    else
    {
      if( ($targetscore / $playerscore < $bounty_ratio || $targetinfo[turns_used] < $bounty_minturns) && substr($targetinfo[email], -8) != "furangee" && $targetscore < 500000 ) 
      {
         // Check to see if there is Federation fine on the player. If there is, people can attack regardless.
         $btyamount = 0;
         $hasbounty = $db->Execute("SELECT SUM(amount) AS btytotal FROM $dbtables[bounty] WHERE bounty_on = $targetinfo[player_id] AND placed_by = 0");
         if($hasbounty)
         {
            $resx = $hasbounty->fields;
            $btyamount = $resx[btytotal];
         }
         if($btyamount <= 0) 
         {
		 	$bounty = ROUND($playerscore * $playerscore * $bounty_maxvalue);
			// Let's find out if the Feds have done this already
			//echo "DEBUG: SELECT SUM(amount) AS btytotal FROM $dbtables[bounty] WHERE bounty_on=$playerinfo[ship_id] AND placed_by =0<br>";
			$hasbounty2 = $db->Execute("SELECT SUM(amount) AS btytotal FROM $dbtables[bounty] WHERE bounty_on=$playerinfo[player_id] AND placed_by =0");
			$resy = $hasbounty2->fields;
			$btyamount2 = $resy[btytotal];
			//echo "DEBUG: Bounty = $bountyCurrent bounty = $btyamount2<br>";
			if ($bounty > $btyamount2 && $btyamount2 != NULL) {
				$bounty = $bounty - $btyamount2; // Only increase it to current %age level
				$insert = $db->Execute("INSERT INTO $dbtables[bounty] (bounty_on,placed_by,amount) values ($playerinfo[player_id], 0 ,$bounty)");
				playerlog($playerinfo[player_id],LOG_BOUNTY_FEDBOUNTY,"$bounty");
				playerlog(1,LOG_RAW,"Additional fine of ".NUMBER($bounty)." placed on $playerinfo[character_name] score $playerscore for attacking $targetinfo[character_name] score $targetscore");
				echo "The Federation added to your fine!<BR><BR>";
			} else if ($btyamount2 != NULL) {
				playerlog($playerinfo[player_id],LOG_RAW,"The Federation kept their fine on you at its current level.");
				playerlog(1,LOG_RAW,"Kept fine on $playerinfo[character_name] score $playerscore for attacking $targetinfo[character_name] score $targetscore");
				echo "The Federation retained their fine on you at its current level.<BR><BR>";
			}
			else
			{
				//echo "INSERT INTO $dbtables[bounty] (bounty_on,placed_by,amount) values ($playerinfo[player_id], 0 ,$bounty)<br>";
				$insert = $db->Execute("INSERT INTO $dbtables[bounty] (bounty_on,placed_by,amount) values ($playerinfo[player_id], 0 ,$bounty)");
				playerlog($playerinfo[ship_id],LOG_BOUNTY_FEDBOUNTY,"$bounty");
				playerlog(1,LOG_RAW,"Initial fine of ".NUMBER($bounty)." placed on $playerinfo[character_name] score $playerscore for attacking $targetinfo[character_name] score $targetscore");
				echo $l_by_fedbounty2 . "<BR><BR>";
				
			}
			$headline=$playerinfo[character_name]." wanted by the Federation!";
			$newstext=addslashes("The Federation declared $playerinfo[character_name] a fugitive of justice today. Any bounty hunter destroying a ship owned by $playerinfo[character_name] will now get triple the salvage until $playerinfo[character_name] pays off the fine.");
			$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$playerinfo[player_id]',NOW(), 'attack')");
         }
      }
      if($targetshipinfo[dev_emerwarp] > 0)
      {
        playerlog($targetinfo[player_id], LOG_ATTACK_EWDFAIL, $playerinfo[character_name]);
      }
	  // ********* Now start the combat calcs *************
      $targetbeams = NUM_BEAMS($targetshipinfo[beams]);
      if($targetbeams>$targetshipinfo[ship_energy])
      {
        $targetbeams=$targetshipinfo[ship_energy];
      }
      $targetshipinfo[ship_energy]=$targetshipinfo[ship_energy]-$targetbeams;
      $playerbeams = NUM_BEAMS($shipinfo[beams]);
      if($playerbeams>$shipinfo[ship_energy])
      {
        $playerbeams=$shipinfo[ship_energy];
      }
      $shipinfo[ship_energy]=$shipinfo[ship_energy]-$playerbeams;
      $playershields = NUM_SHIELDS($shipinfo[shields]);
      if($playershields>$shipinfo[ship_energy])
      {
        $playershields=$shipinfo[ship_energy];
      }
      $shipinfo[ship_energy]=$shipinfo[ship_energy]-$playershields;
      $targetshields = NUM_SHIELDS($targetshipinfo[shields]);
      if($targetshields>$targetshipinfo[ship_energy])
      {
        $targetshields=$targetshipinfo[ship_energy];
      }
      $targetshipinfo[ship_energy]=$targetshipinfo[ship_energy]-$targetshields;

      $playertorpnum = floor(mypw($level_factor,$shipinfo[torp_launchers]))*2;
      if($playertorpnum > $shipinfo[torps])
      {
        $playertorpnum = $shipinfo[torps];
      }
      $targettorpnum = floor(mypw($level_factor,$targetshipinfo[torp_launchers]))*2;
      if($targettorpnum > $targetshipinfo[torps])
      {
        $targettorpnum = $targetshipinfo[torps];
      }
      $playertorpdmg = $torp_dmg_rate*$playertorpnum;
      $targettorpdmg = $torp_dmg_rate*$targettorpnum;
      $playerarmour = $shipinfo[armour_pts];
      $targetarmour = $targetshipinfo[armour_pts];
      $playerfighters = $shipinfo[ship_fighters];
      $targetfighters = $targetshipinfo[ship_fighters];
      $targetdestroyed = 0;
      $playerdestroyed = 0;
      echo "$l_att_att $targetinfo[character_name]$l_abord $targetshipinfo[ship_name]:<BR><BR>";
      echo "<h2>$l_att_beams</h2>";
	  
	  //************ Now we start the battle **************
	  
      if($targetfighters > 0 && $playerbeams > 0)
      {
        if($playerbeams > floor($targetshipinfo[ship_fighters] / 2))
        {
          $temp = floor($targetshipinfo[ship_fighters]/2);
          $lost = $targetfighters-$temp;
          echo "You take out ".NUMBER($lost)." of $targetinfo[character_name]'s fighters with your beams!<BR>";
          $targetfighters = $temp;
          $playerbeams = $playerbeams-$lost;
        }
        else
        {
          $targetfighters = $targetfighters-$playerbeams;
          echo "You take out ".NUMBER($playerbeams)." of $targetinfo[character_name]'s fighters with your beams!<BR>";
          $playerbeams = 0;
        }
      }
      if($playerfighters > 0 && $targetbeams > 0)
      {
        if($targetbeams > floor($shipinfo[ship_fighters] / 2))
        {
          $temp=floor($shipinfo[ship_fighters]/2);
          $lost=$playerfighters-$temp;
          echo "$targetinfo[character_name]'s beams cut down ".NUMBER($lost)." of your fighters!<BR>";
          $playerfighters-=$temp;
          $targetbeams=$targetbeams-$lost;
        }
        else
        {
          $playerfighters=$shipinfo[ship_fighters]-$targetbeams;
          echo "$targetinfo[character_name]'s beams cut down ".NUMBER($targetbeams)." of your fighters!<BR>";
          $targetbeams=0;
        }
      }
      if($playerbeams > 0)
      {
        if($playerbeams > $targetshields)
        {
          $playerbeams=$playerbeams-$targetshields;
          $targetshields=0;
          echo "$targetinfo[character_name] $l_att_sdown!<BR>";
        }
        else
        {
          echo "$targetinfo[character_name]" . $l_att_shits ." ".NUMBER($playerbeams)." $l_att_dmg but they hold up!<BR>";
          $targetshields=$targetshields-$playerbeams;
          $playerbeams=0;
        }
      }
      if($targetbeams > 0)
      {
        if($targetbeams > $playershields)
        {
          $targetbeams=$targetbeams-$playershields;
          $playershields=0;
          echo "$l_att_ydown!<BR>";
        }
        else
        {
          echo "$l_att_yhits ".NUMBER($targetbeams)." $l_att_dmg but hold up!<BR>";
          $playershields=$playershields-$targetbeams;
          $targetbeams=0;
        }
      }
      if($playerbeams > 0)
      {
        if($playerbeams > $targetarmour)
        {
          $targetarmour=0;
          echo "$targetinfo[character_name] " .$l_att_sarm ."!<BR>";
        }
        else
        {
          $targetarmour=$targetarmour-$playerbeams;
          echo "$targetinfo[character_name]". $l_att_ashit ." .".NUMBER($playerbeams)." $l_att_dmg!<BR>";
        }
      }
      if($targetbeams > 0)
      {
        if($targetbeams > $playerarmour)
        {
          $playerarmour=0;
          echo "$l_att_yarm!<BR>";
        }
        else
        {
          $playerarmour=$playerarmour-$targetbeams;
          echo "$l_att_ayhit ".NUMBER($targetbeams)." $l_att_dmg!<BR>";
        }
      }
	  // ************** Torpedoes Attack ***********************8
      echo "<BR><h2>$l_att_torps</h2><BR>";
      if($targetshipinfo[ship_fighters] > 0 && $playertorpdmg > 0)
      {
	    // Correction - torps can take out up to half of initial attack!
        if($playertorpdmg > floor($targetshipinfo[ship_fighters] / 2))
        {
          $lost=floor($targetshipinfo[ship_fighters]/2);
          //$lost=$targetfighters-$temp;
          echo "Your torpedoes incinerate ".NUMBER($lost)." of $targetinfo[character_name]'s fighters!<BR>";
          $targetfighters-=$lost;
          $playertorpdmg-=$lost;
        }
        else
        {
          $targetfighters=$targetfighters-$playertorpdmg;
          echo "Your torpedoes incinerate ".NUMBER($playertorpdmg)." of $targetinfo[character_name]'s fighters!<BR>";
          $playertorpdmg=0;
        }
      }
      if($shipinfo[ship_fighters] > 0 && $targettorpdmg > 0)
      {
        if($targettorpdmg > floor($shipinfo[ship_fighters] / 2))
        {
          $lost=floor($shipinfo[ship_fighters]/2);
          //$lost=$playerfighters-$temp;
          echo "$l_att_ylost ".NUMBER($lost)." fighters from $targetinfo[character_name]'s torpedo attack!<BR>";
          $playerfighters-=$lost;
          $targettorpdmg=$targettorpdmg-$lost;
        }
        else
        {
          $playerfighters=$playerfighters-$targettorpdmg;
          echo "$l_att_ylost ".NUMBER($targettorpdmg)." fighters from $targetinfo[character_name]'s torpedo attack!<BR><BR>";
          $targettorpdmg=0;
        }
      }
      if($playertorpdmg > 0)
      {
        if($playertorpdmg > $targetarmour)
        {
          $targetarmour=0;
          echo "$targetinfo[character_name] $l_att_sarm!<BR>";
        }
        else
        {
          $targetarmour=$targetarmour-$playertorpdmg;
          echo "$targetinfo[character_name]" . $l_att_ashit . " ".NUMBER($playertorpdmg)." $l_att_dmg!<BR>";
        }
      }
      if($targettorpdmg > 0)
      {
        if($targettorpdmg > $playerarmour)
        {
          $playerarmour=0;
          echo "$l_att_yarm!!!<BR>";
        }
        else
        {
          $playerarmour=$playerarmour-$targettorpdmg;
          echo "$l_att_ayhit ".NUMBER($targettorpdmg)." $l_att_dmg but still holds!<BR>";
        }
      }
	  // ********************************* Fighters attack ******************
      echo "<BR><h2>$l_att_fighters</h2>";
	  if ($playerfighters>0) {
	  	if ($playerfighters==1) {
			echo "You have a single fighter remaining to take on the enemy!<br>";
		} else {
	  		echo "You have ".NUMBER($playerfighters)." fighters left to attack.<br>";
		}
	  } else {
	  	echo "You do not have any fighters left to attack!<br>";
	  }
	  if ($targetfighters>0) {
	  	if ($targetfighters==1) {
			echo "One enemy fighter is left.<br>";
		} else {
	  		echo "The enemy has ".NUMBER($targetfighters)." fighters left to attack you.<br>";
		}
	  } else {
	  	echo "$targetinfo[character_name] does not have any fighters left!<br>";
		//echo "He has ".NUMBER($targetfighters)." left";
	  }
	  echo "<br>";
      if($playerfighters > 0 && $targetfighters > 0)
      {
        if($playerfighters > $targetfighters)
        {
          echo "$targetinfo[character_name] $l_att_lostf!<BR>";
          $temptargfighters=0;
        }
        else
        {
          echo "$targetinfo[character_name] $l_att_lost ".NUMBER($targetfighters)." Fighters!<BR>";
          $temptargfighters=$targetfighters-$playerfighters;
        }
        if($targetfighters > $playerfighters)
        {
          echo "You lose all your Fighters!<BR>";
          $tempplayfighters=0;
        }
        else
        {
          echo "$l_att_ylost ".NUMBER($targetfighters)." Fighters!<BR>";
          $tempplayfighters=$playerfighters-$targetfighters;
        }
        $playerfighters=$tempplayfighters;
        $targetfighters=$temptargfighters;
      }
      if($playerfighters > 0)
      {
        if($playerfighters > $targetarmour)
        {
		  $playerfighters -= $targetarmour;
          $targetarmour=0;
          echo "$targetinfo[character_name] $l_att_sarm!!!<BR>";
        }
        else
        {
			echo "$targetinfo[character_name]" . $l_att_ashit ." ".NUMBER($playerfighters)." $l_att_dmg.<BR>";
			$targetarmour=$targetarmour-$playerfighters;
          	$playerfighters = 0;
        }
      }
      if($targetfighters > 0)
      {
        if($targetfighters > $playerarmour)
        {
          $playerarmour=0;
          echo "$l_att_yarm!!!<BR>";
        }
        else
        {
          $playerarmour=$playerarmour-$targetfighters;
          echo "$l_att_ayhit ".NUMBER($targetfighters)." $l_att_dmg but it holds!<BR>";
        }
      }
      if($targetarmour < 1)
      {
        echo "<BR><h2>$targetinfo[character_name] $l_att_sdest</h2>";
        if($targetshipinfo[dev_escapepod] == "Y")
        {
		  // news - target's ship destroyed but escaped'
		  // news
		$headline="Ship Destroyed!";
		$newstext=$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." destroyed ".$targetinfo[character_name]."'s ship but ". $targetinfo[character_name]." shot into space in an escape pod. ";
		if ($playerinfo[rating] < $targetinfo[rating]) {
			$newstext = $newstext . $playerinfo[character_name]." crushed the weak.";
		} else {
			$newstext = $newstext . $playerinfo[character_name]." made the universe a slightly better place.";
		}
		$player_id = $playerinfo[player_id];
		$newstext=addslashes($newstext);
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");

          $rating=floor($targetinfo[rating]/2);
          echo "$l_att_espod<BR><BR>";
          $db->Execute("UPDATE $dbtables[players] SET on_planet='N',rating='$rating',cleared_defences=' ',sector=0 WHERE player_id=$targetinfo[player_id]");
		  playerlog($targetinfo[player_id], LOG_ATTACK_LOSE, "$playerinfo[character_name]|Y");
		  // Mark ship as destroyed and make any towed ship unowned
		  if ($targetshipinfo[tow] > 0) {
		  	$db->Execute("UPDATE $dbtables[ships] SET player_id=0,on_planet='N',sector=$target_ship[sector] WHERE ship_id=$targetshipinfo[tow]");
		  }
		  $db->Execute("UPDATE $dbtables[ships] SET ship_destroyed='Y', sector=0, tow=0 WHERE player_id=$targetinfo[player_id] AND ship_id=$targetinfo[currentship]");
		  // Collect the bounty if any        
          collect_bounty($playerinfo[player_id],$targetinfo[player_id]);
        }
        else
        {
		  // news target died in attack, no escape pod
		  // news
		$headline="Ship Destroyed!";
		$newstext=$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." destroyed ".$targetinfo[character_name]."'s ship and ". $targetinfo[character_name]." went with it! ";
		if ($playerinfo[rating] < $targetinfo[rating]) {
			$newstext = $newstext . $playerinfo[character_name]." crushed the weak.";
		} else {
			$newstext = $newstext . $playerinfo[character_name]." made the universe a slightly better place.";
		}
		$player_id = $playerinfo[player_id];
		$newstext=addslashes($newstext);
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");
          playerlog($targetinfo[player_id], LOG_ATTACK_LOSE, "$playerinfo[character_name]|N");
          db_kill_player($targetinfo[player_id],$targetinfo[currentship],$playerinfo[player_id]);
          collect_bounty($playerinfo[player_id],$targetinfo[player_id]);
        }

        if($playerarmour > 0)
        {
		  // news - attacking player destroyed the opponent's ship'
          $rating_change=floor($targetinfo[rating]*$rating_combat_factor);
          $free_ore = floor($targetshipinfo[ship_ore]/2);
          $free_organics = floor($targetshipinfo[ship_organics]/2);
          $free_goods = floor($targetshipinfo[ship_goods]/2);
          $free_holds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[ship_colonists];
          if($free_holds > $free_goods)
          {
            $salv_goods=$free_goods;
            $free_holds=$free_holds-$free_goods;
          }
          elseif($free_holds > 0)
          {
            $salv_goods=$free_holds;
            $free_holds=0;
          }
          else
          {
            $salv_goods=0;
          }
          if($free_holds > $free_ore)
          {
            $salv_ore=$free_ore;
            $free_holds=$free_holds-$free_ore;
          }
          elseif($free_holds > 0)
          {
            $salv_ore=$free_holds;
            $free_holds=0;
          }
          else
          {
            $salv_ore=0;
          }
          if($free_holds > $free_organics)
          {
            $salv_organics=$free_organics;
            $free_holds=$free_holds-$free_organics;
          }
          elseif($free_holds > 0)
          {
            $salv_organics=$free_holds;
            $free_holds=0;
          }
          else
          {
            $salv_organics=0;
          }
          $ship_value=$upgrade_cost*(floor(mypw($upgrade_factor, $targetshipinfo[hull]))+floor(mypw($upgrade_factor, $targetshipinfo[engines]))+floor(mypw($upgrade_factor, $targetshipinfo[power]))+floor(mypw($upgrade_factor, $targetshipinfo[computer]))+floor(mypw($upgrade_factor, $targetshipinfo[sensors]))+floor(mypw($upgrade_factor, $targetshipinfo[beams]))+floor(mypw($upgrade_factor, $targetshipinfo[torp_launchers]))+floor(mypw($upgrade_factor, $targetshipinfo[shields]))+floor(mypw($upgrade_factor, $targetshipinfo[armour]))+floor(mypw($upgrade_factor, $targetshipinfo[cloak])));
          $ship_salvage_rate=rand(10,20);
          $ship_salvage=$ship_value*$ship_salvage_rate/100;
		  // Triple salvage if this is an outlaw
		  $result3 = $db->Execute ("SELECT amount FROM $dbtables[bounty] WHERE placed_by=0 AND bounty_on=$targetinfo[player_id]");
		  if (!$result3->EOF) {
		  	$ship_salvage *=3;
			echo "You destroyed a Federation fugitive! Triple salvage!<br>";
		  }
		  // 75% of salvage if a furangee
		  if (strpos($targetinfo[email],"@furangee")) {
		  	$ship_salvage *=.75;
		  }
          $l_att_ysalv=str_replace("[salv_ore]",NUMBER($salv_ore),$l_att_ysalv);
          $l_att_ysalv=str_replace("[salv_organics]",NUMBER($salv_organics),$l_att_ysalv);
          $l_att_ysalv=str_replace("[salv_goods]",NUMBER($salv_goods),$l_att_ysalv);
          $l_att_ysalv=str_replace("[ship_salvage_rate]",$ship_salvage_rate,$l_att_ysalv);
          $l_att_ysalv=str_replace("[ship_salvage]",NUMBER($ship_salvage),$l_att_ysalv);
          $l_att_ysalv=str_replace("[rating_change]",NUMBER(abs($rating_change)),$l_att_ysalv);

          echo $l_att_ysalv;
		  $armour_lost=$shipinfo[armour_pts]-$playerarmour;
          $fighters_lost=$shipinfo[ship_fighters]-$playerfighters;
          $energy=$shipinfo[ship_energy];
          $update3 = $db->Execute ("UPDATE $dbtables[players] SET credits=credits+$ship_salvage,turns=turns-1, turns_used=turns_used+1, rating=rating-$rating_change WHERE player_id=$playerinfo[player_id]");
		  $update3 = $db->Execute ("UPDATE $dbtables[ships] SET ship_ore=ship_ore+$salv_ore, ship_organics=ship_organics+$salv_organics, ship_goods=ship_goods+$salv_goods,ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, armour_pts=armour_pts-$armour_lost, torps=torps-$playertorpnum WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
          echo "$l_att_ylost ".NUMBER($armour_lost)." $l_armourpts, ".NUMBER($fighters_lost)." Fighters, $l_att_andused ".NUMBER($playertorpnum)." $l_torps in this battle.<BR><BR>";
        }
      }
      else
      {
	  	// ************ Target survived attack *********
       $l_att_stilship=str_replace("[name]",$targetinfo[character_name],$l_att_stilship);
        echo "<br><h2>$l_att_stilship</h2>";
        $rating_change=floor($targetinfo[rating]*.1);
        $armour_lost=$targetshipinfo[armour_pts]-$targetarmour;
        $fighters_lost=$targetshipinfo[ship_fighters]-$targetfighters;
        $energy=$targetshipinfo[ship_energy];
        playerlog($targetinfo[player_id], LOG_ATTACKED_WIN, "$playerinfo[character_name]|$armour_lost|$fighters_lost");
        $update4 = $db->Execute ("UPDATE $dbtables[ships] SET ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, armour_pts=armour_pts-$armour_lost, torps=torps-$targettorpnum WHERE player_id=$targetinfo[player_id] and ship_id=$targetinfo[currentship]");

		// ******* Update attacker ********************
        $armour_lost=$shipinfo[armour_pts]-$playerarmour;
        $fighters_lost=$shipinfo[ship_fighters]-$playerfighters;
        $energy=$shipinfo[ship_energy];
        $update4b = $db->Execute ("UPDATE $dbtables[players] SET turns=turns-1, turns_used=turns_used+1, rating=rating-$rating_change WHERE player_id=$playerinfo[player_id]");
		$update4b = $db->Execute ("UPDATE $dbtables[ships] SET ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, armour_pts=armour_pts-$armour_lost, torps=torps-$playertorpnum WHERE player_id=$playerinfo[player_id] and ship_id=$playerinfo[currentship]");
        echo "<b>$l_att_ylost ".NUMBER($armour_lost)." $l_armourpts, ".NUMBER($fighters_lost)." Fighters, $l_att_andused ".NUMBER($playertorpnum)." $l_torps in this battle.</b><BR><BR>";
		
		if ($playerarmour > 0) {
			// news - this was a skirmish that ended in a loss of fighters and armor
		  	// news
			$headline="Space Attack!";
			$newstext=$playerinfo[character_name]." and ".$targetinfo[character_name]." clashed today in an indecisive ship attack.";
			$player_id = $playerinfo[player_id];
			$newstext=addslashes($newstext);
			$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");
		}
      }
      if($playerarmour < 1)
      {
        echo "<br><h2>$l_att_yshiplost</h2><BR>";
        if($shipinfo[dev_escapepod] == "Y")
        {
          $rating=floor($playerinfo[rating]/2);
          echo "$l_att_loosepod<BR><BR>";
          $db->Execute("UPDATE $dbtables[players] SET sector=0,on_planet='N',rating='$rating' WHERE player_id=$playerinfo[player_id]");
		  // Immediately release any ships being towed 
    		if ($shipinfo[tow] > 0) {
				$db->Execute("UPDATE $dbtables[ships] SET player_id=0,on_planet='N',sector=$shipinfo[sector] WHERE ship_id=$shipinfo[tow]");
  			}

          $db->Execute("UPDATE $dbtables[ships] SET ship_destroyed='Y', sector=0, tow=0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
          collect_bounty($targetinfo[player_id],$playerinfo[player_id]);
		  // news - the player lost but had an escape pod
		  		  // news
		$headline="Failed ship attack!";
		$newstext=$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." attacked ".$targetinfo[character_name]."'s ship but was overcome by ".$targetinfo[character_name]."'s defenses. Escape pod was deployed.";
		$player_id = $playerinfo[player_id];
		$newstext=addslashes($newstext);
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");

        }
        else
        {
		$headline="Failed ship attack!";
		$newstext=$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." attacked ".$targetinfo[character_name]."'s ship but was overcome by ".$targetinfo[character_name]."'s defenses and was vapourized.";
		$player_id = $playerinfo[player_id];
		$newstext=addslashes($newstext);
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");
          db_kill_player($playerinfo[player_id],$playerinfo[currentship],$targetinfo[player_id]);
          collect_bounty($targetinfo[player_id],$playerinfo[player_id]);
		  // news player died due to no escape pod
        }
        if($targetarmour > 0)
        {
		  // news opponent won when other player attacked
          $free_ore = floor($shipinfo[ship_ore]/2);
          $free_organics = floor($shipinfo[ship_organics]/2);
          $free_goods = floor($shipinfo[ship_goods]/2);
          $free_holds = NUM_HOLDS($targetshipinfo[hull]) - $targetshipinfo[ship_ore] - $targetshipinfo[ship_organics] - $targetshipinfo[ship_goods] - $targetshipinfo[ship_colonists];
          if($free_holds > $free_goods)
          {
            $salv_goods=$free_goods;
            $free_holds=$free_holds-$free_goods;
          }
          elseif($free_holds > 0)
          {
            $salv_goods=$free_holds;
            $free_holds=0;
          }
          else
          {
            $salv_goods=0;
          }
          if($free_holds > $free_ore)
          {
            $salv_ore=$free_ore;
            $free_holds=$free_holds-$free_ore;
          }
          elseif($free_holds > 0)
          {
            $salv_ore=$free_holds;
            $free_holds=0;
          }
          else
          {
            $salv_ore=0;
          }
          if($free_holds > $free_organics)
          {
            $salv_organics=$free_organics;
            $free_holds=$free_holds-$free_organics;
          }
          elseif($free_holds > 0)
          {
            $salv_organics=$free_holds;
            $free_holds=0;
          }
          else
          {
            $salv_organics=0;
          }
          $ship_value=$upgrade_cost*(floor(mypw($upgrade_factor, $shipinfo[hull]))+floor(mypw($upgrade_factor, $shipinfo[engines]))+floor(mypw($upgrade_factor, $shipinfo[power]))+floor(mypw($upgrade_factor, $shipinfo[computer]))+floor(mypw($upgrade_factor, $shipinfo[sensors]))+floor(mypw($upgrade_factor, $shipinfo[beams]))+floor(mypw($upgrade_factor, $shipinfo[torp_launchers]))+floor(mypw($upgrade_factor, $shipinfo[shields]))+floor(mypw($upgrade_factor, $shipinfo[armour]))+floor(mypw($upgrade_factor, $shipinfo[cloak])));
          $ship_salvage_rate=rand(10,20);
          $ship_salvage=$ship_value*$ship_salvage_rate/100;
		  // Triple salvage if you were an outlaw
		  $result3 = $db->Execute ("SELECT amount FROM $dbtables[bounty] WHERE placed_by=0 AND bounty_on=$playerinfo[player_id]");
		  if (!$result3->EOF) {
		  	echo "Triple outlaw salvage bonus for $targetinfo[character_name]!<br>";
		  	$ship_salvage *=3;
		  }

          $l_att_salv=str_replace("[salv_ore]",NUMBER($salv_ore),$l_att_salv);
          $l_att_salv=str_replace("[salv_organics]",NUMBER($salv_organics),$l_att_salv);
          $l_att_salv=str_replace("[salv_goods]",NUMBER($salv_goods),$l_att_salv);
          $l_att_salv=str_replace("[ship_salvage_rate]",$ship_salvage_rate,$l_att_salv);
          $l_att_salv=str_replace("[ship_salvage]",NUMBER($ship_salvage),$l_att_salv);
          $l_att_salv=str_replace("[name]",$targetinfo[character_name],$l_att_salv);

          echo "<b>$l_att_salv</b><BR>";
          $update6 = $db->Execute ("UPDATE $dbtables[players] SET credits=credits+$ship_salvage WHERE player_id=$targetinfo[player_id]");
          $armour_lost=$targetshipinfo[armour_pts]-$targetarmour;
          $fighters_lost=$targetshipinfo[ship_fighters]-$targetfighters;
          $energy=$targetshipinfo[ship_energy];
          $update6b = $db->Execute ("UPDATE $dbtables[ships] SET ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, armour_pts=armour_pts-$armour_lost, torps=torps-$targettorpnum,ship_ore=ship_ore+$salv_ore, ship_organics=ship_organics+$salv_organics, ship_goods=ship_goods+$salv_goods WHERE player_id=$targetinfo[player_id] AND ship_id=$targetinfo[currentship]");
        }
      }
    }
  }
}
$db->Execute("UNLOCK TABLES");
//-------------------------------------------------------------------------------------------------
TEXT_GOTOMAIN();

include("footer.php");

?>
