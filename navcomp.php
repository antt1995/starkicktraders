<?

	include("config.php");
	updatecookie();

  include("languages/$lang");

	$title=$l_nav_title;

	include("header.php");
	connectdb();

	if(checklogin())
  {
    die();
  }

  bigtitle();

  if(!$allow_navcomp)
  {
    echo "$l_nav_nocomp<BR><BR>";
    TEXT_GOTOMAIN();
	  include("footer.php");
    die();
  }
	$result = $db->Execute ("SELECT $dbtables[players].sector,computer FROM $dbtables[players],$dbtables[ships] WHERE email='$username' AND $dbtables[ships].player_id=$dbtables[players].player_id AND $dbtables[ships].ship_id=$dbtables[players].currentship");
	$playerinfo=$result->fields;
	$current_sector = $playerinfo['sector'];
	$computer_tech  = $playerinfo['computer'];

	$result2 = $db->Execute ("SELECT * FROM $dbtables[universe] WHERE sector_id='$current_sector'");
	$sectorinfo=$result2->fields;

	if ($state == 0)
	{
		echo "The Navigation Computer can always find a way back to Sector 0 (Sol).<br>";
		echo "<FORM ACTION=\"navcomp.php\" METHOD=POST>";
		echo "$l_nav_query <INPUT NAME=\"stop_sector\">&nbsp;<INPUT TYPE=SUBMIT VALUE=$l_submit><BR>\n";
		echo "<INPUT NAME=\"state\" VALUE=1 TYPE=HIDDEN>";
		echo "</FORM>\n";
	}
	elseif ($state == 1)
	{
		if ($stop_sector == 0) {
			$max_search_depth = 8;
		} elseif ($computer_tech < 1)
		{
			$max_search_depth = 2;
		}
		elseif ($computer_tech < 2)
		{
			$max_search_depth = 3;
		}
		elseif ($computer_tech < 3)
		{
			$max_search_depth = 4;
		}
		elseif ($computer_tech < 4)
		{
			$max_search_depth = 5;
		}
		else
		{
			$max_search_depth = 6;
		}
		for ($search_depth = 1; $search_depth <= $max_search_depth; $search_depth++)
		{
			$search_query = "SELECT	distinct\n	a1.link_start\n	,a1.link_dest \n";
			for ($i = 2; $i<=$search_depth;$i++)
			{
				$search_query = $search_query . "	,a". $i . ".link_dest \n";
			}
			$search_query = $search_query . "FROM\n	 $dbtables[links] AS a1 \n";

			for ($i = 2; $i<=$search_depth;$i++)
			{
				$search_query = $search_query . "	,$dbtables[links] AS a". $i . " \n";
			}
			$search_query = $search_query . "WHERE \n	    a1.link_start = $current_sector \n";

			for ($i = 2; $i<=$search_depth; $i++)
			{
				$k = $i-1;
				$search_query = $search_query . "	AND a" . $k . ".link_dest = a" . $i . ".link_start \n";
			}
			$search_query = $search_query . "	AND a" . $search_depth . ".link_dest = $stop_sector \n";
			$search_query = $search_query . "	AND a1.link_dest != a1.link_start \n";
			for ($i=2; $i<=$search_depth;$i++)
			{
				$search_query = $search_query . "	AND a" . $i . ".link_dest not in (a1.link_dest, a1.link_start ";

				for ($j=2; $j<$i;$j++)
				{
					$search_query = $search_query . ",a".$j.".link_dest ";
				}
				$search_query = $search_query . ")\n";
			}
			$search_query = $search_query . "ORDER BY a1.link_start, a1.link_dest ";
			for ($i=2;$i<=$search_depth;$i++)
			{
				$search_query = $search_query . ", a" . $i . ".link_dest";
			}
			$search_query = $search_query . " \nLIMIT 1";
			//echo "$search_query\n\n";
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
			$search_result = $db->Execute ($search_query) or die ("Invalid Query");
			$found = $search_result->RecordCount();
			if ($found > 0)
			{
				break;
			}


		}
		if ($found > 0)
		{
			echo "<H3>$l_nav_pathfnd</H3>\n";
      $links=$search_result->fields;
			echo $links[0];
			for ($i=1;$i<$search_depth+1;$i++)
			{
				echo " >> " . $links[$i];
			}
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
			echo "<BR><BR>";
			echo "$l_nav_answ1 $search_depth $l_nav_answ2<BR><BR>";
		}
		else if ($stop_sector == "0") {
			echo "A temporary wormhole appears to Sector 0....<br><br>";
			echo "<a href=escape.php?kk=".time("U").">Take wormhole now!</a><br><br>(There is no known warp route to Sol from this sector so you better take it!)<br><br>";			
		}
		else
		{
			echo "$l_nav_proper<BR><BR>";
		}
	}

    TEXT_GOTOMAIN();
	include("footer.php");

?>
