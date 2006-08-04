<?
include("config.php");
updatecookie();

include("languages/$lang");
$title=$l_gns_title;
include("header.php");

connectdb();

if(checklogin())
{
  die();
}

//-------------------------------------------------------------------------------------------------
$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;
$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship] LIMIT 1");
$shipinfo = $res->fields;

$result2 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]'");
$sectorinfo = $result2->fields;

$result3 = $db->Execute("SELECT planet_id FROM $dbtables[planets] WHERE sector_id='$playerinfo[sector]'");
$num_planets = $result3->RecordCount();
bigtitle();

if($playerinfo[turns] < 1)
{
  echo "$l_gns_turn";
}
elseif($playerinfo[on_planet]=='Y')
{
  echo $l_gns_onplanet;
}
elseif($num_planets >= $max_planets_sector)
{
  echo $l_gns_full;
}

/* -------------------------------------------------------------- *
 * I'm (SharpBlue) lazy. With many planets by sector that code    *
 * becomes pretty hard to manage, and besides... Isn't it a       *
 * little too powerful?                                           *
 * -------------------------------------------------------------- *
elseif($sectorinfo[planet] == "Y")
{
  echo "There is already a planet in this sector.";
  if($playerinfo[player_id]==$sectorinfo[planet_owner])
  {
    if($destroy==1 && $allow_genesis_destroy)
    {
    // not multilingualed cause its not working right now anyway
      echo "<BR>Are you sure???<BR><A HREF=genesis.php?destroy=2>YES, Let them die!</A><BR>";
      echo "<A HREF=device.php>No! That would be Evil!</A><BR>";
    }
    elseif($destroy==2 && $allow_genesis_destroy)
    {
      if($shipinfo[dev_genesis] > 0)
      {
        $deltarating=$sectorinfo[planet_colonists];
        $update = $db->Execute("UPDATE $dbtables[universe] SET planet_name='', planet_organics=0, planet_energy=0, planet_ore=0, planet_goods=0, planet_colonists=0, planet_credits=0, planet_fighters=0, planet_owner=null, planet_corp=null, base='N',base_sells='N', base_torp=0, planet_defeated='N', planet='N' WHERE sector_id=$playerinfo[sector]");
        $update2=$db->Execute("UPDATE $dbtables[players] SET turns_used=turns_used+1, turns=turns-1, rating=rating-$deltarating WHERE player_id=$playerinfo[player_id]");
		$update2=$db->Execute("UPDATE $dbtables[ships] SET dev_genesis=dev_genesis-1 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
        echo "<BR>Errr, there was one with $deltarating colonists here....<BR>";
      }
      else
      {
        echo "$l_gns_nogenesis";
      }
    }
    elseif($allow_genesis_destroy)
    {
      echo "<BR>Do you want to destroy <A HREF=genesis.php?destroy=1>";
      if($sectorinfo[planet_name]=="")
      {
        echo "Unnamed</A>?";
      }
      else
      {
        echo $sectorinfo[planet_name] . "</A>?";
      }
    }
  }
}
* --------------------------------------------------------------- *
* If anyone who's coded this thing is willing to update it to     *
* support multiple planets, go ahead. I suggest removing this     *
* code completely from here and putting it in the planet menu     *
* instead. Easier to manage, makes more sense too.                *
* End of comments section.                                        *
* -------------------------------------------------------------- */

elseif($shipinfo[dev_genesis] < 1)
{
  echo "$l_gns_nogenesis";
}
else
{
  $buildFlag = true; // Okay to build a planet
  $res = $db->Execute("SELECT allow_planet, corp_zone, owner FROM $dbtables[zones] WHERE zone_id='$sectorinfo[zone_id]'");
  $zoneinfo = $res->fields;
  if($zoneinfo[allow_planet] == 'N')
  {
    echo "$l_gns_forbid";
	$buildFlag = false;
  }
  elseif($zoneinfo[allow_planet] == 'L')
  {
  	// The owner can always build a planet
	if ($playerinfo[player_id] != $zoneinfo[owner]) {
		// Now let's find out which team the owner is in
		$res = $db->Execute("SELECT team FROM $dbtables[players] WHERE player_id=$zoneinfo[owner]");
        $ownerinfo = $res->fields;
		// If he is in no team or they are in different teams then tough luck
		if ($ownerinfo[team] == 0 || $ownerinfo[team] != $playerinfo[team])
        {
          echo $l_gns_bforbid;
		  $buildFlag = false;
		}
	}
  }
  if ($buildFlag==true) {
    $query1 = "INSERT INTO $dbtables[planets] VALUES('', $playerinfo[sector], NULL, 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo[player_id], 0, 'N', 'N', $default_prod_organics, $default_prod_ore, $default_prod_goods, $default_prod_energy, $default_prod_fighters, $default_prod_torp, 'N',0)";
    $update1 = $db->Execute($query1);
	// Get the number of the planet just created
	$query = $db->Execute("SELECT planet_id FROM $dbtables[planets] WHERE planet_id = LAST_INSERT_ID()");
	$planet_id = $query->fields;
    $query2 = "UPDATE $dbtables[players] SET turns_used=turns_used+1, turns=turns-1 WHERE player_id=$playerinfo[player_id]";
	$update2=$db->Execute("UPDATE $dbtables[ships] SET dev_genesis=dev_genesis-1 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    $update2 = $db->Execute($query2);
    echo $l_gns_pcreate;
	echo "<br><br><a href=planet.php?planet_id=".$planet_id[planet_id]."&kk=".date("U").">Click here</a> to go to the planet menu.";
  }
}

//-------------------------------------------------------------------------------------------------

echo "<BR><BR>";
TEXT_GOTOMAIN();

include("footer.php");

?>
