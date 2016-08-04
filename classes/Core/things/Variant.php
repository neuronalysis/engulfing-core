<?php
class Variant extends Thing {
    var $dbtable			= "variants";
    var $id;
    function __construct() {
    }
    function get($id, $key = "id") {
        $this->$key = $id;
        $this->db->get($this, false);
    }
    function save() {
        $this->id = $this->db->save($this);
        $onto = new ONTO_Ontology();
			$onto->syncSave($this); return $this->id;
    }
    function delete() {
        $this->db->delete($this);
        $onto = new ONTO_Ontology();
			$onto->syncDelete($this);
    }
    function all() {
        $objects = $this->db->getRecords('Variant');
        return $objects;
    }
}
?>
