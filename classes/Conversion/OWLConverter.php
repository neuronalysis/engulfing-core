<?php
class OWLConverter extends XMLConverter {
	
    function convertToObjectTree($dom) {
        $children = $dom->childNodes;
        $tree = $this->convertNodesToObjects($children[0]);
        
        return $tree;
    }
}
?>
