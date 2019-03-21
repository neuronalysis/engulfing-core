<?php
use ALTO\ALTOString;
use DataArray\DataArray;

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
    function bringStringsToLines($pageNumber) {
        $lines = array();
        
        $strings = $this->ALTO->Layout->Pages[$pageNumber]->getStrings();
        
        $lineCnt = 0;
        $currentLine = new DataLine();
        $currentLine->RowIndex = $lineCnt;
        $currentLine->ColumnIndex = 0;
        $currentLine->VPOS = $strings[0]->TextLine->VPOS;
        $currentLine->HPOS = $strings[0]->TextLine->HPOS;
        $currentLine->HEIGHT = $strings[0]->TextLine->HEIGHT;
        $currentLine->WIDTH = $strings[0]->TextLine->WIDTH;
        $currentLine->Strings = array();
        
        foreach($strings as $string_item) {
            if (abs($string_item->TextLine->VPOS - $currentLine->VPOS) < 20) {
                $string_item->TextLine->VPOS = $currentLine->VPOS;
                
                $currentLine->addString($string_item);
                $currentLine->HPOS = $currentLine->Strings[0]->TextLine->HPOS;
                
            } else {
                $currentLine->setStringColumns();
                
                array_push($lines, $currentLine);
                
                $lineCnt++;
                
                $currentLine = new DataLine();
                $currentLine->RowIndex = $lineCnt;
                $currentLine->ColumnIndex = 0;
                $currentLine->HPOS = $string_item->TextLine->HPOS;
                $currentLine->VPOS = $string_item->TextLine->VPOS;
                $currentLine->HEIGHT = $string_item->TextLine->HEIGHT;
                $currentLine->WIDTH= $string_item->TextLine->WIDTH;
                $currentLine->Strings= array($string_item);
            }
        }
        
        array_push($lines, $currentLine);
        
        return $lines;
    }
    function trimWhitespaces($lines) {
        foreach($lines as $line_item) {
            $line_item->Strings = $line_item->mergeStrings($line_item->Strings);
        }
        
        return $lines;
    }
    function convertPageToArray($pageNumber) {
        $gridconv = new DataGridConverter();
        
        $lines = $this->bringStringsToLines($pageNumber);
        
        $lines = $this->trimWhitespaces($lines);
        
        $grid = new DataGrid();
        $grid->bringLinesToGrid($lines, $pageNumber);
        
        $array = $gridconv->convertToDataArray($grid);
        
        return $array;
    }
    function convertToArray() {
        $array = new DataArray();
        
        foreach($this->ALTO->Layout->Pages as $key => $page_item) {
            if ($key <= 4) {
                try {
                    $pageArray = $this->convertPageToArray($key);
                } catch ( Exception $e ) {
                    $error = new Error ();
                    $error->details = $e->getMessage () . "\n" . $e->getFile() . " - " . $e->getLine();
                    
                    echo json_encode ( $error, JSON_PRETTY_PRINT );
                    exit ();
                }
                
                
                $array->mergeKeyValues($pageArray->getKeyValues());
                $array->mergeTables($pageArray->getTables());
                $array->mergeFreeTexts($pageArray->getFreeTexts());
                $array->mergeHeaders($pageArray->getHeaders());
            }
        }
         
        return $array;
    }
    function convertToDataGrids() {
        $array = array();
        
        foreach($this->ALTO->Layout->Pages as $key => $page_item) {
            if ($key <= 4) {
                 $pageDataGrid = $this->convertPageToDataGrid($key);
                
                array_push($array, $pageDataGrid);
            }
        }
        
        return $array;
    }
    function convertPageToDataGrid($pageNumber) {
        $gridconv = new DataGridConverter();
        
        $lines = $this->bringStringsToLines($pageNumber);
        
        $lines = $this->trimWhitespaces($lines);
        
        try {
            $grid = new DataGrid();
            $grid->bringLinesToGrid($lines, $pageNumber);
        } catch ( Exception $e ) {
            $error = new Error ();
            $error->details = $e->getMessage () . "\n" . $e->getFile() . " - " . $e->getLine();
            
            echo json_encode ( $error, JSON_PRETTY_PRINT );
            exit ();
        }
        
        return $grid;
    }
}
?>
