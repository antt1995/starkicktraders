<?
/*************************************************************************
mission5.php - The 5th mission set up portion
Copyright (c)2003-2004 Ben Gibbs

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

if (preg_match("/mission/mission5.php/i", $PHP_SELF)) {
    echo "You can not access this file directly!";
    die();
}
// This is mission specific code used to setup the mission
// Mission Description
// Find hostage on random planet. Put variables into mstatus list of generic variables
// First, what type of sector is this hostage in? Get a number between 0 and 3 inclusive from player_id
$sector_type = $playerinfo[player_id] % 4;
switch ($sector_type) {
	case 0: $var1 = "Energy";
			break;
	case 1: $var1 = "Organics";
			break;
	case 2: $var1 = "Ore";
			break;
	case 3: $var1 = "Goods";
			break;
}
// Now let's find a random planet in one of those sectors
$misres = $db->Execute("SELECT * FROM $dbtables[universe] LEFT JOIN $dbtables[planets] USING (sector_id) WHERE owner=0 AND port_type='$var1' ORDER BY RAND() LIMIT 1");
if ($misres->EOF) {
	// Oh no, we don't have a free planet in the universe to use. We will pick one that is owned instead
	//echo "<!--DEBUG: No free planet\n\nSELECT * FROM $dbtables[universe] LEFT JOIN $dbtables[planets] USING (sector_id) WHERE port_type='$var1' AND owner !=0 ORDER BY RAND() LIMIT 1\n\n-->";
	$misres = $db->Execute("SELECT * FROM $dbtables[universe] LEFT JOIN $dbtables[planets] USING (sector_id) WHERE port_type='$var1' AND owner !=0 ORDER BY RAND() LIMIT 1");
	if ($misres->EOF) {
		// Still no luck!
		//echo "\n<!-- DEBUG: STILL NO LUCK! -->\n";
		$db->Execute("DELETE FROM $dbtables[mstatus] WHERE mission_id = $trigger[mission_id] AND player_id=$playerinfo[player_id]"); // Quit mission
	}
}
if (!$misres->EOF) {
	$planetinfo = $misres->fields;
	$var2 = $planetinfo[sector_id]; // This is the sector where the guy is located
	$var3 = $planetinfo[planet_id]; // This is the planet where the guy is
	//echo "<!-- DEBUG: $var2    $var3\n\n-->";
	// Now create the unique mission message
	$message = addslashes("Incoming message from Commander Cain... Envoy Lebrinsky and his ship Dominion IV have been hijacked and are being held hostage. The kidnapers have also infected the Envoy with a rare virus that will kill him in 1000 turns. Our intelligence reveals that the Envoy and his ship are currently located on an unnamed planet in a sector with a $var1 port. Rescue our Envoy and return him to Federation Space before he dies and we will reward you well.");
	// Let's put this into the database
	//echo "<!-- DEBUG: UPDATE $dbtables[mstatus] SET message='$message', var1='$var1', var2='$var2', var3='$var3' WHERE player_id=$playerinfo[player_id] AND mission_id=$trigger[mission_id]-->";
	$misres = $db->Execute("UPDATE $dbtables[mstatus] SET message='$message', var1='$var1', var2='$var2', var3='$var3' WHERE player_id=$playerinfo[player_id] AND mission_id=$trigger[mission_id]");
}
?>