<?php

use DataArray\KeyValue;
use DataArray\Key;
use DataArray\Value;
use ALTO\ALTOString;

class DataLine {
    var $RowIndex;
    var $ColumnIndex;
    
    var $VPOS;
    var $HPOS;
    var $HEIGHT;
    var $WIDTH;
    
    var $Classification;

    var $Strings = array();
    
    protected $stringColumns = array();
    
    protected $surroundingDataLines;
    
    function getAverageSpaceBetweenStrings() {
        $space_total = 0;
        
        foreach($this->Strings as $key => $Strings_item) {
            if ($key > 0) {
                $space_total += $Strings_item->HPOS - ($this->Strings[$key-1]->HPOS + $this->Strings[$key-1]->WIDTH);
            }
        }
        
        if (count($this->Strings) > 0) {
            return $space_total / count($this->Strings);
        }
        
        return false;
    }
    /*function splice(Array $hpos = null) {
     $spliced = array();
     
     if ($hpos) {
     $subIdx = 0;
     
     $dLine = new DataLine();
     $dLine->HEIGHT = $this->HEIGHT;
     $dLine->RowIndex= $this->RowIndex;
     $dLine->ColumnIndex = $this->ColumnIndex;
     
     foreach($this->Strings as $string_item) {
     if ($string_item->HPOS < $hpos[0]) {
     array_push($dLine->Strings, $string_item);
     }
     }
     if (count($dLine->Strings) > 0) {
     array_push($spliced, $dLine);
     }
     
     if (count($hpos) > 1) {
     for ($i=1; $i<count($hpos); $i++) {
     $dLine = new DataLine();
     $dLine->HEIGHT = $this->HEIGHT;
     $dLine->RowIndex = $this->RowIndex;
     $dLine->ColumnIndex = $this->ColumnIndex + $subIdx;
     
     foreach($this->Strings as $string_item) {
     if ($string_item->HPOS < $hpos[$i] && $string_item->HPOS >= $hpos[$i-1]) {
     array_push($dLine->Strings, $string_item);
     }
     }
     
     if (count($dLine->Strings) > 0) {
     array_push($spliced, $dLine);
     }
     $subIdx++;
     
     }
     }
     
     $dLine = new DataLine();
     $dLine->HEIGHT = $this->HEIGHT;
     $dLine->RowIndex= $this->RowIndex;
     $dLine->ColumnIndex = $this->ColumnIndex + $subIdx;
     
     foreach($this->Strings as $string_item) {
     if ($string_item->HPOS >= $hpos[count($hpos)-1]) {
     array_push($dLine->Strings, $string_item);
     }
     }
     if (count($dLine->Strings) > 0) {
     array_push($spliced, $dLine);
     }
     
     
     } else {
     $spliced = array($this);
     }
     
     
     return $spliced;
     }*/
    /*function integrateHeaderStringParts($tomerge) {
        $stringsByColumn = $this->getStringsByColumns();
        
        foreach($tomerge as $key => $this_column_item) {
            foreach($this_column_item as $string_item) {
                if ($string_item->CONTENT !== "") {
                    $stringsByColumn[$key+1][count($stringsByColumn[$key+1])-1]->CONTENT .= " " . $string_item->CONTENT;
                }
                
            }
        }
    }*/
    function mergeDataLine(DataLine $dataLine, $headerInfo = null) {
        $mergeResult = array();
        $mergeMissings = array();
        
        $stringsByColumn = $this->getStringsByColumns();
        
        foreach($stringsByColumn as $key => $this_column_item) {
            //echo " merge try for " . $this_column_item[0] . "\n";
            
            $allMerged = null;
            foreach($dataLine->Strings as $objKey => $tomerge_string_item) {
                if ($tomerge_string_item->HPOS >= ($this_column_item[0]->HPOS - 5)) {
                    if (isset($stringsByColumn[$key+1][0])) {
                        if (($tomerge_string_item->HPOS + $tomerge_string_item->WIDTH) < $stringsByColumn[$key+1][0]->HPOS) {
                            //echo "merge '" . $tomerge_string_item . "' into '" . $this_column_item[0] . "' \n";
                            $stringsByColumn[$key][count($stringsByColumn[$key]) - 1]->CONTENT .= ' ' . $tomerge_string_item->CONTENT;
                            
                            $mergeResult[$objKey] = $key;
                        }
                    } else {
                        $stringsByColumn[$key][count($stringsByColumn[$key]) - 1]->CONTENT .= ' ' . $tomerge_string_item->CONTENT;
                        
                        $mergeResult[$objKey] = $key;
                    }
                    
                    
                    //$this->addString($tomerge_string_item);
                }
            }
        }
        
        foreach($dataLine->Strings as $objKey => $tomerge_string_item) {
            if (!isset($mergeResult[$objKey])) {
                array_push($mergeMissings, $objKey);
            }
        }
        
        return $mergeMissings;
    }
    function getColumnContext($lines) {
        $col_hpos = $this->getContextColumnHPOS($lines);
        
        return $col_hpos;
    }
    function getContextColumnHPOS($lines) {
        $hpos = array();
        
        $line_columns = $this->getStringsByColumns();
        
        for($i=0; $i<count($line_columns); $i++) {
            if ($this->isContextColumn($line_columns[$i], $lines)) {
                array_push($hpos, $line_columns[$i][0]->HPOS);
            }
        }
        
        return $hpos;
    }
    function isContextColumn($column, $lines) {
        $sameHPOSCount = 0;
        
        foreach($lines as $line_item) {
            $line_columns = $line_item->getStringsByColumns();
            
            foreach($line_columns as $column_item) {
                if (abs($column[0]->HPOS - $column_item[0]->HPOS) < 10) {
                    $sameHPOSCount++;
                }
            }
            
        }
        
        if ($sameHPOSCount > 5) {
            return true;
        }
        
        return false;
    }
    function addString($string, $wrappedText = false) {
        array_push($this->Strings, $string);
        
        usort($this->Strings, array($this, "posStringCompare"));
        
        $this->Strings = array_values($this->Strings);
    }
    function getAverageDevitationFromAverageSpaces() {
        $avgSpaceOfStrings = $this->getAverageSpaceBetweenStrings();
        
        if(!$avgSpaceOfStrings) return false;
        
        $deviation_total = 0;
        
        foreach($this->Strings as $key => $Strings_item) {
            if ($key > 0) {
                $deviation_total += abs($avgSpaceOfStrings - ($Strings_item->HPOS - ($this->Strings[$key-1]->HPOS + $this->Strings[$key-1]->WIDTH)));
            }
        }
        
        return $deviation_total / count($this->Strings);
    }
    function getSurroundingDataLines() {
        $grid = DataGrid::getInstance();
        
        foreach($grid->DataGridRows as $rowIdx => $row_item) {
            foreach($row_item->DataGridColumns as $colIdx => $column_item) {
                
                if ($this->RowIndex === $rowIdx && $this->ColumnIndex === $colIdx) {
                    return $column_item->DataLines;
                }
            }
        }
    }
    function hasReferencialPart() {
        $stringsByColumns = $this->getConcatenatedStringByColumns();
        
        if (count($stringsByColumns) === 2) {
            if ($stripos = stripos($stringsByColumns[1], "vgl")) {
                return true;
            }
            if ($stripos = stripos($stringsByColumns[1], "(")) {
                return true;
            }
            if ($stripos = stripos($stringsByColumns[1], ";")) {
                return true;
            }
            if ($stripos = stripos($stringsByColumns[1], ",")) {
                return true;
            }
        }
        
        return false;
    }
    function getKeyValuesFromDelimitedStrings() {
        $keyvalues = array();
        
        $stringsByColumns_Concatenated = $this->getConcatenatedStringByColumns();
        
         
        if (stripos($stringsByColumns_Concatenated[0], " / ")) {
            $key_exp = explode(" / ", $stringsByColumns_Concatenated[0]);
            $value_exp = explode(" / ", $stringsByColumns_Concatenated[1]);
            
            if (count($key_exp) === count($value_exp)) {
                foreach($key_exp as $key => $item) {
                    $kv = new KeyValue();
                    
                    $key_string = new ALTOString();
                    $key_string->CONTENT = $key_exp[$key];
                    
                    $kv->Key = new Key();
                    $kv->Key->Strings = array($key_string);
                    
                    $value_string = new ALTOString();
                    $value_string->CONTENT = $value_exp[$key];
                    
                    $kv->Value = new Value();
                    $kv->Value->Strings = array($value_string);
                    
                    array_push($keyvalues, $kv);
                }
                
                
            }
        } else if (stripos($stringsByColumns_Concatenated[0], ": ") && count($stringsByColumns_Concatenated) === 1) {
            $keyvalue_exp = explode(": ", $stringsByColumns_Concatenated[0]);
            
            $kv = new KeyValue();
            $key_string = new ALTOString();
            $key_string->CONTENT = $keyvalue_exp[0];
            
            $kv->Key = new Key();
            $kv->Key->Strings = array($key_string);
         
            $value_string = new ALTOString();
            $value_string->CONTENT = $keyvalue_exp[1];
            
            $kv->Value = new Value();
            $kv->Value->Strings = array($value_string);
            
            array_push($keyvalues, $kv);
        }
        
        return $keyvalues;
    }
    function isMultiLineFreetext($string) {
        $preceedingTextline = $this->getPreceedingTextLine($string);
        $followingTextline = $this->getFollowingTextLine($string);
        
        
        if (abs($string->TextLine->HEIGHT - $preceedingTextline->HEIGHT) < 5 && ($string->TextLine->VPOS - ($preceedingTextline->VPOS + $preceedingTextline->HEIGHT)) < 10) {
            return true;
        } else {
            if (abs($followingTextline->HEIGHT - $string->TextLine->HEIGHT) < 5 && ($followingTextline->VPOS - ($string->TextLine->VPOS + $string->TextLine->VPOS)) < 10 && $this->getNumberOfColumns($this->getStringsOnLine($followingTextline->Strings[0])) === 1) {
                return true;
            } else {
                if (count($this->getStringsOnLine($string)) === 1 & abs($preceedingTextline->HPOS - $string->TextLine->HPOS) < 5  && ($string->TextLine->VPOS - ($preceedingTextline->VPOS + $preceedingTextline->HEIGHT)) < 20) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
    function __toString() {
        $string = "";
        
        $string .= "[" . sprintf('%02d', $this->RowIndex) . "." . sprintf('%01d', $this->ColumnIndex) . "]";
        $string .= "[POS: " . sprintf('%04d', $this->VPOS) . "/". sprintf('%04d', $this->HPOS). "; H: " . str_pad($this->HEIGHT, 4, " ", STR_PAD_RIGHT) . "; W: " . str_pad($this->WIDTH, 4, " ", STR_PAD_RIGHT) . "]";
        if ($this->Classification) {
            $string .= " [FT: " . $this->Classification->isPartOfFreeText . "; SA: " . $this->Classification->isStandAlone . "; H: " . $this->Classification->isHeader . "; KV: " . $this->Classification->isPartOfKeyValueList . "; T: " . $this->Classification->isPartOfTable . "; MG: " . $this->Classification->isMergeCandidate . "; DEL: " . $this->Classification->hasDelimitedStrings. "]";
            
            $string .= " [" . str_pad($this->Classification->name, 10, " ", STR_PAD_RIGHT) . "]";
        
            if ($this->Classification->Classification) {
                $string .= "[" . str_pad($this->Classification->Classification->name, 10, " ", STR_PAD_RIGHT) . "]";
                
                if ($this->Classification->Classification->Classification) {
                    $string .= "[" . str_pad($this->Classification->Classification->Classification->name, 10, " ", STR_PAD_RIGHT) . "]";
                } else {
                    $string .= "[" . str_pad("", 10, " ", STR_PAD_RIGHT) . "]";
                }
            } else {
                $string .= "[" . str_pad("", 10, " ", STR_PAD_RIGHT) . "]";
                $string .= "[" . str_pad("", 10, " ", STR_PAD_RIGHT) . "]";
            }
            
            if ($this->Classification->tableDef) {
                $string .= " [tabledef: " . $this->Classification->tableDef . "]";
            }
        }
        
        
        $stringsByColumn = $this->getConcatenatedStringByColumns();
            
        $string .= " " . join(" | ", $stringsByColumn);
        
        return $string;
    }
    function countColumnsWithContent() {
        $columns= $this->getStringsByColumns();
        
        $count = 0;
        
        foreach($columns as $column_item) {
            if ($column_item) {
                foreach($column_item as $string_item) {
                    
                    if ($string_item->CONTENT !== "") {
                        $count++;
                        
                        break;
                    }
                }
            }
            
        }
        
        return $count;
    }
    function getNumberOfColumns() {
        $avgSpaceOfStrings = $this->getAverageSpaceBetweenStrings();
        $avgDeviationFromAvgSpaces = $this->getAverageDevitationFromAverageSpaces();
        
        $spaceToCheck = $this->getSpaceCheckTreshold();
        
        if ($avgDeviationFromAvgSpaces < 5) return 1;
        
        $noCols = 1;
        
        foreach($this->Strings as $key => $String_item) {
            if ($key > 0) {
                $space = $String_item->HPOS - ($this->Strings[$key-1]->HPOS + $this->Strings[$key-1]->WIDTH);
                
                if ($space > $spaceToCheck) {
                    $noCols++;
                }
            }
        }
        
        
        
        if ($noCols == 1) {
            if (count($this->Strings) === 2) {
                $space = $this->Strings[1]->HPOS - ($this->Strings[0]->HPOS + $this->Strings[0]->WIDTH);
                
                //TODO assumption
                if ($this->Strings[0]->WIDTH < $space) {
                    $noCols++;
                }
            }
        }
        return $noCols;
    }
    function mergeStrings($strings) {
        $merged = array();
        
        $cleanup_scope = array();
        
        $merged_pending = false;
        
        for($i=0; $i<count($strings); $i++) {
            if ($i > 0) {
                if ($strings[$i]->HPOS - ($strings[$i-1]->HPOS + $strings[$i-1]->WIDTH) > 5) {
                    if ($merged_pending) {
                        array_push($merged, $candidate);
                        $merged_pending = false;
                    }
                    
                    $candidate = $strings[$i];
                    
                    array_push($merged, $candidate);
                } else {
                    if (isset($candidate)) {
                        if ($strings[$i]->CONTENT && $candidate->CONTENT) {
                            $candidate->CONTENT = $candidate->CONTENT . $strings[$i]->CONTENT;
                            $candidate->WIDTH = $candidate->WIDTH + $strings[$i]->WIDTH;
                        }
                    }
                }
            } else {
                array_push($merged, $strings[$i]);
            }
        }
        
        return $merged;
    }
    function getSpaceCheckTreshold() {
        $avgSpaceOfStrings = $this->getAverageSpaceBetweenStrings();
        
        $avgSpaceDeviationOfStrings = $this->getAverageDevitationFromAverageSpaces();
        
        if ($avgSpaceDeviationOfStrings < 5) {
            return $avgSpaceOfStrings * 3;
        } else {
            if ($this->HEIGHT < 35) {
                return 22;
            } else if ($this->HEIGHT < 40) {
                return 25;
            } else if ($this->HEIGHT < 50) {
                return 28;
            } else if ($this->HEIGHT < 60) {
                return 31;
            } else {
                return 34;
            }
        }
    }
    function getStringsWidth($strings) {
        $width = 0;
        
        $width = $width + ($strings[count($strings)-1]->HPOS + $strings[count($strings)-1]->WIDTH) - $strings[0]->HPOS;
        
        return $width;
    }
    function setStringColumns() {
        $grid = DataGrid::getInstance();
        
        $avgSpaceOfStrings = $this->getAverageSpaceBetweenStrings();
        
        $spaceToCheck = $this->getSpaceCheckTreshold();
        
        
        $noCols = 1;
        
        $columns[0] = array();
        
        if (count($this->Strings) === 2) {
            $space = $this->Strings[1]->HPOS - ($this->Strings[0]->HPOS + $this->Strings[0]->WIDTH);
            
            //TODO assumption
            if ($this->Strings[0]->WIDTH < $space) {
                array_push($columns[0], $this->Strings[0]);
                $columns[1] = array();
                array_push($columns[1], $this->Strings[1]);
            } else {
                array_push($columns[0], $this->Strings[0]);
                array_push($columns[0], $this->Strings[1]);
            }
        } else {
            $avgDeviationFromAvgSpaces = $this->getAverageDevitationFromAverageSpaces();
            
            //echo $this . "; " . $avgSpaceOfStrings . "; " . $spaceToCheck . "; " . $avgDeviationFromAvgSpaces . "\n";
            
            foreach($this->Strings as $key => $string_item) {
                if ($key > 0) {
                    $space = $string_item->HPOS - ($this->Strings[$key-1]->HPOS + $this->Strings[$key-1]->WIDTH);
                    
                    if ($space > $spaceToCheck) {
                        $noCols++;
                        if (!isset($columns[$noCols-1])) {
                            $columns[$noCols-1] = array();
                        } else {
                            if (!is_array($columns[$noCols-1])) {
                                $columns[$noCols-1] = array();
                            }
                        }
                        
                        array_push($columns[$noCols-1], $string_item);
                    } else {
                        if (!is_array($columns[$noCols-1])) {
                            $columns[$noCols-1] = array();
                        }
                        array_push($columns[$noCols-1], $string_item);
                    }
                } else {
                    array_push($columns[$noCols-1], $string_item);
                }
            }
        }
        
//TODO following part is not re-producable;  
        /*if (count($columns) === 1 && !$this->isLeftAligned()) {
            echo $this . "; " . "; " . $spaceToCheck . "\n";
            
            $columns_imp = array();
            
            if (count($grid->denseStartHPOSNonColumnBased) === 1) {
                $firstString = $this->Strings[0];
                $lastString = $this->Strings[count($this->Strings)-1];
                if (($firstString->HPOS + $firstString->WIDTH <= $grid->denseStartHPOSNonColumnBased[0] && $lastString->HPOS >= $grid->denseStartHPOSNonColumnBased[0]) ) {
                    $columns_imp[0] = array();
                    $columns_imp[1] = array();
                    
                    foreach($this->Strings as $key => $string_item) {
                        if (($string_item->HPOS + $string_item->WIDTH < $grid->denseStartHPOSNonColumnBased[0]) ) {
                            array_push($columns_imp[0], $string_item);
                        } else {
                            array_push($columns_imp[1], $string_item);
                        }
                    }
                    
                    if (count($columns_imp) == 2) {
                        if (count($columns_imp[0]) > 0 && count($columns_imp[1]) > 0) {
                            $columns = $columns_imp;
                        }
                        
                    }
                }
            }
        }*/
        
        $this->stringColumns = $columns;
    }
    function getStringsByColumns() {
        return $this->stringColumns;
    }
    function getConcatenatedStringByColumns() {
        $stringsByColumn = $this->getStringsByColumns();
        
        $columns = array();
        
        foreach($stringsByColumn as $column_item) {
            $string = "";
            
            foreach($column_item as $key => $string_item) {
                if ($key === 0) {
                    $string .= $string_item->CONTENT;
                } else {
                    $string .= " " . $string_item->CONTENT;
                }
            }
            
            array_push($columns, $string);
        }
        
        return $columns;
    }
    function posStringCompare($a, $b) {
        if (abs($a->TextLine->VPOS - $b->TextLine->VPOS) < 5) {
            return $a->HPOS - $b->HPOS;
        } else {
            return $a->TextLine->VPOS - $b->TextLine->VPOS;
        }
    }
    function getConcatenatedString() {
        $stringsByColumns= $this->getConcatenatedStringByColumns();
        
        $string = "";
        foreach($stringsByColumns as $column_item) {
            $string .= " " . $column_item;
        }
        
        return $string;
    }
    function isLeftAligned() {
        $grid = DataGrid::getInstance();
        
        if (abs($this->Strings[0]->HPOS - $grid->pageLeft) < 100) return true;
        
        
        return false;
    }
    function isRightAligned() {
        $grid = DataGrid::getInstance();
        
        if (abs(($this->Strings[count($this->Strings) - 1]->HPOS + $this->Strings[count($this->Strings) - 1]->WIDTH) - $grid->pageRight) < 100) return true;
        
        return false;
    }
}
?>