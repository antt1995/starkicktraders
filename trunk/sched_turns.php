<?

  if (preg_match("/sched_turns.php/i", $PHP_SELF)) {
      echo "You can not access this file directly!";
      die();
  }

  echo "<B>TURNS</B><BR><BR>";
  echo "Adding turns...";
  QUERYOK($db->Execute("UPDATE $dbtables[players] SET turns=turns+(2*$multiplier) WHERE turns<$max_turns"));
  echo "Ensuring maximum turns are $max_turns...";
  QUERYOK($db->Execute("UPDATE $dbtables[players] SET turns=$max_turns WHERE turns>$max_turns"));
  echo "<BR>";
  $multiplier = 0;

?>
