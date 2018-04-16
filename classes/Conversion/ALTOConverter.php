<?php
use ALTO\ALTOString;

class ALTOConverter extends Converter {
    
    var $ALTO;
    function __construct($alto) {
        $this->ALTO = $alto;
        
    }
    function plottStringsByColumns($stringsByColumn) {
        $string = "";
        
        if ($stringsByColumn) {
            foreach ($stringsByColumn as $key => $column_item) {
                if ($key > 0) {
                    $string .= " | ";
                }
                foreach ($column_item as $key => $string_item) {
                    $string .= " " . $string_item->CONTENT;
                }
                
                
            }
        }
        
        
        return $string;
    }
    function bringLinesToGrid($lines) {
        $grid = new DataGrid();
        
        $grid->addLines($lines);
        
        return $grid;
    }
    function bringStringsToLines($pageNumber) {
        $lines = array();
        
        $strings = $this->ALTO->Layout->Pages[$pageNumber]->getStrings();
        
        $lineCnt = 0;
        $currentLine = new DataLine();
        $currentLine->RowIndex = $lineCnt;
        $currentLine->ColumnIndex = 0;
        $currentLine->VPOS = $strings[0]->TextLine->VPOS;
        $currentLine->HEIGHT = $strings[0]->TextLine->HEIGHT;
        $currentLine->Strings = array();
        
        foreach($strings as $string_item) {
            if (abs($string_item->TextLine->VPOS - $currentLine->VPOS) < 8) {
                array_push($currentLine->Strings, $string_item);
            } else {
                array_push($lines, $currentLine);
                $lineCnt++;
                
                $currentLine = new DataLine();
                $currentLine->RowIndex = $lineCnt;
                $currentLine->ColumnIndex = 0;
                $currentLine->VPOS= $string_item->TextLine->VPOS;
                $currentLine->HEIGHT= $string_item->TextLine->HEIGHT;
                $currentLine->Strings= array($string_item);
            }
        }
        
        
        return $lines;
    }
    function countColumnsWithContent($columns) {
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
    function trimWhitespaces($lines) {
        foreach($lines as $line_item) {
            $line_item->Strings = $line_item->mergeStrings($line_item->Strings);
        }
        
        return $lines;
    }
    function getContextColumnHPOS(DataLine $dataLine, $lines) {
        $hpos = array();
        
        $line_columns = $dataLine->getStringsByColumns();
        
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
    function classifyLines($lines) {
        foreach($lines as $line_item) {
            $line_item->isStandAlone = $line_item->shouldBeStandAlone();
            $line_item->hasDelimitedStrings = $line_item->hasDelimitedStrings();
            $line_item->isPartOfKeyValueList = $line_item->shouldBePartOfKeyValueList($lines);
            $line_item->isPartOfFreeText = $line_item->shouldBePartOfFreeText($lines);
            $line_item->isHeader = $line_item->shouldBeHeader();
            
            
            //is standalone
            //part of table
            //part of key value list
            //start of table
            //end of table
            //part of key value list
            //start of key value list
            //end of key value list
            //part of freetext
            //start of key value list
            //end of key value list
            //part of topic group
        }
        
        foreach($lines as $line_item) {
            
            if ($line_item->isStandAlone) {
                $line_item->classification = "standAllone";
            } else if ($line_item->isHeader && !$line_item->isPartOfFreeText) {
                $line_item->classification = "header";
            } else if ($line_item->isPartOfFreeText && !$line_item->isStandAlone) {
                $line_item->classification = "partOfFreeText";
            } else if ($line_item->isPartOfKeyValueList) {
                $line_item->classification = "partOfKeyValueList";
            }
        }
        
        $lines = $this->classifyTables($lines);
        
        foreach($lines as $line_item) {
            if ($line_item->isPartOfTable) {
                if ($line_item->isPartOfKeyValueList) {
                    $line_item->subClassification = "partOfTable";
                } else {
                    $line_item->classification = "partOfTable";
                }
                
            }
        }
        
        $lines = array_values($lines);
        
        return $lines;
    }
    function classifyTables($lines) {
        $partOfKeyValueList= false;
        $inTable = false;
        $inHeader = false;
        $inData = false;
        $inFooter = false;
        $amtColumns = null;
        $headerInfo = null;
        $headerIdx = null;
        
        for($i=0; $i<count($lines)-1; $i++) {
            if (!$inTable) {
                $stringsByColumns = $lines[$i]->getStringsByColumns();
                
                if ($lines[$i]->classification === "partOfKeyValueList") {
                    $partOfKeyValueList = true;
                }
                
                if ($partOfKeyValueList) {
                    if (count($stringsByColumns) > 3) {
                        $amtColumns = count($stringsByColumns)-1;
                        
                        $headerInfo = array_slice($stringsByColumns, 1);
                        $headerIdx = $i;
                        
                        $lines[$i]->tableDef = "header";
                        
                        $inHeader = true;
                        $inTable = true;
                    }
                }
            } else {
                
                $stringsByColumns = $lines[$i]->getStringsByColumns($headerInfo);
                $countColumnsWithContent = $this->countColumnsWithContent($stringsByColumns);
                
                if ($inHeader) {
                    if ($countColumnsWithContent === $amtColumns) {
                        $lines[$i]->tableDef = "datarow";
                        
                        $inHeader = false;
                    } else {
                        $lines[$headerIdx]->integrateHeaderStringParts($stringsByColumns);
                        
                        unset($lines[$i]);
                    }
                } else {
                    if ($countColumnsWithContent !== $amtColumns) {
                        $lines[$i-1]->tableDef = "lastdatarow";
                        $inTable = false;
                    } else {
                        $lines[$i]->tableDef = "datarow";
                    }
                }
            }
            
            
            if (isset($lines[$i])) {
                if ($inTable) {
                    $lines[$i]->isPartOfTable = 1;
                } else {
                    $lines[$i]->isPartOfTable = 0;
                }
            }
            
            
        }
        
        
        $lines = array_values($lines);
        
        return $lines;
    }
    function convertPageToArray($pageNumber) {
        $array = array();
        
        $lines = $this->bringStringsToLines($pageNumber);
        $lines = $this->trimWhitespaces($lines);
        
        $grid = $this->bringLinesToGrid($lines);
        
        //echo $grid->plott() . "\n";
        
        foreach($grid->DataGridRows as $rowIdx => $row_item) {
            foreach($row_item->DataGridColumns as $colIdx => $column_item) {
                $cell_lines = $column_item->DataLines;
                
                if ($column_item->containsStrings()) {
                    $lines_classified = $this->classifyLines($cell_lines);
                    //$lines_classified_priorized = $this->priorizeClassification($lines);
                    
                    foreach($lines_classified as $key => $line_item) {
                        //echo $line_item->plott() . "\n";
                        //echo "[" . $rowIdx . " : " . $colIdx. "] - " . $line_item->plott() . "\n";
                        
                        $stringsByColumns = $line_item->getConcatenatedStringByColumns();
                        
                        
                        if ($line_item->classification === "partOfKeyValueList") {
                            if ($line_item->subClassification === "partOfTable") {
                                if ($line_item->tableDef === "header") {
                                    $table = array();
                                    
                                    $headerInfo = $stringsByColumns;
                                } else if ($line_item->tableDef === "datarow") {
                                    $row = array();
                                    foreach($stringsByColumns as $key => $col_item) {
                                        $row[$headerInfo[$key+1]] = $col_item;
                                    }
                                    
                                    array_push($table, $row);
                                } else if ($line_item->tableDef === "lastdatarow") {
                                    $row = array();
                                    foreach($stringsByColumns as $key => $col_item) {
                                        $row[$headerInfo[$key+1]] = $col_item;
                                    }
                                    
                                    array_push($table, $row);
                                    
                                    $array[$headerInfo[0]] = $table;
                                }
                                
                                
                            } else {
                                if ($line_item->hasDelimitedStrings) {
                                    foreach($line_item->getKeyValuesFromDelimitedStrings() as $key => $value) {
                                        $array[$key] = $value;
                                    }
                                } else {
                                    $array[$stringsByColumns[0]] = $stringsByColumns[1];
                                }
                            }
                            
                            
                        } else if ($line_item->classification === "partOfFreeText") {
                            
                            if ($lines_classified[$key-1]->classification !== "partOfFreeText") {
                                $freetext = "";
                                for($i = $key; $i < (count($lines_classified) -1); $i++) {
                                    $ft_stringsByColumns = $lines_classified[$i]->getConcatenatedStringByColumns();
                                    
                                    if ($i>$key) {
                                        $freetext .= " " . $ft_stringsByColumns[0];
                                    } else {
                                        $freetext .= $ft_stringsByColumns[0];
                                    }
                                    
                                    if($lines_classified[$i+1]->classification !== "partOfFreeText") {
                                        array_push($array, $freetext);
                                        break 1;
                                    }
                                }
                            }
                            
                        } else if ($line_item->classification === "standAllone") {
                            if ($line_item->hasDelimitedStrings) {
                                foreach($line_item->getKeyValuesFromDelimitedStrings() as $key => $value) {
                                    $array[$key] = $value;
                                }
                            } else {
                                if (isset($stringsByColumns[1])) {
                                    $array[$stringsByColumns[0]] = $stringsByColumns[1];
                                }
                            }
                        } else if ($line_item->classification === "header") {
                            if ($line_item->hasDelimitedStrings) {
                                foreach($line_item->getKeyValuesFromDelimitedStrings() as $key => $value) {
                                    $array[$key] = $value;
                                }
                            } else {
                                if (isset($stringsByColumns[1])) {
                                    $array[$stringsByColumns[0]] = $stringsByColumns[1];
                                }
                            }
                        }
                        
                    }
                }
                
                
            }
            
        }
        
        
        return $array;
    }
    function convertToArray() {
        $array = array();
        
        foreach($this->ALTO->Layout->Pages as $key => $page_item) {
            $pageArray = $this->convertPageToArray($key);
            
            $array = array_merge($array, $pageArray);
        }
        
        
        return $array;
    }
    function clusterStrings($classified) {
        $clustered = array();
        
        for($i=0; $i<count($classified); $i++) {
            if ($classified[$i]['classification'] === 1) {
                if ($classified[$i]['amountOfStrings'] === 1) {
                    array_push($clustered, $classified[$i]['string']);
                } else {
                    array_push($clustered, $classified[$i]['strings']);
                    
                    $i = $i + $classified[$i]['amountOfStrings'] - 1;
                }
            } else if ($classified[$i]['classification'] === 2) {
                array_push($clustered, $classified[$i]['columns']);
                
                $i = $i + $classified[$i]['amountOfStrings'] - 1;
            } else if ($classified[$i]['classification'] === 3) {
                $j_count = 0;
                $j_clustered = array();
                for($j=$i; $j<$i+100; $j++) {
                    if ($classified[$j]['classification'] === 3) {
                        array_push($j_clustered, $classified[$j]['strings']);
                        $j_count = $j_count + $classified[$j]['amountOfStrings'];
                        
                        $j = $j + $classified[$j]['amountOfStrings'] - 1;
                    } else {
                        break;
                    }
                }
                array_push($clustered, $j_clustered);
                
                $i = $i + $j_count - 1;
            } else {
                
            }
        }
        
        
        
        return $clustered;
    }
    function isInFieldContext($string, $stringsOfLine) {
        $printspace = $this->ALTO->getPrintSpace(0);
        
        $columns = $this->getStringsByColumns($stringsOfLine);
        
        if (abs($printspace->HPOS - $columns[0][0]->HPOS) < 20) {
            if (count($columns) === 2) {
                return true;
            }
        } else {
            $preceedingTextline = $this->getPreceedingTextLine($string);
            if ($preceedingTextline) {
                $preceedingColumns = $this->getStringsByColumns($preceedingTextline->Strings);
                
                if (abs($printspace->HPOS - $preceedingColumns[0][0]->HPOS) < 20) {
                    if (count($columns) === 2) {
                        return true;
                    }
                }
            }
            
        }
        
        
        return false;
    }
    function countStringsOnLine($string) {
        $count = 0;
        
        $textlines = $this->ALTO->Layout->Pages[0]->getTextLines();
        
        foreach($textlines as $textline_item) {
            if (abs($textline_item->VPOS - $string->TextLine->VPOS) < 5) {
                $count += count($textline_item->Strings);
            }
        }
        
        return $count;
    }
    
}
class DataLine {
    var $RowIndex;
    var $ColumnIndex;
    
    var $VPOS;
    var $HEIGHT;
    
    var $classification;
    var $subClassification;
    
    var $isStandAlone;
    var $isPartOfKeyValueList;
    var $isPartOfFreeText;
    var $isHeader;
    var $hasDelimitedStrings;
    var $isPartOfTable;
    var $tableDef;
    
    var $Strings = array();
    
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
    function integrateHeaderStringParts($tomerge) {
        $stringsByColumn = $this->getStringsByColumns();
        
        foreach($tomerge as $key => $this_column_item) {
            foreach($this_column_item as $string_item) {
                if ($string_item->CONTENT !== "") {
                    $stringsByColumn[$key+1][count($stringsByColumn[$key+1])-1]->CONTENT .= " " . $string_item->CONTENT;
                }
                
            }
        }
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
    function setSurroundingDataLines($lines) {
        $this->surroundingDataLines = $lines;
    }
    function getSurroundingDataLines() {
        return $this->surroundingDataLines;
    }
    function hasDelimitedStrings() {
        $stringsByColumns = $this->getConcatenatedStringByColumns();
        
        
        if (stripos($stringsByColumns[0], " / ")) {
            return true;
        }
        
        if (stripos($stringsByColumns[0], ": ")) {
            return true;
        }
        
        return false;
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
        
        $stringsByColumns = $this->getConcatenatedStringByColumns();
        
        if (stripos($stringsByColumns[0], " / ")) {
            $key_exp = explode(" / ", $stringsByColumns[0]);
            $value_exp = explode(" / ", $stringsByColumns[1]);
            
            if (count($key_exp) === count($value_exp)) {
                foreach($key_exp as $key => $item) {
                    $keyvalues[$item] = $value_exp[$key];
                }
            }
        }
        
        foreach($stringsByColumns as $col_item) {
            if (stripos($col_item, ": ")) {
                $keyvalue_exp = explode(": ", $col_item);
                $keyvalues[$keyvalue_exp[0]] = $keyvalue_exp[1];
            }
        }
        
        
        return $keyvalues;
    }
    function isMultiLineFreetext($string) {
        $preceedingTextline = $this->getPreceedingTextLine($string);
        $followingTextline = $this->getFollowingTextLine($string);
        
        
        if (abs($string->TextLine->HEIGHT - $preceedingTextline->HEIGHT) < 5 && ($string->TextLine->VPOS - ($preceedingTextline->VPOS + $preceedingTextline->HEIGHT)) < 10) {
            //echo $this->mergeStringContents($this->getStringsOnLine($string)) . " - " . $string->TextLine->HEIGHT . "; " . $preceedingTextline->HEIGHT . "\n";
            //echo "same height \n";
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
    function plott() {
        $string = "";
        
        $string .= "[" . $this->RowIndex . "." . $this->ColumnIndex . "]";
        $string .= " [VPOS: " . $this->VPOS . "]";
        if ($this->classification) {
            $string .= " [class: " . $this->classification . "]";
            if ($this->subClassification) {
                $string .= " [sub-class: " . $this->subClassification . "]";
            }
            
            if ($this->tableDef) {
                $string .= " [tabledef: " . $this->tableDef . "]";
            }
        }
        
        
        $stringsByColumn = $this->getStringsByColumns();
        
        foreach ($stringsByColumn as $key => $column_item) {
            if ($key > 0) {
                $string .= " | ";
            }
            foreach ($column_item as $key => $string_item) {
                $string .= " " . $string_item->CONTENT;
            }
            
            
        }
        
        return $string;
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
                $space = $strings[1]->HPOS - ($this->Strings[0]->HPOS + $this->Strings[0]->WIDTH);
                
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
                if ($strings[$i]->HPOS - ($strings[$i-1]->HPOS + $strings[$i-1]->WIDTH) > 8) {
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
        
        if ($this->Strings[0]->CONTENT === "Swisscanto") {
            //echo "dev: " . $avgSpaceOfStrings. "  - height : " . $this->HEIGHT . "\n";
        }
        
        if ($avgSpaceDeviationOfStrings < 5) {
            return $avgSpaceOfStrings + 10;
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
    function getStringsByColumns($tableHeaderInfo = null) {
        if ($tableHeaderInfo) {
            
            for($i=0; $i<count($tableHeaderInfo); $i++) {
                $columns[$i] = array();
                
                foreach($this->Strings as $key => $string_item) {
                    if ($i === count($tableHeaderInfo)-1) {
                        if ($string_item->HPOS > ($tableHeaderInfo[$i][0]->HPOS - 5)) {
                            array_push($columns[$i], $string_item);
                        }
                    } else {
                        if ($string_item->HPOS > ($tableHeaderInfo[$i][0]->HPOS - 5) && $string_item->HPOS < ($tableHeaderInfo[$i+1][0]->HPOS - 5) ) {
                            array_push($columns[$i], $string_item);
                        }
                    }
                    
                }
            }
            
            //print_r($columns);
        } else {
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
                
                foreach($this->Strings as $key => $string_item) {
                    if ($key > 0) {
                        $space = $string_item->HPOS - ($this->Strings[$key-1]->HPOS + $this->Strings[$key-1]->WIDTH);
                        
                        if ($this->Strings[0]->CONTENT === "VONCERT") {
                            //echo "effective space: " . $space . "\n";
                        }
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
        }
        
        
        return $columns;
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
    function getConcatenatedString() {
        $stringsByColumns= $this->getConcatenatedStringByColumns();
        
        $string = "";
        foreach($stringsByColumns as $column_item) {
            $string .= " " . $column_item;
        }
        
        return $string;
    }
    function shouldBeHeader() {
        $numberOfColumns = $this->getNumberOfColumns();
        
        $followingDataLine = $this->getFollowingDataLine();
        
        if ($numberOfColumns === 1 && $this->isLeftAligned() && !$this->isRightAligned()) {
            if ($this->Strings[count($this->Strings) - 1]->HPOS < 1000) {
                return 1;
            }
        }
        
        return 0;
    }
    function isLeftAligned() {
        if ($this->Strings[0]->HPOS < 300) return true;
        
        return false;
    }
    function isRightAligned() {
        if ($this->Strings[count($this->Strings) - 1]->HPOS > 1800) return true;
        
        return false;
    }
    function shouldBeStandAlone() {
        if ($this->RowIndex === 0) {
            return 1;
        }
        
        return 0;
    }
    function shouldBePartOfFreeText($lines) {
        $numberOfColumns = $this->getNumberOfColumns();
        
        if ($numberOfColumns === 1) {
            if ($this->isLeftAligned() && $this->isRightAligned()) {
                
                return 1;
            } else {
                if ($this->RowIndex > 0) {
                    if ($lines[$this->RowIndex - 1]) {
                        if ($lines[$this->RowIndex - 1]->getNumberOfColumns() === 1) {
                            
                            if ($lines[$this->RowIndex - 1]->isLeftAligned() && $lines[$this->RowIndex - 1]->isRightAligned()) {
                                if ($this->VPOS - $lines[$this->RowIndex - 1]->VPOS < 50) {
                                    return 1;
                                }
                            }
                        }
                    }
                }
                
                
                
            }
        }
        
        return 0;
    }
    function shouldBePartOfTable($lines) {
        $currentLineStringsByColumn = $this->getStringsByColumns();
        
        if ($this->RowIndex > 2 && $this->RowIndex < count($lines)-5) {
            //if (count($currentLineStringsByColumn) > 2)
            /*$previousLineStringsByColumn = $lines[$this->RowIndex -1]->getStringsByColumns();
             $previous2LineStringsByColumn = $lines[$this->RowIndex -2]->getStringsByColumns();
             $previous3LineStringsByColumn = $lines[$this->RowIndex -3]->getStringsByColumns();
             $followingLineStringsByColumn = $lines[$this->RowIndex +1]->getStringsByColumns();
             
             $following2LineStringsByColumn = $lines[$this->RowIndex +2]->getStringsByColumns();
             $following3LineStringsByColumn = $lines[$this->RowIndex +3]->getStringsByColumns();
             $following4LineStringsByColumn = $lines[$this->RowIndex +4]->getStringsByColumns();
             $following5LineStringsByColumn = $lines[$this->RowIndex +5]->getStringsByColumns();
             
             $counts_to_check = array();
             array_push($counts_to_check, count($followingLineStringsByColumn));
             array_push($counts_to_check, count($following2LineStringsByColumn));
             array_push($counts_to_check, count($following3LineStringsByColumn));
             array_push($counts_to_check, count($following4LineStringsByColumn));
             array_push($counts_to_check, count($following5LineStringsByColumn));
             array_push($counts_to_check, count($following6LineStringsByColumn));
             array_push($counts_to_check, count($followingLineStringsByColumn)+1);
             array_push($counts_to_check, count($following2LineStringsByColumn)+1);
             array_push($counts_to_check, count($following3LineStringsByColumn)+1);
             array_push($counts_to_check, count($following4LineStringsByColumn)+1);
             array_push($counts_to_check, count($following5LineStringsByColumn)+1);
             array_push($counts_to_check, count($following6LineStringsByColumn)+1);
             
             if (count($currentLineStringsByColumn) > 2 && in_array(count($currentLineStringsByColumn) , $counts_to_check)) {
             return 1;
             }
             if (count($previousLineStringsByColumn) > 2 && in_array(count($previousLineStringsByColumn) , $counts_to_check)) {
             return 1;
             }
             if (count($previous2LineStringsByColumn) > 2 && in_array(count($previous2LineStringsByColumn) , $counts_to_check)) {
             return 1;
             }
             if (count($previous3LineStringsByColumn) > 2 && in_array(count($previous3LineStringsByColumn) , $counts_to_check)) {
             return 1;
             }*/
        }
        
        
        return 0;
    }
    function shouldBePartOfKeyValueList($lines) {
        //TODO really?
        if ($this->Strings[0]->TextLine->HEIGHT > 40) {
            return 0;
        }
        
        $currentLineStringsByColumn = $this->getStringsByColumns();
        
        if (count($currentLineStringsByColumn) === 2) {
            return 1;
        } else if (count($currentLineStringsByColumn) > 2) {
            if ($lines[$this->RowIndex -1]) {
                $previousLineStringsByColumn = $lines[$this->RowIndex -1]->getStringsByColumns();
                
                if ($currentLineStringsByColumn) {
                    if (isset($currentLineStringsByColumn->Strings[0])) {
                        if (abs($currentLineStringsByColumn->Strings[0]->HPOS - $previousLineStringsByColumn[1]->Strings[0]->HPOS) < 5) {
                            return 1;
                        }
                    } else {
                        if ($previousLineStringsByColumn) {
                            if (isset($previousLineStringsByColumn[1]->Strings[0])) {
                                if (abs(0 - $previousLineStringsByColumn[1]->Strings[0]->HPOS) < 5) {
                                    return 1;
                                }
                            } else {
                                return 1;
                            }
                        }
                            
                        
                    }
                    
                }
                
            }
        }
        
        return 0;
    }
    function getFollowingDataLine() {
        if ($this->RowIndex < count($this->surroundingDataLines)) {
            return $this->surroundingDataLines[$this->RowIndex + 1];
        }
        
        return false;
    }
}
?>
