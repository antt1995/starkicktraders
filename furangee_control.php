<?


include("config.php");
updatecookie();

include("languages/$lang");

$title="Furangee Control";
include("header.php");

connectdb();
bigtitle();

function CHECKED($yesno)
{
  return(($yesno == "Y") ? "CHECKED" : "");
}

function YESNO($onoff)
{
  return(($onoff == "ON") ? "Y" : "N");
}

$module = $menu;

if($swordfish != $adminpass)
{
  echo "<FORM ACTION=furangee_control.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
}
else
{
  // ******************************
  // ******** MAIN MENU ***********
  // ******************************
  if(empty($module))
  {
    echo "Welcome to the StarKick Traders Furangee Control module<BR><BR>";
    echo "Select a function from the list below:<BR>";
    echo "<FORM ACTION=furangee_control.php METHOD=POST>";
    echo "<SELECT NAME=menu>";
    echo "<OPTION VALUE=instruct>Furangee Instructions</OPTION>";
    echo "<OPTION VALUE=furangeeedit SELECTED>Furangee Character Editor</OPTION>";
    echo "<OPTION VALUE=createnew>Create A New Furangee Character</OPTION>";
	echo "<OPTION VALUE=furangeeturns>Globally Set Turns Used By Furangees</OPTION>";
    echo "<OPTION VALUE=clearlog>Clear All Furangee Log Files</OPTION>";
    echo "<OPTION VALUE=dropfurangee>Drop and Re-Install Furangee Database</OPTION>";
    echo "</SELECT>";
    echo "<INPUT TYPE=HIDDEN NAME=swordfish VALUE=$swordfish>";
    echo "&nbsp;<INPUT TYPE=SUBMIT VALUE=Submit>";
    echo "</FORM>";
  }
  else
  {
    $button_main = true;
    // ***********************************************
    // ********* START OF INSTRUCTIONS SUB ***********
    // ***********************************************
    if($module == "instruct")
    {
      echo "<H2>Furangee Instructions</H2>";
      echo "<P>&nbsp;&nbsp;&nbsp; Welcome to the Furangee Control module.  This is the module that will control the Furangee players in the game. ";
      echo "It is very simple right now, but will be expanded in future versions. ";
      echo "The ultimate goal of the Furangee players is to create some interactivity for those games without a large user base. ";
      echo "I need not say that the Furangee will also make good cannon fodder for those games with a large user base. ";

      echo "<H3>Furangee Creation</H3>";
      echo "<P>&nbsp;&nbsp;&nbsp; In order to create a Furangee you must choose the <B>\"Create A Furangee Character\"</B> option from the menu. ";
      echo "This will bring up the Furangee character creation screen.  There are only a few fields for you to edit. ";
      echo "However, with these fields you will determine not only how your Furangee will be created, but how he will act in the game. ";
      echo "We will now go over these fields and what they will do. ";

      echo "<P>&nbsp;&nbsp;&nbsp; When creating a new Furangee character the <B>Furangee Name</B> and the <B>Shipname</B> are automatically generated. ";
      echo "You can change these default values by editing these fields before submitting the character for creation. ";
      echo "Take care not to duplicate a current player or ship name, for that will result in creation failure. ";
      echo "<BR>&nbsp;&nbsp;&nbsp; The starting <B>Sector</B> number will also be randomly generated. ";
      echo "You can change this to any sector.  However, you should take care to use a valid sector number. Otherwise the creation will fail.";
      echo "<BR>&nbsp;&nbsp;&nbsp; The <B>Level</B> field will default to '3'.  This field refers to the starting tech level of all ship stats. ";
      echo "So a default Furangee will have it's Hull, Beams, Power, Engine, etc... all set to 3 unless this value is changed. ";
      echo "All appropriate ship stores will be set to the maximum allowed by the given tech level. ";
      echo "So, starting levels of energy, fighters, armour, torps, etc... are all affected by this setting. ";
      echo "<BR>&nbsp;&nbsp;&nbsp; The <B>Active</B> checkbox will default to checked. ";
      echo "This box refers to if the Furangee AI system will see this Furangee and execute it's orders. ";
      echo "If this box is not checked then the Furangee AI system will ignore this record and the next two fields are ignored. ";
      echo "<BR>&nbsp;&nbsp;&nbsp; The <B>Orders</B> selection box will default to 'SENTINEL'. ";
      echo "There are three other options available: ROAM, ROAM AND TRADE, and ROAM AND HUNT. ";
      echo "These Orders and what they mean will be detailed below. ";
      echo "<BR>&nbsp;&nbsp;&nbsp; The <B>Aggression</B> selection box will default to 'PEACEFUL'. ";
      echo "There are two other options available: ATTACK SOMETIMES, and ATTACK ALWAYS. ";
      echo "These Aggression settings and what they mean will be detailed below. ";
      echo "<BR>&nbsp;&nbsp;&nbsp; Pressing the <B>Create</B> button will create the Furangee and return to the creation screen to create another. ";

      echo "<H3>Furangee Orders</H3>";
      echo "<P> Here are the Furangee Order options and what the Furangee AI system will do for each: ";
      echo "<UL>SENTINEL<BR> ";
      echo "This Furangee will stay in place.  His only interactions will be with those who are in his sector at the time he takes his turn. ";
      echo "The aggression level will determine what those player interactions are.</UL> ";
      echo "<UL>ROAM<BR> ";
      echo "This Furangee will warp from sector to sector looking for players to interact with. ";
      echo "The aggression level will determine what those player interactions are.</UL> ";
      echo "<UL>ROAM AND TRADE<BR> ";
      echo "This Furangee will warp from sector to sector looking for players to interact with and ports to trade with. ";
      echo "The Furangee will trade at a port if possible before looking for player interactions. ";
      echo "The aggression level will determine what those player interactions are.</UL> ";
      echo "<UL>ROAM AND HUNT<BR> ";
      echo "This Furangee has a taste for blood and likes the sport of a good hunt. ";
      echo "Ocassionally (around 1/4th the time) this Furangee has the urge to go hunting.  He will randomly choose one of the top ten players to hunt. ";
      echo "If that player is in a sector that allows attack, then the Furangee warps there and attacks. ";
      echo "When he is not out hunting this Furangee acts just like one with ROAM orders.</UL> "; 

      echo "<H3>Furangee Aggression</H3>";
      echo "<P> Here are the Furangee Aggression levels and what the Furangee AI system will do for each: ";
      echo "<UL>PEACEFUL<BR> ";
      echo "This Furangee will not attack players.  He will continue to roam or trade as ordered but will not launch any attacks. ";
      echo "If this Furangee is a hunter then he will still attack players on the hunt but never otherwise.</UL> ";
      echo "<UL>ATTACK SOMETIMES<BR> ";
      echo "This Furangee will compare it's current number of fighters to a players fighters before deciding to attack. ";
      echo "If the Furangee's fighters are greater then the player's, then the Furangee will attack the player.</UL> ";
      echo "<UL>ATTACK ALWAYS<BR> ";
      echo "This Furangee is just mean.  He will attack anyone he comes across regardless of the odds.</UL> ";

    }
    // ***********************************************
    // ********* START OF FURANGEE EDIT SUB ***********
    // ***********************************************
    elseif($module == "furangeeedit")
    {
      echo "<span style=\"font-family : courier, monospace; font-size: 12pt; color: #00FF00 \">Furangee Editor</span><BR>";
      echo "<FORM ACTION=furangee_control.php METHOD=POST>";
      if(empty($user))
      {
        echo "<SELECT SIZE=20 NAME=user>";
		
        $res = $db->Execute("SELECT email,character_name,$dbtables[ships].ship_destroyed,active,$dbtables[players].sector FROM $dbtables[players],$dbtables[furangee],$dbtables[ships] WHERE email=furangee_id AND $dbtables[ships].ship_id=$dbtables[players].currentship AND $dbtables[ships].player_id=$dbtables[players].player_id ORDER BY sector");
        while(!$res->EOF)
        {
          $row=$res->fields;
          $charnamelist = sprintf("%-20s", $row[character_name]);
          $charnamelist = str_replace("  ", "&nbsp;&nbsp;",$charnamelist);
          $sectorlist = sprintf("Sector %'04d&nbsp;&nbsp;", $row[sector]);
          if ($row[active] == "Y") { $activelist = "Active &Oslash;&nbsp;&nbsp;"; } else { $activelist = "Active O&nbsp;&nbsp;"; }
          if ($row[ship_destroyed] == "Y") { $destroylist = "Destroyed &Oslash;&nbsp;&nbsp;"; } else { $destroylist = "Destroyed O&nbsp;&nbsp;"; }
          printf ("<OPTION VALUE=%s>%s %s %s %s</OPTION>", $row[email], $activelist, $destroylist, $sectorlist, $charnamelist);
          $res->MoveNext();
        }
        echo "</SELECT>";
        echo "&nbsp;<INPUT TYPE=SUBMIT VALUE=Edit>";
      }
      else
      {
        if(empty($operation))
        {
          $res = $db->Execute("SELECT * FROM $dbtables[players] JOIN $dbtables[furangee] WHERE email=furangee_id AND email='$user'");
		  $row = $res->fields;
		  $res2 = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$row[player_id] AND ship_id=$row[currentship]");
          $shipinfo = $res2->fields;
          echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5>";
          echo "<TR><TD>Furangee name</TD><TD><INPUT TYPE=TEXT NAME=character_name VALUE=\"$row[character_name]\"></TD></TR>";
          echo "<TR><TD>Active?</TD><TD><INPUT TYPE=CHECKBOX NAME=active VALUE=ON " . CHECKED($row[active]) . "></TD></TR>";
          echo "<TR><TD>E-mail</TD><TD>$row[email]</TD></TR>";
          echo "<TR><TD>ID</TD><TD>$row[player_id]</TD></TR>";
          echo "<TR><TD>Ship</TD><TD><INPUT TYPE=TEXT NAME=ship_name VALUE=\"$shipinfo[ship_name]\"></TD></TR>";
          echo "<TR><TD>Destroyed?</TD><TD><INPUT TYPE=CHECKBOX NAME=ship_destroyed VALUE=ON " . CHECKED($shipinfo[ship_destroyed]) . "></TD></TR>";
          echo "<TR><TD>Orders</TD><TD>";
            echo "<SELECT SIZE=1 NAME=orders>";
            $oorder0 = $oorder1 = $oorder2 = $oorder3 = $oorder4 = "VALUE";
            if ($row[orders] == 0) $oorder0 = "SELECTED=0 VALUE";
            if ($row[orders] == 1) $oorder1 = "SELECTED=1 VALUE";
            if ($row[orders] == 2) $oorder2 = "SELECTED=2 VALUE";
            if ($row[orders] == 3) $oorder3 = "SELECTED=3 VALUE";
			if ($row[orders] == 4) $oorder4 = "SELECTED=4 VALUE";
            echo "<OPTION $oorder0=0>Sentinel</OPTION>";
            echo "<OPTION $oorder1=1>Roam</OPTION>";
            echo "<OPTION $oorder2=2>Roam and Trade</OPTION>";
            echo "<OPTION $oorder3=3>Roam and Hunt</OPTION>";
			echo "<OPTION $oorder4=4>Special Trader</OPTION>";
            echo "</SELECT></TD></TR>";
          echo "<TR><TD>Aggression</TD><TD>";
            $oaggr0 = $oaggr1 = $oaggr2 = "VALUE";
            if ($row[aggression] == 0) $oaggr0 = "SELECTED=0 VALUE";
            if ($row[aggression] == 1) $oaggr1 = "SELECTED=1 VALUE";
            if ($row[aggression] == 2) $oaggr2 = "SELECTED=2 VALUE";
            echo "<SELECT SIZE=1 NAME=aggression>";
            echo "<OPTION $oaggr0=0>Peaceful</OPTION>";
            echo "<OPTION $oaggr1=1>Attack Sometimes</OPTION>";
            echo "<OPTION $oaggr2=2>Attack Always</OPTION>";
            echo "</SELECT></TD></TR>";
          echo "<TR><TD>Levels</TD>";
          echo "<TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5>";
          echo "<TR><TD>Hull</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=hull VALUE=\"$shipinfo[hull]\"></TD>";
          echo "<TD>Engines</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=engines VALUE=\"$shipinfo[engines]\"></TD>";
          echo "<TD>Power</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=power VALUE=\"$shipinfo[power]\"></TD>";
          echo "<TD>Computer</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=computer VALUE=\"$shipinfo[computer]\"></TD></TR>";
          echo "<TR><TD>Sensors</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=sensors VALUE=\"$shipinfo[sensors]\"></TD>";
          echo "<TD>Armour</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=armour VALUE=\"$shipinfo[armour]\"></TD>";
          echo "<TD>Shields</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=shields VALUE=\"$shipinfo[shields]\"></TD>";
          echo "<TD>Beams</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=beams VALUE=\"$shipinfo[beams]\"></TD></TR>";
          echo "<TR><TD>Torpedoes</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=torp_launchers VALUE=\"$shipinfo[torp_launchers]\"></TD>";
          echo "<TD>Cloak</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=cloak VALUE=\"$shipinfo[cloak]\"></TD></TR>";
          echo "</TABLE></TD></TR>";
          echo "<TR><TD>Holds</TD>";
          echo "<TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5>";
          echo "<TR><TD>Ore</TD><TD><INPUT TYPE=TEXT SIZE=8 NAME=ship_ore VALUE=\"$shipinfo[ship_ore]\"></TD>";
          echo "<TD>Organics</TD><TD><INPUT TYPE=TEXT SIZE=8 NAME=ship_organics VALUE=\"$shipinfo[ship_organics]\"></TD>";
          echo "<TD>Goods</TD><TD><INPUT TYPE=TEXT SIZE=8 NAME=ship_goods VALUE=\"$shipinfo[ship_goods]\"></TD></TR>";
          echo "<TR><TD>Energy</TD><TD><INPUT TYPE=TEXT SIZE=8 NAME=ship_energy VALUE=\"$shipinfo[ship_energy]\"></TD>";
          echo "<TD>Colonists</TD><TD><INPUT TYPE=TEXT SIZE=8 NAME=ship_colonists VALUE=\"$shipinfo[ship_colonists]\"></TD></TR>";
          echo "</TABLE></TD></TR>";
          echo "<TR><TD>Combat</TD>";
          echo "<TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5>";
          echo "<TR><TD>Fighters</TD><TD><INPUT TYPE=TEXT SIZE=8 NAME=ship_fighters VALUE=\"$shipinfo[ship_fighters]\"></TD>";
          echo "<TD>Torpedoes</TD><TD><INPUT TYPE=TEXT SIZE=8 NAME=torps VALUE=\"$shipinfo[torps]\"></TD></TR>";
          echo "<TR><TD>Armour Pts</TD><TD><INPUT TYPE=TEXT SIZE=8 NAME=armour_pts VALUE=\"$shipinfo[armour_pts]\"></TD></TR>";
          echo "</TABLE></TD></TR>";
          echo "<TR><TD>Devices</TD>";
          echo "<TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5>";
          echo "<TR><TD>Beacons</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=dev_beacon VALUE=\"$shipinfo[dev_beacon]\"></TD>";
          echo "<TD>Warp Editors</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=dev_warpedit VALUE=\"$shipinfo[dev_warpedit]\"></TD>";
          echo "<TD>Genesis Torpedoes</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=dev_genesis VALUE=\"$shipinfo[dev_genesis]\"></TD></TR>";
          echo "<TR><TD>Mine Deflectors</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=dev_minedeflector VALUE=\"$shipinfo[dev_minedeflector]\"></TD>";
          echo "<TD>Emergency Warp</TD><TD><INPUT TYPE=TEXT SIZE=5 NAME=dev_emerwarp VALUE=\"$shipinfo[dev_emerwarp]\"></TD></TR>";
          echo "<TR><TD>Escape Pod</TD><TD><INPUT TYPE=CHECKBOX NAME=dev_escapepod VALUE=ON " . CHECKED($shipinfo[dev_escapepod]) . "></TD>";
          echo "<TD>FuelScoop</TD><TD><INPUT TYPE=CHECKBOX NAME=dev_fuelscoop VALUE=ON " . CHECKED($shipinfo[dev_fuelscoop]) . "></TD></TR>";
          echo "</TABLE></TD></TR>";
          echo "<TR><TD>Credits</TD><TD><INPUT TYPE=TEXT NAME=credits VALUE=\"$row[credits]\"></TD></TR>";
          echo "<TR><TD>Turns</TD><TD><INPUT TYPE=TEXT NAME=turns VALUE=\"$row[turns]\"></TD></TR>";
          echo "<TR><TD>Current sector</TD><TD><INPUT TYPE=TEXT NAME=sector VALUE=\"$row[sector]\"></TD></TR>";
          echo "</TABLE>";
          echo "<BR>";
          echo "<INPUT TYPE=HIDDEN NAME=user VALUE=$user>";
          echo "<INPUT TYPE=HIDDEN NAME=operation VALUE=save>";
          echo "<INPUT TYPE=SUBMIT VALUE=Save>";
          //******************************
          //*** SHOW FURANGEE LOG DATA ***
          //******************************
          echo "<HR>";
          echo "<span style=\"font-family : courier, monospace; font-size: 12pt; color: #00FF00;\">Log Data For This Furangee</span><BR>";

          $logres = $db->Execute("SELECT * FROM $dbtables[logs] WHERE player_id=$row[player_id] ORDER BY time DESC, type DESC");   
          while(!$logres->EOF)
          {
            $logrow = $logres->fields;
            $logtype = "";
            switch($logrow[type])
            {
              case LOG_FURANGEE_ATTACK:
                $logtype = "Launching an attack on ";
                break;
              case LOG_ATTACK_LOSE:
                $logtype = "We were attacked and lost against ";
                break;
              case LOG_ATTACK_WIN:
                $logtype = "We were attacked and won against ";
                break;
            }
            $logdatetime = substr($logrow[time], 4, 2) . "/" . substr($logrow[time], 6, 2) . "/" . substr($logrow[time], 0, 4) . " " . substr($logrow[time], 8, 2) . ":" . substr($logrow[time], 10, 2) . ":" . substr($logrow[time], 12, 2);
            echo "$logdatetime $logtype$logrow[data] <BR>";
            $logres->MoveNext();
          }
        }
        elseif($operation == "save")
        {
          // update database
          $_ship_destroyed = empty($ship_destroyed) ? "N" : "Y";
          $_dev_escapepod = empty($dev_escapepod) ? "N" : "Y";
          $_dev_fuelscoop = empty($dev_fuelscoop) ? "N" : "Y";
          $_active = empty($active) ? "N" : "Y";
		  $res = $db->Execute("SELECT player_id, currentship FROM $dbtables[players] WHERE email='$user' LIMIT 1");
		  $playerinfo = $res->fields;
          $result = $db->Execute("UPDATE $dbtables[players] SET character_name='$character_name',credits='$credits',turns='$turns',sector='$sector' WHERE email='$user'");
		  $result2 = $db->Execute("UPDATE $dbtables[ships] SET ship_name='$ship_name',ship_destroyed='$_ship_destroyed',hull='$hull',engines='$engines',power='$power',computer='$computer',sensors='$sensors',armour='$armour',shields='$shields',beams='$beams',torp_launchers='$torp_launchers',cloak='$cloak',dev_warpedit='$dev_warpedit',dev_genesis='$dev_genesis',dev_beacon='$dev_beacon',dev_emerwarp='$dev_emerwarp',dev_escapepod='$_dev_escapepod',dev_fuelscoop='$_dev_fuelscoop',dev_minedeflector='$dev_minedeflector',sector='$sector',ship_ore='$ship_ore',ship_organics='$ship_organics',ship_goods='$ship_goods',ship_energy='$ship_energy',ship_colonists='$ship_colonists',ship_fighters='$ship_fighters',torps='$torps',armour_pts='$armour_pts' WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
          if(!$result || !$result2) {
            echo "Changes to Furangee ship record have FAILED Due to the following Error:<BR><BR>";
            echo $db->ErrorMsg() . "<br>";
          } else {
            echo "Changes to Furangee ship record have been saved.<BR><BR>";
            $result2 = $db->Execute("UPDATE $dbtables[furangee] SET active='$_active',orders='$orders',aggression='$aggression' WHERE furangee_id='$user'");
            if(!$result2) {
              echo "Changes to Furangee activity record have FAILED Due to the following Error:<BR><BR>";
              echo $db->ErrorMsg() . "<br>";
            } else {
              echo "Changes to Furangee activity record have been saved.<BR><BR>";
            }
          }
          echo "<INPUT TYPE=SUBMIT VALUE=\"Return to Furangee editor\">";
          $button_main = false;
        }
        else
        {
          echo "Invalid operation";
        }
      }
      echo "<INPUT TYPE=HIDDEN NAME=menu VALUE=furangeeedit>";
      echo "<INPUT TYPE=HIDDEN NAME=swordfish VALUE=$swordfish>";
      echo "</FORM>";
    }
    // ***********************************************
    // ******** START OF DROP FURANGEE SUB ***********
    // ***********************************************
    elseif($module == "dropfurangee")
    {
      echo "<H1>Drop and Re-Install Furangee Database</H1>";
      echo "<H3>This will DELETE All Furangee records from the <i>ships</i> TABLE then DROP and reset the <i>furangee</i> TABLE</H3>";
      echo "<FORM ACTION=furangee_control.php METHOD=POST>";
      if(empty($operation))
      {
        echo "<BR>";
        echo "<H2><FONT COLOR=Red>Are You Sure?</FONT></H2><BR>";
        echo "<INPUT TYPE=HIDDEN NAME=operation VALUE=dropfur>";
        echo "<INPUT TYPE=SUBMIT VALUE=Drop>";
      }
      elseif($operation == "dropfur")
      {
        // Delete all furangee in the ships table
        echo "Deleting furangee records in the ships table...<BR>";
        $db->Execute("DELETE FROM $dbtables[players] WHERE email LIKE '%@furangee'");
        echo "deleted.<BR>";
        // Drop furangee table
        echo "Dropping furangee table...<BR>";
        $db->Execute("DROP TABLE IF EXISTS $dbtables[furangee]");
        echo "dropped.<BR>";
        // Create furangee table
        echo "Re-Creating table: furangee...<BR>";
        $db->Execute("CREATE TABLE $dbtables[furangee](" .
            "furangee_id char(40) NOT NULL," .
            "active enum('Y','N') DEFAULT 'Y' NOT NULL," .
            "aggression smallint(5) DEFAULT '0' NOT NULL," .
            "orders smallint(5) DEFAULT '0' NOT NULL," .
            "PRIMARY KEY (furangee_id)," .
            "KEY furangee_id (furangee_id)" .
            ")");
        echo "created.<BR>";
      }
      else
      {
        echo "Invalid operation";
      }
      echo "<INPUT TYPE=HIDDEN NAME=menu VALUE=dropfurangee>";
      echo "<INPUT TYPE=HIDDEN NAME=swordfish VALUE=$swordfish>";
      echo "</FORM>";
    }
    // ***********************************************
    // ***** START OF CLEAR FURANGEE LOG SUB *********
    // ***********************************************
    elseif($module == "clearlog")
    {
      echo "<H1>Clear All Furangee Logs</H1>";
      echo "<H3>This will DELETE All Furangee log files</H3>";
      echo "<FORM ACTION=furangee_control.php METHOD=POST>";
      if(empty($operation))
      {
        echo "<BR>";
        echo "<H2><FONT COLOR=Red>Are You Sure?</FONT></H2><BR>";
        echo "<INPUT TYPE=HIDDEN NAME=operation VALUE=clearfurlog>";
        echo "<INPUT TYPE=SUBMIT VALUE=Clear>";
      }
      elseif($operation == "clearfurlog")
      {
          $db->Execute("DELETE FROM $dbtables[logs] WHERE furangee='Y'");
		  $db->Execute("OPTIMIZE TABLE $dbtables[logs]");
          echo "Logs  cleared.<BR>";
      }
      else
      {
        echo "Invalid operation";
      }
      echo "<INPUT TYPE=HIDDEN NAME=menu VALUE=clearlog>";
      echo "<INPUT TYPE=HIDDEN NAME=swordfish VALUE=$swordfish>";
      echo "</FORM>";
    }
    // ***********************************************
    // ******** START OF CREATE FURANGEE SUB **********
    // ***********************************************
    elseif($module == "createnew")
    {
      echo "<B>Create A New Furangee</B>";
      echo "<BR>";
      echo "<FORM ACTION=furangee_control.php METHOD=POST>";
      if(empty($operation))
      {
        // Create Furangee Name
        $Sylable1 = array("Ak","Al","Ar","B","Br","D","F","Fr","G","Gr","K","Kr","N","Ol","Om","P","Qu","R","S","Z");
        $Sylable2 = array("a","ar","aka","aza","e","el","i","in","int","ili","ish","ido","ir","o","oi","or","os","ov","u","un");
        $Sylable3 = array("ag","al","ak","ba","dar","g","ga","k","ka","kar","kil","l","n","nt","ol","r","s","ta","til","x");
        $sy1roll = rand(0,19);
        $sy2roll = rand(0,19);
        $sy3roll = rand(0,19);
        $character = $Sylable1[$sy1roll] . $Sylable2[$sy2roll] . $Sylable3[$sy3roll];
        $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
        $resultnm = $db->Execute ("select character_name from $dbtables[players] where character_name='$character'");
        $namecheck = $resultnm->fields;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $nametry = 1;
        // If Name Exists Try Again - Up To Nine Times
        while (($namecheck[0]) and ($nametry <= 9)) {
          $sy1roll = rand(0,19);
          $sy2roll = rand(0,19);
          $sy3roll = rand(0,19);
          $character = $Sylable1[$sy1roll] . $Sylable2[$sy2roll] . $Sylable3[$sy3roll];
          $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
          $resultnm = $db->Execute ("select character_name from $dbtables[players] where character_name='$character'");
          $namecheck = $resultnm->fields;
          $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
          $nametry++;
        }
        // Create Ship Name
        $shipname = "Furangee- " . $character; 
        // Select Random Sector
        $sector = rand(1,$sector_max); 
        // Display Confirmation Form
        echo "<TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5>";
        echo "<TR><TD>Furangee Name</TD><TD><INPUT TYPE=TEXT SIZE=20 NAME=character VALUE='$character'></TD>";
		$lv = rand(3,30);
        echo "<TD>Level <INPUT TYPE=TEXT SIZE=5 NAME=furlevel VALUE='$lv'></TD>";
        echo "<TD>Ship Name <INPUT TYPE=TEXT SIZE=20 NAME=shipname VALUE='$shipname'></TD>";
        echo "<TR><TD>Active?<INPUT TYPE=CHECKBOX NAME=active VALUE=ON CHECKED ></TD>";
        echo "<TD>Orders ";
          echo "<SELECT SIZE=1 NAME=orders>";
          echo "<OPTION VALUE=0>Sentinel</OPTION>";
          echo "<OPTION VALUE=1>Roam</OPTION>";
          echo "<OPTION SELECTED VALUE=2>Roam and Trade</OPTION>";
          echo "<OPTION VALUE=3>Roam and Hunt</OPTION>";
		  echo "<OPTION VALUE=4>Special Trader</OPTION>";
          echo "</SELECT></TD>";
        echo "<TD>Sector <INPUT TYPE=TEXT SIZE=5 NAME=sector VALUE=$sector></TD>";
        echo "<TD>Aggression ";
          echo "<SELECT SIZE=1 NAME=aggression>";
          echo "<OPTION SELECTED VALUE=0>Peaceful</OPTION>";
          echo "<OPTION VALUE=1>Attack Sometimes</OPTION>";
          echo "<OPTION VALUE=2>Attack Always</OPTION>";
          echo "</SELECT></TD></TR>";
        echo "</TABLE>";
        echo "<HR>";
        echo "<INPUT TYPE=HIDDEN NAME=operation VALUE=createfurangee>";
        echo "<INPUT TYPE=SUBMIT VALUE=Create>";
      }
      elseif($operation == "createfurangee")
      {
        // update database
        $_active = empty($active) ? "N" : "Y";
        $errflag=0;
        if ( $character=='' || $shipname=='' ) { echo "Ship name, and character name may not be blank.<BR>"; $errflag=1;}
        // Change Spaces to Underscores in shipname
        //$shipname = str_replace(" ","_",$shipname);
        // Create emailname from character
        $emailname = str_replace(" ","_",$character) . "@furangee";
        $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
        $result = $db->Execute ("select email, character_name, ship_name from $dbtables[players],$dbtables[ships] where email='$emailname' OR character_name='$character' OR ship_name='$shipname'");
        if ($result>0)
        {
          while (!$result->EOF)
          {
            $row= $result->fields;
            if ($row[0]==$emailname) { echo "ERROR: E-mail address $emailname, is already in use.  "; $errflag=1;}
            if ($row[1]==$character) { echo "ERROR: Character name $character, is already in use.<BR>"; $errflag=1;}
            if ($row[2]==$shipname) { echo "ERROR: Ship name $shipname, is already in use.<BR>"; $errflag=1;}
            $result->MoveNext();
          }
        }
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        if ($errflag==0)
        {
          $makepass="";
          $syllables="er,in,tia,wol,fe,pre,vet,jo,nes,al,len,son,cha,ir,ler,bo,ok,tio,nar,sim,ple,bla,ten,toe,cho,co,lat,spe,ak,er,po,co,lor,pen,cil,li,ght,wh,at,the,he,ck,is,mam,bo,no,fi,ve,any,way,pol,iti,cs,ra,dio,sou,rce,sea,rch,pa,per,com,bo,sp,eak,st,fi,rst,gr,oup,boy,ea,gle,tr,ail,bi,ble,brb,pri,dee,kay,en,be,se";
          $syllable_array=explode(",", $syllables);
          srand((double)microtime()*1000000);
          for ($count=1;$count<=4;$count++) {
            if (rand()%10 == 1) {
              $makepass .= sprintf("%0.0f",(rand()%50)+1);
            } else {
              $makepass .= sprintf("%s",$syllable_array[rand()%62]);
            }
          }
          if ($furlevel=='') $furlevel=0;
          $maxenergy = NUM_ENERGY($furlevel);
          $maxarmour = NUM_ARMOUR($furlevel);
          $maxfighters = NUM_FIGHTERS($furlevel);
          $maxtorps = NUM_TORPEDOES($furlevel);
          $stamp=date("Y-m-d H:i:s");
// *****************************************************************************
// *** ADD FURANGEE RECORD TO ships TABLE ... MODIFY IF ships SCHEMA CHANGES ***
// *****************************************************************************
		$result2 = $db->Execute("INSERT INTO $dbtables[players] (`player_id`, `character_name`, `password`, `email`,  `credits`, `sector`,  `on_planet`,`turns_used`, `last_login`, `rating`, `score`, `team`, `team_invite`, `interface`, `ip_address`, `planet_id`, `preset1`, `preset2`, `preset3`, `trade_colonists`, `trade_fighters`, `trade_torps`, `trade_energy`, `cleared_defences`, `lang`, `alerts`,  `alert2`, `subscribed`, `ore_price`, `organics_price`, `goods_price`, `energy_price`, `currentship`,`preset4`,`preset5`,`preset6`) VALUES ('', '$character', '$makepass', '$emailname','10000000', '$sector','N', '3000', '$stamp', '0', '0', '0', '0', 'N', '127.0.0.1', '0', '0', '0', '0', 'N', 'N', 'N', 'N', NULL, '$default_lang', 'N', 'N', NULL, '0', '0', '0', '0', '1',0,0,0)");
		$res = $db->Execute("SELECT player_id from $dbtables[players] WHERE email='$emailname'");
  		$player_id = $res->fields[player_id]; 
		// Calculate ship type
		$shipavg = $furlevel;
		if($shipavg < 4)
		   $shiptype = 1;
		elseif($shipavg < 6)
		   $shiptype = 2;
		elseif($shipavg < 10)
		   $shiptype = 3;
		elseif($shipavg < 20)
		   $shiptype = rand(5,7);
		else
		   $shiptype = 4;
		$shiptype=20; // We have a special furangee ship now.
		if ($furlevel > 16) {
			$ewd = rand(1,2);
		} else {
			$ewd = 0;
		}
		$result3 = $db->Execute("INSERT INTO $dbtables[ships] (`ship_id`, `player_id`, `type`, `ship_name`, `ship_destroyed`, `hull`, `engines`, `power`, `computer`, `sensors`, `beams`, `torp_launchers`, `torps`, `shields`, `armour`, `armour_pts`, `cloak`, `sector`, `ship_ore`, `ship_organics`, `ship_goods`, `ship_energy`, `ship_colonists`, `ship_fighters`, `tow`, `on_planet`, `dev_warpedit`, `dev_genesis`, `dev_beacon`, `dev_emerwarp`, `dev_escapepod`, `dev_fuelscoop`, `dev_minedeflector`, `planet_id`, `cleared_defences`, `dev_lssd`, `dev_sectorwmd`) VALUES ('', $player_id, '$shiptype', '$shipname', 'N', $furlevel,$furlevel,$furlevel,$furlevel,$furlevel,$furlevel,$furlevel,$maxtorps,$furlevel,$furlevel,$maxarmour,$furlevel, $sector,0,0,0,$maxenergy,0,$maxfighters, '0', 'N', '0', '0', '0', '$ewd', 'N', 'N', '0', '0', NULL, 'N', 'N')"); 
         $result4 = $db->Execute("UPDATE $dbtables[players] SET currentship=LAST_INSERT_ID() WHERE player_id=$player_id");
          if(!$result2 | !result3) {
            echo $db->ErrorMsg() . "<br>";
          } else {
            echo "Furangee has been created.<BR><BR>";
            echo "Password has been set.<BR><BR>";
            echo "Ship Records have been updated.<BR><BR>";
          }
		  // Choose a preferance
		  $commods = array("ore","goods","organics");
		  $prefer = $commods[rand(0,2)];
          $result3 = $db->Execute("INSERT INTO $dbtables[furangee] (furangee_id,active,aggression,orders,prefer) VALUES('$emailname','$_active','$aggression','$orders','$prefer')");
          if(!$result3) {
            echo $db->ErrorMsg() . "<br>";
          } else {
            echo "Furangee Records have been updated.<BR><BR>";
          }
        }
        echo "<INPUT TYPE=SUBMIT VALUE=\"Return to Furangee Creator \">";
        $button_main = false;
      }
      else
      {
        echo "Invalid operation";
      }
      echo "<INPUT TYPE=HIDDEN NAME=menu VALUE=createnew>";
      echo "<INPUT TYPE=HIDDEN NAME=swordfish VALUE=$swordfish>";
      echo "</FORM>";
    }
    elseif($module == "furangeeturns")
    {
      echo "<span style=\"font-family : courier, monospace; font-size: 12pt; color: #00FF00 \">Furangee Editor</span><BR>";
      echo "<FORM ACTION=furangee_control.php METHOD=POST>";
      if(empty($turns))
      {
	  	echo "How many turns should the furangee have played?<br>";
		echo "<INPUT TYPE=text SIZE=5 name=turns>";
		echo "<INPUT TYPE=HIDDEN NAME=menu VALUE=furangeeturns>";
		echo "<INPUT TYPE=HIDDEN NAME=swordfish VALUE=$swordfish>";
		echo "<INPUT TYPE=submit VALUE=Set>";
		echo "</FORM>";
      }
	  else 
	  {
        $res = $db->Execute("SELECT email FROM $dbtables[players] JOIN $dbtables[furangee] WHERE email=furangee_id");
        while(!$res->EOF)
        {
          $row=$res->fields;
		  echo "UPDATE $dbtables[players] SET turns_used=$turns WHERE email='$row[email]'<br>";
          $db->Execute("UPDATE $dbtables[players] SET turns_used=$turns WHERE email='$row[email]'");
          $res->MoveNext();
        }
        echo "Turns Set to $turns<br>";
	  }
	}
	else
	{
      echo "Unknown function";
    }

    if($button_main)
    {
      echo "<BR><BR>";
      echo "<FORM ACTION=furangee_control.php METHOD=POST>";
      echo "<INPUT TYPE=HIDDEN NAME=swordfish VALUE=$swordfish>";
      echo "<INPUT TYPE=SUBMIT VALUE=\"Return to main menu\">";
      echo "</FORM>";
    }
  }
}
  
include("footer.php");

?> 
