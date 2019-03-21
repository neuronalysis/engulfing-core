<?php
trait ConnectionManager {
	protected $connectionHost;
	protected $connectionUsername;
	protected $connectionPassword;
	
	protected $databaseConnections = array();
	
	function __construct() {
		
	}
	function openConnection($ontologyName = null) {
	    $databaseName = null;
	    
		$config = $this->getConfig();
		
		$this->connectionHost = $config['databases'][0]['host'];
		$this->connectionUsername = $config['databases'][0]['username'];
		$this->connectionPassword = $config['databases'][0]['password'];
		
		
		if (!$ontologyName) return null;
		
		if (isset($this->databaseConnections[$ontologyName])) {
			if (is_object($this->databaseConnections[$ontologyName])) {
				return $this->databaseConnections[$ontologyName];
			}
		}
		
		if ($this->debug) {
			echo "oname: " . $ontologyName . "\n";
			echo " new connection\n";
		}
		
		if (isset($config['databases'])) {
		    foreach($config['databases'] as $db_item) {
		    	if (strpos($db_item['name'], $ontologyName) !== false) {
		            $databaseName = $db_item['name'];
		        }
		    }
		}
		
		
		if (!$databaseName)	{
		    $databaseName = $config['databases'][0]['name'];
		}
		
		if ($this->debug) {
		    print_r($config['databases']);
		    
		    echo "db-name: " . $databaseName . "\n";
		}
		
		
		if (!isset($this->connectionHost)) {
			$this->connectionHost = $config['databases'][0]['host'];
			$this->connectionUsername = $config['databases'][0]['username'];
			$this->connectionPassword = $config['databases'][0]['password'];
		}
		
		$dbh = new PDO("mysql:host=$this->connectionHost;dbname=$databaseName", $this->connectionUsername, $this->connectionPassword);	
		$dbh->exec("set names utf8");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		
		$this->databaseConnections[$ontologyName] = $dbh;
		
		return $dbh;
	}
	function resetDataBaseConnections() {
		$this->databaseConnections = array();
	}
	function setDataBaseConnections($connections) {
		$this->databaseConnections = $connections;
	}
	function getDataBaseConnections() {
		return $this->databaseConnections;
	}
	function getDatabaseName($ontologyName = null) {
		if ($ontologyName == null) $ontologyName = $this->getOntologyName();
		if ($ontologyName == "extraction") $ontologyName = "nlp";
		
		if (class_exists($ontologyName)) {
			$ontology = new $ontologyName;
			
			if (isset($ontology->database)) {
				$databaseName = $ontology->database;
			} else {
				$databaseName = "engulfin_" . $ontologyName;
			}
		} else {
			$databaseName = "engulfin_" . $ontologyName;
		}
		
		
		return $databaseName;
	}
}
?>