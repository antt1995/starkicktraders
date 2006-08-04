<?php
$flag = checklogin();
if($flag)
{
	if ($flag == 2) {
		die("&fl=subscribe");
	} else {
		 die("&fl=die");
	}
}

$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;
if($playerinfo['cleared_defences'] > ' ')
{
   echo "&fl=incomplete";
   die();
}


$res = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]'");
$sectorinfo = $res->fields;

srand((double)microtime() * 1000000);

if($playerinfo[on_planet] == "Y")
{
  $res2 = $db->Execute("SELECT planet_id, owner FROM $dbtables[planets] WHERE planet_id=$playerinfo[planet_id]");
  if($res2->RecordCount() != 0)
  {
	$planet_id=$playerinfo[planet_id];
	$id=$playerinfo[player_id];
	include("metaplanet.php");
    //-------------------------------------------------------------------------------------------------
    die();
  }
  else
  {
    $db->Execute("UPDATE $dbtables[players] SET on_planet='N' WHERE player_id=$playerinfo[player_id]");
    //echo "&fl=noplanet"; Not required
  }
}

$res = $db->Execute("SELECT * FROM $dbtables[links] WHERE link_start='$playerinfo[sector]' ORDER BY link_dest ASC");

$i = 0;
if($res > 0)
{
  while(!$res->EOF)
  {
    $links[$i] = $res->fields[link_dest];
    $i++;
    $res->MoveNext();
  }
}
$num_links = $i;

$res = $db->Execute("SELECT * FROM $dbtables[planets] WHERE sector_id='$playerinfo[sector]'");

$i = 0;
if($res > 0)
{
  while(!$res->EOF)
  {
    $planets[$i] = $res->fields;
    $i++;
    $res->MoveNext();
  }
}
$num_planets = $i;

$res = $db->Execute("SELECT * FROM $dbtables[sector_defence],$dbtables[players] WHERE $dbtables[sector_defence].sector_id='$playerinfo[sector]'
                                                    AND $dbtables[players].player_id = $dbtables[sector_defence].player_id ");
$i = 0;
if($res > 0)
{
  while(!$res->EOF)
  {
    $defences[$i] = $res->fields;
    $i++;
    $res->MoveNext();
  }
}
$num_defences = $i;

$res = $db->Execute("SELECT zone_id,zone_name FROM $dbtables[zones] WHERE zone_id='$sectorinfo[zone_id]'");
$zoneinfo = $res->fields;
// New ship code
// Find out what my ship is
$res = $db->Execute("SELECT * FROM $dbtables[ship_types],$dbtables[ships] WHERE $dbtables[ship_types].type_id=$dbtables[ships].type AND $dbtables[ships].player_id=$playerinfo[player_id] AND $dbtables[ships].ship_id=$playerinfo[currentship]");
$shipinfo = $res->fields;

// Mission check
include("mission.php");
echo "&insignia=".urlencode(player_insignia_name($username));
echo "&name=".urlencode($playerinfo[character_name]);
echo "&shipname=".urlencode($shipinfo[ship_name]);
if ($shipinfo[tow]>0) {
	$res = $db->Execute("SELECT * FROM $dbtables[ships],$dbtables[ship_types] WHERE ship_id=$shipinfo[tow] AND type_id=type");
	$towedship = $res->fields;
	echo "&tow=".urlencode($towedship[ship_name]);
}
$result = $db->Execute("SELECT * FROM $dbtables[messages] WHERE recp_id='".$playerinfo[player_id]."' AND notified='N'");
if ($result->RecordCount() > 0)
 {
	echo "&mess=".$result->RecordCount();
 }
echo "&turns=$playerinfo[turns]";
echo "&turns_used=$playerinfo[turns_used]";
echo "&score=$playerinfo[score]";
echo "&sector=$playerinfo[sector]";
if(!empty($sectorinfo[beacon]))
{
  echo "&beacon=".urlencode($sectorinfo[beacon]);
}

if($zoneinfo[zone_id] < 5)
  $zoneinfo[zone_name] = $l_zname[$zoneinfo[zone_id]];

echo "&zone=".urlencode($zoneinfo[zone_name]);

// Warp links

if(!$num_links)
{
  echo "&links=N";
}
else
{
  $link_bnthelper_string="&links=";
  for($i=0; $i<$num_links;$i++)
  {
     $link_bnthelper_string=$link_bnthelper_string . ":" . $links[$i];
  }
}
echo $link_bnthelper_string;

// Subscribed
if (!strstr($playerinfo[subscribed],"payment")) {
	echo "&subscribed=N";
}

// Trading port

if($sectorinfo[port_type] != "none")
{
  echo "&port=" . $sectorinfo[port_type] . ":" . $sectorinfo[port_ore] . ":" . $sectorinfo[port_organics] . ":" . $sectorinfo[port_goods] . ":" . $sectorinfo[port_energy].":";
}
else
{
  echo "&port=none:0:0:0:0:";
}
// ******************* Planets in Sector ***********************//
if($num_planets > 0)
{
  $totalcount=0;
  $curcount=0;
  $i=0;
  echo "&planet=Y";
  while($i < $num_planets)
  {
    if($planets[$i][owner] != 0)
    {
      $result5 = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id=" . $planets[$i][owner]);
      $planet_owner = $result5->fields;
	  $result6 = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=" . $planets[$i][owner]." AND ship_id=$planet_owner[currentship]");
      $planet_owner_ship = $result6->fields;

      $planetavg = $planet_owner_ship[hull] + $planet_owner_ship[engines] + $planet_owner_ship[computer] + $planet_owner_ship[beams] + $planet_owner_ship[torp_launchers] + $planet_owner_ship[shields] + $planet_owner_ship[armour];
      $planetavg /= 7;

      if($planetavg < 8)
        $planetlevel = 0;
      else if ($planetavg < 12)
        $planetlevel = 1;
      else if ($planetavg < 16)
        $planetlevel = 2;
      else if ($planetavg < 20)
        $planetlevel = 3;
      else
        $planetlevel = 4;
    }
    else
      $planetlevel=0;
    echo "&planet$i=$planetlevel:";
    if(empty($planets[$i][name]))
    {
      echo $l_unnamed.":";
    }
    else
    {
      echo urlencode($planets[$i][name]).":";
    }
    if($planets[$i][owner] == 0)
    {
      echo "Unowned::";
    }
    else
    {
       echo urlencode($planet_owner[character_name]).":";
	   if ($planets[$i][corp] !=0) {
	  		$result7 = $db->Execute("SELECT team_name FROM $dbtables[teams] WHERE id=" . $planets[$i][corp]." LIMIT 1");
			$row = $result7->fields;
			echo urlencode($row[team_name]);
	   }	   
		echo ":";
    }
    $i++;
  }
}
else
{
 echo "&planet=N";
}
//****************************  Ships in sector ****************************/
if($playerinfo[sector] != 0)
{
	/****************************** NEW CODE  *******************************/
    $result4 = $db->Execute("SELECT $dbtables[ships].*,$dbtables[ship_types].image,$dbtables[players].character_name,$dbtables[teams].team_name FROM $dbtables[ships],$dbtables[ship_types],$dbtables[players] LEFT OUTER JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id WHERE $dbtables[ships].player_id != $playerinfo[player_id] AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].on_planet='N' AND type_id=type AND $dbtables[ships].player_id=$dbtables[players].player_id");
   	$totalcount=0;
    while(!$result4->EOF)
    {
    	$targetship=$result4->fields;
		// Is the ship owned or not? Unowned ships are always visible
		if ($targetship[player_id]==0) {
			echo "&ship$totalcount=".urlencode($targetship[ship_name]).":Unowned";
			$totalcount++;
        } else { 	
			// If the ship is towing anything then it's cloak is going to be weakened
			if ($targetship[tow]>0) {
				$targetship[cloak] = min($targetship[cloak]-1,0);
			}
			$success = SCAN_SUCCESS($shipinfo[sensors], $targetship[cloak]);
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
				if ($targetship[team_name]) {
					echo "&ship$totalcount=".urlencode($targetship[ship_name]).":".urlencode($targetship[character_name]).":".urlencode($targetship[team_name]).":";
				}
				else
				{
					echo "&ship$totalcount=".urlencode($targetship[ship_name]).":".urlencode($targetship[character_name])."::";
				}
				// ********** Display any towed ship too ************* //
				if ($targetship[tow] > 0) {
					$result6 = $db->Execute("SELECT * FROM $dbtables[ships],$dbtables[ship_types] WHERE ship_id=$targetship[tow] AND type_id=type");
					$towedship = $result6->fields;
					echo urlencode($towedship[ship_name]);
				}
				echo ":";	
				$totalcount++;
			} // End if success roll
		} // End If owned ship or not
        $result4->MoveNext();
	} // End while ships in sector
	if($totalcount == 0)
    {
    	echo "&ships=N";
		$displayed=true;
    }
}
else
{
    echo "&ships=sector0";
}
// Sector defenses
if($num_defences > 0)
{
  $totalcount=0;
  $curcount=0;
  $i=0;
  while($i < $num_defences)
  {
    $defence_id = $defences[$i]['defence_id'];
    $def_type = $defences[$i]['defence_type'].":".$defences[$i]['fm_setting'];
    $char_name = $defences[$i]['character_name'];
    $qty = $defences[$i]['quantity'];
    echo "&secdef$i=".urlencode($char_name).":$qty:$def_type:";
    $totalcount++;
    $i++;
  }
}
// Presets
echo "&presets=$playerinfo[preset1]:$playerinfo[preset2]:$playerinfo[preset3]:$playerinfo[preset4]:$playerinfo[preset5]:$playerinfo[preset6]:";
// Trade routes
$i=0;
$num_traderoutes = 0;

/********* Port query ************************************ begin *********/
  $query = $db->Execute("SELECT * FROM $dbtables[traderoutes] WHERE source_type='P' AND source_id=$playerinfo[sector] AND owner=$playerinfo[player_id] ORDER BY dest_id ASC");
  while(!$query->EOF)
  {
    $traderoutes[$i]=$query->fields;
    $i++;
    $num_traderoutes++;
    $query->MoveNext();
  }
/********* Port query ************************************ end **********/

/********* Sector Defense Trade route query *************** begin ********/
/********* this is still under developement ***/
  $query = $db->Execute("SELECT * FROM $dbtables[traderoutes] WHERE source_type='D' AND source_id=$playerinfo[sector] AND owner=$playerinfo[player_id] ORDER BY dest_id ASC");
  while(!$query->EOF)
  {
    $traderoutes[$i]=$query->fields;
    $i++;
    $num_traderoutes++;
    $query->MoveNext();
  }
/********* Defense querry ********************************* end **********/
/********* Personal planet traderoute type query ********** begin ********/
  $query = $db->Execute("SELECT * FROM $dbtables[planets], $dbtables[traderoutes] WHERE source_type='L' AND source_id=$dbtables[planets].planet_id AND $dbtables[planets].sector_id=$playerinfo[sector] AND $dbtables[traderoutes].owner=$playerinfo[player_id]");
  while(!$query->EOF)
  {
    $traderoutes[$i]=$query->fields;
    $i++;
    $num_traderoutes++;
    $query->MoveNext();
  }
/********* Personal planet traderoute type query ********* end **********/
/********* Corporate planet traderoute type query ******** begin ********/
  $query = $db->Execute("SELECT * FROM $dbtables[planets], $dbtables[traderoutes] WHERE source_type='C' AND source_id=$dbtables[planets].planet_id AND $dbtables[planets].sector_id=$playerinfo[sector] AND $dbtables[traderoutes].owner=$playerinfo[player_id]");
  while(!$query->EOF)
  {
    $traderoutes[$i]=$query->fields;
    $i++;
    $num_traderoutes++;
    $query->MoveNext();
  }
/********* Corperate planet traderoute type query ******** end **********/
/********* Selling planet traderoute type query ******** begin ********/
  $query = $db->Execute("SELECT * FROM $dbtables[planets], $dbtables[traderoutes] WHERE source_type='S' AND source_id=$dbtables[planets].planet_id AND $dbtables[planets].sector_id=$playerinfo[sector] AND $dbtables[traderoutes].owner=$playerinfo[player_id]");
  while(!$query->EOF)
  {
    $traderoutes[$i]=$query->fields;
    $i++;
    $num_traderoutes++;
    $query->MoveNext();
  }
/********* Selling planet traderoute type query ******** end **********/

  if($num_traderoutes == 0)
    echo "&traderoute=N";
  else
  {
  	echo "&traderoute=Y";
    $i=0;
    while($i<$num_traderoutes)
    {
      echo "&traderoute$i=";
      if($traderoutes[$i][source_type] == 'P')
        echo "P:".$traderoutes[$i][source_id];
      else
      {
        $query = $db->Execute("SELECT name FROM $dbtables[planets] WHERE planet_id=" . $traderoutes[$i][source_id]);
        if(!$query || $query->RecordCount() == 0)
          echo "L:Unknown";
        else
        {
          $planet = $query->fields;
          if($planet[name] == "")
            echo "L:Unnamed";
          else {
			echo "L:$planet[name]";
		  }
        }
      }

      if($traderoutes[$i][circuit] == '1')
        echo ":One:";
      else
        echo ":Two:";

      if($traderoutes[$i][dest_type] == 'P')
        echo "P:".$traderoutes[$i][dest_id];
      else
      {
        $query = $db->Execute("SELECT name FROM $dbtables[planets] WHERE planet_id=" . $traderoutes[$i][dest_id]);
        if(!$query || $query->RecordCount() == 0)
          echo ":Unknown:";
        else
        {
          $planet = $query->fields;
          if($planet[name] == "")
            echo "L:Unnamed";
          else
			echo "L:$planet[name]";
        }
      }
     $i++;
    }
  }

//-------------------------------------------------------------------------------------------------


echo "&playerinfo=" . $shipinfo[hull] . ":" .  $shipinfo[engines] . ":"  .  $shipinfo[power] . ":" .  $shipinfo[computer] . ":" . $shipinfo[sensors] . ":" .  $shipinfo[beams] . ":" . $shipinfo[torp_launchers] . ":" .  $shipinfo[torps] . ":" . $shipinfo[shields] . ":" .  $shipinfo[armour] . ":" . $shipinfo[armour_pts] . ":" .  $shipinfo[cloak] . ":" . $playerinfo[credits] . ":" .  $playerinfo[sector] . ":" . $shipinfo[ship_ore] . ":" .  $shipinfo[ship_organics] . ":" . $shipinfo[ship_goods] . ":" .  $shipinfo[ship_energy] . ":" . $shipinfo[ship_colonists] . ":" .  $shipinfo[ship_fighters] . ":" . $playerinfo[turns] . ":" .  $playerinfo[on_planet] . ":" . $shipinfo[dev_warpedit] . ":" .  $shipinfo[dev_genesis] . ":" . $shipinfo[dev_beacon] . ":" .  $shipinfo[dev_emerwarp] . ":" . $shipinfo[dev_escapepod] . ":" .  $shipinfo[dev_fuelscoop] . ":" . $shipinfo[dev_minedeflector] . ":";
echo "\n";
?>
