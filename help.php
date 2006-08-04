<?
/*************************************************************************
help.php - shows the help for the game
Copyright (c)2001-2004 Ben Gibbs

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
***************************************************************************/

include("config.php");
updatecookie();

$title = "Help";
include("header.php");
include("languages/$lang");
connectdb();

if(checklogin())
{
  die();
}

?>
<table width="70%" border="0" cellpadding="0" align=center>
  <tr> 
    <td> 
      <h1><font face="Geneva, Arial, Helvetica, san-serif"><img src="images/treoship.gif" width="160" height="119" align="right"> 
        Starkick Traders Help</font></h1>
      <p><font face="Geneva, Arial, Helvetica, san-serif"> This is a game of inter-galactic 
        exploration. Players explore the universe, trading for commodities and 
        increasing their wealth and power. Battles can be fought over space sectors 
        and planets.</font></p>
      <p><font face="Geneva, Arial, Helvetica, san-serif">Choose from the following 
        to find out more information:</font></p>
      <ul>
        <li><font face="Geneva, Arial, Helvetica, san-serif"><a href=#mainmenu>Main 
          Menu commands</a></font></li>
        <li><font face="Geneva, Arial, Helvetica, san-serif"><a href=#techlevels>Tech 
          levels</a></font></li>
        <li><font face="Geneva, Arial, Helvetica, san-serif"><a href=#devices>Devices</a></font></li>
        <li><font face="Geneva, Arial, Helvetica, san-serif"><a href=#zones>Zones</a></font></li>
        <li><font face="Geneva, Arial, Helvetica, san-serif"><a href=newplayer.html>New 
          Player Guide</a></font></li>
        <li><font face="Geneva, Arial, Helvetica, san-serif"><a href="strategy.html">Strategy 
          Guide</a></font></li>
        <li><font face="Geneva, Arial, Helvetica, san-serif"><a href="faq.html">FAQ</a><br>
          <a name=mainmenu></a></font></li>
      </ul>
    </td>
  </tr><tr><td>
      <h2><font face="Geneva, Arial, Helvetica, san-serif">Main Menu commands:</font></h2>
      <font face="Geneva, Arial, Helvetica, san-serif"><b>Ship report:</b><br>
      Display a detailed report on your ship's systems, cargo and weaponry. You 
      can display this report by clicking on your ship's name or picture at the 
      top of the main page. <br>
      <br>
      <b>Warp links:</b><br>
      Move from one sector to another through warp links, by clicking on the sector 
      numbers. <br>
      <br>
      <b>Long-range scan:</b><br>
      Scan a neighboring sector with your long range scanners without actually 
      moving there. A full scan will give you an outlook on all the neighboring 
      sectors in one wide sweep of your sensors. <br>
      <br>
      <b>Ships:</b><br>
      Scan or attack a ship (if it shows up on your sensors) by clicking on the 
      appropriate link on the right of the ship's name. The attacked ship may 
      evade your offensive maneuver depending on its tech levels. <br>
      <br>
      <b>Trading ports:</b><br>
      Access the port trading menu by clicking on a port's type when you enter 
      a sector where one is present. <br>
      <br>
      <b>Planets:</b><br>
      Access the planet menu by clicking on a planet's name when you enter a sector 
      where one is present. <br>
      <br>
      <b>Navigation computer:</b><br>
      Use your computer to find a route to a specific sector. The navigation computer's 
      power depends on your computer tech level. <br>
      <br>
      <b>RealSpace:</b><br>
      Use your ship's engines to get to a specific sector. Upgrade your engines' 
      tech level to use RealSpace moves effectively. By clicking on the 'Presets' 
      link you can memorize up to 6 sector numbers for quick movement or you can 
      target any sector using the 'Other' link. <br>
      <br>
      <b>Trade routes:</b><br>
      Use trade routes to quickly trade commodities between ports. Trade routes 
      take advantage of RealSpace movements to go back and forth between two ports 
      and trade the maximum amount of commodities at each end. <br>
      <br>
      </font> 
      <h3><font face="Geneva, Arial, Helvetica, san-serif">Menu bar (bottom part 
        of the main page):</font></h3>
      <p><font face="Geneva, Arial, Helvetica, san-serif"><b>Devices:</b><br>
        Use the different devices that your ship carries (Genesis Torpedoes, beacons, 
        Warp Editors, etc.). For more details on each individual device, scroll 
        down to the 'Devices' section. <br>
        </font></p>
      <p><font face="Geneva, Arial, Helvetica, san-serif"><b>Ships:</b><br>
        Display a list of all your ships, showing which sector they are in.<br>
        <br>
        </font><font face="Geneva, Arial, Helvetica, san-serif"><b>Planets:</b><br>
        Display a list of all your planets, with current totals on commodities, 
        weaponry and credits. <br>
        <br>
        <b>Log:</b><br>
        Display the log of events that have happened to your ship, planets, bad 
        login attempts, etc. Check it regularly, especially if you do not have 
        email alerts turned on.<br>
        <br>
        <b>Send Message:</b><br>
        Send an in-game message to another player.<br>
        <br>
        <b>Read Message:</b><br>
        Read an in-game message from other players. </font><font face="Geneva, Arial, Helvetica, san-serif"><br>
        <br>
        <b>Rankings:</b><br>
        Display the list of the top players, ranked by their current scores. Click 
        on a player's name to see more information on them.<br>
        <br>
        <b>Options:</b><br>
        Change user-specific options (password, user profile, alerts, etc.). <br>
        <br>
        <b>Feedback:</b><br>
        Send an e-mail to the game admin. Use for bug reports.<br>
        <br>
        <b>Self-Destruct:</b><br>
        Destroy your ship and remove yourself from the game. <br>
        <br>
        <b>Help:</b><br>
        Display the help page (what you're reading right now). <br>
        <br>
        <b>Logout:</b><br>
        Remove any game cookies from your system, ending your current session. 
        <br>
        <br>
        <a name=techlevels></a></font> </p>
      <h2><font face="Geneva, Arial, Helvetica, san-serif">Tech levels:</font></h2>
      <font face="Geneva, Arial, Helvetica, san-serif">You can upgrade your ship 
      components at any special port. Each component upgrade improves your ship's 
      attributes and capabilities. Each ship you own will have a minimum and a 
      maximum level for each of these.<br>
      <br>
      <b>Hull:</b><br>
      Determines the number of holds available on your ship (for transporting 
      commodities and colonists). <br>
      <br>
      <b>Engines:</b><br>
      Determines the size of your engines. Larger engines can move through RealSpace 
      at a faster pace. I.e., use less turns.<br>
      <br>
      <b>Power:</b><br>
      Determines the amount of energy your ship can carry. Energy can be traded 
      or used by your Beams and Shields.<br>
      <br>
      <b>Computer:</b><br>
      Determines the number of fighters your ship can control and also how good 
      your Nav Computer is.<br>
      <br>
      <b>Sensors:</b><br>
      Determines the precision of your sensors when scanning a ship or planet. 
      Scan success is dependent upon the target's cloak level as well as your 
      sensors. Watch out for errors!<br>
      <br>
      <b>Armor:</b><br>
      Determines the number of armor points your ship can have. Armor is your 
      last line of defense. If you end up with zero or less than zero armor after 
      a fight you ship blows up!<br>
      <br>
      <b>Shields:</b><br>
      Determines the efficiency of your ship's shield system during combat. Shields 
      stop beams.<br>
      <br>
      <b>Beams:</b><br>
      Determines the efficiency of your ship's beam weapons during combat. Beams 
      destroy fighters, shields and armor.<br>
      <br>
      <b>Torpedo launchers:</b><br>
      Determines the number of torpedoes your ship can use and carry. You cannot 
      deploy as many torpedoes as you can carry but they do a lot more damage 
      per torpedo than per beam.<br>
      <br>
      <b>Cloak:</b><br>
      Determines the efficiency of your ship's cloaking system. See 'Sensors' 
      for more details. <br>
      <br>
      <a name=devices></a></font> 
      <h2><font face="Geneva, Arial, Helvetica, san-serif">Devices:</font></h2>
      <font face="Geneva, Arial, Helvetica, san-serif"><b>Space Beacons:</b><br>
      Post a warning or message which will be displayed to anyone entering this 
      sector. Only 1 beacon can be active in each sector, so a new beacon removes 
      the existing one (if any). Don't cuss in the beacons.<br>
      <br>
      <b>Warp Editors:</b><br>
      Create or destroy warp links to another sector. There is a limit to how 
      many warp links each sector can handle.<br>
      <br>
      <b>Genesis Torpedoes:</b><br>
      Create a planet in the current sector (if one does not yet exist) or use 
      one to destroy one of your own planets. Destroying a planet with colonists 
      on it results in serious bad karma!<br>
      <br>
      <b>Mine Deflector:</b><br>
      Protect the player against mines dropped in space. Each deflector takes 
      out 1 mine. <br>
      <br>
      <b>Emergency Warp Device:</b><br>
      Transport your ship to a random sector, if manually engaged. Otherwise, 
      an Emergency Warp Device can protect your ship when attacked by transporting 
      you out of the reach of the attacker. EWD's degrade in effectiveness the 
      bigger your average tech level becomes after avergae level 15.<br>
      <br>
      <b>Escape Pod (maximum of 1):</b><br>
      Keep yourself alive when your ship is destroyed, enabling you to keep your 
      credits and planets. <br>
      <br>
      <b>Fuel Scoop (maximum of 1):</b><br>
      Accumulate energy units when using RealSpace movement. <br>
      <br>
      <b>Tractor beam (installed):</b><br>
      Used to haul any other ship you own around the universe.. <br>
      <br>
      <a name=zones></a></font> 
      <h2><font face="Geneva, Arial, Helvetica, san-serif">Zones:</font></h2>
      <font face="Geneva, Arial, Helvetica, san-serif">The galaxy is divided into 
      different areas with different rules being enforced in each zone. To display 
      the restrictions attached to your current sector, just click on the zone 
      name (top right corner of the main page). Your ship can be towed out of 
      a zone to a random sector when your average tech size exceeds the maximum 
      allowed level for that specific zone. Attacking other players and using 
      some devices can also be disallowed in some zones. When you own a sector 
      you can set your own zone rules.</font></td>
  </tr>
</table>

  <BR>
<?
TEXT_GOTOMAIN();
include("footer.php");

?>
