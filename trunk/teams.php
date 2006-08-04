<?
include("config.php");
updatecookie();

include("languages/$lang");
$title=$l_team_title;
include("header.php");
connectdb();

if (checklogin()) {die();}

function decodeHTML($string) {
   $string = strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES)));
   $string = preg_replace("/&#([0-9]+);/me", "chr('\\1')", $string);
   return $string;
}

bigtitle();
$testing = false; // set to false to get rid of password when creating new alliance

/*
   Setting up some recordsets.

   I noticed before the rewriting of this page
   that in some case recordset may be fetched
   more thant once, which is NOT optimized.
*/

/* Get user info */
$result        = $db->Execute("SELECT $dbtables[players].*, $dbtables[teams].team_name, $dbtables[teams].description, $dbtables[teams].creator, $dbtables[teams].id
                        FROM $dbtables[players]
                        LEFT JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id
                        WHERE $dbtables[players].email='$username'") or die($db->ErrorMsg());
$playerinfo    = $result->fields;

/*
   We do not want to query the database
   if it is not necessary.
*/
if ($playerinfo[team_invite] != "") {
   /* Get invite info */
   $invite        = $db->Execute(" SELECT $dbtables[players].player_id, $dbtables[players].team_invite, $dbtables[teams].team_name,$dbtables[teams].id
                        FROM $dbtables[players]
                        LEFT JOIN $dbtables[teams] ON $dbtables[players].team_invite = $dbtables[teams].id
                        WHERE $dbtables[players].email='$username'") or die($db->ErrorMsg());
   $invite_info   = $invite->fields;
}

/*
   Get Team Info
*/
$whichteam = stripnum($whichteam);
if ($whichteam)
{
   $result_team   = $db->Execute("SELECT * FROM $dbtables[teams] WHERE id=$whichteam") or die($db->ErrorMsg());
   $team          = $result_team->fields;
} else {
   $result_team   = $db->Execute("SELECT * FROM $dbtables[teams] WHERE id=$playerinfo[team]") or die($db->ErrorMsg());
   $team          = $result_team->fields;
}

function LINK_BACK()
{
   global $PHP_SELF, $l_clickme, $l_team_menu;
   echo "<BR><BR><a href=$PHP_SELF?&kk=".date("U").">$l_clickme</a> $l_team_menu.<BR><BR>";
}

/*
   Rewrited display of alliances list
*/
function DISPLAY_ALL_ALLIANCES()
{
   global $color, $color_header, $order, $type, $PHP_SELF, $l_team_galax, $l_team_member, $l_team_coord, $l_score, $l_name;
   global $db, $dbtables;

   echo "<br><br>$l_team_galax<BR>";
   echo "<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=2>";
   echo "<TR BGCOLOR=\"$color_header\">";

   if ($type == "d") {
      $type = "a";
      $by = "ASC";
   } else {
      $type = "d";
      $by = "DESC";
   }
   echo "<TD><B><A HREF=$PHP_SELF?order=team_name&type=$type>$l_name</A></B></TD>";
   echo "<TD><B><A HREF=$PHP_SELF?order=number_of_members&type=$type>$l_team_members</A></B></TD>";
   echo "<TD><B><A HREF=$PHP_SELF?order=character_name&type=$type>$l_team_coord</A></B></TD>";
   echo "<TD><B><A HREF=$PHP_SELF?order=total_score&type=$type>$l_score</A></B></TD>";
   echo "</TR>";
   $sql_query = "SELECT $dbtables[players].character_name,
                     COUNT(*) as number_of_members,
                     ROUND(SQRT(SUM(POW($dbtables[players].score,2)))) as total_score,
                     $dbtables[teams].id,
                     $dbtables[teams].team_name,
                     $dbtables[teams].creator
                  FROM $dbtables[players]
                  LEFT JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id
                  WHERE $dbtables[players].team = $dbtables[teams].id
                  GROUP BY $dbtables[teams].team_name";
   /*
      Setting if the order is Ascending or descending, if any.
      Default is ordered by teams.team_name
   */
   if ($order)
   {
      $sql_query = $sql_query ." ORDER BY " . $order . " $by";
   }
   $res = $db->Execute($sql_query) or die($db->ErrorMsg());
   while(!$res->EOF) {
    $row = $res->fields;
   	echo "<TR BGCOLOR=\"$color\">";
   	echo "<TD><a href=$PHP_SELF?teamwhat=1&whichteam=".$row[id].">".$row[team_name]."</A></TD>";
   	echo "<TD>".$row[number_of_members]."</TD>";
// This fixes it so that it actually displays the coordinator, and not the first member of the team.

          $sql_query_2 = "SELECT character_name FROM $dbtables[players] WHERE player_id = $row[creator]";
          $res2 = $db->Execute($sql_query_2) or die($db->ErrorMsg());
          while(!$res2->EOF) {
           $row2 = $res2->fields;
           $res2->MoveNext();
          }

// If there is a way to redo the original sql query instead, please, do so, but I didnt see a way to.

   	echo "<TD><a href=mailto2.php?name=".$row2[character_name].">".$row2[character_name]."</A></TD>";
   	echo "<TD>".NUMBER($row[total_score])."</TD>";
   	echo "</TR>";
    $res->MoveNext();
   }
   echo "</table><BR>";
}


function DISPLAY_INVITE_INFO()
{
   global $playerinfo, $invite_info, $PHP_SELF, $l_team_noinvite, $l_team_ifyouwant, $l_team_tocreate, $l_clickme, $l_team_injoin, $l_team_tojoin, $l_team_reject, $l_team_or;
   if (!$playerinfo[team_invite]) {
      echo "<br><br><font color=blue size=2><b>$l_team_noinvite</b></font><BR>";
      echo "$l_team_ifyouwant<BR>";
      echo "<a href=\"$PHP_SELF?teamwhat=6\">$l_clickme</a> $l_team_tocreate<BR><BR>";
   } else {
	   echo "<br><br><font color=blue size=2><b>$l_team_injoin ";
	   echo "<a href=$PHP_SELF?teamwhat=1&whichteam=$playerinfo[team_invite]>$invite_info[team_name]</A>.</b></font><BR>";
	   echo "<A HREF=$PHP_SELF?teamwhat=3&whichteam=$playerinfo[team_invite]>$l_clickme</A> $l_team_tojoin <B>$invite_info[team_name]</B> $l_team_or <A HREF=$PHP_SELF?teamwhat=8&whichteam=$playerinfo[team_invite]>$l_clickme</A> $l_team_reject<BR><BR>";
   }
}


function showinfo($whichteam,$isowner)
{
	global $playerinfo, $invite_info, $team, $l_team_coord, $l_team_member, $l_options, $l_team_ed, $l_team_inv, $l_team_leave, $l_team_members, $l_score, $l_team_noinvites, $l_team_pending;
  global $db, $dbtables, $l_team_eject;

	/* Heading */
   echo"<div align=center>";
   echo "<h3><font color=white><B>$team[team_name]</B>";
 	echo "<br><font size=2>\"<i>$team[description]</i>\"</font></H3>";
   if ($playerinfo[team] == $team[id])
   {
      echo "<font color=white>";
   	if ($playerinfo[player_id] == $team[creator]) {
   	   echo "$l_team_coord ";
      }
   	else
   	{
   		echo "$l_team_member ";
   	}
   	echo "$l_options<br><font size=2>";
   	if ($playerinfo[player_id] == $team[creator])
   	{
   	   echo "[<a href=$PHP_SELF?teamwhat=9&whichteam=$playerinfo[team]>$l_team_ed</a>] - [<a href=$PHP_SELF?teamwhat=7&whichteam=$playerinfo[team]>$l_team_inv</a>] - ";
   	}
   	echo "[<a href=$PHP_SELF?teamwhat=2&whichteam=$playerinfo[team]>$l_team_leave</a>]</font></font>";
   }
   DISPLAY_INVITE_INFO();
   echo "</div>";

   /* Main table */
	echo "<table border=2 cellspacing=2 cellpadding=2 bgcolor=\"$color_line2\" width=\"100%\" align=center>";
	echo "<tr>";
	echo "<td><font color=white>$l_team_members</font></td>";
	echo "</tr><tr bgcolor=$color_line2>";
	$result  = $db->Execute("SELECT * FROM $dbtables[players] WHERE team=$whichteam");
	while (!$result->EOF) {
    $member = $result->fields;
		echo "<td> - $member[character_name] ($l_score ".NUMBER($member[score]).")";
		if ($isowner && ($member[player_id] != $playerinfo[player_id])) {
			echo " - <font size=2>[<a href=\"$PHP_SELF?teamwhat=5&who=$member[player_id]\">$l_team_eject</A>]</font></td>";
		} else {
			if ($member[player_id] == $team[creator])
			{
				echo " - $l_team_coord</td>";
			}
		}
		echo "</tr><tr bgcolor=$color_line2>";
    $result->MoveNext();
	}
   /* Displays for members name */
   $res = $db->Execute("SELECT player_id,character_name FROM $dbtables[players] WHERE team_invite=$whichteam");
	echo "<td bgcolor=$color_line2><font color=white>$l_team_pending <B>$team[team_name]</B></font></td>";
   echo "</tr><tr>";
	if ($res->RecordCount() > 0) {
		echo "</tr><tr bgcolor=$color_line2>";
		while (!$res->EOF) {
      $who = $res->fields;
			echo "<td> - $who[character_name]";
			if ($isowner) {
				echo " - <font size=2>[<a href=\"$PHP_SELF?teamwhat=10&who=$who[player_id]\">Retract</A>]</font></td>";
			} else {
				echo "</td>";
			}
		   echo "</tr><tr bgcolor=$color_line2>";
		  $res->MoveNext();
    }
	} else {
		echo "<td>$l_team_noinvites <B>$team[team_name]</B>.</td>";
      echo "</tr><tr>";
	}
	echo "</tr></table>";
}

switch ($teamwhat) {
	case 1:	// INFO on sigle alliance
		showinfo($whichteam, 0);
      LINK_BACK();
		break;
	case 2:	// LEAVE
		if (!$confirmleave) {
			echo "$l_team_confirmleave <B>$team[team_name]</B> ? <a href=\"$PHP_SELF?teamwhat=$teamwhat&confirmleave=1&whichteam=$whichteam\">$l_yes</a> - <A HREF=\"$PHP_SELF\">$l_no</A><BR><BR>";
		} elseif ($confirmleave == 1) {
			if ($team[number_of_members] == 1) {
				$db->Execute("DELETE FROM $dbtables[teams] WHERE id=$whichteam");
				$db->Execute("UPDATE $dbtables[players] SET team='0' WHERE player_id='$playerinfo[player_id]'");
				$db->Execute("UPDATE $dbtables[players] SET team_invite=0 WHERE team_invite=$whichteam");

        $res = $db->Execute("SELECT DISTINCT sector_id FROM $dbtables[planets] WHERE owner=$playerinfo[player_id] AND base='Y'");
        $i=0;
        while(!$res->EOF)
        {
          $row = $res->fields;
          $sectors[$i] = $row[sector_id];
          $i++;
          $res->MoveNext();
        }

        $db->Execute("UPDATE $dbtables[planets] SET corp=0 WHERE owner=$playerinfo[player_id]");
        if(!empty($sectors))
        {
          foreach($sectors as $sector)
          {
            calc_ownership($sector);
          }
        }
        defence_vs_defence($playerinfo[player_id]);
        kick_off_planet($playerinfo[player_id],$whichteam);

		$l_team_onlymember = str_replace("[team_name]", "<b>$team[team_name]</b>", $l_team_onlymember);
        echo "$l_team_onlymember<BR><BR>";
				playerlog($playerinfo[player_id], LOG_TEAM_LEAVE, decodeHTML($team[team_name]));
			} else {
				if ($team[creator] == $playerinfo[player_id]) {
					echo "$l_team_youarecoord <B>$team[team_name]</B>. $l_team_relinq<BR><BR>";
					echo "<FORM ACTION='$PHP_SELF' METHOD=POST>";
					echo "<TABLE><INPUT TYPE=hidden name=teamwhat value=$teamwhat><INPUT TYPE=hidden name=confirmleave value=2><INPUT TYPE=hidden name=whichteam value=$whichteam>";
					echo "<TR><TD>$l_team_newc</TD><TD><SELECT NAME=newcreator>";
					$res = $db->Execute("SELECT character_name,player_id FROM $dbtables[players] WHERE team=$whichteam ORDER BY character_name ASC");
					while(!$res->EOF) {
            $row = $res->fields;
						if ($row[player_id] != $team[creator])
							echo "<OPTION VALUE=$row[player_id]>$row[character_name]";
            $res->MoveNext();
					}
					echo "</SELECT></TD></TR>";
					echo "<TR><TD><INPUT TYPE=SUBMIT VALUE=$l_submit></TD></TR>";
					echo "</TABLE>";
					echo "</FORM>";
				} else {
					$db->Execute("UPDATE $dbtables[players] SET team='0' WHERE player_id='$playerinfo[player_id]'");
					$db->Execute("UPDATE $dbtables[teams] SET number_of_members=number_of_members-1 WHERE id=$whichteam");

          $res = $db->Execute("SELECT DISTINCT sector_id FROM $dbtables[planets] WHERE owner=$playerinfo[player_id] AND base='Y' AND corp!=0");
          $i=0;
          while(!$res->EOF)
          {
            $sectors[$i] = $res->fields[sector_id];
            $i++;
            $res->MoveNext();
          }

          $db->Execute("UPDATE $dbtables[planets] SET corp=0 WHERE owner=$playerinfo[player_id]");
          if(!empty($sectors))
          {
            foreach($sectors as $sector)
            {
              calc_ownership($sector);
            }
          }

					echo "$l_team_youveleft <B>$team[team_name]</B>.<BR><BR>";
          defence_vs_defence($playerinfo[player_id]);
          kick_off_planet($playerinfo[player_id],$whichteam);
  				playerlog($playerinfo[player_id], LOG_TEAM_LEAVE, decodeHTML($team[team_name]));
  				playerlog($team[creator], LOG_TEAM_NOT_LEAVE, "$playerinfo[character_name]");
				}
			}
		} elseif ($confirmleave == 2) { // owner of a team is leaving and set a new owner
			$res = $db->Execute("SELECT character_name FROM $dbtables[players] WHERE player_id=$newcreator");
			$newcreatorname = $res->fields;
			echo "$l_team_youveleft <B>$team[team_name]</B> $l_team_relto $newcreatorname[character_name].<BR><BR>";
			$db->Execute("UPDATE $dbtables[players] SET team='0' WHERE player_id='$playerinfo[player_id]'");
			$db->Execute("UPDATE $dbtables[players] SET team=$newcreator WHERE team=$creator");
			$db->Execute("UPDATE $dbtables[teams] SET number_of_members=number_of_members-1,creator=$newcreator WHERE id=$whichteam");

      $res = $db->Execute("SELECT DISTINCT sector_id FROM $dbtables[planets] WHERE owner=$playerinfo[player_id] AND base='Y' AND corp!=0");
      $i=0;
      while(!$res->EOF)
      {
        $sectors[$i] = $res->fields[sector_id];
        $i++;
        $res->MoveNext();
      }

      $db->Execute("UPDATE $dbtables[planets] SET corp=0 WHERE owner=$playerinfo[player_id]");
      if(!empty($sectors))
      {
        foreach($sectors as $sector)
        {
          calc_ownership($sector);
        }
      }

			playerlog($playerinfo[player_id], LOG_TEAM_NEWLEAD, decodeHTML($team[team_name])."|$newcreatorname[character_name]");
			playerlog($newcreator, LOG_TEAM_LEAD,decodeHTML($team[team_name]));
		}

		LINK_BACK();
		break;
	case 3: // JOIN
                if($playerinfo[team] <> 0)
                {
                   echo $l_team_leavefirst . "<BR>";
                }                 
                else
                {
                   if($playerinfo[team_invite] == $whichteam)
                   {
				   	// Find out if the team is full or not
					$sql_query = "SELECT COUNT(*) as number_of_members FROM $dbtables[players]
                  				WHERE $dbtables[players].team = '$whichteam'";
   					$res2 = $db->Execute($sql_query) or die($db->ErrorMsg());
					//$res2=$db->Execute("SELECT number_of_members FROM $dbtables[teams] WHERE id=$whichteam LIMIT 1");
					if (!$res2->EOF) {
						// Find out how many are in the team
						$row=$res2->fields;
						if ($row[number_of_members] <= $max_team_members) {
							// Everything looks ok
							$db->Execute("UPDATE $dbtables[teams] SET number_of_members=number_of_members+1 WHERE id=$whichteam");
  		      				$db->Execute("UPDATE $dbtables[players] SET team=$whichteam,team_invite=0 WHERE player_id=$playerinfo[player_id]");
							echo "$l_team_welcome <B>$team[team_name]</B>.<BR><BR>";
		      				playerlog($playerinfo[player_id], LOG_TEAM_JOIN, decodeHTML($team[team_name]));
		      				playerlog($team[creator], LOG_TEAM_NEWMEMBER, decodeHTML($team[team_name]). "|$playerinfo[character_name]");
						} else {
							// Team is full
							echo "That Alliance is full now! Contact the coordinator to see if they can eject someone or reject the invitation.<br><br>";
						}
					} else {
						echo "That team does not seem to exist any more!<br><br>";
					}
                   }
                   else
                   {
                      echo "$l_team_noinviteto<BR>";
                   }
		}
                LINK_BACK();
                break;
	case 4:
   	/*
   	   Can you comment in english please ??

      	// LEAVE + JOIN - anche per coordinatori - caso speciale ?
      	// mettere nel 2 e senza break -> 3
      	// CREATOR LEAVE - mettere come caso speciale si 3

   	*/
		echo "Not implemented yet. LEAVE+JOIN WE ARE A LAZY BUNCH sorry! :)<BR><BR>";
		LINK_BACK();
		break;

	case 5: // Eject member
    if ($playerinfo[team] == $team[id])
    {
      $who = stripnum($who);
  		$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id=$who");
  		$whotoexpel = $result->fields;
  		if (!$confirmed) {
	  		echo "$l_team_ejectsure $whotoexpel[character_name]? <A HREF=\"$PHP_SELF?teamwhat=$teamwhat&confirmed=1&who=$who\">$l_yes</A> - <a href=\"$PHP_SELF\">$l_no</a><BR>";
  		} else {  
  			/*
	  		   check whether the player we are ejecting might have already left in the meantime
		  	   should go here	if ($whotoexpel[team] ==
			  */
  			$db->Execute("UPDATE $dbtables[planets] SET corp='0' WHERE owner='$who'");
        $db->Execute("UPDATE $dbtables[players] SET team='0' WHERE player_id='$who'");
           /*
              No more necessary due to COUNT(*) in previous SQL statement
  
           	$db->Execute("UPDATE $dbtables[teams] SET number_of_members=number_of_members-1 WHERE id=$whotoexpel[team]");
           */
  			playerlog($who, LOG_TEAM_KICK, decodeHTML($team[team_name]));
	  		echo "$whotoexpel[character_name] $l_team_ejected<BR>";
		  }
  		LINK_BACK();
    }
		break;

	case 6: // Create Team
		if ($testing)
			if($swordfish != $adminpass) {
				echo "<FORM ACTION=\"$PHP_SELF\" METHOD=POST>";
				echo "$l_team_testing<BR><BR>";
				echo "$l_team_pw: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
				echo "<INPUT TYPE=hidden name=teamwhat value=$teamwhat>";
				echo "<INPUT TYPE=SUBMIT VALUE=$l_submit><INPUT TYPE=RESET VALUE=$l_reset>";
				echo "</FORM>";
				echo "<BR><BR>";
				TEXT_GOTOMAIN();
				include("footer.php");
				die();
			}
		if (!$teamname) {
			echo "<FORM ACTION=\"$PHP_SELF\" METHOD=POST>";
			echo "$l_team_entername: ";
			if ($testing)
				echo "<INPUT TYPE=hidden NAME=swordfish value='$swordfish'>";
			echo "<INPUT TYPE=hidden name=teamwhat value=$teamwhat>";
			echo "<INPUT TYPE=TEXT NAME=teamname SIZE=40 MAXLENGTH=40><BR>";
			echo "$l_team_enterdesc: ";
			echo "<INPUT TYPE=TEXT NAME=teamdesc SIZE=40 MAXLENGTH=254><BR>";
			echo "<INPUT TYPE=SUBMIT VALUE=$l_submit><INPUT TYPE=RESET VALUE=$l_reset>";
			echo "</FORM>";
			echo "<BR><BR>";
		} else {
	                $teamname = htmlspecialchars($teamname);
                        $teamdesc = htmlspecialchars($teamdesc);		
                        $res = $db->Execute("INSERT INTO $dbtables[teams] (id,creator,team_name,number_of_members,description) VALUES ('$playerinfo[player_id]','$playerinfo[player_id]','$teamname','1','$teamdesc')");
                        $db->Execute("INSERT INTO $dbtables[zones] VALUES('','$teamname\'s Empire', $playerinfo[player_id], 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 0)");
                        $db->Execute("UPDATE $dbtables[players] SET team='$playerinfo[player_id]' WHERE player_id='$playerinfo[player_id]'");
			echo "$l_team_alliance <B>$teamname</B> $l_team_hcreated.<BR><BR>";
			playerlog($playerinfo[player_id], LOG_TEAM_CREATE, decodeHTML($teamname));
		}
		LINK_BACK();
		break;
	case 7: // INVITE player
		if (!$invited) {
			echo "<FORM ACTION='$PHP_SELF' METHOD=POST>";
			echo "<TABLE><INPUT TYPE=hidden name=teamwhat value=$teamwhat><INPUT TYPE=hidden name=invited value=1><INPUT TYPE=hidden name=whichteam value=$whichteam>";
			echo "<TR><TD>$l_team_selectp:</TD></TR><TR><TD><SELECT NAME=who>";
      $res = $db->Execute("SELECT character_name,player_id FROM $dbtables[players] WHERE team<>$whichteam AND player_id > 1 AND email NOT LIKE '%@furangee' AND turns_used > 1 ORDER BY character_name ASC");
			while(!$res->EOF) {
        $row = $res->fields;
				if ($row[player_id] != $team[creator])
					echo "<OPTION VALUE=$row[player_id]>$row[character_name]";
        $res->MoveNext();
			}
			echo "</SELECT></TD></TR>";
			echo "<TR><TD><INPUT TYPE=SUBMIT VALUE=$l_submit></TD></TR>";
			echo "</TABLE>";
			echo "</FORM>";

		} else {
                        if($playerinfo[team] == $whichteam)
                        {
                   	   $res = $db->Execute("SELECT character_name,team_invite FROM $dbtables[players] WHERE player_id=$who");
  			   $newpl = $res->fields;
			   if ($newpl[team_invite]) 
                           {
			      $l_team_isorry = str_replace("[name]", $newpl[character_name], $l_team_isorry);
			      echo "$l_team_isorry<BR><BR>";
			   }
                           else 
                           {
			      $db->Execute("UPDATE $dbtables[players] SET team_invite=$whichteam WHERE player_id=$who");
			      echo("$l_team_plinvted<BR>");
			      playerlog($who,LOG_TEAM_INVITE, decodeHTML($team[team_name]));
			   }
                        }
                        else
                        {
                           echo "$l_team_notyours<BR>";
                        }
		}
		echo "<BR><BR><a href=$PHP_SELF?&kk=".date("U").">$l_clickme</a> $l_team_menu<BR><BR>";
		break;
	case 8: // REFUSE invitation
		echo "$l_team_refuse <B>$invite_info[team_name]</B>.<BR><BR>";
		$db->Execute("UPDATE $dbtables[players] SET team_invite=0 WHERE player_id=$playerinfo[player_id]");
		playerlog($team[creator], LOG_TEAM_REJECT, "$playerinfo[character_name]|".decodeHTML($invite_info[team_name]));
		LINK_BACK();
		break;
	case 9: // Edit Team
		if ($testing){
			if($swordfish != $adminpass) {
				echo "<FORM ACTION=\"$PHP_SELF\" METHOD=POST>";
				echo "$l_team_testing<BR><BR>";
				echo "$l_team_pw: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
				echo "<INPUT TYPE=hidden name=teamwhat value=$teamwhat>";
				echo "<INPUT TYPE=SUBMIT VALUE=$l_submit><INPUT TYPE=RESET VALUE=$l_reset>";
				echo "</FORM>";
				echo "<BR><BR>";
				TEXT_GOTOMAIN();
				include("footer.php");
				die();
			}
	   }
	   if ($playerinfo[team] == $whichteam) {
   		if (!$update) {
   			echo "<FORM ACTION=\"$PHP_SELF\" METHOD=POST>";
   			echo "$l_team_edname: <BR>";
   			echo "<INPUT TYPE=hidden NAME=swordfish value='$swordfish'>";
   			echo "<INPUT TYPE=hidden name=teamwhat value=$teamwhat>";
   			echo "<INPUT TYPE=hidden name=whichteam value=$whichteam>";
   			echo "<INPUT TYPE=hidden name=update value=true>";
   			echo "<INPUT TYPE=TEXT NAME=teamname SIZE=40 MAXLENGTH=40 VALUE=\"".$team[team_name]."\"><BR>";
   			echo "$l_team_eddesc: <BR>";
   			echo "<INPUT TYPE=TEXT NAME=teamdesc SIZE=40 MAXLENGTH=254 VALUE=\"".$team[description]."\"><BR>";
   			echo "<INPUT TYPE=SUBMIT VALUE=$l_submit><INPUT TYPE=RESET VALUE=$l_reset>";
   			echo "</FORM>";
   			echo "<BR><BR>";
   		} else {
   		        $teamname = htmlspecialchars($teamname);
                        $teamdesc = htmlspecialchars($teamdesc);
   	                $res = $db->Execute("UPDATE $dbtables[teams] SET team_name='$teamname', description='$teamdesc' WHERE id=$whichteam") or die("<font color=red>error: " . $db->ErrorMSG() . "</font>");
   			echo "$l_team_alliance <B>$teamname</B> $l_team_hasbeenr<BR><BR>";
   			/*
   			   Adding a log entry to all members of the renamed alliance
   			*/
   		   $result_team_name = $db->Execute("SELECT player_id FROM $dbtables[players] WHERE team=$whichteam AND player_id<>$playerinfo[player_id]") or die("<font color=red>error: " . $db->ErrorMsg() . "</font>");
   			playerlog($playerinfo[player_id], LOG_TEAM_RENAME, decodeHTML($teamname));
   			while(!$result_team_name->EOF) {
          $teamname_array = $result_team_name->fields;
   			   playerlog($teamname_array[player_id], LOG_TEAM_M_RENAME, decodeHTML($teamname));
                           $result_team_name->MoveNext();
            }
     		}
   		LINK_BACK();
   		break;
	   }
	   else
	   {
   		echo $l_team_error;
   		LINK_BACK();
   		break;
	   }
	case 10: // Retract Invite
    if ($playerinfo[team] == $team[id])
    {
      $who = stripnum($who);
  	  $result = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id=$who");
  		$whotoexpel = $result->fields;
        $db->Execute("UPDATE $dbtables[players] SET team_invite='0' WHERE player_id='$who'");
	    echo "Invitiation to $whotoexpel[character_name] retracted.<BR>";
  		LINK_BACK();
    }
		break;


	default:
		if (!$playerinfo[team]) {
			echo "$l_team_notmember";
			DISPLAY_INVITE_INFO();
		} else {
			if ($playerinfo[team] < 0) {
				$playerinfo[team] = -$playerinfo[team];
				$result = $db->Execute("SELECT * FROM $dbtables[teams] WHERE id=$playerinfo[team]");
				$whichteam = $result->fields;
				echo "$l_team_urejected <B>$whichteam[team_name]</B><BR><BR>";
 				LINK_BACK();
				break;
			}
			$result = $db->Execute("SELECT * FROM $dbtables[teams] WHERE id=$playerinfo[team]");
			$whichteam = $result->fields;;
			if ($playerinfo[team_invite]) {
				$result = $db->Execute("SELECT * FROM $dbtables[teams] WHERE id=$playerinfo[team_invite]");
				$whichinvitingteam = $result->fields;
			}
			$isowner = $playerinfo[player_id] == $whichteam[creator];
			showinfo($playerinfo[team],$isowner);
		}
		$res= $db->Execute("SELECT COUNT(*) as TOTAL FROM $dbtables[teams]");
		$num_res = $res->fields;
		if ($num_res[TOTAL] > 0) {
         DISPLAY_ALL_ALLIANCES();
		} else {
			echo "$l_team_noalliances<BR><BR>";
		}
	break;
} // switch ($teamwhat)

	echo "<BR><BR>";
	TEXT_GOTOMAIN();

	include("footer.php");
?>

