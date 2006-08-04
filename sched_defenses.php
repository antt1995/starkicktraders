<?
  if (preg_match("/sched_defenses.php/i", $PHP_SELF)) {
      echo "You can not access this file directly!";
      die();
  }
  echo "<B>Sector Defence Cleanup</B><BR><BR>";
  $temp_time = time();
  if(!isset($swordfish) || $swordfish != $adminpass)
    die("Script has not been called properly");

  $db->Execute("DELETE from $dbtables[sector_defence] where quantity <= 0");
  $multiplier = 0; //no use to run this again
  $temp_runtime= time() - $temp_time;
  echo "<p>Defense cleanup took $temp_runtime seconds to execute.<p>";

?>
