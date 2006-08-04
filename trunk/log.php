<?

include("config.php");
updatecookie();

include("languages/$lang");

$title="$l_log_titlet";
$no_body=1;
include("header.php");

connectdb();
if(checklogin())
{
  die();
}
$res = $db->Execute("SELECT character_name, player_id, alert2 FROM $dbtables[players] WHERE email='$username'") or die(mysql_error());
$playerinfo = $res->fields;

if($swordfish == $adminpass) //check if called by admin script
{
  $playerinfo[player_id] = $player;
  
  if($player == 0)
    $playerinfo[character_name] = 'Administrator';
  else
  {
    $res = $db->Execute("SELECT character_name FROM $dbtables[players] WHERE player_id=$player");
    $targetname = $res->fields;
    $playerinfo[character_name] = $targetname[character_name];
  }
}

eregi ("ozilla.(.)", $HTTP_USER_AGENT, $mozver);
eregi ("MSIE.(.)", $HTTP_USER_AGENT, $iever);

if(!empty($iever[1]))
{
  if($iever[1] < 5) // Using IE 2.x, 3.x or 4.x
    $mode = 'compat';
  else              // Using IE 5 and up
    $mode = 'full';
}
else
{
  if($mozver[1] < 5) // Using Netscape 3.x or 4.x
    $mode = 'compat';
  else               // Using N6 or Mozilla
    $mode = 'moz';
}

//if($playerinfo[alert2] == 'N') //If player doesn't want alert2, let's do the same as if Nestcape 4
  $mode = 'compat';

if($mode != 'compat')
{
  echo '
        <style type="text/css">
        a:hover {text-decoration:underline; color:#cccccc;}
        body {background-color:#ffffff; color:#999999; font-size:11px; font-family:verdana,arial,helvetica,sans-serif; line-height:15px;}
       ';
}

if($screenres == 640)
  $yres = 270;
elseif($screenres == 800)
  $yres = 390;
elseif($screenres >= 1024)
  $yres = 558;

if($mode == 'full')
  echo "#divScroller1 {position:relative; overflow:hidden; overflow-y:scroll; z-index:9; left:0px; top:0px; width:100%; height:${yres}px; visbility:visible; border-width:1px 1px 1px 1px; border-color:#C6D6E7; border-style:solid; scrollbar-track-color: #DEDEEF; scrollbar-face-color:#040658; scrollbar-arrow-color:#DEDEEF}";
elseif($mode == 'moz')
  echo "#divScroller1 {position:relative; overflow:visible; overflow-y:scroll; z-index:9; left:0px; top:0px; width:100%; height:${yres}px; visbility:visible; scrollbar-track-color: #DEDEEF; scrollbar-face-color:#040658; scrollbar-arrow-color:#DEDEEF}";

if($mode != compat)
{
  echo '
        .dynPage {position:absolute; z-index:10; left:0px; top:0px; width:100%; visibility:hidden; line-height:14px; color:black}
        </style>
        <script language="JavaScript" type="text/javascript">
        /**********************************************************************************   
        PageSlideFade 
        *   Copyright (C) 2001 <a href="/alert2central/michael_van_ouwerkerk.asp">Michael van Ouwerkerk</a>
        *   This script was released at alert2Central.com
        *   Visit for more great scripts!
        *   This may be used and changed freely as long as this msg is intact!
        *   We will also appreciate any links you could give us.
        *
        *   Made by <a href="/alert2central/michael_van_ouwerkerk.asp">Michael van Ouwerkerk</a> 
        *********************************************************************************/
        function lib_bwcheck(){ //Browsercheck (needed)
        	this.ver=navigator.appVersion
        	this.agent=navigator.userAgent
        	this.dom=document.getElementById?1:0
        	this.opera5=(navigator.userAgent.indexOf("Opera")>-1 && document.getElementById)?1:0
        	this.ie5=(this.ver.indexOf("MSIE 5")>-1 && this.dom && !this.opera5)?1:0; 
        	this.ie6=(this.ver.indexOf("MSIE 6")>-1 && this.dom && !this.opera5)?1:0;
        	this.ie4=(document.all && !this.dom && !this.opera5)?1:0;
        	this.ie=this.ie4||this.ie5||this.ie6
        	this.mac=this.agent.indexOf("Mac")>-1
        	this.ns6=(this.dom && parseInt(this.ver) >= 5) ?1:0; 
        	this.ns4=(document.layers && !this.dom)?1:0;
        	this.bw=(this.ie6 || this.ie5 || this.ie4 || this.ns4 || this.ns6 || this.opera5)
        	return this
        }
        var bw=new lib_bwcheck()

        /*** variables to configure... ***/

        var numScrollPages = 3         //Set the number of pages (layers) here. Add and remove the pages in the body too. The first layer is called dynPage0, the second is dynPage1, and so on.
        var transitionOut = 1;         //The out effect... 0= no effect, 1= fade
        var transitionIn = 2;          //The in effect... 0= no effect, 1= fade, 2= slide
        var slideAcceleration = 0.2;   //If you use the slide animation, set this somewhere between 0 and 1.
        var transitionOnload = 1       //Use the in transition when the page first loads? If you want the transition fx only when the links are clicked, you can set it to 0.

        // NOTE: if you change the position of divScroller1 from absolute to relative, you can put the scroller in a table.
        // HOWEVER it will no longer work in netscape 4. If you wish to support netscape 4, you have to use absolute positioning.

        // Please note that there are no effects available in ns4 and ie4, or explorers on the Mac!

        /*** There should be no need to change anything beyond this. ***/ 

        // A unit of measure that will be added when setting the position of a layer.
        var px = bw.ns4||window.opera?"":"px";

        if(document.layers){ //NS4 resize fix...
        	scrX= innerWidth; scrY= innerHeight;
        	onresize= function(){if(scrX!= innerWidth || scrY!= innerHeight){history.go(0)} }
        }

        //object constructor...
        function scrollerobj(obj,nest){
        	nest = (!nest)?"":\'document.\'+nest+\'.\'
        	this.elm = bw.ie4?document.all[obj]:bw.ns4?eval(nest+\'document.\'+obj):document.getElementById(obj)
        	this.css = bw.ns4?this.elm:this.elm.style
        	this.doc = bw.ns4?this.elm.document:document
        	this.obj = obj+\'scrollerobj\'; eval(this.obj+\'=this\')
        	this.x = (bw.ns4||bw.opera5)?this.css.left:this.elm.offsetLeft
        	this.y = (bw.ns4||bw.opera5)?this.css.top:this.elm.offsetTop
        	this.w = (bw.ie4||bw.ie5||bw.ie6||bw.ns6)?this.elm.offsetWidth:bw.ns4?this.elm.clip.width:bw.opera5?this.css.pixelWidth:0
        	this.h = (bw.ie4||bw.ie5||bw.ie6||bw.ns6)?this.elm.offsetHeight:bw.ns4?this.elm.clip.height:bw.opera5?this.css.pixelHeight:0
        }

        //object methods...
        scrollerobj.prototype.moveTo = function(x,y){
        	if(x!=null){this.x=x; this.css.left=x+px}
        	if(y!=null){this.y=y; this.css.top=y+px}
        }
        scrollerobj.prototype.moveBy = function(x,y){this.moveTo(this.x+x,this.y+y)}
        scrollerobj.prototype.hideIt = function(){this.css.visibility=\'hidden\'}
        scrollerobj.prototype.showIt = function(){this.css.visibility=\'visible\'}

        /****************************************************************
        scroll functions...
        ****************************************************************/
        var scrollTimer = null;
        function scroll(step){
        	clearTimeout(scrollTimer);
        	if ( !busy && (step<0&&activePage.y+activePage.h>scroller1.h || step>0&&activePage.y<0) ){
        		activePage.moveBy(0,step);
        		scrollTimer = setTimeout(\'scroll(\'+step+\')\',40);
        	}
        }
        function stopScroll(){
        	clearTimeout(scrollTimer);
        }

        /****************************************************************
        activating the correct layers...
        ****************************************************************/
        var activePage = null;
        var busy = 0;
        function activate(num){
        	if (activePage!=pages[num] && !busy){
        		busy = 1;
        		if (transitionOut==0 || !bw.opacity){ activePage.hideIt(); activateContinue(num); }
        		else if (transitionOut==1) activePage.blend(\'hidden\', \'activateContinue(\'+num+\')\');
        	}
        }
        function activateContinue(num){
        	busy = 1;
        	activePage = pages[num];
        	if (transitionIn==0 || !bw.opacity){ activePage.showIt(); busy=0; }
        	else if (transitionIn==1) activePage.blend(\'visible\', \'busy=0\');
        	else if (transitionIn==2) activePage.slide(0, slideAcceleration, 40, \'busy=0\');
        }


        /****************************************************************
        Slide methods...
        ****************************************************************/
        scrollerobj.prototype.slide = function(target, acceleration, time, fn){
        	this.slideFn= fn?fn:null;
        	this.moveTo(0,scroller1.h);
        	if (bw.ie4&&!bw.mac) this.css.filter = \'alpha(opacity=100)\';
        	if (bw.ns6) this.css.MozOpacity = 1;
        	this.showIt();
        	this.doSlide(target, acceleration, time);
        }
        scrollerobj.prototype.doSlide = function(target, acceleration, time){
        	this.step = Math.round(this.y*acceleration);
        	if (this.step<1) this.step = 1;
        	if (this.step>this.y) this.step = this.y;
        	this.moveBy(0, -this.step);
        	if (this.y>0) this.slideTim = setTimeout(this.obj+\'.doSlide(\'+target+\',\'+acceleration+\',\'+time+\')\', time);
        	else {	
        		eval(this.slideFn);
        		this.slideFn = null;
        	}
        }


        /****************************************************************
        Opacity methods...
        ****************************************************************/
        scrollerobj.prototype.blend= function(vis, fn){
        	if (bw.ie5||bw.ie6 && !bw.mac) {
        		if (vis==\'visible\') this.css.filter= \'blendTrans(duration=0.2)\';
        		else this.css.filter= \'blendTrans(duration=0.2)\';
        		this.elm.onfilterchange = function(){ eval(fn); };
        		this.elm.filters.blendTrans.apply();
        		this.css.visibility= vis;
        		this.elm.filters.blendTrans.play();
        	}
        	else if (bw.ns6 || bw.ie&&!bw.mac){
        		this.css.visibility= \'visible\';
        		vis==\'visible\' ? this.fadeTo(100, 15, 10, fn) : this.fadeTo(0, 15, 10, fn);
        	}
        	else {
        		this.css.visibility= vis;
        		eval(fn);
        	}
        };

        scrollerobj.prototype.op= 100;
        scrollerobj.prototype.opacityTim= null;
        scrollerobj.prototype.setOpacity= function(num){
        	this.css.filter= \'alpha(opacity=\'+num+\')\';
        	this.css.MozOpacity= num/100;
        	this.op= num;
        }
        scrollerobj.prototype.fadeTo= function(target, step, time, fn){
        	clearTimeout(this.opacityTim);
        	this.opacityFn= fn?fn:null;
        	this.op = target==100 ? 0 : 100;
        	this.fade(target, step, time);
        }
        scrollerobj.prototype.fade= function(target, step, time){
        	if (Math.abs(target-this.op)>step){
        		target>this.op? this.setOpacity(this.op+step):this.setOpacity(this.op-step);
        		this.opacityTim= setTimeout(this.obj+\'.fade(\'+target+\',\'+step+\',\'+time+\')\', time);
        	}
        	else {
        		this.setOpacity(target);
        		eval(this.opacityFn);
        		this.opacityFn= null;
        	}
        }


        /**************************************************************
        Init function...
        **************************************************************/
        var pageslidefadeLoaded = 0;
        function initPageSlideFade(){
        	scroller1 = new scrollerobj(\'divScroller1\');
	
        	pages = new Array();
        	for (var i=0; i<numScrollPages; i++){
        		pages[i] = new scrollerobj(\'dynPage\'+i, \'divScroller1\');
        		pages[i].moveTo(0,0);
        	}
        	bw.opacity = ( bw.ie && !bw.ie4 && navigator.userAgent.indexOf(\'Windows\')>-1 ) || bw.ns6
        	if (bw.ie5||bw.ie6 && !bw.mac) pages[0].css.filter= \'blendTrans(duration=0.6)\'; // Preloads the windows filters.
	
        	if (transitionOnload) activateContinue(0);
        	else{
        		activePage = pages[0];
        		activePage.showIt();
        	}

        	if (bw.ie) for(var i=0;i<document.links.length;i++) document.links[i].onfocus=document.links[i].blur;
        	pageslidefadeLoaded = 1;
        }
        //if the browser is ok, the script is started onload..
        if(bw.bw && !pageslidefadeLoaded) onload = initPageSlideFade;
        </script>
        ';
}

		
echo '<BODY BACKGROUND="images/bgoutspace1.gif" bgcolor=#000000 text="#c0c0c0" link="#040658" vlink="#040658" alink="#040658">';

echo '<center>';

echo "<table width=80% border=0 cellspacing=0 cellpadding=0>";

$logline = str_replace("[player]", "$playerinfo[character_name]", $l_log_log);
?>

<tr><td><td width=100%><td></tr>
<tr><td><td height=20 style="background-image: url(images/top_panel.gif); background-repeat:no-repeat">
<font size=2 color=#040658><b>&nbsp;&nbsp;&nbsp;<? echo $logline; ?></b></font>
</td><td><td>&nbsp;</tr>
<tr><td valign=bottom>

<?
if($mode == 'moz')
  echo '<td colspan=2 style="border-width:1px 1px 1px 1px; border-color:#C6D6E7; border-style:solid;" bgcolor=#63639C>';
elseif($mode == 'full')
  echo '<td colspan=2 bgcolor=#63639C>';
else
  echo "<td colspan=2><table border=1 width=100%><tr><td  bgcolor=#63639C>";

if(empty($startdate))
  $startdate = date("Ymd");

$res = $db->Execute("SELECT * FROM $dbtables[logs] WHERE player_id=$playerinfo[player_id] AND time LIKE '$startdate%' ORDER BY time DESC, type DESC") or die(mysql_error());
while(!$res->EOF)
{
  $logs[] = $res->fields;
  $res->MoveNext();
}

$entry = $l_log_months[substr($startdate, 4, 2) - 1] . " " . substr($startdate, 6, 2) . " " . substr($startdate, 0, 4);

echo "<div id=\"divScroller1\">" .
     "\n<div id=\"dynPage0\" class=\"dynPage\">" .
     "<center>" .
     "<br>" .
     "<font size=2 color=#DEDEEF><b>$l_log_start $entry<b></font>" .
     "<p>" .
     "<hr width=80% size=1 NOSHADE style=\"color: #040658\">" .
     "</center>";

if(!empty($logs))
{
  foreach($logs as $log)
  {
    $event = log_parse($log);
    $time = $l_log_months[substr($log[time], 4, 2) - 1] . " " . substr($log[time], 6, 2) . " " . substr($log[time], 0, 4) . " " . substr($log[time], 8, 2) . ":" . substr($log[time], 10, 2);

    echo "<table border=0 cellspacing=5 width=100%>" .
         "<tr>" .
         "<td><font size=2 color=#040658><b>$event[title]</b></td>" .
         "<td align=right><font size=2 color=#040658><b>$time</b></td>" .
         "<tr><td colspan=2>" .
         "<font size=2 color=#DEDEEF>" .
         "$event[text]" .
         "</td></tr>" .
         "</table>" .
         "<center><hr width=80% size=1 NOSHADE style=\"color: #040658\"></center>";
  }
}

echo "<center>" .
     "<br>" .
     "<font size=2 color=#DEDEEF><b>$l_log_end $entry<b></font>" .
     "<p>" .
     "</center>" .
     "</div>\n";

$month = substr($startdate, 4, 2);
$day = substr($startdate, 6, 2) - 1;
$year = substr($startdate, 0, 4);

$yesterday = mktime (0,0,0,$month,$day,$year);
$yesterday = date("Ymd", $yesterday);

$day = substr($startdate, 6, 2) - 2;

$yesterday2 = mktime (0,0,0,$month,$day,$year);
$yesterday2 = date("Ymd", $yesterday2);

if($mode == 'compat')
  echo "</td></tr></table>";

if($mode != 'compat')
{
  $entry = $l_log_months[substr($yesterday, 4, 2) - 1] . " " . substr($yesterday, 6, 2) . " " . substr($yesterday, 0, 4);

  unset($logs);
  $res = $db->Execute("SELECT * FROM $dbtables[logs] WHERE player_id=$playerinfo[player_id] AND time LIKE '$yesterday%' ORDER BY time DESC, type DESC");
  while(!$res->EOF)
  {
    $logs[] = $res->fields;
    $res->MoveNext();
  }

  echo "<div id=\"dynPage1\" class=\"dynPage\">" .
       "<center>" .
       "<br>" .
       "<font size=2 color=#DEDEEF><b>$l_log_start $entry<b></font>" .
       "<p>" .
       "</center>" .
       "<hr width=80% size=1 NOSHADE style=\"color: #040658\">";

  if(!empty($logs))
  {
    foreach($logs as $log)
    {
      $event = log_parse($log);
      $time = $l_log_months[substr($log[time], 4, 2) - 1] . " " . substr($log[time], 6, 2) . " " . substr($log[time], 0, 4) . " " . substr($log[time], 8, 2) . ":" . substr($log[time], 10, 2);

      echo "<table border=0 cellspacing=5 width=100%>" .
           "<tr>" .
           "<td><font size=2 color=#040658><b>$event[title]</b></td>" .
           "<td align=right><font size=2 color=#040658><b>$time</b></td>" .
           "<tr><td colspan=2>" .
           "<font size=2 color=#DEDEEF>" .
           "$event[text]" .
           "</td></tr>" .
           "</table>" .
           "<hr width=80% size=1 NOSHADE style=\"color: #040658\">";
    }
  }

  echo "<center>" .
       "<br>" .
       "<font size=2 color=#DEDEEF><b>$l_log_end $entry<b></font>" .
       "<p>" .
       "</center>" .
       "</div>\n";

  $entry = $l_log_months[substr($yesterday2, 4, 2) - 1] . " " . substr($yesterday2, 6, 2) . " " . substr($yesterday2, 0, 4);

  unset($logs);
  $res = $db->Execute("SELECT * FROM $dbtables[logs] WHERE player_id=$playerinfo[player_id] AND time LIKE '$yesterday2%' ORDER BY time DESC, type DESC");
  while(!$res->EOF)
  {
    $logs[] = $res->fields;
    $res->MoveNext();
  }

  echo "<div id=\"dynPage2\" class=\"dynPage\">" .
       "<center>" .
       "<br>" .
       "<font size=2 color=#DEDEEF><b>$l_log_start $entry<b></font>" .
       "<p>" .
       "</center>" .
       "<hr width=80% size=1 NOSHADE style=\"color: #040658\">";

  if(!empty($logs))
  {
    foreach($logs as $log)
    {
      $event = log_parse($log);
      $time = $l_log_months[substr($log[time], 4, 2) - 1] . " " . substr($log[time], 6, 2) . " " . substr($log[time], 0, 4) . " " . substr($log[time], 8, 2) . ":" . substr($log[time], 10, 2);

      echo "<table border=0 cellspacing=5 width=100%>" .
           "<tr>" .
           "<td><font size=2 color=#040658><b>$event[title]</b></td>" .
           "<td align=right><font size=2 color=#040658><b>$time</b></td>" .
           "<tr><td colspan=2>" .
           "<font size=2 color=#DEDEEF>" .
           "$event[text]" .
           "</td></tr>" .
           "</table>" .
           "<hr width=80% size=1 NOSHADE style=\"color: #040658\">";
    }
  }

  echo "<center>" .
       "<br>" .
       "<font size=2 color=#DEDEEF><b>$l_log_end $entry<b></font>" .
       "<p>" .
       "</center>" .
       "</div>";

}

echo "</div>";

$date1 = $l_log_months_short[substr($startdate, 4, 2) - 1] . " " . substr($startdate, 6, 2);
$date2 = $l_log_months_short[substr($yesterday, 4, 2) - 1] . " " . substr($yesterday, 6, 2);
$date3 = $l_log_months_short[substr($yesterday2, 4, 2) - 1] . " " . substr($yesterday2, 6, 2);

$month = substr($startdate, 4, 2);
$day = substr($startdate, 6, 2) - 3;
$year = substr($startdate, 0, 4);

$backlink = mktime (0,0,0,$month,$day,$year);
$backlink = date("Ymd", $backlink);

$day = substr($startdate, 6, 2) + 3;

$nextlink = mktime (0,0,0,$month,$day,$year);
if($nextlink > time())
  $nextlink = time();
$nextlink = date("Ymd", $nextlink);

if($startdate == date("Ymd"))
  $nonext = 1;

if($swordfish == $adminpass) //fix for admin log view
  $postlink = "&swordfish=" . urlencode($swordfish) . "&player=$player";
else
  $postlink = "";

if($mode != 'compat')
{
  echo "<td valign=bottom>" .
       "<tr><td><td align=right>" .
       "<img src=images/bottom_panel.gif>" .
       "<br>" .
       "<div style=\"position:relative; top:-23px;\">" .
       "<font size=2><b>" .
       "<a href=log.php?startdate=${backlink}$postlink>««</a>&nbsp;&nbsp;&nbsp;" .
       "<a href=\"#\" onclick=\"activate(2); return false;\" onfocus=\"if(this.blur)this.blur()\">$date3</a>" .
       " | " .
       "<a href=\"#\" onclick=\"activate(1); return false;\" onfocus=\"if(this.blur)this.blur()\">$date2</a>" .
       " | " .
       "<a href=\"#\" onclick=\"activate(0); return false;\" onfocus=\"if(this.blur)this.blur()\">$date1</a>";

  if($nonext != 1)
    echo "&nbsp;&nbsp;&nbsp;<a href=log.php?startdate=${nextlink}$postlink>»»</a>";
     
  echo "&nbsp;&nbsp;&nbsp;";
}
else
{
  echo "<tr><td><td align=right>" .
       "<a href=log.php?startdate=${backlink}$postlink><font color=white size =3><b>««</b></font></a>&nbsp;&nbsp;&nbsp;" .
       "<a href=log.php?startdate=${yesterday2}$postlink><font color=white size=3><b>$date3</b></font></a>" .
       "&nbsp;|&nbsp;" .
       "<a href=log.php?startdate=${yesterday}$postlink><font color=white size=3><b>$date2</b></font></a>" .
       " | " .
       "<a href=log.php?startdate=${startdate}$postlink><font color=white size=3><b>$date1</b></font></a>";

  if($nonext != 1)
    echo "&nbsp;&nbsp;&nbsp;<a href=log.php?startdate=${nextlink}$postlink><font color=white size=3><b>»»</b></font></a>";
     
  echo "&nbsp;&nbsp;&nbsp;";

}

if($swordfish == $adminpass)
  echo "<tr><td><td>" .
       "<FORM action=admin.php method=POST>" .
       "<input type=hidden name=swordfish value=\"$swordfish\">" .
       "<input type=hidden name=menu value=logview>" .
       "<input type=submit value=\"Return to Admin\"></td></tr>";
else
  echo "<tr><td><td><p><font size=2 face=arial>$l_log_click</td></tr>";

if($mode != 'compat')
  echo "<tr><td><td align=center><br><font size=2 color=white>$l_log_note</a>.</td></tr>";

echo "</table>" .
     "</center>";
/*
function log_parse($entry)
{
  global $l_log_title;
  global $l_log_text;
  global $l_log_pod;
  global $l_log_nopod;

  switch($entry[type])
  {
    case LOG_LOGIN: //data args are : [ip]
    case LOG_LOGOUT: 
    case LOG_BADLOGIN:
    case LOG_HARAKIRI:
    $retvalue[text] = str_replace("[ip]", "<font color=white><b>$entry[data]</b></font>", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
  
    case LOG_ATTACK_OUTMAN: //data args are : [player]
    case LOG_ATTACK_OUTSCAN: 
    case LOG_ATTACK_EWD:
    case LOG_ATTACK_EWDFAIL:
    case LOG_SHIP_SCAN:
    case LOG_SHIP_SCAN_FAIL:
    case LOG_FURANGEE_ATTACK:
    case LOG_TEAM_NOT_LEAVE:
    $retvalue[text] = str_replace("[player]", "<font color=white><b>$entry[data]</b></font>", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_ATTACK_LOSE: //data args are : [player] [pod]
    list($name, $pod)= split ("\|", $entry[data]);
        
    $retvalue[text] = str_replace("[player]", "<font color=white><b>$name</b></font>", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    if($pod == 'Y')
      $retvalue[text] = $retvalue[text] . $l_log_pod;
    else
      $retvalue[text] = $retvalue[text] . $l_log_nopod;
    break;

    case LOG_ATTACKED_WIN: //data args are : [player] [armor] [fighters]
    list($name, $armor, $fighters)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[player]", "<font color=white><b>$name</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[armor]", "<font color=white><b>$armor</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[fighters]", "<font color=white><b>$fighters</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TOLL_PAID: //data args are : [toll] [sector]
    case LOG_TOLL_RECV:
    list($toll, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[toll]", "<font color=white><b>$toll</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_HIT_MINES: //data args are : [mines] [sector]
    list($mines, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[mines]", "<font color=white><b>$mines</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_SHIP_DESTROYED_MINES: //data args are : [sector] [pod]
    case LOG_DEFS_KABOOM:
    list($sector, $pod)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    if($pod == 'Y')
      $retvalue[text] = $retvalue[text] . $l_log_pod;
    else
      $retvalue[text] = $retvalue[text] . $l_log_nopod;
    break;

    case LOG_PLANET_DEFEATED_D: //data args are :[planet_name] [sector] [name]
    case LOG_PLANET_DEFEATED:
    case LOG_PLANET_SCAN:
    case LOG_PLANET_SCAN_FAIL:
    list($planet_name, $sector, $name)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[planet_name]", "<font color=white><b>$planet_name</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[name]", "<font color=white><b>$name</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_PLANET_NOT_DEFEATED: //data args are : [planet_name] [sector] [name] [ore] [organics] [goods] [salvage] [credits]
    list($planet_name, $sector, $name, $ore, $organics, $goods, $salvage, $credits)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[planet_name]", "<font color=white><b>$planet_name</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[name]", "<font color=white><b>$name</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[ore]", "<font color=white><b>$ore</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[goods]", "<font color=white><b>$goods</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[organics]", "<font color=white><b>$organics</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[salvage]", "<font color=white><b>$salvage</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[credits]", "<font color=white><b>$credits</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
    
    case LOG_RAW: //data is stored as a message
    $retvalue[title] = $l_log_title[$entry[type]];
    $retvalue[text] = $entry[data];    
    break;

    case LOG_DEFS_DESTROYED: //data args are : [quantity] [type] [sector]
    list($quantity, $type, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[quantity]", "<font color=white><b>$quantity</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[type]", "<font color=white><b>$type</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
  
    case LOG_PLANET_EJECT: //data args are : [sector] [player]
    list($sector, $name)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[name]", "<font color=white><b>$name</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
    
    case LOG_STARVATION: //data args are : [sector] [starvation]
    list($sector, $starvation)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[starvation]", "<font color=white><b>$starvation</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TOW: //data args are : [sector] [newsector] [hull]
    list($sector, $newsector, $hull)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[newsector]", "<font color=white><b>$newsector</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[hull]", "<font color=white><b>$hull</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_DEFS_DESTROYED_F: //data args are : [fighters] [sector]
    list($fighters, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[fighters]", "<font color=white><b>$fighters</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TEAM_REJECT: //data args are : [player] [teamname]
    list($player, $teamname)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[player]", "<font color=white><b>$player</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[teamname]", "<font color=white><b>$teamname</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TEAM_RENAME: //data args are : [team]
    case LOG_TEAM_M_RENAME:
    case LOG_TEAM_KICK:
    case LOG_TEAM_CREATE:
    case LOG_TEAM_LEAVE:
    case LOG_TEAM_LEAD:
    case LOG_TEAM_JOIN:
    case LOG_TEAM_INVITE:
    $retvalue[text] = str_replace("[team]", "<font color=white><b>$entry[data]</b></font>", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TEAM_NEWLEAD: //data args are : [team] [name]
    case LOG_TEAM_NEWMEMBER:
    list($team, $name)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[team]", "<font color=white><b>$team</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[name]", "<font color=white><b>$name</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_ADMIN_HARAKIRI: //data args are : [player] [ip]
    list($player, $ip)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[player]", "<font color=white><b>$player</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[ip]", "<font color=white><b>$ip</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_ADMIN_ILLEGVALUE: //data args are : [player] [quantity] [type] [holds]
    list($player, $quantity, $type, $holds)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[player]", "<font color=white><b>$player</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[quantity]", "<font color=white><b>$quantity</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[type]", "<font color=white><b>$type</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[holds]", "<font color=white><b>$holds</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_ADMIN_PLANETDEL: //data args are : [attacker] [defender] [sector]
    list($attacker, $defender, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[attacker]", "<font color=white><b>$attacker</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[defender]", "<font color=white><b>$defender</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_DEFENCE_DEGRADE: //data args are : [sector] [degrade]
    list($sector, $degrade)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[degrade]", "<font color=white><b>$degrade</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_PLANET_CAPTURED: //data args are : [cols] [credits] [owner]
    list($cols, $credits, $owner)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[cols]", "<font color=white><b>$cols</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[credits]", "<font color=white><b>$credits</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[owner]", "<font color=white><b>$owner</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
    case LOG_BOUNTY_CLAIMED:
    list($amount,$bounty_on,$placed_by) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[amount]", "<font color=white><b>$amount</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[bounty_on]", "<font color=white><b>$bounty_on</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[placed_by]", "<font color=white><b>$placed_by</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_BOUNTY_PAID:
    list($amount,$bounty_on) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[amount]", "<font color=white><b>$amount</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[bounty_on]", "<font color=white><b>$bounty_on</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_BOUNTY_CANCELLED:
    list($amount,$bounty_on) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[amount]", "<font color=white><b>$amount</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[bounty_on]", "<font color=white><b>$bounty_on</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
case LOG_BOUNTY_FEDBOUNTY:
    $retvalue[text] = str_replace("[amount]", "<font color=white><b>$entry[data]</b></font>", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_SPACE_PLAGUE:
    list($name,$sector) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[name]", "<font color=white><b>$name</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $retvalue[text]);
    $percentage = $space_plague_kills * 100;
    $retvalue[text] = str_replace("[percentage]", "$space_plague_kills", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_PLASMA_STORM:
    list($name,$sector,$percentage) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[name]", "<font color=white><b>$name</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[percentage]", "<font color=white><b>$percentage</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_PLANET_BOMBED:
    list($planet_name, $sector, $name, $beams, $torps, $figs)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[planet_name]", "<font color=white><b>$planet_name</b></font>", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "<font color=white><b>$sector</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[name]", "<font color=white><b>$name</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[beams]", "<font color=white><b>$beams</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[torps]", "<font color=white><b>$torps</b></font>", $retvalue[text]);
    $retvalue[text] = str_replace("[figs]", "<font color=white><b>$figs</b></font>", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

  }
  return $retvalue;
}*/

?> 

