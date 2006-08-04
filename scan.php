<?

include("config.php");

updatecookie();

include("languages/$lang");
$title=$l_scan_title;
include("header.php");

connectdb();
if(checklogin())
{
  die();
}
$result = $db->Execute ("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo=$result->fields;
$result = $db->Execute ("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo=$result->fields;

$result = $db->Execute ("SELECT * FROM $dbtables[ships] WHERE ship_id=$ship_id");
$targetshipinfo=$result->fields;
$result2 = $db->Execute ("SELECT * FROM $dbtables[players] WHERE player_id='$targetshipinfo[player_id]'");
$targetinfo=$result2->fields;


$playerscore = gen_score($playerinfo[player_id]);
$targetscore = gen_score($targetinfo[player_id]);

//$playerscore = $playerscore * $playerscore;
//$targetscore = $targetscore * $targetscore;
bigtitle();

srand((double)microtime()*1000000);

/* check to ensure target is in the same sector as player and that the target is not on a planet*/
if(($targetinfo[sector] != $playerinfo[sector]) || $targetinfo[on_planet] == 'Y')
{
  echo $l_planet_noscan;
}
else
{
  if($playerinfo[turns] < 1)
  {
    echo $l_scan_turn;
  }
  else
  {
    /* determine per cent chance of success in scanning target ship - based on player's sensors and opponent's cloak */
    $success= SCAN_SUCCESS($shipinfo[sensors], $targetshipinfo[cloak]);
    if($success < 5)
    {
      $success = 5;
    }
    if($success > 95)
    {
      $success = 95;
    }
    $roll = rand(1, 100);
	
    if($roll > $success)
    {
      /* if scan fails - inform both player and target. */
      echo $l_planet_noscan;
      playerlog($targetinfo[player_id], LOG_SHIP_SCAN_FAIL, "$playerinfo[character_name]");
    }
    else
    {
      /* if scan succeeds, show results and inform target. */
      /* scramble results by scan error factor. */

      // Get total bounty on this player, if any
      $btyamount = 0;
      $hasbounty = $db->Execute("SELECT SUM(amount) AS btytotal FROM $dbtables[bounty] WHERE bounty_on = $targetinfo[player_id] AND placed_by !=0");

      if($hasbounty)
      {
         $resx = $hasbounty->fields;
         if($resx[btytotal] > 0) 
         {
			$btyamount = NUMBER($resx[btytotal]);
            $l_scan_bounty=str_replace("[amount]",$btyamount,$l_scan_bounty);
            echo $l_scan_bounty . "<BR>";
         }
      }
	  // Check for Federation bounty
	  //echo "DEBUG (Please ignore!) SELECT SUM(amount) AS btytotal FROM $dbtables[bounty] WHERE bounty_on = $targetinfo[player_id] AND placed_by = 0<br>";
      $hasfedbounty = $db->Execute("SELECT SUM(amount) AS btytotal FROM $dbtables[bounty] WHERE bounty_on = $targetinfo[player_id] AND placed_by = 0");
	  $btyamount = $hasfedbounty[btytotal];
	  //echo "Debug:$btyamount<br>";
      if($btyamount != null)
      {
          echo $l_scan_fedbounty . "<BR>";
		  $btyamount = 1; // Just a flag
      }

      // Player will get a Federation Bounty on themselves if they attack a player who's score is less than bounty_ratio of
      // themselves. If the target has a Federation Bounty, they can attack without attracting a bounty on themselves.
	  if ($btyamount > 0 || $targetscore > 500000 || ($targetinfo[turns_used] > $bounty_minturns && ($targetscore / $playerscore) > $bounty_ratio) || (substr($targetinfo[email], -8)=="furangee"))
      {
         echo $l_by_nofedbounty . "<BR><BR>";
      }
      else
      {
         echo $l_by_fedbounty . "<BR><BR>";
      }
      $sc_error= SCAN_ERROR($shipinfo[sensors], $targetshipinfo[cloak]);
      echo "$l_scan_ron $targetshipinfo[ship_name], $l_scan_capt  $targetinfo[character_name]<BR><BR>";
      echo "<b>$l_ship_levels:</b><BR><BR>";
      echo "<table  width=\"\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">";
      echo "<tr><td>$l_hull:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_hull=round($targetshipinfo[hull] * $sc_error / 100);
        echo "<td>$sc_hull</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_engines:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_engines=round($targetshipinfo[engines] * $sc_error / 100);
        echo "<td>$sc_engines</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_power:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_power=round($targetshipinfo[power] * $sc_error / 100);
        echo "<td>$sc_power</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_computer:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_computer=round($targetshipinfo[computer] * $sc_error / 100);
        echo "<td>$sc_computer</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_sensors:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_sensors=round($targetshipinfo[sensors] * $sc_error / 100);
        echo "<td>$sc_sensors</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_beams:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_beams=round($targetshipinfo[beams] * $sc_error / 100);
        echo "<td>$sc_beams</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_torpedo Launchers:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_torp_launchers=round($targetshipinfo[torp_launchers] * $sc_error / 100);
        echo "<td>$sc_torp_launchers</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_armour:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_armour=round($targetshipinfo[armour] * $sc_error / 100);
        echo "<td>$sc_armour</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_shields:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_shields=round($targetshipinfo[shields] * $sc_error / 100);
        echo "<td>$sc_shields</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_cloak:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_cloak=round($targetshipinfo[cloak] * $sc_error / 100);
        echo "<td>$sc_cloak</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "</table><BR>";
      echo "<b>$l_scan_arma</b><BR><BR>";
      echo "<table  width=\"\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">";
      echo "<tr><td>$l_armourpts:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_armour_pts=NUMBER(round($targetshipinfo[armour_pts] * $sc_error / 100));
        echo "<td>$sc_armour_pts</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_fighters:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_ship_fighters=NUMBER(round($targetshipinfo[ship_fighters] * $sc_error / 100));
        echo "<td>$sc_ship_fighters</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_torps:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_torps=NUMBER(round($targetshipinfo[torps] * $sc_error / 100));
        echo "<td>$sc_torps</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "</table><BR>";
      echo "<b>$l_scan_carry</b><BR><BR>";
      echo "<table  width=\"\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">";
      echo "<tr><td>Credits:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_credits=NUMBER(round($targetinfo[credits] * $sc_error / 100));
        echo "<td>$sc_credits</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_colonists:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_ship_colonists=NUMBER(round($targetshipinfo[ship_colonists] * $sc_error / 100));
        echo "<td>$sc_ship_colonists</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_energy:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_ship_energy=NUMBER(round($targetshipinfo[ship_energy] * $sc_error / 100));
        echo "<td>$sc_ship_energy</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_ore:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_ship_ore=NUMBER(round($targetshipinfo[ship_ore] * $sc_error / 100));
        echo "<td>$sc_ship_ore</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_organics:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_ship_organics=NUMBER(round($targetshipinfo[ship_organics] * $sc_error / 100));
        echo "<td>$sc_ship_organics</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_goods:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_ship_goods=NUMBER(round($targetshipinfo[ship_goods] * $sc_error / 100));
        echo "<td>$sc_ship_goods</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "</table><BR>";
      echo "<b>$l_devices:</b><BR><BR>";
      echo "<table  width=\"\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">";
      echo "<tr><td>$l_warpedit:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_dev_warpedit=round($targetshipinfo[dev_warpedit] * $sc_error / 100);
        echo "<td>$sc_dev_warpedit</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_genesis:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_dev_genesis=round($targetshipinfo[dev_genesis] * $sc_error / 100);
        echo "<td>$sc_dev_genesis</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_deflect:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_dev_minedeflector=NUMBER(round($targetshipinfo[dev_minedeflector] * $sc_error / 100));
        echo "<td>$sc_dev_minedeflector</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_ewd:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
      {
        $sc_dev_emerwarp=round($targetshipinfo[dev_emerwarp] * $sc_error / 100);
        echo "<td>$sc_dev_emerwarp</td></tr>";
      }
      else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_escape_pod:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
        {echo "<td>$targetshipinfo[dev_escapepod]</td></tr>";} else {echo"<td>???</td></tr>";}
      echo "<tr><td>$l_fuel_scoop:</td>";
      $roll=rand(1,100);
      if ($roll<$success)
        {echo "<td>$targetshipinfo[dev_fuelscoop]</td></tr>";} else {echo"<td>???</td></tr>";}
      echo "</table><BR>";
      playerlog($targetinfo[player_id], LOG_SHIP_SCAN, "$playerinfo[character_name]");
    }

    $db->Execute("UPDATE $dbtables[players] SET turns=turns-1,turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
  }
}


echo "<BR><BR>";
TEXT_GOTOMAIN();

include("footer.php");
?>
