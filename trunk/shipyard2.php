<?
include("config.php");
updatecookie();

include("languages/$lang");
$title="Buying a new ship";
include("header.php");

connectdb();

if(checklogin())
{
  die();
}

bigtitle();

$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;

// Check that player is at a special port
$res = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]' AND port_type = 'special'");
$techLevel = 0;
if ($res->EOF) {
	echo "You are in sector $playerinfo[sector]. There is no shipyard in this sector.<br><br>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
} else {
	// Get the tech level of the port
	$row = $res->fields;
	$techLevel = $row[tech_level];
}

$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo = $res->fields;
if(isset($stype))
{
	$stype=mysql_escape_string($stype);
}
else
{
  shipyard_die("Wrong ship class specified");
}
// Get the ships that are available at this port
$res = $db->Execute("SELECT * FROM $dbtables[ship_types] WHERE buyable = 'Y' AND tech_level<=$techLevel AND type_id='$stype' LIMIT 1");
if ($res->EOF) {
	shipyard_die("Wrong ship class specified");
} else {
  $sship = $res->fields;
}

if (!strpos($playerinfo[subscribed],"payment") && $stype != 1 && $stype !=3) {
	include("subscribe.php");
	die();
}

if(!isset($confirm)) //display info only
{
  $shipvalue=value_ship($shipinfo[ship_id]);
  $shipvalue *= 0.8;
  $totalcost = $sship[cost_credits] - $shipvalue;

  echo "
    <font size=4 color=white><b>You are buying:</b></font><p>
    <table border=0 cellpadding=5>
    <tr><td align=center><font color=white size=4><b>$sship[name]</b><br><img src=images/$sship[image]></font></td>
    <td><font size=2><b>$sship[description]</b></font>
    </table>
    <table border=0>
    <tr><td>
    <font size=4>New Ship Price:&nbsp;&nbsp;&nbsp;</font></td>
    <td align=right><font size=4 color=#FF0000><b>" . NUMBER($sship[cost_credits]) . "</b></font></td></tr>
    <tr><td><font size=4>Trade In Value:&nbsp;&nbsp;&nbsp;</font></td>
    <td align=right><font size=4 color=#00FF00><b>" . NUMBER($shipvalue) . "</b></font></td></tr>
    <tr><td>
    <tr><td><td><hr></td></tr>
    <tr><td>
    <font size=4>Final Cost:&nbsp;&nbsp;&nbsp;</font></td>
    <td align=right><font size=4 color=#FF0000><b>" . NUMBER($totalcost) . "</b></font></td></tr></table>
    <p>
  ";

  if($totalcost > $playerinfo[credits])
  {
    echo "<br><font size=3 color=white><b>&nbsp;You do not have enough credits to buy this ship.</b></font><p><br>";
  }
  else
  {
  	if ($sship[cost_credits] <= $playerinfo[credits]) {
		    echo "<form action=shipyard2.php method=POST>" .
         "<input type=hidden name=stype value=$stype>" .
         "<input type=hidden name=confirm value=yes>" .
		 "<input type=hidden name=trade value=no>" .
		 "<font size=3><b>Name your new ship:&nbsp;&nbsp;&nbsp;</b></font><input type=text name=shipname size=20 maxlength=20 value=''><p>" .
         "<input type=submit value='Buy Outright'>".
         "</form><p>";
	}
    echo "<form action=shipyard2.php method=POST>" .
         "<input type=hidden name=stype value=$stype>" .
         "<input type=hidden name=confirm value=yes>" .
		 "<input type=hidden name=trade value=yes>" .
		 "<font size=3><b>Name your new ship:&nbsp;&nbsp;&nbsp;</b></font><input type=text name=shipname size=20 maxlength=20 value='$shipinfo[ship_name]'><p>" .
         "<input type=submit value='Trade and Buy'>".
         "</form><p>";
  }
}
else //ok, now we buy the ship for true
{
	if ($trade == "yes") {
		// This is a trade-in
  		$shipvalue=value_ship($shipinfo[ship_id]);
  		$shipvalue *= 0.8;
  		$totalcost = $sship[cost_credits] - $shipvalue;
	} else {
		$totalcost = $sship[cost_credits];
	}
  //Let's do the regular sanity checks first

  if($playerinfo[turns] < 1)
    shipyard_die("You need at least one turn to perform this action");

  if(!isset($sship))
    shipyard_die("Internal error. Cannot find ship class.");
/*
  if($sship[type_id] == $shipinfo[type])
    shipyard_die("You already own this model of ship.");
*/
  if($playerinfo[credits] < $totalcost)
    shipyard_die("You do not have enough credits to complete this transaction.");

  $shipname = substr($shipname, 0, 20);
  if($shipname == "")
    shipyard_die("You must specify a name for your new ship.");

  $shipname=htmlspecialchars($shipname,ENT_QUOTES);
  $shipname=ereg_replace("[^[:digit:][:space:][:alpha:][\']]"," ",$shipname);

  $shipname = trim($shipname);
  
  $result = $db->Execute ("SELECT name FROM $dbtables[ships] WHERE name='$shipname' AND ship_id!=$shipinfo[ship_id]");

  if ($result>0)
  {
    while (!$result->EOF)
    {
      shipyard_die("This ship name is already in use. Please choose another.");
      $result->MoveNext();
    }
  }

  //Okay, we're done checking. Now time to create the new ship and assign it as current

  $res = $db->Execute("INSERT INTO $dbtables[ships] (`ship_id`, `player_id`, `type`, `ship_name`, `ship_destroyed`, `hull`, `engines`, `power`, `computer`, `sensors`, `beams`, `torp_launchers`, `torps`, `shields`, `armour`, `armour_pts`, `cloak`, `sector`, `ship_ore`, `ship_organics`, `ship_goods`, `ship_energy`, `ship_colonists`, `ship_fighters`, `on_planet`, `dev_warpedit`, `dev_genesis`, `dev_beacon`, `dev_emerwarp`, `dev_escapepod`, `dev_fuelscoop`, `dev_minedeflector`, `planet_id`, `cleared_defences`, `dev_lssd`,`dev_sectorwmd`,`fur_tech`)  
    VALUES(" .
               "''," .             //ship_id
               "$playerinfo[player_id]," .     //player_id
               "'$stype'," .            //type
               "'$shipname'," .   //name
               "'N'," .            //destroyed
               "$sship[minhull]," .              //hull
               "$sship[minengines]," .              //engines
               "$sship[minpower]," .              //power
               "$sship[mincomputer]," .              //computer
               "$sship[minsensors]," .              //sensors
               "$sship[minbeams]," .              //beams
               "$sship[mintorp_launchers]," .              //torp_launchers
               "0," .              //torps
               "$sship[minshields]," .              //shields
               "$sship[minarmour]," .              //armour
               "$start_armour," .  //armour_pts
               "$sship[mincloak]," .              //cloak
               "$playerinfo[sector]," .              //sector
               "0," .              //ore
               "0," .              //organics
               "0," .              //goods
               "$start_energy," .  //energy
               "0," .              //colonists
               "$start_fighters," .//fighters
               "'N'," .            //on_planet
               "0," .              //dev_warpedit
               "0," .              //dev_genesis
               "0," .              //dev_beacon
               "0," .              //dev_emerwarp
               "'N'," .            //dev_escapepod
               "'N'," .            //dev_fuelscoop
               "0," .              //dev_minedeflector
               "0," .              //planet_id
               "''," .             //cleared_defences
               "'N'," .            //dev_lssd
			   "'N'," .				// dev_sectorwmd
			   "'N'" .				// Furangee Tech
               ")");
	if (mysql_affected_rows() < 1) {
		echo "Really sorry but there was a problem trying to buy that ship, please try again or contact the admin. An alert has already been sent.";
		$msg = "Shipyard failure!\r\n".
		"INSERT INTO $dbtables[ships] (`ship_id`, `player_id`, `type`, `ship_name`, `ship_destroyed`, `hull`, `engines`, `power`, `computer`, `sensors`, `beams`, `torp_launchers`, `torps`, `shields`, `armour`, `armour_pts`, `cloak`, `sector`, `ship_ore`, `ship_organics`, `ship_goods`, `ship_energy`, `ship_colonists`, `ship_fighters`, `on_planet`, `dev_warpedit`, `dev_genesis`, `dev_beacon`, `dev_emerwarp`, `dev_escapepod`, `dev_fuelscoop`, `dev_minedeflector`, `planet_id`, `cleared_defences`, `dev_lssd`,`dev_sectorwmd`,`fur_tech`)  
    VALUES(" .
               "''," .             //ship_id
               "$playerinfo[player_id]," .     //player_id
               "'$stype'," .            //type
               "'$shipname'," .   //name
               "'N'," .            //destroyed
               "$sship[minhull]," .              //hull
               "$sship[minengines]," .              //engines
               "$sship[minpower]," .              //power
               "$sship[mincomputer]," .              //computer
               "$sship[minsensors]," .              //sensors
               "$sship[minbeams]," .              //beams
               "$sship[mintorp_launchers]," .              //torp_launchers
               "0," .              //torps
               "$sship[minshields]," .              //shields
               "$sship[minarmour]," .              //armour
               "$start_armour," .  //armour_pts
               "$sship[mincloak]," .              //cloak
               "$playerinfo[sector]," .              //sector
               "0," .              //ore
               "0," .              //organics
               "0," .              //goods
               "$start_energy," .  //energy
               "0," .              //colonists
               "$start_fighters," .//fighters
               "'N'," .            //on_planet
               "0," .              //dev_warpedit
               "0," .              //dev_genesis
               "0," .              //dev_beacon
               "0," .              //dev_emerwarp
               "'N'," .            //dev_escapepod
               "'N'," .            //dev_fuelscoop
               "0," .              //dev_minedeflector
               "0," .              //planet_id
               "''," .             //cleared_defences
               "'N'," .            //dev_lssd
			   "'N'" .				// dev_sectorwmd
			   "'N'," .				// Furangee Tech
               ")\n\n";
  		$hdrs = "From: Shipyard Warning <nobody@berigames.com>\r\n";
  		$e_response=mail("ben.gibbs@berigames.com","Shipyard failure!",$msg,$hdrs);
		shipyard_die("Report sent");
	}
  //Insert current ship in players table and update player credits & turns
  $db->Execute("UPDATE $dbtables[players] SET currentship=LAST_INSERT_ID(),turns=turns-1, turns_used=turns_used+1, credits=credits-$totalcost WHERE player_id=$playerinfo[player_id]");

  echo "<p><font size=2 face='Verdana, Arial, Helvetica, sans-serif'>You have just bought a new ship!</font><p>";    
  //Delete old ship if a trade-in otherwise put the old ship into space dock
  if ($trade == "yes") {
  	if ($shipinfo[fur_tech]=="Y") {
		echo "<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>The shipyard mechanics rip out the Furangee Techs and replace them with Federation techs.</font></p>";
	}
  	$db->Execute("UPDATE $dbtables[ships] SET on_planet='Y', sector=$playerinfo[sector], player_id=1, fur_tech='N' WHERE ship_id=$shipinfo[ship_id] AND player_id=$playerinfo[player_id]");
  } else {
  	echo "<p><font size=2 face='Verdana, Arial, Helvetica, sans-serif'>$shipinfo[ship_name] 
	  is now in our Space Dock. </font> </p>";
	echo "<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>The current mooring 
	  fee is ".NUMBER($mooringFee*(1440/$sched_mooring))." credits per day pro rated 
	  and will be deducted automatically from your IGB account.</font></p>
	<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>Your first 5 minutes 
	  of mooring is free (".NUMBER($mooringFee*5)." has been deposited in your IGB account).</font></p>
	<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif' color='#FF0000'>WARNING:</font><font size='2' face='Verdana, Arial, Helvetica, sans-serif'> 
	  If your IGB account reaches zero, the ship will <b>become the Federation's property!</b></font></p>
	<p><font size='2' face='Verdana, Arial, Helvetica, sans-serif'><a href='spacedock.php'>Go 
	  to the space dock now</a> <br><br>
	  </font>"; 
	$db->Execute("UPDATE $dbtables[ibank_accounts] SET balance=balance+($mooringFee*60), loantime=loantime WHERE player_id=$playerinfo[player_id]");
  	$db->Execute("UPDATE $dbtables[ships] SET on_planet='Y', sector=$playerinfo[sector] WHERE ship_id=$shipinfo[ship_id] AND player_id=$playerinfo[player_id]");
  }
  gen_score($playerinfo[player_id]);
}

TEXT_GOTOMAIN();

include("footer.php");

function shipyard_die($error_msg)
{
  global $l_footer_until_update, $l_footer_players_on_1, $l_footer_players_on_2, $l_footer_one_player_on, $sched_ticks;
  echo "<p>$error_msg<p>";

  TEXT_GOTOMAIN();
  include("footer.php");
  die();
}

?>
