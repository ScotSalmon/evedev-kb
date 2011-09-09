<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Contains the attributes of a Ship and standard methods to manipulate Ships.
 * @package EDK
 */
class Ship extends Cacheable
{
	/** @var boolean */
	private $executed = false;
	/** @var integer */
	private $id = 0;
	/** @var integer */
	private $externalid = null;
	/** @var string */
	private $shipname = null;
	/** @var ShipClass */
	private $shipclass = null;
	/** @var integer */
	private $shiptechlevel = null;
	/** @var boolean */
	private $shipisfaction = null;
	/** @var float */
	private $value = 0;

	/**
	 * Construct the Ship object.
	 *
	 * A Ship object can be constructed from an ID and further details fetched
	 * from the db. It can also be constructed by passing more details to the
	 * constructor.
	 *
	 * @param integer $id The Ship ID.
	 * @param integer $externalID The Ship external ID.
	 * @param string $name The Ship name.
	 * @param ShipClass $class The ShipClass for this Ship.
	 */
	function Ship($id = 0, $externalID = null, $name = null, $class = null)
	{
		if ($id) {
			$this->id = (int)$id;
		}
		if (isset($externalID)) {
			$this->externalid = (int)$externalID;
		}
		if (isset($name)) {
			$this->shipname = $name;
		}
		if (isset($class)) {
			$this->shipclass = $class;
		}
	}

	/**
	 * Return the id for this Ship.
	 *
	 * @return integer id for this Ship.
	 */
	function getID()
	{
		if ($this->id) {
			return $this->id;
		} else if (isset($this->externalid)) {
			$this->execQuery();
			return $this->id;
		}
		return 0;
	}

	/**
	 * Return the external id for this Ship.
	 *
	 * @return integer external id for this Ship.
	 */
	function getExternalID()
	{
		if (!$this->externalid) {
			$this->execQuery();
		}
		return $this->externalid;
	}

	/**
	 * Return the name of this Ship.
	 *
	 * @return string name of this Ship.
	 */
	function getName()
	{
		if (is_null($this->shipname)) {
			$this->execQuery();
		}
		return $this->shipname;
	}

	/**
	 * Return the ShipClass for this Ship.
	 *
	 * @return ShipClass object for this Ship.
	 */
	function getClass()
	{
		if (is_null($this->shipclass)) {
			$this->execQuery();
		}
		return $this->shipclass;
	}

	/**
	 * Return the tech level of this Ship.
	 *
	 * @return integer tech level for this Ship.
	 */
	function getTechLevel()
	{
		if (is_null($this->shiptechlevel)) {
			$this->execQuery();
		}
		return $this->shiptechlevel;
	}

	/**
	 * Return if this Ship is faction.
	 *
	 * @return boolean factionality for this Ship.
	 */
	function isFaction()
	{
		if (is_null($this->shipisfaction)) {
			$this->execQuery();
		}
		return $this->shipisfaction;
	}

	/**
	 * Return the URL for a portrait of this Ship.
	 *
	 * @param integer $size the size of the image to return.
	 * @return string containing valid URL for a portrait of this Ship.
	 */
	function getImage($size)
	{
		if (is_null($this->externalid)) {
			$this->execQuery();
		}

		return imageURL::getURL('Ship', $this->externalid, $size);
	}

	/**
	 * Return the base price of this Ship.
	 *
	 * @return float a number representing the baseprice of this Ship.
	 */
	function getPrice()
	{
		if (!$this->value) {
			$this->execQuery();
		}
		return $this->value;
	}

	/**
	 * Set the name of this ship.
	 *
	 * @param string $shipname the name to set for this Ship
	 */
	function setName($shipname)
	{
		$this->shipname = $shipname;
	}

	/**
	 * Set the class of this ship.
	 *
	 * @param ShipClass $shipclass the class object to set for this Ship
	 */
	function setClass($shipclass)
	{
		$this->shipclass = $shipclass;
	}

	function execQuery()
	{
		if (!$this->executed) {
			if ($this->id && $this->isCached()) {
				$cache = $this->getCache();
				$this->shipname = $cache->shipname;
				$this->shipclass = $cache->shipclass;
				$this->shiptechlevel = $cache->shiptechlevel;
				$this->shipisfaction = $cache->shipisfaction;
				$this->externalid = $cache->externalid;
				$this->id = $cache->id;
				$this->value = $cache->value;
				$this->executed = true;
				return;
			}

			$qry = DBFactory::getDBQuery();

			$sql = "select * from kb3_ships shp
						   inner join kb3_ship_classes scl on shp.shp_class = scl.scl_id";
			$sql .= ' left join kb3_item_price itm on (shp.shp_externalid = itm.typeID) ';
			if (is_null($this->externalid)) {
				$sql .= " where shp.shp_id = ".$this->id;
			} else {
				$sql .= " where shp.shp_externalid = ".$this->externalid;
			}

			$qry->execute($sql);
			$row = $qry->getRow();
			$this->shipname = $row['shp_name'];
			$this->shipclass = Cacheable::factory('ShipClass', $row['scl_id']);
			$this->shiptechlevel = (int) $row['shp_techlevel'];
			$this->shipisfaction = (boolean) $row['shp_isfaction'];
			$this->externalid = (int) $row['shp_externalid'];
			$this->id = (int) $row['shp_id'];

			if (!$this->value = (float) $row['price']) {
				$this->value = (float) $row['shp_baseprice'];
			}

			if ($this->id) {
				$this->putCache();
			}
		}
		$this->executed = true;
	}

	/**
	 * Look up a Ship by name.
	 *
	 * @param string $name a string containing a ship name.
	 */
	static function lookup($name)
	{
		$pqry = new DBPreparedQuery();
		$pqry->prepare("select shp_id, shp_name, shp_externalid, shp_class "
				."from kb3_ships where shp_name = ?");
		$pqry->bind_param('s', $name);
		$id = 0;
		$external_id = 0;
		$name = "";
		$scl_id = 0;
		$pqry->bind_result($id, $name, $external_id, $scl_id);
		if (!$pqry->execute() || !$pqry->recordCount()) {
			return false;
		} else {
			$pqry->fetch();
		}

		$shipclass = Cacheable::factory('ShipClass', $scl_id);
		return new Ship($id, $external_id, $name, $shipclass);
	}
}