<?
/*************************************************************************
mission11.php - The 11th mission set up portion
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

if (preg_match("/mission/mission11.php/i", $PHP_SELF)) {
    echo "You can not access this file directly!";
    die();
}
// This is mission specific code used to setup the mission
// Mission Description
// Get a fur_tech ship to level 22 engines
// Make sure that they use the same ship
	$var1 = $playerinfo[currentship];
	$var2 = rand(25,5000); // Start sector
	$var3 = rand(25,5000); // End sector
	// Let's put this into the database
	$misres = $db->Execute("UPDATE $dbtables[mstatus] SET var1='$var1',var2='$var2',var3='$var3' WHERE player_id=$playerinfo[player_id] AND mission_id=11");
?>
