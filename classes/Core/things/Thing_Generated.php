<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-core/classes/Core/Helper.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/AccessControl.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/RessourceLoader.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/DOMHelper.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ORM.php");

//class Thing_Generated implements JsonSerializable {
class Thing_Generated {
	var $id;
	
	protected $createdBy;
	protected $createdAt;
	protected $updatedBy;
	protected $updatedAt;
	
	protected $cascade;
	
	use ORM;
	use Helper;
	use AccessControl;
	use RessourceLoader;

	
	function __construct() {
	}
	function setGenericOntologyName($ontologyName) {
		$this->ontologyName = $ontologyName;
	}
	function getRelations() {
		return $this->relations;
	}
	function getGenericOntologyName() {
		return $this->ontologyName;
	}
	function setClassName($className) {
		$this->className = $className;
	}
	function getClassName() {
		return $this->className;
	}
	function setCreatedBy($createdBy) {
		$this->createdBy = $createdBy;
	}
	function setCreatedAt($createdAt) {
		$this->createdAt = $createdAt;
	}
	function getCreatedBy() {
		return $this->createdBy;
	}
	function getCreatedAt() {
		return $this->createdAt;
	}
	function setUpdatedBy($updatedBy) {
		$this->createdBy = $updatedBy;
	}
	function setUpdatedAt($updatedAt) {
		$this->updatedAt = $updatedAt;
	}
	function getUpdatedBy() {
		return $this->updatedBy;
	}
	function getUpdatedAt() {
		return $this->updatedAt;
	}
	function getCascade() {
		return $this->cascade;
	}
	function setCascade($cascade) {
		$this->cascade = $cascade;
	}
	/*public function jsonSerialize()
	{
		return array(
				'id' 	=> $this->id,
				'name' 	=> $this->name
		);
	}*/
}
?>