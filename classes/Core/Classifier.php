<?php
class Classifier {
	var $Context;
	var $Classification;
	
	use ObjectHelper;
}
class Classification {
    var $name;
    
    var $Classification;
    
    function __toString() {
        $str = "";
        
        $str = $name;
        
        return $str;
    }
}
?>