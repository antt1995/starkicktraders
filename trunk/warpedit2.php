<?


include("config.php");
updatecookie();

include("languages/$lang");

$title=$l_warp_title;
include("header.php");

connectdb();

if(checklogin())
{
  die();
}

$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo=$result->fields;
$result = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo=$result->fields;
$result4 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]'");
$sectorinfo=$result4->fields;

// Check if player is on a planet or not
if ($playerinfo[on_planet] == 'Y') {
	echo "You cannot use warp editors when on a planet!<br><br>";
  TEXT_GOTOMAIN();
  include("footer.php");
  die();
}	

if($playerinfo[turns] < 1)
{
  echo "$l_warp_turn<BR><BR>";
  TEXT_GOTOMAIN();
  include("footer.php");
  die();
}

if($shipinfo[dev_warpedit] < 1)
{
  echo "$l_warp_none<BR><BR>";
  TEXT_GOTOMAIN();
  include("footer.php");
  die();
}
$target_sector=round($target_sector);

if($target_sector > $sector_max || $target_sector < 0 ) {
  echo "$l_warp_nosector<BR><BR>";
  TEXT_GOTOMAIN();
  include("footer.php");
  die();
}
	
// ******************** SOURCE CHECK **************************
// Check source sector to see if forming warp links is okay or not
$res = $db->Execute("SELECT allow_warpedit FROM $dbtables[zones] WHERE zone_id='$sectorinfo[zone_id]'");
$zoneinfo = $res->fields;
if($zoneinfo[allow_warpedit] == 'N')
{
  echo "$l_warp_forbid<BR><BR>";
  TEXT_GOTOMAIN();
  include("footer.php");
  die();
}

if($zoneinfo[allow_warpedit] == 'L')
{
  $result3 = $db->Execute("SELECT * FROM $dbtables[zones] WHERE zone_id='$sectorinfo[zone_id]'");
  $zoneowner_info = $result3->fields;

  $result5 = $db->Execute("SELECT team FROM $dbtables[players] WHERE player_id='$zoneowner_info[owner]'");
  $zoneteam = $result5->fields;

  if($zoneowner_info[owner] != $playerinfo[player_id])
  {
    if(($zoneteam[team] != $playerinfo[team]) || ($playerinfo[team] == 0))
    {
      echo "$l_warp_forbid<BR><BR>";
      TEXT_GOTOMAIN();
      include("footer.php");
      die();
    }
  }
}

// ******************** TARGET SECTOR CHECK **********************

bigtitle();
if (empty($oneway) || !$oneway) {
	$result2 = $db->Execute ("SELECT * FROM $dbtables[universe] WHERE sector_id=$target_sector");
	$row = $result2->fields;
	if(!$row)
	{
	  echo "$l_warp_nosector<BR><BR>";
	  TEXT_GOTOMAIN();
	  die();
	}
	// Check target zone
	$res = $db->Execute("SELECT allow_warpedit FROM $dbtables[zones] WHERE zone_id='$row[zone_id]'");
	$zoneinfo = $res->fields;
	if($zoneinfo[allow_warpedit] == 'N')
	{
	  $l_warp_twoerror = str_replace("[target_sector]", $target_sector, $l_warp_twoerror);
	  echo "$l_warp_twoerror<BR><BR>";
	  TEXT_GOTOMAIN();
	  include("footer.php");
	  die();
	}
	
	if($zoneinfo[allow_warpedit] == 'L')
	{
	  $result3 = $db->Execute("SELECT * FROM $dbtables[zones] WHERE zone_id='$row[zone_id]'");
	  $zoneowner_info = $result3->fields;
	
	  $result5 = $db->Execute("SELECT team FROM $dbtables[players] WHERE player_id='$zoneowner_info[owner]'");
	  $zoneteam = $result5->fields;
	
	  if($zoneowner_info[owner] != $playerinfo[player_id])
	  {
		if(($zoneteam[team] != $playerinfo[team]) || ($playerinfo[team] == 0))
		{
	  	  $l_warp_twoerror = str_replace("[target_sector]", $target_sector, $l_warp_twoerror);
	  	  echo "$l_warp_twoerror<BR><BR>";
		  TEXT_GOTOMAIN();
		  include("footer.php");
		  die();
		}
	  }
	}
}

$res = $db->Execute("SELECT COUNT(*) as count FROM $dbtables[links] WHERE link_start=$playerinfo[sector]");
$row = $res->fields;
$numlink_start=$row[count];

if($numlink_start>=$link_max )
{

  echo "$l_warp_sectex<BR><BR>";
  TEXT_GOTOMAIN();
  include("footer.php");
  die();
}
// Check for destination sector link max
if (!$oneway) {
	$res = $db->Execute("SELECT COUNT(*) as count FROM $dbtables[links] WHERE link_start=$target_sector");
	$row = $res->fields;
	$numlink_start=$row[count];
	if($numlink_start>=$link_max )
	{	
	  echo "$l_warp_sectex2<BR><BR>";
	  TEXT_GOTOMAIN();
	  include("footer.php");
	  die();
	}	
}


$result3 = $db->Execute("SELECT * FROM $dbtables[links] WHERE link_start=$playerinfo[sector]");
if($result3 > 0)
{
  while(!$result3->EOF)
  {
    $row = $result3->fields;
    if($target_sector == $row[link_dest])
    {
      $flag = 1;
    }
    $result3->MoveNext();
  }
  if($flag == 1)
  {
  $l_warp_linked = str_replace("[target_Sector]", $target_sector, $l_warp_linked);
    echo "$l_warp_linked<BR><BR>";
  }
  elseif($playerinfo[sector] == $target_sector)
  {
    echo $l_warp_cantsame;
  }
  else
  {
    $insert1 = $db->Execute ("INSERT INTO $dbtables[links] SET link_start=$playerinfo[sector], link_dest=$target_sector");
    $update1 = $db->Execute ("UPDATE $dbtables[players] turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
	$update2 = $db->Execute ("UPDATE $dbtables[ships] SET dev_warpedit=dev_warpedit - 1 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    if($oneway)
    {
      echo "$l_warp_coneway $target_sector.<BR><BR>";
    }
    else
    {
      $result4 = $db->Execute ("SELECT * FROM $dbtables[links] WHERE link_start=$target_sector");
      if($result4)
      {
        while(!$result4->EOF)
        {
          $row = $result4->fields;
          if($playerinfo[sector] == $row[link_dest])
          {
            $flag2 = 1;
          }
          $result4->MoveNext();
        }
      }
      if($flag2 != 1)
      {
        $insert2 = $db->Execute ("INSERT INTO $dbtables[links] SET link_start=$target_sector, link_dest=$playerinfo[sector]");
      }

      echo "$l_warp_ctwoway $target_sector.<BR><BR>";
    }
  }
}

TEXT_GOTOMAIN();

include("footer.php");

?>
