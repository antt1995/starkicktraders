<?
include("config.php");
updatecookie();

include("languages/$lang");

$title="Create Furangee";
include("header.php");

connectdb();
bigtitle();

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
	$furlevel = rand(3,30);
    $active="on";
	$aggression=0; // 0 = Peaceful 1 = Attack Sometimes 2=Attack Always
	$makepass="jasdkfasl123$!@";
   	$maxenergy = NUM_ENERGY($furlevel);
    $maxarmour = NUM_ARMOUR($furlevel);
    $maxfighters = NUM_FIGHTERS($furlevel);
    $maxtorps = NUM_TORPEDOES($furlevel);
    $maxcloak = min(22,$furlevel);
    $stamp=date("Y-m-d H:i:s");
	// *****************************************************************************
	// *** ADD FURANGEE RECORD TO ships TABLE ... MODIFY IF ships SCHEMA CHANGES ***
	// *****************************************************************************
	$result2 = $db->Execute("INSERT INTO $dbtables[players] (`player_id`, `character_name`, `password`, `email`,  `credits`, `sector`,  `on_planet`,`turns_used`, `last_login`, `rating`, `score`, `team`, `team_invite`, `interface`, `ip_address`, `planet_id`, `preset1`, `preset2`, `preset3`, `trade_colonists`, `trade_fighters`, `trade_torps`, `trade_energy`, `cleared_defences`, `lang`, `alerts`,  `alert2`, `subscribed`, `ore_price`, `organics_price`, `goods_price`, `energy_price`, `currentship`,`preset4`,`preset5`,`preset6`) VALUES ('', '$character', '$makepass', '$emailname','10000000', '$sector','N', '3000', '$stamp', '0', '0', '0', '0', 'N', '127.0.0.1', '0', '0', '0', '0', 'N', 'N', 'N', 'N', NULL, '$default_lang', 'N', 'N', NULL, '0', '0', '0', '0', '1',0,0,0)");
	$res = $db->Execute("SELECT player_id from $dbtables[players] WHERE email='$emailname'");
  	$player_id = $res->fields[player_id]; 
	$shiptype=20; // We have a special furangee ship now.
	if ($furlevel > 16) {
		$ewd = rand(1,2);
	} else {
		$ewd = 0;
	}
	$result3 = $db->Execute("INSERT INTO $dbtables[ships] (`ship_id`, `player_id`, `type`, `ship_name`, `ship_destroyed`, `hull`, `engines`, `power`, `computer`, `sensors`, `beams`, `torp_launchers`, `torps`, `shields`, `armour`, `armour_pts`, `cloak`, `sector`, `ship_ore`, `ship_organics`, `ship_goods`, `ship_energy`, `ship_colonists`, `ship_fighters`, `tow`, `on_planet`, `dev_warpedit`, `dev_genesis`, `dev_beacon`, `dev_emerwarp`, `dev_escapepod`, `dev_fuelscoop`, `dev_minedeflector`, `planet_id`, `cleared_defences`, `dev_lssd`, `dev_sectorwmd`) VALUES ('', $player_id, '$shiptype', '$shipname', 'N', $furlevel,$furlevel,$furlevel,$furlevel,$furlevel,$furlevel,$furlevel,$maxtorps,$furlevel,$furlevel,$maxarmour,$maxcloak, $sector,0,0,0,$maxenergy,0,$maxfighters, '0', 'N', '0', '0', '0', '$ewd', 'N', 'N', '0', '0', NULL, 'N', 'N')"); 
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
if($swordfish != $adminpass)
{
  echo "<FORM ACTION=create_fur.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
}
else
{
	$res=$db->Execute("SELECT COUNT(*) AS FNUM FROM $dbtables[ships] WHERE ship_name LIKE '%furangee%'");
    $row=$res->fields;
	echo "There are about $row[FNUM] furangee<br>";
	for ($i=0;$i<5;$i++) {
		createFur(2);
	}
}

include("footer.php");

?> 
