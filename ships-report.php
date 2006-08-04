<?
include("config.php");
updatecookie();

include("languages/$lang");
$title="Ships Report";

include("header.php");

connectdb();

if(checklogin())
{
  die();
}
bigtitle();

// Get data about ships
$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;


$query = "SELECT * FROM $dbtables[ships],$dbtables[ship_types] WHERE player_id=$playerinfo[player_id] AND type=type_id";

if(!empty($sort))
{
    $query .= " ORDER BY";
    if($sort == "sector" || $sort == "ship_name" || $sort == "type" )
    {
      $query .= " $sort ASC";
    }
    else
    {
      $query .= " sector ASC";
    }
}
else
{
     $query .= " ORDER BY sector ASC";
}
$res = $db->Execute($query);
$num_ships = $res->RecordCount();
echo "<BR>";
if ($num_ships == 1) {
	echo "<H2>You have 1 ship</H2>";
} else {
	echo "<H2>You have $num_ships ships</H2>";
}
	  echo "<TABLE WIDTH=100% BORDER=1 CELLSPACING=0 CELLPADDING=2>";
	  echo "<TR BGCOLOR=\"$color\">";
	  echo "<TD align=center><B><a href=ships-report.php>Sector</a></B></TD><td align=center><a href=ships-report.php?sort=ship_name>Ship Name</a></td><td align=center><a href=ships-report.php?sort=type>Ship Class</a></td></tr>";

while(!$res->EOF)
{
	$shipinfo = $res->fields;
     echo "<TD align=center><A HREF=rsmove.php?engage=1&destination=". $shipinfo[sector] . "&kk=".date("U").">". $shipinfo[sector] ."</A></B></TD>";
	 echo "<TD align=center>$shipinfo[ship_name]</TD>";
     echo "<TD align=center>$shipinfo[name]</TD></tr>";
	$res->MoveNext();
}
echo "</TABLE><P>";
echo "<BR><BR>";

TEXT_GOTOMAIN();

include("footer.php");

?>