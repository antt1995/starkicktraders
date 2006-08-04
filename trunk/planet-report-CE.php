<?

// var_dump($HTTP_POST_VARS);

include("config.php");
updatecookie();
include("languages/$lang");

$title=$l_pr_title;

include("header.php");

connectdb();

if(checklogin())
{
  die();
}

bigtitle();

//if(isset($HTTP_POST_VARS["TPCreds"]))
//  collect_credits($HTTP_POST_VARS["TPCreds"]);
//else
if(isset($buildp) AND isset($builds)) {
	go_build_base($buildp, $builds);
	echo "<BR>";
	echo "Click <A HREF=planet-report.php>here</A> to return to report menu<br>";
} elseif(isset($my_ore_price)) {
	set_prices();
} else {
	change_planet_production($HTTP_POST_VARS);
	echo "<BR>";
	echo "Click <A HREF=planet-report.php>here</A> to return to report menu<br>";
}

echo "<BR><BR>";
TEXT_GOTOMAIN();

function set_prices() {
	// Set my global pricing for my planet selling
    global $db, $dbtables, $username;
  	global $ore_price, $ore_delta, $organics_price, $organics_delta, $goods_price, $goods_delta, $energy_price, $energy_delta;
	global $my_ore_price, $my_organics_price, $my_goods_price, $my_energy_price;
  	// Calculate maximum prices allowed
  	$max_ore_price = $ore_price-$ore_delta;
  	$max_organics_price = $organics_price-$organics_delta;
  	$max_goods_price = $goods_price-$goods_delta;
	$max_energy_price = $energy_price-$energy_delta;
	// Make sure we have floats here
	$my_ore_price = (float)$my_ore_price;
	$my_goods_price = (float)$my_goods_price;
	$my_organics_price = (float)$my_organics_price;
	$my_energy_price = (float)$my_energy_price;
  	// Check that prices are more than half a credit and less than the max unless zero
	if ($my_ore_price<0.5) {
		$my_ore_price=0;
		echo "Ore will not be sold.<br>";
	}
	if ($my_organics_price<0.5) {
		$my_organics_price=0;
		echo "Organics will not be sold.<br>";
	}
	if ($my_goods_price<0.5) {
		$my_goods_price=0;
		echo "Goods will not be sold.<br>";
	}
	if ($my_energy_price<0.5) {
		$my_energy_price=0;
		echo "Energy will not be sold.<br>";
	}
	if ($my_ore_price>$max_ore_price) {
		$my_ore_price=$max_ore_price;
		echo "Setting ore price to maximum ($max_ore_price credits).<br>";
	}
	if ($my_organics_price>$max_organics_price) {
		$my_organics_price=$max_organics_price;
		echo "Setting organics price to maximum ($max_organics_price credits).<br>";
	}
	if ($my_goods_price>$max_goods_price) {
		$my_goods_price=$max_goods_price;
		echo "Setting goods price to maximum ($max_goods_price credits).<br>";
	}
	if ($my_energy_price>$max_energy_price) {
		$my_energy_price=$max_energy_price;
		echo "Setting energy price to maximum ($max_energy_price credits).<br>";
	}
	//echo "DEBUG<br>UPDATE $dbtables[players] SET ore_price='$my_ore_price', goods_price='$my_goods_price', organics_price='$my_organics_price', energy_price='$my_energy_price' where email='$username'<br>";
	$res = $db->Execute("UPDATE $dbtables[players] SET ore_price='$my_ore_price', goods_price='$my_goods_price', organics_price='$my_organics_price', energy_price='$my_energy_price' where email='$username'");
	echo "<br>Updated your commodity pricing!<br>";
}

function go_build_base($planet_id, $sector_id)
{
  global $db;
  global $dbtables;
  global $base_ore, $base_organics, $base_goods, $base_credits;
  global $l_planet_bbuild;
  global $username;

  echo "<BR>Click <A HREF=planet-report.php?PRepType=1>here</A> to return to the Planet Status Report<BR><BR>";

  $result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
  $playerinfo=$result->fields;

  $result2 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$playerinfo[sector]");
  $sectorinfo=$result2->fields;

  $result3 = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$planet_id");
  if($result3)
    $planetinfo=$result3->fields;


  $result4 = Real_Space_Move($sector_id);
  if ($result4 != "ON_PLANET" && $result4 != "HOSTILE") {
	  // build a base
	  if($planetinfo[ore] >= $base_ore && $planetinfo[organics] >= $base_organics && $planetinfo[goods] >= $base_goods && $planetinfo[credits] >= $base_credits)
	  {
		// ** Create The Base
		$update1 = $db->Execute("UPDATE $dbtables[planets] SET base='Y', ore=$planetinfo[ore]-$base_ore, organics=$planetinfo[organics]-$base_organics, goods=$planetinfo[goods]-$base_goods, credits=$planetinfo[credits]-$base_credits WHERE planet_id=$planet_id");
		// ** Update User Turns
		$update1b = $db->Execute("UPDATE $dbtables[players] SET turns=turns-1, turns_used=turns_used+1 where player_id=$playerinfo[player_id]");
		// ** Refresh Plant Info
		$result3 = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$planet_id");
		$planetinfo=$result3->fields;
		// ** Notify User Of Base Results
		echo "$l_planet_bbuild<BR><BR>";
		// ** Calc Ownership and Notify User Of Results
		$ownership = calc_ownership($planetinfo[sector_id]);
		if(!empty($ownership))
		{
		  echo "$ownership<p>";
		}
  		echo "<BR>Click <A HREF=planet.php?planet_id=$planet_id>here</A> to go to the Planet Menu<BR><BR>";
	  }
  }
}


function collect_credits($planetarray)
{
  global $db, $dbtables, $username;

  $CS = "GO"; // Current State

  // create an array of sector -> planet pairs
  for($i = 0; $i < count($planetarray); $i++)
  {
    $res = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$planetarray[$i]");
    $s_p_pair[$i]= array($res->fields["sector_id"], $planetarray[$i]);
  }

  // Sort the array so that it is in order of sectors, lowest number first, not closest
  sort($s_p_pair);
  reset($s_p_pair);

  // run through the list of sector planet pairs realspace moving to each sector and then performing the transfer. 
  // Based on the way realspace works we don't need a sub loop -- might add a subloop to clean things up later.


  for($i=0; $i < count($planetarray) && $CS == "GO"; $i++)
  {
    echo "<BR>";
    $CS = Real_space_move($s_p_pair[$i][0]);
	if ($CS != "ON_PLANET") {
		if ($CS == "HOSTILE")
		{
		$CS = "GO";
		} else if($CS == "GO")
		{
		  $CS = Take_Credits($s_p_pair[$i][0], $s_p_pair[$i][1]);
		}
		else
		 echo "<BR> NOT ENOUGH TURNS TO TAKE CREDITS<BR>";
	}
    echo "<BR>";
  }

  if($CS != "GO" && $CS != "HOSTILE" && $CS != "ON_PLANET")
  {
    echo "<BR>Not enough turns to complete credit collection<BR>";
  }

  echo "<BR>";
  echo "Click <A HREF=planet-report.php?PRepType=1>here</A> to return to the Planet Status Report<br>";
}


function change_planet_production($prodpercentarray)
{
// **************************************************
// **  NOTES on what this function does and how
// **  Declares some global variables so they are accessable
// **    $db, $dbtables and default production values from the config.php file
// **  
// **  We need to track what the player_id is and what corp they belong to if they belong to a corp,
// **    these two values are not passed in as arrays
// **    player_id = the owner of the planet          ($player_id = $prodpercentarray[player_id])
// **    team_id = the corperation creators player_id ($team_id = $prodpercentarray[team_id])
// **
// **  First we generate a list of values based on the commodity
// **    (ore, organics, goods, energy, fighters, torps, corp, team, sells)
// **
// **  Second we generate a second list of values based on the planet_id
// **  Because team and player_id are not arrays we do not pass them through the second list command.
// **  When we write the ore production percent we also clear the selling and corp values out of the db
// **  When we pass through the corp array we set the value to $team we grabbed out of the array.
// **  in the sells and corp the prodpercent = the planet_id.
// **
// **  We run through the database checking to see if any planet production is greater than 100, or possibly negative
// **    if so we set the planet to the default values and report it to the player.
// **
// **  There has got to be a better way, but at this time I am not sure how to do it.
// **  Off the top of my head if we could sort the data passed in, in order of planets we could check before we do the writes
// **    This would save us from having to run through the database a second time checking our work.
// **  

  global $db, $dbtables;
  global $default_prod_ore, $default_prod_organics, $default_prod_goods, $default_prod_energy, $default_prod_fighters, $default_prod_torp;

  $player_id = $prodpercentarray[player_id];
  $team_id = $prodpercentarray[team_id]; 

  echo "Click <A HREF=planet-report.php?PRepType=2>here</A> to return to the Change Planet Production Report<br><br>";

  while(list($commod_type, $valarray) = each($prodpercentarray))
  {
    if($commod_type != "team_id" && $commod_type != "player_id")
    {
      while(list($planet_id, $prodpercent) = each($valarray))
      {  
        if($commod_type == "prod_ore")
        {
          $db->Execute("UPDATE $dbtables[planets] SET $commod_type=$prodpercent WHERE planet_id=$planet_id");
          $db->Execute("UPDATE $dbtables[planets] SET sells='N' WHERE planet_id=$planet_id");
          $db->Execute("UPDATE $dbtables[planets] SET corp=0 WHERE planet_id=$planet_id");
        }
        elseif($commod_type == "sells")
        {
          $db->Execute("UPDATE $dbtables[planets] SET sells='Y' WHERE planet_id=$prodpercent");
        }
        elseif($commod_type == "corp")
        {
          $db->Execute("UPDATE $dbtables[planets] SET corp=$team_id WHERE planet_id=$prodpercent");
        }
        else
        {
          $db->Execute("UPDATE $dbtables[planets] SET $commod_type=$prodpercent WHERE planet_id=$planet_id");
        }
      }
    }
  }

  echo "<BR>";
  echo "Production Percentages Updated <BR><BR>";
  echo "Checking Values for excess of 100% and negative production values <BR><BR>";

  $res = $db->Execute("SELECT * FROM $dbtables[planets] WHERE owner=$player_id ORDER BY sector_id");
  $i = 0;
  if($res)
  {
    while(!$res->EOF)
    {
      $planets[$i] = $res->fields;
      $i++;
      $res->MoveNext();
    }
  }

  foreach($planets as $planet)
  {
    if(empty($planet[name]))
    {
      $planet[name] = $l_unnamed;
    }

    if($planet[prod_ore] < 0)
      $planet[prod_ore] = 110;
    if($planet[prod_organics] < 0)
      $planet[prod_organics] = 110;
    if($planet[prod_goods] < 0)
      $planet[prod_goods] = 110;
    if($planet[prod_energy] < 0)
      $planet[prod_energy] = 110;
    if($planet[prod_fighters] < 0)
      $planet[prod_fighters] = 110;
    if($planet[prod_torp] < 0)
      $planet[prod_torp] = 110;


    if($planet[prod_ore] + $planet[prod_organics] + $planet[prod_goods] + $planet[prod_energy] + $planet[prod_fighters] + $planet[prod_torp] > 100)
    {
      echo "Planet $planet[name] in sector $planet[sector_id] has a negative production value or exceeds 100% production.  Resetting to default production values<BR>";
      $db->Execute("UPDATE $dbtables[planets] SET prod_ore=$default_prod_ore           WHERE planet_id=$planet[planet_id]");
      $db->Execute("UPDATE $dbtables[planets] SET prod_organics=$default_prod_organics WHERE planet_id=$planet[planet_id]");
      $db->Execute("UPDATE $dbtables[planets] SET prod_goods=$default_prod_goods       WHERE planet_id=$planet[planet_id]");
      $db->Execute("UPDATE $dbtables[planets] SET prod_energy=$default_prod_energy     WHERE planet_id=$planet[planet_id]");
      $db->Execute("UPDATE $dbtables[planets] SET prod_fighters=$default_prod_fighters WHERE planet_id=$planet[planet_id]");
      $db->Execute("UPDATE $dbtables[planets] SET prod_torp=$default_prod_torp         WHERE planet_id=$planet[planet_id]");
    }

  }
}


function Take_Credits($sector_id, $planet_id)
{
  global $db, $dbtables, $username;

  // Get basic Database information (ship and planet)
  $res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
  $playerinfo = $res->fields;
  $res = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$planet_id");
  $planetinfo = $res->fields;

  // Set the name for unamed planets to be "unnamed"
  if(empty($planetinfo[name]))
  {
    $planet[name] = $l_unnamed;
  }

  //verify player is still in same sector as the planet
  if($playerinfo[sector] == $planetinfo[sector_id])
  {
    if($playerinfo[turns] >= 1)
    {
      // verify player owns the planet to take credits from
      if($planetinfo[owner] == $playerinfo[player_id])
      {
        // get number of credits from the planet and current number player has on ship
        $CreditsTaken = $planetinfo[credits];
        $CreditsOnShip = $playerinfo[credits];
        $NewShipCredits = $CreditsTaken + $CreditsOnShip;

        // update the planet record for credits
        $res = $db->Execute("UPDATE $dbtables[planets] SET credits=0 WHERE planet_id=$planetinfo[planet_id]");

        // update the player record
        // credits
        $res = $db->Execute("UPDATE $dbtables[players] SET credits=$NewShipCredits WHERE email='$username'");
        // turns
        $res = $db->Execute("UPDATE $dbtables[players] SET turns=turns-1 WHERE email='$username'");

        echo "Took " . NUMBER($CreditsTaken) . " Credits from planet $planetinfo[name]. <BR>";
        echo "Your now have " . NUMBER($NewShipCredits) . ". <BR>";

        $retval = "GO";
      }
      else
      {
        echo "<BR><BR>You do not own planet $planetinfo[name]<BR><BR>";

        $retval = "GO";
      }
    }
    else
    {
      echo "<BR><BR>You do not have enough turns to take credits from $planetinfo[name] in sector $planetinfo[sector_id]<BR><BR>";

      $retval = "BREAK-TURNS";
    }
  }
  else
  {
    echo "<BR><BR>You must be in the same sector as the planet to transfer to/from the planet<BR><BR>";

    $retval = "BREAK-SECTORS";
  }

  return($retval);
}

function Real_Space_Move($destination)
{
  global $db;
  global $dbtables;
  global $level_factor;
  global $username;
  global $lang;

  $res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
  $playerinfo = $res->fields;
  // Check if player is on a planet or not
  if ($playerinfo[on_planet] == 'Y') {
	  echo "You cannot realspace when on a planet!<br><br>";
	  return("ON_PLANET");
  }	
  $res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
  $shipinfo = $res->fields;
  
  $distance=calc_dist($playerinfo['sector'],$destination);

  if($distance<1) {
    // TODO: The query failed. What now?
	$distance = 1;
  }

  $shipspeed = mypw($level_factor, $shipinfo[engines]);
  $triptime = round($distance / $shipspeed);






  if($triptime == 0 && $destination != $playerinfo[sector])
  {
    $triptime = 1;
  }


  if($playerinfo[dev_fuelscoop] == "Y")
  {
    $energyscooped = $distance * 100;
  }
  else
  {
    $energyscooped = 0;
  }

 
  if($shipinfo[dev_fuelscoop] == "Y" && $energyscooped == 0 && $triptime == 1)
  {
    $energyscooped = 100;
  }
  $free_power = NUM_ENERGY($shipinfo[power]) - $shipinfo[ship_energy];

  // amount of energy that can be stored is less than amount scooped amount scooped is set to what can be stored
  if($free_power < $energyscooped)
  {
    $energyscooped = $free_power;
  }

  // make sure energyscooped is not null
  if(!isset($energyscooped))
  {
    $energyscooped = "0";
  }

  // make sure energyscooped not negative, or decimal
  if($energyscooped < 1)
  {
    $energyscooped = 0;
  }

  // check to see if already in that sector
  if($destination == $playerinfo[sector])
  {
    $triptime = 0;
    $energyscooped = 0;
  }

  if($triptime > $playerinfo[turns])
  {
    $l_rs_movetime=str_replace("[triptime]",NUMBER($triptime),$l_rs_movetime);
    echo "$l_rs_movetime<BR><BR>";
    echo "$l_rs_noturns";
    $db->Execute("UPDATE $dbtables[players] SET cleared_defences=' ' where player_id=$playerinfo[player_id]");

    $retval = "BREAK-TURNS";
  }
  else
  {

// ********************************
// ***** Sector Defense Check *****
// ********************************

  $result99 = $db->Execute("SELECT * FROM $dbtables[players] LEFT JOIN $dbtables[sector_defence] ON ( $dbtables[players].player_id = $dbtables[sector_defence].player_id ) WHERE sector_id = $destination AND ( team = 0 OR team != $playerinfo[team] ) AND $dbtables[sector_defence].player_id != $playerinfo[player_id]");
  if(!$result99->EOF)
  {
	$retval = "HOSTILE";
	// need to add a language value for this
	echo "YOU CANNOT MOVE TO SECTOR $destination: THERE ARE HOSTILE SECTOR DEFENSES THERE!<br>";
  } else
  {
       $stamp = date("Y-m-d H-i-s");
       $update = $db->Execute("UPDATE $dbtables[players] SET last_login='$stamp',sector=$destination,turns=turns-$triptime,turns_used=turns_used+$triptime WHERE player_id=$playerinfo[player_id]");
	   $update = $db->Execute("UPDATE $dbtables[ships] SET ship_energy=ship_energy+$energyscooped WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
       $l_rs_ready=str_replace("[sector]",$destination,$l_rs_ready);
   
       $l_rs_ready= str_replace("[triptime]",NUMBER($triptime),$l_rs_ready);
       $l_rs_ready=str_replace("[energy]",NUMBER($energyscooped),$l_rs_ready);
       echo "$l_rs_ready<BR>";
       $retval = "GO";	
  }
 }
  return($retval);
}

include("footer.php");
?>
