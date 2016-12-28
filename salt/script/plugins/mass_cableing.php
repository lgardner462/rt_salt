<?php


$page['port_mass_cableing']['title'] = 'Mass Cabeling';
$page['port_mass_cableing']['parent'] = 'index';
$tab['port_mass_cableing']['default'] = 'Summary';
$tabhandler['port_mass_cableing']['default'] = 'renderMassCableing';
$ophandler['port_mass_cableing']['default']['UpdateCableing'] = 'UpdateCableing';

array_push($indexlayout[3], 'port_mass_cableing');

// global $logfh;
// $LogFile = "/tmp/manon.log";
// $logfh = fopen($LogFile, 'a') or die("can't open file");
// fwrite($logfh,"\n/\n/\n/ New Request \n");



function renderMassCableing()
{
	echo "<h1> test</h2>";
	echo "\n";
	echo "\n<!-- printOpFormIntro ('copyLotOfObjects') -->\n";
	printOpFormIntro ('UpdateCableing');
	echo "\n";
	startPortlet ('Same type, same tags');
	echo "\n" . '<table border=1 align=center>';
	echo "\n" . '<tr><th>"Object A","Port_Obj_A","Object B","Port_Obj_B","0/336","0" (no csv escaping) <br>' . "\n";
	echo '"DUS04.MESH01-CU-01.04-02.02","P1","pk-100-cat7-001119","P1","0","0"<br>';
	echo '"DUS04.MESH01-CU-01.04-02.02","P1","pk-100-cat7-001119","P1","336","0"<br>';
	echo '"DUS04.MESH01-CU-01.04-02.02","P1","BackEndCableTest","P1","336","0"<br>';
	echo '"DUS04.MESH01-CU-01.04-02.02","P1","pk-100-mm-001011","P1","336","0"<br>';
	echo "</th><th>type</th></tr>";
	echo "<tr><td><input type=submit name=got_very_fast_data value='Go!'></td><td></td></tr>\n";
	echo "\n" . "<tr><td><textarea name=namelist cols=80 rows=40>\n</textarea></td>";
	echo "<td valign=top>";
	echo "</td></tr>";
	echo "<tr><td colspan=2><input type=submit name=got_very_fast_data value='Go!'></td></tr></table>\n";
	echo "</form>\n";
	finishPortlet();
	// EOF_Stolen
	/*
	startPortlet ('debug');
 	var_dump (getPortListPrefs() );
 	var_dump (getNewPortTypeOptions() );
	echo "</pre>";
	finishPortlet();
	*/

}

function UpdateCableing()
{
	global $dbxlink;
	$dbrollback = 0;
	$object_cache = new MassCableingObjectCache;
	if (! $dbxlink->beginTransaction() ) 
		throw new  RTDatabaseError ("can not start transaction");
	$log = emptyLog();
	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	assertStringArg ('namelist', TRUE);
	if (!strlen($_REQUEST['namelist']))
		$log = mergeLogs ($log, oneLiner (186));
	else
	{
		// The name extractor below was stolen from ophandlers.php:addMultiPorts()
		$names1 = explode ("\n", $_REQUEST['namelist']);
		$names2 = array();
		foreach ($names1 as $line)
		{
			$parts = explode ('\r', $line);
			reset ($parts);
			if (!strlen ($parts[0]))
				continue;
			else
				$names2[] = rtrim ($parts[0]);
		}
		foreach ($names2 as $name)
		{
			$name = htmlspecialchars_decode($name, ENT_QUOTES);	
			$object1 = '';
			$portofobject1 = '';
			$object2 = '';
			$portofobject2 = '';
			$connection_type = '';
			$regexp='/^\"([^\"]*)\","([^\"]*)\","([^\"]*)\","([^\"]*)\"(,"([^\"]*)\","([^\"]*)\")?/';
			if (preg_match($regexp, $name, $matches) ) 
			{
				$object_a_name = $matches[1];
				$portofobject_a_name = $matches[2];
				$object_b_name = $matches[3];
				$portofobject_b_name = $matches[4];
				$link_type = $matches[6];
				$link_overwrite = $matches[7];
			} 
			else 
			{
				$dbrollback = 1;
			}
			try
			{
				// global $logfh;
				// fwrite($logfh,"\nX $name $object_a_name $portofobject_a_name $object_b_name $portofobject_b_name  $link_type $link_overwritei\n");
				// fwrite($logfh,"\n");
				$masscabeling = new PortLinker($object_cache, $object_a_name, $object_b_name,$link_overwrite, $link_type);
				$masscabeling->set_port_a_by_name($portofobject_a_name);
				$masscabeling->set_port_b_by_name($portofobject_b_name);
				$masscabeling->set_overwrite($link_overwrite);
				$masscabeling->check();
				$masscabeling->portlink();
				// error_log ("End fine");
				// Copy links
				$log = mergeLogs ($log, oneLiner (5, array (
					sprintf('Connected [<a href="%s">%s-%s</a>] -- [<a href="%s">%s-%s</a>]',
			 			makeHref(array('page'=>'object',
							'tab' => "PortCabeling",
							'object_id'=>$masscabeling->object_a->get_id(),
							'hl_port_id'=>$masscabeling->get_port_a_id()
						)),
						$object_a_name,
						$portofobject_a_name,
			 			makeHref(array('page'=>'object',
							'tab' => "PortCabeling",
							'object_id'=>$masscabeling->object_b->get_id(),
							'hl_port_id'=>$masscabeling->get_port_b_id()
						)),
						$portofobject_b_name,
						$object_b_name
					)
				)));
			}
			catch (RTDatabaseError $e)
			{
				error_log("rolling back DB");
				$dbrollback = 1;
				$dbxlink->rollBack();
				$log = mergeLogs ($log, oneLiner (147, array ($object_name)));
				throw new RTDatabaseError (sprintf("<h3>%s</h3>", $e->getMessage() ) );
			}
			catch (LocalRackTablesException $e)
			{
				error_log("rolling back DB");
				$dbrollback = 1;
				$dbxlink->rollBack();
				$log = mergeLogs ($log, oneLiner (147, array ($object_name)));
				throw new RTDatabaseError ($e->getMessage() );
			}
		}
	}
	if (! $dbrollback )
		$dbxlink->commit();
	//return buildWideRedirectURL ($log);
}

Class MassCableingObjectCache {
	private $_object_cache = array();
	private $_poifc = array();


	function get($id)
	{
		return $this->_object_cache[$id];
	}

	function add($id)
	{
		if ( !$this->_object_cache[$id] )
		{
			$tmp = spotEntity ('object', $id);
			amplifyCell ($tmp);
			$this->_object_cache[$id] = $tmp;
			unset($tmp);
			// error_log("Cache Populate $id");
		}
		// else 
			// error_log("Cache Hit $id");
	}	
	function return_poifc () {
		if (empty( $this->_poifc))
		{
			foreach (getPortOIFCompat() as $item)
			{
				$this->_poifc[$item['type1']][$item['type2']] = TRUE;
				$this->_poifc[$item['type2']][$item['type1']] = TRUE;
			}
		}
		return $this->_poifc;
	}
		 
}
Class PortLinker {
	public $object_cache;
	public $object_a;
	public $object_b;
	private $_port_a_name = '';
	private $_port_b_name = '';
	private $_port_a_id = '';
	private $_port_b_id = '';
	private $_port_a_oif_id = '';
	private $_port_b_oif_id = '';
	private $_cableid = '';
	private $_link_type;
	private $_overwrite;

	function __construct (& $object_cache, $object_a_name, $object_b_name, $link_overwrite=0, $link_type=0)
	{	
		$this->object_cache = $object_cache;
		$this->set_object_a_by_name($object_a_name);
		$this->set_object_b_by_name($object_b_name);
		$this->set_link_type($link_type);
		$this->set_overwrite($link_overwrite);
	}
	function portlink()
	{
		// error_log(sprintf("porta: %s, portb: %s cabel: %s link_type: %s", $this->_port_a_id, $this->_port_b_id, $this->_cableid, $this->_link_type));
		linkPorts( $this->_port_a_id, $this->_port_b_id, $this->_cableid, $this->_link_type);
	}

	function check()
	{
		$poifc = $this->object_cache->return_poifc();
		if ($poifc[$this->_port_a_oif_id][$this->_port_b_oif_id])
			return TRUE;
		throw new LocalRackTablesException( sprintf ('Port "%s" from Object "%s" not compatible with Port "%s" from Object "%s"', $this->_port_a_oif_id, $this->object_a->get_name(), $this->_port_a_oif_id, $this->object_b->get_name() ) ) ;

	}
	function get_overwrite()
	{
		return $this-_overwrite;
	}
	function set_overwrite($link_overwrite)
	{
		$this->_overwrite = $overwrite;
	}

	function get_port_a_id()
	{
		return $this->_port_a_id;
	}

	function get_port_b_id()
	{
		return $this->_port_b_id;
	}

	function get_link_type()
	{
		return $this->_link_type;
	}
	function set_link_type($link_type)
	{
		// global $logfh;
		// fwrite($logfh, "Linktype: $link_type\n");
		$this->_link_type = $link_type;
	}

	function set_object_a_by_name($name)
	{
		try 
		{
		$this->object_a = new LinkObject ( $this->object_cache );
		$this->object_a->serach_for_id_by_name(
							'RackObject', 
							'id',
							'name',
							$name,
							'',
							1
						);
		}
		catch(RethrowableEntityNotFoundException $e) 
		{ 	throw new RethrowableEntityNotFoundException($e->getMessage()); }
	}
	
	function set_object_b_by_name($name)
	{
		$this->object_b = new LinkObject ( $this->object_cache );
		$this->object_b->serach_for_id_by_name(
							'RackObject', 
							'id',
							'name',
							$name,
							'',
							1
						);
	}
	
	function set_port_a_by_name($name)
	{
		try
		{
			$this->_port_a_oif_id = $this->object_a->get_port_oif_id($name,$this->get_link_type() );
			$this->_port_a_id = $this->object_a->get_port_id($name,$this->get_link_type() );
			$this->_port_a_name =$name;
		}
		catch(LinkExistsException $e)
		{
			throw new LocalRackTablesException ($e->getMessage());
		}
	}
	
	function set_port_b_by_name($name)
	{
		try
		{
			$this->_port_b_oif_id = $this->object_b->get_port_oif_id($name,$this->get_link_type() );
			$this->_port_b_id = $this->object_b->get_port_id($name,$this->get_link_type() );
			$this->_port_b_name =$name;
		}
		catch(LinkExistsException $e)
		{
			throw new LocalRackTablesException ($e->getMessage());
		}
	}
}

class LinkExistsException extends Exception { }
class LocalRackTablesException extends RackTablesError { }
Class LinkObject {
	private $_name;
	private $_id;
	private $_cache;
	public $object;

	function __construct ( & $cache)
	{
		$this->_cache = $cache;
	}

	function serach_for_id_by_name ($tname, $rcolumn, $scolumn, $terms, $ocolumn = '', $exactness = 0)
	{
		$tmp = getSearchResultByField($tname, array($rcolumn) , $scolumn, $terms, $ocolumn, $exactness );
		$notunique=0;
		foreach ($tmp as $row)
		{
			if ($notunique)
				throw new RTDatabaseError("Query $terms on $scolumn not uniqe");
			$this->set_id($row[$rcolumn]);
			$notunique=1;
		}
		if ($this->get_id())
		{
			$this->_name = $terms;
			$this->_cache->add($this->get_id());
			return $this->get_id();
		}
		throw new LocalRackTablesException("could not find id for $terms");
	}
	

	function set_id($id)
	{
		$this->_id = $id;
	}

	function get_id()
	{
		return $this->_id;
	}

	function get_name()
	{
		return $this->_name;
	}

	function get_object()
	{
		return $this->_cache->get($this->get_id());
	}

	function get_port_id ($port_name, $link_type)
	{
		$port = $this->get_port($port_name, $link_type);
		return $port['id'];
	}

	function get_port_oif_id ($port_name, $link_type)
	{
		$port = $this->get_port($port_name, $link_type);
		return $port['oif_id'];
	}

	function get_port($port_name, $link_type)
	{
		$object = $this->get_object();
		foreach ($object['ports'] as $port)
		{
			if ($port['name'] == $port_name)
			{
				// global $logfh;
				// fwrite($logfh, print_r($port,TRUE) );
				// fwrite($logfh, sprintf('checking Portname "%s" with linktype "%s" for remote_object_id "%s"', $port_name, $link_type, $port[$link_type]['remote_object_id'] ));
				// fwrite($logfh, "\n");
				if ($port[$link_type]['remote_object_id'] )
				{
					throw new LinkExistsException( sprintf('Port "%s" on Object "%s" alredy linked with id "%s".', $port_name, $this->get_name(), $port[$link_type]['remote_object_id'] ) );
				}
				return $port;
			}
		}
		throw new LocalRackTablesException(sprintf(
				'Object "%s" with object_id "%s" does not have Port "%s" ' , $this->_name, $this->get_id(), $port_name ) );
	}
}




?>


