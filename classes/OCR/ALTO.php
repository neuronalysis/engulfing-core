<?php
namespace ALTO;

class alto {
    var $namespaceDefinitions	= array(
        "xmlns" => "http://www.loc.gov/standards/alto/ns-v2#",
        "xmlns:xlink" => "http://www.w3.org/1999/xlink",
        "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
        "xsi:schemaLocation" => "http://www.loc.gov/standards/alto/ns-v2# http://www.loc.gov/standards/alto/alto-v2.0.xsd"
    );
    
    var $Description;
    var $Styles;
    var $Layout;
    
    function getPrintSpace($pageNumber) {
        return $this->Layout->Pages[$pageNumber]->PrintSpace;
    }
    
}
class Description {
    var $MeasurementUnit;
    var $OCRProcessing;
}
class OCRProcessing {
    var $ID;
    
    var $ocrProcessingStep;
}
class ocrProcessingStep {
    var $processingDateTime;
    var $processingSoftware;
}
class processingDateTime {
}
class processingSoftware {
    var $softwareCreator;
    var $softwareName;
    var $softwareVersion;
}
class softwareName {
}
class softwareVersion {
}
class softwareCreator {
}
class MeasurementUnit {
    //var $value;
}
class ParagraphStyle {
    var $ID;
    var $ALIGN;
    
    var $LEFT;
    var $RIGHT;
    var $FIRSTLINE;
    var $LINESPACE;
}
class TextStyle {
    var $ID;
    
    var $FONTSIZE;
    var $FONTFAMILY;
}
class Styles {
    var $TextStyles;
    var $ParagraphStyles;
}
class Layout {
    var $Pages;
}
class Page {
    var $ID;
    var $PHYSICAL_IMG_NR;
    var $HEIGHT;
    var $WIDTH;
    
    var $TopMargin;
    var $LeftMargin;
    var $RightMargin;
    var $BottomMargin;
    var $PrintSpace;
    
    function getStrings() {
        $strings = array();
        
        $printSpaceStrings = $this->PrintSpace->getStrings();
        $strings = array_merge($strings, $printSpaceStrings);
        
        if ($this->BottomMargin) {
            $marginBottomStrings = $this->BottomMargin->getStrings();
            $strings = array_merge($strings, $marginBottomStrings);
        }
        
        if ($this->TopMargin) {
            $marginTopStrings = $this->TopMargin->getStrings();
            $strings = array_merge($strings, $marginTopStrings);
        }
        
        usort($strings, array($this, "posCompare"));
        
        foreach($strings as $string_item) {
            unset($string_item->TextLine->Strings);
            unset($string_item->TextLine->SPs);
            unset($string_item->TextLine->TextBlock->TextLines);
        }
        
        return $strings;
    }
    function getTextLines() {
        $textlines = array();
        
        if ($this->PrintSpace->ComposedBlocks) {
            foreach($this->PrintSpace->ComposedBlocks as $ComposedBlock_item) {
                foreach($ComposedBlock_item->TextBlocks as $TextBlock_item) {
                    foreach($TextBlock_item->TextLines as $TextLine_item) {
                        $TextLine_item->TextBlock = $TextBlock_item;
                        array_push($textlines, $TextLine_item);
                    }
                }
            }
        }
        
        return $textlines;
    }
    function posCompare($a, $b) {
        if (abs($a->TextLine->VPOS - $b->TextLine->VPOS) < 5) {
            return $a->HPOS - $b->HPOS;
        } else {
            return $a->TextLine->VPOS - $b->TextLine->VPOS;
        }
    }
}
class Margin {
    var $HEIGHT;
    var $WIDTH;
    var $VPOS;
    var $HPOS;
    
    var $TextBlocks;
    var $ComposedBlocks;
    var $GraphicalElements;
    
    function getStrings() {
        $strings = array();
        
        if ($this->ComposedBlocks) {
            foreach($this->ComposedBlocks as $ComposedBlock_item) {
                $ComposedBlock_item->PrintSpace = $this;
                if ($ComposedBlock_item->ComposedBlocks) {
                    foreach($ComposedBlock_item->ComposedBlocks as $sub_ComposedBlock_item) {
                        $sub_ComposedBlock_item->ComposedBlock = $ComposedBlock_item;
                        if ($sub_ComposedBlock_item->ComposedBlocks) {
                            foreach($sub_ComposedBlock_item->ComposedBlocks as $subsub_ComposedBlock_item) {
                                $subsub_ComposedBlock_item->ComposedBlock = $sub_ComposedBlock_item;
                                foreach($subsub_ComposedBlock_item->TextBlocks as $TextBlock_item) {
                                    $TextBlock_item->ComposedBlock = $subsub_ComposedBlock_item;
                                    foreach($TextBlock_item->TextLines as $TextLine_item) {
                                        $TextLine_item->TextBlock = $TextBlock_item;
                                        foreach($TextLine_item->Strings as $String_item) {
                                            $String_item->TextLine = $TextLine_item;
                                            array_push($strings, $String_item);
                                        }
                                    }
                                }
                            }
                        }
                        foreach($sub_ComposedBlock_item->TextBlocks as $TextBlock_item) {
                            $TextBlock_item->ComposedBlock = $sub_ComposedBlock_item;
                            foreach($TextBlock_item->TextLines as $TextLine_item) {
                                $TextLine_item->TextBlock = $TextBlock_item;
                                foreach($TextLine_item->Strings as $String_item) {
                                    $String_item->TextLine = $TextLine_item;
                                    array_push($strings, $String_item);
                                }
                            }
                        }
                    }
                }
                if ($ComposedBlock_item->TextBlocks) {
                    foreach($ComposedBlock_item->TextBlocks as $TextBlock_item) {
                        $TextBlock_item->ComposedBlock = $ComposedBlock_item;
                        foreach($TextBlock_item->TextLines as $TextLine_item) {
                            $TextLine_item->TextBlock = $TextBlock_item;
                            foreach($TextLine_item->Strings as $String_item) {
                                $String_item->TextLine = $TextLine_item;
                                array_push($strings, $String_item);
                            }
                        }
                    }
                }
                
            }
        }
        
        if ($this->TextBlocks) {
            foreach($this->TextBlocks as $TextBlock_item) {
                $TextBlock_item->PrintSpace = $this;
                foreach($TextBlock_item->TextLines as $TextLine_item) {
                    $TextLine_item->TextBlock = $TextBlock_item;
                    foreach($TextLine_item->Strings as $String_item) {
                        $String_item->TextLine = $TextLine_item;
                        array_push($strings, $String_item);
                    }
                }
            }
        }
        
        return $strings;
    }
}

class TopMargin extends Margin {
    
}
class RightMargin extends Margin {
}
class LeftMargin extends Margin {
}
class HYP {
    var $CONTENT;
}
class BottomMargin extends Margin {
    
}

class PrintSpace {
    var $HEIGHT;
    var $WIDTH;
    var $VPOS;
    var $HPOS;
    
    var $ComposedBlocks;
    var $TextBlocks;
    
    var $Illustrations;
    var $GraphicalElements;
    
    function tidy() {
        $array = array();
        
        if ($this->ComposedBlocks) {
            foreach($this->ComposedBlocks as $composedBlockItem) {
                $blockID = explode("Block", $composedBlockItem->ID);
                
                if (isset($blockID[1])) $array[$blockID[1]] = $composedBlockItem;
            }
        }
        
        if ($this->Illustrations) {
            foreach($this->Illustrations as $illustrationItem) {
                $blockID = explode("Block", $illustrationItem->ID);
                
                if (isset($blockID[1])) $array[$blockID[1]] = $illustrationItem;
            }
        }
        
        if ($this->TextBlocks) {
            foreach($this->TextBlocks as $textBlockItem) {
                $blockID = explode("Block", $textBlockItem->ID);
                
                if (isset($blockID[1])) $array[$blockID[1]] = $textBlockItem;
            }
        }
        
        if ($this->GraphicalElements) {
            foreach($this->GraphicalElements as $graphicalElementItem) {
                $blockID = explode("Block", $graphicalElementItem->ID);
                
                if (isset($blockID[1])) $array[$blockID[1]] = $graphicalElementItem;
            }
        }
        
        ksort($array);
        
        return $array;
    }
    function getStrings() {
        $strings = array();
        
        if ($this->ComposedBlocks) {
            foreach($this->ComposedBlocks as $ComposedBlock_item) {
                $strings = array_merge($strings, $ComposedBlock_item->getStrings());
            }
        }
        
        if ($this->TextBlocks) {
            foreach($this->TextBlocks as $TextBlock_item) {
                $strings = array_merge($strings, $TextBlock_item->getStrings());
            }
        }
        
        /*if ($this->ComposedBlocks) {
            foreach($this->ComposedBlocks as $ComposedBlock_item) {
                $ComposedBlock_item->PrintSpace = $this;
                if ($ComposedBlock_item->ComposedBlocks) {
                    foreach($ComposedBlock_item->ComposedBlocks as $sub_ComposedBlock_item) {
                        $sub_ComposedBlock_item->ComposedBlock = $ComposedBlock_item;
                        if ($sub_ComposedBlock_item->ComposedBlocks) {
                            foreach($sub_ComposedBlock_item->ComposedBlocks as $subsub_ComposedBlock_item) {
                                $subsub_ComposedBlock_item->ComposedBlock = $sub_ComposedBlock_item;
                                foreach($subsub_ComposedBlock_item->TextBlocks as $TextBlock_item) {
                                    $TextBlock_item->ComposedBlock = $subsub_ComposedBlock_item;
                                    foreach($TextBlock_item->TextLines as $TextLine_item) {
                                        $TextLine_item->TextBlock = $TextBlock_item;
                                        foreach($TextLine_item->Strings as $String_item) {
                                            $String_item->TextLine = $TextLine_item;
                                            array_push($strings, $String_item);
                                        }
                                    }
                                }
                            }
                        }
                        foreach($sub_ComposedBlock_item->TextBlocks as $TextBlock_item) {
                            $TextBlock_item->ComposedBlock = $sub_ComposedBlock_item;
                            foreach($TextBlock_item->TextLines as $TextLine_item) {
                                $TextLine_item->TextBlock = $TextBlock_item;
                                foreach($TextLine_item->Strings as $String_item) {
                                    $String_item->TextLine = $TextLine_item;
                                    array_push($strings, $String_item);
                                }
                            }
                        }
                    }
                }
                if ($ComposedBlock_item->TextBlocks) {
                    foreach($ComposedBlock_item->TextBlocks as $TextBlock_item) {
                        $TextBlock_item->ComposedBlock = $ComposedBlock_item;
                        foreach($TextBlock_item->TextLines as $TextLine_item) {
                            $TextLine_item->TextBlock = $TextBlock_item;
                            foreach($TextLine_item->Strings as $String_item) {
                                $String_item->TextLine = $TextLine_item;
                                array_push($strings, $String_item);
                            }
                        }
                    }
                }
                
            }
        }
        
        if ($this->TextBlocks) {
            foreach($this->TextBlocks as $TextBlock_item) {
                $TextBlock_item->PrintSpace = $this;
                foreach($TextBlock_item->TextLines as $TextLine_item) {
                    $TextLine_item->TextBlock = $TextBlock_item;
                    foreach($TextLine_item->Strings as $String_item) {
                        $String_item->TextLine = $TextLine_item;
                        array_push($strings, $String_item);
                    }
                }
            }
        }*/
        
        return $strings;
    }
}
class ComposedBlock {
    var $ID;
    
    var $HEIGHT;
    var $WIDTH;
    var $VPOS;
    var $HPOS;
    
    var $TYPE;
    
    var $ComposedBlocks;
    var $TextBlocks;
    var $Illustrations;
    var $GraphicalElements;
    
    function getStrings() {
        $strings = array();
        
        if ($this->ComposedBlocks) {
            foreach($this->ComposedBlocks as $ComposedBlock_item) {
                $strings = array_merge($strings, $ComposedBlock_item->getStrings());
            }
        }
        
        if ($this->TextBlocks) {
            foreach($this->TextBlocks as $TextBlock_item) {
                $strings = array_merge($strings, $TextBlock_item->getStrings());
            }
        }
        
        return $strings;
    }
}
class Illustration {
    var $ID;
    
    var $HEIGHT;
    var $WIDTH;
    var $VPOS;
    var $HPOS;
}
class GraphicalElement {
    var $ID;
    
    var $HEIGHT;
    var $WIDTH;
    var $VPOS;
    var $HPOS;
}
class TextBlock {
    var $ID;
    
    var $HEIGHT;
    var $WIDTH;
    var $VPOS;
    var $HPOS;
    
    var $language;
    
    var $STYLEREFS;
    
    var $Shapes;
    
    var $TextLines;
    
    function getStrings() {
        $strings = array();
        
        if ($this->TextLines) {
            foreach($this->TextLines as $TextLine_item) {
                $strings = array_merge($strings, $TextLine_item->getStrings());
            }
        }
        
        return $strings;
    }
}
class TextLine {
    var $BASELINE;
    
    var $HEIGHT;
    var $WIDTH;
    var $VPOS;
    var $HPOS;
    
    var $STYLEREFS;
    
    var $Strings;
    var $SPs;
    var $HYPs;
    
    //TODO refact
    function tidy() {
        $array = array();
        
        if (isset($this->ALTOStrings)) {
            if ($this->ALTOStrings) {
                foreach($this->ALTOStrings as $stringItem) {
                    $array[$stringItem->HPOS] = $stringItem;
                }
            }
        }
        
        if (isset($this->SPs)) {
            if ($this->SPs) {
                foreach($this->SPs as $spItem) {
                    $array[$spItem->HPOS] = $spItem;
                }
            }
        }
        
        if (isset($this->HYPs)) {
            if ($this->HYPs) {
                foreach($this->HYPs as $hypItem) {
                    $array[10000000] = $hypItem;
                }
            }
        }
        
        
        ksort($array);
        
        return $array;
    }
    function getPreceedingTextLine() {
        $textblock = $this->TextBlock;
        
        $preceedingTextLine = null;
        
        foreach($textblock->TextLines as $TextLine_item) {
            if ($TextLine_item->VPOS < $this->VPOS) {
                if ($preceedingTextLine) {
                    if ($TextLine_item->VPOS > $preceedingTextLine->VPOS) {
                        $preceedingTextLine = $TextLine_item;
                    }
                } else {
                    $preceedingTextLine = $TextLine_item;
                }
            }
        }
        
        return $preceedingTextLine;
    }
    function getFollowingTextLine() {
        $textblock = $this->TextBlock;
        
        $followingTextLine = null;
        
        foreach($textblock->TextLines as $TextLine_item) {
            if ($TextLine_item->VPOS > $this->VPOS) {
                if ($followingTextLine) {
                    if ($TextLine_item->VPOS < $followingTextLine->VPOS) {
                        $followingTextLine= $TextLine_item;
                    }
                } else {
                    $followingTextLine= $TextLine_item;
                }
            }
        }
        
        return $followingTextLine;
    }
    
    function getStrings() {
        $strings = array();
        
        if ($this->Strings) {
            foreach($this->Strings as $String_item) {
                $String_item->TextLine = $this;
                array_push($strings, $String_item);
            }
        }
        
        return $strings;
    }
}
class Shape {
    var $Polygon;
}
class Polygon {
    var $POINTS;
}
class SP {
    var $WIDTH;
    var $VPOS;
    var $HPOS;
    var $HEIGHT;
    
}
class ALTOString {
    //var $STYLE;
    var $CONTENT;
    var $HEIGHT;
    var $WIDTH;
    var $VPOS;
    var $HPOS;
    
    var $SUBS_TYPE;
    var $SUBS_CONTENT;
    
    var $Word;
    
    function __toString() {
        return $this->CONTENT;
    }
}
?>
