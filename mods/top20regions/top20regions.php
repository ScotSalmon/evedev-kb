<?php
// Version 1.1

function show_region ($region_id) {
    $qry = new DBQuery();
    $query = "SELECT reg_name FROM kb3_regions WHERE reg_id = " . $region_id;
    $result = $qry->execute($query);
    $row = $qry->getRow();
    $page = new Page("Top 20 in " . $row['reg_name']);

    $query = "
SELECT COUNT(*) AS kills,
       entity_id AS id,
       entity_name AS name
FROM (SELECT inv.ind_kll_id AS kill_id,
             inv.ind_all_id AS entity_id,
             alli.all_name AS entity_name
      FROM kb3_inv_detail inv
           INNER JOIN kb3_kills ki ON inv.ind_kll_id = ki.kll_id
           INNER JOIN kb3_systems sys ON ki.kll_system_id = sys.sys_id
           INNER JOIN kb3_constellations const ON sys.sys_con_id = const.con_id
           INNER JOIN kb3_alliances alli ON inv.ind_all_id = alli.all_id
           INNER JOIN kb3_corps corp ON inv.ind_crp_id = corp.crp_id
      WHERE ki.kll_timestamp > DATE_SUB(CURDATE(), INTERVAL 28 DAY)
        AND const.con_reg_id = " . $region_id . "
        AND alli.all_name != 'None'
      GROUP BY kill_id, entity_name
     ) AS sq
GROUP BY entity_name, entity_id
ORDER BY kills DESC, entity_name ASC
LIMIT 20
";
    $result = $qry->execute($query);
    $entities = array();
    while ($row = $qry->getRow()) {
        $alliances[] = array('id' => $row['id'],
                             'name' => $row['name'],
                             'kills' => $row['kills']);
    }

    $query = "
SELECT COUNT(*) AS kills,
       entity_id AS id,
       entity_name AS name
FROM (SELECT inv.ind_kll_id AS kill_id,
             inv.ind_crp_id AS entity_id,
             corp.crp_name AS entity_name
      FROM kb3_inv_detail inv
           INNER JOIN kb3_kills ki ON inv.ind_kll_id = ki.kll_id
           INNER JOIN kb3_systems sys ON ki.kll_system_id = sys.sys_id
           INNER JOIN kb3_constellations const ON sys.sys_con_id = const.con_id
           INNER JOIN kb3_alliances alli ON inv.ind_all_id = alli.all_id
           INNER JOIN kb3_corps corp ON inv.ind_crp_id = corp.crp_id
      WHERE ki.kll_timestamp > DATE_SUB(CURDATE(), INTERVAL 28 DAY)
        AND const.con_reg_id = " . $region_id . "
      GROUP BY kill_id, entity_name
     ) AS sq
GROUP BY entity_name, entity_id
ORDER BY kills DESC, entity_name ASC
LIMIT 20
";
    $result = $qry->execute($query);
    $corps = array();
    while ($row = $qry->getRow()) {
        $corps[] = array('id' => $row['id'],
                         'name' => $row['name'],
                         'kills' => $row['kills']);
    }

    $query = "
SELECT COUNT(*) AS kills,
       entity_id AS id,
       entity_name AS name
FROM (SELECT inv.ind_kll_id AS kill_id,
             inv.ind_plt_id AS entity_id,
             pilot.plt_name AS entity_name
      FROM kb3_inv_detail inv
           INNER JOIN kb3_kills ki ON inv.ind_kll_id = ki.kll_id
           INNER JOIN kb3_systems sys ON ki.kll_system_id = sys.sys_id
           INNER JOIN kb3_constellations const ON sys.sys_con_id = const.con_id
           INNER JOIN kb3_alliances alli ON inv.ind_all_id = alli.all_id
           INNER JOIN kb3_corps corp ON inv.ind_crp_id = corp.crp_id
           INNER JOIN kb3_pilots pilot ON inv.ind_plt_id = pilot.plt_id
      WHERE ki.kll_timestamp > DATE_SUB(CURDATE(), INTERVAL 28 DAY)
        AND const.con_reg_id = " . $region_id . "
      GROUP BY kill_id, entity_name
     ) AS sq
GROUP BY entity_name, entity_id
ORDER BY kills DESC, entity_name ASC
LIMIT 20
";
    $result = $qry->execute($query);
    $pilots = array();
    while ($row = $qry->getRow()) {
        $pilots[] = array('id' => $row['id'],
                          'name' => $row['name'],
                          'kills' => $row['kills']);
    }


    global $smarty;
    $smarty->assign_by_ref('alliances', $alliances);
    $smarty->assign_by_ref('corps', $corps);
    $smarty->assign_by_ref('pilots', $pilots);
    $html = $smarty->fetch('../../../mods/top20regions/detail.tpl');
    $page->setContent($html);
    $page->generate();
}

function show_region_selection () {
    $page = new Page("Top 20 Regions");
    $qry = new DBQuery();
    $query = "
SELECT COUNT(*) AS kills,
       reg.reg_name AS name,
       reg.reg_id AS id
FROM kb3_kills ki
     INNER JOIN kb3_systems sys ON ki.kll_system_id = sys.sys_id
     INNER JOIN kb3_constellations const ON sys.sys_con_id = const.con_id
     INNER JOIN kb3_regions reg ON const.con_reg_id = reg.reg_id
WHERE ki.kll_timestamp > DATE_SUB(CURDATE(), INTERVAL 28 DAY)
GROUP BY reg.reg_name, reg.reg_id
ORDER BY kills DESC, name ASC";
    $result = $qry->execute($query);
    $regions = array();
    while ($row = $qry->getRow()) {
        $regions[] = array('id' => $row['id'],
                           'kills' => $row['kills'],
                           'name' => $row['name']);
    }
    global $smarty;
    $smarty->assign_by_ref('regions', $regions);
    $html = $smarty->fetch('../../../mods/top20regions/selection.tpl');
    $page->setContent($html);
    $page->generate();
}

if ($kll_id=intval($_GET['region_id'])) {
    show_region($kll_id);
} else {
    show_region_selection();
}
?>
