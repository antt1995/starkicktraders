<?

  if (preg_match("/sched_apocalypse.php/i", $PHP_SELF)) {
      echo "You can not access this file directly!";
      die();
  }
  $temp_time=time();
  echo "<B>PLANETARY APOCALYPSE</B><BR><BR>";
  echo "The four horsemen of the apocalypse set forth...<BR>";
  $doomsday = $db->Execute("SELECT * from $dbtables[planets] WHERE colonists > ($doomsday_value-1000)");
  $chance = 9;
  $reccount = $doomsday->RecordCount();
  if($reccount > 50) $chance = 7; // increase chance it will happen if we have lots of planets meeting the criteria 
  $affliction = rand(1,$chance); // the chance something bad will happen
  if($doomsday && $affliction < 3 && $reccount > 0)
  {
     $i=1;
     $targetnum=rand(1,$reccount);
     while (!$doomsday->EOF)
     {
        if ($i==$targetnum)
        {
           $targetinfo=$doomsday->fields;
           break;
        }
        $i++;
        $doomsday->MoveNext();
     }
     if($affliction == 1) // Space Plague
     {
        echo "The horsmen release the Space Plague!<BR>.";
        $db->Execute("UPDATE $dbtables[planets] SET colonists = ROUND(colonists-colonists*$space_plague_kills) WHERE planet_id = $targetinfo[planet_id]");
        $logpercent = ROUND($space_plague_kills * 100);
        playerlog($targetinfo[owner],LOG_SPACE_PLAGUE,"$targetinfo[name]|$targetinfo[sector_id]|$logpercent"); 
		$name = get_player_name($targetinfo[owner]);
        $headline="Travel Advisory";
		$news = addslashes("The Universe Health Organization warned visitors to refrain from visiting $targetinfo[name] due to an outbreak of Space Plague. Millions are reported dead. The plague was blamed on colonist overcrowding by $name. Shame!");
   		$db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$news','$targetinfo[owner]',NOW(), 'plague')");
     }
     else
     {
        echo "The horsemen release a Plasma Storm!<BR>.";
        $db->Execute("UPDATE $dbtables[planets] SET energy = 0 WHERE planet_id = $targetinfo[planet_id]");
        playerlog($targetinfo[owner],LOG_PLASMA_STORM,"$targetinfo[name]|$targetinfo[sector_id]");
		$name = get_player_name($targetinfo[owner]);
        $headline="Weather Report";
		$news = addslashes("A plasma storm struck $name's sector today. No casualties were reported.");
   		$db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$news','$targetinfo[owner]',NOW(), 'plasma')");
     } 
  }
  echo "<BR>";
  $temp_runtime= time() - $temp_time;
  echo "<p>The apocolypse took $temp_runtime seconds to execute.<p>";

?>
