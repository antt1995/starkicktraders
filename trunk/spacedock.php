<?
include_once("config.php");
updatecookie();

include_once("languages/$lang");
$title="Space Dock";
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
$result = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo = $result->fields;
$result = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$playerinfo[sector]");
$sector_info = $result->fields;

// Calculate my average size
$shipavg = $shipinfo[hull] + $shipinfo[engines] + $shipinfo[power] + $shipinfo[computer] + $shipinfo[sensors] + $shipinfo[armour] + $shipinfo[shields] + $shipinfo[beams] + $shipinfo[torp_launchers] + $shipinfo[cloak];
$shipavg /= 10;

if (empty($cmd)) {
if ($playerinfo[on_planet] == 'Y') {
	// Planet based space dock
	$result3 = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$playerinfo[planet_id]");
	if($result3) {
	  	$planetinfo=$result3->fields;
		echo "<h2>";
    	if(empty($planetinfo[name])) {
      		echo "Unnamed Planet in Sector $playerinfo[sector]";
    	} else {
			echo "Planet $planetinfo[name] in Sector $playerinfo[sector]";
    	}
		echo "</h2>";
	}
	$res = $db->Execute("SELECT $dbtables[ships].*,character_name,name,image,turnstobuild,$dbtables[players].player_id AS owner_id FROM $dbtables[ships],$dbtables[ship_types],$dbtables[players] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].planet_id=$playerinfo[planet_id] AND type=type_id AND $dbtables[ships].player_id=$dbtables[players].player_id AND ship_id!=currentship");
	// Show the ship building menu
	echo "<a href=buildship.php?kk=".date("U").">See what ships can be built here</a><br>";
} else {
	// Check that player is at a special port
	$res = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]' AND port_type = 'special'");
	if ($res->EOF) {
		echo "You are in sector $playerinfo[sector]. There is no Special Port Space Dock in that sector.<br><br>";
		TEXT_GOTOMAIN();
		include("footer.php");
		die();
	}
	// Special port space dock
	echo "<h2>Sector $playerinfo[sector] Special Port</h2>";
	$res = $db->Execute("SELECT $dbtables[ships].*,character_name,name,image,turnstobuild,$dbtables[players].player_id AS owner_id FROM $dbtables[ships],$dbtables[ship_types],$dbtables[players] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND type=type_id AND $dbtables[ships].player_id=$dbtables[players].player_id");
	echo "Ships moored in this Special Port Space Dock are safe from thieves and bandits.<br>Our mooring fee is ".NUMBER($mooringFee*(1440/$sched_mooring))." credits per day pro-rated and will be deducted automatically from your IGB account.<br>If your IGB account is zero at the time of deduction the ship will become the Federation's property.<br>"; 
	if ($playerinfo[sector]==0) {
		echo "<font color=red>Sector 0 Spacedock is primarily for use by new traders only (Score < ".NUMBER($fed_dock_max).")</font><br>";
	}
}
if ($res->EOF) {
	echo "There are no ships moored in the space dock right now.<br><br>";
} else {
	?>

  <table width=100% border=1 cellpadding=5>
    <tr bgcolor=<? echo $color_line2 ?>><td align=center colspan=4>
    <font size=2 color=white><b>The following ships are in this space dock:</b></font>
    </td>
    </tr>
	<tr><td>Ship Name</td><td>Class</td><td>Owner</td><td>Command</td>
    <?
    $first=1;
    while(!$res->EOF)
    {
	  $curship = $res->fields;
      echo "<tr><td><a href=spacedock.php?cmd=info&sid=$curship[ship_id]>" .
		   "<font size=2 color=white>$curship[ship_name]</a></font></td><td><font size=2 color=white><b>$curship[name]</b></font></td>";
      echo "<td>$curship[character_name]</td><td>";
	  // Now we work out what commands are possible
	  // Show how far the construction is to go:
	  $shipUnfinished = false;
	  if ($curship[ship_colonists] < 0) {
	  	$percentFinished = 100+($curship[ship_colonists]/$curship[turnstobuild]*100);
		echo "This ship is ".NUMBER($percentFinished,1)."% finished.<br>";
		$shipUnfinished = true;
	  }
	  echo "<a href=spacedock.php?cmd=info&sid=$curship[ship_id]>View ship</a><br>";
	  // Check that the space dock is available
	  if ($playerinfo[score] < $fed_dock_max || $playerinfo[sector] !=0 || $playerinfo[player_id] == 1) {
		  // Current commands are: tx - switch ships, info - another way to look at the ship
		  if ($playerinfo[player_id] == $curship[owner_id] && !$shipUnfinished) {
			// Owner
			if ($playerinfo[player_id] == 1) {
				echo "<a href=spacedock.php?cmd=ds&sid=$curship[ship_id]>Destroy ship</a><br>";
			}
			echo "<a href=spacedock.php?cmd=tx&sid=$curship[ship_id]>Transfer to this ship</a><br>";
			if ($shipinfo[tow] == $curship[ship_id]) {
					echo "<a href=spacedock.php?cmd=unhitch&sid=$curship[ship_id]>Unhitch tractor beam from this ship</a><br>";
			}
		  }
	  }
	  if ($playerinfo[player_id] == $curship[owner_id]) {
	  //if ($playerinfo[player_id] == 1) {
	  	echo "<a href=spacedock.php?cmd=rename&sid=$curship[ship_id]>Rename ship</a><br>";
	  }
	  if ($playerinfo[player_id] == $curship[owner_id] && !$shipUnfinished) {
		if ($shipinfo[tow] == 0) {
			echo "<a href=spacedock.php?cmd=tow&sid=$curship[ship_id]>Tow ship</a><br>";
		}
		$res2 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$shipinfo[sector] AND port_type='special'");
		if (!$res2->EOF) {
			echo "<a href=spacedock.php?cmd=sell&sid=$curship[ship_id]>Sell ship</a><br>";
		}
	  } else if ($curship[owner_id] == 1 && !$shipUnfinished) {
			echo "<a href=spacedock.php?cmd=buy&sid=$curship[ship_id]>Buy ship</a><br>";
	  }
	  echo "</td></tr>";
	  $res->MoveNext();
	}
	?>
	</table>
	
<?
	}
} else if ($cmd == "rename") {
	// Check that the ship is owned by player
	$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$playerinfo[player_id] AND ship_id = '".mysql_escape_string($sid)."'");
	if (!$res->EOF) {
		$shipinfo = $res->fields;
		// Check to see if this is the new name command or the request
		if (empty($new_name)) {
			echo "Current name: $shipinfo[ship_name]<br>";
			echo "<form action=spacedock.php?kk=".date("U")." method=post>";					
			echo "<input type=hidden name=sid value='$sid'>";
			echo "<input type=hidden name=cmd value=rename>";
			echo "New name: <input type=text name=new_name><br>";
			echo "<input type=submit value='Submit'></form><br><br>";
		} else {
			// Make sure that new_name is safe			
			echo "The $shipinfo[ship_name] is now $new_name.<br><br>";
			$db->Execute("UPDATE $dbtables[ships] SET ship_name = '".mysql_escape_string($new_name)."' WHERE ship_id='".mysql_escape_string($sid)."' LIMIT 1");
		}
	} else {
		echo "You cannot rename that ship!<br><br>";
	}
} else if ($cmd == "tx") {
	if($playerinfo[turns] < 1) {
		echo "You need at least one turn to perform this action";
	} else {
		if ($playerinfo[score] < $fed_dock_max || $playerinfo[sector] !=0) {
			// Check that the ship is owned by
			$res = $db->Execute("SELECT ship_id, ship_name FROM $dbtables[ships],$dbtables[players] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$dbtables[players].player_id AND ship_id = $sid and ship_colonists >=0");
			if (!$res->EOF) {
				$shipinfo = $res->fields;
				$db->Execute("UPDATE $dbtables[ships] SET on_planet='Y',sector=$playerinfo[sector],tow=0 WHERE ship_id=$playerinfo[currentship]");
				$db->Execute("UPDATE $dbtables[ships] SET on_planet='N',sector=$playerinfo[sector],tow=0 WHERE ship_id=$sid");
				$db->Execute("UPDATE $dbtables[players] SET currentship=$sid,turns=turns-1,turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
				echo "You transfer into the $shipinfo[ship_name]!<br><br>";
			} else {
				echo "You cannot transfer to that ship!<br><br>";
			}
		} else {
			echo "You cannot transfer to that ship!<br><br>";
		}
	}
} else if ($cmd == "ds") {
	// Destroy ship
	if($playerinfo[player_id] == 1) {
		// Check that the ship is owned by
		$res = $db->Execute("SELECT ship_id, ship_name FROM $dbtables[ships],$dbtables[players] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$dbtables[players].player_id AND ship_id = $sid");
		if (!$res->EOF) {
			$shipinfo = $res->fields;
			//echo "DELETE FROM $dbtables[ships] WHERE ship_id=$sid LIMIT 1";			
			$db->Execute("DELETE FROM $dbtables[ships] WHERE ship_id=$sid LIMIT 1");
			echo "You destroyed the $shipinfo[ship_name]!<br><br>";
		} else {
			echo "You cannot destroy that ship!<br><br>";
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
		//echo "<table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td width=100%>";
		echo "<font size=4 color=white>";
		echo "<b>$sship[name]</b></font><br>";
		echo "<img src='images/big".$sship[image]."' width='120' height='90'><br>";
		$shipUnfinished = false;
	  	if ($sship[ship_colonists] < 0) {
	  		$percentFinished = 100+($sship[ship_colonists]/$sship[turnstobuild]*100);
			echo "<font size=2 color=red><b>This ship is ".NUMBER($percentFinished,1)."% finished.</b></font><br>";
			$shipUnfinished = true;
	  	}
		echo "<font size=2 color=silver><b>Owned by: $sship[character_name]<br><font size=2 color=silver><b>$sship[description]";		 
		echo "<br></font>";
		//echo "</td></tr></table>";		 
		echo "<table border=0 cellpadding=2>" .
			 "<tr><td valign=top><font size=4 color=white><b>Ship Components Levels</b></font><br>&nbsp;</td></tr>" .
               "<tr><td><font size=2><b>Hull</b> ($sship[hull]/$sship[maxhull])</td>" .
               "<td valign=bottom>$hull_bars</td></tr>" .
               "<tr><td><font size=2><b>Engines</b> ($sship[engines]/$sship[maxengines])</td>" .
               "<td valign=bottom>$engines_bars</td></tr>" .
               "<tr><td><font size=2><b>Power</b> ($sship[power]/$sship[maxpower])</td>" .
               "<td valign=bottom>$power_bars</td></tr>" .
               "<tr><td><font size=2><b>Computer</b> ($sship[computer]/$sship[maxcomputer])</td>" .
               "<td valign=bottom>$computer_bars</td></tr>" .
               "<tr><td><font size=2><b>Sensors</b> ($sship[sensors]/$sship[maxsensors])</td>" .
               "<td valign=bottom>$sensors_bars</td></tr>" .
               "<tr><td><font size=2><b>Armour</b> ($sship[armour]/$sship[maxarmour])</td>" .
               "<td valign=bottom>$armour_bars</td></tr>" .
               "<tr><td><font size=2><b>Shields</b> ($sship[shields]/$sship[maxshields])</td>" .
               "<td valign=bottom>$shields_bars</td></tr>" .
               "<tr><td><font size=2><b>Beams</b> ($sship[beams]/$sship[maxbeams])</td>" .
               "<td valign=bottom>$beams_bars</td></tr>" .
               "<tr><td><font size=2><b>Torpedo Launchers</b> ($sship[torp_launchers]/$sship[maxtorp_launchers])</td>" .
               "<td valign=bottom>$torp_launchers_bars</td></tr>" .
               "<tr><td><font size=2><b>Cloak</b> ($sship[cloak]/$sship[maxcloak])</td>" .
			 "<td valign=bottom>$cloak_bars</td></tr>";
		echo "<tr><td><font size=2><b>Escape Pod</b></td><td>$sship[dev_escapepod]</td></tr>";
		echo "<tr><td><font size=2><b>Fuel Scoop</b></td><td>$sship[dev_fuelscoop]</td></tr>";
		echo "<tr><td><font size=2><b>Last Seen Ship Device</b></td><td>$sship[dev_lssd]</td></tr>";
		if ($sship[dev_sectorwmd] == 'Y')
			echo "<tr><td><font size=2><b>Sector-WMD</b></td><td>$sship[dev_sectorwmd]</td></tr>";
		echo "<tr><td><font size=2><b>Fighters</b></td><td>".NUMBER($sship[ship_fighters])."</td></tr>";
		echo "<tr><td><font size=2><b>Torpedoes</b></td><td>".NUMBER($sship[torps])."</td></tr>";
		echo "<tr><td><font size=2><b>Armor Points</b></td><td>".NUMBER($sship[armour_pts])."</td></tr>";
		echo "<tr><td><font size=2><b>Genesis Torpedoes</b></td><td>".NUMBER($sship[dev_genesis])."</td></tr>";
		echo "<tr><td><font size=2><b>Beacons</b></td><td>".NUMBER($sship[dev_beacon])."</td></tr>";
		echo "<tr><td><font size=2><b>EWD's</b></td><td>".NUMBER($sship[dev_emerwarp])."</td></tr>";
		echo "<tr><td><font size=2><b>Mine Deflectors</b></td><td>".NUMBER($sship[dev_minedeflector])."</td></tr>";
		if ($sship[ship_ore]+$sship[ship_organics]+$sship[ship_goods] > 0)
			echo "<tr><td colspan=2><font size=2><b>There is some cargo on board!</b></td></tr>";
		if ($sship[ship_energy]>0) 
			echo "<tr><td colspan=2><font size=2><b>There is some energy on board!</b></td></tr>";
		echo "</table>";
		if ($sship[player_id] == 1) {
			$shipprice = floor(0.95*value_ship($sid));
			if ($sship[fur_tech]=="Y") {
				echo "<br>WARNING: <font color=red>This ship contains illegal Furangee upgrade techs!</font>You can buy it but it will be tough to sell again.";
				$shipprice *= 2;
			}
			echo "<br><h3>This ship is offered for sale at ".NUMBER($shipprice)."C</h3><br>";
			if ($playerinfo[credits]>=$shipprice) {
	  			echo "<b><a href=spacedock.php?cmd=buy&sid=$sship[ship_id]>Buy ship</a></b><br><br>";
			}
	  	}
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
			echo "<h2>Sell Ship</h2>";
			if ($sectorinfo[port_type] == "special") {
				echo "You can only sell a ship at a special port.<br><br>";
			} else {
				$shipvalue = floor(0.8*value_ship($sid));
				if ($shipvalue < 1) {
					echo "<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>This ship is not worth anything!</font></p>";
				} else {
					if (empty($sell_confirm)) {
						if ($shipinfo[fur_tech]=="Y") {
							echo "<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>This ship contains sub-standard Furangee upgrade techs. We'll buy it but here's our best offer...</font></p>";
						}						
						echo "<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>The Space Dock owners make you the following offer: ";
						echo NUMBER($shipvalue)." credits for the $shipinfo[ship_name].</font></p>";
						echo "<form action=spacedock.php?kk=".date("U")." method=post>";					
						echo "<input type=hidden name=sid value='$sid'>";
						echo "<input type=hidden name=cmd value=sell>";
						echo "<input type=hidden name=sell_confirm value=yes>";
						echo "<input type=submit value='Sell It'></form><br><br>";
					} else {
						$db->Execute("UPDATE $dbtables[ships] SET player_id=1, on_planet='Y', tow=0, fur_tech='N' WHERE ship_id=$sid");
						$db->Execute("UPDATE $dbtables[ships] SET tow=0 WHERE tow=$sid");
						$db->Execute("UPDATE $dbtables[players] SET credits=credits+$shipvalue, turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
						if ($shipinfo[fur_tech]=="Y") {
							echo "<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>The spacedock mechanics rip out the Furangee Techs and replace them with Federation techs.</font></p>";
						}
						echo "<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>Thank you for your business!<br><br>Ship sold for ".NUMBER($shipvalue)." credits. </font></p>";
					}
				}
			}
		}
	}
} else if ($cmd == "buy") {
	if($playerinfo[turns] < 1) {
		echo "You need at least one turn to perform this action";
	} else {	
		// Check that the ship is owned by Federation
		$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=1 AND ship_id = $sid");
		if (!$res->EOF) {
			$shipinfo = $res->fields;	
			// Check that we are at a special port
			$res = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$shipinfo[sector] AND port_type='special'");
			echo "<h2>Buy Ship</h2>";
			if ($res->EOF) {
				echo "You can only buy a ship at a special port.<br><br>";
			} else {
				$shipprice = floor(0.95*value_ship($sid));
				if ($shipprice < 0) {
					$shipprice = 100;
				}
				if ($sship[fur_tech]=="Y") {
						$shipprice *= 2;
				}						
				if (empty($sell_confirm)) {
					echo "The Space Dock owners will sell you the $shipinfo[ship_name] for ".NUMBER($shipprice)." credits.<br><br>";
					if ($playerinfo[credits] < $shipprice) {
						echo "Please come back when you have enough cash to buy it!<br><br>";
					} else {
						if ($sship[fur_tech]=="Y") {
							echo "Watch out though because this ship contains illegal Furangee tech upgrades and we won't be keen to buy it back from you!<br>";
						}						
						echo "<form action=spacedock.php?kk=".date("U")." method=post>";
						echo "<input type=hidden name=sid value='$sid'>";
						echo "<input type=hidden name=cmd value=buy>";
						echo "<input type=hidden name=sell_confirm value=yes>";
						echo "<input type=submit value='Buy It Now'></form><br><br>";
					}
				} else {
					if ($playerinfo[credits] < $shipprice) {
						echo "Please come back when you have enough cash to buy it!<br><br>";
					} else if (!strpos($playerinfo[subscribed],"payment") && $shipinfo[type] != 1 && $shipinfo[type] !=3) {
						include("subscribe.php");
						die();
					} else {			
						echo "Thank you for your business!<br><br>Ship sold for ".NUMBER($shipprice)." credits.<br>";
						echo " 
							<p><font size=2 face='Verdana, Arial, Helvetica, sans-serif'>$shipinfo[ship_name] 
							  is now in our Space Dock. </font> </p>
							<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>The current mooring 
							  fee is ".NUMBER($mooringFee*(1440/$sched_mooring))." credits per day pro rated 
							  and will be deducted automatically from your IGB account.</font></p>
							<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>Your first 5 minutes 
							  of mooring is free (".NUMBER($mooringFee*5)." has been deposited in your IGB account).</font></p>
							<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif' color='#FF0000'>WARNING:</font><font size='2' face='Verdana, Arial, Helvetica, sans-serif'> 
							  If your IGB account reaches zero, the ship will <b>become the Federation's property!</b></font></p>
							<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'><a href='spacedock.php'>Go 
							  to the space dock now</a> <br><br>
							  </font>"; 
						$db->Execute("UPDATE $dbtables[ibank_accounts] SET balance=balance+($mooringFee*5), loantime=loantime WHERE player_id=$playerinfo[player_id]");
						$db->Execute("UPDATE $dbtables[ships] SET player_id=$playerinfo[player_id], on_planet='Y', tow=0 WHERE ship_id=$sid");
						$db->Execute("UPDATE $dbtables[ships] SET tow=0 WHERE tow=$sid");
						$db->Execute("UPDATE $dbtables[players] SET credits=credits-$shipprice, turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");						
					}
				}
			}
		}
	}
} else if ($cmd == "tow") {
	// Check that the ship is owned by
	$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$playerinfo[player_id] AND ship_id = $sid AND ship_colonists >=0");
	if (!$res->EOF) {
		$shipinfo = $res->fields;
		echo "You establish a tractor beam connection between your ship and the $shipinfo[ship_name].<br><br>";
		$db->Execute("UPDATE $dbtables[ships] SET tow = $sid WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
	} else {
		echo "You cannot tow that ship!<br><br>";
	}
} else if ($cmd == "unhitch") {
	// Check if we are towing anything
	if ($shipinfo[tow]>0) {
		// Check that the ship is owned by
		$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE $dbtables[ships].on_planet='Y' AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].player_id=$playerinfo[player_id] AND ship_id = $sid AND ship_colonists >=0");
		if (!$res->EOF) {
			$curship = $res->fields;
			// Federation Sector Check - only newbies can use
			if ($playerinfo[sector]==0 && $playerinfo[score] > $fed_dock_max) {
				echo "This Spacedock is reserved for new traders. Please try another special port spacedock.<br>";
			} else {
				$canDock = true;
				// Check to see if we are at a special port
				if ($sectorinfo[port_type] == "special") {
					// Check that they have some money in their IGB account
					$res2 = $db->Execute("SELECT balance FROM $dbtables[ibank_accounts] WHERE player_id=$playerinfo[player_id] ");
					$row = $res2->fields;
					if ($row[balance] <1000) {
						echo "The Spacedock owners require you to have at least 1000 credits in your IGB account before they will allow you to dock.<br><br>";
						$canDock=false;
					} else if ($row[balance] < 10000) {
						echo "The Spacedock owners grudgingly let you moor the ship but with only ".NUMBER($row[balance])." credits in your IGB account you better be careful not to loose you ship for non-payment of mooring fees!<br>";
					}
				}
				if ($canDock) {
					echo "You detatch the tractor beam connection from your ship to the $shipinfo[ship_name] and it settles safely into the space dock.<br><br>";
					// Belt and braces
					$db->Execute("UPDATE $dbtables[ships] SET sector=$playerinfo[sector] WHERE ship_id=$shipinfo[tow]");
					$db->Execute("UPDATE $dbtables[ships] SET tow = 0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
				}
			}
		} else {
			echo "You cannot do that!<br><br>";
		}
	} else {
		echo "You are not towing any ship!<br><br>";
	}
}
if (isset($cmd)) {
	echo "<font size=2><b><a href=spacedock.php>Return to Space Dock</a></b></font><br><br>";
}		
TEXT_GOTOMAIN();

include("footer.php");

?>
