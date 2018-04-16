<?php
class DataGrid {
    var $DataGridRows = array();
    
    function plott() {
        $string = "";
        
        $string .= "\n\nData Grid --START---   \n\n";
        
        foreach ($this->DataGridRows as $rowIdx => $row_item) {
            $string .= "   --- Data ROW --START--- " . "VPOS: " . $row_item->VPOS . "   \n\n";
            if ($row_item->DataGridColumns) {
                foreach ($row_item->DataGridColumns as $colIdx => $column_item) {
                    $string .= "      --- Data COL --START--- " . "VPOS: " . $row_item->VPOS . " HPOS: " . $column_item->HPOS . "   \n\n";
                    foreach ($column_item->DataLines as $line_item) {
                        $string .= "[" . $rowIdx . " : " . $colIdx . "] -  " . $line_item->plott() . "\n";
                    }
                    $string .= "      --- Data COL --END---   \n\n";
                }
            }
            $string .= "   --- Data ROW --END---   \n\n";
            
        }
        
        $string .= "\n\nData Grid --END---   \n\n";
        
        return $string;
    }
    function addLines($lines) {
        $this->prepareGrid($lines);
        
        for($i=0; $i<count($lines); $i++) {
            $this->addLine($lines[$i], $lines);
        }
    }
    function prepareGrid($lines) {
        $denseStartHPOS = $this->getHighDensityStartHPOS($lines);
        $denseEndHPOS = $this->getHighDensityEndHPOS($lines);
        
        $vposPoints = $this->getHighPriorityVPOS($lines, $denseStartHPOS, $denseEndHPOS);
        
        //print_r($vposPoints);
        //print_r($denseEndHPOS);
        
        $coords = array();
        
        if (isset($denseStartHPOS[1])) {
            if ($denseStartHPOS[1] > 1000) {
                foreach($vposPoints as $vkey => $vpos) {
                    $row = new DataGridRow();
                    $row->VPOS = $vpos;
                    $row->HPOS = $denseStartHPOS[0];
                    
                    
                    if ($vkey === 0) {
                        $col = new DataGridColumn();
                        $col->VPOS = $vpos;
                        $col->HPOS= $row->HPOS;
                        $row->addColumn($col);
                    } else {
                        if ($vkey === 1) {
                            foreach($denseStartHPOS as $hkey => $hpos) {
                                $col = new DataGridColumn();
                                $col->VPOS = $vpos;
                                $col->HPOS = $hpos;
                                
                                $row->addColumn($col);
                            }
                        } else {
                            $col = new DataGridColumn();
                            $col->VPOS = $vpos;
                            $col->HPOS= $row->HPOS;
                            $row->addColumn($col);
                        }
                        
                    }
                    
                    
                    $this->addRow($row);
                }
            } else {
                $row = new DataGridRow();
                $row->VPOS = 0;
                $row->HPOS = 0;
                
                $col = new DataGridColumn();
                $col->VPOS = 0;
                $col->HPOS = 0;
                
                $row->addColumn($col);
                
                $this->addRow($row);
            }
        } else {
            $row = new DataGridRow();
            $row->VPOS = 0;
            $row->HPOS = 0;
            
            $col = new DataGridColumn();
            $col->VPOS = 0;
            $col->HPOS = 0;
            
            $row->addColumn($col);
            
            $this->addRow($row);
        }
        
        
        
        //echo $this->plott();
    }
    function getHighPriorityVPOS($lines, $col_start_hpos, $col_end_hpos) {
        $vpos = array();
        
        array_push($vpos, 0);
        
        $crossovers = array();
        
        foreach($lines as $line_item) {
            $line_columns = $line_item->getStringsByColumns();
            
            if (count($line_columns) === 1) {
                
            } else {
                foreach($line_columns as $column_item) {
                    foreach($col_start_hpos as $key => $hpos) {
                        if ($key > 0 && abs($column_item[0]->HPOS - $hpos) < 50) {
                            $crossovers[$column_item[0]->TextLine->VPOS]++;
                        }
                    }
                    foreach($col_end_hpos as $key => $hpos) {
                        if ($key > 0 && abs($column_item[0]->HPOS + $column_item[0]->WIDTH - $hpos) < 50) {
                            $crossovers[$column_item[0]->TextLine->VPOS]++;
                        }
                    }
                }
            }
        }
        
        $i=0;
        
        foreach($crossovers as $key => $value) {
            if ($i === 0) {
                array_push($vpos, $key);
            }
                
            if ($i === count($crossovers)-1) {
                array_push($vpos, $key);
            }
            $i++;
        }
        
        return $vpos;
    }
    function getHighDensityEndHPOS($lines) {
        $hpos = array();
        $filter = array();
        
        foreach(range(0, 2500, 50) as $number) {
            foreach($lines as $line_item) {
                $line_columns = $line_item->getStringsByColumns();
                
                foreach($line_columns as $column_item) {
                    $endHPOS = $column_item[count($column_item)-1]->HPOS + $column_item[count($column_item)-1]->WIDTH;
                    
                    if ($number <= $endHPOS && $number+50 > $endHPOS) {
                        if (isset($hpos[$number])) {
                            $hpos[$number]++;
                        }
                    }
                }
                
            }
        }
        
        
        foreach($hpos as $key => $value) {
            if ($value > 7) {
                array_push($filter, $key);
            }
        }
        
        return $filter;
    }
    function getHighDensityStartHPOS($lines) {
        $hpos = array();
        $filter = array();
        
        foreach(range(0, 2500, 50) as $number) {
            foreach($lines as $line_item) {
                $line_columns = $line_item->getStringsByColumns();
                
                foreach($line_columns as $column_item) {
                    $startHPOS = $column_item[0]->HPOS;
                    
                    if ($number <= $startHPOS && $number+50 > $startHPOS) {
                        if (isset($hpos[$number])) {
                            $hpos[$number]++;
                        }
                    }
                }
                
            }
        }
        
        
        foreach($hpos as $key => $value) {
            if ($value > 7) {
                array_push($filter, $key);
            }
        }
        
        return $filter;
    }
    function selectTargetRow(DataLine $dataLine) {
        $targetRow = null;
        
        if (count($this->DataGridRows) === 1) {
            $targetRow = $this->DataGridRows[0];
        } else {
            for($i=0; $i<count($this->DataGridRows); $i++) {
                if ($i !== count($this->DataGridRows)-1) {
                    if (abs($dataLine->VPOS - $this->DataGridRows[$i]->VPOS) < 2) {
                        $targetRow = $this->DataGridRows[$i];
                    } else {
                        if ($dataLine->VPOS > ($this->DataGridRows[$i]->VPOS) && $dataLine->VPOS < ($this->DataGridRows[$i+1]->VPOS - 2)) {
                            $targetRow = $this->DataGridRows[$i];
                        }
                    }
                }
                
            }
        }
        
        
        return $targetRow;
    }
    function selectTargetColumn(DataLine $dataLine) {
        $targetColumn= null;
        
        $targetRow = $this->selectTargetRow($dataLine);
        
        if ($targetRow) {
            //echo $targetRow->VPOS . " - count.cols: " . count($targetRow->DataGridColumns) . "\n";
            
            for($i=0; $i<count($targetRow->DataGridColumns); $i++) {
                if ($i !== count($targetRow->DataGridColumns)-1) {
                    if (abs($dataLine->Strings[0]->HPOS - $targetRow->DataGridColumns[$i]->HPOS) < 2) {
                        $targetColumn = $targetRow->DataGridColumns[$i];
                    } else {
                        if ($dataLine->Strings[0]->HPOS > ($targetRow->DataGridColumns[$i]->HPOS) && $dataLine->Strings[0]->HPOS < ($targetRow->DataGridColumns[$i+1]->HPOS - 2)) {
                            $targetColumn = $targetRow->DataGridColumns[$i];
                        }
                    }
                } else {
                    if ($dataLine->Strings[0]->HPOS > ($targetRow->DataGridColumns[$i]->HPOS)) {
                        $targetColumn = $targetRow->DataGridColumns[$i];
                    }
                }
            }
        }
        
        return $targetColumn;
    }
    function addLine(DataLine $dataLine) {
        $line_columns = $dataLine->getStringsByColumns();
        
        foreach($line_columns as $linecol_item) {
            $dl = new DataLine();
            $dl->VPOS = $dataLine->VPOS;
            $dl->Strings = $linecol_item;
            //$dl->RowIndex = $dataLine->RowIndex;
            
            //echo "\n\n" . $dl->plott() . "\n";
            
            $targetColumn = $this->selectTargetColumn($dl);
            //if ($targetColumn) echo "target-col: " . $targetColumn->HPOS  . "\n";
            
            if ($targetColumn) {
                if (count($targetColumn->DataLines) > 0) {
                    $dl_vpos_contained = false;
                    
                    foreach($targetColumn->DataLines as $col_dataline) {
                        if ($col_dataline->VPOS === $dl->VPOS) {
                            $dl_vpos_contained = $col_dataline;
                        }
                    }
                    
                    if ($dl_vpos_contained) {
                        $dl_vpos_contained->Strings = array_merge($dl_vpos_contained->Strings, $dl->Strings);
                    } else {
                        $dl->RowIndex = count($targetColumn->DataLines);
                        array_push($targetColumn->DataLines, $dl);
                    }
                } else {
                    $dl->RowIndex = count($targetColumn->DataLines);
                    array_push($targetColumn->DataLines, $dl);
                }
            }
            
        }
    }
    function addRow(DataGridRow $row) {
        array_push($this->DataGridRows, $row);
    }
    function getLastRow() {
        return $this->DataGridRows[count($this->DataGridRows)-1];
    }

    
    
}
class DataGridRow {
    var $HPOS;
    var $VPOS;
    
    var $DataGridColumns = array();
    
    function addColumn(DataGridColumn $col) {
        array_push($this->DataGridColumns, $col);
    }
    
}
class DataGridColumn {
    var $HPOS;
    var $VPOS;
    
    var $DataLines = array();
    
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