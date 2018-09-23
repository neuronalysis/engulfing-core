<?php
namespace DataArray;

use ALTO\ALTOString;

class DataArray {
    var $KeyValues = array();
    var $Tables = array();
    var $FreeTexts = array();
    
    function toJSON() {
        $obj = new \stdClass();
        
        $obj->KeyValues = array();
        $obj->Tables = array();
        $obj->FreeTexts = array();
        
        $tbls = 1;
        
        foreach($this->KeyValues as $keyvalue_item) {
            $keystring = $keyvalue_item->getKeyString();
            $valuestring = $keyvalue_item->getValueString();
            
            if ($keyvalue_item->Value instanceof Table) {
                if ($keystring->CONTENT === "") {
                    $obj->KeyValues["TABLE_" . $tbls] = $keyvalue_item->Value->toJSON();
                    
                    $tbls++;
                } else {
                    $obj->KeyValues[$keystring->CONTENT] = $keyvalue_item->Value->toJSON();
                }
                
            } else {
                $obj->KeyValues[$keystring->CONTENT] = $valuestring->CONTENT;
            }
        }
        
        foreach($this->FreeTexts as $idx => $freetext_item) {
            $freetext_string = $freetext_item->getString();
            
            $obj->FreeTexts[$idx] = $freetext_string->CONTENT;
        }
        
        
        return $obj;
    }
    function addKeyValue(KeyValue $kv) {
        if (!in_array($kv, $this->KeyValues)) {
            array_push($this->KeyValues, $kv);
        }
    }
    function addFreeText(FreeText $freetext) {
        if (!in_array($freetext, $this->FreeTexts)) {
            array_push($this->FreeTexts, $freetext);
        }
    }
    function mergeKeyValues(array $keyvalues) {
        $this->KeyValues = array_merge($this->KeyValues, $keyvalues);
    }
    function getKeyValues() {
        return $this->KeyValues;
    }
    function mergeFreeTexts(array $freetexts) {
        $this->FreeTexts = array_merge($this->FreeTexts, $freetexts);
    }
    function getFreeTexts() {
        return $this->FreeTexts;
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
        $string = new ALTOString();
        
        if (isset($this->Key->Strings)) {
            if(is_array($this->Key->Strings)) {
                foreach($this->Key->Strings as $key => $string_item) {
                    if ($key > 0) {
                        $string->CONTENT .= " " . $string_item->CONTENT;
                    } else {
                        $string->CONTENT .= $string_item->CONTENT;
                    }
                }
                
                
            }
        } else {
            $string->CONTENT = "";
        }
        
        return $string;
    }
    function getValueString() {
        $string = new ALTOString();
        if (isset($this->Value->Strings)) {
            if(is_array($this->Value->Strings)) {
                foreach($this->Value->Strings as $key => $string_item) {
                    if ($key > 0) {
                        $string->CONTENT .= " " . $string_item->CONTENT;
                    } else {
                        $string->CONTENT .= $string_item->CONTENT;
                    }
                }
                
            }
        } else {
            $string->CONTENT = "";
            
        }
        return $string;
        
    }
}
class Key {
	var $Strings;
}
class Value {
    var $Strings;
}
class FreeText {
    var $Strings;
    
    function getString() {
        $string = new ALTOString();
        
        if (isset($this->Strings)) {
            if(is_array($this->Strings)) {
                foreach($this->Strings as $key => $string_item) {
                    if ($key > 0) {
                        $string->CONTENT .= " " . $string_item->CONTENT;
                    } else {
                        $string->CONTENT .= $string_item->CONTENT;
                    }
                }
                
                
            }
        } else {
            $string->CONTENT = "";
        }
        
        return $string;
    }
}
class Table {
    var $TableDataRows = array();
    
    function toJSON() {
        $obj = array();
        
        foreach($this->TableDataRows as $tabledatarow_item) {
            $row = array();
            
            foreach($tabledatarow_item->TableDataCells as $tabledatacell_item) {
                $keystring = $tabledatacell_item->getKeyString();
                $valuestring = $tabledatacell_item->getValueString();
                
                
                $row[$keystring->CONTENT] = $valuestring->CONTENT;
            }
            
            array_push($obj, $row);
        }
        
        
        return $obj;
    }
}
class TableDataRow {
    var $TableDataCells = array();
}
class TableDataCell {
    var $Key;
    var $Value;
    
    function getKeyString() {
        $string = new ALTOString();
        
        $string->CONTENT = $this->Key;
        
        return $string;
    }
    function getValueString() {
        $string = new ALTOString();
        
        $string->CONTENT = $this->Value;
        
        return $string;
        
    }
}
?>