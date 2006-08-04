<?

  if (preg_match("/sched_ranking.php/i", $PHP_SELF)) {
      echo "You can not access this file directly!";
      die();
  }
  $temp_time=time();
  echo "<B>RANKING</B><BR><BR>";
  $res = $db->Execute("SELECT player_id FROM $dbtables[players] WHERE turns_used > 100");
  while(!$res->EOF)
  {
    gen_score($res->fields[player_id]);
    $res->MoveNext();
  }
  echo "<BR>";
  $multiplier = 0;
  $temp_runtime= time() - $temp_time;
  echo "<p>Ranking took $temp_runtime seconds to execute.<p>";

?>
