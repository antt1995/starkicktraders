<?
include_once("config.php");
updatecookie();

include_once("languages/$lang");
$title="Drydock";
include_once("header.php");

connectdb();

if(checklogin())
{
  die();
}

bigtitle();

function shipping_distance($start, $dest)
{
  global $playerinfo;
  global $shipinfo;
  global $level_factor;
  global $db, $dbtables;

  $retvalue[triptime] = 0;
  $query = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$start");
  $start = $query->fields;
  $query = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$dest");
  $dest = $query->fields;

  if($start[sector_id] == $dest[sector_id])
  {
    return 1;
  }

  $distance=calc_dist($start,$dest);
  if($distance<1) {
    $distance = 1;
    // TODO: The query failed. What now?
  }

  $shipspeed = mypw($level_factor, $shipinfo[engines]);
  $triptime = round($distance / $shipspeed);

  if(!$triptime && $destination != $playerinfo[sector])
    $triptime = 1;

  $triptime+=1;
  return $triptime;
}

$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;

if (empty($cmd)) {
if ($playerinfo[on_planet] == 'Y') {
	// Planet based drydock
	$res = $db->Execute("SELECT ship_id,ship_name,character_name,name,image,$dbtables[players].player_id AS owner_id FROM $dbtables[ships],$dbtables[ship_types],$dbtables[players] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].planet_id=$playerinfo[planet_id] AND type=type_id AND $dbtables[ships].player_id=$dbtables[players].player_id AND ship_id!=currentship");
} else {
	// Special port drydock
	$res = $db->Execute("SELECT ship_id,ship_name,character_name,name,image,$dbtables[players].player_id AS owner_id FROM $dbtables[ships],$dbtables[ship_types],$dbtables[players] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND type=type_id AND $dbtables[ships].player_id=$dbtables[players].player_id");
}
if ($res->EOF) {
	echo "There are no ships in the dry dock right now.<br><br>";
} else {
	?>

  <table width=100% border=1 cellpadding=5>
    <tr bgcolor=<? echo $color_line2 ?>><td align=center colspan=4>
    <font size=2 color=white><b>The following ships are in this drydock:</b></font>
    </td>
    </tr>
	<tr><td>Ship Name</td><td>Class</td><td>Owner</td><td>Command</td>
    <?
    $first=1;
    while(!$res->EOF)
    {
	  $curship = $res->fields;
      echo "<tr><td><a href=drydock.php?cmd=info&sid=$curship[ship_id]>" .
		   "<font size=2 color=white>$curship[ship_name]</a></font></td><td><font size=2 color=white><b>$curship[name]</b></font></td>";
      echo "<td>$curship[character_name]</td><td>";
	  // Now we work out what commands are possible
	  // Current commands are: tx - switch ships, mv - move a ship to a planet, info - another way to look at the ship
	  if ($playerinfo[player_id] == $curship[owner_id]) {
	  	// Owner
		echo "<a href=drydock.php?cmd=tx&sid=$curship[ship_id]>Transfer to this ship</a><br>";
		echo "<a href=drydock.php?cmd=tow&sid=$curship[ship_id]>Tow this ship out of dry dock</a><br>";
		$res2 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$shipinfo[sector] AND port_type='special'");
		if (!$res->EOF) {
			echo "<a href=drydock.php?cmd=sell&sid=$curship[ship_id]>Sell ship</a><br>";
		}
	  }
	  echo "<a href=drydock.php?cmd=info&sid=$curship[ship_id]>View ship</a></td>";
	  echo "</tr>";
	  $res->MoveNext();
	}
	?>
	</table>
	<?
	}
} else if ($cmd == "tx") {
	if($playerinfo[turns] < 1) {
		echo "You need at least one turn to perform this action";
	} else {		
		// Check that the ship is owned by
		$res = $db->Execute("SELECT ship_id, ship_name FROM $dbtables[ships],$dbtables[players] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$dbtables[players].player_id AND ship_id = $sid");
		if (!$res->EOF) {
			$shipinfo = $res->fields;
			$db->Execute("UPDATE $dbtables[ships] SET on_planet='Y',sector=$playerinfo[sector] WHERE ship_id=$playerinfo[currentship]");
			$db->Execute("UPDATE $dbtables[ships] SET on_planet='N',sector=$playerinfo[sector] WHERE ship_id=$sid");
			$db->Execute("UPDATE $dbtables[players] SET currentship=$sid,turns=turns-1,turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
			echo "You transfer into the $shipinfo[ship_name]!<br><br>";
		} else {
			echo "You cannot transfer to that ship!<br><br>";
		}
	}
} else if ($cmd == "info") {
	// Show more about this ship
	$res = $db->Execute("SELECT * FROM $dbtables[ships],$dbtables[players],$dbtables[ship_types] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND type=type_id AND $dbtables[ships].player_id=$dbtables[players].player_id AND $dbtables[ships].ship_id=$sid LIMIT 1");
	if (!$res->EOF) {
		$sship = $res->fields;
		$hull_bars = MakeBars($sship[hull], $sship[maxhull]);
		$engines_bars = MakeBars($sship[engines], $sship[maxengines]);
		$power_bars = MakeBars($sship[power], $sship[maxpower]);
		$computer_bars = MakeBars($sship[computer], $sship[maxcomputer]);
		$sensors_bars = MakeBars($sship[sensors], $sship[maxsensors]);
		$armour_bars = MakeBars($sship[armour], $sship[maxarmour]);
		$shields_bars = MakeBars($sship[shields], $sship[maxshields]);
		$beams_bars = MakeBars($sship[beams], $sship[maxbeams]);
		$torp_launchers_bars = MakeBars($sship[torp_launchers], $sship[maxtorp_launchers]);
		$cloak_bars = MakeBars($sship[cloak], $sship[maxcloak]);
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
		<td width="100%"><img border=1 ALIGN="right" src="<?echo "images/big$sship[image]"; ?>" width="120" height="90">
		<font size=4 color=white>
		<?
		
		 echo "<b>$sship[name]</b></font><br><font size=2 color=silver><b>Owned by: $sship[character_name]<br><font size=2 color=silver><b>$sship[description]";
		 
		?>
		<br>
		</font></td>
		</tr></table>
		<?		 
		echo "<table border=0 cellpadding=2>" .
			 "<tr><td valign=top><font size=4 color=white><b>Ship Components Levels</b></font><br>&nbsp;</td></tr>" .
			 "<tr><td><font size=2><b>Hull</b></td>" .
			 "<td valign=bottom>$hull_bars</td></tr>" .
			 "<tr><td><font size=2><b>Engines</b></td>" .
			 "<td valign=bottom>$engines_bars</td></tr>" .
			 "<tr><td><font size=2><b>Power</b></td>" .
			 "<td valign=bottom>$power_bars</td></tr>" .
			 "<tr><td><font size=2><b>Computer</b></td>" .
			 "<td valign=bottom>$computer_bars</td></tr>" .
			 "<tr><td><font size=2><b>Sensors</b></td>" .
			 "<td valign=bottom>$sensors_bars</td></tr>" .
			 "<tr><td><font size=2><b>Armour</b></td>" .
			 "<td valign=bottom>$armour_bars</td></tr>" .
			 "<tr><td><font size=2><b>Shields</b></td>" .
			 "<td valign=bottom>$shields_bars</td></tr>" .
			 "<tr><td><font size=2><b>Beams</b></td>" .
			 "<td valign=bottom>$beams_bars</td></tr>" .
			 "<tr><td><font size=2><b>Torpedo Launchers</b></td>" .
			 "<td valign=bottom>$torp_launchers_bars</td></tr>" .
			 "<tr><td><font size=2><b>Cloak</b></td>" .
			 "<td valign=bottom>$cloak_bars</td></tr>" .
			 "</table><p>";
	}
} else if ($cmd == "mv") {
	// Check that the ship is owned by
	$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$playerinfo[player_id] AND ship_id = $sid");
	if (!$res->EOF) {
		$shipinfo = $res->fields;
		// Display the planet option
		if (empty($planet_id1)) {
			echo "Ships are transported using their own engines. The number of turns it will take is quoted in the list.<p>Where would you like to send this ship?<p>";
			//---- Get Planet info Corp and Personal Planets(BEGIN) ----
			 $result = $db->Execute("SELECT * FROM $dbtables[planets] WHERE owner=$playerinfo[player_id] AND planet_id != $shipinfo[planet_id] ORDER BY sector_id");
			
			  $num_planets = $result->RecordCount();
			  $i=0;
			  while (!$result->EOF)
			  {
				$planets[$i] = $result->fields;
				if($planets[$i][name] == "")
				  $planets[$i][name] = $l_tdr_unnamed;
				// Find distance
				$dist[$i] = shipping_distance($shipinfo[sector],$planets[$i][sector_id]);
				$i++;
				$result->MoveNext();
			  }
			//---- Get Planet info Corp and Personal (END) ------
			  echo "<form action=drydock.php?cmd=mv&kk=".date("U")." method=post>";
			  echo "Your planet: <select name=planet_id1>";			
			  if($num_planets == 0)
				echo "<option value=none>$l_tdr_none</option>";
			  else
			  {
				$i=0;
				while($i < $num_planets)
				{
				  echo "<option value=" . $planets[$i][planet_id] . ">" . substr($planets[$i][name],0,12). " in Sector ".$planets[$i][sector_id]." (".$dist[$i]." turns)</option>";
				  $i++;
				}
			  }
			//----------------------- End Start point selection
			echo "<input type=hidden name=sid value='$sid'>";
			echo "<br><br><input type=submit value='Transport ship!'></form><br><br>";
		} else {
			// A request to ship the ship!
			// Check that this is OK
			$result = $db->Execute("SELECT * FROM $dbtables[planets] WHERE owner=$playerinfo[player_id] AND planet_id=$planet_id1");
			if ($result->EOF) {
				echo "Shipping to that planet is no longer possible! Do you still own it?<br><br>";
			} else {
				$planetinfo = $result->fields;
				// Check that there are enough turns to do this
				$dist = shipping_distance($shipinfo[sector],$planetinfo[sector_id]);
				if ($dist>$playerinfo[turns]) {
					echo "You do not have enough turns to transport the ship to $planetinfo[name] in sector $planetinfo[sector].<br>";
					echo "That requires $dist turns and you only have $playerinfo[turns]!<br><br>";
				} else {
					$db->Execute("UPDATE $dbtables[ships] SET on_planet='Y',sector=$planetinfo[sector_id], planet_id=$planetinfo[planet_id] WHERE ship_id=$sid");
					$db->Execute("UPDATE $dbtables[players] SET turns=turns-$dist, turns_used=turns_used+$dist WHERE player_id=$playerinfo[player_id]");
					echo "Your ship $shipinfo[name] was safely transported to $planetinfo[name] in sector $planetinfo[sector_id].<br>";
					echo "The trip took $dist turns.<br><br>";
				}
			}
		}	
	} else {
		echo "This is not your ship!<br><br>";
	}
} else if ($cmd == "sell") {
	if($playerinfo[turns] < 1) {
		echo "You need at least one turn to perform this action";
	} else {	
		// Check that the ship is owned by player
		$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$playerinfo[player_id] AND ship_id = $sid");
		if (!$res->EOF) {
			$shipinfo = $res->fields;	
			// Check that we are at a special port

			$res = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$shipinfo[sector] AND port_type='special'");
			echo "<h2>Sell Ship</h2>";
			if ($res->EOF) {
				/*
				$who = rand(0,3);
				$dealers = array("Galactic Gazzer","Neutron Nuff","XdfgvKl the Voweless","Quasar QwikBuy");
				echo "Dealer ".$dealers[$who];
				$shipvalue = 0.5*(value_ship($sid));
				*/
				echo "You can only sell your ship at a special port.<br><br>";
			} else {
				if (empty($sell_confirm)) {								
					echo " makes you the following offer: ";
					echo "<form action=drydock.php?kk=".date("U")." method=post>";
					echo NUMBER($shipvalue)." credits for the $shipinfo[ship_name].<br><br>";
					echo "<input type=hidden name=sid value='$sid'>";
					echo "<input type=hidden name=cmd value=sell>";
					echo "<input type=hidden name=sell_confirm value=yes>";
					echo "<input type=submit value='Sell It'></form><br><br>";
				} else {
					$db->Execute("DELETE FROM $dbtables[ships] WHERE ship_id=$sid");
					$db->Execute("UPDATE $dbtables[players] SET credits=credits+$shipvalue, turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
					echo "The shipyard thanks you for your business!<br><br>Ship sold for ".NUMBER($shipvalue).". <br><br>";
				}
			}
		}
	}
} else if ($cmd == "tow") {
	// Check that the ship is owned by
	$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$playerinfo[player_id] AND ship_id = $sid");
	if (!$res->EOF) {
		$shipinfo = $res->fields;
		echo "You establish a tractor beam connection between your ship and the $shipinfo[ship_name] and tow it out of space dock.<br><br>";
		$db->Execute("UPDATE $dbtables[ships] SET on_planet='N',sector=$playerinfo[sector], planet_id=0, tow = 0 WHERE ship_id=$sid");
		$db->Execute("UPDATE $dbtables[ships] SET tow = $sid WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
	} else {
		echo "This is not your ship!<br><br>";
	}
} else if ($cmd == "unhitch") {
	// Check that the ship is owned by
	$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$playerinfo[player_id] AND ship_id = $sid");
	if (!$res->EOF) {
		$shipinfo = $res->fields;
		echo "You detatch the tractor beam connection from your ship to the $shipinfo[ship_name] and it settles safely into the space dock.<br><br>";
		// Check if we are on a planet or a special port
		$res2 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$shipinfo[sector] AND port_type='special'");
		if ($res->EOF) {
			// Planet
			$db->Execute("UPDATE $dbtables[ships] SET on_planet='Y',sector=$playerinfo[sector], planet_id=$playerinfo[planet_id], tow = 0 WHERE ship_id=$sid");
			$db->Execute("UPDATE $dbtables[ships] SET tow = 0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		} else {
			echo "This ship can now be sold.<br><br>";
			$db->Execute("UPDATE $dbtables[ships] SET on_planet='Y',sector=$playerinfo[sector], planet_id=0, tow = 0 WHERE ship_id=$sid");
			$db->Execute("UPDATE $dbtables[ships] SET tow = 0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
		}
	} else {
		echo "This is not your ship!<br><br>";
	}
}

echo "<font size=2><b><a href=drydock.php>Return to Drydock</a></b></font><br><br>";		
TEXT_GOTOMAIN();

include("footer.php");

?>
