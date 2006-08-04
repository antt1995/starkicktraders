<?
include("config.php");
include("languages/$lang");

/*
##############################################################################
# Create Universe Script                                                     #
#                                                                            #
# ChangeLog                                                                  #
#  Nov 2, 01 - Wandrer - Rewritten mostly from scratch                       #
##############################################################################
*/

/*
##############################################################################
# Define Functions for this script                                           #
##############################################################################
*/

### Description: Create Benchmark Class

class c_Timer {
   var $t_start = 0;
   var $t_stop = 0;
   var $t_elapsed = 0;

   function start() { $this->t_start = microtime(); }

   function stop()  { $this->t_stop  = microtime(); }

   function elapsed() {
      $start_u = substr($this->t_start,0,10); $start_s = substr($this->t_start,11,10);
      $stop_u  = substr($this->t_stop,0,10);  $stop_s  = substr($this->t_stop,11,10);
      $start_total = doubleval($start_u) + $start_s;
      $stop_total  = doubleval($stop_u) + $stop_s;
      $this->t_elapsed = $stop_total - $start_total;
      return $this->t_elapsed;
   }
}
function createRandomPassword() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);    
	$i = 0;    
	$pass = '' ;
	while ($i <= 7) {        
		$num = rand() % 33;        
		$tmp = substr($chars, $num, 1);        
		$pass = $pass . $tmp;        
		$i++;    
	}    
	return $pass;
}

function PrintFlush($Text="") {
print "$Text";
//flush();
}

### End defining functions.

### Start Timer
$BenchmarkTimer = new c_Timer;
$BenchmarkTimer->start();

### Set timelimit and randomize timer.

// set_time_limit(0); - This causes an error when running in safe_mode, and its a bad thing. 
srand((double)microtime()*1000000);

### Include config files and db scheme.

include("includes/schema.php");

### Update cookie.
updatecookie();

$title="Create Universe";
include("header.php");

### Connect to the database.

connectdb();

### Print Title on Page.

bigtitle();

### Manually set step var if info isn't correct.

if($swordfish != $adminpass) {
$step="0";
}

if($swordfish == $adminpass && $engage == "") {
$step="1";
}

if($swordfish == $adminpass && $engage == "1") {
$step="2";
}

### Main switch statement.

switch ($step) {
// Stage 1, Getting things started
   case "1":
      echo "<form action=create_universe.php method=post>";
      echo "<table>";
      echo "<tr><td><b><u>Base/Planet Setup</u></b></td><td></td></tr>";
      echo "<tr><td>Percent Special</td><td><input type=text name=special size=5 maxlength=5 value=1></td></tr>";
      echo "<tr><td>Percent Ore</td><td><input type=text name=ore size=5 maxlength=5 value=15></td></tr>";
      echo "<tr><td>Percent Organics</td><td><input type=text name=organics size=5 maxlength=5 value=10></td></tr>";
      echo "<tr><td>Percent Goods</td><td><input type=text name=goods size=5 maxlength=5 value=15></td></tr>";
      echo "<tr><td>Percent Energy</td><td><input type=text name=energy size=5 maxlength=5 value=10></td></tr>";
      echo "<tr><td>Percent Empty</td><td>Equal to 100 - total of above.</td></tr>";
      echo "<tr><td>Initial Commodities to Sell<br><td><input type=text name=initscommod size=6 maxlength=6 value=100.00> % of max</td></tr>";
      echo "<tr><td>Initial Commodities to Buy<br><td><input type=text name=initbcommod size=6 maxlength=6 value=100.00> % of max</td></tr>";
      echo "<tr><td><b><u>Sector/Link Setup</u></b></td><td></td></tr>";
      $fedsecs = intval($sector_max / 200);
      $loops = intval($sector_max / 500);
      echo "<tr><td>Number of sectors total (<b>overrides config.php</b>)</td><td><input type=text name=sektors size=5 maxlength=5 value=$sector_max></td></tr>";
      echo "<TR><TD>Number of Federation sectors</TD><TD><INPUT TYPE=TEXT NAME=fedsecs SIZE=6 MAXLENGTH=6 VALUE=$fedsecs></TD></TR>";
      echo "<tr><td>Number of loops</td><td><input type=text name=loops size=6 maxlength=6 value=$loops></td></tr>";
      echo "<tr><td>Percent of sectors with unowned planets</td><td><input type=text name=planets size=5 maxlength=5 value=10></td></tr>";
      echo "<tr><td></td><td><input type=hidden name=engage value=1><input type=hidden name=step value=2><input type=hidden name=swordfish value=$swordfish><input type=submit value=Submit><input type=reset value=Reset></td></tr>";
      echo "</table>";
      echo "</form>";
      break;

// Stage 2, Configuration
   case "2":
      $sector_max = round($sektors);
      if($fedsecs > $sector_max) {
         echo "The number of Federation sectors must be smaller than the size of the universe!";
         break;
      }
      $spp = round($sector_max*$special/100);
      $oep = round($sector_max*$ore/100);
      $ogp = round($sector_max*$organics/100);
      $gop = round($sector_max*$goods/100);
      $enp = round($sector_max*$energy/100);
      $empty = $sector_max-$spp-$oep-$ogp-$gop-$enp;
      $nump = round ($sector_max*$planets/100);
      echo "So you would like your $sector_max sector universe to have:<BR><BR>";
      echo "$spp special ports<BR>";
      echo "$oep ore ports<BR>";
      echo "$ogp organics ports<BR>";
      echo "$gop goods ports<BR>";
      echo "$enp energy ports<BR>";
      echo "$initscommod% initial commodities to sell<BR>";
      echo "$initbcommod% initial commodities to buy<BR>";
      echo "$empty empty sectors<BR>";
      echo "$fedsecs Federation sectors<BR>";
      echo "$loops loops<BR>";
      echo "$nump unowned planets<BR><BR>";
      echo "If this is correct, click confirm - otherwise go back.<BR>";
      echo "<form action=create_universe.php method=post>";
      echo "<input type=hidden name=step value=3>";
      echo "<input type=hidden name=spp value=$spp>";
      echo "<input type=hidden name=oep value=$oep>";
      echo "<input type=hidden name=ogp value=$ogp>";
      echo "<input type=hidden name=gop value=$gop>";
      echo "<input type=hidden name=enp value=$enp>";
      echo "<input type=hidden name=initscommod value=$initscommod>";
      echo "<input type=hidden name=initbcommod value=$initbcommod>";
      echo "<input type=hidden name=nump value=$nump>";
      echo "<INPUT TYPE=HIDDEN NAME=fedsecs VALUE=$fedsecs>";
      echo "<input type=hidden name=loops value=$loops>";
      echo "<input type=hidden name=sektors value=$sector_max>";
      echo "<input type=hidden name=engage value=2>";
      echo "<input type=hidden name=swordfish value=$swordfish>";
      echo "<input type=submit value=Confirm>";
      echo "</form>";
      echo "<BR><BR><FONT COLOR=RED>";
      echo "WARNING: ALL TABLES WILL BE DROPPED AND THE GAME WILL BE RESET WHEN YOU CLICK 'CONFIRM'!<br>";
	  echo "Action will be taken on $db_prefix</FONT>";
      break;

// Stage 3, Out with the old and in with the new
   case "3":
      $sector_max = round($sektors);
      create_schema($dbtables);
      echo "<form action=create_universe.php method=post>";
      echo "<input type=hidden name=step value=4>";
      echo "<input type=hidden name=spp value=$spp>";
      echo "<input type=hidden name=oep value=$oep>";
      echo "<input type=hidden name=ogp value=$ogp>";
      echo "<input type=hidden name=gop value=$gop>";
      echo "<input type=hidden name=enp value=$enp>";
      echo "<input type=hidden name=initscommod value=$initscommod>";
      echo "<input type=hidden name=initbcommod value=$initbcommod>";
      echo "<input type=hidden name=nump value=$nump>";
      echo "<INPUT TYPE=HIDDEN NAME=fedsecs VALUE=$fedsecs>";
      echo "<input type=hidden name=loops value=$loops>";
      echo "<input type=hidden name=sektors value=$sector_max>";
      echo "<input type=hidden name=engage value=2>";
      echo "<input type=hidden name=swordfish value=$swordfish>";
      echo "<input type=submit value=Confirm>";
      echo "</form>";
      break;

// Stage 4, Galaxies-R-Us
   case "4":
      $sector_max = round($sektors);
// Build the zones table. Only four zones here. The rest are named after players for
// when they manage to dominate a sector.
      print("Building zone descriptions ");
      $replace = $db->Execute("REPLACE INTO $dbtables[zones](zone_id, zone_name, owner, corp_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) VALUES ('1', 'Unchartered space', 0, 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', '0' )");
      $replace = $db->Execute("REPLACE INTO $dbtables[zones](zone_id, zone_name, owner, corp_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) VALUES ('2', 'Federation space', 0, 'N', 'N', 'N', 'N', 'N', 'N',  'Y', 'N', '$fed_max_hull')");
      $replace = $db->Execute("REPLACE INTO $dbtables[zones](zone_id, zone_name, owner, corp_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) VALUES ('3', 'Free-Trade space', 0, 'N', 'N', 'Y', 'N', 'N', 'N','Y', 'N', '0')");
      $replace = $db->Execute("REPLACE INTO $dbtables[zones](zone_id, zone_name, owner, corp_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) VALUES ('4', 'War Zone', 0, 'N', 'Y', 'Y', 'Y', 'Y', 'Y','N', 'Y', '0')");
	  $replace = $db->Execute("REPLACE INTO $dbtables[zones](zone_id, zone_name, owner, corp_zone, allow_beacon, allow_attack, allow_planetattack, allow_warpedit, allow_planet, allow_trade, allow_defenses, max_hull) VALUES ('5', 'Asteroid Belt', 0, 'N', 'Y', 'Y', 'Y', 'Y', 'N', 'Y', 'N', '0')");
      $update = $db->Execute("UPDATE $dbtables[universe] SET zone_id='2' WHERE sector_id<$fedsecs");
      print("");
      PrintFlush("- completed successfully.<BR>");

// Setup some need values for product amounts
      $initsore = $ore_limit * $initscommod / 100.0;
      $initsorganics = $organics_limit * $initscommod / 100.0;
      $initsgoods = $goods_limit * $initscommod / 100.0;
      $initsenergy = $energy_limit * $initscommod / 100.0;
      $initbore = $ore_limit * $initbcommod / 100.0;
      $initborganics = $organics_limit * $initbcommod / 100.0;
      $initbgoods = $goods_limit * $initbcommod / 100.0;
      $initbenergy = $energy_limit * $initbcommod / 100.0;

// Build Sector 0, Sol
      print("Creating sector 0 - Sol ");
      $sector = array();
      $sector[0] = array('sector_id' => '0',
                         'sector_name' => 'Sol',
                         'zone_id' => '2',
                         'port_type' => 'special',
                         'port_organics' => '0',
                         'port_ore' => '0',
                         'port_goods' => '0',
                         'port_energy' => '0',
                         'beacon' => 'Sol: Hub of the Universe',
                         'x' => '0',
                         'y' => '0',
                         'z' => '0',
						 'tech_level' => '0');
      print("");
      PrintFlush("- completed successfully.<BR>");

// Build Sector 1, Alpha Centauri
      print("Creating sector 1 - Alpha Centauri ");
      $sector[1] = array('sector_id' => '1',
                         'sector_name' => 'Alpha Centari',
                         'zone_id' => '2',
                         'port_type' => 'energy',
                         'port_organics' => $initborganics,
                         'port_ore' => $initbore,
                         'port_goods' => $initbgoods,
                         'port_energy' => $initsenergy,
                         'beacon' => 'Alpha Centari: Gateway to the Galaxy',
                         'x' => '0',
                         'y' => '0',
                         'z' => '1',
						 'tech_level' => '1');
      print("");
      PrintFlush("- completed successfully.<BR>");

// Here's where the remaining sectors get built
      print("Creating remaining ".($sector_max-2)." sectors ");
      $collisions=0;
      for($i=2; $i<=$sector_max; $i++) {
        $sector[$i]= array('sector_id' => "$i");
        $collision = FALSE;
        while(TRUE) {
          // Lot of shortcuts here. Basically we generate a spherical coordinate and convert it to cartesian.
          // Why? Cause random spherical coordinates tend to be denser towards the center.
          // Should really be like a spiral arm galaxy but this'll do for now.
          $radius = rand(100,$universe_size*100)/100;

          $temp_a = deg2rad(rand(0,36000)/100-180);
          $temp_b = deg2rad(rand(0,18000)/100-90);
          $temp_c = $radius*sin($temp_b);

          $sector[$i]['x'] = round(cos($temp_a)*$temp_c);
          $sector[$i]['y'] = round(sin($temp_a)*$temp_c);
          $sector[$i]['z'] = round($radius*cos($temp_b));

          // Collision check
          if(isset($index[$sector[$i]['x'].','.$sector[$i]['y'].','.$sector[$i]['z']])) {
            $collisions++;
          } else {
            break;
         }
      }

        $index[$sector[$i]['x'].','.$sector[$i]['y'].','.$sector[$i]['z']]=&$sector[$i];

        // The Federation owns the first series of sectors. Logical because they
        // probably numbered them as they were found.
        if($i<$fedsecs) {
          $sector[$i]['zone_id'] = '2'; // Federation space
            } else {
          $sector[$i]['zone_id'] = '1'; // Uncharted
         }
      }
      if($collisions) {
        print("- $collisions sector collisions repaired ");
            } else {
        print("- no sector collisions detected ");
            }
      PrintFlush("- completed successfully.<BR>");


// Locations are mapped out so now we need ports.
      $shuffled = array();
      print "Preparing for port placement ";
      // Build up an array of references for conveniece
      for($i=0; $i<=$sector_max; $i++) {
        $shuffled[$i] = &$sector[$i];
      }

      // Give it a really good shuffling. Once isn't enough, the sectors that get
      // ports will tend to be packed at the high end. Five seems to give a good,
      // even distribution.
      for($i=0;$i<5;$i++){
        shuffle($shuffled);
      }
      print("");
      PrintFlush("- preperations completed successfully.<br>");

      // Now we have two indexes, one normal and one referencing the array randomly.
      // This makes port placement easier because they can be added sequentually
      // using the shuffled reference array.

      // Place the special ports
      print "Placing $spp special ports ";
      for($i=0, $max = $spp; $i<$max; $i++) {
        if(isset($shuffled[$i]['port_type'])) {
          $max++;
          continue;
         }
        $shuffled[$i]['zone_id'] = '3';
        $shuffled[$i]['port_type'] = 'special';
		$shuffled[$i]['tech_level'] = $i%6; 
      }
      print("");
      PrintFlush("- completed successfully.<br>");

      // Place the ore ports
      print "Placing $oep ore ports ";
      // $max += $oep-1; because Sol is an special port and counts towards the total.
      for($max += $oep-1; $i<$max; $i++) {
        if(isset($shuffled[$i]['port_type'])) {
          $max++;
          continue;
        }
        $shuffled[$i]['port_type'] = 'ore';
        $shuffled[$i]['port_ore'] = $initsore;
        $shuffled[$i]['port_organics'] = $initborganics;
        $shuffled[$i]['port_goods'] = $initbgoods;
        $shuffled[$i]['port_energy'] = $initbenergy;
		$shuffled[$i]['tech_level'] = $i%6;
      }
      print("");
      PrintFlush("- completed successfully.<br>");

      // Place the organics ports
      print "Placing $ogp organics ports ";
      for($max += $ogp; $i<$max; $i++) {
        if(isset($shuffled[$i]['port_type'])) {
          $max++;
          continue;
        }
        $shuffled[$i]['port_type'] = 'organics';
        $shuffled[$i]['port_ore'] = $initbore;
        $shuffled[$i]['port_organics'] = $initsorganics;
        $shuffled[$i]['port_goods'] = $initbgoods;
        $shuffled[$i]['port_energy'] = $initbenergy;
		$shuffled[$i]['tech_level'] = $i%6;
            }
      print("");
      PrintFlush("- completed successfully.<br>");

      // Place the goods ports
      print "Placing $gop goods ports ";
      for($max += $gop; $i<$max; $i++) {
        if(isset($shuffled[$i]['port_type'])) {
          $max++;
          continue;
        }
        $shuffled[$i]['port_type'] = 'goods';
        $shuffled[$i]['port_ore'] = $initbore;
        $shuffled[$i]['port_organics'] = $initborganics;
        $shuffled[$i]['port_goods'] = $initsgoods;
        $shuffled[$i]['port_energy'] = $initbenergy;
		$shuffled[$i]['tech_level'] = $i%6;
         }
      print("");
      PrintFlush("- completed successfully.<br>");

      // Place the energy ports
      print "Placing $enp energy ports ";
      // $max += $enp-1; because Alpha Centari is an energy port and counts towards the total.
      for($max += $enp-1; $i<$max; $i++) {
        if(isset($shuffled[$i]['port_type'])) {
          $max++;
          continue;
        }
        $shuffled[$i]['port_type'] = 'energy';
        $shuffled[$i]['port_ore'] = $initbore;
        $shuffled[$i]['port_organics'] = $initborganics;
        $shuffled[$i]['port_goods'] = $initbgoods;
        $shuffled[$i]['port_energy'] = $initsenergy;
		$shuffled[$i]['tech_level'] = $i%6;
      }
      print("");
      PrintFlush("- completed successfully.<br>");

      // Now we wrap the whole thing up and stuff it into the database.
      print "Transferring universe data to database ";
      for($i=0; $i<=$sector_max; $i++){
        // Every 500 (and zero) we send it off to be processed.
        if($i%500 == 0) {
          // Don't want to handle zero here, we have to do something special
          // with it anyway
          if($i) {
            $insert = substr_replace($insert, ";", -2);
            $results = $db->Execute($insert);
            $insert=str_replace("\n","<br>",$insert);
//            print "<br>".$insert."<br>";
            PrintFlush($db->ErrorMsg());
            }
          // Set things up for the next batch
          $insert = "INSERT INTO $dbtables[universe] (sector_id,sector_name,zone_id,port_type,".
            "port_organics,port_ore,port_goods,port_energy,beacon,x,y,z,tech_level) VALUES \n";
        }

        // Add a sector to the current batch
        $insert .= "('".$sector[$i]['sector_id']."',".
                   (isset($sector[$i]['sector_name'])?"'".$sector[$i]['sector_name']."'":"NULL").",".
                   (isset($sector[$i]['zone_id'])?$sector[$i]['zone_id']:"").",".
                   (isset($sector[$i]['port_type'])?"'".$sector[$i]['port_type']."'":"'none'").",".
                   (isset($sector[$i]['port_organics'])?(
                     $sector[$i]['port_organics'].",".
                     $sector[$i]['port_ore'].",".
                     $sector[$i]['port_goods'].",".
                     $sector[$i]['port_energy']):"0,0,0,0").",".
                   (isset($sector[$i]['beacon'])?"'".$sector[$i]['beacon']."'":"NULL").",".
                   $sector[$i]['x'].",".
                   $sector[$i]['y'].",".
                   $sector[$i]['z'].",".
				   (isset($sector[$i]['tech_level'])?"'".$sector[$i]['tech_level']."'":"NULL")."),\n";

        // Handle zero specially here
        if(!$i) {
          // Stick it in the database all by itself
          $insert = substr_replace($insert, ";", -2);
          $results = $db->Execute($insert);
          PrintFlush($db->ErrorMsg());

          // Darn it, MySQL insists on reindexing record zero to record one
          // so we change it back.
          $update = "UPDATE $dbtables[universe] SET sector_id=0 WHERE sector_id=1;";
          $results = $db->Execute($update);
          PrintFlush($db->ErrorMsg());

          // Set things up for the next batch
          $insert = "INSERT INTO $dbtables[universe] (sector_id,sector_name,zone_id,port_type,".
            "port_organics,port_ore,port_goods,port_energy,beacon,x,y,z,tech_level) VALUES \n";
         }
      }
      // There will always be at least one sector left over so it's
      // taken care of here.
      $insert = substr_replace($insert, ";", -2);
      $results = $db->Execute($insert);
      PrintFlush($db->ErrorMsg());
      print("");
      PrintFlush("- completed successfully.<br>");


      // build a form for the next stage
      echo "<form action=create_universe.php method=post>";
      echo "<input type=hidden name=step value=5>";
      echo "<input type=hidden name=spp value=$spp>";
      echo "<input type=hidden name=oep value=$oep>";
      echo "<input type=hidden name=ogp value=$ogp>";
      echo "<input type=hidden name=gop value=$gop>";
      echo "<input type=hidden name=enp value=$enp>";
      echo "<input type=hidden name=initscommod value=$initscommod>";
      echo "<input type=hidden name=initbcommod value=$initbcommod>";
      echo "<input type=hidden name=nump value=$nump>";
      echo "<INPUT TYPE=HIDDEN NAME=fedsecs VALUE=$fedsecs>";
      echo "<input type=hidden name=loops value=$loops>";
      echo "<input type=hidden name=sektors value=$sector_max>";
      echo "<input type=hidden name=engage value=2>";
      echo "<input type=hidden name=swordfish value=$swordfish>";
      echo "<input type=submit value=Confirm>";
      echo "</form>";
      break;

// Stage 5, Planets-R-Us
   case "5":
      $sector_max = round($sektors);

      PrintFlush("Creating $nump planets ");

      $results = $db->Execute("SELECT $dbtables[universe].sector_id ".
                              "FROM $dbtables[universe], $dbtables[zones] ".
                              "WHERE $dbtables[zones].zone_id=$dbtables[universe].zone_id ".
                                "AND $dbtables[zones].allow_planet='N'");
      if(!$results) die("DB error while gathering 'No planet' zones");

      $blocked = array();

      while (!$results->EOF) {
        $blocked[$results->fields['sector_id']] = 1;
        $results->MoveNext();
      }

      for($i=0; $i<$nump; $i++) {
        $n = rand(0,$sector_max-1);
        if($blocked[$n]) {
          $i--;
          continue;
        }
        if(!$i%500) {
          if($i) {
            $insert = substr_replace($insert, ";", -2);
            $BenchmarkTimer->pause();
            $results = $db->Execute($insert);
            $BenchmarkTimer->resume();
            if(!$results) {
         PrintFlush($db->ErrorMsg());
              print "<pre>";
              print_r($insert);
              PrintFlush("</pre>");
              die("DB error while placing planets");
            }
          }
          $insert = "INSERT INTO $dbtables[planets] (colonists,owner,corp,prod_ore,prod_organics,prod_goods,".
                    "prod_energy,prod_fighters,prod_torp,sector_id) VALUES\n";
        }
        $insert .= "(2,0,0,$default_prod_ore,$default_prod_organics,$default_prod_goods,".
                   "$default_prod_energy,$default_prod_fighters,$default_prod_torp,$n),\n";
      }
      $insert = substr_replace($insert, ";", -2);
      $results = $db->Execute($insert);
      print "";
      if(!$results) {
         PrintFlush($db->ErrorMsg());
        print "<pre>";
        print_r($insert);
        PrintFlush("</pre>");
        die("DB error while placing planets");
      }
      PrintFlush("- completed.<br>");

      $links=array();
      $hi=-1;
      for($l = 0; $l<$loops; $l++) {
        $lo=$hi+1;
        $hi = round(($sector_max)*($l+1)/$loops)-1;
        echo"Creating warp loop ".($l+1)." of $loops (from sector $lo to $hi)\n";
        for($i=$lo; $i<$hi; $i++) {
          $links[$i][] = $i+1;
          $links[$i+1][] = $i;
        }
        $links[$lo][]=$hi;
        $links[$hi][]=$lo;
        echo "- completed.<br>";
      }


      PrintFlush("Randomly generating $sector_max two-way warps ");
      $dups = 0;
      for($i=0; $i<$sector_max; $i++) {
        do {
          do {
            $x = rand(1,$sector_max-1);
            $y = rand(1,$sector_max-1);
          } while ($x==$y);

          // Only need to check in one direction because only
          // two-way links exist so far.
          $duplicate=FALSE;
          if(isset($links[$x])) {
            foreach($links[$x] as $v) {
              if($y == $v) {
                $duplicate=TRUE;
                $dups++;
                break;
              }
            }
          }
        } while ($duplicate);
        $links[$x][]=$y;
        $links[$y][]=$x;
      }
      PrintFlush("- $dups duplicates prevented - completed.<br>");


      PrintFlush("Randomly generating $sector_max one-way warps ");
      $dups = 0;
      for($i=0; $i<$sector_max; $i++) {
        do {
          do {
            $x = rand(1,$sector_max-1);
            $y = rand(1,$sector_max-1);
          } while ($x==$y);

          $duplicate=FALSE;
          if(isset($links[$x])) {
            foreach($links[$x] as $v) {
              if($y == $v) {
                $duplicate=TRUE;
                $dups++;
                break;
              }
            }
          }
        } while ($duplicate);
        $links[$x][]=$y;
      }
      PrintFlush("- $dups duplicates prevented - completed.<br>");


      PrintFlush("Dumping warps to database ");
      $i = 0;
      foreach($links as $k1 => $v1) {
        foreach($links[$k1] as $k2 => $v2) {
          if(!($i%5000)) {
            if($i) {
              $insert = substr_replace($insert, ";", -2);
              $results = $db->Execute($insert);
              if(!$results) {
                PrintFlush($db->ErrorMsg());
                print "<pre>\n";
                print_r($insert);
                PrintFlush("</pre>");
                die("DB error while placing one-way warps");
              }
            }
            $insert = "INSERT INTO $dbtables[links] (link_start,link_dest) VALUES\n";
          }
          $insert .= "($k1,$v2),\n";
          $i++;
        }
      }
      $insert = substr_replace($insert, ";", -2);
      $results = $db->Execute($insert);
      print "";
      if(!$results) {
        PrintFlush($db->ErrorMsg());
        print "<pre>\n";
        print_r($insert);
        PrintFlush("</pre>");
        die("DB error while inserting links");
      }
      PrintFlush("- completed.<br>");


      echo "<form action=create_universe.php method=post>";
      echo "<input type=hidden name=step value=7>";
      echo "<input type=hidden name=spp value=$spp>";
      echo "<input type=hidden name=oep value=$oep>";
      echo "<input type=hidden name=ogp value=$ogp>";
      echo "<input type=hidden name=gop value=$gop>";
      echo "<input type=hidden name=enp value=$enp>";
      echo "<input type=hidden name=initscommod value=$initscommod>";
      echo "<input type=hidden name=initbcommod value=$initbcommod>";
      echo "<input type=hidden name=nump value=$nump>";
      echo "<INPUT TYPE=HIDDEN NAME=fedsecs VALUE=$fedsecs>";
      echo "<input type=hidden name=loops value=$loops>";
      echo "<input type=hidden name=engage value=2>";
      echo "<input type=hidden name=swordfish value=$swordfish>";
      echo "<input type=submit value=Confirm>";
      echo "</form>";
      break;

// Stage 7, Let there be life
   case "7":
      echo "<B><BR>Configuring game scheduler<BR></B>";

      echo "<BR>Update ticks will occur every $sched_ticks minutes<BR>";
 
      echo "Turns will occur every $sched_turns minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_turns, 0, 'sched_turns.php', '',unix_timestamp(now()))");

      echo "Defenses will be checked every $sched_turns minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_turns, 0, 'sched_defenses.php', '',unix_timestamp(now()))");

      echo "Furangees will play every $sched_turns minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_turns, 0, 'sched_furangee.php', '',unix_timestamp(now()))");

      echo "Interests on IGB accounts will be accumulated every $sched_IGB minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_IGB, 0, 'sched_IGB.php', '',unix_timestamp(now()))");

      echo "News will be generated every $sched_news minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_news, 0, 'sched_news.php', '',unix_timestamp(now()))");

      echo "Planets will generate production every $sched_planets minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_planets, 0, 'sched_planets.php', '',unix_timestamp(now()))");

      echo "Ports will regenerate every $sched_ports minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_ports, 0, 'sched_ports.php', '',unix_timestamp(now()))");

      echo "Ships will be towed from fed sectors every $sched_turns minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_turns, 0, 'sched_tow.php', '',unix_timestamp(now()))");

      echo "Rankings will be generated every $sched_ranking minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_ranking, 0, 'sched_ranking.php', '',unix_timestamp(now()))");

      echo "Sector Defences will degrade every $sched_degrade minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_degrade, 0, 'sched_degrade.php', '',unix_timestamp(now()))");

      echo "The planetary apocalypse will occur every $sched_apocalypse minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_apocalypse, 0, 'sched_apocalypse.php', '',unix_timestamp(now()))");

      echo "Mooring fees will occur every $sched_mooring minutes.<br>";
      $db->Execute("INSERT INTO $dbtables[scheduler] VALUES('', 'Y', 0, $sched_mooring, 0, 'sched_mooring.php', '',unix_timestamp(now()))");

      echo "<B><BR>Configuring ship types<p></B>";

      echo "Inserting ship types"; //starting ship
	  echo "Adding Shuttle";
      $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (1, 'Shuttle', 'shuttle.gif', 'The shuttle ship is fundamentally a science and personnel transport vessel. It is the standard ship issued by the Federation to new colonists departing Earth. This class of ship possesses limited cargo and weapons space. In addition, its short range engines are not suited for long space travel. Hopping along warp lanes is the only viable way of moving through the universe with this ship.', 'Y', 10000, 0, 0, 0, 0, 10, 0, 4, 0, 4, 0, 4, 0, 4, 0, 4, 0, 2, 0, 2, 0, 2, 0, 2, 0, 4, 0)");
	  echo "- Done<br>Adding Stinger";
      $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (2, 'Stinger', 'stinger.jpg', 'This small, yet nimble, specialist ship is a favorite for those who want to sneak about the universe undetected without fear of attack. Its small, lightweight hull allows for tight turning and a good cruise speed. Since most of the ship\'s limited interior space is used by the cloaking systems and engines, it can carry only a minimal cargo. It\'s very small detection signature means that it\'s cloaking capability is particularly effective. A specialist ship for those looking to avoid a fight and have the best chance of getting past sector defences undetected. This ship has the best record on file at the Federation for evading attack. (Best Sneak)', 'Y', 10500000000, 0, 0, 0, 0, 50, 4, 8, 20, 30, 20, 25, 0, 0, 20, 25, 4, 30, 4, 8, 20, 25, 20, 25, 20, 30, 5)");
	  echo "- Done<br>Adding Marauder";
      $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (3, 'Marauder', 'marauder.jpg', 'The marauder is a popular Federation supply ship. It offers reasonable cargo space and can be decently outfitted with enough military techs to offer some resistance from pirates. It is well-liked by Federation officials, who turn its spacious cargo bay into luxuriant living quarters. (Best Upgrade from Useless Trash)', 'Y', 218000, 0, 0, 0, 0, 60, 4, 18, 4, 14, 4, 12, 4, 12, 4, 12, 2, 12, 2, 12, 2, 12, 2, 12, 4, 12, 0)");
	  echo "- Done<br>Adding Katana";
      $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (4, 'Katana', 'katana.jpg', 'The Katana is the most modern intergalactic ship from the Federation. This \"do-it-all\" ship is perfect for the successful space adventurer. The ship combines excellent cargo space with advanced weapons and defense capabilities. Although the ship has no glaring weaknesses, owners should know that very advanced Specialist ships are available that outperform the Katana in certain characteristics. (Best All-Rounder)', 'Y', 215000000, 0, 0, 0, 0, 55, 16, 24, 14, 22, 12, 22, 12, 22, 12, 22, 12, 22, 12, 22, 12, 22, 12, 22, 10, 16, 1)");
	  echo "- Done<br>Adding Destroyer";
      $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (5, 'Destroyer', 'destroyer.gif', 'The Destroyer is the very latest in military muscle that the Federation has to offer. This very large ship has been equiped to the hilt with the most advanced weapons systems in the universe. As a result, this ship has almost no cargo space and is therfore useless for supply and trading. The ship\'s unusually large detection signature is beyond the means of modern cloaking technology to hide. But hey, who needs to hide when you\'re packing this kind of armor? (Best Killer)', 'Y', 14700000000, 0, 0, 0, 0, 120, 1, 2, 20, 25, 20, 30, 20, 25, 20, 25, 20, 30, 20, 30, 20, 30, 20, 30, 0, 0, 4)");
	  echo "- Done<br>Adding Explorer";
      $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (6, 'Explorer', 'explorer.jpg', 'The Explorer is a retired Federation expedition ship that has been largely stripped of its weapon systems and made available to space adventureres for recon. missions. The ship has exceptional ship and planet sensing capability and when called upon, can also launch the most deadly SOFA raids. Since all the available on-board space has been allocated to the ship\'s state-of-the art computers, there is little room for anything else. This ship has the worst record on file at the Federation for being destroyed while it is out patroling the universe. (Best Recon and SOFA)', 'Y', 6310000000, 0, 0, 0, 0, 325, 10, 20, 20, 25, 4, 12, 20, 30, 20, 30, 4, 12, 4, 12, 4, 12, 4, 12, 4, 12, 4)");
	  echo "- Done<br>Adding Transport";
      $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (7, 'Transport', 'transport.jpg', 'This is a specialist transport and supply ship. It can carry a huge amount of cargo but does not have much in the way of military techs.  Strong engines for manouverability and a decent cloaking system are its only real defense.  This ship is favorite target of space pirates and owners often like to have an escort when they utilize it. (Best Trader)', 'Y', 1000000000, 0, 0, 0, 0, 135, 20, 30, 20, 25, 20, 30, 2, 10, 2, 10, 2, 10, 2, 10, 2, 10, 2, 10, 10, 20, 3)");
	  //echo "- Done<br>Adding Training ship";
      //$db->Execute("INSERT INTO $dbtables[ship_types] VALUES (10, 'Evangeleon IV', 'trainingship.gif', 'Training ship', 'N', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6)");
	  echo "- Done<br>Adding Furangee Trader";
	  $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (20, 'Furangee Trader', 'furangeeship.jpg', 'A strange, almost organic hulled ship with a vast hull space.', 'N', 2000000000, 0, 0, 0, 0, 0, 25, 30, 25, 30, 10, 20, 10, 20, 10, 20, 10, 20, 10, 20, 20, 20, 20, 20, 0, 20, 1270000000000)");
	  echo "- Done<br>Adding Beamer";
	  $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (8, 'Beamer', 'beamer.jpg', 'The Beamer is basically a gun with a ship attached to it. Beamers are the tool of choice for asteroid miners and come from the Vega Belt where everyone has one. Equiped with powerful beams, incredible armor plating and extended sensor capability, Beamers can easily survive in the asteroid zones they call home. As a general-purpose craft they loose out on cargo space and other weapon systems. Beamers are used to break rocks up and other ships carry the ore away. Using the Beamer for other purposes than it was designed for invalidates the warranty.', 'N', 308342857, 15214286, 15214286, 30428571, 30428571, 161, 25, 30, 25, 30, 10, 20, 10, 20, 10, 20, 10, 20, 10, 20, 20, 20, 20, 20, 0, 20, 521372)");
	  echo "- Done<br>Adding Cruiser";
	  $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (9, 'Cruiser', 'cruiser.jpg', 'The Cruiser is a Top Secret long-range attack vessel that can use the new tritomian armor plating developed from the recently discovered triple-nucleus alloy forged within red giant suns. This armor plating capability is expensive but puts the Cruiser into the enviable position of being able to take a stunning beating from Destroyers and other vehicles but still be able to deliver an immediate counter-attack.', 'N', 2171428571, 107142857, 107142857, 214285714, 214285714, 1137, 1, 10, 19, 25, 17, 30, 19, 26, 5, 26, 13, 29, 14, 29, 20, 29, 28, 32, 0, 2, 1383577)");
	  echo "- Done<br>Adding Raven";
	  $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (12, 'Raven', 'stinger.jpg', 'This small, yet nimble, specialist ship is a favorite for those who want to sneak about the universe undetected without fear of attack. Its small, lightweight hull allows for tight turning and a good cruise speed. Since most of the ship\'s limited interior space is used by the cloaking systems and engines, it can carry only a minimal cargo. It\'s very small detection signature means that it\'s cloaking capability is particularly effective. A specialist ship for those looking to avoid a fight and have the best chance of getting past sector defences undetected. This ship has the best record on file at the Federation for evading attack.', 'N', 760000000, 37500000, 37500000, 75000000, 75000000, 398, 4, 8, 20, 30, 20, 25, 0, 0, 20, 25, 4, 30, 4, 8, 20, 25, 20, 25, 20, 30, 818535)");
	  echo "- Done<br>Adding Daito";
	$db->Execute("INSERT INTO $dbtables[ship_types] VALUES (14, 'Daito', 'daito.gif', 'The Daito is a great intergalactic ship design from mOdE labs design lab. This ship is perfect for the adventurer and copies a lot from the successful Katana. The ship combines excellent cargo space with advanced weapons and defense capabilities.', 'N', 15561905, 767857, 767857, 1535714, 1535714, 8, 16, 24, 14, 22, 12, 22, 12, 22, 12, 22, 12, 22, 12, 22, 12, 22, 12, 22, 10, 18, 117128)");
	  echo "- Done<br>Adding Hawk";	  
	  $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (15, 'Hawk', 'destroyer.gif', 'The Hawk is a paramilitary muscle machine. This very large ship is built for war with the most advanced weapons systems in the universe. As a result, this ship has almost no cargo space and is therfore useless for supply and trading. The ship\'s unusually large detection signature is beyond the means of modern cloaking technology to hide. But hey, who needs to hide when you\'re packing this kind of armor?', 'N', 1064000000, 52500000, 52500000, 105000000, 105000000, 557, 1, 2, 20, 25, 20, 30, 20, 26, 20, 25, 20, 30, 20, 30, 20, 30, 20, 30, 0, 0, 968504)");
	  echo "- Done<br>Adding B32 Galactic";	  
	  $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (16, 'B32 Galactic', 'explorer.jpg', 'The B32 Galactic is a tried and tested long-range bomber. It can carry a huge fighter complement and has massive engines to make long range attack runs possible and quick. The ship also has exceptional ship and planet sensing capability.', 'N', 456723810, 22535714, 22525714, 45071429, 45071429, 239, 10, 20, 20, 30, 4, 12, 20, 31, 20, 30, 4, 12, 4, 12, 4, 12, 4, 12, 4, 12, 634538)");
	  echo "- Done<br>Adding Hauler";
	  $db->Execute("INSERT INTO $dbtables[ship_types] VALUES (17, 'Hauler', 'transport.jpg', 'This is a great transport and supply ship. It can carry a huge amount of cargo but does not have much in the way of military techs.  Strong engines for manouverability and a decent cloaking system are its only real defense.  This ship is favorite target of space pirates and owners often like to have an escort when they utilize it.', 'N', 72380952, 3571429, 3571429, 7142857, 7142857, 38, 20, 31, 20, 25, 20, 30, 2, 10, 2, 10, 2, 10, 2, 10, 2, 10, 2, 10, 10, 20, 252605)");
	  echo "- Done<br>";



/************************************************************
Ships from here will have to be built, and the stats above
will probably all be changed when testing, so no use defining
these right now.
************************************************************/

      echo "<B><BR>Inserting Missions<p></B>";
	  echo "Adding newbie mission<br>";
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (1, 0, 1, 'Welcome to Starkick Traders! To help you get started this is a training mission to help you learn how to play the game. You can quit this mission any time by clicking on the Quit Mission link but if you finish it you\'ll be rewarded.<br>The first thing you need to do is to learn to fly to different sectors. To do that use WARP LINKS. Under the Warp menu you will see some numbers. Those are links to other sectors. By clicking on the number 1 you will go to Sector 1. Do that now.', 'Please go to sector 1 using warp links. If you get lost, click on the Real Space menu, choose Other and go to sector 1 that way.', 'SELECT * FROM dbtables[players] WHERE sector=0 AND player_id=playerinfo[player_id]', -1)") or die(mysql_error());
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (1, 1, 2, 'Well done! You can travel between any sectors that have warp links between them and it only takes one turn. This is how you can travel early in the game.<br>Now use warp links to go to Sector 2 where we will learn how to trade.', 'You need to real space to sector 2', 'SELECT * FROM dbtables[players] WHERE sector=1 AND player_id=playerinfo[player_id]', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (1, 2, 3, 'Congratulations! Now we will trade. Let\'s buy something from the port here. You have 1000 credits and some energy on board that you can trade. Different ports sell different commodities. This one sells Ore. Click on the Ore link and buy some.', 'You need to buy Ore from the port in sector 2 or quit the mission.', 'SELECT * FROM dbtables[players] WHERE sector=2 AND player_id=playerinfo[player_id]', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (1, 3, 4, 'Now we have bought the Ore let\'s sell it at another port. Warp to sector 24.', 'Fly to sector 24 to sell your Ore at the port.', 'SELECT * FROM dbtables[ships] WHERE sector=2 AND player_id=playerinfo[player_id] AND ship_id=playerinfo[currentship] AND ship_ore>0', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (1, 4, 5, 'Here in Sector 24 there is another port. Ports buy everything they do not sell, so this port will buy your cargo. Click on the port link and sell what you have.', 'You need to fly to sector 24 to sell your Ore at the port there or quit the mission.', 'SELECT * FROM dbtables[ships] WHERE sector=24 AND player_id=playerinfo[player_id] AND ship_id=playerinfo[currentship] AND ship_ore>0', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (1, 5, -1, 'Well done!<br>Did you notice that the computer automatically filled in amounts for you so you bought some goods? You can sell those goods elsewhere and make even more profit.<br>This simple mission showed you how to move and how to trade. These are the basic skills you need to make money and build up a bigger ship!<br>Now you need to start finding profitable trade routes (hint: Ore<>Goods is a good one!) and start making more money.<br>Once you have made a few thousand credits go back to sector 0 and upgrade your hull so you can transport more and make more money. Keep on upgrading your ship and you will soon be able to colonize planets, start empires and complete real missions!<br>Stay safe from attacks by parking in Federation Space when you log off. Use the Nav Computer if you loose you way.<br>For finishing this tutorial your hull will be upgraded one level for free if you are in a shuttle, just make a move back to Sector 0.<br>Live long and prosper!', 'Sell your ore at the port in sector 24.', 'SELECT * FROM dbtables[ships] WHERE sector=24 AND player_id=playerinfo[player_id] AND ship_id=playerinfo[currentship] AND ship_ore=0', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (1, -1, -1, 'Hull upgraded to 1', 'Hull upgraded to 1', 'UPDATE dbtables[ships] SET hull=hull+1 WHERE player_id=playerinfo[player_id] AND ship_id=playerinfo[currentship] AND type=1 LIMIT 1', 0)");
	  echo "Making sure that the universe matches this mission<br>";
	  // Add the links
	  $res=$db->Execute("SELECT * FROM $dbtables[links] WHERE link_start=2 AND link_dest=24");
	  if ($res->RowCount() ==0) {
	  	$db->Execute("INSERT INTO $dbtables[links] SET link_start=2, link_dest=24");
	  }
	  $res=$db->Execute("SELECT * FROM $dbtables[links] WHERE link_start=24 AND link_dest=0");
	  if ($res->RowCount() ==0) {
	  	$db->Execute("INSERT INTO $dbtables[links] SET link_start=24, link_dest=0");
	  }
	  // Make the ports
	  // Change port type to be Ore
	  echo "UPDATE $dbtables[universe] SET port_type='ore', port_ore=$ore_limit, port_organics = $organics_limit, port_goods = $goods_limit, port_energy = $energy_limit WHERE sector_id=2 LIMIT 1";
	  $db->Execute("UPDATE $dbtables[universe] SET port_type='ore', port_ore=$ore_limit, port_organics = $organics_limit, port_goods = $goods_limit, port_energy = $energy_limit WHERE sector_id=2 LIMIT 1");
	  $db->Execute("UPDATE $dbtables[universe] SET port_type='goods', port_ore=$ore_limit, port_organics = $organics_limit, port_goods = $goods_limit, port_energy = $energy_limit WHERE sector_id=24 LIMIT 1");
	  echo "Done<br>";	
	  echo "Furangee Special Trader Mission<br>";	  
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (2, 0, 1, 'Incoming message from Commander Cain: Meet me in the asteroid belt in sector 3980 for further instructions...<EOM>', 'Go to sector 3980 to meet Commander Cain. You need at least 35000 points otherwise he will not see you.', 'SELECT * FROM dbtables[players] WHERE player_id=playerinfo[player_id] and score>35000', -1)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (2, 1, 2, 'Hello I am Commander Cain of the Federation. We have a secret mission for you. We want you to find and scan a Furangee Special Trader. These aliens sell Special port goods for half price. That is good if you can find them but we are keen to know more about their ships and how they can dodge sector defenses so easily. If you want to accept go to our base in sector 23 to have you ship fitted with a special scanner, otherwise please just quit the mission.', 'Go to sector 23 to accept the mission and have your ship fitted with a special scanning unit.', 'SELECT * FROM dbtables[players] WHERE player_id=playerinfo[player_id] AND sector=3980 and score>35000', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (2, 2, 3, 'Mission accepted. Scanning unit fitted! Now find a Furangee Special Trader!', 'Find a special trader: The scanning unit will automatically take readings as soon as one is detected.', 'SELECT * FROM dbtables[players] WHERE player_id=playerinfo[player_id] AND sector=23', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (2, 3, 4, 'Special Trader found! Scanning..... scanning complete!<br>Your reward will be a free 1 level Sensor Array upgrade. Deliver the scanning unit to sector 23 to get your reward of a +1 Sensor upgrade (Level 26 maximum).', 'Deliver the scanning unit to sector 23 to have your Sensors upgraded.<br>The highest Sensor upgrade possible is to Level 26 so if your sensors are already higher than that we cannot give you a reward (go there in another ship?).', 'SELECT * FROM dbtables[players] ,dbtables[furangee] WHERE email=furangee_id AND playerinfo[sector]=sector AND orders=4 AND sector!=0', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (2, 4, -1, 'Well done - mission completed! We have the information we need. As a token of our appreciation we will try to upgrade your sensor array (Level 26 max).', 'Mission complete', 'SELECT * FROM dbtables[players] WHERE player_id=playerinfo[player_id] AND sector=23', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (2, -1, -1, 'Sensor array upgraded', 'Sensor array upgraded', 'UPDATE dbtables[ships] SET sensors=sensors+1 WHERE player_id=playerinfo[player_id] AND ship_id=playerinfo[currentship] AND sensors<26 LIMIT 1', 0)");
	  echo "Make sure that Sector 3980 is an Asteroid Belt to prevent planets and sector defenses<br>";
	  $db->Execute("UPDATE $dbtables[universe] SET zone_id=5 WHERE sector_id=3980 LIMIT 1");
	  echo "Done<br>";
	  
	  echo "Envoy Mission<br>";
	  // Put the envoy mission near the end so that the planet is likely to have to be captured
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (5, 0, 1, 'Start of mission', '', 'SELECT * FROM dbtables[players] WHERE turns_used > 20000 AND player_id=playerinfo[player_id] AND score > 500000 AND sector > FLOOR(5000*RAND())', 1000)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (5, 3, 4, 'The Envoy is on a planet in this sector! You have to capture the planet to get him!', 'Capture the planet with the Envoy on in Sector [var2]. Do whatever you can, time is running out!', 'SELECT * FROM dbtables[players] WHERE player_id=playerinfo[player_id] AND sector = [var2]', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (5, 1, 2, '[message]', '', 'SELECT * FROM dbtables[players] WHERE player_id=playerinfo[player_id]', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (5, 4, 5, 'You found the Envoy! He is now on board your ship but <b>you need to take him back to Federation space before he dies.</b>', 'Take the Envoy back into Federation Space before he dies! Time is running out!', 'SELECT * FROM dbtables[players],dbtables[planets] WHERE player_id=playerinfo[player_id] AND dbtables[planets].planet_id=[var3] AND owner = playerinfo[player_id] AND sector=[var2]', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (5, 5, -1, 'You made it! Envoy Lebrinsky is whisked away by Med Corp to receive a body transplant. After his recovery he thanks you for the search and rescue and gives you his ship as a reward!<br>It will be found in Sector 0\'s Space Dock soon.', NULL, 'SELECT * FROM dbtables[players] WHERE player_id=playerinfo[player_id] AND sector < $fedsecs', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (5, -1, -1, 'Reward', NULL, 'INSERT INTO dbtables[ships] ( `ship_id` , `player_id` , `type` , `ship_name` , `ship_destroyed` , `hull` , `engines` , `power` , `computer` , `sensors` , `beams` , `torp_launchers` , `torps` , `shields` , `armour` , `armour_pts` , `cloak` , `sector` , `ship_ore` , `ship_organics` , `ship_goods` , `ship_energy` , `ship_colonists` , `ship_fighters` , `tow` , `on_planet` , `dev_warpedit` , `dev_genesis` , `dev_beacon` , `dev_emerwarp` , `dev_escapepod` , `dev_fuelscoop` , `dev_minedeflector` , `planet_id` , `cleared_defences` , `dev_lssd` , `dev_sectorwmd` , `fur_tech` ) \r\nVALUES (\'\', \'playerinfo[player_id]\', \'8\', \'Firefly\', \'N\', \'4\', \'19\', \'20\', \'10\', \'19\', \'27\', \'6\', \'0\', \'10\', \'19\', \'0\', \'0\', \'0\', \'0\', \'0\', \'0\', \'0\', \'0\', \'0\', \'0\', \'Y\', \'0\', \'0\', \'0\', \'0\', \'Y\', \'Y\', \'0\', \'0\', NULL , \'Y\', \'N\', \'N\')\r\n', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (5, 2, 3, 'Find a [var1] port with at least one planet in the sector. The planet might be owned or unowned.<br>You will be told if the Envoy is in that sector.\r\n', NULL, 'SELECT * FROM dbtables[players] WHERE 1', 0)");
	  echo "Done<br>";
	  echo "Furangee Killing Mission<br>";
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (6, 0, 1, 'Furangee killing mission', NULL, 'SELECT * FROM dbtables[players] WHERE playerinfo[score]>20000 AND email like \'%furangee%\' and sector=playerinfo[sector] and sector>0', 1000)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (6, 1, 2, 'Incoming message from Commander Cain... Deep space sensors have detected the Furangee are massing for an attack. The Federation needs to slow down their attack plans. Destroy 15 Furangee ships to buy time for the Federation to prepare our defenses.', '', 'SELECT * FROM dbtables[players] WHERE 1', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (6, 2, 3, 'Find and destroy 15 Furangee ships within the turn limit.<br>You will be told when you have destroyed all 15.', '', 'SELECT * FROM dbtables[players] WHERE 1', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (6, 3, -1, 'Well done... as a result of your pre-emptive attacks the Furangee has had to regroup. Your fighting skills are clearly an asset to the Federation. Your ship will be fitted with a Weapon of Mass Desctruction (yes, they do exist). The WMD does two things: it makes it more difficult for target ships to EWD when you attach and them, and it converts Genesis Torpedoes into a weapon that can destroy all enemy sector fighters!', NULL, 'SELECT * FROM dbtables[kills] WHERE player_id=playerinfo[player_id] AND fks>[var1]', 0)");
	  $db->Execute("INSERT INTO $dbtables[missions] VALUES (6, -1, -1, NULL, NULL, 'UPDATE dbtables[ships] SET dev_sectorwmd = \'Y\' WHERE player_id=playerinfo[player_id] AND ship_id=playerinfo[currentship]', 0)");

	  echo "Done<br>";
	  echo "Planet Building Mission<br>";
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (10, 0, 1, 'Build a planet mission', NULL, 'SELECT * FROM dbtables[players] WHERE turns_used > 5000 AND score < 10000 AND player_id=playerinfo[player_id]', -1)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (10, 1, 2, 'The Federation needs good homes for colonists. Find or build a planet, build a base on it and fill it with 25 million colonists. If you do, the Federation will send 25 million additional colonists to that planet to help get your empire started!', NULL, 'SELECT * FROM dbtables[planets] WHERE 1', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (10, 2, 3, 'Colonize a planet to have at least 25 million people on it and build a base on it.<br>Tip: Build the planet in an Energy sector so that when your colonists make commodities you can sell them easily in the same Sector.', NULL, 'SELECT * FROM dbtables[planets] WHERE 1', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (10, 3, -1, 'Well done! You obviously can build places that are great to live in! The Federation had no problem finding 25 million more colonists to go and join the others. They will arrive on your planet shortly.', NULL, 'SELECT * FROM dbtables[planets] WHERE owner=playerinfo[player_id] AND colonists > 24999999 AND colonists < 50000000 AND base = \'Y\'', 0)");
      $db->Execute("INSERT INTO $dbtables[missions] VALUES (10, -1, -1, 'Reward - 25 million colonists', NULL, 'UPDATE dbtables[planets] SET colonists=colonists+25000000 WHERE owner=playerinfo[player_id] AND colonists>24999999 LIMIT 1', 0)");
	  echo "Done<br>";
	  
	  echo "Furangee tech removal mission<br>";
	  $db->Execute("INSERT INTO $dbtables[missions] VALUES (11, 0, 1, 'YO, THIS IS A MESSAGE FOR YOU. WE NEED A FAST SHIP QUICK TO DELIVER SOME NON-FEDERATION APPROVED CARGO, IF YOU KNOW WHAT WE MEAN. DO THIS AND WE\'LL REMOVE THE FURANGEE TECH TAGS ON YOUR UPGRADES. BUT FIRST GET LEVEL 22 ENGINES - YOU GOTTA BE FAST! YOU HAVE ONLY 50 TURNS TO GET A FAST SHIP AND TRANSPORT THE CARGO STARTIN\' FROM NOW!', NULL, 'SELECT * FROM dbtables[players], dbtables[ships] WHERE playerinfo[player_id]=dbtables[players].player_id AND score>2000000 AND fur_tech=\'Y\' AND engines>19 AND currentship=dbtables[ships].ship_id', 50)");
	  $db->Execute("INSERT INTO $dbtables[missions] VALUES (11, 1, 2, 'YOU GOT DA HIGH SPEED ENGINES - OKAY SPEEDY, GET TO SECTOR [var2] AND WE\'LL TRANSFER THE CARGO.', NULL, 'SELECT * FROM dbtables[players], dbtables[ships] WHERE playerinfo[player_id]=dbtables[players].player_id AND fur_tech=\'Y\' AND engines>21 AND currentship=dbtables[ships].ship_id AND dbtables[ships].ship_id=[var1]', 0)");
	  $db->Execute("INSERT INTO $dbtables[missions] VALUES (11, 2, 3, 'CARGO TRANSFERED! GET THIS TO SECTOR [var3] NOW BEFORE IT BOILS OFF INTO OUTERSPACE. DON\'T LET THE FEDS CATCH YOU.', 'Deliver the cargo to Sector [var3] before the Feds catch you or it boils away!', 'SELECT * FROM dbtables[ships] WHERE ship_id=[var1] AND sector=[var2] AND player_id=playerinfo[player_id]', 0)");
	  $db->Execute("INSERT INTO $dbtables[missions] VALUES (11, 3, -1, 'DELIVERED. YOUR FURANGEE TECHS JUST GOT RECLASSIFIED! FLY SOMEWHERE TO GET IT ACTIVATED... ', '', 'SELECT * FROM dbtables[ships] WHERE ship_id=[var1] AND sector=[var3] AND player_id=playerinfo[player_id]', 0)");
	  $db->Execute("INSERT INTO $dbtables[missions] VALUES (11, 4, -1, '', NULL, 'SELECT * FROM dbtables[ships] WHERE ship_id=[var1] AND player_id=1', 0)");
	  $db->Execute("INSERT INTO $dbtables[missions] VALUES (11, -1, -1, 'Reward', NULL, 'UPDATE dbtables[ships] SET fur_tech = \'N\' WHERE ship_id=[var1] AND player_id=playerinfo[player_id] LIMIT 1', 0)");
	  echo "Done<br>";
	  
	  
	  echo "Inserting Medals<br>";
	  $db->Execute("INSERT INTO $dbtables[medals] VALUES ('Game Winner', 'Given to the winner of a game. Retained forever.', 1, 'winner.gif')");
	  $db->Execute("INSERT INTO $dbtables[medals] VALUES ('Three Time Winner', 'This medal is given to those special players who have won 3 games of SKT', 2, '3x.gif')");
	  $db->Execute("INSERT INTO $dbtables[medals] VALUES ('MVP Award', 'This medal is for anyone who has won an MVP award. This award is either given by the admin or voted by players. Kept for life of character but can only be won once.', 3, 'mvp.gif')");
	  $db->Execute("INSERT INTO $dbtables[medals] VALUES ('Medal of Darkness', 'This medal is awarded to the most evil player of the previous game.', 4, 'dark.gif')");
	  $db->Execute("INSERT INTO $dbtables[medals] VALUES ('Medal of Light', 'This medal is awarded to the most saintly player of the previous game.', 5, 'light.gif')");
	  $db->Execute("INSERT INTO $dbtables[medals] VALUES ('Silver Medal', 'Runner-up in last game', 6, 'silver.gif')");
	  $db->Execute("INSERT INTO $dbtables[medals] VALUES ('Team Medal', 'Winning Alliance member from last game', 7, 'team.gif')");
	  $db->Execute("INSERT INTO $dbtables[medals] VALUES ('Best Newcomer', 'A medal for the best new player from the last game', 8, 'newbie.gif')");
	  $db->Execute("INSERT INTO $dbtables[medals] VALUES ('Furangee Quasher', 'An award for the player who for better or for worse destroys the most Furangee', 9, 'fur.gif')");
	  echo "Done<br>";
      $password = substr($admin_mail, 0, $maxlen_password);
	  echo "Inserting Unowned user into players<br>";
	  $db->Execute("INSERT INTO $dbtables[players] SET character_name='Unowned'");
	  // Create a random email and password
	  $UnownedPassword = createRandomPassword();
	  $db->Execute("UPDATE `$dbtables[players]` SET `player_id` = '0', `cleared_defences` = NULL, `subscribed` = NULL, email = '$UnownedPassword', password = '$UnownedPassword' WHERE `character_name` = 'Unowned' LIMIT 1");
      echo "<BR><BR><center><B>Your admin login is: <BR>";
      echo "<BR>Username: $admin_mail";
      echo "<BR>Password: $password<BR></B></center>";
      newplayer($admin_mail, "The Federation", $password, "The Orb");
  	  $db->Execute("UPDATE $dbtables[players] SET player_id = '1', `cleared_defences` = NULL, `subscribed` = NULL WHERE `email` = '$admin_mail' LIMIT 1");
	  $db->Execute("UPDATE $dbtables[ships] SET player_id = '1' WHERE player_id=2 LIMIT 1");
      PrintFlush("<BR><BR><center><BR><B>Congratulations! Universe created successfully.<BR>");
      PrintFlush("Click <A HREF=login.php>here</A> to return to the login screen.</B></center>");
      break;

// Pre-stage, What's the password?
   default:
      echo "<form action=create_universe.php method=post>";
      echo "Password: <input type=password name=swordfish size=20 maxlength=20>&nbsp;&nbsp;";
      echo "<input type=submit value=Submit><input type=hidden name=step value=1>";
      echo "<input type=reset value=Reset>";
      echo "</form>";
      break;
}

// Done
$StopTime=$BenchmarkTimer->stop();
$Elapsed=$BenchmarkTimer->elapsed();
PrintFlush("<br>Elapsed Time - $Elapsed");
include("footer.php");
?>
