<?php
class Ressource_Copy_From_Extraction {
	var $id;
	var $path;
	var $type;
	var $size;
	var $modificationTime;
	var $pages;
	var $language;
	
	
	var $ressource_url;
	var $ressource_processing_timestamp;
	
	var $content;
	
	function __construct($path = null) {
		$this->path = $this->preparePath($path);
	}
	function preparePath($path) {
		$path = str_replace(" ", "+", $path);
		
		return $path;
	}
	function getFileName() {
		if (strpos($this->ressource_url, "/") !== false) {
			$slash_explode = explode("/", $this->ressource_url);
			
			return $slash_explode[count($slash_explode) - 1] . ".pdf";
		}
		
		return "fuckit";
	}
	function is_connected()
	{
		$connected = @fsockopen("www.google.com", 80);
		//website, port  (try 80 or 443)
		if ($connected){
			$is_conn = true; //action when connected
			fclose($connected);
		}else{
			$is_conn = false; //action in connection failure
		}
		return $is_conn;
	
	}
	function load($noDownload = false, $enforcedType = null) {
		error_reporting(E_ALL & ~E_NOTICE);
		
		$finfo = new finfo(FILEINFO_MIME);
		$fio = new FileIO();
		
		if (!$this->is_connected() || $noDownload) {
			//echo getcwd();
			$this->content = file_get_contents('data/temp/structure/processing/processed.html');
				
			if ($enforcedType) {
				$this->Type = $enforcedType;
			} else {
				$this->Type = $finfo->buffer($this->content);
			}
			//echo "type: " . $this->Type . "\n";
			
			$this->size = strlen($this->content);
			
			if ($this->Type == "application/pdf; charset=binary" || $this->Type == "application/octet-stream; charset=binary") {
				$filetime = $fio->filemtime_remote('../data/temp/structure/processing/processed.html');
				$this->modificationTime = date ("F d Y H:i:s.", $filetime);
			}
		} else {
			$this->content = $fio->loadFile($this->path);
			
			//echo $this->path . "\n";
			
			$this->Type = $finfo->buffer($this->content);
			
			//echo "type: " . $this->Type . "\n";
			$this->size = strlen($this->content);
			
			if ($this->Type == "application/pdf; charset=binary" || $this->Type == "application/octet-stream; charset=binary") {
				$filetime = $fio->filemtime_remote($this->path);
				$this->modificationTime = date ("F d Y H:i:s.", $filetime);
			}
		}
		
		//echo $this->content;
	}
	function save() {
		$rest = new REST();
		
		if ($this->id == null) {
			$response = $rest->request("webcrawling/api/ressources", "POST", $this);
		} else {
			$response = $rest->request("webcrawling/api/ressources/" . $this->id, "PUT", $this);
		}
	
		$restTransformer = new REST_Transformer();
		$result = $restTransformer->deserialize_JSON($response, "Ressource");
	
		return $result;
	}
	
}
?>