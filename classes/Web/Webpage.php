<?php
class Webpage {
}
class HTML_Website extends Element {
	var $xpath_self = 'html';
	var $object_class_names = array('Body_Website');
}
class Body_Website extends Element {
	var $xpath_self = 'body';
	var $object_class_names = array('Div_Website', 'P_Website', 'Ul_Website', 'Table_Website', 'ColdFusion_Website', 'Aside_Website', 'Footer_Website', 'Span_Website', 'Input_Website', 'Style_Website', 'Link_Website', 'Script_Website', 'NoScript_Website', 'A_Website', 'H2_Website', 'Text_Website_Content', 'Comment_Website');
}
class Form_Website extends Element {
	var $xpath_self = 'form';
	var $suppress = true;
	var $object_class_names = array('Div_Website', 'Comment_Website', 'TR_Website', 'Script_Website', 'A_Website', 'FieldSet_Website', 'Button_Website', 'H2_Website', 'Label_Website', 'Table_Website', 'Text_Website_Content', 'Input_Website');
}
class Footer_Website extends Element {
	var $xpath_self = 'footer';
	var $posX;
	var $posY;
	var $object_class_names = array('Text_Website_Content', 'Div_Website');
}
class FieldSet_Website extends Element {
	var $xpath_self = 'fieldset';
	var $object_class_names = array('Text_Website_Content', 'Legend_Website', 'Input_Website', 'Label_Website', 'Select_Website');
}
class ColdFusion_If_Website extends Element {
	var $xpath_self = 'cfif';
	var $object_class_names = array('Text_Website_Content');
}
class ColdFusion_Website extends Element {
	var $xpath_self = 'cfinclude';
	var $object_class_names = array('Div_Website', 'Ul_Website', 'Table_Website', 'Aside_Website', 'Footer_Website', 'Span_Website', 'Input_Website', 'Style_Website', 'Link_Website', 'Script_Website', 'NoScript_Website', 'A_Website', 'H2_Website', 'Text_Website_Content', 'Comment_Website');
}
class Legend_Website extends Element {
	var $xpath_self = 'legend';
	var $object_class_names = array('Text_Website_Content');
}
class Font_Website extends Element {
	var $xpath_self = 'font';
	var $object_class_names = array('Text_Website_Content', 'B_Website', 'Break_Website', 'A_Website', 'Sup_Website');
}
class Option_Website extends Element {
	var $xpath_self = 'option';
	var $object_class_names = array('Text_Website_Content');
}
class Select_Website extends Element {
	var $xpath_self = 'select';
	var $object_class_names = array('Option_Website');
}
class Script_Website extends Element {
	var $xpath_self = 'script';
	var $suppress = true;
	var $object_class_names = array('Text_Website_Content', 'CData_Website');
}
class Section_Website extends Element {
	var $xpath_self = 'section';
	var $posX;
	var $posY;
	var $object_class_names = array('Text_Website_Content', 'Comment_Website', 'A_Website', 'Figure_Website', 'Span_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'H6_Website', 'Ul_Website', 'P_Website', 'Div_Website', 'Nav_Website');
}
class NoScript_Website extends Element {
	var $xpath_self = 'noscript';
	var $suppress = true;
	var $object_class_names = array('Image_Website', 'A_Website', 'Text_Website_Content', 'CData_Website', 'P_Website', 'Style_Website', 'Div_Website');
}
class Div_Website extends Element {
	var $xpath_self = 'div';
	var $posX;
	var $posY;

	var $object_class_names = array('P_Website', 'Cite_Website', 'Audio_Website', 'Small_Website', 'BlockQuote_Website', 'Center_Website', 'ColdFusion_If_Website', 'Hr_Website', 'Sup_Website', 'I_Website', 'U_Website', 'Li_Website', 'Select_Website', 'Time_Website', 'HGroup_Website', 'Aside_Website', 'Article_Website', 'B_Website', 'Canvas_Website', 'Em_Website', 'Style_Website', 'Ol_Website', 'Dl_Website', 'Footer_Website', 'NoScript_Website', 'Section_Website', 'Meta_Website', 'IFrame_Website', 'Nav_Website', 'Button_Website', 'Header_Website', 'Map_Website', 'Script_Website', 'Break_Website', 'Image_Website', 'Link_Website', 'Div_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'H6_Website', 'Ul_Website', 'Table_Website', 'Strong_Website', 'Form_Website', 'A_Website', 'Text_Website_Content', 'Comment_Website', 'Pre_Website', 'Span_Website', 'Label_Website', 'Input_Website');
}
class H1_Website extends Element {
	var $xpath_self = 'h1';
	var $object_class_names = array('Text_Website_Content', 'Span_Website', 'A_Website', 'Comment_Website', 'Div_Website');
}
class H2_Website extends Element {
	var $xpath_self = 'h2';
	var $object_class_names = array('Text_Website_Content', 'Span_Website', 'A_Website', 'Comment_Website', 'Div_Website');
}
class H3_Website extends Element {
	var $xpath_self = 'h3';
	var $object_class_names = array('Text_Website_Content', 'Cite_Website', 'Sup_Website', 'Label_Website', 'Span_Website', 'A_Website', 'Comment_Website', 'Div_Website');
}
class H4_Website extends Element {
	var $xpath_self = 'h4';
	var $object_class_names = array('Text_Website_Content', 'Span_Website', 'A_Website', 'Comment_Website', 'Div_Website');
}
class H5_Website extends Element {
	var $xpath_self = 'h5';
	var $object_class_names = array('Text_Website_Content', 'Break_Website', 'Span_Website', 'Sup_Website', 'A_Website', 'Comment_Website', 'Div_Website');
}
class H6_Website extends Element {
	var $xpath_self = 'h6';
	var $object_class_names = array('Text_Website_Content', 'Time_Website', 'Span_Website', 'A_Website', 'Comment_Website', 'Div_Website', 'Span_Website');
}
class BlockQuote_Website extends Element {
	var $xpath_self = 'blockquote';
	var $object_class_names = array('Text_Website_Content', 'P_Website', 'Div_Website');
}
class Meta_Website extends Element {
	var $xpath_self = 'meta';
	var $suppress = true;
	var $object_class_names = array('Text_Website_Content');
}
class Aside_Website extends Element {
	var $xpath_self = 'aside';
	var $object_class_names = array('Text_Website_Content', 'Div_Website', 'IFrame_Website', 'NoScript_Website', 'Section_Website', 'Style_Website', 'Comment_Website', 'Script_Website');
}
class Figure_Website extends Element {
	var $xpath_self = 'figure';
	var $object_class_names = array('Text_Website_Content', 'Div_Website', 'Span_Website', 'A_Website', 'FigCaption_Website');
}
class Time_Website extends Element {
	var $xpath_self = 'time';
	var $object_class_names = array('Text_Website_Content', 'Span_Website');
}
class FigCaption_Website extends Element {
	var $xpath_self = 'figcaption';
	var $object_class_names = array('Text_Website_Content', 'Span_Website', 'Div_Website', 'A_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'H6_Website');
}
class Article_Website extends Element {
	var $xpath_self = 'article';
	var $object_class_names = array('Text_Website_Content', 'Footer_Website', 'P_Website', 'Ul_Website', 'Div_Website', 'A_Website', 'Figure_Website', 'Header_Website', 'Span_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'H6_Website');
}
class Strong_Website extends Element {
	var $xpath_self = 'strong';
	var $object_class_names = array('Text_Website_Content', 'Strong_Website', 'Sup_Website', 'Cite_Website', 'Em_Website', 'Break_Website', 'Span_Website');
}
class HGroup_Website extends Element {
	var $xpath_self = 'hgroup';
	var $object_class_names = array('Text_Website_Content', 'Time_Website', 'Span_Website', 'A_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'H6_Website');
}
class Label_Website extends Element {
	var $xpath_self = 'label';
	var $object_class_names = array('Text_Website_Content', 'Span_Website', 'Input_Website');
}
class U_Website extends Element {
	var $xpath_self = 'u';
	var $object_class_names = array('A_Website', 'Text_Website_Content');
}
class Hr_Website extends Element {
	var $xpath_self = 'hr';
	var $object_class_names = array('Text_Website_Content');
}
class Be_Website extends Element {
	var $xpath_self = 'be';
	var $object_class_names = array('Text_Website_Content');
}
class Em_Website extends Element {
	var $xpath_self = 'em';
	var $object_class_names = array('Sup_Website', 'A_Website', 'Text_Website_Content');
}
class Input_Website extends Element {
	var $xpath_self = 'input';
	var $suppress = true;
	var $object_class_names = array('Text_Website_Content', 'Span_Website');
}
class Table_Website extends Element {
	var $xpath_self = 'table';
	var $object_class_names = array('TBody_Website', 'Form_Website', 'TFoot_Website', 'TR_Website', 'ColGroup_Website', 'Caption_Website', 'Text_Website_Content', 'THead_Website', 'Comment_Website');
}
class ColGroup_Website extends Element {
	var $xpath_self = 'colgroup';
	var $suppress = true;
	var $object_class_names = array('TBody_Website', 'Col_Website', 'Caption_Website', 'Text_Website_Content', 'THead_Website', 'Comment_Website');
}
class Col_Website extends Element {
	var $xpath_self = 'col';
	var $object_class_names = array('TBody_Website', 'Caption_Website', 'Text_Website_Content', 'THead_Website', 'Comment_Website');
}
class Map_Website extends Element {
	var $xpath_self = 'map';
	var $suppress = true;
	var $object_class_names = array('Area_Website');
}
class TBody_Website extends Element {
	var $xpath_self = 'tbody';
	var $object_class_names = array('TR_Website', 'Text_Website_Content', 'TH_Website', 'Comment_Website');
}
class THead_Website extends Element {
	var $xpath_self = 'thead';
	var $object_class_names = array('TR_Website');
}
class TFoot_Website extends Element {
	var $xpath_self = 'tfoot';
	var $object_class_names = array('TR_Website');
}
class TR_Website extends Element {
	var $xpath_self = 'tr';
	var $object_class_names = array('P_Website', 'Break_Website', 'Text_Website_Content', 'TD_Website', 'TH_Website');
}
class TH_Website extends Element {
	var $xpath_self = 'th';
	var $object_attribute_names = array('colspan');
	var $object_class_names = array('P_Website', 'Break_Website', 'B_Website', 'Strong_Website', 'Em_Website', 'Sup_Website', 'A_Website', 'Small_Website', 'Div_Website', 'Span_Website', 'Text_Website_Content', 'TD_Website', 'TH_Website');
}
class TD_Website extends Element {
	var $xpath_self = 'td';
	var $object_class_names = array('Text_Website_Content', 'Big_Website', 'Font_Website', 'Center_Website', 'Form_Website', 'B_Website', 'Map_Website', 'I_Website', 'Break_Website', 'Sup_Website', 'Cite_Website', 'Comment_Website', 'Small_Website', 'Ul_Website', 'Strong_Website', 'Image_Website', 'Table_Website', 'Div_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'H6_Website', 'A_Website', 'Input_Website', 'Span_Website', 'P_Website');
}
class Center_Website extends Element {
	var $xpath_self = 'center';
	var $object_class_names = array('Text_Website_Content', 'Break_Website', 'Table_Website');
}
class Break_Website extends Element {
	var $xpath_self = 'br';
	var $suppress = true;
	var $object_class_names = array('Text_Website_Content');
}
class Big_Website extends Element {
	var $xpath_self = 'big';
	var $object_class_names = array('Text_Website_Content', 'B_Website');
}
class Canvas_Website extends Element {
	var $xpath_self = 'canvas';
	var $object_class_names = array('Text_Website_Content');
}
class B_Website extends Element {
	var $xpath_self = 'b';
	var $object_class_names = array('Text_Website_Content', 'Span_Website', 'A_Website');
}
class Style_Website extends Element {
	var $xpath_self = 'style';

	var $css;

	//var $suppress = true;
	var $object_class_names = array('Text_Website_Content', 'CData_Website');
}
class Head_Website extends Element {
	var $xpath_self = 'head';
	var $object_class_names = array('Meta_Website', 'Style_Website', 'Script_Website');
}
class Header_Website extends Element {
	var $xpath_self = 'header';
	var $object_class_names = array('Text_Website_Content', 'P_Website', 'Figure_Website', 'Image_Website', 'HGroup_Website', 'A_Website', 'Div_Website', 'NoScript_Website', 'Comment_Website', 'Section_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'H6_Website');
}
class Nav_Website extends Element {
	var $xpath_self = 'nav';
	var $object_class_names = array('Text_Website_Content', 'Div_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'H6_Website', 'Ul_Website', 'A_Website');
}
class Button_Website extends Element {
	var $xpath_self = 'button';
	var $object_class_names = array('Span_Website', 'Text_Website_Content', 'Div_Website');
}
class Area_Website extends Element {
	var $xpath_self = 'area';
	var $object_class_names = array('Text_Website_Content');
}
class Caption_Website extends Element {
	var $xpath_self = 'caption';
	var $object_class_names = array('Text_Website_Content', 'I_Website', 'Span_Website');
}
class Dl_Website extends Element {
	var $xpath_self = 'dl';
	var $object_class_names = array('Dd_Website', 'Dt_Website', 'Text_Website_Content');
}
class Abbr_Website extends Element {
	var $xpath_self = 'abbr';
	var $object_class_names = array('Text_Website_Content');
}
class Source_Website extends Element {
	var $xpath_self = 'source';
	var $object_class_names = array('Text_Website_Content');
}
class Dt_Website extends Element {
	var $xpath_self = 'dt';
	var $object_class_names = array('Text_Website_Content');
}
class Audio_Website extends Element {
	var $xpath_self = 'audio';
	var $object_class_names = array('Text_Website_Content', 'Source_Website', 'Break_Website', 'A_Website');
}
class Dd_Website extends Element {
	var $xpath_self = 'dd';
	var $object_class_names = array('Span_Website', 'Break_Website', 'A_Website', 'B_Website', 'I_Website', 'Text_Website_Content', 'Ul_Website');
}
class Ol_Website extends Element {
	var $xpath_self = 'ol';
	var $object_class_names = array('Li_Website', 'Text_Website_Content');
}
class Ul_Website extends Element {
	var $xpath_self = 'ul';
	var $object_class_names = array('Li_Website', 'Span_Website', 'Comment_Website', 'Script_Website', 'Text_Website_Content');
}
class Li_Website extends Element {
	var $xpath_self = 'li';
	var $object_class_names = array('A_Website', 'Image_Website', 'Dl_Website', 'Figure_Website', 'Sup_Website', 'Table_Website', 'Dl_Website', 'B_Website', 'I_Website', 'Small_Website', 'Time_Website', 'Break_Website', 'Article_Website', 'Strong_Website', 'Ul_Website', 'Cite_Website', 'Input_Website', 'Span_Website', 'Text_Website_Content', 'Comment_Website', 'Div_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website');
}
class IFrame_Website extends Element {
	var $xpath_self = 'iframe';
	var $posX;
	var $posY;
	var $object_class_names = array('Text_Website_Content');
}
class Index_Website extends Element {
	var $xpath_self = 'index';
	var $object_class_names = array('Text_Website_Content', 'Go_Website');
}
class Go_Website extends Element {
	var $xpath_self = 'go';
	var $object_class_names = array('Text_Website_Content', 'Go_Website');
}
class P_Website extends Element {
	var $xpath_self = 'p';
	var $object_class_names = array('Comment_Website', 'Cite_Website', 'Em_Website', 'I_Website', 'Sup_Website', 'Small_Website', 'U_Website', 'Time_Website', 'Strong_Website', 'Image_Website', 'Index_Website', 'Meta_Website', 'B_Website', 'Break_Website', 'Text_Website_Content', 'Span_Website', 'A_Website');
}
class Span_Website extends Element {
	var $xpath_self = 'span';
	var $width;

	var $object_class_names = array('Break_Website', 'Abbr_Website', 'Small_Website', 'Image_Website', 'Figure_Website', 'I_Website', 'Sub_Website', 'Sup_Website', 'Cite_Website', 'B_Website', 'Meta_Website', 'Em_Website', 'Strong_Website', 'A_Website', 'Text_Website_Content', 'Comment_Website', 'Div_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'Span_Website');
}
class Sup_Website extends Element {
	var $xpath_self = 'sup';
	var $object_class_names = array('A_Website', 'Break_Website', 'Span_Website', 'I_Website', 'Text_Website_Content');
}
class Small_Website extends Element {
	var $xpath_self = 'small';
	var $object_class_names = array('Text_Website_Content', 'B_Website', 'Input_Website', 'A_Website', 'I_Website', 'Sup_Website', 'Span_Website');
}
class Sub_Website extends Element {
	var $xpath_self = 'sub';
	var $object_class_names = array('A_Website', 'Text_Website_Content');
}
class Cite_Website extends Element {
	var $xpath_self = 'cite';
	var $object_class_names = array('Text_Website_Content', 'Span_Website');
}
class I_Website extends Element {
	var $xpath_self = 'i';
	var $object_class_names = array('A_Website', 'B_Website', 'Strong_Website', 'Text_Website_Content');
}
class Image_Website extends Element {
	var $xpath_self = 'img';
	var $suppress = true;
	var $object_class_names = array('Text_Website_Content', 'Span_Website');
}
class Pre_Website extends Element {
	var $xpath_self = 'pre';
	var $object_class_names = array('Text_Website_Content', 'A_Website', 'Ul_Website');
}
class Link_Website extends Element {
	var $xpath_self = 'link';
	var $suppress = true;
	var $object_class_names = array('Text_Website_Content', 'Image_Website', 'Em_Website', 'Span_Website');
}
class A_Website extends Element {
	var $xpath_self = 'a';
	var $href;
	var $object_class_names = array('Text_Website_Content', 'Cite_Website', 'I_Website', 'Be_Website', 'Sup_Website', 'P_Website', 'Time_Website', 'Break_Website', 'Strong_Website', 'Div_Website', 'Image_Website', 'Em_Website', 'Span_Website', 'H1_Website', 'H2_Website', 'H3_Website', 'H4_Website', 'H5_Website', 'H6_Website');
}


class Website_Chapter {
	var $title;
	var $href;
	var $website_chapters;

	function __construct() {
	}
	function getSection() {

	}
}
class Website_Section {
	var $title;
	var $website_content;
	var $website_sections;

	function __construct() {
	}
}
class Website_Directory {
	var $website_chapters;

	function __construct() {

	}
}
class Website_Content {
	var $text;

	function __construct() {

	}
}
class Comment_Website extends Element {
	var $xpath_self = '#comment';
	var $suppress = true;
	//var $object_class_names = array('Div_ConvertedPdf_Page');
}
class CData_Website extends Element {
	var $xpath_self = '#cdata-section';
	//var $object_class_names = array('Div_ConvertedPdf_Page');
}
class Text_Website_Content extends Element {
	var $xpath_self = '#text';

	//var $object_class_names = array('Div_ConvertedPdf_Page');
}
?>
