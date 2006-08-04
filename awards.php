<?
/*************************************************************************
awards.php - shows the award winners of the game
Copyright (c)2003-2004 Ben Gibbs

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
***************************************************************************/

include("config.php");
updatecookie();

include("languages/$lang");
$title="Awards";
include("header.php");
function insignia_name($score) {
global $insignia;
global $l_insignia;

$score_array = array('1000', '3000', '6000', '9000', '12000', '15000', '20000', '40000', '60000', '80000', '100000', '120000', '160000', '200000', '250000', '300000', '350000', '400000', '450000', '500000', '1000000','2000000','4000000','8000000');

for ( $i=0; $i<count($score_array); $i++)
{           
    if ( $score < $score_array[$i])
     {
       $insignia = $l_insignia[$i];
       break;    
     }
}

if(!isset($insignia))
  $insignia = end($l_insignia);

return $insignia;

}
/*
   Rewrited display of alliances list
*/
function DISPLAY_ALL_ALLIANCES()
{
   global $color, $color_header, $order, $type, $PHP_SELF, $l_team_galax, $l_team_member, $l_team_coord, $l_score, $l_name;
   global $db, $dbtables;

   echo "<h2>$l_team_galax</h2>";
   echo "<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=2>";
   echo "<TR BGCOLOR=\"$color_header\">";
   echo "<TD><B>Rank</B></TD>";
   echo "<TD><B>$l_name</B></TD>";
   echo "<TD><B>$l_score</B></TD>";
   echo "</TR>";
   $sql_query = "SELECT $dbtables[players].character_name,
                     COUNT(*) as number_of_members,
                     ROUND(SQRT(SUM(POW($dbtables[players].score,2)))) as total_score,
                     $dbtables[teams].id,
                     $dbtables[teams].team_name
                  FROM $dbtables[players]
                  LEFT JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id
                  WHERE $dbtables[players].team = $dbtables[teams].id
                  GROUP BY $dbtables[teams].team_name ORDER BY total_score DESC";
   $res = $db->Execute($sql_query) or die($db->ErrorMsg());
   $rank = 1;
   while(!$res->EOF) {
    $row = $res->fields;
   	echo "<TR BGCOLOR=\"$color\">";
	echo "<TD><B>$rank</B></TD>";
	$rank++;
   	echo "<TD>".$row[team_name]."</TD>";
   	echo "<TD>".NUMBER($row[total_score])."</TD>";
   	echo "</TR>";
    $res->MoveNext();
   }
   echo "</table><BR>";
}
connectdb();
bigtitle();
if($swordfish != $adminpass)
{
  echo "<FORM ACTION=awards.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
}
else
{
	//-------------------------------------------------------------------------------------------------
	
	$by="score DESC,character_name ASC";
	
	$res = $db->Execute("SELECT $dbtables[players].email,$dbtables[players].score,$dbtables[players].character_name,$dbtables[players].turns_used,$dbtables[players].last_login,UNIX_TIMESTAMP($dbtables[players].last_login) as online,$dbtables[players].rating, $dbtables[teams].team_name, IF($dbtables[players].turns_used<150,0,ROUND($dbtables[players].score/$dbtables[players].turns_used)) AS efficiency,$dbtables[ships].ship_destroyed,$dbtables[ships].dev_escapepod FROM $dbtables[players] LEFT JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id JOIN $dbtables[ships] WHERE email NOT LIKE '%@furangee' AND $dbtables[players].player_id > 1 AND ship_id=currentship ORDER BY $by");
	$num_players = $res->RecordCount();
	//-------------------------------------------------------------------------------------------------
	
	if(!$res)
	{
	  echo "$l_ranks_none<BR>";
	}
	else {
		$rowCount = 0;
		while(!$res->EOF) {
			$row[$rowCount++] = $res->fields;
			$res->MoveNext();
		}
		echo "Awards for the game ending ".date("jS F")."<br><br>";
		// get the top scoring non-dead player
		reset($row);
		$top = $row[0];
		while ($top[ship_destroyed] == "Y" && $top[dev_escapepod] == "N") {
			$top = next($row);
		}
		echo "[b]Top Player[/b]<br><br>";
		echo "8) ".insignia_name($top[score])." ".$top[character_name]." (".NUMBER($top[score]).")<br><br>";
		echo "[b]Top Team[/b]<br><br>";
		$sql_query = "SELECT COUNT(*) as number_of_members,
						 ROUND(SQRT(SUM(POW($dbtables[players].score,2)))) as total_score,
						 $dbtables[teams].*
					  FROM $dbtables[players]
					  LEFT JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id
					  WHERE $dbtables[players].team = $dbtables[teams].id
					  GROUP BY $dbtables[teams].team_name ORDER BY total_score DESC";
		$res = $db->Execute($sql_query) or die($db->ErrorMsg());
		$row = $res->fields;
		while ($row[number_of_members]<2 && !$res->EOF) {
			$row=$res->fields;
		}
		echo " 8)  8)  8) ".$row[team_name]." (".NUMBER($row[total_score]).")<br>";
		echo "'".$row[description]."'<br><br>";
		echo "[b]Members[/b]<br>";
		$sql_query = "SELECT character_name, score FROM $dbtables[players] WHERE team=$row[id]";
		$res2= $db->Execute($sql_query) or die($db->ErrorMsg());
		while (!$res2->EOF) {
			$row = $res2->fields;
			echo "- ".insignia_name($row[score])." ".$row[character_name]." (Score: ".NUMBER($row[score]).")<br>";
			$res2->MoveNext();
		}
		echo "<br><br>";
		echo "[b]Most Evil Player[/b]<br><br>";
		$res3=$db->Execute("SELECT character_name, rating, score FROM $dbtables[players] WHERE email NOT LIKE '%furangee%' ORDER BY rating ASC LIMIT 1");
		$row = $res3->fields;
		echo " :twisted: ".insignia_name($row[score])." ".$row[character_name]."!!!<br><br>";
		echo "[b]Most Saintly[/b]<br><br>";
		$res=$db->Execute("SELECT character_name, rating, score FROM $dbtables[players] WHERE email NOT LIKE '%furangee%' ORDER BY rating DESC LIMIT 1");
		$row = $res->fields;
		echo " :D ".insignia_name($row[score])." ".$row[character_name]."!!!<br><br>";
		echo "[b]Killing Rankings[/b]<br><br>";
		$res=$db->Execute("SELECT character_name,score,fks FROM $dbtables[players],$dbtables[kills] WHERE $dbtables[players].player_id=$dbtables[kills].player_id AND fks>0 ORDER BY fks DESC");
		$i=1;
		echo "Top Furangee Destroyers:<br><br>";
		while (!$res->EOF) {
			$row = $res->fields;
			if ($i == 1) {
				$top = $row[character_name];
			}
			echo ($i++).". ".$row[character_name]." killed ".NUMBER($row[fks])." furangee<br>";
			$res->MoveNext();
		}
		
		echo "<br>Well done to $top for killing the most Furangee!<br>";
		
		echo "<h1>Rankings</h1>";
		$by="score DESC,character_name ASC";
	
	$res = $db->Execute("SELECT $dbtables[players].email,$dbtables[players].score,$dbtables[players].character_name,$dbtables[players].turns_used,$dbtables[players].last_login,UNIX_TIMESTAMP($dbtables[players].last_login) as online,$dbtables[players].rating, $dbtables[teams].team_name, IF($dbtables[players].turns_used<150,0,ROUND($dbtables[players].score/$dbtables[players].turns_used)) AS efficiency,$dbtables[ships].ship_destroyed,$dbtables[ships].dev_escapepod FROM $dbtables[players] LEFT JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id JOIN $dbtables[ships] WHERE email NOT LIKE '%@furangee' AND $dbtables[players].player_id > 1 AND ship_id=currentship ORDER BY $by");
	$num_players = $res->RecordCount();
	//-------------------------------------------------------------------------------------------------
	$i=0;
	if(!$res)
	{
	  echo "$l_ranks_none<BR>";
	}
	else
	{
	  echo "<BR>$l_ranks_pnum: " . NUMBER($num_players)."<br>";
	  while(!$res->EOF)
	  {
		$row = $res->fields;
		if ($row[turns_used]!=0) {
			$i++;
			$rating=round(sqrt( abs($row[rating]) ));
			if(abs($row[rating])!=$row[rating])
			{
			  $rating=-1*$rating;
			}
			$curtime = TIME();
			$time = $row[online];
			$difftime = ($curtime - $time) / 60;
			$temp_turns = $row[turns_used];
			if ($temp_turns <= 0)
			{
			$temp_turns = 1;
			}
			echo NUMBER($i) . ":   ".player_insignia_name($row[email])." ".$row[character_name]." (";
			if ($row[ship_destroyed] == "Y" && $row[dev_escapepod] == "N") {
				echo "Player Died)";
			} else {
				echo NUMBER($row[score]) . ")";
			}
			echo "<br>";
		}
		$res->MoveNext();
	  }
	echo "<br>[b]Alliances[/b]<BR><br>";
	   $sql_query = "SELECT $dbtables[players].character_name,
						 COUNT(*) as number_of_members,
						 ROUND(SQRT(SUM(POW($dbtables[players].score,2)))) as total_score,
						 $dbtables[teams].id,
						 $dbtables[teams].team_name,
						 $dbtables[teams].description 
					  FROM $dbtables[players]
					  LEFT JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id
					  WHERE $dbtables[players].team = $dbtables[teams].id
					  GROUP BY $dbtables[teams].team_name ORDER BY total_score DESC";
	   $res = $db->Execute($sql_query) or die($db->ErrorMsg());
	   $rank = 1;
	   while(!$res->EOF) {
		$row = $res->fields;
		echo "$rank: ".$row[team_name]." (".NUMBER($row[total_score]).")<br>";
		echo "'".$row[description]."'<br>";
		$rank++;
		$res->MoveNext();
	   }
	   echo "<BR>";	
	}
	}
}
include("footer.php");

?>
