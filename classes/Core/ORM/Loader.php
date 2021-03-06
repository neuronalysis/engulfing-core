<?php
trait Loader {
	protected $cascades = null;
	
	protected $loading = array();
	protected $loaded = array();
	
	function __construct() {
	}
	function getCascades() {
		return $this->cascades;
	}
	function startLoading($object, $id) {
		array_push($this->loading, $object . "_" . $id);
	}
	function endLoading($object, $id) {
		if(($key = array_search($object . "_" . $id, $this->loading)) !== false) {
			unset($this->loading[$key]);
		}
	}
	function isLoading($object, $id) {
		if(($key = array_search($object . "_" . $id, $this->loading)) !== false) return true;
	}
	function isLoadedObject($object, $id) {
		$filter = $object . "_" . $id;
		
		if (isset($this->loaded[$filter])) return $this->loaded[$filter];
		
		return false;
	}
	function isLoadedObjectsArray($object, $keyValues) {
		$filter = $object . "_" . implode("_", array_keys($keyValues)) . "_" . implode("_", array_values($keyValues));
		
		if (isset($this->loaded[$filter])) return $this->loaded[$filter];
		
		return false;
	}
	function storeObject($object) {
		$this->loaded[get_class($object). "_" . $object->id] = $object;
	}
	function storeObjectsArray($objects, $keyValues) {
		if (!isset($objects[0])) return null;
		
		$filter = get_class($objects[0]) . "_" . implode("_", array_keys($keyValues)) . "_" . implode("_", array_values($keyValues));
		
		$this->loaded[$filter] = $objects;
	}
	/*function fetch($object_name, $id) {
		if ($id == null) {
			return $this->fetchList($object_name);
		}
		
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			if (!apc_exists(strtolower($object_name) . "_" . $id)) {
				return false;
			} else {
				$data = apc_fetch(strtolower($object_name) . "_" . $id);
				
				return $data;
			}
		}
	}
	function fetchWikiArticle($id) {
		return $this->fetch("Article", $id);
	}
	function fetchList($object_name) {
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			if (!apc_exists(strtolower($object_name) . "_list")) {
				return false;
			} else {
				$data = apc_fetch(strtolower($object_name) . "_list");
		
				return $data;
			}
		}
	}
	function fetchFiltered($object_name, $filter) {
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			if (!apc_exists(strtolower($object_name) . print_r($filter, true))) {
				return false;
			} else {
				$data = apc_fetch(strtolower($object_name) . print_r($filter, true));
	
				return $data;
			}
		}
	}
	function store($object) {
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			apc_store(strtolower(get_class($object)) . "_" . $object->id, $object);
		}
	}
	function storeWikiArticle($article, $id) {
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			$article->OntologyClass->resetDataBaseConnections();
			foreach($article->OntologyClass->RelationOntologyClassOntologyClasses as $item_relococ) {
				$item_relococ->IncomingOntologyClass->resetDataBaseConnections();
			}
			apc_store("article_" . $id, $article);
		}
	}
	function storeList($list) {
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			apc_store(strtolower(get_class($list->items[0])) . "_list", $list);
		}
	}
	function storeFiltered($data, $object_name, $filter) {
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			apc_store(strtolower($object_name) . print_r($filter, true), $data);
		}
	}*/
}
?>