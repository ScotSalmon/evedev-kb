<?php
require_once('class.pilot.php');
// require_once('class.corp.php');
require_once('class.alliance.php');
require_once('class.kill.php');

class Scouts
{
	function __construct($kll_id = '')
	{
		if ($kll_id != '')
		{
			$this->init($kll_id);
		}
	}

	function init($kll_id)
	{
		$this->id_ = $kll_id;
		$this->raw_ = false;
		$this->scouts_ = array();
		$qry = new DBQuery(true);
		$qry->execute("SELECT * FROM kb3_scout WHERE `inp_kll_id` = '".$kll_id."'");
		while ($row = $qry->getRow())
		{
			$pilotid = $row['inp_plt_id'];
			$pilot = new Pilot($pilotid);
			$corp = $pilot->getCorp();
			$corpid = $corp->getID();
			$alliance = $corp->getAlliance();
			$allianceid = $alliance->getID();
			$pilotname = $pilot->getName();
			$corpname = $corp->getName();
			$alliancename = $alliance->getName();
			$img = $pilot->getPortraitURL(64);
			$this->scouts_[] = array('pilotid' => $row['inp_plt_id'], 'scoutid' => $row['scout_id'], 'pilotname' => $pilotname, 'corp' => $corpname, 'corpid' => $corpid, 'alliance' => $alliancename, 'allianceid' => $allianceid, 'pilotimg' => $img, 'killid' => $kll_id);
		}
	}

	function getScouts($passReq, $isAdmin, $confirmDelete, $confirmDeleteScoutID, $confirmDeletePilotID, $confirmDeletePilotName, $error)
	{
		global $smarty;

		$smarty->assignByRef('scouts', $this->scouts_);
		$smarty->assign('scoutPassReq', $passReq);
		$smarty->assign('isAdmin', $isAdmin);
		$smarty->assign('confirmDelete', $confirmDelete);
		$smarty->assign('confirmDeleteScoutID', $confirmDeleteScoutID);
		$smarty->assign('confirmDeletePilotID', $confirmDeletePilotID);
		$smarty->assign('confirmDeletePilotName', $confirmDeletePilotName);
		$smarty->assign('error', $error);
		return $smarty->fetch('../../../mods/scout/scout.tpl');
	}

	function addScout($pilot_name)
	{
		$scoutship = "9999";

		//get pilot order
		$pqry = new DBPreparedQuery();
		$pqry->prepare("select max(ind_order) from kb3_inv_detail where ind_kll_id = ?");
		$pqry->bind_param('i', $this->id_);
		$pqry->bind_result($scoutorder);
		if (!$pqry->execute() || !$pqry->recordCount())
		{
			return false;
		}
		else
		{
			$pqry->fetch();
		}

		$scoutorder = $scoutorder + 1;

		//lookup pilot id by name
		$pqry = new DBPreparedQuery();
		$pqry->prepare("select plt_id from kb3_pilots where plt_name = ?");
		$pqry->bind_param('s', stripslashes($pilot_name));
		$pqry->bind_result($pilotid);
		if (!$pqry->execute() || !$pqry->recordCount())
		{
			return false;
		}
		else
		{
			$pqry->fetch();
		}

		$qry = new DBQuery(true);
		$qry->execute("INSERT INTO kb3_scout (`inp_kll_id`,`inp_plt_id`) VALUES ('".$this->id_."','".$pilotid."')");
		$qry->execute("INSERT INTO kb3_inv_detail (`ind_kll_id`,`ind_plt_id`,`ind_shp_id`,`ind_order`) VALUES ('".$this->id_."','".$pilotid."','".$scoutship."','".$scoutorder."')");
                $this->updateTables();
		$pilot = new Pilot($pilotid);
		$corp = $pilot->getCorp();
		$corpid = $corp->getID();
		$alliance = $corp->getAlliance();
		$allianceid = $alliance->getID();
		$pilotname = $pilot->getName();
		$corpname = $corp->getName();
		$alliancename = $alliance->getName();
		$img = $pilot->getPortraitURL(64);
		$this->scouts_[] = array('pilotid' => $row['inp_plt_id'], 'scoutid' => $row['scout_id'], 'pilotname' => $pilotname, 'corp' => $corpname, 'corpid' => $corpid, 'alliance' => $alliancename, 'allianceid' => $allianceid, 'pilotimg' => $img, 'killid' => $kll_id);

		//make sure involved count is shown correctly (it's generated before this class is loaded)
		header("Location: ?a=kill_detail&kll_id=".$this->id_);
        exit;
	}

	function delScout($s_id, $pilotid)
	{
		$qry = new DBQuery(true);
		$qry->execute("delete from kb3_scout where inp_kll_id = ".$this->id_." and scout_id = ".$s_id." limit 1");

		//get pilot order to be deleted
		$pqry = new DBPreparedQuery();
		$pqry->prepare("select ind_order from kb3_inv_detail where ind_kll_id = ? and ind_plt_id = ?");
		$pqry->bind_param('ii', $this->id_, $pilotid);
		$pqry->bind_result($scoutOrder);
		if (!$pqry->execute() || !$pqry->recordCount())
		{
			return false;
		}
		else
		{
			$pqry->fetch();
		}

		//get highest pilot order
		$pqry = new DBPreparedQuery();
		$pqry->prepare("select max(ind_order) from kb3_inv_detail where ind_kll_id = ?");
		$pqry->bind_param('i', $this->id_);
		$pqry->bind_result($maxScoutOrder);
		if (!$pqry->execute() || !$pqry->recordCount())
		{
			return false;
		}
		else
		{
			$pqry->fetch();
		}

		$qry->execute("delete from kb3_inv_detail where ind_kll_id = ".$this->id_." and ind_plt_id = ".$pilotid." and ind_shp_id = '9999' limit 1");

		//reorder remaining scouts
		for($i = $scoutOrder + 1; $i <= $maxScoutOrder; $i++)
		{
			$qry->execute("update kb3_inv_detail set ind_order = '".($i-1)."' where ind_kll_id = '".$this->id_."' and ind_shp_id = '9999' and ind_order = '".$i."' limit 1");
		}

		//make sure involved count is shown correctly (it's generated before this class is loaded)
		header("Location: ?a=kill_detail&kll_id=".$this->id_);
        exit;
	}

        function updateTables () {
            $qry = new DBQuery(true);
            $killid = $this->id_;
            $qry->execute("DELETE FROM kb3_inv_detail 
                           WHERE ind_kll_id = $killid AND ind_shp_id = 9999");
            $qry->execute("INSERT INTO kb3_inv_detail
                               (ind_kll_id, ind_timestamp, ind_plt_id,
                                ind_all_id, ind_crp_id, ind_shp_id)
                           SELECT scout.inp_kll_id, kll.kll_timestamp,
                                  scout.inp_plt_id, crp.crp_all_id,
                                  plt.plt_crp_id, 9999
                           FROM kb3_scout scout
                             INNER JOIN kb3_kills kll
                               ON scout.inp_kll_id = kll.kll_id
                             INNER JOIN kb3_pilots plt
                               ON scout.inp_plt_id = plt.plt_id
                             INNER JOIN kb3_corps crp
                               ON plt.plt_crp_id = crp.crp_id
                           WHERE scout.inp_kll_id = $killid");
            $qry->execute("DELETE FROM kb3_inv_crp 
                           WHERE inc_kll_id = $killid");
            $qry->execute("INSERT INTO kb3_inv_crp 
                               (inc_kll_id, inc_crp_id, inc_timestamp)
                           SELECT DISTINCT ind_kll_id, ind_crp_id, ind_timestamp
                           FROM kb3_inv_detail
                           WHERE ind_kll_id = $killid");

            $qry->execute("DELETE FROM kb3_inv_all
                           WHERE ina_kll_id = $killid");
            $qry->execute("INSERT INTO kb3_inv_all
                               (ina_kll_id, ina_all_id, ina_timestamp)
                           SELECT DISTINCT ind_kll_id, ind_all_id, ind_timestamp
                           FROM kb3_inv_detail
                           WHERE ind_kll_id = $killid");
        }
}
?>
