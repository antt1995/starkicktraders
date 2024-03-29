<?
/******************************************************************
* Explanation of the scheduler                                    *
*                                                                 *
* Here are the scheduler DB fields, and what they are used for :  *
*  - sched_id : Unique ID. Before calling the file responsible    *
*    for the event, the variable $sched_var_id will be set to     *
*    this value, so the called file can modify the triggering     *
*    scheduler entry if it needs to.                              *
*                                                                 *
*  - loop : Set this to 'Y' if you want the event to be looped    *
*    endlessly. If this value is set to 'Y', the 'spawn' field is *
*    not used.                                                    *
*                                                                 *
*  - ticks_left : Used internally by the scheduler. It represents *
*    the number of mins elapsed since the last call. ALWAYS set   *
*    this to 0 when scheduling a new event.                       *
*                                                                 *
*  - ticks_full : This is the interval in minutes between         *
*    different runs of your event. Set this to the frenquency     *
*    you wish the event to happen. For example, if you want your  *
*    event to be run every three minutes, set this to 3.          *
*                                                                 *
*  - spawn : If you want your event to be run a certain number of *
*    times only, set this to the number of times. For this to     *
*    work, loop must be set to 'N'. When the event has been run   *
*    spawn number of times, it is deleted from the scheduler.     *
*                                                                 *
*  - file : This is the file that will be called when an event    *
*    has been trigerred.                                          *
*                                                                 *
*  - extra_info : This is a text variable that can be used to     *
*    store any extra information concerning the event triggered.  *
*    It will be made available to the called file through the     *
*    variable $sched_var_extrainfo.                               *
*                                                                 *
* If you are including files in your trigger file, it is important*
* to use include_once() instead of include(), as your file might  *
* be called multiple times in a single execution. If you need to  *
* define functions, you can put them in the sched_funcs.php file  *
* that is included by the scheduler. Else put them in your own    *
* include file, with an include statement. THEY CANNOT BE    *
* DEFINED IN YOUR MAIN FILE BODY. This would cause PHP to issue a *
* multiple function declaration error.                            *
*                                                                 *
* End of scheduler explanation                                    *
******************************************************************/

require_once("config.php");
if ($sched_type == 1) ob_start();
$title="System Update";

//include("header.php");
?>
<!doctype html public "-//w3c//dtd html 3.2//en">
<html>
<head>
<META HTTP-EQUIV="Expires" CONTENT="-1">
<meta http-equiv="Pragma" content="no_cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no cache">
<?php

if (isset($swordfish)) {
	echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"120;URL=http://www.mpgames.com".$PHP_SELF."?swordfish=$swordfish\">";
}
echo "<body background=\"images/bgoutspace1.gif\" bgcolor=\"#000000\" text=\"#c0c0c0\" link=\"#00ff00\" vlink=\"#00ff00\" alink=\"#ff0000\">";
echo "<title>$title</title></head>";
connectdb();

bigtitle();

require_once("sched_funcs.php");

srand((double)microtime() * 1000000);

if($swordfish != $adminpass)
{
  echo "<FORM ACTION=scheduler.php METHOD=POST>";
  echo "Password: <INPUT TYPE=PASSWORD NAME=swordfish SIZE=20 MAXLENGTH=20><BR><BR>";
  echo "<INPUT TYPE=SUBMIT VALUE=Submit><INPUT TYPE=RESET VALUE=Reset>";
  echo "</FORM>";
}
else
{
  $starttime=time();
  echo "Server time: ".date ("l dS of F Y h:i:s A")."<br>";
  $sched_res = $db->Execute("SELECT * FROM $dbtables[scheduler]");
  if($sched_res)
  {
     while (!$sched_res->EOF)
     {
       $event = $sched_res->fields;
	   echo "Last run ";
	   echo date ("l dS of F Y h:i:s A",$event[last_run])."<br>";
       $multiplier = ($sched_ticks / $event[ticks_full]) + ($event[ticks_left] / $event[ticks_full]);
       $multiplier = (int) $multiplier;
       $ticks_left = ($sched_ticks + $event[ticks_left]) % $event[ticks_full];

       if($event[loop] == 'N')
       {
         if($multiplier > $event[spawn])
           $multiplier = $event[spawn];
      
         if($event[spawn] - $multiplier == 0)
           $db->Execute("DELETE FROM $dbtables[scheduler] WHERE sched_id=$event[sched_id]");
         else
           $db->Execute("UPDATE $dbtables[scheduler] SET ticks_left=$ticks_left, spawn=spawn-$multiplier WHERE sched_id=$event[sched_id]");
       }
       else
         $db->Execute("UPDATE $dbtables[scheduler] SET ticks_left=$ticks_left WHERE sched_id=$event[sched_id]");
  
       $sched_var_id = $event[sched_id];
       $sched_var_extrainfo = $event[extra_info];

       $sched_i = 0;
       while($sched_i < $multiplier)
       {
         include("$event[file]");
         $sched_i++;
       }
       $sched_res->MoveNext();
     }
   }
  
  $runtime= time() - $starttime;
  echo "<p>The scheduler took $runtime seconds to execute.<p>";

  include("footer.php");
  if($sched_type == 1) ob_end_clean();
  if($sched_type != 1)
  {
    $db->Execute("UPDATE $dbtables[scheduler] SET last_run=". TIME());
  }
}
