<?php


class KillListTable2
{
    function KillListTable2($kill_list)
    {
        $this->limit = 0;
        $this->offset = 0;

        $this->kill_list_ = $kill_list;
        $this->daybreak_ = true;
    }

    function setBrowsable($browsable)
    {
        $this->browsable = $browsable;
    }

    function setDayBreak($daybreak)
    {
        $this->daybreak_ = $daybreak;
    }

    function setLimit($limit)
    {
        $this->limit_ = $limit;
    }


    
    function generate()
    {
 
        global $smarty;
        $prevdate = "";
        $this->kill_list_->rewind();
        $smarty->assign('daybreak', $this->daybreak_);
        $smarty->assign('comments_count', config::get('comments_count'));

        // evil hardcode-hack, don't do this at home kids ! ;)
        if (config::get('style_name') == 'revelations')
        {
            $smarty->assign('comment_white', '_white');
        }

        while ($kill = $this->kill_list_->getKill())
        {
            if ($this->limit_ && $c >= $this->limit_)
            {
                break;
            }
            else
            {
                $c++;
            }

            $curdate = substr($kill->getTimeStamp(), 0, 10);
            if ($curdate != $prevdate)
            {
                if (count($kills) && $this->daybreak_)
                {
                    $kl[] = array('kills' => $kills, 'date' => strtotime($prevdate), 'killer' => TrovaMassimo($killers), 'loser' => TrovaMassimo($losers));
                    $kills = array();
                    $killers = array();
                    $losers = array();
                }
                $prevdate = $curdate;
            }
            $kll = array();
            $kll['id'] = $kill->getID();
            $kll['victimshipimage'] = $kill->getVictimShipImage(32);
            $kll['victimshipname'] = $kill->getVictimShipName();
            $kll['victimshipclass'] = $kill->getVictimShipClassName();
            $kll['victimshipindicator'] = $kill->getVictimShipValueIndicator();
            $kll['victim'] = $kill->getVictimName();
            $kll['victimcorp'] = $kill->getVictimCorpName();
            $kll['victimalliancename'] = $kill->getVictimAllianceName();
            $kll['fb'] = $kill->getFBPilotName();
            $kll['fbcorp'] = $kill->getFBCorpName();
            $kll['victimportrait'] = $kill->getVictimPortrait(32);
            $kll['system'] = $kill->getSolarSystemName();
            $kll['systemsecurity'] = $kill->getSolarSystemSecurity();
            $kll['inv'] = $kill->getInvolvedPartyCount();
            $kll['timestamp'] = $kill->getTimeStamp();
            if (config::get('killlist_alogo'))
            {
                $kll['victimallianceicon'] = preg_replace('/[^a-zA-Z0-9]/', '', $kll['victimalliancename']);
                $kll['allianceexists'] = file_exists('img/alliances/'.$kll['victimallianceicon'].'.png');
            }

            if (isset($kill->_tag))
            {
                $kll['tag'] = $kill->_tag;
            }

            if ($kill->fbplt_ext_)
            {
                $kll['fbplext'] = $kill->fbplt_ext_;
            }
            else
            {
                $kll['fbplext'] = null;
            }
            if ($kill->plt_ext_)
            {
                $kll['plext'] = $kill->plt_ext_;
            }
            else
            {
                $kll['plext'] = null;
            }
            if (config::get('comments_count'))
            {
                $kll['commentcount'] = $kill->countComment($kill->getID());
            }
                        
            $kll['BS'] = TrovaInvolvedParty($kill, &$killers);

            $kills[] = $kll;
            
            $losers[$kll['victim']]['punti'] += $kill->getKillPoints();
            $losers[$kll['victim']]['portrait'] = $kll['victimportrait']; 
            $losers[$kll['victim']]['corp'] = $kll['victimcorp'];
            $losers[$kll['victim']]['id'] = $kill->getVictimID(); 
            
            //TrovaInvolvedParty($kill, &$killers);
        }
        if (count($kills))
        {
            $kl[] = array('kills' => $kills, 'date' => strtotime($prevdate), 'killer' => TrovaMassimo($killers), 'loser' => TrovaMassimo($losers));
        }

        $smarty->assign_by_ref('killlist', $kl);
        return $smarty->fetch(get_tpl('../../../mods/battles_mod/killlisttable2'));
    }     
}

function TrovaMassimo($piloti) 
    {
        $valore=0;
        $chiave="";
        
        foreach ($piloti as $k => $v) {
            if($v['punti']>$valore){
                switch ($k) 
                {                                          
                    case (strpos($k, "Control Tower")!== FALSE):                    
                    case (strpos($k, "Warp Disruptor")!== FALSE):                    
                    case (strpos($k, "Battery")!== FALSE):                    
                    
                        break;
                        
                    default:
                        $valore=$v['punti'];
                        $chiave=$k;
                }
            }
        }
        return array('pilota' => $chiave, 'punti' => $piloti[$chiave]['punti'], 'portrait' => $piloti[$chiave]['portrait'], 'corp' => $piloti[$chiave]['corp'], 'id' => $piloti[$chiave]['id']);
    }
     
function TrovaInvolvedParty($kill, &$killers)
    {
    $qry=new DBQuery();
/*        $qry->execute("SELECT kb3_pilots.plt_name as pilot, kb3_corps.crp_name as corp, kb3_pilots.plt_id as id, kb3_pilots.plt_externalid as xid 
        FROM kb3_inv_detail, kb3_pilots, kb3_corps
        WHERE (kb3_inv_detail.ind_plt_id = kb3_pilots.plt_id)and (kb3_inv_detail.ind_crp_id = kb3_corps.crp_id)and(kb3_inv_detail.ind_kll_id =" . $kill->getID() .")") 
        or die($qry->getErrorMsg()); 
*/        
    $qry->execute("SELECT kb3_pilots.plt_name AS pilot, kb3_corps.crp_name AS corp, kb3_pilots.plt_id AS id, kb3_pilots.plt_externalid AS xid, kb3_ships.shp_class AS SClass
        FROM kb3_inv_detail, kb3_pilots, kb3_corps, kb3_ships
        WHERE (
            kb3_inv_detail.ind_plt_id = kb3_pilots.plt_id
            )
        AND (
            kb3_inv_detail.ind_crp_id = kb3_corps.crp_id
            )
        AND (
            kb3_inv_detail.ind_shp_id = kb3_ships.shp_id
            )
        AND (
            kb3_inv_detail.ind_kll_id = " . $kill->getID() .")")
        or die($qry->getErrorMsg());

    $bs=0;      
    while ($row=$qry->getRow())
        {
        $pilot = TestPilotName($row['pilot']);    
            
        $killers[$pilot]['punti']+=$kill->getKillPoints();

        if ($pilot == TestPilotName($kill->getFBPilotName()))
            {
            $killers[$pilot]['punti']+=1;
            }

        $killers[$pilot]['portrait']="?a=thumb&amp;id=" . $row['xid'] . "&amp;size=32";
        $killers[$pilot]['corp']    =$row['corp'];
        $killers[$pilot]['id']      =$row['id'];
        
        if ($row['SClass']==1) //battleship
            $bs++;
        }
        return $bs;
    }
    
function TestPilotName($fbpilotname)
    {
    $npc=strpos($fbpilotname, "#");

    if ($npc === false)
        {
        $PilotName=$fbpilotname;
        }
    else
        {
        $name     =explode("#", $fbpilotname);
        $plt      =new Item($name[2]);
        $PilotName=$plt->getName();
        }

    return $PilotName;
    }
?>