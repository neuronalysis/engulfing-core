<?php
trait ConnectionManager {
	protected $connectionHost = "127.0.0.1";
	//protected $connectionUsername = "engulfin_od";
	//protected $connectionPassword = "hom3stak378";
	protected $connectionUsername = "root";
	protected $connectionPassword = "";
	
	protected $databaseConnections = array();
	
	function ConnectionManager() {
	}
	function openConnection($ontologyName = null) {
		if (!$ontologyName) return null;
		
		if (isset($this->databaseConnections[$ontologyName])) {
			if (is_object($this->databaseConnections[$ontologyName])) {
				//echo " existing connection\n";
				
				return $this->databaseConnections[$ontologyName];
			}
		}
		
		//echo "oname: " . $ontologyName . "";
		//echo " new connection\n";
		
		$databaseName = $this->getDatabaseName($ontologyName);
		
		if (!isset($this->connectionHost)) {
			$this->connectionHost = "127.0.0.1";
			$this->connectionUsername = "root";
			$this->connectionPassword = "";
		}
		
		$dbh = new PDO("mysql:host=$this->connectionHost;dbname=$databaseName", $this->connectionUsername, $this->connectionPassword);	
		$dbh->exec("set names utf8");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//$dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
		
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