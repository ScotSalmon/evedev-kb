<?php
require_once ('common/includes/class.corp.php');

require_once ('common/includes/class.alliance.php');
require_once ('common/includes/class.killlist.php');
require_once ('common/includes/class.killsummarytable.php');
require_once ('mods/battles_mod/class.killlisttable2.php');

if (!$kll_id=intval($_GET['kll_id']))
    {
    echo 'No valid kill id specified';
    exit;
    }

$page=new Page('Related kills & losses');
$html.='<link rel="stylesheet" type="text/css" href="mods/battles_mod/style.css">'; // Sapyx 04/09/2008

// this is a fast query to get the system and timestamp
$rqry=new DBQuery();
$rsql='SELECT kll_timestamp, kll_system_id from kb3_kills where kll_id = ' . $kll_id;
$rqry->execute($rsql);
$rrow  =$rqry->getRow();
$system=new SolarSystem($rrow['kll_system_id']);

// now we get all kills in that system for +-12 hours
$query ='SELECT kll.kll_timestamp AS ts FROM kb3_kills kll WHERE kll.kll_system_id=' . $rrow['kll_system_id'] . '
            AND kll.kll_timestamp <= date_add( \'' . $rrow['kll_timestamp'] . '\', INTERVAL \'12\' HOUR )
            AND kll.kll_timestamp >= date_sub( \'' . $rrow['kll_timestamp'] . '\', INTERVAL \'12\' HOUR )
            ORDER BY kll.kll_timestamp ASC';
$qry   =new DBQuery();
$qry->execute($query);

$ts=array();

while ($row=$qry->getRow())
    {
    $time                          = strtotime($row['ts']);
    $ts[intval(date('H', $time))][]=$row['ts'];
    }

// this tricky thing looks for gaps of more than 1 hour and creates an intersection
$baseh=date('H', strtotime($rrow['kll_timestamp']));
$maxc =count($ts);

$times=array();

for ($i=0; $i < $maxc; $i++)
    {
    $h = ($baseh + $i) % 24;

    if (!isset($ts[$h]))
        {
        break;
        }

    foreach ($ts[$h] as $timestamp)
        {
        $times[]=$timestamp;
        }
    }

for ($i=0; $i < $maxc; $i++)
    {
    $h = ($baseh - $i) % 24;

    if ($h < 0)
        {
        $h+=24;
        }

    if (!isset($ts[$h]))
        {
        break;
        }

    foreach ($ts[$h] as $timestamp)
        {
        $times[]=$timestamp;
        }
    }

unset ($ts);
asort ($times);

// we got 2 resulting timestamps
$firstts=array_shift($times);
$lastts =array_pop($times);

$kslist =new KillList();
$kslist->setOrdered(true);
$kslist->addSystem($system);
$kslist->setStartDate($firstts);
$kslist->setEndDate($lastts);
involved::load($kslist, 'kill');

$lslist=new KillList();
$lslist->setOrdered(true);
$lslist->addSystem($system);
$lslist->setStartDate($firstts);
$lslist->setEndDate($lastts);
involved::load($lslist, 'loss');

// By Dark Korrosion. Print the needed javascript function for toggle view

$html
    .="<script language='javascript'>
function toggle_view(id) {
  attribute = document.getElementById(id).style.display
  if (attribute == 'block') {
    document.getElementById(id).style.display = 'none';
  } else {
    document.getElementById(id).style.display = 'block';
  }
}
</script>";

// BEGIN --- Anne Sapyx 18/07/2008
$html.="<div class=\"kb-kills-header\">Battle in " . $system->getName() . " (" . $system->getRegionName() . "), "
    . substr($firstts, 8, 2) . substr($firstts, 4, 4) . substr($firstts, 0, 4) . " (" . substr($firstts, 11, 5) . " - " 
    . substr($lastts, 11, 5) . ")</div>";
// END --- Anne Sapyx 18/07/2008

$summarytable=new KillSummaryTable($kslist, $lslist);
$summarytable->setBreak(config::get('summarytable_rowcount'));
$html.=$summarytable->generate();

$klist=new KillList();
$klist->setOrdered(true);
$klist->addSystem($system);
$klist->setStartDate($firstts);
$klist->setEndDate($lastts);
involved::load($klist, 'kill');

$llist=new KillList();
$llist->setOrdered(true);
$llist->addSystem($system);
$llist->setStartDate($firstts);
$llist->setEndDate($lastts);
involved::load($llist, 'loss');

if ($_GET['scl_id'])
    {
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
    $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));
    }

$destroyed=$pods=array();

$pilots=array
    (
    'a' => array(),
    'e' => array()
    );

$kslist->rewind();
$classified=false;

while ($kill=$kslist->getKill())
    {
    handle_involved($kill, 'a');
    handle_destroyed($kill, 'e');

    if ($kill->isClassified())
        {
        $classified=true;
        }
    }

$lslist->rewind();

while ($kill=$lslist->getKill())
    {
    handle_involved($kill, 'e');
    handle_destroyed($kill, 'a');

    if ($kill->isClassified())
        {
        $classified=true;
        }
    }

// sort pilot ships, order pods after ships
foreach ($pilots as $side => $pilot)
    {
    foreach ($pilot as $id => $kll)
        {
        usort($pilots[$side][$id], 'cmp_ts_func');
        }
    }

// sort arrays, ships with high points first
uasort($pilots['a'], 'cmp_func');
uasort($pilots['e'], 'cmp_func');

// now get the pods out and mark the ships the've flown as podded
foreach ($pilots as $side => $pilot)
    {
    foreach ($pilot as $id => $kll)
        {
        $max = count($kll);

        for ($i=0; $i < $max; $i++)
            {
            if ($kll[$i]['ship'] == 'Capsule')
                {
                if (isset($kll[$i - 1]['sid']) && isset($kll[$i]['destroyed']))
                    {
                    $pilots[$side][$id][$i - 1]['podded']=true;
                    $pilots[$side][$id][$i - 1]['podid'] =$kll[$i]['kll_id'];
                    unset ($pilots[$side][$id][$i]);
                    }
                else
                    {
                    // now sort out all pods from pilots who previously flown a real ship
                    $valid_ship=false;

                    foreach ($kll as $ship)
                        {
                        if ($ship['ship'] != 'Capsule')
                            {
                            $valid_ship=true;
                            break;
                            }
                        }

                    if ($valid_ship)
                        {
                        unset ($pilots[$side][$id][$i]);
                        }
                    }
                }
            }
        }
    }

$smarty->assign_by_ref('pilots_a', $pilots['a']);
$smarty->assign_by_ref('pilots_e', $pilots['e']);

$pod=new Ship(6);
$smarty->assign('podpic', $pod->getImage(32));

if ($classified)
    $smarty->assign('system', 'Classified System');
else
    $smarty->assign('system', $system->getName());

$smarty->assign('firstts', $firstts);
$smarty->assign('lastts', $lastts);

// BEGIN --- Anne Sapyx 18/07/2008

$kill_summary=new KillSummaryTable($klist, $llist);
$summary_html=$kill_summary->generate();

if ($kill_summary->getTotalKillISK())
    $efficiency = round($kill_summary->getTotalKillISK() / ($kill_summary->getTotalKillISK() + $kill_summary->getTotalLossISK()) * 100, 2);
else
    $efficiency = 0;

if ($kill_summary->getTotalKills() == 0)
    $k_ratio='N/A';

elseif ($kill_summary->getTotalLosses() == 0)
    $k_ratio=$k_count . ' : 0';

else
    $k_ratio=round($kill_summary->getTotalKills() / $kill_summary->getTotalLosses(), 2) . ' : 1';


$html.="<div class=\"kb-kills-header\">Battle Statistics</div>";
$html.="<table class=kb-table  border=\"0\" cellspacing=1>";

$html.="<tr class=kb-table-row-even>" . "<td class=kb-table-cell align='left'><b>Real Kills</b></td>"
    . "<td class=kl-kill align='right'>" . $kill_summary->getTotalKills() . "</td>" . "<td class=kl-loss align='left'>"
    . $kill_summary->getTotalLosses() . "</td>" . "<td class=kb-table-cell align='right'><b>Real Losses</b></td>"
    . "</tr>";
$html.="<tr class=kb-table-row-even>" . "<td class=kb-table-cell align='left'><b>Damage done</b></td>"
    . "<td class=kl-kill align='right'>" . round($kill_summary->getTotalKillISK() / 1000000,
                                                 2) . "M</td>" . "<td class=kl-loss align='left'>"
    . round($kill_summary->getTotalLossISK() / 1000000,
            2) . "M</td>" . "<td class=kb-table-cell align='right'><b>Damage received</b></td>" . "</tr>";
$html.="<tr class=kb-table-row-even>" . "<td class=kb-table-cell align='left'><b>Efficiency:</b></td>"
    . "<td class=kb-table-cell align='right'>" . $efficiency . "%" . "<td class=kb-table-cell align='left'>" . $k_ratio
    . "</td>" . "<td class=kb-table-cell align='right'><b>Kill ratio</b></td>" . "</td>" . "</tr>";

$html.="</table><br/>";

$smarty->assign('kcount', $kill_summary->getTotalKills());
$smarty->assign('lcount', $kill_summary->getTotalLosses());

//print_r ($pilots);

//---------------
$friendlycnt=$hostilecnt=0;

foreach ($pilots as $side => $pilotA)
    {
    foreach ($pilotA as $kll)
        {
        foreach ($kll as $pilota)
            {
            $shippa = TestPilotName($pilota["ship"]);    
                
            if ($side == 'a')
                {
                $GoodAllies[$pilota["alliance"]]["quantity"]+=1;
                $GoodAllies[$pilota["alliance"]]["corps"][$pilota["corp"]]+=1;

                $GoodShips[$shippa]["shipClass"]=$pilota["shipClass"];
                $GoodShips[$shippa]["times"]+=1;
                $GoodShips[$shippa]["color"]=$pilota["color"];
                if ($pilota["destroyed"]==1) 
                        $GoodShips[$shippa]["destroyed"]+=1;   
                    else
                        $GoodShips[$shippa]["destroyed"]+=0;   
                
                $friendlycnt++;
                }
            else
                {
                $BadAllies[$pilota["alliance"]]["quantity"]+=1;
                $BadAllies[$pilota["alliance"]]["corps"][$pilota["corp"]]+=1;

                $BadShips[$shippa]["shipClass"]=$pilota["shipClass"];
                $BadShips[$shippa]["times"]+=1;
                $BadShips[$shippa]["color"]=$pilota["color"];
                if ($pilota["destroyed"]==1) 
                        $BadShips[$shippa]["destroyed"]+=1;   
                    else
                        $BadShips[$shippa]["destroyed"]+=0;   

                $hostilecnt++;
                }
            }
        }
    }

asort ($GoodShips);
asort ($GoodAllies);

$smarty->assign_by_ref('GAlliesCount', count($GoodAllies));
$smarty->assign_by_ref('GoodAllies', $GoodAllies);
$smarty->assign_by_ref('GoodShips', $GoodShips);

asort ($BadShips);
asort ($BadAllies);

$smarty->assign_by_ref('BAlliesCount', count($BadAllies));
$smarty->assign_by_ref('BadAllies', $BadAllies);
$smarty->assign_by_ref('BadShips', $BadShips);

$smarty->assign('friendlycnt', $friendlycnt);
$smarty->assign('hostilecnt', $hostilecnt);

$html.=$smarty->fetch(get_tpl('../../../mods/battles_mod/battle_overview'));

$html.="<div class=\"kb-kills-header\"><a href=\"javascript:toggle_view('related_kills');\">Related kills ("
    . $kill_summary->getTotalKills() . ")</a></div>";
$html.="<div id=\"related_kills\">";

$ktable=new KillListTable2($klist);
$html.=$ktable->generate();
$html.="<br/></div>";

$html.="<div class=\"kb-losses-header\"><a href=\"javascript:toggle_view('related_losses');\">Related losses ("
    . $kill_summary->getTotalLosses() . ")</a></div>";
$html.="<div id=\"related_losses\">";

$ltable=new KillListTable2($llist);
$html.=$ltable->generate();
$html.="<br/></div>";
// END ---Anne Sapyx 18/07/2008 

// BEGIN ---Anne Sapyx 03/09/2008 

$html.="<div class=\"kb-kills-header\"><a href=\"javascript:toggle_view('enemy_loot');\">Enemy's loot</a></div>";
$html.="<div id=\"enemy_loot\">";

$DLoot=$SLoot=Array();
$smarty->assign('DTotal', getDestroyedLoot($klist, &$DLoot));
$smarty->assign('STotal', getDroppedLoot($klist, &$SLoot));
$smarty->assign_by_ref('Dloot', $DLoot);
$smarty->assign_by_ref('Sloot', $SLoot);

$html.=$smarty->fetch(get_tpl('../../../mods/battles_mod/loot_overview'));
$html.="<br/></div>";

$html.="<div class=\"kb-losses-header\"><a href=\"javascript:toggle_view('friendly_loot');\">Friendly's loot</a></div>";
$html.="<div id=\"friendly_loot\">";

$DLoot=$SLoot=Array();
$smarty->assign('DTotal', getDestroyedLoot($llist, &$DLoot));
$smarty->assign('STotal', getDroppedLoot($llist, &$SLoot));
$smarty->assign_by_ref('Dloot', $DLoot);
$smarty->assign_by_ref('Sloot', $SLoot);

$html.=$smarty->fetch(get_tpl('../../../mods/battles_mod/loot_overview'));
$html.="<br/></div>";

// END ---Anne Sapyx 03/09/2008 

$menubox=new Box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "View");
$menubox->addOption("link", "Back to Killmail", "?a=kill_detail&kll_id=" . $_GET['kll_id']);
$menubox->addOption("link", "Kills & losses", "?a=kill_related&kll_id=" . $_GET['kll_id']);
$page->addContext($menubox->generate());

//Admin is able to see classsiefied systems
$fromKill=new kill($kll_id);

if ((!$fromKill->isClassified()) || ($page->isAdmin()))
    {
    $mapbox=new Box("Map");
    $mapbox->addOption("img", "?a=mapview&sys_id=" . $system->getID() . "&mode=map&size=145");
    $mapbox->addOption("img", "?a=mapview&sys_id=" . $system->getID() . "&mode=region&size=145");
    $mapbox->addOption("img", "?a=mapview&sys_id=" . $system->getID() . "&mode=cons&size=145");
    $page->addContext($mapbox->generate());
    }

$html.='</br><hr><b>New Kills Related (part of BATTLES MOD) v. 0.93 (2008 - Anne Sapyx)<b/></br>';

$page->setContent($html);
$page->generate();

// --------------------------------- END MAIN---------------------------------

// ------------------------------ BEGIN FUNCTIONS ----------------------------


// BEGIN --- Anne Sapyx 02/09/2008
function getDestroyedLoot($kill_list, &$dest_array)
    {
    $kill_list->rewind();

    $TotalValue=0;
    $qry       =new DBQuery();

    while ($kill=$kill_list->getKill())
        {
        $query = "SELECT kb3_items_destroyed.itd_itm_id AS 'ID', kb3_invtypes.typeName AS 'Name', SUM( kb3_items_destroyed.itd_quantity ) AS 'Quantity', SUM( kb3_item_price.price ) AS Price, 
            SUM( kb3_items_destroyed.itd_quantity ) * SUM( kb3_item_price.price ) AS 'Total Price'
         FROM kb3_items_destroyed, kb3_invtypes, kb3_item_price
         WHERE 
         (
            (
                kb3_items_destroyed.itd_itm_id = kb3_invtypes.typeID
            )
            AND 
            (
                kb3_items_destroyed.itd_itm_id = kb3_item_price.typeID
            )
            AND 
            (
                kb3_items_destroyed.itd_kll_id = " . $kill->getID() . ")
         )
         GROUP BY kb3_items_destroyed.itd_itm_id";

        $qry->execute($query);

        while ($row=$qry->getRow())
            {
            $dest_array[$row['Name']]['Quantity'] = $row['Quantity'];

            if (config::get('item_values'))
                {
                $dest_array[$row['Name']]['TValue']=FormattedValue($row['Total Price']);
                $dest_array[$row['Name']]['Value'] =FormattedValue($row['Price']);
                $TotalValue+=$row['Total Price'];
                }
            }
        }

    return FormattedValue($TotalValue);
    }

function getDroppedLoot($kill_list, &$dest_array)
    {
    $kill_list->rewind();

    $TotalValue=0;
    $qry       =new DBQuery();

    while ($kill=$kill_list->getKill())
        {
        $query = "SELECT kb3_invtypes.typeName AS 'Name', SUM( kb3_items_dropped.itd_quantity ) AS 'Quantity', SUM( kb3_item_price.price ) AS Price, 
            SUM( kb3_items_dropped.itd_quantity ) * SUM( kb3_item_price.price ) AS 'Total Price'
         FROM kb3_items_dropped, kb3_invtypes, kb3_item_price
         WHERE 
         (
            (
                kb3_items_dropped.itd_itm_id = kb3_invtypes.typeID
            )
            AND 
            (
                kb3_items_dropped.itd_itm_id = kb3_item_price.typeID
            )
            AND 
            (
                kb3_items_dropped.itd_kll_id = " . $kill->getID() . ")
         )
         GROUP BY kb3_items_dropped.itd_itm_id";

        $qry->execute($query);

        while ($row=$qry->getRow())
            {
            $dest_array[$row['Name']]['Quantity'] = $row['Quantity'];

            if (config::get('item_values'))
                {
                $dest_array[$row['Name']]['TValue']=FormattedValue($row['Total Price']);
                $dest_array[$row['Name']]['Value'] =FormattedValue($row['Price']);
                $TotalValue+=$row['Total Price'];
                }
            }
        }

    return FormattedValue($TotalValue);
    }

function FormattedValue($value)
    {
    if ($value > 0)
        {
        // Value Manipulation for prettyness.
        if (strlen($value) > 6) // Is this value in the millions?
            {
            $formatted=round($value / 1000000, 2);
            $formatted=number_format($formatted, 2);
            $formatted=$formatted . " M";
            }
        elseif (strlen($value) > 3) // 1000's ?
            {
            $formatted=round($value / 1000, 2);

            $formatted=number_format($formatted, 2);
            $formatted=$formatted . " K";
            }
        else
            {
            $formatted=number_format($value, 2);
            $formatted=$formatted . " isk";
            }
        }
    else
        {
        $formatted="0 isk";
        }

    return $formatted;
    }

// BEGIN --- Anne Sapyx 30/07/2008
function getColor($ship)
    {
    $value=$ship->getValue();

    if ($value >= 0 && $value <= 1)
        $color="gray";

    elseif ($value > 1 && $value <= 15)
        $color="darkblue";

    elseif ($value > 15 && $value <= 25)
        $color="green";

    elseif ($value > 25 && $value <= 40)
        $color="yellow";

    elseif ($value > 40 && $value <= 80)
        $color="red";

    elseif ($value > 80 && $value <= 250)
        $color="orange";

    elseif ($value > 250)
        $color="purple";

    return ($color);
    }
//--------------------

function cmp_func($a, $b)
    {
    // select the biggest fish of that pilot
    $t_scl=0;

    foreach ($a as $i => $ai)
        {
        if ($ai['scl'] > $t_scl)
            {
            $t_scl=$ai['scl'];
            $cur_i=$i;
            }
        }

    $a    =$a[$cur_i];

    $t_scl=0;

    foreach ($b as $i => $bi)
        {
        if ($bi['scl'] > $t_scl)
            {
            $t_scl=$bi['scl'];
            $cur_i=$i;
            }
        }

    $b=$b[$cur_i];

    if ($a['scl'] > $b['scl'])
        {
        return -1;
        }
    // sort after points, shipname, pilotname
    elseif ($a['scl'] == $b['scl'])
        {
        if ($a['ship'] == $b['ship'])
            {
            if ($a['name'] > $b['name'])
                {
                return 1;
                }

            return -1;
            }
        elseif ($a['ship'] > $b['ship'])
            {
            return 1;
            }

        return -1;
        }

    return 1;
    }

function is_destroyed($pilot)
    {
    global $destroyed;

    if ($result=array_search((string)$pilot, $destroyed))
        {
        global $smarty;

        $smarty->assign('kll_id', $result);
        return true;
        }

    return false;
    }

function podded($pilot)
    {
    global $pods;

    if ($result=array_search((string)$pilot, $pods))
        {
        global $smarty;

        $smarty->assign('pod_kll_id', $result);
        return true;
        }

    return false;
    }

function cmp_ts_func($a, $b)
    {
    if ($a['ts'] < $b['ts'])
        {
        return -1;
        }

    return 1;
    }

function handle_involved($kill, $side)
    {
    global $pilots;

    // we need to get all involved pilots, killlists dont supply them
    $qry=new DBQuery();
    $sql="select ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status, ind_shp_id, ind_wep_id,
            typeName, plt_name, crp_name, all_name, shp_name, scl_points, scl_id, shp_externalid
            from kb3_inv_detail
            left join kb3_invtypes on ind_wep_id=typeID
            left join kb3_pilots on ind_plt_id=plt_id
            left join kb3_corps on ind_crp_id=crp_id
            left join kb3_alliances on ind_all_id=all_id
            left join kb3_ships on ind_shp_id=shp_id
            left join kb3_ship_classes on shp_class=scl_id
            where ind_kll_id = " . $kill->getID() . "  and ind_shp_id != '9999'
            order by ind_order";

    $qry->execute($sql);

    while ($row=$qry->getRow())
        {
        $ship           = new ship($row['ind_shp_id']);
        $shipc          =$ship->getClass();

        // check for npc names (copied from pilot class)

        $row['plt_name']=TestPilotName($row['plt_name']);

        // dont set pods as ships for pilots we already have
        if (isset($pilots[$side][$row['ind_plt_id']]))
            {
            if ($row['scl_id'] == 18 || $row['scl_id'] == 2)
                {
                continue;
                }
            }

        // search for ships with the same id
        if (isset($pilots[$side][$row['ind_plt_id']]))
            {
            foreach ($pilots[$side][$row['ind_plt_id']] as $id => $_ship)
                {
                if ($row['ind_shp_id'] == $_ship['sid'])
                    {
                    // we already got that pilot in this ship, continue
                    $pilots[$side][$row['ind_plt_id']][0]["times"]+=1;
                    continue 2;
                    }
                }
            }

        $shipimage=IMG_URL . '/ships/32_32/' . $row['shp_externalid'] . '.png';

        $pilots[$side][$row['ind_plt_id']][]=array
            (
            'name'      => $row['plt_name'],
            'sid'       => $row['ind_shp_id'],
            'spic'      => $shipimage,
            'aid'       => $row['ind_all_id'],
            'ts'        => strtotime($kill->getTimeStamp()),
            'corp'      => $row['crp_name'],
            'alliance'  => $row['all_name'],
            'scl'       => $row['scl_points'],
            'ship'      => $row['shp_name'],
            'shipClass' => $shipc->getName(),
            'weapon'    => $row['itm_name'],
            'cid'       => $row['ind_crp_id'],
            'times'     => 1,
            'color'     => getColor($shipc)
            );
        }
    }

function handle_destroyed($kill, $side)
    {
    global $destroyed, $pilots;

    $destroyed[$kill->getID()]=$kill->getVictimID();

    $ship                     =new Ship();
    $ship->lookup($kill->getVictimShipName());
    $shipc=$ship->getClass();

    $ts   =strtotime($kill->getTimeStamp());

    // mark the pilot as podded
    if ($shipc->getID() == 18 || $shipc->getID() == 2)
        {
        // increase the timestamp of a podkill by 1 so its after the shipkill
        $ts++;
        global $pods;
        $pods[$kill->getID()]=$kill->getVictimID();

        // return when we've added him already
        if (isset($pilots[$side][$kill->getVictimId()]))
            {
            #return;
            }
        }

    // search for ships with the same id
    if (isset($pilots[$side][$kill->getVictimId()]))
        {
        foreach ($pilots[$side][$kill->getVictimId()] as $id => $_ship)
            {
            if ($ship->getID() == $_ship['sid'])
                {
                $pilots[$side][$kill->getVictimId()][$id]['destroyed']=true;

                if (!isset($pilots[$side][$kill->getVictimId()][$id]['kll_id']))
                    {
                    $pilots[$side][$kill->getVictimId()][$id]['kll_id']=$kill->getID();
                    }

                //$pilots[$side][$kill->getVictimId()][0]["times"] +=1;
                return;
                }
            }
        }

    $pilots[$side][$kill->getVictimId()][]=array
        (
        'name'      => $kill->getVictimName(),
        'kll_id'    => $kill->getID(),
        'spic'      => $ship->getImage(32),
        'scl'       => $shipc->getPoints(),
        'destroyed' => true,
        'corp'      => $kill->getVictimCorpName(),
        'alliance'  => $kill->getVictimAllianceName(),
        'aid'       => $kill->getVictimAllianceID(),
        'ship'      => $kill->getVictimShipname(),
        'shipClass' => $shipc->getName(),
        'sid'       => $ship->getID(),
        'cid'       => $kill->getVictimCorpID(),
        'ts'        => $ts,
        'times'     => 0,
        'color'     => getColor($shipc)
        );
    }
?>
