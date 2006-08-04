<?

	include("config.php");
	//updatecookie();

  	include("languages/$lang");
	$title="Map of Ports";
	include("header.php");

	connectdb();

  	if(checklogin())
  	{
    	die();
  	}
	bigtitle();
	if($swordfish != $adminpass)
	{
	  echo "<FORM ACTION=map.php METHOD=POST>";
	  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
	  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
	  echo "</FORM>";
	}
	else
	{
		$tile[special]="S";
		$tile[ore]="O";
		$tile[organics]="o";
		$tile[energy]="E";
		$tile[goods]="G";
		$tile[none]="&nbsp;";
		$tile[unknown] ="<font color=red>X</font>";
		$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
		$playerinfo = $res->fields;
		//$result = $db->Execute ("SELECT distinct $dbtables[movement_log].sector_id, port_type FROM $dbtables[universe],$dbtables[movement_log] WHERE player_id=$playerinfo[player_id] AND $dbtables[universe].sector_id=$dbtables[movement_log].sector_id ORDER BY sector_id ASC");
	$result = $db->Execute ("SELECT distinct $dbtables[movement_log].sector_id, port_type FROM $dbtables[universe],$dbtables[movement_log] WHERE  $dbtables[universe].sector_id=$dbtables[movement_log].sector_id ORDER BY sector_id ASC");
		echo "<TABLE border=1 cellpadding=0 >\n";
		$sec = 0;
		while(!$result->EOF)
		{
			$row = $result->fields;
			$break=($sec+1)%50;
			if ($break==1)
			{
				   echo "<TR><TD><font size=1>$sec</font></TD>";
				}
			if($row[sector_id] == $sec) {         
				$port=$row[port_type];
				$alt = "$row[sector_id] - $row[port_type]";
				$result->Movenext();
				$row = $result->fields;
			} else {
				$port="unknown";
				$alt = "$row[sector_id] - unknown";
			}
			echo "<TD><A HREF=rsmove.php?engage=1&destination=$sec>$tile[$port]</A></TD>";
			//echo "<A HREF=rsmove.php?engage=1&destination=$sec>$tile[$port]</A>";
	
			if ($break==0)
			{
				echo "<TD><font size=1>$sec</font></td></TR>\n";
				//echo " $row[sector_id]<BR>\n";
			}
			$sec++;
		}
		echo "</TABLE>\n";
	}
    echo "<BR><BR>";
	echo "Click <a href=main.php>here</a> to return to main menu.";
	include("footer.php");

?> 

