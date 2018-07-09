<?php
namespace owl;

class Ontology {
    var $about;
}

class ObjectProperty {
    var $about;
    
    var $domain;
}
class DatatypeProperty {
    var $about;
    var $domain;
    var $range;
    var $subPropertyOf;
}
class owlClass {
    var $about;
}
class NamedIndividual {
    var $about;
    var $type;
    
}

?>
