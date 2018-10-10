<?php
use DataArray\DataArray;
use DataArray\Table;
use DataArray\TableDataRow;
use DataArray\KeyValue;
use DataArray\TableDataCell;
use DataArray\Key;
use DataArray\Value;
use DataArray\FreeText;
use DataArray\Header;

class DataGridConverter extends Converter {
	function convertToDataArray(DataGrid $grid) {
	    $array = new DataArray();
	    
	    foreach($grid->DataGridRows as $rowIdx => $row_item) {
	        foreach($row_item->DataGridColumns as $colIdx => $column_item) {
	            $cell_lines = $column_item->DataLines;
	            
	            foreach($cell_lines as $key => $line_item) {
	                $stringsByColumns = $line_item->getStringsByColumns();
	                $ft_stringsByColumns = $line_item->getConcatenatedStringByColumns();
	                
	                if (isset($stringsByColumns[0])) {
	                    if ($line_item->Classification->name === "KEYVALUE") {
	                        if ($line_item->Classification->Classification) {
	                            if ($line_item->Classification->Classification->name=== "TABLE") {
	                                if ($line_item->Classification->Classification->Classification->name === "T-HEADER") {
	                                    $table = new Table();
	                                    $headerInfo = $ft_stringsByColumns;
	                                    
	                                    
	                                    
	                                    
	                                } else if ($line_item->Classification->Classification->Classification->name === "T-DATAROW") {
	                                    $row= new TableDataRow();
	                                    
	                                    foreach($ft_stringsByColumns as $key => $col_item) {
	                                        if (isset($headerInfo[$key+1])) {
	                                            $cell = new TableDataCell();
	                                            $cell->Key = $headerInfo[$key+1];
	                                            $cell->Value = $col_item;
	                                            
	                                            array_push($row->TableDataCells, $cell);
	                                        }
	                                        
	                                    }
	                                    
	                                    array_push($table->TableDataRows, $row);
	                                    
	                                    if (!isset($cell_lines[$key+1]->Classification->Classification)) {
	                                        $kv = new KeyValue();
	                                        $kv->Key = $headerInfo[0];
	                                        $kv->Value = $table;
	                                        
	                                        $array->addKeyValue($kv);
	                                    } else {
	                                        if ($cell_lines[$key+1]->Classification->Classification->name !== "TABLE") {
	                                            //$array[$headerInfo[0]] = $table;
	                                            $kv = new KeyValue();
	                                            $kv->Key = $headerInfo[0];
	                                            $kv->Value = $table;

	                                            $array->addKeyValue($kv);
	                                        }
	                                    }
	                                    
	                                }
	                            }
	                        } else {
	                            if ($line_item->Classification->hasDelimitedStrings) {
	                                foreach($line_item->getKeyValuesFromDelimitedStrings() as $kv) {
	                                    $array->addKeyValue($kv);
	                                }
	                            } else {
	                                $ft_stringsByColumns[0] = str_ireplace(":", "", $ft_stringsByColumns[0]);
	                                if (isset($ft_stringsByColumns[0]) && isset($ft_stringsByColumns[1])) {
	                                    $kv = new KeyValue();
	                                    $kv->Key = new Key();
	                                    foreach($stringsByColumns[0] as $string_item) {
	                                        unset($string_item->TextLine);
	                                    }
	                                    
	                                    $stringsByColumns[0][count($stringsByColumns[0])-1]->CONTENT = rtrim($stringsByColumns[0][count($stringsByColumns[0])-1]->CONTENT, ":");
	                                    
	                                    $kv->Key->Strings = $stringsByColumns[0];
	                                    
	                                    
	                                    $kv->Value = new Value();
	                                    foreach($stringsByColumns[1] as $string_item) {
	                                        unset($string_item->TextLine);
	                                    }
	                                    $kv->Value->Strings = $stringsByColumns[1];
	                                    
	                                    $array->addKeyValue($kv);
	                                }
	                            }
	                        }
	                        
	                        
	                    } else if ($line_item->Classification->name=== "TABLE") {
	                        if ($line_item->Classification->Classification->name === "T-HEADER") {
	                            //$table = array();
	                            $table= new Table();
	                            
	                            if ($line_item->Classification->hasDelimitedStrings) {
	                                $headerInfo = $ft_stringsByColumns;
	                                $headerInfo_delimited = $line_item->getConcatenatedStringByColumns(true);
	                            } else {
	                                $headerInfo = $ft_stringsByColumns;
	                            }
	                            
                                
                            } else if ($line_item->Classification->Classification->name === "T-DATAROW") {
                                //$row = array();
                                $row = new TableDataRow();
                                if (isset($headerInfo_delimited)) {
                                    $ft_stringsByColumns_delimited = $line_item->getConcatenatedStringByColumns(true, $headerInfo, $headerInfo_delimited);
                                    
                                    if (strtolower($ft_stringsByColumns_delimited[0]) === "name") {
                                        
                                        foreach($ft_stringsByColumns_delimited as $key => $col_item) {
                                            if (isset($headerInfo_delimited[$key+1])) {
                                                //$row[$headerInfo[$key+1]] = $col_item;
                                                $cell = new TableDataCell();
                                                $cell->Key = $headerInfo_delimited[$key+1];
                                                $cell->Value = $col_item;
                                                
                                                array_push($row->TableDataCells, $cell);
                                            }
                                        }
                                    } else {
                                        foreach($ft_stringsByColumns_delimited as $key => $col_item) {
                                            if (isset($headerInfo_delimited[$key])) {
                                                //$row[$headerInfo[$key]] = $col_item;
                                                $cell = new TableDataCell();
                                                $cell->Key = $headerInfo_delimited[$key];
                                                $cell->Value = $col_item;
                                                
                                                array_push($row->TableDataCells, $cell);
                                            }
                                        }
                                    }
                                } else {
                                    if (strtolower($ft_stringsByColumns[0]) === "name") {
                                        
                                        foreach($ft_stringsByColumns as $key => $col_item) {
                                            if (isset($headerInfo[$key+1])) {
                                                //$row[$headerInfo[$key+1]] = $col_item;
                                                $cell = new TableDataCell();
                                                $cell->Key = $headerInfo[$key+1];
                                                $cell->Value = $col_item;
                                                
                                                array_push($row->TableDataCells, $cell);
                                            }
                                        }
                                    } else {
                                        foreach($ft_stringsByColumns as $key => $col_item) {
                                            if (isset($headerInfo[$key])) {
                                                //$row[$headerInfo[$key]] = $col_item;
                                                $cell = new TableDataCell();
                                                $cell->Key = $headerInfo[$key];
                                                $cell->Value = $col_item;
                                                
                                                array_push($row->TableDataCells, $cell);
                                            }
                                        }
                                    }
                                }
                                
                                
                                array_push($table->TableDataRows, $row);
                                
                                if (!isset($cell_lines[$key+1]->Classification)) {
                                    //$array[$headerInfo[0]] = $table;
                                    $kv = new KeyValue();
                                    $kv->Key = $headerInfo[0];
                                    $kv->Value = $table;
                                    
                                    $array->addKeyValue($kv);
                                 } else {
                                    if ($cell_lines[$key+1]->Classification->name !== "TABLE") {
                                        //$array[$headerInfo[0]] = $table;
                                        $kv = new KeyValue();
                                        $kv->Key = $headerInfo[0];
                                        $kv->Value = $table;
                                        
                                        $array->addKeyValue($kv);
                                    }
                                }
                                
                            }
	                    } else if ($line_item->Classification->name === "FREETEXT") {
	                        if ($key > 0) {
	                            if (isset($cell_lines[$key-1])) {
	                                if ($cell_lines[$key-1]->Classification->name !== "FREETEXT") {
	                                    $freetext = new FreeText();
	                                    $freetext->Strings = $stringsByColumns[0];
	                                    
	                                    if (isset($cell_lines[$key+1])) {
	                                        if($cell_lines[$key+1]->Classification->name !== "FREETEXT") {
	                                            $array->addFreeText($freetext);
	                                        }
	                                    }
	                                } else {
	                                    $freetext->Strings = array_merge($freetext->Strings, $stringsByColumns[0]);
	                                    
	                                    if (isset($cell_lines[$key+1])) {
	                                        if($cell_lines[$key+1]->Classification->name !== "FREETEXT") {
	                                            $array->addFreeText($freetext);
	                                        }
	                                    }
	                                    
	                                }
	                            } else {
	                                $freetext = new FreeText();
	                                $freetext->Strings = $stringsByColumns[0];
	                                
	                                if (isset($cell_lines[$key+1])) {
	                                    if($cell_lines[$key+1]->Classification->name !== "FREETEXT") {
	                                        $array->addFreeText($freetext);
	                                    }
	                                }
	                            }
	                            
	                        } else {
	                            $freetext = new FreeText();
	                            $freetext->Strings = $stringsByColumns[0];
	                            
	                            if (isset($cell_lines[$key+1])) {
	                                if ($cell_lines[$key+1]->Classification->name !== "FREETEXT") {
	                                    $array->addFreeText($freetext);
	                                }
	                            }
	                        }
	                    } else if ($line_item->Classification->name === "ALLONE") {
	                        if ($line_item->Classification->hasDelimitedStrings) {
	                            foreach($line_item->getKeyValuesFromDelimitedStrings() as $key => $value) {
	                                $array[$key] = $value;
	                            }
	                        } else {
	                            if (isset($stringsByColumns[1])) {
	                                $array[$ft_stringsByColumns[0]] = $ft_stringsByColumns[1];
	                            }
	                        }
	                    } else if ($line_item->Classification->name === "HEADER") {
	                        if ($line_item->Classification->hasDelimitedStrings) {
	                            foreach($line_item->getKeyValuesFromDelimitedStrings() as $kv) {
	                                $array->addKeyValue($kv);
	                            }
	                        } else {
	                            $header = new Header();
	                            $header->Strings = $stringsByColumns[0];
	                            
	                            $array->addHeader($header);
	                        }
	                    }
	                }
	            }
	        }
	    }
	    
	    return $array;
	}
}
?>