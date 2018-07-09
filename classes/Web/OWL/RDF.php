<?php
namespace rdf;

class RDF {
    var $namespaceDefinitions	= array(
        "xml:base" => "http://www.semanticweb.org/christian/ontologies/2018/1/untitled-ontology-2",
        "xmlns:rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
        "xmlns:owl" => "http://www.w3.org/2002/07/owl#",
        "xmlns:xml" => "http://www.w3.org/XML/1998/namespace",
        "xmlns:xsd" => "http://www.w3.org/2001/XMLSchema#",
        "xmlns:rdfs" => "http://www.w3.org/2000/01/rdf-schema#"
    );
    
    var $Ontology;
    var $ObjectProperties = array();
    var $DatatypeProperties = array();
    var $owlClasses = array();
    var $NamedIndividuals = array();
    
    function getDatatypePropertyByName($name) {
        foreach($this->DatatypeProperties as $dt_property) {
            $dt_property_exp = explode("#", $dt_property->about);
            
            if ($dt_property_exp[1] === $name) {
                return $dt_property;
            }
        }
    }
}
class type {
    var $resource;
}

class resource {
    var $value;
}

class about {
    var $value;
}
?>