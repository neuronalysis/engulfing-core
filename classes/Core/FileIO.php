<?php
class FileIO {
	/*
	* Konstruktor
	*/
	function __construct() {
	}
	function streamFile($source) {
		$stream = "";
		
		if (file_exists($source)) {
			$fp = fopen ($source, "r");
		
			$stream = fread($fp, filesize($source));
			
			fclose ($fp);
		}
		return $stream;
	}
	function loadFile($path) {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $path);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		$contents = curl_exec($ch);
		if (curl_errno($ch)) {
			throw new Exception(curl_error($ch));
			
			//echo curl_error($ch);
			//echo "\n<br />";
			$contents = '';
		} else {
			curl_close($ch);
		}
		
		if (!is_string($contents) || !strlen($contents)) {
			//echo "Failed to get contents.";
			$contents = '';
		}
		
		return $contents;
	}
	function remote_filesize($url) {
		static $regex = '/^Content-Length: *+\K\d++$/im';
		if (!$fp = @fopen($url, 'rb')) {
			return false;
		}
		if (
		isset($http_response_header) &&
		preg_match($regex, implode("\n", $http_response_header), $matches)
		) {
			return (int)$matches[0];
		}
		return strlen(stream_get_contents($fp));
	}
	function filemtime_remote($uri) {
	    $uri = parse_url($uri);
	    $handle = @fsockopen($uri['host'],80);
	    if(!$handle)
	        return 0;
	
	    fputs($handle,"GET $uri[path] HTTP/1.1\r\nHost: $uri[host]\r\n\r\n");
	    $result = 0;
	    while(!feof($handle))
	    {
	        $line = fgets($handle,1024);
	        if(!trim($line))
	            break;
	
	        $col = strpos($line,':');
	        if($col !== false)
	        {
	            $header = trim(substr($line,0,$col));
	            $value = trim(substr($line,$col+1));
	            if(strtolower($header) == 'last-modified')
	            {
	                $result = strtotime($value);
	                break;
	            }
	        }
	    }
	    fclose($handle);
	    return $result;
	}
	function saveStringToFile($string, $target) {
		$file = fopen($target, 'w+', 1);
		$text=$string;
		fwrite($file, $text);
		fclose($file);
	}
	/**
	* Delete a file, or a folder and its contents
	*
	* @author Aidan Lister <aidan@php.net>
	* @version 1.0.2
	* @param string $dirname Directory to delete
	* @return bool Returns TRUE on success, FALSE on failure
	*/
	function rmdirr($dirname) {
		// Sanity check
		if (!file_exists($dirname)) {
			return false;
		}
	
		// Simple delete for a file
		if (is_file($dirname)) {
			return unlink($dirname);
		}
		
		// Loop through the folder
		$dir = dir($dirname);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			
			// Recurse
			$this->rmdirr("$dirname/$entry");
		}
		
		// Clean up
		$dir->close();
		return rmdir($dirname);
	}
	function cpy($source, $dest){
	    if(is_dir($source)) {
	        $dir_handle=opendir($source);
	        while($file=readdir($dir_handle)){
	            if($file!="." && $file!=".."){
	                if(is_dir($source."/".$file)){
	                	if (!file_exists($dest."/".$file)) mkdir($dest."/".$file);
	                    $this->cpy($source."/".$file, $dest."/".$file);
	                } else {
	                    copy($source."/".$file, $dest."/".$file);
	                }
	            }
	        }
	        closedir($dir_handle);
	    } else {
	        copy($source, $dest);
	    }
	}
}
class File {
	var $path;
	var $name;
	var $content;
	
	function __construct($base = null, $path = null, $name = null) {
		$this->path = $path;
		$this->name = $name;
	}
}
?>