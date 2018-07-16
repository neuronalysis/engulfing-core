<?php
include_once (__DIR__ . "/../../../engulfing-core/classes/Core/Helper.php");
include_once (__DIR__ . "/../../../engulfing-core/classes/Core/AccessControl.php");
include_once (__DIR__ . "/../../../engulfing-core/classes/Core/ResourceLoader.php");
include_once (__DIR__ . "/../../../engulfing-core/classes/Core/DOMHelper.php");
include_once (__DIR__ . "/../../../engulfing-core/classes/Core/ORM/ORM.php");

class Thing {
	var $id;
	
	protected $createdBy;
	protected $createdAt;
	protected $updatedBy;
	protected $updatedAt;
	
	protected $cascade;
	
	protected $constraintsUnique;
	protected $defaultOrder;
	
	//
	use Helper;
	use ResourceLoader;

	
	function __construct($keyValues = array()) {
		foreach($keyValues as $key => $value) {
			$this->$key = $value;
		}
	}
	function setGenericOntologyName($ontologyName) {
		$this->ontologyName = $ontologyName;
	}
	/*function getRelations() {
		return $this->relations;
	}*/
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
		$this->updatedBy = $updatedBy;
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
	function getConstraintsUnique() {
		return $this->constraintsUnique;
	}
	function setConstraintsUnique($constraintsUnique) {
		$this->constraintsUnique = $constraintsUnique;
	}
	function getDefaultOrder() {
		return $this->defaultOrder;
	}
}
?>