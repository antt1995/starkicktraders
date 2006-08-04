<?
include("config.php");
updatecookie();

include("languages/$lang");
$title="Top Rankings";
include("header.php");
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

//-------------------------------------------------------------------------------------------------

$by="score DESC,character_name ASC";

$res = $db->Execute("SELECT $dbtables[players].email,$dbtables[players].score,$dbtables[players].character_name,$dbtables[players].turns_used,$dbtables[players].last_login,UNIX_TIMESTAMP($dbtables[players].last_login) as online,$dbtables[players].rating, $dbtables[teams].team_name, IF($dbtables[players].turns_used<150,0,ROUND($dbtables[players].score/$dbtables[players].turns_used)) AS efficiency,$dbtables[ships].ship_destroyed,$dbtables[ships].dev_escapepod FROM $dbtables[players] LEFT JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id JOIN $dbtables[ships] WHERE email NOT LIKE '%@furangee' AND $dbtables[players].player_id > 1 AND ship_id=currentship ORDER BY $by");
$num_players = $res->RecordCount();
//-------------------------------------------------------------------------------------------------

if(!$res)
{
  echo "$l_ranks_none<BR>";
}
else if($teams=="yes") {
	DISPLAY_ALL_ALLIANCES();
	echo "Find out more about these Alliances in the game!<br>";
}
else if($detail=="")
{
  echo "<a href=ranking.php?teams=yes>Show Alliance Ranking</a><br>";
  echo "<BR>$l_ranks_pnum: " . NUMBER($num_players);
  echo "<br>Inactive players are not shown<br>";
  //echo "<BR>$l_ranks_dships<BR><BR>";
  echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>";
  echo "<TR BGCOLOR=\"$color_header\"><TD><B>$l_ranks_rank</B></TD><TD><B>$l_score</B></TD><TD><B>$l_player</B></TD></TR>\n";
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
		$online = " ";
		if($difftime <= 5) $online = "Online";
		echo "<TR BGCOLOR=\"$color\"><TD>" . NUMBER($i) . "</TD><TD align=center>";
		if ($row[ship_destroyed] == "Y" && $row[dev_escapepod] == "N") {
			echo "-</td><td><font color=#ff0000>Dead player ";
		} else {
			echo NUMBER($row[score]) . "</TD><TD><font color=#00ff00>".player_insignia_name($row[email]);
		}
		echo "&nbsp;";
		echo "<b><a href=ranking.php?detail=".urlencode($row[character_name]).">$row[character_name]</a></b></font></TD></TR>";
		if($color == $color_line1)
		{
		  $color = $color_line2;
		}
		else
		{
		  $color = $color_line1;
		}
	}
    $res->MoveNext();
  }
  echo "</TABLE>";
} else {
	// Detailed description of player
	$detail = urldecode($detail);
	$res = $db->Execute("SELECT $dbtables[players].*,UNIX_TIMESTAMP($dbtables[players].last_login) as online, $dbtables[teams].team_name, IF($dbtables[players].turns_used<150,0,ROUND($dbtables[players].score/$dbtables[players].turns_used)) AS efficiency FROM $dbtables[players] LEFT JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id  WHERE email NOT LIKE '%@furangee' and character_name='$detail'");
  if (!$res->EOF)
  {
	$row = $res->fields;
	echo "<h2>Player: ". player_insignia_name($row[email])." $detail</h2>";
  	//echo "SELECT * FROM $dbtables[profile] WHERE player_id=$row[player_id]<br>";
    $res2 = $db->Execute("SELECT * FROM $dbtables[profile] WHERE player_id=$row[player_id]");
	$profile = $res2->fields;
	if (($profile[pic_url] != NULL) && ($profile[pic_url] != "http://") && ($profile[pic_url] != "http://www.berigames.com/skt/")) {
		echo "<img src=\"$profile[pic_url]\">";
	} else {
		echo "<img src=\"images/anon.gif\">";
	}
	echo "<br>";
	// get medals
	$res3 = $db->Execute("SELECT * FROM award_winners, $dbtables[medals] WHERE character_name='".addslashes($row[character_name])."' AND award_winners.type_id=$dbtables[medals].type_id ORDER BY game_num");
	while (!$res3->EOF) {
		$medal = $res3->fields;
		echo "Game $medal[game_num] Medal: ".$medal[medal_name]." <image src=images/$medal[graphic]><br>";
		$res3->MoveNext();
	}
	//echo "Profile $profile[0],$profile[1]<br>";
    //$row = $res->fields;
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
    $online = "No";
    if($difftime <= 5) $online = "Yes";
	echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>";
    echo "<TR BGCOLOR=\"$color\"><TD><b>$l_score</b></TD><TD>" . NUMBER($row[score]) . "</TD></TR>";
    echo "<TR><TD><B>$l_turns_used</B></TD><TD>" . NUMBER($row[turns_used]) . "</TD></TR>";
	echo "<TR><TD><B>$l_ranks_lastlog</B></TD><TD>$row[last_login]</TD></TR>";
	echo "<TR><TD><B>$l_ranks_good/$l_ranks_evil</B></TD><TD>" . NUMBER($rating) . "</TD></TR>";
	echo "<TR><TD><B>$l_team_alliance</B></TD><TD>$row[team_name]</TD></TR>";
	echo "<TR><TD><B>Online</B></TD><TD>$online</TD></TR>";
	echo "<TR><TD><B>Efficiency Rating</TD><TD>$row[efficiency]</TD></TR>\n";
	echo "<TR><td><b>Personal Skill Rating:</b></TD><TD>$profile[skill]</TD></TR>\n";
	echo "<TR><td><b>Professed Alignment:</b></TD><TD>$profile[alignment]</TD></TR>\n";
	echo "<TR><td colspan=2 align=center><b>Background Story</b><br><hr></td></tr>\n";
	echo "<tr><td colspan=2 align=justified>";
	if ($profile[story] == "") {
		echo "No story so far!";
	} else {
		echo stripslashes($profile[story]);
	}
	echo "</td></tr></table><br><br>";
  } else {
  	echo "No such player!<br>";
  } 
  echo "<a href=ranking.php>Return to Rankings</A><BR>"; 
}

echo "<BR>";

if(empty($username))
{
  TEXT_GOTOLOGIN();
}
else
{
  TEXT_GOTOMAIN();
}

include("footer.php");

?>
