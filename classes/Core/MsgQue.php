<?php
//
// +------------------------------------------------------------------------+
// | Umbrella.net                                                           |
// +------------------------------------------------------------------------+
// | Copyright ::	Umbrella AG                                               |
// +------------------------------------------------------------------------+
// +------------------------------------------------------------------------+
//
// $Id: MsgQue.php $
//
/**
 * Message Queueing
 * 
 * @package Magellan
 * @author  Christian F�rst
 */
class MsgQue extends Magellan {
	var $dbconnection;
  /**
  *
  *
  */
	function MsgQue() {
	}
	
	function saveDossier($dossier) {
   	$sql="INSERT INTO DossiersDocRec (
       	dossierid,
       	dossierTitle,
       	docrecid,
       	docrecFirstName,
       	docrecLastName
    ) VALUES (
        '" . $dossier->id . "',
        '" . $dossier->title . "',
        '" . $dossier->docreceivers[0]->id . "',
        '" . $dossier->docreceivers[0]->firstname . "',
        '" . $dossier->docreceivers[0]->lastname . "')";
		$result = $this->dbconnection->query($sql);
	}
}
?>