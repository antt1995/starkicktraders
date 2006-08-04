<?

	include("config.php");
	updatecookie();

  include("languages/$lang");

	$title="Sol Wormhole";

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
	$result = $db->Execute ("SELECT player_id, sector,turns,currentship FROM $dbtables[players] WHERE email='$username'");
	$playerinfo=$result->fields;
	if($playerinfo[turns] < 1)
  	{
		echo "You need at least one turn to get down the wormhole! Wait a minute or two until you get some more turns.<BR><BR>";
  	}
  	else
  	{
		$result = $db->Execute ("SELECT stow FROM $dbtables[ships] WHERE ship_id=$playerinfo[currentship]");
		$shipinfo=$result->fields;
		$current_sector = $playerinfo[sector];
		$result2 = $db->Execute ("SELECT * FROM $dbtables[universe] WHERE sector_id='$current_sector'");
		$sectorinfo=$result2->fields;
		$stop_sector = 0;
		$max_search_depth = 8;
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
			$result_warp = $db->Execute ("UPDATE $dbtables[players] SET turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
			echo "<H3>$l_nav_pathfnd</H3>\n";
			echo "There is no wormhole here now!<br><br>";
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
		else if ($search_query == 0) {
			echo "You zoom through the wormhole to Sector 0....<br><br>";
			$dest_sector=0;
			$result_warp = $db->Execute ("UPDATE $dbtables[players] SET sector=$dest_sector, turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
			$result_warp = $db->Execute ("UPDATE $dbtables[ships] SET sector=$dest_sector WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
			$result_warp = $db->Execute ("UPDATE $dbtables[ships] SET sector=$dest_sector WHERE player_id=$playerinfo[player_id] AND ship_id=$shipinfo[tow]");
        	log_move($playerinfo[player_id],$dest_sector);						
		}
		else
		{
			echo "$l_nav_proper<BR><BR>";
		}
	}

    TEXT_GOTOMAIN();
	include("footer.php");

?>
