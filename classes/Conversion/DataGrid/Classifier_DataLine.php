<?php
class Classifier_DataLine extends Classifier {
    var $tableHeaderInfo = null;
    var $keyValueInfo = null;
    var $mergeTargetRowIndex;
    var $clusterContext;
    
    function __construct(DataGrid $dataGrid = null) {
        $this->Context = $dataGrid;
    }
    function classify(DataLine $dataLine) {
        $this->shouldBeHeader($dataLine);
        $this->shouldBePartOfFreeText($dataLine);
        $this->shouldBeKeyValue($dataLine);
        $this->shouldBeTable($dataLine);
    }
    function harmonizeClassifications(DataLine $dataLine) {
        if ($dataLine->Classification->isStandAlone === 1) {
            //$dataLine->Classification->name = "ALLONE";
        } else if ($dataLine->Classification->isHeader === 1) {
            $dataLine->Classification->name = "HEADER";
        } else if ($dataLine->Classification->isPartOfFreeText === 1) {
            $dataLine->Classification->name= "FREETEXT";
        } else if ($dataLine->Classification->isPartOfKeyValueList === 1) {
            
            $dataLine->Classification->name = "KEYVALUE";
            
            if ($dataLine->Classification->isPartOfTable === 1) {
                $dataLine->Classification->Classification = new Classification_DataLine();
                
                $dataLine->Classification->Classification->name= "TABLE";
                
                $dataLine->Classification->Classification->Classification = new Classification_DataLine();
                
                if ($dataLine->Classification->isTableHeader === 1) {
                    $dataLine->Classification->Classification->Classification->name = "T-HEADER";
                    
                } else if ($dataLine->Classification->isTableDataRow === 1) {
                    $dataLine->Classification->Classification->Classification->name = "T-DATAROW";
                }
            }
        } else if ($dataLine->Classification->isPartOfTable === 1) {
            if (isset($this->clusterContext->Classification)) {
                if ($this->clusterContext->Classification->isPartOfTable === 1) {
                    if ($this->clusterContext->Classification->isPartOfKeyValueList === 1) {
                        $dataLine->Classification->isPartOfKeyValueList = 1;
                        $dataLine->Classification->name = "KEYVALUE";
                        
                        $dataLine->Classification->Classification = new Classification_DataLine();
                        
                        $dataLine->Classification->Classification->isPartOfTable = 1;
                        
                        
                        $dataLine->Classification->Classification->name = "TABLE";
                        
                        $dataLine->Classification->Classification->Classification = new Classification_DataLine();
                        if ($dataLine->Classification->isTableHeader === 1) {
                            $dataLine->Classification->Classification->Classification->name = "T-HEADER";
                            
                        } else if ($dataLine->Classification->isTableDataRow === 1) {
                            $dataLine->Classification->Classification->Classification->name = "T-DATAROW";
                        }
                    } else {
                        $dataLine->Classification->name = "TABLE";
                        
                        $dataLine->Classification->Classification = new Classification_DataLine();
                        if ($dataLine->Classification->isTableHeader === 1) {
                            $dataLine->Classification->Classification->name = "T-HEADER";
                            
                        } else if ($dataLine->Classification->isTableDataRow === 1) {
                            $dataLine->Classification->Classification->name = "T-DATAROW";
                        }
                    }
                    
                } else {
                    $dataLine->Classification->name = "TABLE";
                    
                    $dataLine->Classification->Classification = new Classification_DataLine();
                    if ($dataLine->Classification->isTableHeader === 1) {
                        $dataLine->Classification->Classification->name = "T-HEADER";
                        
                    } else if ($dataLine->Classification->isTableDataRow === 1) {
                        $dataLine->Classification->Classification->name = "T-DATAROW";
                    }
                }
            }
            
            
            
            
            
            //$dataLine->Classification->Classification->name = $this->classifyTable($dataLine);
            
        }
    }
    function belongsToPreviousLine(DataLine $dataLine) {
        $targetColumn = $this->Context->selectTargetColumn($dataLine);
        $lines = $targetColumn->DataLines;
        
        if (isset($lines[$dataLine->RowIndex -2])) {
            $vspace = $dataLine->VPOS - ($lines[$dataLine->RowIndex -1]->VPOS + $lines[$dataLine->RowIndex -1]->HEIGHT);
            $vspace_previous = $lines[$dataLine->RowIndex -1]->VPOS - ($lines[$dataLine->RowIndex -2]->VPOS + $lines[$dataLine->RowIndex -2]->HEIGHT);
            
            $width = $dataLine->WIDTH;
            $width_previous = $lines[$dataLine->RowIndex -1]->WIDTH;
            $width_previous_previous = $lines[$dataLine->RowIndex -2]->WIDTH;
            
            if (abs($vspace - $vspace_previous) < 20) {
                if ($width < $width_previous / 2) {
                    return true;
                }
            }
        }
        
        
        return false;
    }
    function shouldBeStandAlone(DataLine $dataLine) {
        $targetColumn = $this->Context->selectTargetColumn($dataLine);
        $lines = $targetColumn->DataLines;
        
        /*if ($dataLine->RowIndex === 0) {
            if(abs($lines[$dataLine->RowIndex + 1]->VPOS - ($dataLine->VPOS + $dataLine->HEIGHT) ) > 50) {
                return 1;
            }
        } else {
            if (isset($lines[$dataLine->RowIndex + 1])) {
                if ($this->clusterContext === "TABLE") {
                    
                } else {
                    if(abs($lines[$dataLine->RowIndex + 1]->VPOS - ($dataLine->VPOS + $dataLine->HEIGHT) ) > 50) {
                        if ($this->shouldBePartOfFreeText($dataLine) !== 1) {
                            return 1;
                        }
                    }
                }
                
            }
            
        }*/
        
        return 0;
    }
    function shouldBePartOfFreeText(DataLine $dataLine) {
        
        // criteria
        $hasExactlyOneColumns = $this->hasExactlyOneColumns($dataLine);
        $hasDelimitedStrings = $this->hasDelimitedStrings($dataLine);
        $hasAtLeastFiveWords = $this->hasAtLeastFiveWords($dataLine);
        $isInlineWithValueIndent = $this->isInlineWithValueIndent($dataLine);
        $belongsToPreviousLine = $this->belongsToPreviousLine($dataLine);
        $isRightAligned = $dataLine->isRightAligned();
        $isLeftAligned = $dataLine->isLeftAligned();
        
        // sufficient criteria
        $conditions_sufficient = array();
        array_push($conditions_sufficient,
            $hasExactlyOneColumns && ($isRightAligned && $isLeftAligned)
        );
        array_push($conditions_sufficient,
            $hasExactlyOneColumns && (!$isRightAligned && $isLeftAligned && $belongsToPreviousLine)
        );
        
        // necessities
        $conditions_necessary = array();
        
        array_push($conditions_necessary, $hasExactlyOneColumns);
        array_push($conditions_necessary, !$hasDelimitedStrings);
        array_push($conditions_necessary, !$isInlineWithValueIndent);
        array_push($conditions_necessary, $hasAtLeastFiveWords || $belongsToPreviousLine);
        array_push($conditions_necessary, $isLeftAligned);
        
        $res = $this->verifyCriteria($conditions_necessary, $conditions_sufficient);
        
        $dataLine->Classification->isPartOfFreeText = $res;
        
        return $res;
    }
    function hasDelimitedStrings(DataLine $dataLine) {
        $stringsByColumns = $dataLine->getConcatenatedStringByColumns();
        
        
        if (stripos($stringsByColumns[0], " / ")) {
            return true;
        }
        
        if (stripos($stringsByColumns[0], ": ")) {
            return true;
        }
        
        return false;
    }
    function shouldBeHeader(DataLine $dataLine) {
        // criteria
        $hasExactlyOneColumns = $this->hasExactlyOneColumns($dataLine);
        $hasAtLeastFiveWords = $this->hasAtLeastFiveWords($dataLine);
        $isLeftAligned = $dataLine->isLeftAligned();
        $belongsToPreviousLine = $this->belongsToPreviousLine($dataLine);
        
        // sufficient criteria
        $conditions_sufficient = array();
        array_push($conditions_sufficient,
            $hasExactlyOneColumns && $isLeftAligned && !$hasAtLeastFiveWords && !$belongsToPreviousLine
        );
        
        
        // necessities
        $conditions_necessary = array();
        
        $res = $this->verifyCriteria($conditions_necessary, $conditions_sufficient);
        
        $dataLine->Classification->isHeader= $res;
        
        return $res;
    }
    function isInContextTableHeader() {
        if ($this->clusterContext) {
            if ($this->clusterContext->Classification) {
                if ($this->clusterContext->Classification->name === "TABLE") {
                    if ($this->clusterContext->Classification->Classification->name === "T-HEADER") {
                        return true;
                    }
                } else {
                    if ($this->clusterContext->Classification->Classification) {
                        if ($this->clusterContext->Classification->Classification->name === "TABLE") {
                            if ($this->clusterContext->Classification->Classification->Classification) {
                                if ($this->clusterContext->Classification->Classification->Classification->name === "T-HEADER") {
                                    return true;
                                }
                            }
                            
                        }
                    }
                }
            }
        }
        
        return false;
    }
    function isInContextKeyValue() {
        if ($this->clusterContext) {
            if ($this->clusterContext->Classification) {
                if ($this->clusterContext->Classification->name === "KEYVALUE") {
                    return true;
                }
            }
        }
        
        return false;
    }
    function isInContextTableDataRow() {
        if ($this->clusterContext) {
            if ($this->clusterContext->Classification) {
                if ($this->clusterContext->Classification->name === "TABLE") {
                    if ($this->clusterContext->Classification->Classification->name === "T-DATAROW") {
                        return true;
                    }
                } else {
                    if ($this->clusterContext->Classification->Classification) {
                        if ($this->clusterContext->Classification->Classification->name === "TABLE") {
                            if ($this->clusterContext->Classification->Classification->Classification) {
                                if ($this->clusterContext->Classification->Classification->Classification->name === "T-DATAROW") {
                                    return true;
                                }
                            }
                            
                        }
                    }
                }
            }
        }
        
        return false;
    }
    function hasNormalVSpace(DataLine $dataLine) {
        $targetColumn = $this->Context->selectTargetColumn($dataLine);
        $lines = $targetColumn->DataLines;
        
        if (isset($lines[$dataLine->RowIndex -2])) {
            $vspace = $dataLine->VPOS - ($lines[$dataLine->RowIndex -1]->VPOS + $lines[$dataLine->RowIndex -1]->HEIGHT);
            $vspace_previous = $lines[$dataLine->RowIndex -1]->VPOS - ($lines[$dataLine->RowIndex -2]->VPOS + $lines[$dataLine->RowIndex -2]->HEIGHT);
            
            if (abs($vspace - $vspace_previous) < 20) {
                return true;
            }
        }
        
        
        return false;
    }
    function hasOnlyColumnsInlineWithKeyValue(DataLine $dataLine) {
        /*$stringsByColumn = $dataLine->getStringsByColumns();
        
        
        if ($this->tableHeaderInfo) {
            print_r($this->tableHeaderInfo);
            
            $headerStringsByColumn = $this->tableHeaderInfo->getStringsByColumns();
            
            foreach($stringsByColumn as $string_column_item) {
                $columnMatch = false;
                
                foreach($headerStringsByColumn as $header_column_item) {
                    if (abs($string_column_item[0]->HPOS - $header_column_item[0]->HPOS) < 5) {
                        $columnMatch = true;
                    }
                }
                
                if (!$columnMatch) return false;
            }
            
            return true;
        }*/
        
        return false;
    }
    function hasOnlyColumnsInlineWithKeyValueAndHeaderInfo(DataLine $dataLine) {
        
    }
    function hasAtLeastTwoColumns($dataLine) {
        $currentLineStringsByColumn = $dataLine->getStringsByColumns();
        
        if (count($currentLineStringsByColumn) >= 2) {
            return true;
        }
        
        return false;
    }
    function hasAtLeastFiveWords($dataLine) {
        $currentLineStringsByColumn = $dataLine->getStringsByColumns();
        
        $words = 0;
        foreach ($currentLineStringsByColumn as $column_item) {
            $words += count($column_item);
        }
        
        if ($words >= 5) {
            return true;
        }
        
        return false;
    }
    function hasExactlyTwoColumns($dataLine) {
        $currentLineStringsByColumn = $dataLine->getStringsByColumns();
        
        if (count($currentLineStringsByColumn) === 2) {
            return true;
        }
        
        return false;
    }
    function hasExactlyOneColumns($dataLine) {
        $currentLineStringsByColumn = $dataLine->getStringsByColumns();
        
        if (count($currentLineStringsByColumn) === 1) {
            return true;
        }
        
        return false;
    }
    function hasAtLeastThreeColumns($dataLine) {
        $currentLineStringsByColumn = $dataLine->getStringsByColumns();
        
        if (count($currentLineStringsByColumn) >= 3) {
            return true;
        }
        
        return false;
    }
    function doesNotStartWith_Name($dataLine) {
        $concatenatedStringsByColumns = $dataLine->getConcatenatedStringByColumns();
        
        if (strtolower($concatenatedStringsByColumns[0]) !== "name") {
            return true;
        }
        
        return false;
    }
    
    function isInSuperContextTable($dataLine) {
        if ($this->clusterContext) {
            if ($this->clusterContext->Classification->name === "TABLE") {
                return true;
            }
        }
        
        return false;
    }
    function isInContextTable($dataLine) {
        if ($this->clusterContext) {
            if ($this->clusterContext->Classification->name === "TABLE") {
                return true;
            } else {
                if (isset($this->clusterContext->Classification->Classification)) {
                    if ($this->clusterContext->Classification->Classification->name === "TABLE") {
                        return true;
                    }
                }
                
            }
        }
        
        return false;
    }
    function shouldBeTable(DataLine $dataLine) {
        $res = $this->shouldBeTableHeaderOrDataRow($dataLine);
        
        $dataLine->Classification->isPartOfTable = $res;
        
        return $res;
    }
    function shouldBeTableHeader(DataLine $dataLine) {
        $res = 0;
        
        $hasExactlyOneColumns = $this->hasExactlyOneColumns($dataLine);
        $hasAtLeastTwoColumns = $this->hasAtLeastTwoColumns($dataLine);
        
        $doesStartWith_Name = !$this->doesNotStartWith_Name($dataLine);
        
        $isInTableContext = $this->isInContextTable($dataLine);
        
        $isInContextKeyValue = $this->isInContextKeyValue($dataLine);
        $isInContextTableHeader = $this->isInContextTableHeader($dataLine);
        
        $hasAtLeastThreeColumns = $this->hasAtLeastThreeColumns($dataLine);
        
        $hasEqualAmountOfColumnsLikeHeader = $this->hasEqualAmountOfColumnsLikeHeader($dataLine);
        
        $hasOnlyColumnsInlineWithHeaderInfo = $this->hasOnlyColumnsInlineWithHeaderInfo($dataLine);
        $hasOnlyColumnsInlineWithKeyValueAndHeaderInfo = $this->hasOnlyColumnsInlineWithKeyValueAndHeaderInfo($dataLine);
        
        if ($hasAtLeastTwoColumns) {
            if ($doesStartWith_Name) {
                $this->tableHeaderInfo = $dataLine;
                
                $res = 1;
            } else {
                if (!$isInTableContext) {
                    if ($hasAtLeastThreeColumns) {
                        $this->tableHeaderInfo = $dataLine;
                        
                        $res = 1;
                    }
                    
                } else {
                    if ($isInContextTableHeader) {
                        if ($hasOnlyColumnsInlineWithHeaderInfo) {
                            if (!$hasEqualAmountOfColumnsLikeHeader) {
                                $res = 1;
                            }
                        }
                    }
                }
            }
            
        } else if ($hasExactlyOneColumns) {
            if ($isInContextTableHeader&& $hasOnlyColumnsInlineWithHeaderInfo) {
                $res = 1;
            }
            
        }
        
        if ($res === 1) {
            if ($isInContextTableHeader) {
                $dataLine->Classification->isMergeCandidate = 1;
            }
        }
        
        $dataLine->Classification->isPartOfTable = $res;
        
        return $res;
    }
    function shouldBeTableDataRow(DataLine $dataLine) {
        $res = 0;
        
        $stringsByColumn = $dataLine->getStringsByColumns();
        
        # certain necessities
        
        # has at least two columns
        $hasAtLeastTwoColumns = $this->hasAtLeastTwoColumns($dataLine);
        
        
        # probable necessities
        
        # is in context of table header
        $isInContextTableHeader = $this->isInContextTableHeader();
        # is in context of table datarow
        $isInContextTableDataRow = $this->isInContextTableDataRow();
        
        $isInlineWithKeyIndent= $this->isInlineWithKeyIndent($dataLine);
        
        # is having columns inline with table header info columns hpos
        
        # has normal vspace
        $hasNormalVSpace= $this->hasNormalVSpace($dataLine);
        
        $hasEqualAmountOfColumnsLikeHeader = $this->hasEqualAmountOfColumnsLikeHeader($dataLine);
        
        $hasOnlyColumnsInlineWithHeaderInfo = $this->hasOnlyColumnsInlineWithHeaderInfo($dataLine);
        
        
        if ($hasAtLeastTwoColumns) {
            if ($isInContextTableHeader) {
                if ($hasEqualAmountOfColumnsLikeHeader) {
                    $res = 1;
                }
            } else if ($isInContextTableDataRow) {
                if ($hasEqualAmountOfColumnsLikeHeader) {
                    if ($hasNormalVSpace) {
                        $res = 1;
                    }
                } else {
                    if ($hasNormalVSpace) {
                        $dataLine->Classification->isMergeCandidate = 1;
                        $res = 1;
                    }
                }
            }
        } else {
            $stringsByColumns = $dataLine->getStringsByColumns();
            
            if ($hasOnlyColumnsInlineWithHeaderInfo) {
                if (!$isInlineWithKeyIndent) {
                    if ($isInContextTableHeader) {
                    } else if ($isInContextTableDataRow) {
                        if ($hasNormalVSpace) {
                            $res = 1;
                            if (!$hasEqualAmountOfColumnsLikeHeader) {
                                $dataLine->Classification->isMergeCandidate = 1;
                            }
                        }
                        
                    }
                }
                
            }
        }
        
        if ($res === 0 && $isInContextTableDataRow ) {
            $this->tableHeaderInfo = null;
        }
        
        $dataLine->Classification->isPartOfTable = $res;
        
        
        return $res;
    }
    function shouldBeTableHeaderOrDataRow($dataLine) {
        $res = 0;
        
        $shouldBeTableDataRow = $this->shouldBeTableDataRow($dataLine);
        $shouldBeTableHeader = $this->shouldBeTableHeader($dataLine);
        
        $dataLine->Classification->isTableHeader = $shouldBeTableHeader;
        $dataLine->Classification->isTableDataRow = $shouldBeTableDataRow;
        
        if ($shouldBeTableHeader === 1 || $shouldBeTableDataRow === 1) {
            $res = 1;
        }
        
        return $res;
    }
    function classifyTable(DataLine $dataLine) {
        $tableDef = "";
        
        if ($dataLine->Classification->isTableHeader) {
            $tableDef = "T-HEADER";
        } else if ($dataLine->Classification->isTableDataRow) {
            $tableDef = "T-DATAROW";
        }
        
        return $tableDef;
    }
    function hasEqualAmountOfColumnsLikeHeader(DataLine $dataLine) {
        if ($this->tableHeaderInfo) {
            $headerStringsByColumn = $this->tableHeaderInfo->getStringsByColumns();
            
            $stringsByColumn = $dataLine->getStringsByColumns();
            
            $isInContextKeyValue = $this->isInContextKeyValue($dataLine);
            
            if ($isInContextKeyValue) {
                if (count($stringsByColumn) === count($headerStringsByColumn) - 1) {
                    return true;
                }
            } else {
                if (count($stringsByColumn) === count($headerStringsByColumn)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    function hasOnlyColumnsInlineWithHeaderInfo(DataLine $dataLine) {
        $stringsByColumn = $dataLine->getStringsByColumns();
        
        if ($this->tableHeaderInfo) {
            $headerStringsByColumn = $this->tableHeaderInfo->getStringsByColumns();
            
            foreach($stringsByColumn as $string_column_item) {
                $columnMatch = false;
                
                foreach($headerStringsByColumn as $key => $header_column_item) {
                    if (abs($string_column_item[0]->HPOS - $header_column_item[0]->HPOS) < 5) {
                        $columnMatch = true;
                    }
                    
                }
                
                if (!$columnMatch) {
                    return false;
                }
            }
            
            return true;
        }
        
        return false;
    }
    function hasLessColumnsThanHeader(DataLine $dataLine) {
        if ($this->tableHeaderInfo) {
            $headerStringsByColumn = $this->tableHeaderInfo->getStringsByColumns();
            $stringsByColumn = $dataLine->getStringsByColumns();
            
            if (count($stringsByColumn) < count($headerStringsByColumn)) {
                return true;
            }
        }
        
        return false;
    }
    function shouldBeKeyValue($dataLine) {
        // criteria
        $hasAtLeastTwoColumns = $this->hasAtLeastTwoColumns($dataLine);
        $hasAtLeastThreeColumns = $this->hasAtLeastThreeColumns($dataLine);
        $hasExactlyTwoColumns = $this->hasExactlyTwoColumns($dataLine);
        $hasExactlyOneColumns = $this->hasExactlyOneColumns($dataLine);
        $doesNotStartWith_Name = $this->doesNotStartWith_Name($dataLine);
        $isInlineWithOverallKeyValueIndent = $this->isInlineWithOverallKeyValueIndent($dataLine);
        $isInlineWithValueIndent = $this->isInlineWithValueIndent($dataLine);
        $shouldBeTable = $this->shouldBeTable($dataLine);
        $isInSuperContextTable= $this->isInSuperContextTable($dataLine);
        $hasKeyAndValueInlineWithIndent = $this->hasKeyAndValueInlineWithIndent($dataLine);

        
        // necessities
        $conditions_necessary = array();
        
        // sufficiencies
        
        $conditions_sufficient = array();
        array_push($conditions_sufficient, 
            ($doesNotStartWith_Name && !$isInSuperContextTable) && $hasExactlyTwoColumns && $hasKeyAndValueInlineWithIndent
            );
        array_push($conditions_sufficient,
            ($doesNotStartWith_Name && !$isInSuperContextTable) && $hasAtLeastThreeColumns && $hasKeyAndValueInlineWithIndent
            );
        array_push($conditions_sufficient,
            ($doesNotStartWith_Name && $isInSuperContextTable) && $hasExactlyTwoColumns && $hasKeyAndValueInlineWithIndent && !$shouldBeTable
            );
        
        
        $res = $this->verifyCriteria($conditions_necessary, $conditions_sufficient);
        
        $dataLine->Classification->isPartOfKeyValueList = $res;
        
        return $res;
    }
    function hasKeyAndValueInlineWithIndent(DataLine $dataLine) {
        $res = false;
        
        $keyValueContext = array();
        $keyValueContext_End= array();
        
        
        
        $currentLineStringsByColumn = $dataLine->getStringsByColumns();
        
        if ($this->keyValueInfo) {
            $keyValueStringsByColumn = $this->keyValueInfo->getStringsByColumns();
            
            $keyValueContext[0] = $keyValueStringsByColumn[0][0]->HPOS;
            $keyValueContext[1] = $keyValueStringsByColumn[1][0]->HPOS;
        } else {
            $grid_column = $this->Context->selectTargetColumn($dataLine);
            
            $keyValueContext = $grid_column->denseStartHPOS;
            $keyValueContext_End = $grid_column->denseEndHPOS;
            
            $keyValueContextNonColumnBased= $this->Context->denseStartHPOSNonColumnBased;
        }
        
        
        /*if (isset($dataLine->Strings[1])) {
            if ($dataLine->Strings[1]->CONTENT === "orders@leshop.ch") {
                print_r($grid_column->denseStartHPOS);
                print_r($grid_column->denseEndHPOS);
            }
        }*/
        if (isset($currentLineStringsByColumn[0])
            && isset($currentLineStringsByColumn[1]) && isset($keyValueContext[0]) && isset($keyValueContext[1])) {
                if (abs($keyValueContext[0] - $currentLineStringsByColumn[0][0]->HPOS) < 50
                    && abs($keyValueContext[1] - $currentLineStringsByColumn[1][0]->HPOS) < 50) {
                        $res = true;
                    } else {
                        $secondColumnEndHPOS = $currentLineStringsByColumn[1][count($currentLineStringsByColumn[1])-1]->HPOS + $currentLineStringsByColumn[1][count($currentLineStringsByColumn[1])-1]->WIDTH;
                        
                        if (abs($keyValueContext[0] - $currentLineStringsByColumn[0][0]->HPOS) < 50
                            && abs($keyValueContext[1] - $secondColumnEndHPOS) < 50) {
                                
                                $res = true;
                        } else {
                            if (isset($keyValueContext[0]) && isset($keyValueContext_End[0])) {
                                if (abs($keyValueContext[0] - $currentLineStringsByColumn[0][0]->HPOS) < 50
                                    && abs($keyValueContext_End[0] - $secondColumnEndHPOS) < 50) {
                                        
                                        $res = true;
                                    } else {
                                        //echo $dataLine . "\n";
                                        //echo $keyValueContext[0] . "/" . $keyValueContext[1] . "/" . $keyValueContext_End[0]. " vs " . $currentLineStringsByColumn[0][0]->HPOS . "/" . $currentLineStringsByColumn[1][0]->HPOS . "/" . $secondColumnEndHPOS. "\n";
                                    }
                            }
                            
                        }
                        
                    }
            }
            
            if (isset($keyValueContextNonColumnBased[0]) && isset($currentLineStringsByColumn[1])) {
            if (abs($keyValueContextNonColumnBased[0] - $currentLineStringsByColumn[1][0]->HPOS) < 50) {
                if (abs($keyValueContextNonColumnBased[0] - ($currentLineStringsByColumn[0][count($currentLineStringsByColumn[0])-1]->HPOS + $currentLineStringsByColumn[0][count($currentLineStringsByColumn[0])-1]->WIDTH)) < 50) {
                    $res = true;
                }
            }
        }
            
        return $res;
    }
    function isInlineWithValueIndent(DataLine $dataLine) {
        $res = false;
        
        $keyValueContext = array();
        
        $currentLineStringsByColumn = $dataLine->getStringsByColumns();
        
        if ($this->keyValueInfo) {
            $keyValueStringsByColumn = $this->keyValueInfo->getStringsByColumns();
            
            $keyValueContext[0] = $keyValueStringsByColumn[0][0]->HPOS;
            $keyValueContext[1] = $keyValueStringsByColumn[1][0]->HPOS;
        } else {
            $keyValueContext = $this->Context->denseStartHPOS;
        }
        
        if (isset($keyValueContext[1])) {
            if (abs($keyValueContext[1] - $currentLineStringsByColumn[0][0]->HPOS) < 50) {
                $res = true;
            }
        }
        
        return $res;
    }
    function isInlineWithKeyIndent(DataLine $dataLine) {
        $res = false;
        
        $keyValueContext = array();
        
        $currentLineStringsByColumn = $dataLine->getStringsByColumns();
        
        if ($this->keyValueInfo) {
            $keyValueStringsByColumn = $this->keyValueInfo->getStringsByColumns();
            
            $keyValueContext[0] = $keyValueStringsByColumn[0][0]->HPOS;
            $keyValueContext[1] = $keyValueStringsByColumn[1][0]->HPOS;
        } else {
            $keyValueContext = $this->Context->denseStartHPOS;
        }
        
        if (abs($keyValueContext[0] - $currentLineStringsByColumn[0][0]->HPOS) < 50) {
            $res = true;
        }
        return $res;
    }
    function isInlineWithOverallKeyValueIndent(DataLine $dataLine) {
        $res = false;
        
        $keyValueContext = array();
        
        $currentLineStringsByColumn = $dataLine->getStringsByColumns();
        
        if ($this->keyValueInfo) {
            $keyValueStringsByColumn = $this->keyValueInfo->getStringsByColumns();
            
            $keyValueContext[0] = $keyValueStringsByColumn[0][0]->HPOS;
            $keyValueContext[1] = $keyValueStringsByColumn[1][0]->HPOS;
        } else {
            $keyValueContext = $this->Context->denseStartHPOS;
            
            $keyValueContextNonColumnBased= $this->Context->denseStartHPOSNonColumnBased;
        }
        
        if (abs($keyValueContext[0] - $currentLineStringsByColumn[0][0]->HPOS) < 50) {
            $res = true;
        } else {
            if (isset($keyValueContext[1])) {
                if (abs($keyValueContext[1] - $currentLineStringsByColumn[0][0]->HPOS) < 50) {
                    $res = true;
                }
            }
            
        }
        
        if (isset($keyValueContextNonColumnBased[0]) && isset($currentLineStringsByColumn[1])) {
            if (abs($keyValueContextNonColumnBased[0] - $currentLineStringsByColumn[1][0]->HPOS) < 50) {
                $res = true;
            }
        }
        
        
        return $res;
    }
    function verifyCriteria(array $necessities, array $sufficiencies) {
        $res = 0;
        
        foreach($sufficiencies as $sufficient_item) {
            if ($sufficient_item) {
                $res = 1;
                
                break;
            }
        }
        
        if ($res === 0) {
            if (count($necessities) > 0) {
                $res = 1;
                
                foreach($necessities as $necessary_item) {
                    if (!$necessary_item) {
                        $res = 0;
                    }
                }
            }
            
        }
        
        return $res;
    }
}
class Classification_DataLine {
    var $name;
    
    var $Classification = null;
    
    var $isStandAlone;
    var $isPartOfKeyValueList;
    var $isPartOfFreeText;
    var $isHeader;
    var $hasDelimitedStrings;
    var $isPartOfTable;
    var $isTableHeader;
    var $isTableDataRow;
    var $tableDef;
    var $isMergeCandidate;
    
    function __toString() {
        $str = "";
        
        $str = $this->name;
        
        return $str;
    }
}
?>