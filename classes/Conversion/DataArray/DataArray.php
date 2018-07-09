<?php
namespace DataArray;

use ALTO\ALTOString;

class DataArray {
    var $KeyValues = array();
    var $Tables = array();
    
    function addKeyValue(KeyValue $kv) {
        if (!in_array($kv, $this->KeyValues)) {
            array_push($this->KeyValues, $kv);
        }
    }
    function mergeKeyValues(array $keyvalues) {
        $this->KeyValues = array_merge($this->KeyValues, $keyvalues);
    }
    function getKeyValues() {
        return $this->KeyValues;
    }
    function addTable(Table $table) {
        if (!in_array($table, $this->Tables)) {
            array_push($this->Tables, $table);
        }
    }
    function mergeTables(array $tables) {
        $this->Tables= array_merge($this->Tables, $tables);
    }
    function getTables() {
        return $this->Tables;
    }
}
class DataArrayString {
    
    var $CONTENT;
    var $HEIGHT;
    var $WIDTH;
    var $VPOS;
    var $HPOS;
    
    var $SUBS_TYPE;
    var $SUBS_CONTENT;
    
    var $Word;
}
class KeyValue {
    var $Key;
    var $Value;
    
    function getKeyString() {
        if(is_array($this->Key->Strings)) {
            $string = new ALTOString();
            
            foreach($this->Key->Strings as $key => $string_item) {
                if ($key > 0) {
                    $string->CONTENT .= " " . $string_item->CONTENT;
                } else {
                    $string->CONTENT .= $string_item->CONTENT;
                }
            }
            
            return $string;
        }
    }
    function getValueString() {
        if(is_array($this->Value->Strings)) {
            $string = new ALTOString();
            
            foreach($this->Value->Strings as $key => $string_item) {
                if ($key > 0) {
                    $string->CONTENT .= " " . $string_item->CONTENT;
                } else {
                    $string->CONTENT .= $string_item->CONTENT;
                }
            }
            
            return $string;
        }
    }
}
class Key {
	var $Strings;
}
class Value {
    var $Strings;
}
class Table {
    var $TableDataRows = array();
}
class TableDataRow {
    var $TableDataCells = array();
}
class TableDataCell {
    var $Key;
    var $Value;
}
?>