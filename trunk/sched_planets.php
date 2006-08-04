<?

  if (preg_match("/sched_planets.php/i", $PHP_SELF)) {
      echo "You can not access this file directly!";
      die();
  }

  $expoprod = mypw($colonist_reproduction_rate + 1, $multiplier);
  $expoprod *=$multiplier;

  $expocreds = mypw($interest_rate, $multiplier);
  $tech_prate = 0.02;
  echo "<B>PLANETS</B><p>";
  $temp_time=time();
  $planetupdate = "UPDATE $dbtables[planets] SET " .
    "organics=organics + GREATEST(((LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $organics_prate * prod_organics / 100.0 * $expoprod) - LEAST(colonists, $colonist_limit) * $colonist_production_rate * $organics_consumption * $expoprod,0)," .
    "ore=ore + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $ore_prate * prod_ore / 100.0 * $expoprod," .
    "goods=goods + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $goods_prate * prod_goods / 100.0 * $expoprod," .
    "energy=energy + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $energy_prate * prod_energy / 100.0 * $expoprod," .
    "colonists=LEAST(colonists + (colonists - (colonists * $starvation_death_rate)) * $colonist_reproduction_rate * $expoprod, $colonist_limit)," .
	"credits=credits * $expocreds + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $credits_prate * (100.0 - prod_organics - prod_ore - prod_goods - prod_energy - prod_fighters - prod_torp) / 100.0 * $expoprod," .
    "tech_level=tech_level + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $tech_prate * (100.0 - prod_organics - prod_ore - prod_goods - prod_energy - prod_fighters - prod_torp) / 100.0 * $expoprod";
	echo $planetupdate."<br>";
/*
$planetupdate = "UPDATE $dbtables[planets] SET " .
    "organics=organics + GREATEST(((LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $organics_prate * prod_organics / 100.0 * $expoprod) - LEAST(colonists, $colonist_limit) * $colonist_production_rate * $organics_consumption * $expoprod,0)," .
    "ore=ore + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $ore_prate * prod_ore / 100.0 * $expoprod," .
    "goods=goods + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $goods_prate * prod_goods / 100.0 * $expoprod," .
    "energy=energy + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $energy_prate * prod_energy / 100.0 * $expoprod," .
    "colonists=LEAST(colonists + (colonists - (colonists * $starvation_death_rate)) * $colonist_reproduction_rate * $expoprod, $colonist_limit)," .
    "credits=credits * $expocreds + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $credits_prate * (100.0 - prod_organics - prod_ore - prod_goods - prod_energy - prod_fighters - prod_torp) / 100.0 * $expoprod";
	*/
  $db->Execute($planetupdate);

  $planetupdate = "UPDATE $dbtables[planets] SET " .
    "fighters=fighters + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $fighter_prate * prod_fighters / 100.0 * $expoprod," .
    "torps=torps + (LEAST(colonists, $colonist_limit) * $colonist_production_rate) * $torpedo_prate * prod_torp / 100.0 * $expoprod " .
    "WHERE owner!=0";

  $db->Execute($planetupdate);
  
  // Now we need to build any ships that need building!
  $res=$db->Execute("UPDATE $dbtables[ships] SET ship_colonists=ship_colonists+1 WHERE ship_colonists < 0 AND on_planet='Y' AND ship_destroyed = 'N'");
  
  
  
  $multiplier = 0;
  $temp_runtime= time() - $temp_time;
  echo "<p>Planets took $temp_runtime seconds to execute.<p>";

  echo "Planets updated.<BR><BR>";
  echo "<BR>";

?>

