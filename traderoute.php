<?
include("config.php");
updatecookie();

include("languages/$lang");

$title=$l_tdr_title;
include("header.php");

connectdb();

if(checklogin())
{
  die();
}

//-------------------------------------------------------------------------------------------------


bigtitle();

$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;
$result = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo = $result->fields;
$result = $db->Execute("SELECT * FROM $dbtables[traderoutes] WHERE owner=$playerinfo[player_id]");
$num_traderoutes=$result->RecordCount();
$i=0;
while(!$result->EOF)
{
  $traderoutes[$i] = $result->fields;
  $i++;
  $result->MoveNext();
}

$freeholds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[ship_colonists];
$maxholds = NUM_HOLDS($shipinfo[hull]);
$maxenergy = NUM_ENERGY($shipinfo[power]);
if ($shipinfo[ship_colonists] < 0 || $shipinfo[ship_ore] < 0 || $shipinfo[ship_organics] < 0 || $shipinfo[ship_goods] < 0 || $shipinfo[ship_energy] < 0 || $freeholds < 0)
{
	if ($shipinfo[ship_colonists] < 0 || $shipinfo[ship_colonists] > $maxholds)
	{
		adminlog(LOG_ADMIN_ILLEGVALUE, $playerinfo[player_id], "$shipinfo[ship_name]|$shipinfo[ship_colonists]|colonists|$maxholds");
		$shipinfo[ship_colonists] = 0;
	}
	if ($shipinfo[ship_ore] < 0 || $shipinfo[ship_ore] > $maxholds)
	{
		adminlog(LOG_ADMIN_ILLEGVALUE, $playerinfo[player_id], "$shipinfo[ship_name]|$shipinfo[ship_ore]|ore|$maxholds");
		$shipinfo[ship_ore] = 0;
	}
	if ($shipinfo[ship_organics] < 0 || $shipinfo[ship_organics] > $maxholds)
	{
		adminlog(LOG_ADMIN_ILLEGVALUE, $playerinfo[player_id], "$shipinfo[ship_name]|$shipinfo[ship_organics]|organics|$maxholds");
		$shipinfo[ship_organics] = 0;
	}
	if ($shipinfo[ship_goods] < 0 || $shipinfo[ship_goods] > $maxholds)
	{
		adminlog(LOG_ADMIN_ILLEGVALUE, $playerinfo[player_id], "$shipinfo[ship_name]|$shipinfo[ship_goods]|goods|$maxholds");
		$shipinfo[ship_goods] = 0;
	}
	if ($shipinfo[ship_energy] < 0 || $shipinfo[ship_energy] > $maxenergy)
	{
		adminlog(LOG_ADMIN_ILLEGVALUE, $playerinfo[player_id], "$shipinfo[ship_name]|$shipinfo[ship_energy]|energy|$maxenergy");
		$shipinfo[ship_energy] = 0;
	}
	if ($freeholds < 0)
	{
		$freeholds = 0;
	}
$update1 = $db->Execute("UPDATE $dbtables[ships] SET ship_ore=$shipinfo[ship_ore], ship_organics=$shipinfo[ship_organics], ship_goods=$shipinfo[ship_goods], ship_energy=$shipinfo[ship_energy], ship_colonists=$shipinfo[ship_colonists] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
}
if(!isset($tr_repeat) || $tr_repeat <= 0)
  $tr_repeat = 1;


if($command == 'new')   //displays new trade route form
  traderoute_new('');
elseif($command == 'create')    //enters new route in db
  traderoute_create();
elseif($command == 'edit')    //displays new trade route form, edit
  traderoute_new($traderoute_id);
elseif($command == 'delete')  //displays delete info
  traderoute_delete();
elseif($command == 'settings')  //global traderoute settings form
  traderoute_settings();
elseif($command == 'setsettings') //enters settings in db
  traderoute_setsettings();
elseif(isset($engage)) //performs trade route
{
	$i=$tr_repeat;
	$tradeok = true;
	while ($i>0 && $tradeok) {
	  $result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
	  $playerinfo = $result->fields;
	  if ($playerinfo[on_planet] == 'Y') {
	  	echo "You cannot use trade routes when you are on a planet!<br><br>";
	  	$tradeok = false;
	  } else {
		  $result = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		  $shipinfo = $result->fields;
		  $tradeok = traderoute_engage($i);  // If there is a reason to stop early do so
		  $i--;
		}
	}
	traderoute_die("");
}


//-----------------------------------------------------------------
if($command != 'delete')
{
  echo "<p>$l_tdr_newtdr<p>";
  echo "<p>$l_tdr_modtdrset<p>";
}
else {
  $l_tdr_confdel = str_replace("[tdr_id]", $traderoute_id, $l_tdr_confdel);
  echo "<p>$l_tdr_confdel<p>";
}

if($num_traderoutes == 0)
  echo "$l_tdr_noactive<p>";
else
{
  if ($browser == "treo") {
  	echo '<table border=1 cellspacing=0 cellpadding=0 width="100%" align=center>';
  } else {
  	echo '<table border=1 cellspacing=1 cellpadding=2 width="100%" align=center>';
  }
  echo '<tr bgcolor=' . $color_line2 . '><td align="center" colspan=5><b><font color=white>
       ';

  if($command != 'delete')
    echo $l_tdr_curtdr;
  else
    echo $l_tdr_deltdr;

  echo "</font></b>" .
       "</td></tr>" .
       "<tr align=center bgcolor=$color_line2>" .
       "<td align=center>$l_tdr_src<br>Type</td>" .
       "<td align=center>Dest<br>Type</td>" .
       "<td align=center>$l_tdr_move</td>" .
       "<td align=center>$l_tdr_circuit</td>" .
       "<td align=center>$l_tdr_change</td>" .
       "</tr>";
  $i=0;
  $curcolor=$color_line1;
  while($i < $num_traderoutes)
  {
    echo "<tr bgcolor=$curcolor>";
    if($curcolor == $color_line1)
      $curcolor = $color_line2;
    else
      $curcolor = $color_line1;

    echo "<td align=center>";
    if($traderoutes[$i][source_type] == 'P')
      echo "$l_tdr_portin <a href=rsmove.php?engage=1&destination=" . $traderoutes[$i][source_id] . ">" . $traderoutes[$i][source_id] . "</a><br>";
    else
    {
      $result = $db->Execute("SELECT name, sector_id FROM $dbtables[planets] WHERE planet_id=" . $traderoutes[$i][source_id]);
      if($result)
      {
        $planet1 = $result->fields;
		if ($browser=="hiptop" && $planet1[name] != "") 
            $planet1[name] = substr($planet1[name],0,5)."..";
        echo "$l_tdr_planet <b>$planet1[name]</b>$l_tdr_within<a href=\"rsmove.php?engage=1&destination=$planet1[sector_id]\">$planet1[sector_id]</a><br>";
      }
      else
        echo "$l_tdr_nonexistance<br>";
    }

    //echo "<td align=center>";
    if($traderoutes[$i][source_type] == 'P')
    {
      $result = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=" . $traderoutes[$i][source_id]);
      $port1 = $result->fields;
	  // Find out if we have been here before or not
	  $result = $db->Execute("SELECT * FROM $dbtables[movement_log] WHERE sector_id=" . $traderoutes[$i][source_id]." AND player_id=".$playerinfo[player_id]);
	  if (!$result->EOF) {
      	echo t_port($port1[port_type]) . "</td>";
	  } else {
	  	echo "Unknown port</td>";
	  }
    }
    else
    {
      if(empty($planet1))
        echo "$l_tdr_na</td>";
      else
        echo "$l_tdr_cargo</td>";
    }

    echo "<td align=center>";

    if($traderoutes[$i][dest_type] == 'P')
    	echo "$l_tdr_portin <a href=\"rsmove.php?engage=1&destination=" . $traderoutes[$i][dest_id] . "\">" . $traderoutes[$i][dest_id] . "</a><br>";
    else
    {
      $result = $db->Execute("SELECT name, sector_id FROM $dbtables[planets] WHERE planet_id=" . $traderoutes[$i][dest_id]);
      if($result)
      {
        $planet2 = $result->fields;
		if ($browser=="hiptop" && $planet2[name] != "") 
            $planet2[name] = substr($planet2[name],0,5)."..";
        echo "$l_tdr_planet <b>$planet2[name]</b>$l_tdr_within<a href=\"rsmove.php?engage=1&destination=$planet2[sector_id]\">$planet2[sector_id]</a><br>";
      }
      else
        echo "$l_tdr_nonexistance<br>";
    }

    //echo "<td align=center>";
    if($traderoutes[$i][dest_type] == 'P')
    {
      $result = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=" . $traderoutes[$i][dest_id]);
      $port2 = $result->fields;
	  // Find out if we have been here before or not
	  $result = $db->Execute("SELECT * FROM $dbtables[movement_log] WHERE sector_id=" . $traderoutes[$i][dest_id]." AND player_id=".$playerinfo[player_id]);
	  if (!$result->EOF) {
      	echo t_port($port2[port_type]) . "</td>";
	  } else {
	  	echo "Unknown port</td>";
	  }
    }
    else
    {
      if(empty($planet2))
        echo "$l_tdr_na</td>";
      else
      {
        if($playerinfo[trade_colonists] == 'N' && $playerinfo[trade_fighters] == 'N' && $playerinfo[trade_torps] == 'N')
          echo $l_tdr_none;
        else
        {
          if($playerinfo[trade_colonists] == 'Y')
            echo $l_tdr_colonists;
          if($playerinfo[trade_fighters] == 'Y')
          {
            if($playerinfo[trade_colonists] == 'Y')
              echo "<br>";
            echo $l_tdr_fighters;
          }
          if($playerinfo[trade_torps] == 'Y')
            echo "<br>$l_tdr_torps";
        }
        echo "</td>";
      }
    }
    echo "<td align=center>";
    if($traderoutes[$i][move_type] == 'R')
    {
      echo "RS, ";

      if($traderoutes[$i][source_type] == 'P')
        $src=$port1[sector_id];
      else
        $src = $planet1[sector_id];

      if($traderoutes[$i][dest_type] == 'P')
        $dst=$port2[sector_id];
      else
        $dst = $planet2[sector_id];

      $dist = traderoute_distance($traderoutes[$i][source_type], $traderoutes[$i][dest_type], $src, $dst, $traderoutes[$i][circuit]);

      $l_tdr_escooped2 = $l_tdr_escooped;
      $l_tdr_escooped2 = str_replace("[tdr_dist_triptime]", $dist[triptime], $l_tdr_escooped2);
      $l_tdr_escooped2 = str_replace("[tdr_dist_scooped]", NUMBER($dist[scooped]), $l_tdr_escooped2);
      echo $l_tdr_escooped2;

      echo "</td>";

    }
    else
    {
      echo "$l_tdr_warp";
      if($traderoutes[$i][circuit] == '1')
        echo ", 2 $l_tdr_turns";
      else
        echo ", 4 $l_tdr_turns";
      echo "</td>";
    }

    echo "<td align=center>";
    if($traderoutes[$i][circuit] == '1')
      echo "1 $l_tdr_way</td>";
    else
      echo "2 $l_tdr_ways</td>";

    echo "<td align=center>";
    echo "<a href=\"traderoute.php?command=edit&traderoute_id=" . $traderoutes[$i][traderoute_id] . "\">";
    echo "$l_tdr_edit</a><br><a href=\"traderoute.php?command=delete&traderoute_id=" . $traderoutes[$i][traderoute_id] . "\">";
    echo "$l_tdr_del</a></td></tr>";

    $i++;
  }

  echo "</table><p>";
}
?>

<?

//-------------------------------------------------------------------------------------------------

TEXT_GOTOMAIN();
include("footer.php");

?>

<?

function traderoute_die($error_msg)
{
  global $l_footer_until_update, $l_footer_players_on_1, $l_footer_players_on_2, $l_footer_one_player_on, $sched_ticks;
  echo "<p>$error_msg<p>";


  TEXT_GOTOMAIN();
  include("footer.php");
  die();
}

function traderoute_check_compatible($type1, $type2, $move, $circuit, $src, $dest)
{
  global $playerinfo;
  global $l_tdr_nowlink1, $l_tdr_nowlink2, $l_tdr_sportissrc, $l_tdr_notownplanet, $l_tdr_planetisdest;
  global $l_tdr_samecom, $l_tdr_sportcom, $l_tdr_errnoport;
  global $db, $dbtables;

  //check warp links compatibility
  if($move == 'warp')
  {
    $query = $db->Execute("SELECT link_id FROM $dbtables[links] WHERE link_start=$src[sector_id] AND link_dest=$dest[sector_id]");
    if($query->EOF)
    {
      $l_tdr_nowlink1 = str_replace("[tdr_src_sector_id]", $src[sector_id], $l_tdr_nowlink1);
      $l_tdr_nowlink1 = str_replace("[tdr_dest_sector_id]", $dest[sector_id], $l_tdr_nowlink1);
      traderoute_die($l_tdr_nowlink1);
    }
    if($circuit == '2')
    {
      $query = $db->Execute("SELECT link_id FROM $dbtables[links] WHERE link_start=$dest[sector_id] AND link_dest=$src[sector_id]");
      if($query->EOF)
      {
        $l_tdr_nowlink2 = str_replace("[tdr_src_sector_id]", $src[sector_id], $l_tdr_nowlink2);
        $l_tdr_nowlink2 = str_replace("[tdr_dest_sector_id]", $dest[sector_id], $l_tdr_nowlink2);
        traderoute_die($l_tdr_nowlink2);
      }
    }
  }

  //check ports compatibility
  if($type1 == 'port')
  {
    if($src[port_type] == 'special')
    {
      if(($type2 != 'planet') && ($type2 != 'corp_planet'))
        traderoute_die($l_tdr_sportissrc);
      if($dest[owner] != $playerinfo[player_id] && ($dest[corp] == 0 || ($dest[corp] != $playerinfo[team])))
        traderoute_die($l_tdr_notownplanet);
	  // Check to see if the player has visited this special port before or not
	  $result = $db->Execute("SELECT * FROM $dbtables[movement_log] WHERE sector_id=" . $src[sector_id]." AND player_id=".$playerinfo[player_id]);
	  if ($result->EOF) {
      	traderoute_die($l_tdr_planetisdest);
	  }
    }
    else
    {
      if($type2 == 'planet')
        traderoute_die($l_tdr_planetisdest);
      if($src[port_type] == $dest[port_type])
        traderoute_die($l_tdr_samecom);
    }
  }
  else if($type1 == 'sell_planet')
  {
    if($dest[port_type] == 'special') {
      //traderoute_die($l_tdr_sportcom); Changed so that specials cannot be located this way
	  traderoute_die($l_tdr_planetisdest);
	}
    else if($type2 != 'port')
      traderoute_die($l_tdr_planetisdest);
  }
  else
  {
    if($dest[port_type] == 'special') {
	  // Just say that there isn't a port there
      $l_tdr_errnoport = str_replace("[tdr_port_id]", $dest[sector_id], $l_tdr_errnoport);
      traderoute_die($l_tdr_errnoport);
	}
  }
}


function traderoute_distance($type1, $type2, $start, $dest, $circuit, $sells = 'N')
{
//echo "DEBUG: $type1, $type2, $start, $dest, $circuit, $sells<br>";
  global $playerinfo;
  global $shipinfo;
  global $level_factor;
  global $db, $dbtables;

  $retvalue[triptime] = 0;
  $retvalue[scooped1] = 0;
  $retvalue[scooped2] = 0;
  $retvalue[scooped] = 0;
/*
  if($type1 == 'L')
  {
    $query = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$start");
    $start = $query->fields;
  }

  if($type2 == 'L')
  {
    $query = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$dest");
    $dest = $query->fields;
  }
*/
  if($start == $dest)
  {
    if($circuit == '1')
      $retvalue[triptime] = '1';
    else
      $retvalue[triptime] = '2';
    return $retvalue;
  }
  //echo "Start = $start<br>Dest = $dest<br>";
   $distance=calc_dist($start,$dest);
   //echo "Distance = $distance<br>";
  if($distance<1) {
    $distance = 1;
    // TODO: The query failed. What now?
  }

  $shipspeed = mypw($level_factor, $shipinfo[engines]);
  $triptime = round($distance / $shipspeed);

  if(!$triptime && $destination != $playerinfo[sector])
    $triptime = 1;

  if($shipinfo[dev_fuelscoop] == "Y")
      $energyscooped = $distance * 100;
  else
    $energyscooped = 0;

  if($shipinfo[dev_fuelscoop] == "Y" && !$energyscooped && $triptime == 1)
    $energyscooped = 100;

  $free_power = NUM_ENERGY($shipinfo[power]) - $shipinfo[ship_energy];

  if($free_power < $energyscooped)
    $energyscooped = $free_power;

  if($energyscooped < 1)
    $energyscooped = 0;

  $retvalue[scooped1] = $energyscooped;

  if($circuit == '2')
  {
    if($sells == 'Y' && $shipinfo[dev_fuelscoop] == 'Y' && $type2 == 'P' && $dest[port_type] != 'energy')
    {
      $energyscooped = $distance * 100;
      $free_power = NUM_ENERGY($shipinfo[power]);
      if($free_power < $energyscooped)
        $energyscooped = $free_power;
      $retvalue[scooped2] = $energyscooped;
    }
    elseif($shipinfo[dev_fuelscoop] == 'Y')
    {
      $energyscooped = $distance * 100;
      $free_power = NUM_ENERGY($shipinfo[power]) - $retvalue[scooped1] - $shipinfo[ship_energy];
      if($free_power < $energyscooped)
        $energyscooped = $free_power;
      $retvalue[scooped2] = $energyscooped;
    }
  }

  if($circuit == '2')
  {
    $triptime*=2;
    $triptime+=2;
  }
  else
    $triptime+=1;

  $retvalue[triptime] = $triptime;
  $retvalue[scooped] = $retvalue[scooped1] + $retvalue[scooped2];

  return $retvalue;
}

function traderoute_new($traderoute_id)
{
  global $playerinfo;
  global $num_traderoutes;
  global $max_traderoutes_player;
  global $l_tdr_editerr, $l_tdr_maxtdr, $l_tdr_createnew, $l_tdr_editinga, $l_tdr_traderoute, $l_tdr_unnamed;
  global $l_tdr_cursector, $l_tdr_selspoint, $l_tdr_port, $l_tdr_planet, $l_tdr_none, $l_tdr_insector, $l_tdr_selendpoint;
  global $l_tdr_selmovetype, $l_tdr_realspace, $l_tdr_warp, $l_tdr_selcircuit, $l_tdr_oneway, $l_tdr_bothways, $l_tdr_create;
  global $l_tdr_modify, $l_tdr_returnmenu, $l_tdr_none;
  global $l_footer_until_update, $l_footer_players_on_1, $l_footer_players_on_2, $l_footer_one_player_on, $sched_ticks;
  global $db, $dbtables;

  if(!empty($traderoute_id))
  {
    $result = $db->Execute("SELECT * FROM $dbtables[traderoutes] WHERE traderoute_id=$traderoute_id");
    if(!result || $result->EOF)
      traderoute_die($l_tdr_editerr);
    $editroute = $result->fields;
    if($editroute[owner] != $playerinfo[player_id])
      traderoute_die($l_tdr_notowner);
  }

  if($num_traderoutes >= $max_traderoutes_player && empty($editroute))
    traderoute_die("<p>$l_tdr_maxtdr<p>");

  echo "<p><font size=3 color=blue><b>";
  if(empty($editroute))
    echo $l_tdr_createnew;
  else
    echo "$l_tdr_editinga ";
  echo "$l_tdr_traderoute</b></font><p>";

//---------------------------------------------------
//---- Get Planet info Corp and Personal and Selling Planets(BEGIN) ----

  $result = $db->Execute("SELECT * FROM $dbtables[planets] WHERE owner=$playerinfo[player_id] ORDER BY sector_id");

  $num_planets = $result->RecordCount();
  $i=0;
  while (!$result->EOF)
  {
    $planets[$i] = $result->fields;
    if($planets[$i][name] == "")
      $planets[$i][name] = $l_tdr_unnamed;
    $i++;
    $result->MoveNext();
  }

  $result = $db->Execute("SELECT * FROM $dbtables[planets] WHERE corp=$playerinfo[team] AND corp!=0 AND owner<>$playerinfo[player_id] ORDER BY sector_id");
  $num_corp_planets = $result->RecordCount();
  $i=0;
  while (!$result->EOF)
  {
    $planets_corp[$i] = $result->fields;
    if($planets_corp[$i][name] == "")
      $planets_corp[$i][name] = $l_tdr_unnamed;
    $i++;
    $result->MoveNext();
  }
  
  $result = $db->Execute("SELECT * FROM $dbtables[planets] WHERE sells='Y' AND owner<>$playerinfo[player_id] ORDER BY sector_id");
  $num_selling_planets = $result->RecordCount();
  $i=0;
  while (!$result->EOF)
  {
    $planets_sell[$i] = $result->fields;
    if($planets_sell[$i][name] == "")
      $planets_sell[$i][name] = $l_tdr_unnamed;
    $i++;
    $result->MoveNext();
  }
//---- Get Planet info Corp and Personal and Selling (END) ------
//---------------------------------------------------
  // Display Current Sector
  echo "$l_tdr_cursector $playerinfo[sector]<br>";

  // Start of form for starting location
  echo "                                                                          
    <form action=traderoute.php?command=create method=post>
    <table width=100% border=0><tr>
    <td colspan=3><font size=2><b>$l_tdr_selspoint</b></font></td>
    <tr>
    <td align=right><font size=2>$l_tdr_port : </font></td>
    <td><input type=radio name=\"ptype1\" value=\"port\"                          
    ";

  if(empty($editroute) || (!empty($editroute) && $editroute[source_type] == 'P'))
    echo " checked";

    echo " 
      ></td>
      <td>&nbsp;&nbsp;<input type=text name=port_id1 size=10 align=center
      ";

  if(!empty($editroute) && $editroute[source_type] == 'P')
    echo " value=\"$editroute[source_id]\"";

  echo "
    ></td>
    </tr><tr>
    ";

//-------------------- Personal Planet
  echo "
    <td align=right><font size=2>Personal $l_tdr_planet : </font></td>
    <td><input type=radio name=\"ptype1\" value=\"planet\"
    ";
  if(!empty($editroute) && $editroute[source_type] == 'L')
    echo " checked";

    echo '
    ></td>
    <td>&nbsp;&nbsp;<select name=planet_id1>
    ';

  if($num_planets == 0)
    echo "<option value=none>$l_tdr_none</option>";
  else
  {
    $i=0;
    while($i < $num_planets)
    {
      echo "<option ";
      if($planets[$i][planet_id] == $editroute[source_id])
        echo "selected ";
      echo "value=" . $planets[$i][planet_id] . ">" . substr($planets[$i][name],0,12). "</option>";
      $i++;
    }
  }
//-------------------- Selling Planet
   echo "</tr><tr>
    <td align=right><font size=2>Selling $l_tdr_planet : </font></td>
    <td><input type=radio name=\"ptype1\" value=\"sell_planet\"
    ";
  if(!empty($editroute) && $editroute[source_type] == 'S')
    echo " checked";

    echo '
    ></td>
    <td>&nbsp;&nbsp;<select name=sell_planet_id1>
    ';

  if($num_selling_planets == 0)
    echo "<option value=none>$l_tdr_none</option>";
  else
  {
    $i=0;
    while($i < $num_selling_planets)
    {
      echo "<option ";
      if($planets_sell[$i][planet_id] == $editroute[source_id])
        echo "selected ";
      echo "value=" . $planets_sell[$i][planet_id] . ">" . substr($planets_sell[$i][name],0,12). "</option>";
      $i++;
    }
  }
//----------------------- Corp Planet
    echo "
    </tr><tr>
    <td align=right><font size=2>Corporate $l_tdr_planet : </font></td>
    <td><input type=radio name=\"ptype1\" value=\"corp_planet\"
    ";

  if(!empty($editroute) && $editroute[source_type] == 'C')
    echo " checked";

    echo '
    ></td>
    <td>&nbsp;&nbsp;<select name=corp_planet_id1>
    ';

  if($num_corp_planets == 0)
    echo "<option value=none>$l_tdr_none</option>";
  else
  {
    $i=0;
    while($i < $num_corp_planets)
    {
      echo "<option ";
      if($planets_corp[$i][planet_id] == $editroute[source_id])
        echo "selected ";
      echo "value=" . $planets_corp[$i][planet_id] . ">" . substr($planets_corp[$i][name],0,12) . "</option>";
      $i++;
    }
  }
  echo "
    </select>
    </tr>";
//----------------------- End Start point selection
//----------------------- Begin Ending point selection
  echo "
    <tr><td>&nbsp;
    </tr><tr>
    <td colspan=3><font size=2><b>$l_tdr_selendpoint : <br>&nbsp;</b></font></td>
    <tr>
    <td align=right><font size=2>$l_tdr_port : </font></td>
    <td><input type=radio name=\"ptype2\" value=\"port\"
    ";

  if(empty($editroute) || (!empty($editroute) && $editroute[dest_type] == 'P'))
    echo " checked";

    echo '
    ></td>
    <td>&nbsp;&nbsp;<input type=text name=port_id2 size=15 align=center
    ';

  if(!empty($editroute) && $editroute[dest_type] == 'P')
    echo " value=\"$editroute[dest_id]\"";

    echo "
    ></td>
    </tr>";
//-------------------- Personal Planet
    echo "
    <tr>
    <td align=right><font size=2>Personal $l_tdr_planet : </font></td>
    <td><input type=radio name=\"ptype2\" value=\"planet\"
    ";

  if(!empty($editroute) && $editroute[dest_type] == 'L')
    echo " checked";

  echo '
    ></td>
    <td>&nbsp;&nbsp;<select name=planet_id2>
    ';

  if($num_planets == 0)
    echo "<option value=none>$l_tdr_none</option>";
  else
  {
    $i=0;
    while($i < $num_planets)
    {
      echo "<option ";
      if($planets[$i][planet_id] == $editroute[dest_id])
        echo "selected ";
      echo "value=" . $planets[$i][planet_id] . ">" . substr($planets[$i][name],0,12) . "</option>";
      $i++;
    }
  }
//----------------------- Corp Planet
  echo "
    </tr><tr>
    <td align=right><font size=2>Corporate $l_tdr_planet : </font></td>
    <td><input type=radio name=\"ptype2\" value=\"corp_planet\"
    ";

  if(!empty($editroute) && $editroute[dest_type] == 'C')
    echo " checked";

    echo '
    ></td>
    <td>&nbsp;&nbsp;<select name=corp_planet_id2>
    ';

  if($num_corp_planets == 0)
    echo "<option value=none>$l_tdr_none</option>";
  else
  {
    $i=0;
    while($i < $num_corp_planets)
    {
      echo "<option ";
      if($planets_corp[$i][planet_id] == $editroute[dest_id])
        echo "selected ";
      echo "value=" . $planets_corp[$i][planet_id] . ">" . substr($planets_corp[$i][name],0,12) . "</option>";
      $i++;
    }
  }
  echo "
    </select>
    </tr>";
//----------------------- End finishing point selection

  echo "
    </select>
    </tr><tr>
    <td>&nbsp;
    </tr><tr>
    <td align=right><font size=2><b>$l_tdr_selmovetype : </b></font></td>
    <td colspan=2 valign=top><font size=2><input type=radio name=\"move_type\" value=\"warp\"
    ";

  if(empty($editroute) || (!empty($editroute) && $editroute[move_type] == 'W'))
    echo " checked";

  echo "
    >&nbsp;$l_tdr_warp&nbsp;&nbsp<font size=2><input type=radio name=\"move_type\" value=\"realspace\"
    ";

  if(!empty($editroute) && $editroute[move_type] == 'R')
    echo " checked";

  echo "
    >&nbsp;$l_tdr_realspace</font></td>
    </tr><tr>
    <td align=right><font size=2><b>$l_tdr_selcircuit : </b></font></td>
    <td colspan=2 valign=top><font size=2><input type=radio name=\"circuit_type\" value=\"1\"
    ";

  if(!empty($editroute) && $editroute[circuit] == '1')
    echo " checked";

  echo "
    >&nbsp;$l_tdr_oneway&nbsp;<input type=radio name=\"circuit_type\" value=\"2\"
    ";

  if(empty($editroute) || (!empty($editroute) && $editroute[circuit] == '2'))
    echo " checked";

  echo "
    >&nbsp;$l_tdr_bothways</font></td>
    </tr><tr>
    <td>&nbsp;
    </tr><tr>
    <td><td><td align=center>
    ";

  if(empty($editroute))
    echo "<input type=submit value=\"$l_tdr_create\">";
  else
  {
    echo "<input type=hidden name=editing value=$editroute[traderoute_id]>";
    echo "<input type=submit value=\"$l_tdr_modify\">";
  }

  echo "
    </table>
    $l_tdr_returnmenu<br>
    </form>
    ";


  TEXT_GOTOMAIN();
  include("footer.php");
  die();
}

function traderoute_create()
{
  global $playerinfo;
  global $num_traderoutes;
  global $max_traderoutes_player;
  global $ptype1;
  global $ptype2;
  global $port_id1;
  global $port_id2;
  global $planet_id1;
  global $planet_id2;
  global $corp_planet_id1;
  global $corp_planet_id2;
  global $sell_planet_id1;
  global $move_type;
  global $circuit_type;
  global $editing;
  global $l_tdr_maxtdr, $l_tdr_errnotvalidport, $l_tdr_errnoport, $l_tdr_errnosrc, $l_tdr_errnotownnotsell;
  global $l_tdr_errnotvaliddestport, $l_tdr_errnoport2, $l_tdr_errnodestplanet, $l_tdr_errnotownnotsell2;
  global $l_tdr_newtdrcreated, $l_tdr_tdrmodified, $l_tdr_returnmenu;
  global $db, $dbtables;

  if($num_traderoutes >= $max_traderoutes_player && empty($editing))
    traderoute_die($l_tdr_maxtdr);

  //dbase sanity check for source
  if($ptype1 == 'port')
  {
    $query = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$port_id1");
    if(!$query || $db->EOF)
    {
      $l_tdr_errnotvalidport = str_replace("[tdr_port_id]", $port_id1, $l_tdr_errnotvalidport);
      traderoute_die($l_tdr_errnotvalidport);
    }

    $source=$query->fields;
    if($source[port_type] == 'none')
    {
      $l_tdr_errnoport = str_replace("[tdr_port_id]", $port_id1, $l_tdr_errnoport);
      traderoute_die($l_tdr_errnoport);
    }
  }
  else if($ptype1 == 'planet') {
    $query = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$planet_id1");
    if(!$query || $query->EOF)
      traderoute_die($l_tdr_errnosrc);
    $source=$query->fields;
  }
  else if($ptype1 == 'corp_planet')
  {
    $query = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$corp_planet_id1");
    if(!$query || $query->EOF)
      traderoute_die($l_tdr_errnosrc);
    $source=$query->fields;
	if($playerinfo[team] == 0 || $playerinfo[team] != $source[corp])
      {
        traderoute_die("That planet is no longer in your alliance!");
      }
  }
  else if($ptype1 == 'sell_planet')
  {
    $src_id = $sell_planet_id1;
    $query = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$sell_planet_id1");
    if(!$query || $query->EOF)
      traderoute_die($l_tdr_errnosrc);
    $source=$query->fields;
	if($source[sells] == 'N')
      {
        $l_tdr_errnotownnotsell = str_replace("[tdr_source_name]", $source[name], $l_tdr_errnotownnotsell);
        $l_tdr_errnotownnotsell = str_replace("[tdr_source_sector_id]", $source[sector_id], $l_tdr_errnotownnotsell);
        traderoute_die($l_tdr_errnotownnotsell);
      }
  }


  //dbase sanity check for dest
  if($ptype2 == 'port')
  {
    $query = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$port_id2");
    if(!$query || $query->EOF)
    {
      $l_tdr_errnotvaliddestport = str_replace("[tdr_port_id]", $port_id2, $l_tdr_errnotvaliddestport);
      traderoute_die($l_tdr_errnotvaliddestport);
    }

    $destination=$query->fields;
    if($destination[port_type] == 'none') {
      $l_tdr_errnoport2 = str_replace("[tdr_port_id]", $port_id2, $l_tdr_errnoport2);
      traderoute_die($l_tdr_errnoport2);
    }
  }
  else if($ptype2 == 'corp_planet')
  {
    $query = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$corp_planet_id2");
    if(!$query || $query->EOF)
      traderoute_die($l_tdr_errnodestplanet);
    $destination=$query->fields;
	if($playerinfo[team] == 0 || $playerinfo[team] != $destination[corp])
      {
        traderoute_die("That planet is no longer in your alliance!");
      }
  }
  else
  {
    $query = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$planet_id2");
    if(!$query || $query->EOF)
      traderoute_die($l_tdr_errnodestplanet);
    $destination=$query->fields;

    if($destination[owner] != $playerinfo[player_id] && $destination[sells] == 'N')
    {
      $l_tdr_errnotownnotsell2 = str_replace("[tdr_dest_name]", $destination[name], $l_tdr_errnotownnotsell2);
      $l_tdr_errnotownnotsell2 = str_replace("[tdr_dest_sector_id]", $destination[sector_id], $l_tdr_errnotownnotsell2);
      traderoute_die($l_tdr_errnotownnotsell2);
    }
  }

  //check traderoute for src => dest
  traderoute_check_compatible($ptype1, $ptype2, $move_type, $circuit_type, $source , $destination);
	//echo "DEBUG: ptype1 = $ptype1<br>";
	//echo "DEBUG: ptype2 = $ptype2<br>";
  if($ptype1 == 'port')
    $src_id = $port_id1;
  elseif($ptype1 == 'planet')
    $src_id = $planet_id1;
  elseif($ptype1 == 'corp_planet')
    $src_id = $corp_planet_id1;
  elseif($ptype1 == 'sell_planet')
    $src_id = $sell_planet_id1;

  if($ptype2 == 'port')
    $dest_id = $port_id2;
  elseif($ptype2 == 'planet')
    $dest_id = $planet_id2;
  elseif($ptype2 == 'corp_planet')
    $dest_id = $corp_planet_id2;


  if($ptype1 == 'port')
    $src_type = 'P';
  elseif($ptype1 == 'planet')
    $src_type = 'L';
  elseif($ptype1 == 'corp_planet')
    $src_type = 'C';
  elseif($ptype1 == 'sell_planet')
    $src_type = 'S';


  if($ptype2 == 'port')
    $dest_type = 'P';
  elseif($ptype2 == 'planet')
    $dest_type = 'L';
  elseif($ptype2 == 'corp_planet')
    $dest_type = 'C';

  if($move_type == 'realspace')
    $mtype = 'R';
  else
    $mtype = 'W';

  if(empty($editing))
  { 
  	//echo "DEBUG: INSERT INTO $dbtables[traderoutes] VALUES('', $src_id, $dest_id, '$src_type', '$dest_type', '$mtype', $playerinfo[player_id], '$circuit_type')<br>";
    $query = $db->Execute("INSERT INTO $dbtables[traderoutes] VALUES('', $src_id, $dest_id, '$src_type', '$dest_type', '$mtype', $playerinfo[player_id], '$circuit_type')");
    echo "<p>$l_tdr_newtdrcreated";
  }
  else
  {
    $query = $db->Execute("UPDATE $dbtables[traderoutes] SET source_id=$src_id, dest_id=$dest_id, source_type='$src_type', dest_type='$dest_type', move_type='$mtype', owner=$playerinfo[player_id], circuit='$circuit_type' WHERE traderoute_id=$editing");
    echo "<p>$l_tdr_tdrmodified";
  }

  echo " $l_tdr_returnmenu";
  traderoute_die("");

}

function traderoute_delete()
{
  global $playerinfo;
  global $confirm;
  global $num_traderoutes;
  global $traderoute_id;
  global $traderoutes;
  global $l_tdr_returnmenu, $l_tdr_tdrdoesntexist, $l_tdr_notowntdr, $l_tdr_tdrdeleted;
  global $db, $dbtables;

  $query = $db->Execute("SELECT * FROM $dbtables[traderoutes] WHERE traderoute_id=$traderoute_id");
  if(!$query || $query->EOF)
    traderoute_die($l_tdr_tdrdoesntexist);

  $delroute = $query->fields;

  if($delroute[owner] != $playerinfo[player_id])
    traderoute_die($l_tdr_notowntdr);

  if(empty($confirm))
  {
    $num_traderoutes = 1;
    $traderoutes[0] = $delroute;
    // here it continues to the main file area to print the route
  }
  else
  {
    $query = $db->Execute("DELETE FROM $dbtables[traderoutes] WHERE traderoute_id=$traderoute_id");
    echo "$l_tdr_tdrdeleted $l_tdr_returnmenu";
    traderoute_die("");
  }
}

function traderoute_settings()
{
  global $playerinfo;
  global $l_tdr_globalset, $l_tdr_tdrsportsrc, $l_tdr_colonists, $l_tdr_fighters, $l_tdr_torps, $l_tdr_trade;
  global $l_tdr_tdrescooped, $l_tdr_keep, $l_tdr_save, $l_tdr_returnmenu;
  global $db, $dbtables;

  echo "<p><font size=3 color=blue><b>$l_tdr_globalset</b></font><p>";

  echo "
    <font color=white size=2><b>$l_tdr_tdrsportsrc :</b></font><p>
    <form action=traderoute.php?command=setsettings method=post>
    <table border=0><tr>
    <td><font size=2 color=white> - $l_tdr_colonists :</font></td>
    <td><input type=checkbox name=colonists
    ";

  if($playerinfo[trade_colonists] == 'Y')
    echo " checked";

  echo "
    ></tr><tr>
    <td><font size=2 color=white> - $l_tdr_fighters :</font></td>
    <td><input type=checkbox name=fighters
    ";

  if($playerinfo[trade_fighters] == 'Y')
    echo " checked";

  echo "
    ></tr><tr>
    <td><font size=2 color=white> - $l_tdr_torps :</font></td>
    <td><input type=checkbox name=torps
    ";

  if($playerinfo[trade_torps] == 'Y')
    echo " checked";

  echo "
    ></tr>
    </table>
    <p>
    <font color=white size=2><b>$l_tdr_tdrescooped :</b></font><p>
    <table border=0><tr>
    <td><font size=2 color=white>&nbsp;&nbsp;&nbsp;$l_tdr_trade or drop on planet (if possible)</font></td>
    <td><input type=radio name=energy value=\"Y\"
    ";

  if($playerinfo[trade_energy] == 'Y')
    echo " checked";

  echo "
    ></td></tr><tr>
    <td><font size=2 color=white>&nbsp;&nbsp;&nbsp;$l_tdr_keep</font></td>
    <td><input type=radio name=energy value=\"N\"
    ";

  if($playerinfo[trade_energy] == 'N')
    echo " checked";

  echo "></td></tr><tr><td>&nbsp;</td></tr><tr><td>
    <td><input type=submit value=\"$l_tdr_save\"></td>
    </tr></table>
    </form>
    ";

  echo $l_tdr_returnmenu;
  traderoute_die("");

}

function traderoute_setsettings()
{
  global $playerinfo;
  global $colonists;
  global $fighters;
  global $torps;
  global $energy;
  global $l_tdr_returnmenu, $l_tdr_globalsetsaved;
  global $db, $dbtables;

  empty($colonists) ? $colonists = 'N' : $colonists = 'Y';
  empty($fighters) ? $fighters = 'N' : $fighters = 'Y';
  empty($torps) ? $torps = 'N' : $torps = 'Y';

  $db->Execute("UPDATE $dbtables[players] SET trade_colonists='$colonists', trade_fighters='$fighters', trade_torps='$torps', trade_energy='$energy' WHERE player_id=$playerinfo[player_id]");

  echo "$l_tdr_globalsetsaved $l_tdr_returnmenu";
  traderoute_die("");
}

function traderoute_engage($j)
{
  global $browser;
  global $playerinfo;
  global $shipinfo;
  global $engage;
  global $traderoutes;
  global $fighter_price;
  global $torpedo_price;
  global $colonist_price;
  global $colonist_limit;
  global $inventory_factor;
  global $ore_price;
  global $ore_delta;
  global $ore_limit;
  global $organics_price;
  global $organics_delta;
  global $organics_limit;
  global $goods_price;
  global $goods_delta;
  global $goods_limit;
  global $energy_price;
  global $energy_delta;
  global $energy_limit;
  global $l_tdr_turnsused, $l_tdr_turnsleft, $l_tdr_credits, $l_tdr_profit, $l_tdr_cost, $l_tdr_totalprofit, $l_tdr_totalcost;
  global $l_tdr_planetisovercrowded, $l_tdr_engageagain, $l_tdr_onlyonewaytdr, $l_tdr_engagenonexist, $l_tdr_notowntdr;
  global $l_tdr_invalidspoint, $l_tdr_inittdr, $l_tdr_invalidsrc, $l_tdr_inittdrsector, $l_tdr_organics, $l_tdr_energy, $l_tdr_loaded;
  global $l_tdr_nothingtoload, $l_tdr_scooped, $l_tdr_dumped, $l_tdr_portisempty, $l_tdr_portisfull, $l_tdr_ore, $l_tdr_sold;
  global $l_tdr_goods, $l_tdr_notyourplanet, $l_tdr_invalidssector, $l_tdr_invaliddport, $l_tdr_invaliddplanet;
  global $l_tdr_invaliddsector, $l_tdr_nowlink1, $l_tdr_nowlink2, $l_tdr_moreturnsneeded, $l_tdr_tdrhostdef;
  global $l_tdr_globalsetbuynothing, $l_tdr_nosrcporttrade, $l_tdr_tradesrcportoutsider, $l_tdr_tdrres, $l_tdr_torps;
  global $l_tdr_nodestporttrade, $l_tdr_tradedestportoutsider, $l_tdr_portin, $l_tdr_planet, $l_tdr_bought, $l_tdr_colonists;
  global $l_tdr_fighters, $l_tdr_nothingtotrade;
  global $db, $dbtables;
  // Initialize some variables
  $energy_buy = 0;
  $tradeok = true; // Flag to indicate if a trade route was futile. If so then we'll stop trading. Returned at end of function.
  //10 pages of sanity checks! yeah!

  foreach($traderoutes as $testroute)
  {
    if($testroute[traderoute_id] == $engage)
      $traderoute = $testroute;
  }

  if(!isset($traderoute))
    traderoute_die($l_tdr_engagenonexist);

  if($traderoute[owner] != $playerinfo[player_id])
    traderoute_die($l_tdr_notowntdr);



// ********************************
// *****  Source Check ************
// ********************************
  if($traderoute[source_type] == 'P')
  {
    //retrieve port info here, we'll need it later anyway
    $result = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$traderoute[source_id]");
    if(!$result || $result->EOF)
      traderoute_die($l_tdr_invalidspoint);

    $source = $result->fields;

    if($traderoute[source_id] != $playerinfo[sector])
    {
      $l_tdr_inittdr = str_replace("[tdr_source_id]", $traderoute[source_id], $l_tdr_inittdr);
      traderoute_die($l_tdr_inittdr);
    }
  }
  elseif($traderoute[source_type] == 'L' || $traderoute[source_type] == 'C' || $traderoute[source_type] == 'S')  // get data from planet table
  {
    $result = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$traderoute[source_id]");
    if(!$result || $result->EOF)
      traderoute_die($l_tdr_invalidsrc);

    $source = $result->fields;

    if($source[sector_id] != $playerinfo[sector])
    {
      $l_tdr_inittdrsector = str_replace("[tdr_source_sector_id]", $source[sector_id], $l_tdr_inittdrsector);
      traderoute_die($l_tdr_inittdrsector);
    }

    if($traderoute[source_type] == 'L')
    {
      if($source[owner] != $playerinfo[player_id])
      {
        $l_tdr_notyourplanet = str_replace("[tdr_source_name]", $source[name], $l_tdr_notyourplanet);
        $l_tdr_notyourplanet = str_replace("[tdr_source_sector_id]", $source[sector_id], $l_tdr_notyourplanet);
        traderoute_die($l_tdr_notyourplanet);
      }
    }
    elseif($traderoute[source_type] == 'C')   // check to make sure player and planet are in the same corp.
    {
      if($source[corp] != $playerinfo[team])
      {
        $l_tdr_notyourplanet = str_replace("[tdr_source_name]", $source[name], $l_tdr_notyourplanet);
        $l_tdr_notyourplanet = str_replace("[tdr_source_sector_id]", $source[sector_id], $l_tdr_notyourplanet);
        $not_corp_planet = "$source[name] in $source[sector_id] not a Copporate Planet";
        traderoute_die($not_corp_planet);
      }
    }
	elseif($traderoute[source_type] == 'S')   // check to make sure planet is still selling
    {
      if($source[sells] != 'Y')
      {
        traderoute_die($l_tdr_notselling);
      }
    }
    //store starting port info, we'll need it later
    $result = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$source[sector_id]");
    if(!$result || $result->EOF)
      traderoute_die($l_tdr_invalidssector);

    $sourceport = $result->fields;
  }

// ********************************
// ***** Destination Check ********
// ********************************
  if($traderoute[dest_type] == 'P')
  {
    $result = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$traderoute[dest_id]");
    if(!$result || $result->EOF)
      traderoute_die($l_tdr_invaliddport);

    $dest = $result->fields;
  }
  elseif(($traderoute[dest_type] == 'L') || ($traderoute[dest_type] == 'C'))  // get data from planet table
  {
    $result = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$traderoute[dest_id]");
    if(!$result || $result->EOF)
      traderoute_die($l_tdr_invaliddplanet);

    $dest = $result->fields;

    $result = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$dest[sector_id]");
    if(!$result || $result->EOF)
      traderoute_die($l_tdr_invaliddsector);

    $destport = $result->fields;
  }

  if(!isset($sourceport))
    $sourceport=$source;
  if(!isset($destport))
    $destport=$dest;

// ***************************************************
// ***** Warp or RealSpace and generate distance *****
// ***************************************************
  if($traderoute[move_type] == 'W')
  {
    $query = $db->Execute("SELECT link_id FROM $dbtables[links] WHERE link_start=$source[sector_id] AND link_dest=$dest[sector_id]");
    if($query->EOF)
    {
      $l_tdr_nowlink1 = str_replace("[tdr_src_sector_id]", $source[sector_id], $l_tdr_nowlink1);
      $l_tdr_nowlink1 = str_replace("[tdr_dest_sector_id]", $dest[sector_id], $l_tdr_nowlink1);
      traderoute_die($l_tdr_nowlink1);
    }
    if($traderoute[circuit] == '2')
    {
      $query = $db->Execute("SELECT link_id FROM $dbtables[links] WHERE link_start=$dest[sector_id] AND link_dest=$source[sector_id]");
      if($query->EOF)
      {
        $l_tdr_nowlink2 = str_replace("[tdr_src_sector_id]", $source[sector_id], $l_tdr_nowlink2);
        $l_tdr_nowlink2 = str_replace("[tdr_dest_sector_id]", $dest[sector_id], $l_tdr_nowlink2);
        traderoute_die($l_tdr_nowlink2);
      }
      $dist[triptime] = 4;
    }
    else
      $dist[triptime] = 2;

    $dist[scooped] = 0;
  }
  else
    $dist = traderoute_distance('P', 'P', $sourceport[sector_id], $destport[sector_id], $traderoute[circuit]);


// ********************************************
// ***** Check if player has enough turns *****
// ********************************************
  if($playerinfo[turns] < $dist[triptime])
  {
    $l_tdr_moreturnsneeded = str_replace("[tdr_dist_triptime]", $dist[triptime], $l_tdr_moreturnsneeded);
    $l_tdr_moreturnsneeded = str_replace("[tdr_playerinfo_turns]", $playerinfo[turns], $l_tdr_moreturnsneeded);
    traderoute_die($l_tdr_moreturnsneeded);
  }


// ********************************
// ***** Sector Defense Check *****
// ********************************
  $hostile = 0;

  $result99 = $db->Execute("SELECT * FROM $dbtables[sector_defence] WHERE sector_id = $source[sector_id] AND player_id <> $playerinfo[player_id]");
  if(!$result99->EOF)
  {
     $fighters_owner = $result99->fields;
     $nsresult = $db->Execute("SELECT * from $dbtables[players] where player_id=$fighters_owner[player_id]");
     $nsfighters = $nsresult->fields;
     if ($nsfighters[team] != $playerinfo[team] || $playerinfo[team]==0)
            $hostile = 1;
  }

  $result98 = $db->Execute("SELECT * FROM $dbtables[sector_defence] WHERE sector_id = $dest[sector_id] AND player_id <> $playerinfo[player_id]");
  if(!$result98->EOF)
  {
     $fighters_owner = $result98->fields;
     $nsresult = $db->Execute("SELECT * from $dbtables[players] where player_id=$fighters_owner[player_id]");
     $nsfighters = $nsresult->fields;
     if ($nsfighters[team] != $playerinfo[team] || $playerinfo[team]==0)
            $hostile = 1;
  }

  if($hostile > 0 && $shipinfo[hull] > $mine_hullsize)
     traderoute_die($l_tdr_tdrhostdef);

// ***************************************
// ***** Special Port Nothing to do ******
// ***************************************
  if($traderoute[source_type] == 'P' && $source[port_type] == 'special' && $playerinfo[trade_colonists] == 'N' && $playerinfo[trade_fighters] == 'N' && $playerinfo[trade_torps] == 'N')
    traderoute_die($l_tdr_globalsetbuynothing);


// *********************************************
// ***** Check if zone allows trading  SRC *****
// *********************************************
  if($traderoute[source_type] == 'P')
  {
    $res = $db->Execute("SELECT * FROM $dbtables[zones],$dbtables[universe] WHERE $dbtables[universe].sector_id=$traderoute[source_id] AND $dbtables[zones].zone_id=$dbtables[universe].zone_id");
    $zoneinfo = $res->fields;
    if($zoneinfo[allow_trade] == 'N')
      traderoute_die($l_tdr_nosrcporttrade);
    elseif($zoneinfo[allow_trade] == 'L')
    {
      if($zoneinfo[corp_zone] == 'N')
      {
        $res = $db->Execute("SELECT team FROM $dbtables[players] WHERE player_id=$zoneinfo[owner]");
        $ownerinfo = $res->fields;

        if($playerinfo[player_id] != $zoneinfo[owner] && $playerinfo[team] == 0 || $playerinfo[team] != $ownerinfo[team])
          traderoute_die($l_tdr_tradesrcportoutsider);
      }
      else
      {
        if($playerinfo[team] != $zoneinfo[owner])
          traderoute_die($l_tdr_tradesrcportoutsider);
      }
    }
  }

// **********************************************
// ***** Check if zone allows trading  DEST *****
// **********************************************
  if($traderoute[dest_type] == 'P')
  {
    $res = $db->Execute("SELECT * FROM $dbtables[zones],$dbtables[universe] WHERE $dbtables[universe].sector_id=$traderoute[dest_id] AND $dbtables[zones].zone_id=$dbtables[universe].zone_id");
    $zoneinfo = $res->fields;
    if($zoneinfo[allow_trade] == 'N')
      traderoute_die($l_tdr_nodestporttrade);
    elseif($zoneinfo[allow_trade] == 'L')
    {
      if($zoneinfo[corp_zone] == 'N')
      {
        $res = $db->Execute("SELECT team FROM $dbtables[players] WHERE player_id=$zoneinfo[owner]");
        $ownerinfo = $res->fields;

        if($playerinfo[player_id] != $zoneinfo[owner] && $playerinfo[team] == 0 || $playerinfo[team] != $ownerinfo[team])
          traderoute_die($l_tdr_tradedestportoutsider);
      }
      else
      {
        if($playerinfo[team] != $zoneinfo[owner])
          traderoute_die($l_tdr_tradedestportoutsider);
      }
    }
  }

//--------------------------------------------------------------------------------
//--------------------------------------------------------------------------------
//---------  We're done with checks! All that's left is to make it happen --------
//--------------------------------------------------------------------------------
//--------------------------------------------------------------------------------
if ($browser == "treo") {
  echo "<table border=1 cellspacing=1 cellpadding=2 width=\"100%\" align=center>";
} else {
  echo "
    <table border=1 cellspacing=1 cellpadding=2 width=\"65%\" align=center>";
}
echo "
    <tr bgcolor=#004000><td align=\"center\" colspan=2><b><font color=white>$l_tdr_tdrres</font></b></td></tr>
    <tr align=center bgcolor=#004000>
    <td width=50%><font size=2 color=white><b>
    ";


// ------------ Determine if Source is Planet or Port
  if($traderoute[source_type] == 'P')
    echo "$l_tdr_portin $source[sector_id]";
  elseif(($traderoute[source_type] == 'L') || ($traderoute[source_type] == 'C') || ($traderoute[source_type] == 'S'))
    echo "$l_tdr_planet $source[name] in $sourceport[sector_id]";

  echo '
    </b></font></td>
    <td width=50%><font size=2 color=white><b>
    ';

// ------------ Determine if Destination is Planet or Port
  if($traderoute[dest_type] == 'P')
    echo "$l_tdr_portin $dest[sector_id]";
  elseif(($traderoute[dest_type] == 'L') || ($traderoute[dest_type] == 'C'))
    echo "$l_tdr_planet $dest[name] in $destport[sector_id]";

  echo '
    </b></font></td>
    </tr><tr bgcolor=$color_line1>
    <td align=center><font size=2 color=white>
    ';



  $sourcecost=0;

//-------- Source is Port ------------
  if($traderoute[source_type] == 'P')
  {
    //-------- Special Port Section (begin) ------
    if($source[port_type] == 'special')
    {
      $total_credits = $playerinfo[credits];
      
      if($playerinfo[trade_colonists] == 'Y')
      {
      $free_holds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[ship_colonists];
        $colonists_buy = $free_holds;
        
        if($playerinfo[credits] < $colonist_price * $colonists_buy)
          $colonists_buy = $playerinfo[credits] / $colonist_price;
        
        if($colonists_buy != 0)
          echo "$l_tdr_bought " . NUMBER($colonists_buy) . " $l_tdr_colonists<br>";
        
        $sourcecost-=$colonists_buy * $colonist_price;
        $total_credits-=$colonists_buy * $colonist_price;
      }
      else
        $colonists_buy = 0;
      
      if($playerinfo[trade_fighters] == 'Y')
      {
        $free_fighters = NUM_FIGHTERS($shipinfo[computer]) - $shipinfo[ship_fighters];
        $fighters_buy = $free_fighters;
        
        if($total_credits < $fighters_buy * $fighter_price)
          $fighters_buy = $total_credits / $fighter_price;
        
        if($fighters_buy != 0)
          echo "$l_tdr_bought " . NUMBER($fighters_buy) . " $l_tdr_fighters<br>";
        
        $sourcecost-=$fighters_buy * $fighter_price;
        $total_credits-=$fighters_buy * $fighter_price;
      }
      else
        $fighters_buy = 0;
      
      if($playerinfo[trade_torps] == 'Y')
      {
        $free_torps = NUM_FIGHTERS($shipinfo[torp_launchers]) - $shipinfo[torps];
        $torps_buy = $free_torps;
        
        if($total_credits < $torps_buy * $torpedo_price)
          $torps_buy = $total_credits / $torpedo_price;
        
        if($torps_buy != 0)
          echo "$l_tdr_bought " . NUMBER($torps_buy) . " $l_tdr_torps<br>";
        
        $sourcecost-=$torps_buy * $torpedo_price;
      }
      else
        $torps_buy = 0;
        
      if($torps_buy == 0 && $colonists_buy == 0 && $fighters_buy == 0)
        echo "$l_tdr_nothingtotrade<br>";
        
      if($traderoute[circuit] == '1')
        $db->Execute("UPDATE $dbtables[ships] SET ship_colonists=ship_colonists+$colonists_buy, ship_fighters=ship_fighters+$fighters_buy,torps=torps+$torps_buy, ship_energy=ship_energy+$dist[scooped1] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    }
//-------- Special Port Section (end) ------
//-------- Normal Port Section (begin) ------
    else
    {
      //sells commodities
	  $ore_buy = 0;
	  $goods_buy = 0;
	  $organics_buy = 0;
	  $energy_buy = 0;
	  
      if($source[port_type] != 'ore')
      {
        $ore_price1 = $ore_price + $ore_delta * $source[port_ore] / $ore_limit * $inventory_factor;
        if($source[port_ore] - $shipinfo[ship_ore] < 0)
        {
          $ore_buy = $source[port_ore];
          $portfull = 1;
        }
        else
          $ore_buy = $shipinfo[ship_ore];
        $sourcecost += $ore_buy * $ore_price1;
        if($ore_buy != 0)
        {
          if($portfull == 1)
            echo "$l_tdr_sold " . NUMBER($ore_buy) . " $l_tdr_ore ($l_tdr_portisfull)<br>";
          else
            echo "$l_tdr_sold " . NUMBER($ore_buy) . " $l_tdr_ore<br>";
        }
        $shipinfo[ship_ore] -= $ore_buy;
      }
      
      $portfull = 0;
      if($source[port_type] != 'goods')
      {
        $goods_price1 = $goods_price + $goods_delta * $source[port_goods] / $goods_limit * $inventory_factor;
        if($source[port_goods] - $shipinfo[ship_goods] < 0)
        {
          $goods_buy = $source[port_goods];
          $portfull = 1;
        }
        else
          $goods_buy = $shipinfo[ship_goods];
        $sourcecost += $goods_buy * $goods_price1;
        if($goods_buy != 0)
        {
          if($portfull == 1)
            echo "$l_tdr_sold " . NUMBER($goods_buy) . " $l_tdr_goods ($l_tdr_portisfull)<br>";
          else
            echo "$l_tdr_sold " . NUMBER($goods_buy) . " $l_tdr_goods<br>";
        }
        $shipinfo[ship_goods] -= $goods_buy;
      }
      
      $portfull = 0;
      if($source[port_type] != 'organics')
      {
        $organics_price1 = $organics_price + $organics_delta * $source[port_organics] / $organics_limit * $inventory_factor;
        if($source[port_organics] - $shipinfo[ship_organics] < 0)
        {
          $organics_buy = $source[port_organics];
          $portfull = 1;
        }
        else
          $organics_buy = $shipinfo[ship_organics];
        $sourcecost += $organics_buy * $organics_price1;
        if($organics_buy != 0)
        {
          if($portfull == 1)
            echo "$l_tdr_sold " . NUMBER($organics_buy) . " $l_tdr_organics ($l_tdr_portisfull)<br>";
          else
            echo "$l_tdr_sold " . NUMBER($organics_buy) . " $l_tdr_organics<br>";
        }
        $shipinfo[ship_organics] -= $organics_buy;
      }
      
      $portfull = 0;
      if($source[port_type] != 'energy' && $playerinfo[trade_energy] == 'Y')
      {
        $energy_price1 = $energy_price + $energy_delta * $source[port_energy] / $energy_limit * $inventory_factor;
        if($source[port_energy] - $shipinfo[ship_energy] < 0)
        {
          $energy_buy = $source[port_energy];
          $portfull = 1;
        }
        else
          $energy_buy = $shipinfo[ship_energy];
        $sourcecost += $energy_buy * $energy_price1;
        if($energy_buy != 0)
        {
          if($portfull == 1)
            echo "$l_tdr_sold " . NUMBER($energy_buy) . " $l_tdr_energy ($l_tdr_portisfull)<br>";
          else
            echo "$l_tdr_sold " . NUMBER($energy_buy) . " $l_tdr_energy<br>";
        }
        $shipinfo[ship_energy] -= $energy_buy;
      }
      
      $free_holds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[ship_colonists];
      
      //time to buy
      if($source[port_type] == 'ore')
      {
        $ore_price1 = $ore_price - $ore_delta * $source[port_ore] / $ore_limit * $inventory_factor;
        $ore_buy = $free_holds;
        if($playerinfo[credits] + $sourcecost < $ore_buy * $ore_price1)
          $ore_buy = ($playerinfo[credits] + $sourcecost) / $ore_price1;
        if($source[port_ore] < $ore_buy)
        {
          $ore_buy = $source[port_ore];
          if($source[port_ore] == 0)
            echo "$l_tdr_bought " . NUMBER($ore_buy) . " $l_tdr_ore ($l_tdr_portisempty)<br>";
        }
        if($ore_buy != 0)
          echo "$l_tdr_bought " . NUMBER($ore_buy) . " $l_tdr_ore<br>";
        $shipinfo[ship_ore] += $ore_buy;
        $sourcecost -= $ore_buy * $ore_price1;
        $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore-$ore_buy, port_energy=port_energy-$energy_buy, port_goods=port_goods-$goods_buy, port_organics=port_organics-$organics_buy WHERE sector_id=$source[sector_id]");
      }
      
      if($source[port_type] == 'goods')
      {
        $goods_price1 = $goods_price - $goods_delta * $source[port_goods] / $goods_limit * $inventory_factor;
        $goods_buy = $free_holds;
        if($playerinfo[credits] + $sourcecost < $goods_buy * $goods_price1)
          $goods_buy = ($playerinfo[credits] + $sourcecost) / $goods_price1;
        if($source[port_goods] < $goods_buy)
        {
          $goods_buy = $source[port_goods];
          if($source[port_goods] == 0)
            echo "$l_tdr_bought " . NUMBER($goods_buy) . " $l_tdr_goods ($l_tdr_portisempty)<br>";
        }
        if($goods_buy != 0)
          echo "$l_tdr_bought " . NUMBER($goods_buy) . " $l_tdr_goods<br>";
        $shipinfo[ship_goods] += $goods_buy;
        $sourcecost -= $goods_buy * $goods_price1;
        $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore-$ore_buy, port_energy=port_energy-$energy_buy, port_goods=port_goods-$goods_buy, port_organics=port_organics-$organics_buy WHERE sector_id=$source[sector_id]");
      }
      
      if($source[port_type] == 'organics')
      {
        $organics_price1 = $organics_price - $organics_delta * $source[port_organics] / $organics_limit * $inventory_factor;
        $organics_buy = $free_holds;
        if($playerinfo[credits] + $sourcecost < $organics_buy * $organics_price1)
          $organics_buy = ($playerinfo[credits] + $sourcecost) / $organics_price1;
        if($source[port_organics] < $organics_buy)
        {
          $organics_buy = $source[port_organics];
          if($source[port_organics] == 0)
            echo "$l_tdr_bought " . NUMBER($organics_buy) . " $l_tdr_organics ($l_tdr_portisempty)<br>";
        }
        if($organics_buy != 0)
          echo "$l_tdr_bought " . NUMBER($organics_buy) . " $l_tdr_organics<br>";
        $shipinfo[ship_organics] += $organics_buy;
        $sourcecost -= $organics_buy * $organics_price1;
        $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore-$ore_buy, port_energy=port_energy-$energy_buy, port_goods=port_goods-$goods_buy, port_organics=port_organics-$organics_buy WHERE sector_id=$source[sector_id]");
      }
      
      if($source[port_type] == 'energy')
      {
        $energy_price1 = $energy_price - $energy_delta * $source[port_energy] / $energy_limit * $inventory_factor;
        $energy_buy = NUM_ENERGY($shipinfo[power]) - $shipinfo[ship_energy] - $dist[scooped1];
        if($playerinfo[credits] + $sourcecost < $energy_buy * $energy_price1)
          $energy_buy = ($playerinfo[credits] + $sourcecost) / $energy_price1;
        if($source[port_energy] < $energy_buy)
        {
          $energy_buy = $source[port_energy];
          if($source[port_energy] == 0)
            echo "$l_tdr_bought " . NUMBER($energy_buy) . " $l_tdr_energy ($l_tdr_portisempty)<br>";
        }
        if($energy_buy != 0)
          echo "$l_tdr_bought " . NUMBER($energy_buy) . " $l_tdr_energy<br>";
        $shipinfo[ship_energy] += $energy_buy;
        $sourcecost -= $energy_buy * $energy_price1;
        $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore-$ore_buy, port_energy=port_energy-$energy_buy, port_goods=port_goods-$goods_buy, port_organics=port_organics-$organics_buy WHERE sector_id=$source[sector_id]");
      }
      if($dist[scooped1] > 0)
      {
        $shipinfo[ship_energy]+= $dist[scooped1];
        if($shipinfo[ship_energy] > NUM_ENERGY($shipinfo[power]))
          $shipinfo[ship_energy] = NUM_ENERGY($shipinfo[power]);
      }
      if($ore_buy == 0 && $goods_buy == 0 && $energy_buy == 0 && $organics_buy == 0) {
        echo "$l_tdr_nothingtotrade<br>";
		$tradeok = false;
	  }
      
      if($traderoute[circuit] == '1')
        $db->Execute("UPDATE $dbtables[ships] SET ship_ore=$shipinfo[ship_ore], ship_goods=$shipinfo[ship_goods], ship_organics=$shipinfo[ship_organics], ship_energy=$shipinfo[ship_energy] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    }
  }
//------------- Source is port (end) ---------
//------------- Source is planet (begin) -----
  elseif(($traderoute[source_type] == 'L') || ($traderoute[source_type] == 'C') || ($traderoute[source_type] == 'S'))
  {
  	//echo "DEBUG: Source is planet<br>";
    $free_holds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[ship_colonists];
	$free_power = NUM_ENERGY($shipinfo[power]) - $shipinfo[ship_energy];
    if($traderoute[dest_type] == 'P')
    {
      //pick stuff up to sell at port
      if(($playerinfo[player_id] == $source[owner]) || ($playerinfo[team] == $source[corp] && $playerinfo[team] !=0))
      {
        if($source[goods] > 0 && $free_holds > 0 && $dest[port_type] != 'goods')
        {
          if($source[goods] > $free_holds)
            $goods_buy = $free_holds;
          else
            $goods_buy = $source[goods];
          $free_holds -= $goods_buy;
          $shipinfo[ship_goods] += $goods_buy;
          echo "$l_tdr_loaded " . NUMBER($goods_buy) . " $l_tdr_goods<br>";
        }
        else
          $goods_buy = 0;
        
        if($source[ore] > 0 && $free_holds > 0 && $dest[port_type] != 'ore')
        {
          if($source[ore] > $free_holds)
            $ore_buy = $free_holds;
          else
            $ore_buy = $source[ore];
          $free_holds -= $ore_buy;
          $shipinfo[ship_ore] += $ore_buy;
          echo "$l_tdr_loaded " . NUMBER($ore_buy) . " $l_tdr_ore<br>";
        }
        else
          $ore_buy = 0;
        
        if($source[organics] > 0 && $free_holds > 0 && $dest[port_type] != 'organics')
        {
          if($source[organics] > $free_holds)
            $organics_buy = $free_holds;
          else
            $organics_buy = $source[organics];
          $free_holds -= $organics_buy;
          $shipinfo[ship_organics] += $organics_buy;
          echo "$l_tdr_loaded " . NUMBER($organics_buy) . " $l_tdr_organics<br>";
        }
        else
          $organics_buy = 0;

        if($ore_buy == 0 && $goods_buy == 0 && $organics_buy == 0)
          echo "$l_tdr_nothingtoload<br>";

        if($traderoute[circuit] == '1')
          $db->Execute("UPDATE $dbtables[ships] SET ship_ore=$shipinfo[ship_ore], ship_goods=$shipinfo[ship_goods], ship_organics=$shipinfo[ship_organics] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		$db->Execute("UPDATE $dbtables[planets] SET ore=ore-$ore_buy, goods=goods-$goods_buy, organics=organics-$organics_buy WHERE planet_id=$source[planet_id]");

      }
	  else  //buy from planet
      {
	  //echo "DEBUG: Buy from planet<br>";
	  	// Basically the trade route automatically selects the best value purchase every trip based on current selling price
		// and the price at the port. Isn't that cool?
		// Find out the best arbitrage opportunity
		// Get the sales prices at this planet
		$result3 = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id=$source[owner]");
    	$ownerinfo = $result3->fields;
		$ore_price1 = $ownerinfo[ore_price];
        $organics_price1 = $ownerinfo[organics_price];
        $goods_price1 = $ownerinfo[goods_price];
		//echo "DEBUG: $goods_price1<br>";
        $energy_price1 = $ownerinfo[energy_price];
		$free_power = NUM_ENERGY($shipinfo[power]) - $shipinfo[ship_energy];
		// If a price is zero then the commoditiy is not for sale
		$ore_opp = ($ore_price + $ore_delta * $dest[port_ore] / $ore_limit * $inventory_factor) - $ownerinfo[ore_price];
		$goods_opp = ($goods_price + $goods_delta * $dest[port_goods] / $goods_limit * $inventory_factor) - $ownerinfo[goods_price];
		$organics_opp = ($organics_price + $organics_delta * $dest[port_organics] / $organics_limit * $inventory_factor) - $ownerinfo[organics_price];
		$energy_opp = ($energy_price + $energy_delta * $dest[port_energy] / $energy_limit * $inventory_factor) - $ownerinfo[energy_price];
		//echo "Opportunity: Ore=$ore_opp, Goods=$goods_opp, Energy=$energy_opp Organics=$organics_opp<br>";
		$forsale = array (
				commodity => array("ore","goods","organics","energy"),
				opportunity => array("ore"=>$ore_opp,"goods"=>$goods_opp,"organics"=>$organics_opp,"energy"=>$energy_opp),
				price => array("ore"=>$ownerinfo[ore_price], "goods"=>$ownerinfo[goods_price], "organics"=>$ownerinfo[organics_price], "energy"=>$ownerinfo[energy_price]),
				bought => array("ore"=>0,"goods"=>0,"organics"=>0,"energy"=>0)
				);
		// Sort by best opportunity
		array_multisort($forsale[opportunity],SORT_NUMERIC, SORT_DESC);
		$best = array_keys($forsale[opportunity]);
		// Now run through the array from best to worst opportunity
		reset ($best); 
		while( $res=each($best) ) {
			//echo "This commoditity is $res[1] and the planet has ".$source[$res[1]]." of it. My free holds are $free_holds<br>"; 
			//echo "The destination port is $dest[port_type]<br>";
			$tempPrice = $res[1]."_price";
			// Commodities
			if ($res[1] != 'energy') {
				if($source[$res[1]] > 0 && $free_holds > 0 && $dest[port_type] != $res[1] && $ownerinfo[$tempPrice] > 0)
				{
				  // Calculate the most that I can get
				  if($source[$res[1]] > $free_holds)
					$forsale[bought][$res[1]] = $free_holds;
				  else
					$forsale[bought][$res[1]] = $source[$res[1]];
				  //echo "The most I can hold is ".$forsale[bought][$res[1]]."<br>";
				  // Now how much can I afford?
				  //echo "I have $playerinfo[credits] credits and the price is ".$forsale[price][$res[1]]." credits<br>";
				  if($playerinfo[credits] < ($forsale[bought][$res[1]]*$forsale[price][$res[1]]))
					$forsale[bought][$res[1]] = floor($playerinfo[credits]/$forsale[price][$res[1]]);
				  //echo "The most I can afford is ".$forsale[bought][$res[1]]."<br>";
				  $sourcecost -= $forsale[bought][$res[1]]*$forsale[price][$res[1]];
				  $free_holds -= $forsale[bought][$res[1]];
				  $ship_hold = "ship_".$res[1];
				  $shipinfo[$ship_hold] += $forsale[bought][$res[1]];
				  echo "$l_tdr_loaded " . NUMBER($forsale[bought][$res[1]]) . " $res[1]<br>";
				}
				else
				  $forsale[bought][$res[1]] = 0;
			} else {
				// Energy Sale
				if($source[$res[1]] > 0 && $free_power > 0 && $dest[port_type] != $res[1] && $ownerinfo[$tempPrice] > 0)
				{
				  // Calculate the most that I can get
				  if($source[$res[1]] > $free_power)
					$forsale[bought][$res[1]] = $free_power;
				  else
					$forsale[bought][$res[1]] = $source[$res[1]];
				  //echo "The most I can hold is ".$forsale[bought][$res[1]]."<br>";
				  // Now how much can I afford?
				  //echo "I have $playerinfo[credits] credits and the price is ".$forsale[price][$res[1]]." credits<br>";
				  if($playerinfo[credits] < ($forsale[bought][$res[1]]*$forsale[price][$res[1]]))
					$forsale[bought][$res[1]] = floor($playerinfo[credits]/$forsale[price][$res[1]]);
				  //echo "The most I can afford is ".$forsale[bought][$res[1]]."<br>";
				  $sourcecost -= $forsale[bought][$res[1]]*$forsale[price][$res[1]];
				  $free_holds -= $forsale[bought][$res[1]];
				  $ship_hold = "ship_".$res[1];
				  $shipinfo[$ship_hold] += $forsale[bought][$res[1]];
				  echo "$l_tdr_loaded " . NUMBER($forsale[bought][$res[1]]) . " $res[1]<br>";
				}
				else
				  $forsale[bought][$res[1]] = 0;
			}			
		} 
        if($forsale[bought][ore] == 0 && $forsale[bought][goods] == 0 && $forsale[bought][organics] == 0 && $forsale[bought][energy] == 0)
          echo "$l_tdr_nothingtoload<br>";

        if($traderoute[circuit] == '1')
          $db->Execute("UPDATE $dbtables[ships] SET ship_ore=$shipinfo[ship_ore], ship_goods=$shipinfo[ship_goods], ship_organics=$shipinfo[ship_organics], ship_energy=$shipinfo[ship_energy] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
	//echo "DEBUG: $sourcecost<br>";
	    $planetProfit = -$sourcecost;
	    $db->Execute("UPDATE $dbtables[planets] SET ore=ore-".$forsale[bought][ore].", goods=goods-".$forsale[bought][goods].", organics=organics-".$forsale[bought][organics].", energy=energy-".$forsale[bought][energy].", credits=credits+$planetProfit WHERE planet_id=$source[planet_id]");
		}
    }
// ---------- destination is a planet, so load cols and weapons and energy if requested
    else if(($traderoute[dest_type] == 'L') || ($traderoute[dest_type] == 'C'))
    {
      if($source[colonists] > 0 && $free_holds > 0 && $playerinfo[trade_colonists] == 'Y')
      {
        if($source[colonists] > $free_holds)
          $colonists_buy = $free_holds;
        else
          $colonists_buy = $source[colonists];
        $free_holds -= $colonists_buy;
        $shipinfo[ship_colonists] += $colonists_buy;
        echo "$l_tdr_loaded " . NUMBER($colonists_buy) . " $l_tdr_colonists<br>";
      }
      else
        $colonists_buy = 0;
 
       if($source[energy] > 0 && $free_power > 0 && $playerinfo[trade_energy] == 'Y')
      {
        if($source[energy] > $free_power)
          $energy_buy = $free_power;
        else
          $energy_buy = $source[energy];
        $free_holds -= $energy_buy;
        $shipinfo[ship_energy] += $energy_buy;
        echo "$l_tdr_loaded " . NUMBER($energy_buy) . " $l_tdr_energy<br>";
      }
      else
        $energy_buy = 0;
      
     
      $free_torps = NUM_TORPEDOES($shipinfo[torp_launchers]) - $shipinfo[torps];
      if($source[torps] > 0 && $free_torps > 0 && $playerinfo[trade_torps] == 'Y')
      {
        if($source[torps] > $free_torps)
          $torps_buy = $free_torps;
        else
          $torps_buy = $source[torps];
        $free_torps -= $torps_buy;
        $shipinfo[torps] += $torps_buy;
        echo "$l_tdr_loaded " . NUMBER($torps_buy) . " $l_tdr_torps<br>";
      }
      else
        $torps_buy = 0;
      
      $free_fighters = NUM_FIGHTERS($shipinfo[computer]) - $shipinfo[ship_fighters];
      if($source[fighters] > 0 && $free_fighters > 0 && $playerinfo[trade_fighters] == 'Y')
      {
        if($source[fighters] > $free_fighters)
          $fighters_buy = $free_fighters;
        else
          $fighters_buy = $source[fighters];
        $free_fighters -= $fighters_buy;
        $shipinfo[ship_fighters] += $fighters_buy;
        echo "$l_tdr_loaded " . NUMBER($fighters_buy) . " $l_tdr_fighters<br>";
      }
      else
        $fighters_buy = 0;
      
      if($fighters_buy == 0 && $torps_buy == 0 && $colonists_buy == 0 && $energy_buy == 0)
        echo "$l_tdr_nothingtoload<br>";
      
      if($traderoute[circuit] == '1')
        $db->Execute("UPDATE $dbtables[ships] SET torps=$shipinfo[torps], ship_fighters=$shipinfo[ship_fighters], ship_colonists=$shipinfo[ship_colonists], ship_energy=$shipinfo[ship_energy] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		//echo "DEBUG: UPDATE $dbtables[planets] SET colonists=colonists-$colonists_buy, torps=torps-$torps_buy, fighters=fighters-$fighters_buy, energy=energy-$energy_buy WHERE planet_id=$source[planet_id]<br>";
      $db->Execute("UPDATE $dbtables[planets] SET colonists=colonists-$colonists_buy, torps=torps-$torps_buy, fighters=fighters-$fighters_buy, energy=energy-$energy_buy WHERE planet_id=$source[planet_id]");
    }
  }

  if($dist[scooped1] != 0)
    echo "$l_tdr_scooped " . NUMBER($dist[scooped1]) . " $l_tdr_energy<br>";

  echo '
    </font></td>
    <td align=center><font size=2 color=white>
  ';
// *****  Now we do the destination *******
  if($traderoute[circuit] == '2')
  {
    $playerinfo[credits] += $sourcecost;
    $destcost = 0;
    if($traderoute[dest_type] == 'P')
    {
	  $ore_buy = 0;
	  $goods_buy = 0;
	  $organics_buy = 0;
	  $energy_buy = 0;

      //sells commodities
      $portfull = 0;
      if($dest[port_type] != 'ore')
      {
        $ore_price1 = $ore_price + $ore_delta * $dest[port_ore] / $ore_limit * $inventory_factor;
        if($dest[port_ore] - $shipinfo[ship_ore] < 0)
        {
          $ore_buy = $dest[port_ore];
          $portfull = 1;
        }
        else
          $ore_buy = $shipinfo[ship_ore];
        $destcost += $ore_buy * $ore_price1;
        if($ore_buy != 0)
        {
          if($portfull == 1)
            echo "$l_tdr_sold " . NUMBER($ore_buy) . " $l_tdr_ore ($l_tdr_portisfull)<br>";
          else
            echo "$l_tdr_sold " . NUMBER($ore_buy) . " $l_tdr_ore<br>";
        }
        $shipinfo[ship_ore] -= $ore_buy;
      }

      $portfull = 0;
      if($dest[port_type] != 'goods')
      {
        $goods_price1 = $goods_price + $goods_delta * $dest[port_goods] / $goods_limit * $inventory_factor;
        if($dest[port_goods] - $shipinfo[ship_goods] < 0)
        {
          $goods_buy = $dest[port_goods];
          $portfull = 1;
        }
        else
          $goods_buy = $shipinfo[ship_goods];
        $destcost += $goods_buy * $goods_price1;
        if($goods_buy != 0)
        {
          if($portfull == 1)
            echo "$l_tdr_sold " . NUMBER($goods_buy) . " $l_tdr_goods ($l_tdr_portisfull)<br>";
          else
            echo "$l_tdr_sold " . NUMBER($goods_buy) . " $l_tdr_goods<br>";
        }
        $shipinfo[ship_goods] -= $goods_buy;
      }

      $portfull = 0;
      if($dest[port_type] != 'organics')
      {
        $organics_price1 = $organics_price + $organics_delta * $dest[port_organics] / $organics_limit * $inventory_factor;
        if($dest[port_organics] - $shipinfo[ship_organics] < 0)
        {
          $organics_buy = $dest[port_organics];
          $portfull = 1;
        }
        else
          $organics_buy = $shipinfo[ship_organics];
        $destcost += $organics_buy * $organics_price1;
        if($organics_buy != 0)
        {
          if($portfull == 1)
            echo "$l_tdr_sold " . NUMBER($organics_buy) . " $l_tdr_organics ($l_tdr_portisfull)<br>";
          else
            echo "$l_tdr_sold " . NUMBER($organics_buy) . " $l_tdr_organics<br>";
        }
        $shipinfo[ship_organics] -= $organics_buy;
      }

      $portfull = 0;
      if($dest[port_type] != 'energy' && $playerinfo[trade_energy] == 'Y')
      {
        $energy_price1 = $energy_price + $energy_delta * $dest[port_energy] / $energy_limit * $inventory_factor;
        if($dest[port_energy] - $shipinfo[ship_energy] < 0)
        {
          $energy_buy = $dest[port_energy];
          $portfull = 1;
        }
        else
          $energy_buy = $shipinfo[ship_energy];
        $destcost += $energy_buy * $energy_price1;
        if($energy_buy != 0)
        {
          if($portfull == 1)
            echo "$l_tdr_sold " . NUMBER($energy_buy) . " $l_tdr_energy ($l_tdr_portisfull)<br>";
          else
            echo "$l_tdr_sold " . NUMBER($energy_buy) . " $l_tdr_energy<br>";
        }
        $shipinfo[ship_energy] -= $energy_buy;
      }
      else
        $energy_buy = 0;

      $free_holds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[ship_colonists];

      //time to buy
      if($dest[port_type] == 'ore')
      {
        $ore_price1 = $ore_price - $ore_delta * $dest[port_ore] / $ore_limit * $inventory_factor;
        if($traderoute[source_type] == 'L' | $traderoute[source_type] == 'S' | $traderoute[source_type] == 'C')
          $ore_buy = 0;
        else
        {
          $ore_buy = $free_holds;
          if($playerinfo[credits] + $destcost < $ore_buy * $ore_price1)
          $ore_buy = ($playerinfo[credits] + $destcost) / $ore_price1;
          if($dest[port_ore] < $ore_buy)
          {
            $ore_buy = $dest[port_ore];
            if($dest[port_ore] == 0)
              echo "$l_tdr_bought " . NUMBER($ore_buy) . " $l_tdr_ore ($l_tdr_portisempty)<br>";
          }
          if($ore_buy != 0)
            echo "$l_tdr_bought " . NUMBER($ore_buy) . " $l_tdr_ore<br>";
          $shipinfo[ship_ore] += $ore_buy;
          $destcost -= $ore_buy * $ore_price1;
        }
        $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore-$ore_buy, port_energy=port_energy-$energy_buy, port_goods=port_goods-$goods_buy, port_organics=port_organics-$organics_buy WHERE sector_id=$dest[sector_id]");
      }

      if($dest[port_type] == 'goods')
      {
        $goods_price1 = $goods_price - $goods_delta * $dest[port_goods] / $goods_limit * $inventory_factor;
        if($traderoute[source_type] == 'L' | $traderoute[source_type] == 'S' | $traderoute[source_type] == 'C')
          $goods_buy = 0;
        else
        {
          $goods_buy = $free_holds;
          if($playerinfo[credits] + $destcost < $goods_buy * $goods_price1)
            $goods_buy = ($playerinfo[credits] + $destcost) / $goods_price1;
          if($dest[port_goods] < $goods_buy)
          {
            $goods_buy = $dest[port_goods];
            if($dest[port_goods] == 0)
              echo "$l_tdr_bought " . NUMBER($goods_buy) . " $l_tdr_goods ($l_tdr_portisempty)<br>";
          }
          if($goods_buy != 0)
            echo "$l_tdr_bought " . NUMBER($goods_buy) . " $l_tdr_goods<br>";
          $shipinfo[ship_goods] += $goods_buy;
          $destcost -= $goods_buy * $goods_price1;
        }
        $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore-$ore_buy, port_energy=port_energy-$energy_buy, port_goods=port_goods-$goods_buy, port_organics=port_organics-$organics_buy WHERE sector_id=$dest[sector_id]");
      }

      if($dest[port_type] == 'organics')
      {
        $organics_price1 = $organics_price - $organics_delta * $dest[port_organics] / $organics_limit * $inventory_factor;
        if($traderoute[source_type] == 'L' | $traderoute[source_type] == 'S' | $traderoute[source_type] == 'C')
          $organics_buy = 0;
        else
        {
          $organics_buy = $free_holds;
          if($playerinfo[credits] + $destcost < $organics_buy * $organics_price1)
            $organics_buy = ($playerinfo[credits] + $destcost) / $organics_price1;
          if($dest[port_organics] < $organics_buy)
          {
            $organics_buy = $dest[port_organics];
            if($dest[port_organics] == 0)
              echo "$l_tdr_bought " . NUMBER($organics_buy) . " $l_tdr_organics ($l_tdr_portisempty)<br>";
          }
          if($organics_buy != 0)
            echo "$l_tdr_bought " . NUMBER($organics_buy) . " $l_tdr_organics<br>";
          $shipinfo[ship_organics] += $organics_buy;
          $destcost -= $organics_buy * $organics_price1;
        }
        $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore-$ore_buy, port_energy=port_energy-$energy_buy, port_goods=port_goods-$goods_buy, port_organics=port_organics-$organics_buy WHERE sector_id=$dest[sector_id]");
      }

      if($dest[port_type] == 'energy')
      {
        $energy_price1 = $energy_price - $energy_delta * $dest[port_energy] / $energy_limit * $inventory_factor;
        if($traderoute[source_type] == 'L' | $traderoute[source_type] == 'S' | $traderoute[source_type] == 'C')
          $energy_buy = 0;
        else
        {
          $energy_buy = NUM_ENERGY($shipinfo[power]) - $shipinfo[ship_energy] - $dist[scooped1];
          if($playerinfo[credits] + $destcost < $energy_buy * $energy_price1)
            $energy_buy = ($playerinfo[credits] + $destcost) / $energy_price1;
          if($dest[port_energy] < $energy_buy)
          {
            $energy_buy = $dest[port_energy];
            if($dest[port_energy] == 0)
              echo "$l_tdr_bought " . NUMBER($energy_buy) . " $l_tdr_energy ($l_tdr_portisempty)<br>";
          }
          if($energy_buy != 0)
            echo "$l_tdr_bought " . NUMBER($energy_buy) . " $l_tdr_energy<br>";
          $shipinfo[ship_energy] += $energy_buy;
          $destcost -= $energy_buy * $energy_price1;
        }

        $db->Execute("UPDATE $dbtables[universe] SET port_ore=port_ore-$ore_buy, port_energy=port_energy-$energy_buy, port_goods=port_goods-$goods_buy, port_organics=port_organics-$organics_buy WHERE sector_id=$dest[sector_id]");
      }
	  
      if($ore_buy == 0 && $goods_buy == 0 && $energy_buy == 0 && $organics_buy == 0) {
          echo "$l_tdr_nothingtotrade<br>";
		  $tradeok =false;
	  }

      if($dist[scooped2] > 0)
      {
        $shipinfo[ship_energy]+= $dist[scooped2];
        if($shipinfo[ship_energy] > NUM_ENERGY($shipinfo[power]))
          $shipinfo[ship_energy] = NUM_ENERGY($shipinfo[power]);
      }
      $db->Execute("UPDATE $dbtables[ships] SET ship_ore=$shipinfo[ship_ore], ship_goods=$shipinfo[ship_goods], ship_organics=$shipinfo[ship_organics], ship_energy=$shipinfo[ship_energy] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    }
    else //dest is planet
    {
      if($traderoute[source_type] == 'L' || $traderoute[source_type] == 'C')
      {
        $colonists_buy=0;
        $fighters_buy=0;
        $torps_buy=0;
		$energy_buy=0;
      }

      if($playerinfo[trade_colonists] == 'Y')
      {
        $colonists_buy += $shipinfo[ship_colonists];
        $col_dump = $shipinfo[ship_colonists];
        if($dest[colonists] + $colonists_buy >= $colonist_limit)
        {
          $exceeding = $dest[colonists] + $colonists_buy - $colonist_limit;
          $col_dump = $exceeding;
          $setcol = 1;
          $colonists_buy-=$exceeding;
          if($colonists_buy < 0)
            $colonists_buy = 0;
        }
      }
      else
        $col_dump = 0;

      if($colonists_buy != 0)
      {
        if($setcol ==1)
          echo "$l_tdr_dumped " . NUMBER($colonists_buy) . " $l_tdr_colonists ($l_tdr_planetisovercrowded)<br>";
        else
          echo "$l_tdr_dumped " . NUMBER($colonists_buy) . " $l_tdr_colonists<br>";
      }

      if($playerinfo[trade_fighters] == 'Y')
      {
        $fighters_buy += $shipinfo[ship_fighters];
        $fight_dump = $shipinfo[ship_fighters];
      }
      else
        $fight_dump = 0;

      if($fighters_buy != 0)
        echo "$l_tdr_dumped " . NUMBER($fighters_buy) . " $l_tdr_fighters<br>";

      if($playerinfo[trade_torps] == 'Y')
      {
        $torps_buy += $shipinfo[torps];
        $torps_dump = $shipinfo[torps];
      }
      else
        $torps_dump = 0;

      if($torps_buy != 0)
        echo "$l_tdr_dumped " . NUMBER($torps_buy) . " $l_tdr_torps<br>";

      if($playerinfo[trade_energy] == 'Y')
      {
        $energy_buy += $shipinfo[ship_energy]+$dist[scooped];
        $energy_dump = $shipinfo[ship_energy]+$dist[scooped];
      }
      else
        $energy_dump = 0;

      if($energy_buy != 0)
        echo "$l_tdr_dumped " . NUMBER($energy_buy) . " $l_tdr_energy<br>";

      if($energy_buy == 0 && $torps_buy == 0 && $fighters_buy == 0 && $colonists_buy == 0 && $organics_buy == 0)
        echo "$l_tdr_nothingtodump<br>";

      if($traderoute[source_type] == 'L' || $traderoute[source_type] == 'C')
      {
        if($playerinfo[trade_colonists] == 'Y')
        {
          if($setcol != 1)
            $col_dump = 0;
        }
        else
          $col_dump = $shipinfo[ship_colonists];

        if($playerinfo[trade_fighters] == 'Y')
          $fight_dump = 0;
        else
          $fight_dump = $shipinfo[ship_fighters];

        if($playerinfo[trade_torps] == 'Y')
          $torps_dump = 0;
        else
          $torps_dump = $shipinfo[torps];
		  
		if($playerinfo[trade_energy] == 'Y')
          $energy_dump = 0;
        else
          $energy_dump = $shipinfo[ship_energy]+$dist[scooped];
      }
      $db->Execute("UPDATE $dbtables[planets] SET colonists=colonists+$colonists_buy, fighters=fighters+$fighters_buy, torps=torps+$torps_buy, energy=energy+$energy_buy WHERE planet_id=$traderoute[dest_id]");

      if($traderoute[source_type] == 'L' || $traderoute[source_type] == 'C')
      {
        $db->Execute("UPDATE $dbtables[ships] SET ship_colonists=$col_dump, ship_fighters=$fight_dump, torps=$torps_dump, ship_energy=$energy_dump WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
      }
      else
      {
        if($setcol == 1) {
         $db->Execute("UPDATE $dbtables[ships] SET ship_colonists=$col_dump, ship_fighters=ship_fighters-$fight_dump, torps=torps-$torps_dump, ship_energy=ship_energy+$dist[scooped]-$energy_dump WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		 }
        else {
          $db->Execute("UPDATE $dbtables[ships] SET ship_colonists=ship_colonists-$col_dump, ship_fighters=ship_fighters-$fight_dump, torps=torps-$torps_dump, ship_energy=ship_energy+$dist[scooped]-$energy_dump WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		 }
      }
    }
    if($dist[scooped2] != 0)
    {
      echo "$l_tdr_scooped " . NUMBER($dist[scooped1]) . " $l_tdr_energy<br>";
    }

  }
  else
  {
    echo $l_tdr_onlyonewaytdr;
    $destcost = 0;
  }

  echo "</font></td></tr><tr bgcolor=#004000><td align=center><font size=2 color=white>";

  if($sourcecost > 0)
    echo "$l_tdr_profit : " . NUMBER(abs($sourcecost));
  else
    echo "$l_tdr_cost : " . NUMBER(abs($sourcecost));

  echo "</font></td><td align=center><font size=2 color=white>";

  if($destcost > 0)
    echo "$l_tdr_profit : " . NUMBER(abs($destcost));
  else
    echo "$l_tdr_cost : " . NUMBER(abs($destcost));

  echo '
    </font></td></tr>
    </table>
    <p>
    <center>
    <font size=3 color=white><b>
    ';

  $total_profit = $sourcecost + $destcost;
  if($total_profit > 0)
    echo "$l_tdr_totalprofit : <font color=#00ff00>" . NUMBER(abs($total_profit)) . "</font></b><p>";
  else
    echo "$l_tdr_totalcost : <font color=red>" . NUMBER(abs($total_profit)) . "</font></b><br>";

  if($traderoute[circuit] == '1')
    $newsec = $destport[sector_id];
  else
    $newsec = $sourceport[sector_id];

  $db->Execute("UPDATE $dbtables[players] SET turns=turns-$dist[triptime], credits=credits+$total_profit, turns_used=turns_used+$dist[triptime], sector=$newsec WHERE player_id=$playerinfo[player_id]");
  $playerinfo[credits]+=$total_profit - $sourcecost;
  $playerinfo[turns]-=$dist[triptime];

  echo "<font size=3 color=white><b>$l_tdr_turnsused : <font color=red>$dist[triptime]</font></b><br>";
  echo "<font size=3 color=white><b>$l_tdr_turnsleft : <font color=#00ff00>$playerinfo[turns]</font></b><br><p>";

  echo "<font size=3 color=white><b>$l_tdr_credits : <font color=#00ff00>" . NUMBER($playerinfo[credits]) . "</font></b><br></center><p><font size=2>";

// ===============
  if($traderoute[circuit] == 2)
  {
    $l_tdr_engageagain = str_replace("[tdr_engage]", $engage, $l_tdr_engageagain);
    if($j == 1)
    {
       echo "$l_tdr_engageagain";
       echo "<FORM ACTION=traderoute.php?engage=$engage METHOD=POST>" .
            "<BR>Enter times to repeat <INPUT TYPE=TEXT NAME=tr_repeat VALUE=1 SIZE=5> <INPUT TYPE=SUBMIT VALUE=Submit>";
       echo "<p>";
    }
  }
// ===============
  if($j == 1)
     traderoute_die("");
  return $tradeok;
}
?>
