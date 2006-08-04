<?
include_once("config.php");
updatecookie();

include_once("languages/$lang");
$title="Ship Building";
include_once("header.php");

connectdb();

if(checklogin())
{
  die();
}

bigtitle();

function sec2hms ($sec, $padHours = false) 
  {

    // holds formatted string
    $hms = "";
    
    // there are 3600 seconds in an hour, so if we
    // divide total seconds by 3600 and throw away
    // the remainder, we've got the number of hours
    $hours = intval(intval($sec) / 3600); 

    // add to $hms, with a leading 0 if asked for
    $hms .= ($padHours) 
          ? str_pad($hours, 2, "0", STR_PAD_LEFT). " hours "
          : $hours. " hours ";
     
    // dividing the total seconds by 60 will give us
    // the number of minutes, but we're interested in 
    // minutes past the hour: to get that, we need to 
    // divide by 60 again and keep the remainder
    $minutes = intval(($sec / 60) % 60); 

    // then add to $hms (with a leading 0 if needed)
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). " mins ";

    // seconds are simple - just divide the total
    // seconds by 60 and keep the remainder
    $seconds = intval($sec % 60); 

    // add to $hms, again with a leading 0 if needed
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT). " secs ";

    // done!
    return $hms;
    
  }
$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;
$result = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo = $result->fields;
$result = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$playerinfo[sector]");
$sector_info = $result->fields;

// Check if we are on a planet or not
if ($playerinfo[on_planet] == 'Y') {
	// Is it our planet
	$result3 = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$playerinfo[planet_id] AND owner = $playerinfo[player_id] LIMIT 1");
	if($result3) {
		if ($result3->RowCount() >0) {
			$planetinfo=$result3->fields;
			echo "<h2>";
			if(empty($planetinfo[name])) {
				echo "Unnamed Planet in Sector $playerinfo[sector]";
			} else {
				echo "Planet $planetinfo[name] in Sector $playerinfo[sector]";
			}
			echo "</h2>";
			// Mention tech level
			echo "Planet intelligence level is currently ".NUMBER($planetinfo[tech_level])." units.<p>";
			// Parse commands
			if(isset($stype) && $stype > 0 && $stype !="")
			{
				
				$res2 = $db->Execute("SELECT * FROM $dbtables[ship_types] WHERE type_id='$stype' AND buyable = 'N' AND tech_level<=$planetinfo[tech_level] LIMIT 1");
				if ($res2->EOF) {
					echo "Unknown ship class.<br>";
				} else {
					$sship = $res2->fields;
					$hull_bars = MakeBars($sship[minhull], $sship[maxhull]);
					$engines_bars = MakeBars($sship[minengines], $sship[maxengines]);
					$power_bars = MakeBars($sship[minpower], $sship[maxpower]);
					$computer_bars = MakeBars($sship[mincomputer], $sship[maxcomputer]);
					$sensors_bars = MakeBars($sship[minsensors], $sship[maxsensors]);
					$armour_bars = MakeBars($sship[minarmour], $sship[maxarmour]);
					$shields_bars = MakeBars($sship[minshields], $sship[maxshields]);
					$beams_bars = MakeBars($sship[minbeams], $sship[maxbeams]);
					$torp_launchers_bars = MakeBars($sship[mintorp_launchers], $sship[maxtorp_launchers]);
					$cloak_bars = MakeBars($sship[mincloak], $sship[maxcloak]);
				?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
				<td width="100%"><img border=1 ALIGN="right" src="<?echo "images/big$sship[image]"; ?>" width="120" height="90">
				<font size=4 color=white>
				<?
				
				 echo "<b>$sship[name]</b></font><br><font size=2 color=silver><b>$sship[description]";
				 
				?>
				<br>
				</font></td>
				</tr></table>
				<?		 
					echo "<table border=0 cellpadding=2>" .
						 "<tr><td valign=top><font size=4 color=white><b>Ship Components Levels</b></font><br>&nbsp;</td></tr>" .
						   "<tr><td><font size=2><b>Hull</b> ($sship[minhull]/$sship[maxhull])</td>" .
						   "<td valign=bottom>$hull_bars</td></tr>" .
						   "<tr><td><font size=2><b>Engines</b> ($sship[minengines]/$sship[maxengines])</td>" .
						   "<td valign=bottom>$engines_bars</td></tr>" .
						   "<tr><td><font size=2><b>Power</b> ($sship[minpower]/$sship[maxpower])</td>" .
						   "<td valign=bottom>$power_bars</td></tr>" .
						   "<tr><td><font size=2><b>Computer</b> ($sship[mincomputer]/$sship[maxcomputer])</td>" .
						   "<td valign=bottom>$computer_bars</td></tr>" .
						   "<tr><td><font size=2><b>Sensors</b> ($sship[minsensors]/$sship[maxsensors])</td>" .
						   "<td valign=bottom>$sensors_bars</td></tr>" .
						   "<tr><td><font size=2><b>Armour</b> ($sship[minarmour]/$sship[maxarmour])</td>" .
						   "<td valign=bottom>$armour_bars</td></tr>" .
						   "<tr><td><font size=2><b>Shields</b> ($sship[minshields]/$sship[maxshields])</td>" .
						   "<td valign=bottom>$shields_bars</td></tr>" .
						   "<tr><td><font size=2><b>Beams</b> ($sship[minbeams]/$sship[maxbeams])</td>" .
						   "<td valign=bottom>$beams_bars</td></tr>" .
						   "<tr><td><font size=2><b>Torpedo Launchers</b> ($sship[mintorp_launchers]/$sship[maxtorp_launchers])</td>" .
						   "<td valign=bottom>$torp_launchers_bars</td></tr>" .
						   "<tr><td><font size=2><b>Cloak</b> ($sship[mincloak]/$sship[maxcloak])</td>" .
						 "<td valign=bottom>$cloak_bars</td></tr>" .
						 "<tr><td><font color=white size=4><b><br>Cost to build: </b></td>" .
						 "<tr><td><font color=white size=4>Credits<b> " . NUMBER($sship[cost_credits]) . " C</b></td></tr>" .
						 "<tr><td><font color=white size=4>Ore<b> " . NUMBER($sship[cost_ore]) . "</b></td></tr>" .
						 "<tr><td><font color=white size=4>Goods<b> " . NUMBER($sship[cost_goods]) . "</b></td></tr>" .
						 "<tr><td><font color=white size=4>Organics<b> " . NUMBER($sship[cost_organics]) . "</b></td></tr>" .
						 "<tr><td><font color=white size=4>Energy<b> " . NUMBER($sship[cost_energy]) . "</b></td></tr>" .
						 "<tr><td>It will take ".sec2hms(($sched_ticks*$sship[turnstobuild]*60))." to build this ship.</td></tr>".
						 "</table><p>";
					  echo "<form action=buildship.php method=POST>" .
						   "<input type=hidden name=id value=$stype>" .
						   "<input type=hidden name=cmd value=build>" .
						   "<input type=submit value=Build>" .
						   "</form><br>";
					echo "<font size=2 color=silver><b><a href=spacedock.php?kk=".date("U").">Return to spacedock</a></b></font><p>";
			  }
			} else if ($cmd == "build" || $cmd=="confirm") {
				// Build a specific ship
				$res = $db->Execute("SELECT * FROM $dbtables[ship_types] WHERE buyable = 'N' AND tech_level<=$planetinfo[tech_level] AND type_id='$id'");
				if ($res->RowCount() >0) {
					$ship = $res->fields;
					// Check to see if this planet can build this ship
					// Display the ships that can be built
					echo "<h2>Ship Type: $ship[name]</h2>";
					echo "<table border=1><tr><td>&nbsp;</td><td>Required</td><td>On Planet</td></tr>";
					echo "<tr><td>Ore: </td><td>".NUMBER($ship[cost_ore])."</td><td>".NUMBER($planetinfo[ore])."</td></tr>";
					echo "<tr><td>Goods: </td><td>".NUMBER($ship[cost_goods])."</td><td>".NUMBER($planetinfo[goods])."</td></tr>";
					echo "<tr><td>Energy: </td><td>".NUMBER($ship[cost_energy])."</td><td>".NUMBER($planetinfo[energy])."</td></tr>";
					echo "<tr><td>Organics: </td><td>".NUMBER($ship[cost_organics])."</td><td>".NUMBER($planetinfo[organics])."</td></tr>";
					echo "<tr><td>Credits: </td><td>".NUMBER($ship[cost_credits])."</td><td>".NUMBER($planetinfo[credits])."</td></tr>";
					echo "</table>";
					// First check
					$build = true;
					if ($planetinfo[ore] < $ship[cost_ore]) {
						echo "You do not have enough ore on the planet!<br>";
						$build = false;
					}
					if ($planetinfo[goods] < $ship[cost_goods]) {
						echo "You do not have enough goods on the planet!<br>";
						$build = false;
					}
					if ($planetinfo[energy] < $ship[cost_energy]) {
						echo "You do not have enough energy on the planet!<br>";
						$build = false;
					}
					if ($planetinfo[organics] < $ship[cost_organics]) {
						echo "You do not have enough organics on the planet!<br>";
						$build = false;
					}
					if ($planetinfo[credits] < $ship[cost_credits]) {
						echo "You do not have enough credits on the planet!<br>";
						$build = false;
					}
					if ($cmd == "build" && $build) {			
						$hms = sec2hms(($sched_ticks*$ship[turnstobuild]*60));
					  echo "It will take ".$hms." to build this ship.<br>";
						echo "<form action=buildship.php method=POST>" .
						   "<input type=hidden name=id value=$ship[type_id]>" .
						   "<input type=hidden name=cmd value=confirm>" .
						   "<input type=submit value=Confirm>" .
						   "</form><br>";
					} else if ($cmd=="confirm" && $build) {
						// Okay let's build the ship
						// Take the goods off the planet
						$res2 = $db->Execute("UPDATE $dbtables[planets] SET ore=ore-$ship[cost_ore], goods=goods-$ship[cost_goods], organics=organics-$ship[cost_organics],energy=energy-$ship[cost_energy],credits=credits-$ship[cost_credits] WHERE planet_id=$playerinfo[planet_id] LIMIT 1");
						if ($res2) {
							// Now put the ship on the planet
							// Work is measured in headcount so we use negative colonists to indicate that the ship is still being built
							// Make sure that turns to build is always at least 1 and negative
							if ($ship[turnstobuild] < 1) {
								$ship[turnstobuild] = 1;
							}
							$res3 = $db->Execute("INSERT INTO $dbtables[ships] (`ship_id`, `player_id`, `type`, `ship_name`, `ship_destroyed`, `hull`, `engines`, `power`, `computer`, `sensors`, `beams`, `torp_launchers`, `torps`, `shields`, `armour`, `armour_pts`, `cloak`, `sector`, `ship_ore`, `ship_organics`, `ship_goods`, `ship_energy`, `ship_colonists`, `ship_fighters`, `on_planet`, `dev_warpedit`, `dev_genesis`, `dev_beacon`, `dev_emerwarp`, `dev_escapepod`, `dev_fuelscoop`, `dev_minedeflector`, `planet_id`, `cleared_defences`, `dev_lssd`,`dev_sectorwmd`,`fur_tech`)  
		VALUES(" .
							   "''," .             //ship_id
							   "$playerinfo[player_id]," .     //player_id
							   "'$ship[type_id]'," .            //type
							   "'$ship[name]'," .   //name
							   "'N'," .            //destroyed
							   "$ship[minhull]," .              //hull
							   "$ship[minengines]," .              //engines
							   "$ship[minpower]," .              //power
							   "$ship[mincomputer]," .              //computer
							   "$ship[minsensors]," .              //sensors
							   "$ship[minbeams]," .              //beams
							   "$ship[mintorp_launchers]," .              //torp_launchers
							   "0," .              					//torps
							   "$ship[minshields]," .              //shields
							   "$ship[minarmour]," .              //armour
							   "0," .  							//armour_pts
							   "$ship[mincloak]," .              //cloak
							   "$planetinfo[sector_id]," .              //sector
							   "0," .              //ore
							   "0," .              //organics
							   "0," .              //goods
							   "0," .  				//energy
							   "'-$ship[turnstobuild]'," .              //colonists - negative colonists indicate ship is being built
							   "0," .				//fighters
							   "'Y'," .            //on_planet
							   "0," .              //dev_warpedit
							   "0," .              //dev_genesis
							   "0," .              //dev_beacon
							   "0," .              //dev_emerwarp
							   "'N'," .            //dev_escapepod
							   "'N'," .            //dev_fuelscoop
							   "0," .              //dev_minedeflector
							   "'$planetinfo[planet_id]'," .              //planet_id
							   "''," .             //cleared_defences
							   "'N'," .            //dev_lssd
							   "'N'," .				// dev_sectorwmd
							   "'N'" .				// Furangee Tech
							   ")");
							echo "Ship building has started. ";
							$hms = sec2hms(($sched_ticks*$ship[turnstobuild]*60));
							echo "It will take approximately ".$hms." to build this ship.<br>";
						}
					} else {
						echo "<br>Click <a href=buildship.php?kk=".date("U").">here</a> to return to the Ship Building Menu<br><br>";
					}
				} else {
					echo "You cannot build that ship on this planet yet.<br>";
					echo "Click <a href=buildship.php?kk=".date("U").">here</a> to return to the Ship Building Menu<br><br>";
				}
			} else {
				// No command or unrecognized command
				// Get the ships that are available to build
				//echo "SELECT * FROM $dbtables[ship_types] WHERE buyable = 'N' AND tech_level<=$planetinfo[tech_level] ORDER BY tech_level ASC<br>";
				$res = $db->Execute("SELECT * FROM $dbtables[ship_types] WHERE buyable = 'N' AND tech_level<='$planetinfo[tech_level]' ORDER BY tech_level ASC");
				$numRows=$res->RowCount();
				if ($numRows > 0) {
					while(!$res->EOF)
					{
					  $ship = $res->fields;
					  // Display the ships that can be built
					  echo "<h2>Ship Type: $ship[name]</h2><a href=buildship.php?stype=$ship[type_id]&kk=".date("U").">";
					  echo "<img border=0 src=images/$ship[image]></a><br>";
					  /*
					  echo "<i>$ship[description]</i><br><b>Resources required to build this ship:</b><br>";
					  echo "Ore: ".NUMBER($ship[cost_ore])."<br>";
					  echo "Goods: ".NUMBER($ship[cost_goods])."<br>";
					  echo "Energy: ".NUMBER($ship[cost_energy])."<br>";
					  echo "Organics: ".NUMBER($ship[cost_organics])."<br>";
					  echo "Credits: ".NUMBER($ship[cost_credits])."<br>";
					  $hms = sec2hms(($sched_ticks*$ship[turnstobuild]*60));
					  echo "It will take ".$hms." to build this ship.<br>";
					  */
					  echo "<table><tr><td><form action=buildship.php method=POST>" .
						   "<input type=hidden name=stype value=$ship[type_id]>" .
						   "<input type=submit value=Info></form></td><td>" .
						   "<form action=buildship.php method=POST>" .
						   "<input type=hidden name=id value=$ship[type_id]>" .
						   "<input type=hidden name=cmd value=build>" .
						   "<input type=submit value=Build>" .
						   "</form></td></tr></table>";
	
					  $res->MoveNext();
					}
				} else {
					echo "You cannot build any ships yet, your planet intelligence level is too low.<br>";
					echo "Hint: do not allocate 100% of your colonists to manufacturing so they can think instead.<br>";
				}
			}
		} else {
			echo "Ship building is only possible on planets you own.<br>";
		}
	}
} else {
	echo "You have to be on a planet to access this function!<br>";
}
echo "<br><br>";
TEXT_GOTOMAIN();

include("footer.php");

?>

