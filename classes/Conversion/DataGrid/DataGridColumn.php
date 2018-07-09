<?php
class DataGridColumn {
    var $HPOS;
    var $VPOS;
    var $HEIGHT;
    var $WIDTH;
    
    var $index;
    
    var $DataLines = array();
    
    var $denseStartHPOS;
    var $denseStartHPOSNonColumnBased;
    
    function addLine(DataLine $dataLine) {
        array_push($this->DataLines, $dataLine);
    }
    function containsStrings() {
        if (count($this->DataLines) > 0) {
            foreach($this->DataLines as $dl) {
                if (!$dl->Strings) {
                    return false;
                }
                
                if (count($dl->Strings) === 0) {
                    return false;
                }
            }
        } else {
            return false;
        }
        
        return true;
    }
}
?>