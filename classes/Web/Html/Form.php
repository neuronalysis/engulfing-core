<?php
class Form {
	var $inputfields 		= array();
	var $buttons 			= array();
	var $showbuttons		= true;
	var $editable			= true;
	var $returnEventResult 	= "false";
	var $event			 	= null;
	var $enctype			= null;
	
	function Form() {
	}
	function addObjects($objects) {
		$this->objects = $objects;
	}
	function addListInput($fName) {
		$this->listinputfields->$fName = true;
	}
	function addButton($button) {
		array_push($this->buttons, $button);
	}
	function addInputField($inputfield) {
		global $engulfing, $language;
		
		if (!$inputfield->caption) {
			$inputfield->caption = $engulfing->trans->translate($inputfield->name, $engulfing->user->language);
		}
		if ($inputfield->mandatory == "true") $inputfield->caption .= "(*)";
		if ($this->Type == "search") {
			$inputfield->name = "search_" . $inputfield->name;
		} else {
			$inputfield->name = "obj_" . $inputfield->name;
		}
		
		array_push($this->inputfields, $inputfield);
	}
	function addInput($type, $name, $value = "", $entries = null, $caption = null, $mandatory = "false", $like = false, $editable = true, $rows = 6, $igrp = null) {
		global $engulfing, $language;
		
		$inputfield = new InputField();
		
		$inputfield->igrp = $igrp;
		$inputfield->Type = $type;
		$inputfield->name = $name;
		$inputfield->mandatory = $mandatory;
		if ($caption) {
			$inputfield->caption = $caption;
		} else {
			$inputfield->caption = $engulfing->trans->translate($inputfield->name, $engulfing->user->language);
		}
		if ($inputfield->mandatory == "true") $inputfield->caption .= "(*)";
		
		if ($this->Type == "search") {
			$inputfield->name = "search_" . $inputfield->name;
		} else {
			$inputfield->name = "obj_" . $inputfield->name;
		}
		$inputfield->value = $value;
		$inputfield->entries = $entries;
		
		$inputfield->like = $like;
		
		$inputfield->rows = $rows;
		$inputfield->editable = $editable;
		
		array_push($this->inputfields, $inputfield);
	}
	function addInputGroup($inputgroup) {
		$inputfield = new InputField();
		
		$inputfield->Type = "inputgroup";
		$inputfield->inputfields = $inputgroup->inputfields;
		
		array_push($this->inputfields, $inputfield);
	}
	function setInputsByObjectDBFields($obj) {
		$dbfields = $obj->db->tableInfo($obj->dbtable);
		
		for ($i=0; $i<count($dbfields); $i++) {
			if ( $dbfields[$i]['type'] == "blob" && $dbfields[$i]['len'] == -1) {
				$this->addInput("textarea", $dbfields[$i]['name'], $obj->$dbfields[$i]['name']);
			} else {
				$this->addInput("text", $dbfields[$i]['name'], $obj->$dbfields[$i]['name']);
			}
		}
	}
	function addSelect($select) {
		array_push($this->inputfields, $select);
	}
	function addMultiSelect($multiselect) {
		array_push($this->inputfields, $multiselect);
	}
	function addUpload($upload) {
		array_push($this->inputfields, $upload);
	}
	function addComboBox($combobox) {
		array_push($this->inputfields, $combobox);
	}
	function setInput($name, $key, $value) {
		for ($i=0; $i<count($this->inputfields); $i++) {
			if ($this->inputfields[$i]->name == "obj_" . $name) {
				$this->inputfields[$i]->$key = $value;
			}
		}
	}
	function setType($type) {
		$this->Type = $type;
	}
	function setForward($forward) {
		$this->forward = $forward;
	}
	function render() {
		global $engulfing, $language;
		
		$sys = new GSystem();
		
		if ($this->editable == false) $disabled = " disabled ";
		if ($this->enctype != null) $enctype = " enctype='" . $this->enctype . "'";
		
		$str = "";
		$str .= "<form name='" . $this->name . "' method='post' target='" . $this->target . "' action='" . $this->action . "'" . $enctype . ">";
		
		if ($this->enctype != null) {
			$str .= "<input type='hidden' name='MAX_FILE_SIZE' value='10000000'/>";
		}
		$str .= "<input type='hidden' name='event' value='" . $this->event . "'/>";
		$str .= "<input type='hidden' name='sortKey' value='" . $this->sortKey . "'/>";
		$str .= "<input type='hidden' name='sortDirection' value='" . $this->sortDirection . "'/>";
		$str .= "<input type='hidden' name='forward' value='" . $this->forward . "'/>";
		$str .= "<input type='hidden' name='returnEventResult' value='" . $this->returnEventResult . "'/>";
		$str .= "<input type='hidden' name='object' value='" . $this->object . "'/>";
		$str .= "<input type='hidden' name='viewName' value='" . $this->viewName . "'/>";
		$str .= "<input type='hidden' name='objectid' value='" . $this->objectid . "'/>";
		$str .= "<input type='hidden' name='UserID' value='" . $this->UserID . "'/>";
		$str .= "<input type='hidden' name='formtype' value='" . $this->Type . "'/>";
		for ($i=0; $i<count($this->inputfields); $i++) {
			if ($this->editable == true) {
				$disabled = " ";
				if (isset($this->inputfields[$i]->editable) && $this->inputfields[$i]->editable == false) {
					$disabled = " disabled ";
				}
			}
			
			if (!ereg("obj_", $this->inputfields[$i]->name)) $this->inputfields[$i]->name = "obj_" . $this->inputfields[$i]->name;
			if ($this->inputfields[$i]->Type == "hidden" || $this->editable == false) $str .= "<input type='hidden' name='". $this->inputfields[$i]->name . "' value='" . $this->inputfields[$i]->value . "'/>";
		}
		if ($this->Type == "list") {
			$str .= "<input type='hidden' name='listobjects'/>";
		}
		
		if ($this->filter) {
			$str .= $this->filter->render();
		} else if (count($this->filters) > 0) {
			for ($i=0; $i<count($this->filters); $i++) {
				$str .= $this->filters[$i]->render();
			}
		}
		if (count($this->inputfields) > 0) {
			$str .= "<table class='form'>";
			for ($i=0; $i<count($this->inputfields); $i++) {
				if ($this->inputfields[$i]->igrp) {
					$str .= "<tr height='22px'>";
					$str .= "<td colspan='2'>" . "<b>" . $this->inputfields[$i]->igrp->title . "</b>" . "<hr align='left' size='1' width='100%' />" . "</td>";
					$str .= "</tr>";
				}
				if ($this->inputfields[$i]->Type != "hidden") $str .= "<tr height='22px'>";
				switch ($this->inputfields[$i]->Type) {
					case "text":
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>" . "<td class='form_input'>" . "<input class='text' type='text' name='" . $this->inputfields[$i]->name . "' " . 'value="' . $this->inputfields[$i]->value . '"' . " " . $disabled . " mandatory='" . $this->inputfields[$i]->mandatory . "' onkeyup='" . $this->inputfields[$i]->onkeyup . "'/>" . "</td>";
						break;
					case "anrede":
						$anrede[0]->id	= 1;		$anrede[0]->Caption	= $engulfing->trans->translate("Herr", $engulfing->user->language);
						$anrede[1]->id	= 2;		$anrede[1]->Caption	= $engulfing->trans->translate("Frau", $engulfing->user->language);
						
						$sel_anrede = new Select();
						$sel_anrede->entries = $anrede;
						$sel_anrede->value = $this->inputfields[$i]->value;
						$sel_anrede->name = "obj_Anrede";
						$sel_anrede->entryCaption = "Caption";
						$sel_anrede->entryValue = "id";
						$sel_anrede->caption = "Anrede";
						
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>" . "<td class='form_input'>" . $sel_anrede->render() . "</td>";
						break;
					case "language":
						$languages[0]->id	= "de";		$languages[0]->Caption	= $engulfing->trans->translate("Deutsch", $engulfing->user->language);
						$languages[1]->id	= "en";		$languages[1]->Caption	= $engulfing->trans->translate("Englisch", $engulfing->user->language);
						
						$sel_anrede = new Select();
						$sel_anrede->entries = $languages;
						$sel_anrede->value = $this->inputfields[$i]->value;
						$sel_anrede->name = "obj_language";
						$sel_anrede->entryCaption = "Caption";
						$sel_anrede->entryValue = "id";
						$sel_anrede->caption = $engulfing->trans->translate("Sprache", $engulfing->user->language);
						
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>" . "<td class='form_input'>" . $sel_anrede->render() . "</td>";
						break;
					case "inputgroup":
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->inputfields[0]->caption . " / " . $this->inputfields[$i]->inputfields[1]->caption . "</td>" . "<td class='form_input'>" . "<input class='text_g1' type='text' size='6' name='" . $this->inputfields[$i]->inputfields[0]->name . "' value='" . $this->inputfields[$i]->inputfields[0]->value . "' " . $disabled . " mandatory='" . $this->inputfields[$i]->inputfields[0]->mandatory . "'/>" . "&nbsp;&nbsp;" . "<input class='text_g2' type='text' name='" . $this->inputfields[$i]->inputfields[1]->name . "' value='" . $this->inputfields[$i]->inputfields[1]->value . "' " . $disabled . " mandatory='" . $this->inputfields[$i]->inputfields[1]->mandatory . "'/>" . "</td>";
						break;
					case "password":
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>" . "<td>" . "<input class='text' type='password' name='" . $this->inputfields[$i]->name . "' value='" . $this->inputfields[$i]->value . "' " . $disabled . " mandatory='" . $this->inputfields[$i]->mandatory . "'/>" . "</td>";
						break;
					case "checkbox":
						if ($this->inputfields[$i]->value == "on") $checked = " checked ";
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>" . "<td>" . "<input type='" . $this->inputfields[$i]->Type . "' name='" . $this->inputfields[$i]->name . "' " . $checked . " " . $disabled . "/>" . "</td>";
						break;
					case "radio":
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>" . "<td>" . "<input type='radio' name='" . $this->inputfields[$i]->name . "' " . " " . $disabled . "/>" . "</td>";
						break;
					case "date":
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>" . "<td>" . "<input class='text' type='text' name='" . $this->inputfields[$i]->name . "' value='" . $sys->convertDateUStoCH($this->inputfields[$i]->value) . "' " . $disabled . "/>" . "</td>";
						break;
					case "combobox":
						if ($this->Type == "search") {
							$this->inputfields[$i]->name = str_replace("obj_", "obj_search_", $this->inputfields[$i]->name);
						}
						$this->inputfields[$i]->editable = $this->editable;
						$str .= "<td>" . $this->inputfields[$i]->caption . "</td>";
						$str .= "<td valign='top'>";
						
						$str .= $this->inputfields[$i]->render();
						
						$str .= "</td>";
						break;
					case "textarea":
						$str .= "<td class='form_caption' valign='top'>" . $this->inputfields[$i]->caption . "</td>" . "<td>" . "<textarea  class='text' rows='" . $this->inputfields[$i]->rows . "' name='" . $this->inputfields[$i]->name . "' cols='60' " . $disabled . ">" . $this->inputfields[$i]->value . "</textarea>" . "</td>";
						break;
					case "select": 
						$this->inputfields[$i]->editable = $this->editable;
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>";
						$str .= "<td>";
						
						$str .= $this->inputfields[$i]->render();
						
						$str .= "</td>";
						break;
					case "multiselect": 
						$this->inputfields[$i]->editable = $this->editable;
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>";
						$str .= "<td>";
						
						$str .= $this->inputfields[$i]->render();
						
						$str .= "</td>";
						break;
					case "upload": 
						$this->inputfields[$i]->editable = $this->editable;
						$str .= "<td class='form_caption'>" . $this->inputfields[$i]->caption . "</td>";
						$str .= "<td>";
						
						$str .= $this->inputfields[$i]->render();
						
						$str .= "</td>";
						break;
				}
				if ($this->inputfields[$i]->Type != "hidden") $str .= "</tr>";
			}
			$str .= "</table>";
		}
			
		
		if ($this->showbuttons == true) {
			$str .= "<table width='100%'>";
	
			$str .= "<tr>";
			if ($this->Type == "listandedit") {
				$str .= "<td class='buttons_left'>";
			} else {
				$str .= "<td class='buttons_left'></td><td class='buttons_right'>";
			}
		
			switch ($this->Type) {
				case "search":
					$str .= "<button type='button' name='search' onclick='searchForm(this.form);'>" . "Search" . "</button>";
					break;
				case "mail":
					$str .= "<button type='button' name='mail' onclick='mailForm(this.form);'>" . "Schicken" . "</button>";
					break;
				case "compositor":
					$str .= "<button type='button' name='compositor' onclick='orderComposition(this.form);'>" . "Konfiguration bestellen" . "</button>";
					break;
				case "issue":
					$str .= "<button type='button' name='save' onclick='saveForm(this.form);'>" . "Feedback abschicken" . "</button>";
					break;
				case "rma":
					$str .= "<button type='button' name='save' onclick='requestRMA(this.form);'>" . "RMA Anfrage senden" . "</button>";
					break;
				case "basket":
					$str .= "<button type='button' name='buy' onclick='viewBasket();'>" . "View" . "</button>";
					break;
				case "prepareorder":
					$str .= "<button type='button' name='prepareorder' onclick='prepareOrder();'>" . "Bestellung vorbereiten" . "</button>";
					break;
				case "previeworder":
					$str .= "<button type='button' name='previeworder' onclick='previewOrder();'>" . "Bestellungsvorschau" . "</button>";
					break;
				case "placeorder":
					$str .= "<button type='button' name='placeorder' onclick='placeOrder();'>" . "Bestellung abschicken" . "</button>";
					break;
				case "export":
					$str .= "<button type='button' name='exportdata' onclick='exportData();'>" . "Export ausf�hren" . "</button>";
					break;
				case "profile":
					$str .= "<button type='button' name='profile' onclick='sendProfile();'>" . "Profil/Anfrage speichern" . "</button>";
					break;
				case "signup":
					$str .= "<button type='button' name='signup' onclick='processSignup();'>" . $engulfing->trans->translate("confirm_registration", $language) . "</button>";
					break;
				case "signupfirst":
    			$str .= "<button class='login' type='button' name='signupfirst' onclick='processSignup();'>" . $engulfing->trans->translate("Senden", $language) . "</button>";
    			$str .= '&nbsp;&nbsp;&nbsp;<button class="login" onclick="' . "forms['login'].reset();" . '" id="submit_login">Reset</button>';
					break;
				case "signuppreview":
    			$str .= "<button class='login' type='button' name='signuppreview' onclick='registerSignup();'>" . $engulfing->trans->translate("Senden", $language) . "</button>";
    			$str .= '&nbsp;&nbsp;&nbsp;<button class="login" onclick="' . "forms['login'].reset();" . '" id="submit_login">Reset</button>';
					break;
				case "signupproperties":
    			$str .= "<button class='login' type='button' name='signupproperties' onclick='registerSignup();'>" . $engulfing->trans->translate("Senden", $language) . "</button>";
    			$str .= '&nbsp;&nbsp;&nbsp;<button class="login" onclick="' . "forms['login'].reset();" . '" id="submit_login">Reset</button>';
					break;
				case "newuser":
					$str .= "<button type='button' name='signup' onclick='createNewUser();'>" . "Benutzer hinzuf�gen" . "</button>";
					break;
				case "poll":
					$str .= "<button type='button' name='poll' onclick='sendPollChoice();'>" . "vote me" . "</button>";
					break;
				case "lostpassword":
					$str .= "<button type='button' name='lostpassword' onclick='resendLoginData();'>" . "LoginDaten an eMail Adresse senden." . "</button>";
					break;
				case "Usersave":
    			$str .= "<button class='login' type='button' name='Usersave' onclick='saveForm_User(this.form);'>" . $engulfing->trans->translate("Senden", $language) . "</button>";
    			$str .= '&nbsp;&nbsp;&nbsp;<button class="login" onclick="' . "forms['login'].reset();" . '" id="submit_login">Reset</button>';
					break;
				case "subscription":
    			$str .= "<button class='login' type='button' name='subscription' onclick='saveForm_Subscription(this.form);'>" . $engulfing->trans->translate("Senden", $language) . "</button>";
    			$str .= '&nbsp;&nbsp;&nbsp;<button class="login" onclick="' . "forms['login'].reset();" . '" id="submit_login">Reset</button>';
					break;
				case "single":
					$str .= "<button type='button' name='save' onclick='saveForm(this.form);'>" . "Save" . "</button>";
					break;
				case "process":
					$str .= "<button type='button' name='process' onclick='processData(this.form);'>" . "Process" . "</button>";
					break;
				case "generateApplication":
					$str .= "<button type='button' name='generateApplication' onclick='generateApplication(this.form);'>" . "Generate Application" . "</button>";
					break;
				default:
					if ($this->Type == "listandedit" || $this->Type = "treeview") {
						for ($i=0; $i<count($this->buttons); $i++) {
							if ($this->buttons[$i]->editable) 	{
								$editable = " enabled ";
							} else {
								$editable = " disabled ";
							}
							if ($this->buttons[$i]->caption) 	$caption = $this->buttons[$i]->caption;
							if ($this->buttons[$i]->name) 		$name = " name='" . "button_" . $this->buttons[$i]->name . "'";
							if ($this->buttons[$i]->onclick) 	$onclick = " onClick='" . $this->buttons[$i]->onclick . "'";
		    				
		    				$str .= "<button type='button' " . $name . " " . $editable . " " . $onclick . ">" . $sys->wellformXML($engulfing->trans->translate($caption, $language)) . "</button>&nbsp;";
						}
					} else {
		    			$str .= "<button class='login' type='button' name='save' onclick='saveForm(this.form);'>" . $engulfing->trans->translate("Senden", $language) . "</button>";
		    			$str .= '&nbsp;&nbsp;&nbsp;<button class="login" onclick="' . "forms['login'].reset();" . '" id="submit_login">Reset</button>';
					}
					break;
			}
		
			if ($this->Type == "listandedit") {
				$str .= "</td><td class='buttons_right'></td>";
			} else {
				$str .= "</td>";
			}
			$str .= "</tr>";
			$str .= "</table>";
		}
		if (get_class($this->result) == "error") {
			$str .= "<br/><br/><span class='errormsg'>" . $this->result->message . "</span>";
		}
		
		if ($this->table) $str .= $this->table->render();
		
		if ($this->rnav) {
			$this->rnav->style = $this->table->style;
			$str .= $this->rnav->render();
		}
		$str .= "</form>";
		
		return $str;
	}
}
class InputGroup {
	var $inputfields 	= array();
	function InputGroup() {
	}
	function addInput($type, $name, $value = "", $entries = null, $caption = null, $mandatory = "false", $like = false, $editable = true, $rows = 6, $igrp = null) {
		$inputfield = new InputField();
		
		$inputfield->igrp = $igrp;
		$inputfield->Type = $type;
		$inputfield->name = $name;
		$inputfield->mandatory = $mandatory;
		if ($caption) {
			$inputfield->caption = $caption;
		} else {
			$inputfield->caption = $inputfield->name;
		}
		if ($inputfield->mandatory == "true") $inputfield->caption .= "&nbsp;(*)";
		
		if ($this->Type == "search") {
			$inputfield->name = "search_" . $inputfield->name;
		} else {
			$inputfield->name = "obj_" . $inputfield->name;
		}
		$inputfield->value = $value;
		$inputfield->entries = $entries;
		
		$inputfield->like = $like;
		
		$inputfield->rows = $rows;
		$inputfield->editable = $editable;
		
		array_push($this->inputfields, $inputfield);
	}
}
class InputField {
	var $mandatory = "false";
	var $editable = true;
}
class Button {
	var $editable = true;
}
?>