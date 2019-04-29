<?php
class Resource extends Thing {
	var $name;
	var $url;
	var $schemaDefinition;
	
	
	function __construct($url = null) {
		if ($url) $this->url = $this->preparePath($url);
	}
	function preparePath($path) {
		$path = str_replace(" ", "+", $path);
	
		return $path;
	}
	function getFileName() {
		if (strpos($this->resource_url, "/") !== false) {
			$slash_explode = explode("/", $this->resource_url);
			
			return $slash_explode[count($slash_explode) - 1] . ".pdf";
		}
		
		return "fuckit";
	}
	function loadByContent($content) {
	    error_reporting(E_ALL & ~E_NOTICE);
	    
	    $finfo = new finfo(FILEINFO_MIME);
	    $fio = new FileIO();
	    
	    $this->content = $content;
	    
	    $this->Type = $finfo->buffer($this->content);
	    if ($this->Type === "text/plain; charset=us-ascii") {
	        if ($this->isJson($this->content)) {
	            $this->Type = "application/json; charset=utf-8";
	        }
	    }
	    $this->size = strlen($this->content);
	    
	    if ($this->Type == "application/pdf; charset=binary" || $this->Type == "application/octet-stream; charset=binary") {
	        $filetime = $fio->filemtime_remote($this->url);
	        $this->modificationTime = date ("F d Y H:i:s.", $filetime);
	    }
	}
	function load($noDownload = false, $enforcedType = null, $headers = null) {
		error_reporting(E_ALL & ~E_NOTICE);
		
		$finfo = new finfo(FILEINFO_MIME);
		$fio = new FileIO();
		
		if (!$this->is_connected() || $noDownload) {
		    //$this->content = file_get_contents('data/temp/structure/processing/processed.html');
		    $this->content = file_get_contents($this->url);
			
		    
			if ($enforcedType) {
				$this->Type = $enforcedType;
			} else {
				$this->Type = $finfo->buffer($this->content);
			}
			
			$this->size = strlen($this->content);
			
			if ($this->Type == "application/pdf; charset=binary" || $this->Type == "application/octet-stream; charset=binary") {
				//$filetime = $fio->filemtime_remote('../data/temp/structure/processing/processed.html');
			    $filetime = $fio->filemtime_remote($this->url);
				$this->modificationTime = date ("F d Y H:i:s.", $filetime);
			}
		} else {
		    $this->content = $fio->loadFile($this->url, $headers);
		    
			$this->Type = $finfo->buffer($this->content);
			if ($this->Type === "text/plain; charset=us-ascii") {
				if ($this->isJson($this->content)) {
					$this->Type = "application/json; charset=utf-8";
				}
			}
			$this->size = strlen($this->content);
			
			if ($this->Type == "application/pdf; charset=binary" || $this->Type == "application/octet-stream; charset=binary") {
				$filetime = $fio->filemtime_remote($this->url);
				$this->modificationTime = date ("F d Y H:i:s.", $filetime);
			}
		}
	}
	function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
}
?>
