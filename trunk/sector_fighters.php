<?
                    if (preg_match("/sector_fighters.php/i", $PHP_SELF)) {
                        echo "You can not access this file directly!";
                        die();
                    }
                    include("languages/$lang");

                    echo $l_sf_attacking;
                    $targetfighters = $total_sector_fighters;
     	            $playerbeams = NUM_BEAMS($shipinfo[beams]);
                    if($calledfrom == 'rsmove.php')
                    {
                       $shipinfo[ship_energy] += $energyscooped;
                    }
                    if($playerbeams>$shipinfo[ship_energy])
                    {
                       $playerbeams=$shipinfo[ship_energy];
                    }
                    $shipinfo[ship_energy]=$shipinfo[ship_energy]-$playerbeams;
                    $playershields = NUM_SHIELDS($shipinfo[shields]);
                    if($playershields>$shipinfo[ship_energy])
                    {  
                       $playershields=$shipinfo[ship_energy];
                    }
//                    $shipinfo[ship_energy]=$shipinfo[ship_energy]-$playershields;
                    $playertorpnum = round(mypw($level_factor,$shipinfo[torp_launchers]))*2;
                    if($playertorpnum > $shipinfo[torps])
                    { 
                       $playertorpnum = $shipinfo[torps];
                    }
                    $playertorpdmg = $torp_dmg_rate*$playertorpnum;
                    $playerarmour = $shipinfo[armour_pts];
                    $playerfighters = $shipinfo[ship_fighters];
                    if($targetfighters > 0 && $playerbeams > 0)
                    {
                       if($playerbeams > round($targetfighters / 2))
                       {
                          $temp = round($targetfighters/2);
                          $lost = $targetfighters-$temp;
                          $l_sf_destfight = str_replace("[lost]", $lost, $l_sf_destfight);
                          echo $l_sf_destfight;
                          $targetfighters = $temp;
                          $playerbeams = $playerbeams-$lost;
                       }
                       else
                       {
                          $targetfighters = $targetfighters-$playerbeams;
                          $l_sf_destfightb = str_replace("[lost]", $playerbeams, $l_sf_destfightb);
                          echo $l_sf_destfightb;
                          
                          $playerbeams = 0;
                       }   
                   }
                   echo "<BR>$l_sf_torphit<BR>";
                   if($targetfighters > 0 && $playertorpdmg > 0)
                   {
                      if($playertorpdmg > round($targetfighters / 2))
                      {
                         $temp=round($targetfighters/2);
                         $lost=$targetfighters-$temp;
                         $l_sf_destfightt = str_replace("[lost]", $lost, $l_sf_destfightt);
                         echo $l_sf_destfightt;
                         $targetfighters=$temp;
                         $playertorpdmg=$playertorpdmg-$lost;
                      }
                      else
                      {
                         $targetfighters=$targetfighters-$playertorpdmg;
                         $l_sf_destfightt = str_replace("[lost]", $playertorpdmg, $l_sf_destfightt);
                         echo $l_sf_destfightt;
                         $playertorpdmg=0;
                      }
                  }
                  echo "<BR>$l_sf_fighthit<BR>";
                  if($playerfighters > 0 && $targetfighters > 0)
                  {
                     if($playerfighters > $targetfighters)
                     {
                        echo $l_sf_destfightall;
                        $temptargfighters=0;
                     }
                     else
                     {
                        $l_sf_destfightt2 = str_replace("[lost]", $playerfighters, $l_sf_destfightt2);
                        echo $l_sf_destfightt2;
                        $temptargfighters=$targetfighters-$playerfighters;
                     }
                     if($targetfighters > $playerfighters)
                     {
                        echo $l_sf_lostfight;
                        $tempplayfighters=0;
                     }
                     else
                     {
                        $l_sf_lostfight2 = str_replace("[lost]", $targetfighters, $l_sf_lostfight2);
                        echo $l_sf_lostfight2;
                        $tempplayfighters=$playerfighters-$targetfighters;
                     }     
                     $playerfighters=$tempplayfighters;
                     $targetfighters=$temptargfighters;
                 }
                 if($targetfighters > 0)
                 {
                    if($targetfighters > $playerarmour)
                    {
                       $playerarmour=0;
                       echo $l_sf_armorbreach;
                    }
                    else
                    {
                       $playerarmour=$playerarmour-$targetfighters;
                       $l_sf_armorbreach2 = str_replace("[lost]", $targetfighters, $l_sf_armorbreach2);
                       echo $l_sf_armorbreach2;
                    } 
                 }
                 $fighterslost = $total_sector_fighters - $targetfighters;
 
                 $l_sf_sendlog = str_replace("[player]", $playerinfo[character_name], $l_sf_sendlog);
                 $l_sf_sendlog = str_replace("[lost]", NUMBER($fighterslost), $l_sf_sendlog);
                 $l_sf_sendlog = str_replace("[sector]", $sector, $l_sf_sendlog);
                 destroy_fighters($sector,$fighterslost);
                
                 message_defence_owner($sector,$l_sf_sendlog);
                 playerlog($playerinfo[player_id], LOG_DEFS_DESTROYED_F, NUMBER($fighterslost)."|$sector");
                 $armour_lost=$shipinfo[armour_pts]-$playerarmour;
                 $fighters_lost=$shipinfo[ship_fighters]-$playerfighters;
                 $energy=$shipinfo[ship_energy];
                 $update4b = $db->Execute ("UPDATE $dbtables[ships] SET ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, armour_pts=armour_pts-$armour_lost, torps=torps-$playertorpnum WHERE ship_id=$playerinfo[currentship] AND player_id=$playerinfo[player_id]");
                 $l_sf_lreport = str_replace("[armor]", $armour_lost, $l_sf_lreport);
                 $l_sf_lreport = str_replace("[fighters]", $fighters_lost, $l_sf_lreport);
                 $l_sf_lreport = str_replace("[torps]", $playertorpnum, $l_sf_lreport);
                 echo $l_sf_lreport;
                 if($playerarmour < 1)
                 {
                    echo $l_sf_shipdestroyed;
                    playerlog($playerinfo[player_id], LOG_DEFS_KABOOM, "$sector|$shipinfo[dev_escapepod]");
                    $l_sf_sendlog2 = str_replace("[player]", $playerinfo[character_name], $l_sf_sendlog2);
                    $l_sf_sendlog2 = str_replace("[sector]", $sector, $l_sf_sendlog2);
                    $killer_id=message_defence_owner($sector,$l_sf_sendlog2);
                    if($shipinfo[dev_escapepod] == "Y")
                    {
                       $rating=round($playerinfo[rating]/2);
                       echo $l_sf_escape;
                       $db->Execute("UPDATE $dbtables[players] SET sector=0,on_planet='N',rating='$rating',cleared_defences=' ' WHERE player_id=$playerinfo[player_id]"); 
					   // Immediately release any ships being towed 
						if ($shipinfo[tow] > 0) {
							$db->Execute("UPDATE $dbtables[ships] SET player_id=0,on_planet='N',sector=$shipinfo[sector] WHERE ship_id=$shipinfo[tow]");
						}
						$db->Execute("UPDATE $dbtables[ships] SET ship_destroyed ='Y', sector=0, tow=0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");

                       cancel_bounty($playerinfo[player_id]);
                       $ok=0;
                       TEXT_GOTOMAIN();
                       die();

                    }
                    else
                    { 
                       cancel_bounty($playerinfo[player_id]);
                       db_kill_player($playerinfo[player_id],$playerinfo[currentship],$killer_id);
                       $ok=0;
                       TEXT_GOTOMAIN();
                       die();
                    }         
                 }
                 if($targetfighters > 0)
                    $ok=0;
                 else
                    $ok=2;
?>
