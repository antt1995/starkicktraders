<?
include("config.php");
updatecookie();

include("languages/$lang");
$title=$l_by_title;
include("header.php");

connectdb();

if(checklogin())
{
  die();
}

//-------------------------------------------------------------------------------------------------


$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;

switch($response) {
   case "display":
      bigtitle();
      $res5 = $db->Execute("SELECT * FROM $dbtables[players],$dbtables[bounty] WHERE bounty_on = player_id AND bounty_on = $bounty_on");
      $j = 0;
      if($res5)
      {
         while(!$res5->EOF)
         {
            $bounty_details[$j] = $res5->fields;
            $j++;
            $res5->MoveNext();
         } 
      }

      $num_details = $j;
      if($num_details < 1)
      {
         echo "$l_by_nobounties<BR>";
      }
      else
      {
         echo "$l_by_bountyon " . $bounty_details[0][character_name];
         echo '<table border=1 cellspacing=1 cellpadding=2 width="50%" align=center>';
         echo "<TR BGCOLOR=\"$color_header\">";
         echo "<TD><B>$l_amount</TD>";
         echo "<TD><B>$l_by_placedby</TD>";
         echo "<TD><B>$l_by_action</TD>";
         echo "</TR>";
         $color = $color_line1;
         for($j=0; $j<$num_details; $j++)
         {
            $someres = $db->execute("SELECT character_name FROM $dbtables[players] WHERE player_id = " . $bounty_details[$j][placed_by]);
            $details = $someres->fields;
            echo "<TR BGCOLOR=\"$color\">";
            echo "<TD>" . NUMBER($bounty_details[$j]['amount']) . "</TD>";
            if($bounty_details[$j][placed_by] == 0)
            {
               echo "<TD>$l_by_thefeds</TD>";
            }
            else
            {
               echo "<TD>" . $details['character_name'] . "</TD>";
            }
            if($bounty_details[$j][placed_by] == $playerinfo[player_id])
            {
               echo "<TD><A HREF=bounty.php?bid=" . $bounty_details[$j][bounty_id] . "&response=cancel>$l_by_cancel</A></TD>";
            }
            else
            {
               echo "<TD> </TD>";
            }
          
            echo "</TR>";

            if($color == $color_line1)
            {
               $color = $color_line2;
            }
            else
            {
               $color = $color_line1;
            }
         }
         echo "</TABLE>";
      }
      break;
   case "cancel":
      bigtitle();
      if ($playerinfo[turns]<1 )
      {
	echo "$l_by_noturn<BR><BR>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
      }
      
      $res = $db->Execute("SELECT * from $dbtables[bounty] WHERE bounty_id = $bid");
      if(!res)
      {
      	echo "$l_by_nobounty<BR><BR>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
      }
      $bty = $res->fields;
      if($bty[placed_by] <> $playerinfo[player_id])
      {
      	echo "$l_by_notyours<BR><BR>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
      }
      $del = $db->Execute("DELETE FROM $dbtables[bounty] WHERE bounty_id = $bid");      
      $stamp = date("Y-m-d H-i-s");
      $refund = $bty['amount'];
      $db->Execute("UPDATE $dbtables[players] SET last_login='$stamp',turns=turns-1, turns_used=turns_used+1, credits=credits+$refund where player_id=$playerinfo[player_id]");
      echo "$l_by_canceled<BR>";
      TEXT_GOTOMAIN();
      die();
      break;
   case "place":
      bigtitle();
      $bounty_on = stripnum($bounty_on);
      $ex = $db->Execute("SELECT * from $dbtables[players] WHERE player_id = $bounty_on AND player_id > 0");
      if(!$ex)
      {
	echo "$l_by_notexists<BR><BR>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
      }
      $bty = $ex->fields;
      if ($bty[ship_destroyed] == "Y")
      {
	echo "$l_by_destroyed<BR><BR>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
      }
      if ($playerinfo[turns]<1 )
      {
	echo "$l_by_noturn<BR><BR>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
      }
      $amount = stripnum($amount);
      if($amount < 0)
      {
         echo "$l_by_zeroamount<BR><BR>";
         TEXT_GOTOMAIN();
         include("footer.php");
         die();
      }
      if($bounty_on == $playerinfo['player_id'])
      {
         echo "$l_by_yourself<BR><BR>";
         TEXT_GOTOMAIN();
         include("footer.php");
         die();
      }
      if($amount > $playerinfo['credits'])
      {
         echo "$l_by_notenough<BR><BR>";
         TEXT_GOTOMAIN();
         include("footer.php");
         die();
      }
      if($bounty_maxvalue != 0)
      {
         $percent = $bounty_maxvalue * 100;
         $score = gen_score($playerinfo[player_id]);
         $maxtrans = $score * $score * $bounty_maxvalue;
         $previous_bounty = 0;
         $pb = $db->Execute("SELECT SUM(amount) AS totalbounty FROM $dbtables[players] WHERE bounty_on = $bounty_on AND placed_by = $playerinfo[player_id]");
         if($pb)
         {
            $prev = $pb->fields;
            $previous_bounty = $prev[totalbounty];
         }
         if($amount + previous_bounty > $maxtrans)
         {   
            echo "$l_by_toomuch<BR><BR>";
            TEXT_GOTOMAIN();
            include("footer.php");
            die();
         }

      }
      $insert = $db->Execute("INSERT INTO $dbtables[bounty] (bounty_on,placed_by,amount) values ($bounty_on, $playerinfo[player_id] ,$amount)");      
      $stamp = date("Y-m-d H-i-s");
      $db->Execute("UPDATE $dbtables[players] SET last_login='$stamp',turns=turns-1, turns_used=turns_used+1, credits=credits-$amount where player_id=$playerinfo[player_id]");
      echo "$l_by_placed<BR>";
      TEXT_GOTOMAIN();
      die();
      break;
   default:
      bigtitle();
      $res = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id != $playerinfo[player_id] AND player_id > 1 AND turns_used > 10 AND email NOT LIKE '%@furangee' ORDER BY character_name ASC");
      echo "<FORM ACTION=bounty.php METHOD=POST>";
      echo "<TABLE>";
      echo "<TR><TD>$l_by_bountyon</TD><TD><SELECT NAME=bounty_on>";
      while(!$res->EOF)
      {
         if(isset($bounty_on) && $bounty_on == $res->fields[player_id])
            $selected = "selected";
         else
            $selected = "";
         $charname = $res->fields[character_name];
         $player_id = $res->fields[player_id];
         echo "<OPTION VALUE=$player_id $selected>$charname</OPTION>";
         $res->MoveNext();
      }
      echo "</SELECT></TD></TR>";
      echo "<TR><TD>$l_by_amount:</TD><TD><INPUT TYPE=TEXT NAME=amount SIZE=20 MAXLENGTH=20></TD></TR>";
      echo "<TR><TD></TD><TD><INPUT TYPE=SUBMIT VALUE=$l_by_place><INPUT TYPE=RESET VALUE=Clear></TD>";
      echo "</TABLE>";
      echo "<input type=hidden name=response value=place>";
      echo "</FORM>";

      $result3 = $db->Execute ("SELECT bounty_on, SUM(amount) as total_bounty FROM $dbtables[bounty] WHERE placed_by != 0 GROUP BY bounty_on");

      $i = 0;
      if($result3)
      {
         while(!$result3->EOF)
         {
            $bounties[$i] = $result3->fields;
            $i++;
            $result3->MoveNext();
         } 
      }

      $num_bounties = $i;
      if($num_bounties < 1)
      {
         echo "$l_by_nobounties<BR>";
      }
      else
      {
         echo $l_by_moredetails . "<BR><BR>";
         echo "<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=2>";
         echo "<TR BGCOLOR=\"$color_header\">";
         echo "<TD><B>$l_by_bountyon</B></TD>";
         echo "<TD><B>$l_amount</TD>";
         echo "</TR>";
         $color = $color_line1;
         for($i=0; $i<$num_bounties; $i++)
         {
            $someres = $db->execute("SELECT character_name FROM $dbtables[players] WHERE player_id = " . $bounties[$i][bounty_on]);
            $details = $someres->fields;
            echo "<TR BGCOLOR=\"$color\">";
            echo "<TD><A HREF=bounty.php?bounty_on=" . $bounties[$i][bounty_on] . "&response=display>". $details[character_name] ."</A></TD>";
            echo "<TD>" .NUMBER( $bounties[$i][total_bounty]) . "</TD>";
            echo "</TR>";

            if($color == $color_line1)
            {
               $color = $color_line2;
            }
            else
            {
               $color = $color_line1;
            }
        }
        echo "</TABLE><br><br>";
    }
// Fines!
    $result3 = $db->Execute ("SELECT SUM(amount),character_name FROM $dbtables[bounty],$dbtables[players] WHERE placed_by=0 AND player_id=bounty_on GROUP BY character_name ORDER BY `SUM(amount)` ASC");
	echo "<h1>Universe Most Wanted</h1>";
	echo "Destroy any ship owned by these fugitives and you will get <b>triple the salvage rate!</b><br><br>";
	$fugitives = 0;
    while(!$result3->EOF)
    {
		$fugitives++; 
	  	$outlaw = $result3->fields;
        echo "<b>$fugitives. $outlaw[character_name]</b><br>";
        $result3->MoveNext();
		
    }
    echo "<BR><BR>";
	if ($fugitives==0)
		echo "No fugitives from the Federation yet.<br>";
    break;
}


//-------------------------------------------------------------------------------------------------

TEXT_GOTOMAIN();

include("footer.php");

?>
