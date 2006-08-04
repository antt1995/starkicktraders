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
	  echo "<FORM ACTION=map2.php METHOD=POST>";
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
		$tile[none]="N";
		$tile[unknown] ="<font color=red>X</font>";
		$tile[ore] = "<img border=0 src=images/ore.gif>";
		$tile[organics] = "<img border=0 src=images/organics.gif>";
		$tile[goods] = "<img border=0 src=images/goods.gif>";
		$tile[energy] = "<img border=0 src=images/energy.gif>";
		
		$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
		$playerinfo = $res->fields;
		$result = $db->Execute ("SELECT distinct sector_id FROM $dbtables[movement_log] WHERE player_id=$playerinfo[player_id] ORDER BY sector_id ASC");
		$result2 = $db->Execute ("SELECT distinct sector_id FROM $dbtables[scan_log] WHERE  player_id=$playerinfo[player_id] ORDER BY sector_id ASC");
		$result3 = $db->Execute("SELECT port_type FROM $dbtables[universe] ORDER BY sector_id ASC");
		if (!$result->EOF) {
			$row = $result->fields;
		}
		if (!$result2->EOF) {
			$row2 = $result2->fields;
		}
		echo "<TABLE border=0 cellpadding=0 >\n";
		for ($sec=0;$sec<5001;$sec++) {
			$sectors = $result3->fields;
			$port=$sectors[port_type];
			$break=($sec+1)%50;
			if ($break==1)
			{
				   echo "<TR><TD><font size=1>$sec</font></TD>";
			}
			if ($row[sector_id] != $sec && $row2[sector_id] != $sec) {
				echo "<TD><A HREF=rsmove.php?engage=1&destination=$sec>&nbsp;</A></TD>";
			} else {
				if ($row[sector_id] == $sec || $row2[sector_id] == $sec) {
					echo "<TD><A HREF=rsmove.php?engage=1&destination=$sec>$tile[$port]</A></TD>";
				}
				if($row[sector_id] == $sec) {				         
					$result->MoveNext();
					$row = $result->fields;
				}
				if($row2[sector_id] == $sec) { 
					$result2->MoveNext();
					$row2 = $result2->fields;
				}
			}	
			if ($break==0)
			{
				echo "<TD><font size=1>$sec</font></td></TR>\n";
				//echo " $row[sector_id]<BR>\n";
			}
			$result3->MoveNext();
		}
		echo "</TABLE>\n";
	}
    echo "<BR><BR>";
	echo "Click <a href=main.php>here</a> to return to main menu.";
	include("footer.php");

?> 

