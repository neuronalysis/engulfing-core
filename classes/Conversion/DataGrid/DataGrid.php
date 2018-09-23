<?php
class DataGrid {
    var $DataGridRows = array();
    
    var $pageLeft;
    var $pageRight;
    var $pageCenter;
    var $pageTop;
    var $pageBottom;
    var $denseStartHPOS;
    var $denseStartHPOSNonColumnBased;
    var $denseEndHPOS;
    
    public static $instance;
    
    function __construct() {
        self::$instance = $this;
    }
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    function __toString() {
        $string = "";
        
        $string .= "\n\nData Grid --MARKUP INFO---   \n\n";
        
        $string .= "  LEFT: " . $this->pageLeft . " ; RIGHT: " . $this->pageRight . " ; TOP: " . $this->pageTop . " ; BOTTOM: " . $this->pageBottom . "\n\n";
        
        
        $string .= "\n\n\n\n";
        
        $string .= "\n\n\n\n";
        
        $string .= "\n\nData Grid --START---   \n\n";
        
        foreach ($this->DataGridRows as $rowIdx => $row_item) {
            $string .= "   --- Data ROW --START--- " . "VPOS: " . $row_item->VPOS . " HEIGHT: " . $row_item->HEIGHT . " WIDTH: " . $row_item->WIDTH . "     \n\n";
            if ($row_item->DataGridColumns) {
                foreach ($row_item->DataGridColumns as $colIdx => $column_item) {
                    $string .= "      --- Data COL --START--- " . "VPOS: " . $row_item->VPOS . " HPOS: " . $column_item->HPOS . " HEIGHT: " . $column_item->HEIGHT . " WIDTH: " . $column_item->WIDTH. "   \n\n";
                    
                    if ($column_item->DataLines) {
                        foreach ($column_item->DataLines as $line_item) {
                            $string .= "[" . sprintf('%01d', $rowIdx) . ":" . sprintf('%01d', $colIdx) . "]-" . $line_item . "\n";
                        }
                    }
                    
                    $string .= "      --- Data COL --END---   \n\n";
                }
            }
            $string .= "   --- Data ROW --END---   \n\n";
            
        }
        
        $string .= "\n\nData Grid --END---   \n\n";
        
        return $string;
    }
    function bringLinesToGrid($lines, $pageNumber) {
        $minHPOS = 100000;
        foreach($lines as $line_item) {
            $line_columns = $line_item->getStringsByColumns();
            foreach($line_columns as $line_strings) {
                if ($line_strings[0]->HPOS < $minHPOS) $minHPOS = $line_strings[0]->HPOS;
            }
            
        }
        
        $minVPOS = 100000;
        foreach($lines as $line_item) {
            $line_columns = $line_item->getStringsByColumns();
            foreach($line_columns as $line_strings) {
                if ($line_strings[0]->VPOS < $minVPOS) $minVPOS = $line_strings[0]->VPOS;
            }
        }
        
        $maxHPOS = 0;
        foreach($lines as $line_item) {
            $line_columns = $line_item->getStringsByColumns();
            foreach($line_columns as $line_strings) {
                if ($line_strings[count($line_strings)-1]->HPOS + $line_strings[count($line_strings)-1]->WIDTH > $maxHPOS) {
                    $maxHPOS = $line_strings[count($line_strings)-1]->HPOS + $line_strings[count($line_strings)-1]->WIDTH;
                }
            }
        }
        
        $maxVPOS = 0;
        foreach($lines as $line_item) {
            $line_columns = $line_item->getStringsByColumns();
            foreach($line_columns as $line_strings) {
                if ($line_strings[count($line_strings)-1]->VPOS + $line_strings[count($line_strings)-1]->HEIGHT > $maxVPOS) $maxVPOS = $line_strings[count($line_strings)-1]->VPOS + $line_strings[count($line_strings)-1]->HEIGHT;
            }
        }
        
        $this->pageLeft = $minHPOS;
        $this->pageRight = $maxHPOS;
        $this->pageTop = $minVPOS;
        $this->pageBottom = $maxVPOS;
        
        $this->pageCenter = $this->pageLeft + ($this->pageRight - $this->pageLeft) / 2;
        
        $this->addLines($lines);
        
        $this->classifyLines();
        
        $this->mergeLines();
    }
    function mergeLines() {
        foreach($this->DataGridRows as $rowIdx => $row_item) {
            foreach($row_item->DataGridColumns as $colIdx => $column_item) {
                unset($mergeTarget);
                
                $cell_lines = $column_item->DataLines;
                
                foreach($cell_lines as $dlIdx => $line_item) {
                    if(isset($line_item->Classification)) {
                        if ($line_item->Classification->isMergeCandidate === 1) {
                            if (!isset($mergeTarget)) {
                                $mergeTarget = $dlIdx -1;
                            }
                            
                            if (isset($cell_lines[$mergeTarget])) {
                                $mergeResult = $cell_lines[$mergeTarget]->mergeDataLine($line_item);
                            }
                            
                            if (count($mergeResult) === 0) {
                                unset($column_item->DataLines[$dlIdx]);
                            }
                        } else {
                            if (isset($mergeTarget)) {
                                unset($mergeTarget);
                            }
                        }
                    }
                    
                }
            }
        }
    }
    function classifyLines() {
        $classifier = new Classifier_DataLine($this);
        
        foreach($this->DataGridRows as $rowIdx => $row_item) {
            foreach($row_item->DataGridColumns as $colIdx => $column_item) {
                $cell_lines = $column_item->DataLines;
                
                foreach($cell_lines as $dlIdx => $line_item) {
                    $line_item->setStringColumns();
                    
                    $line_item->Classification = new Classification_DataLine();
                    
                    $classifier->classify($line_item);
                    
                    $classifier->harmonizeClassifications($line_item);
                    
                    $classifier->clusterContext = $line_item;
                }
            }
        }
    }
    function selectDataLine($rowIdx, $colIdx, $lineItemNumber) {
        if (isset($this->DataGridRows[$rowIdx]->DataGridColumns[$colIdx]->DataLines[$lineItemNumber])) {
            return $this->DataGridRows[$rowIdx]->DataGridColumns[$colIdx]->DataLines[$lineItemNumber];
        } else {
            return false;
        }
    }
    function addLines($lines) {
        $this->prepareGrid($lines);
        
        for($i=0; $i<count($lines); $i++) {
            $this->addLine($lines[$i], $lines);
        }
        
        foreach($this->DataGridRows as $row_item) {
            foreach($row_item->DataGridColumns as $col_item) {
                $col_item->denseStartHPOS = $this->getHighDensityStartHPOS($col_item->DataLines);
                $col_item->denseStartHPOSNonColumnBased = $this->getHighDensityStartHPOSNonColumnBased($col_item->DataLines);
                
                $col_item->denseEndHPOS = $this->getHighDensityEndHPOS($col_item->DataLines);
            }
        }
    }
    function getCoordDensities($lines) {
        $densities = array();
        
        foreach($lines as $line_item) {
            $line_columns = $line_item->getStringsByColumns();
            
            foreach($line_columns as $column_item) {
                $vpos = round($column_item[0]->VPOS / 20, 0) * 20;
                $hpos = round($column_item[0]->HPOS / 20, 0) * 20;
                
                if (isset($densities[$vpos][$hpos])) {
                    $densities[$vpos][$hpos]++;
                } else {
                    $densities[$vpos][$hpos] = 1;
                }
            }
        }
        
        return $densities;
    }
    function getRealColumnsAmount(DataLine $dataLine) {
        $amt = null;
        
        $matched_density_hpos = array();
        
        $line_columns = $dataLine->getStringsByColumns();
        $line_columns_concatenated = $dataLine->getConcatenatedStringByColumns();
        
        foreach($line_columns as $col_item) {
            foreach($this->denseStartHPOS as $key => $dense_hpos_item) {
                if ($col_item[0]->HPOS < ($dense_hpos_item + 100) && $col_item[0]->HPOS > ($dense_hpos_item - 100)) {
                    if (!in_array($key, $matched_density_hpos)) {
                        if ($dense_hpos_item < ($this->pageCenter + 100) && $dense_hpos_item > ($this->pageCenter - 100)) {
                            array_push($matched_density_hpos, $key);
                        
                            $amt++;
                        }
                    }
                }
            }
        }
        
        if ($amt) {
            if ($amt === 1) {
                if ($matched_density_hpos[0] === 1) {
                    $amt = 2;
                } else if ($matched_density_hpos[0] === 0) {
                    if ($line_columns[0][count($line_columns[0])-1]->HPOS < $this->denseStartHPOS[1]) {
                        if ($this->denseStartHPOS[1] < ($this->pageCenter + 100) && $this->denseStartHPOS[1] > ($this->pageCenter - 100)) {
                            $amt = 2;
                        }
                    }
                }
            }
        } else {
            if ($line_columns[0][count($line_columns[0])-1]->HPOS < $this->denseStartHPOS[1]) {
                if ($this->denseStartHPOS[1] < ($this->pageCenter + 100) && $this->denseStartHPOS[1] > ($this->pageCenter - 100)) {
                    $amt = 2;
                }
            }
        }
        
        if (!$amt) $amt = 1;
        
        return $amt;
    }
    function linesInlineWithColumns(DataLine $dataLine) {
        $line_columns = $dataLine->getStringsByColumns();
        
        foreach($line_columns as $col_strings) {
            if ($col_strings[0]->HPOS < $this->pageLeft && ($col_strings[count($col_strings)-1]->HPOS + $col_strings[count($col_strings)-1]->WIDTH) > $this->pageLeft) {
                return false;
            }
            if ($col_strings[0]->HPOS < $this->pageCenter && ($col_strings[count($col_strings)-1]->HPOS + $col_strings[count($col_strings)-1]->WIDTH) > $this->pageCenter) {
                return false;
            }
        }
        
        return true;
    }
    function linesInlineWithFirstColumn(DataLine $dataLine) {
        $line_columns = $dataLine->getStringsByColumns();
        
        foreach($line_columns as $key => $col_strings) {
            if ($col_strings[0]->HPOS < $this->pageLeft && ($col_strings[count($col_strings)-1]->HPOS + $col_strings[count($col_strings)-1]->WIDTH) > $this->pageLeft) {
                return false;
            } else {
                if ($key === 0) {
                    
                } else if ($key === count($line_columns)-1) {
                    
                } else {
                    $cpos_reference = ($line_columns[$key-1][0]->HPOS + $line_columns[$key-1][0]->WIDTH) + ($line_columns[$key+1][0]->HPOS - ($line_columns[$key-1][0]->HPOS + $line_columns[$key-1][0]->WIDTH) ) / 2;
                    $cpos_actual = ($line_columns[$key-1][0]->HPOS + $line_columns[$key-1][0]->WIDTH) + $line_columns[$key][0]->HPOS + ($line_columns[$key][0]->WIDTH / 2) ;
                    
                    if (abs($cpos_reference - $cpos_actual) < 20) {
                        $effectiveHPOS = $line_columns[$key-1][0]->HPOS + $line_columns[$key-1][0]->WIDTH;
                        $effectiveWIDTH = $line_columns[$key+1][0]->HPOS - $effectiveHPOS;
                        
                        if ($effectiveHPOS < $this->pageLeft && ($effectiveHPOS + $effectiveWIDTH) > $this->pageLeft) {
                            return false;
                        }
                    }
                }
            }
        }
        
        return true;
    }
    function containsStringsInFirstColumn(DataLine $dataLine) {
        $line_columns = $dataLine->getStringsByColumns();
        
        foreach($line_columns as $key => $col_strings) {
            if ($col_strings[0]->HPOS < $this->pageCenter) {
                return true;
            }
        }
        
        return false;
    }
    function containsStringsInSecondColumn(DataLine $dataLine) {
        $line_columns = $dataLine->getStringsByColumns();
        
        foreach($line_columns as $key => $col_strings) {
            if ($col_strings[0]->HPOS > $this->pageCenter) {
                return true;
            }
        }
        
        return false;
    }
    function isAlignedWithMajority(DataLine $dataLine) {
        $line_columns = $dataLine->getStringsByColumns();
        
        foreach($line_columns as $key => $col_strings) {
            foreach($this->denseStartHPOS as $denseHPOS) {
                if (abs($col_strings[0]->HPOS - $denseHPOS) < 50) {
                    return true;
                }
            }
        }
        
        return false;
    }
    function startsWith_Name(DataLine $dataLine) {
        $line_columns = $dataLine->getStringsByColumns();
        
        foreach($line_columns as $key => $col_strings) {
            if (strtolower($col_strings[0]->CONTENT) === "name") {
                return true;
            }
        }
        
        return false;
    }
    function linesInlineWithSecondColumn(DataLine $dataLine) {
        $line_columns = $dataLine->getStringsByColumns();
        
        foreach($line_columns as $key => $col_strings) {
            if ($col_strings[0]->HPOS < $this->pageCenter && ($col_strings[count($col_strings)-1]->HPOS + $col_strings[count($col_strings)-1]->WIDTH) > ($this->pageCenter)) {
                return false;
            } else {
                if ($key === 0) {
                    
                } else if ($key === count($line_columns)-1) {
                    
                } else {
                    $cpos_reference = ($line_columns[$key-1][0]->HPOS + $line_columns[$key-1][0]->WIDTH) + ($line_columns[$key+1][0]->HPOS - ($line_columns[$key-1][0]->HPOS + $line_columns[$key-1][0]->WIDTH) ) / 2;
                    $cpos_actual = ($line_columns[$key-1][0]->HPOS + $line_columns[$key-1][0]->WIDTH) + $line_columns[$key][0]->HPOS + ($line_columns[$key][0]->WIDTH / 2) ;
                    
                    if (abs($cpos_reference - $cpos_actual) < 20) {
                        $effectiveHPOS = $line_columns[$key-1][0]->HPOS + $line_columns[$key-1][0]->WIDTH;
                        $effectiveWIDTH = $line_columns[$key+1][0]->HPOS - $effectiveHPOS;
                        
                        if ($effectiveHPOS < $this->pageCenter && ($effectiveHPOS + $effectiveWIDTH) > $this->pageCenter) {
                            return false;
                        }
                    }
                }
            }
        }
        
        return true;
    }
    //TODO ugly
    function prepareGrid($lines) {
        $this->denseStartHPOS = $this->getHighDensityStartHPOS($lines);
        
        $this->denseStartHPOSNonColumnBased = $this->getHighDensityStartHPOSNonColumnBased($lines);
       
        $current_columns_amount = 0;
        
        $currentRow = null;
        $currentColumns = null;
        
        $startedWithName = false;
        
        foreach($lines as $line_item) {
            $line_columns = $line_item->getStringsByColumns();
            
            if ($current_columns_amount === 0 && count($line_columns) === 1) {
                $row = new DataGridRow();
                $row->HPOS = $this->pageLeft;
                $row->VPOS = 0;
                $row->WIDTH = $this->pageRight - $this->pageLeft;
                $row->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                
                $col = new DataGridColumn();
                $col->HPOS = $this->pageLeft;
                $col->VPOS = 0;
                $col->WIDTH = $this->pageRight - $this->pageLeft;
                $col->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $col->VPOS;
                
                $currentRow = $row;
                $currentColumns = array($col);
                
                $current_columns_amount = 1;
            } else if ($current_columns_amount === 0 && count($line_columns) === 2) {
                $row = new DataGridRow();
                $row->HPOS = $this->pageLeft;
                $row->VPOS = 0;
                $row->WIDTH = $this->pageRight - $this->pageLeft;
                $row->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                
                $col = new DataGridColumn();
                $col->HPOS = $this->pageLeft;
                $col->VPOS = 0;
                $col->WIDTH = $this->pageRight - $this->pageLeft;
                $col->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $col->VPOS;
                
                $currentRow = $row;
                $currentColumns = array($col);
                
                $current_columns_amount = 1;
            } else if ($current_columns_amount === 1 && count($line_columns) === 1) {
                $currentColumns[0]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[0]->VPOS;
                $currentRow->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentRow->VPOS;
            } else if ($current_columns_amount === 1 && count($line_columns) === 4) {
                if ($this->startsWith_Name($line_item)) {
                    $startedWithName = true;
                }
                if (!$startedWithName && $this->containsStringsInFirstColumn($line_item) && $this->containsStringsInSecondColumn($line_item) && $this->linesInlineWithFirstColumn($line_item) && $this->linesInlineWithSecondColumn($line_item)) {
                    if (($line_columns[2][0]->HPOS) > $this->pageCenter) {
                        $currentRow->addColumn($currentColumns[0]);
                        $this->addRow($currentRow);
                        
                        $currentRow = null;
                        $currentColumns = null;
                        
                        $row = new DataGridRow();
                        $row->HPOS = $this->pageLeft;
                        $row->VPOS = $line_item->VPOS;
                        $row->WIDTH = $this->pageRight - $this->pageLeft;
                        $row->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                        
                        $col_1 = new DataGridColumn();
                        $col_1->HPOS = $this->pageLeft;
                        $col_1->VPOS = $line_item->VPOS;
                        $col_1->WIDTH = $this->pageCenter - $this->pageLeft;
                        $col_1->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                        
                        $col_2 = new DataGridColumn();
                        $col_2->HPOS = $this->pageCenter;
                        $col_2->VPOS = $line_item->VPOS;
                        $col_2->WIDTH = $this->pageRight - $this->pageCenter;
                        $col_2->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                        
                        $currentRow = $row;
                        $currentColumns = array($col_1, $col_2);
                        
                        $current_columns_amount = 2;
                    } else {
                        $currentColumns[0]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[0]->VPOS;
                        
                        $currentRow->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentRow->VPOS;
                    }
                } else {
                    $currentColumns[0]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[0]->VPOS;
                    
                    $currentRow->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentRow->VPOS;
                }
                
            } else if ($current_columns_amount === 1 && count($line_columns) === 3) {
                if (($line_columns[1][count($line_columns[1])-1]->HPOS + $line_columns[1][count($line_columns[1])-1]->WIDTH) < $this->pageCenter) {
                    if (!$startedWithName && $this->linesInlineWithFirstColumn($line_item) && $this->linesInlineWithSecondColumn($line_item)) {
                        $currentRow->addColumn($currentColumns[0]);
                        $this->addRow($currentRow);
                        
                        $currentRow = null;
                        $currentColumns = null;
                        
                        $row = new DataGridRow();
                        $row->HPOS = $this->pageLeft;
                        $row->VPOS = $line_item->VPOS;
                        $row->WIDTH = $this->pageRight - $this->pageLeft;
                        $row->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                        
                        $col_1 = new DataGridColumn();
                        $col_1->HPOS = $this->pageLeft;
                        $col_1->VPOS = $line_item->VPOS;
                        $col_1->WIDTH = $this->pageCenter - $this->pageLeft;
                        $col_1->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                        
                        $col_2 = new DataGridColumn();
                        $col_2->HPOS = $this->pageCenter;
                        $col_2->VPOS = $line_item->VPOS;
                        $col_2->WIDTH = $this->pageRight - $this->pageCenter;
                        $col_2->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                        
                        $currentRow = $row;
                        $currentColumns = array($col_1, $col_2);
                        
                        $current_columns_amount = 2;
                    } else {
                        $currentColumns[0]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[0]->VPOS;
                        
                        $currentRow->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentRow->VPOS;
                    }
                } else {
                    $currentColumns[0]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[0]->VPOS;
                    
                    $currentRow->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentRow->VPOS;
                }
                
            } else if ($current_columns_amount === 2 && count($line_columns) === 4) {
                $currentColumns[0]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[0]->VPOS;
                $currentColumns[1]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[1]->VPOS;
                
                $currentRow->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentRow->VPOS;
            } else if ($current_columns_amount === 2 && count($line_columns) < 10) {
                $startedWithName = false;
                
                if ($this->linesInlineWithFirstColumn($line_item) && $this->linesInlineWithSecondColumn($line_item)) {
                    $currentColumns[0]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[0]->VPOS;
                    $currentColumns[1]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[1]->VPOS;
                    
                    $currentRow->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentRow->VPOS;
                } else {
                    $currentRow->addColumn($currentColumns[0]);
                    $currentRow->addColumn($currentColumns[1]);
                    $this->addRow($currentRow);
                    
                    $currentRow = null;
                    $currentColumns = null;
                    
                    $row = new DataGridRow();
                    $row->HPOS = $this->pageLeft;
                    $row->VPOS = $line_item->VPOS;
                    $row->WIDTH = $this->pageRight - $this->pageLeft;
                    $row->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                    
                    $col_1 = new DataGridColumn();
                    $col_1->HPOS = $this->pageLeft;
                    $col_1->VPOS = $line_item->VPOS;
                    $col_1->WIDTH = $this->pageRight - $this->pageLeft;
                    $col_1->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $row->VPOS;
                    
                    $currentRow = $row;
                    $currentColumns = array($col_1);
                    
                    $current_columns_amount = 1;
                }
            } else if ($current_columns_amount === 1 && !in_array(count($line_columns), array(1, 3, 4))) {
                $currentColumns[0]->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentColumns[0]->VPOS;
                $currentRow->HEIGHT = ($line_item->VPOS + $line_item->HEIGHT) - $currentRow->VPOS;
            } else {
                //echo "case not covered for\n";
                //echo "     " . $current_columns_amount . "; " . count($line_columns) . "\n";
                //echo "     " . $line_item . "\n";
            }
        }
        
        if($currentRow) {
            if(isset($currentColumns[0])) $currentRow->addColumn($currentColumns[0]);
            if(isset($currentColumns[1])) $currentRow->addColumn($currentColumns[1]);
            $this->addRow($currentRow);
        }
        
    }
    //TODO ugly
    function getHighPriorityVPOS($lines, $col_start_hpos, $col_end_hpos) {
        $vpos = array();
        
        array_push($vpos, 0);
        
        if (count($col_start_hpos) === 1 || !isset($col_end_hpos[0])) {
            array_push($vpos, $this->pageBottom);
        } else if (count($col_start_hpos) === 2) {
            $vpos_start = 10000;
            foreach($lines as $line_item) {
                $line_columns = $line_item->getStringsByColumns();
                
                if (count($line_columns) > 1) {
                    $lc_start = $line_columns[0][0]->HPOS;
                    $lc_end = $line_columns[count($line_columns)-1][count($line_columns[count($line_columns)-1])-1]->HPOS + $line_columns[count($line_columns)-1][count($line_columns[count($line_columns)-1])-1]->WIDTH;
                    
                    if (abs($lc_start - $col_start_hpos[0]) < 50) {
                        if ($lc_end > $col_end_hpos[0]) {
                            if ($vpos_start > $line_columns[0][0]->VPOS) {
                                $vpos_start = $line_columns[0][0]->VPOS;
                            }
                        }
                    }
                }
            }
            
            $vpos_end = 0;
            
            foreach($lines as $line_item) {
                $line_columns = $line_item->getStringsByColumns();
                
                if (count($line_columns) > 1) {
                    $lc_start = $line_columns[0][0]->HPOS;
                    
                    if (abs($lc_start - $col_start_hpos[0]) < 50) {
                        foreach($line_columns as $column_item) {
                            $lc_end = $column_item[count($column_item)-1]->HPOS + $column_item[count($column_item)-1]->WIDTH;
                            
                            if (abs($lc_end - $col_end_hpos[0]) < 50) {
                                if ($vpos_end < $column_item[0]->VPOS) {
                                    $vpos_end = $column_item[0]->VPOS;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            array_push($vpos, $this->pageBottom);
        }
        
        if (isset($vpos_start)) array_push($vpos, $vpos_start);
        if (isset($vpos_end)) array_push($vpos, $vpos_end);
        
        return $vpos;
    }
    function getHighDensityEndHPOS($lines) {
        $hpos = array();
        $filter = array();
        
        $floor = intval(count($lines) / 6);
        
        $floor = max($floor, 2);
        
        foreach(range(0, 2500, 50) as $number) {
            foreach($lines as $line_item) {
                $line_columns = $line_item->getStringsByColumns();
                
                foreach($line_columns as $column_item) {
                    $endHPOS = $column_item[count($column_item)-1]->HPOS + $column_item[count($column_item)-1]->WIDTH;
                    
                    if ($number <= $endHPOS && $number+50 > $endHPOS) {
                        if (isset($hpos[$number])) {
                            $hpos[$number]++;
                        } else {
                            $hpos[$number] = 1;
                        }
                    }
                }
            }
        }
        
        foreach($hpos as $key => $value) {
            if ($value >= $floor) {
                array_push($filter, $key);
            }
        }
        
        return $filter;
    }
    function getHighDensityStartHPOSNonColumnBased($lines) {
        $hpos = array();
        $filter = array();
        
        $floor = 4;
        
        foreach(range(300, 2500, 50) as $number) {
            foreach($lines as $line_item) {
                foreach($line_item->Strings as $string_item) {
                    $startHPOS = $string_item->HPOS;
                    
                    if ($number <= $startHPOS && $number+50 > $startHPOS) {
                        if (isset($hpos[$number])) {
                            $hpos[$number]++;
                        } else {
                            $hpos[$number] = 1;
                        }
                    }
                }
            }
        }
        
        $maxValue = 0;
        
        foreach($hpos as $key => $value) {
            if ($value >= $floor) {
                if ($value >= $maxValue) {
                    $filter = array($key);
                    $maxValue = $value;
                }
            }
        }
        
        return $filter;
    }
    function getHighDensityStartHPOS($lines) {
        $hpos = array();
        $filter = array();
        
        $floor = intval(count($lines) / 6);
        
        $floor = max($floor, 2);
        
        foreach(range(0, 2500, 50) as $number) {
            foreach($lines as $line_item) {
                $line_columns = $line_item->getStringsByColumns();
                
                foreach($line_columns as $column_item) {
                    $startHPOS = $column_item[0]->HPOS;
                    
                    if ($number <= $startHPOS && $number+50 > $startHPOS) {
                        if (isset($hpos[$number])) {
                            $hpos[$number]++;
                        } else {
                            $hpos[$number] = 1;
                        }
                    }
                }
                
            }
        }
        
        foreach($hpos as $key => $value) {
            if ($value >= $floor) {
                array_push($filter, $key);
            }
        }
        
        return $filter;
    }
    function selectTargetRow(DataLine $dataLine) {
        $targetRow = null;
        
        for($i=0; $i<count($this->DataGridRows); $i++) {
            if (($dataLine->VPOS + $dataLine->HEIGHT) >= $this->DataGridRows[$i]->VPOS && $dataLine->VPOS <= ($this->DataGridRows[$i]->VPOS + $this->DataGridRows[$i]->HEIGHT)) {
                $targetRow = $this->DataGridRows[$i];
            }
        }
        
        return $targetRow;
    }
    function selectTargetColumn(DataLine $dataLine) {
        $targetColumn = null;
        
        $targetRow = $this->selectTargetRow($dataLine);
        
        if ($targetRow) {
            for($i=0; $i<count($targetRow->DataGridColumns); $i++) {
                if (($dataLine->HPOS) >= $targetRow->DataGridColumns[$i]->HPOS && ($dataLine->HPOS) <= ($targetRow->DataGridColumns[$i]->HPOS + $targetRow->DataGridColumns[$i]->WIDTH)) {
                    $targetColumn = $targetRow->DataGridColumns[$i];
                }
            }
        }
        
        return $targetColumn;
    }
    function splitDataLineForGridRow(DataLine $dataLine, DataGridRow $row) {
        $splitted_dls = array();
        
        $line_columns = $dataLine->getStringsByColumns();
        
        foreach($row->DataGridColumns as $key => $col_item) {
            $dl = new DataLine();
            
            foreach($line_columns as $linecol_item) {
                if ($linecol_item[0]->HPOS >= $col_item->HPOS && ($linecol_item[count($linecol_item)-1]->HPOS + $linecol_item[count($linecol_item)-1]->WIDTH) <= $col_item->HPOS + $col_item->WIDTH) {
                    $dl->ColumnIndex = $key;
                    $dl->RowIndex = $dataLine->RowIndex;
                    $dl->VPOS = $dataLine->VPOS;
                    if (!$dl->HPOS) $dl->HPOS = $linecol_item[0]->HPOS;
                    $dl->HEIGHT = $dataLine->HEIGHT;
                    $dl->WIDTH = ($linecol_item[count($linecol_item)-1]->HPOS + $linecol_item[count($linecol_item)-1]->WIDTH) - $dl->HPOS;
                    
                    $dl->Strings = array_merge($dl->Strings, $linecol_item);
                }
            }
            
            if (count($dl->Strings) > 0) {
                array_push($splitted_dls, $dl);
            }
        }
        
        return $splitted_dls;
    }
    function addLine(DataLine $dataLine, $lines) {
        $targetRow = $this->selectTargetRow($dataLine);
        
        if (count($targetRow->DataGridColumns) === 1) {
            $targetColumn = $this->selectTargetColumn($dataLine);
            
            $dataLine->RowIndex = count($targetColumn->DataLines);
            array_push($targetColumn->DataLines, $dataLine);
        } else {
            $splitted_dls = $this->splitDataLineForGridRow($dataLine, $targetRow);
            
            foreach($splitted_dls as $splitted_dl_item) {
                $splitted_dl_item->setStringColumns();
                
                $targetColumn = $this->selectTargetColumn($splitted_dl_item);
                
                $splitted_dl_item->RowIndex = count($targetColumn->DataLines);
                array_push($targetColumn->DataLines, $splitted_dl_item);
            }
        }
    }
    function addRow(DataGridRow $row) {
        $row->index = count($this->DataGridRows);
        
        array_push($this->DataGridRows, $row);
    }
    function getLastRow() {
        return $this->DataGridRows[count($this->DataGridRows)-1];
    }
}
?>