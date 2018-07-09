<?php
class DataGridRow {
    var $HPOS;
    var $VPOS;
    var $WIDTH;
    var $HEIGHT;
    
    var $index;
    
    var $DataGridColumns = array();
    
    function addColumn(DataGridColumn $col) {
        $col->index = count($this->DataGridColumns);
        
        array_push($this->DataGridColumns, $col);
    }
    
}
?>