<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-generated/classes/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/km/KM_Generated.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/Helper.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ORM.php");

include_once ("KM_Relations.php");
include_once ("Ontology.php");
include_once ("OntologyClass.php");
include_once ("OntologyProperty.php");
include_once ("OntologyRelationType.php");

class KM extends KM_Generated {
	use ORM;
	use Helper;
	
	var $Ontologies = array();
	var $lexicon;
	var $classes = array("Ontology", "OntologyClass", "OntologyProperty", "OntologyRelationType", "RelationOntologyClassOntologyClass", "RelationOntologyClassOntologyProperty", "Lexeme", "Owner");
	
	var $entities = '{}';
	
	
	function __construct() {
		//$this->lexicon = new Lexicon();
	}
	function getNews($topic) {
		if ($topic === "km") {
			$ontologies = $this->getOntologies();
			
			$news = array();
			
			foreach($ontologies as $ontology) {
				$new = new News();
				$new->publishedAt = $ontology->getCreatedAt();
				$new->title = '<a href="./km/ontologies/#' . $ontology->id . '">' . $ontology->name . '</a> created';
				//$new->content = "New OntologyClass " . $class->name . " was created by ";
				$creator = $this->getById("User", $ontology->getCreatedBy());
			
				$new->content = 'New Ontology ' . '<a href="./km/ontologies/#' . $ontology->id . '">' . $ontology->name . '</a>' . ' was created by ' . '<a href="./usermanagement/users/#' . $creator->id . '">' . $creator->name . '</a>';
			
				if ($new->publishedAt !== "0000-00-00 00:00:00") array_push($news, $new);
					
				$oclasses = $ontology->getOntologyClasses();
					
				foreach($oclasses as $class) {
					$new = new News();
					$new->publishedAt = $class->getCreatedAt();
					$new->title = '<a href="./km/ontologyclasses/#' . $class->id . '">' . $class->name . '</a> created';
					//$new->content = "New OntologyClass " . $class->name . " was created by ";
					$creator = $this->getById("User", $class->getCreatedBy());
						
					$new->content = 'New OntologyClass ' . '<a href="./km/ontologyclasses/#' . $class->id . '">' . $class->name . '</a>' . ' for Ontology ' . '<a href="./km/ontologies/#' . $ontology->id . '">' . $ontology->name . '</a>' . ' was created by ' . '<a href="./usermanagement/users/#' . $creator->id . '">' . $creator->name . '</a>';
						
					if ($new->publishedAt !== "0000-00-00 00:00:00") array_push($news, $new);
				}
					
			}
		} else if ($topic === "entities") {
			$ocentities = $this->getOntologyClassEntities();
				
			$news = array();
				
			foreach($ocentities as $entity) {
				$class = $entity->getOntologyClass();
				$entity->RelationOntologyClassOntologyPropertyEntities = $entity->getOntologyPropertyEntities();
				
				$new = new News();
				$new->publishedAt = $entity->getCreatedAt();
				$entityName = $entity->getName();
				
				
				if ($entityName) {
					$new->title = '<a href="./km/ontologyclasses/#' . $class->id . '/entities/#' . $entity->id . '">' . $entityName . '</a> created';
				} else {
					$new->title = '<a href="./km/ontologyclasses/#' . $class->id . '/entities/#' . $entity->id . '">' . $class->name . '-Entity' . '</a> created';
				}
				$creator = $this->getById("User", $class->getCreatedBy());
				
				if ($entityName) {
					$new->content = 'New Entity ' . '<a href="./km/ontologyclasses/#' . $class->id . '/entities/#' . $entity->id . '">' . $entityName . '</a>' . ' of OntologyClass ' . '<a href="./km/ontologyclasses/#' . $class->id . '">' . $class->name . '</a>' . ' was created by ' . '<a href="./usermanagement/users/#' . $creator->id . '">' . $creator->name . '</a>';
				} else {
					$new->content = 'New ' . '<a href="./km/ontologyclasses/#' . $class->id . '/entities/#' . $entity->id . '">' . 'Entity' . '</a>' . ' of OntologyClass ' . $class->name . ' was created by ' . '<a href="./usermanagement/users/#' . $creator->id . '">' . $creator->name . '</a>';
				}	
				
				if ($new->publishedAt !== "0000-00-00 00:00:00") array_push($news, $new);
				
			}
					
		}
		
		
		$this->sort_on_field($news, "publishedAt", "DESC");
	
		return $news;
	}
	function loadDomainClassesByOntology($ontology, $nested = false) {
		$desc = "";
		if (!file_exists("../engulfing/")) {
			$desc = "../";
			if (!file_exists($desc . "../engulfing/")) {
				$desc .= "../";
			}
		}
		
		if (file_exists($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/GEO/GEO.php")) {
			include_once ($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/GEO/GEO.php");
		}
		
		
		$allClasses = $this->getOntologyClassesByOntologyId($ontology->id);
		foreach($allClasses as $classItem) {
			//echo $classItem->name . "\n";
			if ($classItem->Ontology) {
				//echo "o-name: " . $classItem->Ontology->name . "\n";
				if (file_exists($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/" . $ontology->name . "/" . $ontology->name . ".php")) {
					include_once ($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/" . $ontology->name . "/" . $ontology->name . ".php");
				} else if (file_exists($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/" . $ontology->name . "/" . $classItem->name . ".php")) {
					include_once ($desc . "../engulfing/engulfing-generated/classes/" . $ontology->name . "/" . $classItem->name . "_Generated.php");
					include_once ($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/" . $ontology->name . "/" . $classItem->name . ".php");
				}
			} else {
				//echo "o-name: " . $ontology->name . "\n";
				if (file_exists($desc . "../engulfing/engulfing-generated/classes/" . $ontology->name . "/" . $ontology->name . "_Generated.php")) {
					include_once ($desc . "../engulfing/engulfing-generated/classes/" . $ontology->name . "/" . $ontology->name . "_Generated.php");
				}
				if (file_exists($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/" . $ontology->name . "/" . $ontology->name . ".php")) {
					include_once ($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/" . $ontology->name . "/" . $ontology->name . ".php");
				}
				
				
				if (file_exists($desc . "../engulfing/engulfing-generated/classes/" . $ontology->name . "/" . $classItem->name . "_Generated.php")) {
					include_once ($desc . "../engulfing/engulfing-generated/classes/" . $ontology->name . "/" . $classItem->name . "_Generated.php");
				}
				if (file_exists($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/" . $ontology->name . "/" . $classItem->name . ".php")) {
					include_once ($desc . "../engulfing/engulfing-extensions/classes/BusinessLogic/" . $ontology->name . "/" . $classItem->name . ".php");
				}
			}
		}
	}
	function getDataSummary() {
		$datasummary = new DataSummary();
		
		$ontologies = $this->getOntologies();
		$ontologyClasses = $this->getOntologyClasses();
		$ontologyProperties = $this->getOntologyProperties();
		$ontologyRelationTypes = $this->getOntologyRelationTypes();
		
		$datasummary->ontologies = count($ontologies);
		$datasummary->ontologyClasses = count($ontologyClasses);
		$datasummary->ontologyProperties = count($ontologyProperties);
		$datasummary->ontologyRelationTypes = count($ontologyRelationTypes);
		
		return $datasummary;
	}
	function getWikiDataSummary() {
		$datasummary = new DataSummary();
	
		/*$ontologyClasses = $this->getPersistedOntologyClasses();
		$ontologyClassEntities = $this->getPersistedOntologyClassEntities($ontologyClasses);
		
	
		$datasummary->ontologyClasses = count($ontologyClasses);
		$datasummary->ontologyClassEntities = $ontologyClassEntities;
		*/
		
		return $datasummary;
	}
	function getAccessSummary() {
		$accesssummary = new AccessSummary();
		
		$monitoring = new Monitoring();
		$requests = $monitoring->getRequests();
		
		
		$tmp_requests = array();
		
		foreach($requests as $request) {
			if (isset($tmp_requests[$request->refererUrl])) {
				$tmp_requests[$request->refererUrl]++;
			} else {
				$tmp_requests[$request->refererUrl] = 1;
			}
			
		}
		
		foreach($tmp_requests as $key => $value) {
			if ($key !== "") {
				$dest = new AccessDestination();
				$dest->url = $key;
				$dest->visits = $value;
				$dest->title = $accesssummary->getTitleByURL($dest->url);
				
				if ($dest->title !== "///") {
					array_push($accesssummary->AccessDestinations, $dest);
				}
			}
		}
		
		$this->sort_on_field($accesssummary->AccessDestinations, "visits", "DESC", "num");
		
		
		
		return $accesssummary;
	}
	function getNewsByOntologyName($ontologyName) {
		$ontology = $this->getOntologyByName($ontologyName);
		
		$classes = $ontology->getOntologyClasses();
	
		$news = array();
	
		foreach($classes as $class) {
			$new = new News();
			$new->publishedAt = $class->getCreatedAt();
			$new->title = '<a href="./km/ontologyclasses/#' . $class->id . '">' . $class->name . '</a> created';
			//$new->content = "New OntologyClass " . $class->name . " was created by ";
			$creator = $this->getById("User", $class->getCreatedBy());
				
			$new->content = 'New OntologyClass ' . '<a href="./km/ontologyclasses/#' . $class->id . '">' . $class->name . '</a>' . ' was created by ' . '<a href="./usermanagement/users/#' . $creator->id . '">' . $creator->name . '</a>';
				
			array_push($news, $new);
		}
	
		return $news;
	}
	function getOntologyClasses() {
		$oclasses = $this->getAllByName("OntologyClass", true);
		
		return $oclasses;
	}
	function getPersistedOntologyClasses() {
		$oclasses = $this->getByNamedFieldValues("OntologyClass", array("isPersistedConcrete"), array(true));
		
		return $oclasses;
	}
	function getPersistedOntologyClassEntities($oclasses) {
		$entities = array();
		
		foreach($oclasses as $class) {
			$result = $this->getAllCountByName($class->name);
			
			if ($result > 0) {
				$entities[$class->name]['count'] = $result;
				$entities[$class->name]['classID'] = $class->id;
			}
		}
		
		arsort($entities);
		
		return $entities;
	}
	function getOntologyClassesArrayForLookup() {
		$oclasses = $this->getOntologyClasses();
		
		$oclassesarray = array();
		
		foreach($oclasses as $item_oclass) {
			$oclassesarray[$item_oclass->name] = $item_oclass;
		}
		
		
		return $oclassesarray;
	}
	function getWordsByDocumentWordNames($docwordnames) {
		$rest = new REST();
		
		$request_list = "";
		
		$iter = new CachingIterator(new ArrayIterator($docwordnames));
		
		foreach($iter as $token_item) {
			$request_list .= "'" . $token_item . "'";
			
			if ($iter->hasNext()) $request_list .= ", ";
		}
		
		$fields = array(
				'names' => $request_list
		);
		
		$response = $rest->request("api/nlp/words", "POST", null, urlencode(strtolower(json_encode($fields))));
		
		$restTransformer = new REST_Transformer();
		$result = $restTransformer->deserialize_JSON($response, "Word");
		
		
		$filtered_words = array();
		
		if (isset($result)) {
			foreach($result as $word) {
				if ($word->Type == "" || $word->Type == "noun") {
					array_push($filtered_words, $word);
				}
			}
		}
		
		
		return $filtered_words;
	}
	function getWordsArrayForLookup() {
		$words = $this->getWords();
		
		$wordsarray = array();
		
		foreach($words as $item_word) {
			$wordsarray[$item_word->name] = $item_word;
		}
		
		return $wordsarray;
	}
	function getWordsArrayForLookupByDocumentWords($docwords) {
		$wordsarray = array();
	
		foreach($docwords as $item_word) {
			$wordsarray[$item_word->name] = $item_word;
		}
	
		return $wordsarray;
	}
	function getOntologyClassesArrayForLookupByDocumentOntologyClasses($docOntologyClasses) {
		$OntologyClassessarray = array();
		
		foreach($docOntologyClasses as $item_OntologyClass) {
			$OntologyClassessarray[$item_OntologyClass->name] = $item_OntologyClass;
		}
		
		return $OntologyClassessarray;
	}
	function getOntologyById($id) {
		$rest = new REST();
		$result = $rest->getById("Ontology", $id);
	
		return $result;
	}
	function getOntologyClassById($id) {
		$rest = new REST();
		$result = $rest->getById("OntologyClass", $id);
		
		return $result;
	}
	function getOntologyByName($name) {
		$objects = $this->getByNamedFieldValues("Ontology", array("name"), array($name));
		
		if (isset($objects[0])) return $objects[0];
	}
	function getOntologyPropertyByName($name) {
		$objects = $this->getByNamedFieldValues("OntologyProperty", array("name"), array($name));
	
		if (isset($objects[0])) return $objects[0];
	}
	function getOntologyRelationTypeByName($name) {
		$objects = $this->getByNamedFieldValues("OntologyRelationType", array("name"), array($name));
	
		if (isset($objects[0])) return $objects[0];
	}
	function getOntologyClassByName($name, $eager = false) {
		$objects = $this->getByNamedFieldValues("OntologyClass", array("name"), array($name), false, null, $eager, true);
		
		
		if (isset($objects[0])) {
			$objects[0]->setDataBaseConnections($this->databaseConnections);
			return $objects[0];
		}
	}
	function getOntologyClassEntityByName($name) {
		$rest = new REST();
		
		//echo $name . "\n";
		$opes = $this->getByNamedFieldValues("OntologyPropertyEntity", array("name"), array($name));
	
		//print_r($opes);
		
		$relocopes = $this->getByNamedFieldValues("RelationOntologyClassOntologyPropertyEntity", array("ontologyPropertyEntityID"), array($opes[0]->id));
		
		//print_r($relocopes[0]);
		
		$objects = $rest->getById("OntologyClassEntity", $relocopes[0]->ontologyClassEntityID);
		
		return $objects;
	}
	function getSimilarOntologyClassEntityByName($ontologyClass, $name, $requiredSimilarity = 80) {
		$oces = $ontologyClass->getOntologyClassEntities();
		foreach ($oces as $item_oce) {
			$item_oce_name = $item_oce->getName();
				
			similar_text($name,$item_oce_name,$percent);
				
			//echo "similarity-check: " . $name . "; " . $item_oce_name . "; " . $percent . "\n";
				
			if ($percent >= $requiredSimilarity) return $item_oce;
		}
		
		return null;
	}
	function getOntologies() {
		$ontologies = $this->getAllByName("Ontology", true);
		
		return $ontologies;
	}
	function getOntologyClassEntities() {
		$ocentities = $this->getAllByName("OntologyClassEntity", false, "createdAt", null, array("ontologyClassID"));
		
		foreach($ocentities as $entity) {
			$entity->OntologyClass = $this->getById("OntologyClass", $entity->ontologyClassID);
		}
		
		return $ocentities;
	}
	function getOntologyProperties() {
		$ontologyproperties = $this->getAllByName("OntologyProperty", true);
		
		return $ontologyproperties;
	}
	function getOntologyRelationTypes() {
		$ontologyrelationtypes = $this->getAllByName("OntologyRelationType", true);
		
		return $ontologyrelationtypes;
	}
	function getOntologiesByNameSpaces($namespaces) {
		$Ontologies = array();
		
		foreach ($namespaces as $namespace_item) {
			array_push($Ontologies, $this->getOntologyByNameSpace($namespace_item));
		}
		
		return $Ontologies;
	}
	function getOntologyClassesByOntologyId($ontologyID) {
		$objects = $this->getByNamedFieldValues("OntologyClass", array("ontologyID"), array($ontologyID));

		return $objects;
	}
	function getOntologyByNameSpace($namespace) {
		$ontology = $this->getOntologyByName($namespace);
		
		if (isset($ontology)) $ontology->OntologyClasses = $this->getOntologyClassesByOntologyId($ontology->id);
		
		/*
		
		$namespace_class_name = $namespace . "";
		$namespace_class = new $namespace_class_name;
		
		$restTransformer = new REST_Transformer();
		$cat_entities = $restTransformer->deserialize_JSON($namespace_class->entities, "Catalogue");
		
		for ($i=0; $i<count($namespace_class->classes); $i++) {
			$class_name = $namespace_class->classes[$i];
				
			$oclass = $this->loadOntologyClass(new $class_name);
				
			$entities_name = $this->pluralize(str_replace("_impl", "", strtolower($class_name)));
				
			if (isset($cat_entities->$entities_name)) $this->loadOntologyClassEntities($oclass, $cat_entities->$entities_name);
				
			$Ontology->addOntologyClass($oclass);
		}
		
		$this->loadOntologyRelations($Ontology->OntologyClasses);
		*/
		
		return $ontology;
	}
	function getNamedEntityByOntologyElementAndValue($Ontologyelement, $value, $propose = false) {
		if (get_class($Ontologyelement) == "OntologyRelationType") {
			$oclass_incoming = $Ontologyelement->Ontologyrelation->relation_ococ_incomingOntologyClass;
			$oclass_outgoing = $Ontologyelement->Ontologyrelation->relation_ococ_outgoingOntologyClass;
			
			foreach ($oclass_incoming->entities as $oclass_entity) {
				foreach($oclass_entity->OntologyClass_propertyentities as $oproperty_entity) {
					$oproperty_entity_value = $oproperty_entity->getValue();
					
					if ($oproperty_entity_value == $value) {
						return $oclass_entity;
					} else {
						similar_text($value, $oproperty_entity_value, $percent);
						//echo "pct-match between [" . $value . "] and [" . $oproperty_entity_value . "]: " . $percent . "\n";
						
						if ($percent > 80) {
							return $oclass_entity;
						} else {
							if (stripos($value, substr($oproperty_entity_value, 0, 5)) !== false) {
								//echo $oproperty_entity_value . "; " . stripos($value, substr($oproperty_entity_value, 0, 5)) . "; " . strlen($oproperty_entity_value) . "\n";
								
								similar_text(substr($value, stripos($value, substr($oproperty_entity_value, 0, 5)), strlen($oproperty_entity_value)), $oproperty_entity_value, $percent);
								//echo "pct-match between [" . substr($value, stripos($value, substr($oproperty_entity_value, 0, 5)), strlen($oproperty_entity_value)) . "] and [" . $oproperty_entity_value . "]: " . $percent . "\n";
								if ($percent > 80) {
									return $oclass_entity;
								}
							}
						}
					}
					
				}
				
			}
			
			if ($propose) {
				$oclass_entity_proposal = new OntologyClassEntity();
				$oclass_entity_proposal->OntologyClass = $oclass_incoming;
				
				$hasName = false;
				foreach($oclass_incoming->OntologyClass_properties as $property_item) {
					if ($property_item->name == "name") {
						$hasName = true;
					}
				}
				
				if ($hasName) {
					$oclass_entity_proposal->setOPEntity("name", "proposed value: " . $value);
					return $oclass_entity_proposal;
				}
			}
			
		}
		
		return null;
		
	}
	function loadOntologyClassEntities($ontologyClassName, $objects) {
		$entities = array();
		
		$oclass = $this->getOntologyClassByName($ontologyClassName);
		
		$entities = $oclass->getOntologyClassEntitiesByObjects($objects);
		
		/*foreach ($catalogue as $item) {
			$oc_entity = new OntologyClassEntity();
			$oc_entity->OntologyClass = $oclass;
			
			foreach ($item as $key => $value) {
				$oc_entity->setOPEntity($key, $value, array());
			}
			
			array_push($entities, $oc_entity);
		}*/
		
		return $entities;
	}
	function loadOntologyRelations($oclasses) {
		//Extensions
		$parents = array();
		$children = array();
		
		foreach($oclasses as $oclass) {
			$obj = new $oclass->name;
		
			$parent_class_name = get_parent_class($obj);
				
			if ($parent_class_name) {
				$or_ococ[$parent_class_name] = new RelationOntologyClassOntologyClass();
				$or_ococ[$parent_class_name]->relation_ococ_outgoingOntologyClass = $oclass;
			}
		}
		foreach($oclasses as $oclass) {
			$obj = new $oclass->name;
		
			if (isset($or_ococ[$oclass->name])) {
				$or_ococ[$oclass->name]->relation_ococ_incomingOntologyClass = $oclass;
			}
		}
		
		if (isset($or_ococ)) {
			foreach($or_ococ as $child_name => $relation) {
				if (isset($relation->relation_ococ_incomingOntologyClass) && isset($relation->relation_ococ_outgoingOntologyClass)) {
					$relation->setOntologyRelationType("extends", array());
					foreach($oclasses as $oclass) {
						if ($oclass->name == $relation->relation_ococ_outgoingOntologyClass->name) {
							//echo "pushing " . ": " . $oclass->name . " " . $relation->relation_ococ_Ontologyrelationtype->name . " " . $relation->relation_ococ_incomingOntologyClass->name . "outgoing pushed \n";
							//array_push($oclass->RelationOntologyClassOntologyClasses_outgoing, $relation);
						}
					}
				}
			}
		}
		
		
		//Inheritances
		$parents = array();
		$children = array();
		
		
		foreach($oclasses as $oclass) {
			$obj = new $oclass->name;
		
			$parent_class_name = get_parent_class($obj);
				
			if ($parent_class_name) {
				$or_ococ_inherit[$parent_class_name] = new RelationOntologyClassOntologyClass();
				$or_ococ_inherit[$parent_class_name]->relation_ococ_incomingOntologyClass = $oclass;
			}
		}
		foreach($oclasses as $oclass) {
			$obj = new $oclass->name;
		
			if (isset($or_ococ_inherit[$oclass->name])) {
				$or_ococ_inherit[$oclass->name]->relation_ococ_outgoingOntologyClass = $oclass;
			}
		}
		
		if (isset($or_ococ_inherit)) {
			foreach($or_ococ_inherit as $parent_name => $relation) {
				if (isset($relation->relation_ococ_incomingOntologyClass) && isset($relation->relation_ococ_outgoingOntologyClass)) {
					$relation->setOntologyRelationType("extendedBy", array());
					foreach($oclasses as $oclass) {
						if ($oclass->name == $relation->relation_ococ_outgoingOntologyClass->name) {
							//echo "pushing " . ": " . $oclass->name . " " . $relation->relation_ococ_Ontologyrelationtype->name . " " . $relation->relation_ococ_incomingOntologyClass->name . "outgoing pushed \n";
							array_push($oclass->RelationOntologyClassOntologyClasses, $relation);
						}
					}
				}
			}
		}
		
		//HasOneToOnes
		/*
		foreach($oclasses as $oclass) {
			$class_name = $oclass->name;
			
			$object = new $class_name;
			
			$class_vars = get_object_vars($object);
			
			foreach($class_vars as $key => $value) {
				if (class_exists(strtolower($key . ""))) {
					$or_ococ_hasone[strtolower($key . "")] = new RelationOntologyClassOntologyClass();
					$or_ococ_hasone[strtolower($key . "")]->relation_ococ_outgoingOntologyClass = $oclass;
					
					if (isset($this->lexicon->Words[$key])) {
						$words = $this->lexicon->Words[$key];
							
						$or_ococ_hasone[strtolower($key . "")]->setOntologyRelationType("hasOne", $words);
					}
				}
			}
			
			if (isset($or_ococ_hasone[strtolower($class_name)])) {
				$or_ococ_hasone[strtolower($class_name)]->relation_ococ_incomingOntologyClass = $oclass;
				
				array_push($oclass->RelationOntologyClassOntologyClasses, $or_ococ_hasone[strtolower($class_name)]);
			}
			
		}
		*/
	}
	function loadOntologyClass($class) {
		$class_name = get_class($class);
		
		$words = $this->lexicon->Words;
		$identifiers = $this->lexicon->identifiers;
		$dates = $this->lexicon->dates;
		
		$oclass = new OntologyClass();
		$oclass->name = $class_name;
		
		$class_vars = get_object_vars($class);
		
		foreach($class_vars as $key => $value) {
			if (!class_exists($key . "")) {
				//print_r($words[$key]);
				if (in_array($key, $identifiers, TRUE)) {
					$oclass->setOntologyProperty($key, $words[$key], true);
				}
				if (in_array($key, $dates, TRUE)) {
					$oclass->setOntologyProperty($key, $words[$key], false, true);
				}
				if (isset($words[$key])) {
					if (!in_array($key, $identifiers, TRUE) && !in_array($key, $dates, TRUE) && !in_array($key, array("dates", "words", "identifiers")) && $words[$key] != NULL) {
						$oclass->setOntologyProperty($key, $words[$key]);
					}
				}
				
			} else {
				//echo $key . "\n";
			}
		}
		
		return $oclass;
	}
	function getOntology_StructuredProducts() {
		$Ontology = new Ontology("StructuredProducts");
	
		$oclass_sec = new OntologyClass("Security");
			$oclass_sec->setOntologyIdentifier("security_isin", array("ISIN", "ISIN Code"));
			$oclass_sec->setOntologyIdentifier("security_isin", array("ISIN"), "de");
			$oclass_sec->setValidation("security_isin", "^[a-zA-Z]{2}[0-9A-Z]{10}$^");
			$oclass_sec->setOntologyProperty("security_symbol", array("Symbol", "SIX Symbol", "Telekurs Symbol", "Telekurs Ticker", "SIX Trading Symbol"));
			$oclass_sec->setOntologyProperty("security_symbol", array("Symbol", "SIX Symbol", "Telekurs Symbol", "Telekurs Ticker", "TK Symbol", "Ticker-Symbol"), "de");
			$oclass_sec->setOntologyProperty("security_valor", array("Valor", "Swiss Sec. No.", "Swiss Security Number", "Swiss Security No.", "Valoren"));
			$oclass_sec->setOntologyProperty("security_valor", array("Valorennummer", "CH - Valorennummer", "CH-Valorennummer"), "de");
			$oclass_sec->setOntologyProperty("security_tradedcurrency", array("Traded Currency"));
			$oclass_sec->setOntologyProperty("security_tradedcurrency", array("Handelswährung"), "de");
			
		$oclass_fi = new OntologyClass("FinancialInstrument");
			$oclass_fi->setOntologyProperty("financialinstrument_wkn", array("WKN"));
			$oclass_fi->setOntologyProperty("financialinstrument_issuer", array("Issuer"));
			$oclass_fi->setOntologyProperty("financialinstrument_issuer", array("Emittentin", "Emittent"), "de");
			$oclass_fi->setOntologyProperty("financialinstrument_termsheet", array());
			
			$oclass_sec->setRelatedClass($oclass_fi, "extendedBy");
			$oclass_fi->setRelatedClass($oclass_sec, "extends");
		
		$oclass_stp = new OntologyClass("StructuredProduct");
			$oclass_stp->setOntologyProperty("structuredproduct_category", array("Product Category", "product type", "EUSIPA Code", "SVSP Product Type", "SSPA Product Type"), "en", true);
			$oclass_stp->setOntologyProperty("structuredproduct_category", array("Produktetyp", "Produkttyp", "SVSP Kategorie", "Art", "SVSP Produkttyp"), "de", true);
			$oclass_stp->setOntologyDate("structuredproduct_pricingdate", array("Pricing Date"));
			$oclass_stp->setOntologyDate("structuredproduct_expirationdate", array("Expiration Date"));
			$oclass_stp->setOntologyDate("structuredproduct_firsttradingdate", array("First Exchange Trading Date", "First SIX Trading Date (anticipated)", "First SIX Trading Date", "Issue/Payment Date", "Listing Date"));
			$oclass_stp->setOntologyDate("structuredproduct_firsttradingdate", array("Liberierung", "Erster Handelstag"), "de");
			$oclass_stp->setOntologyDate("structuredproduct_lasttradingdate", array("Last Trading Day/Time", "Last Trading Day", "Last trading day", "Last Trading Date", "Last Trading Date and Time"));
			$oclass_stp->setOntologyDate("structuredproduct_lasttradingdate", array("Letzte/r Handelstag/-zeit", "Letzter Handelstag"), "de");
			$oclass_stp->setOntologyDate("structuredproduct_maturitydate", array("Maturity Date", "Redemption Date / Maturity Date"));
			$oclass_stp->setOntologyDate("structuredproduct_maturitydate", array("Verfall"), "de");
			$oclass_stp->setOntologyProperty("structuredproduct_daystomaturity", array("Laufzeit"), "de");
			$oclass_stp->setOntologyProperty("structuredproduct_issuesize", array("Issue Size"));
			$oclass_stp->setOntologyProperty("structuredproduct_issuesize", array("Emissionsvolumen"), "de");
			$oclass_stp->setOntologyProperty("structuredproduct_issueprice", array("Issue Price", "Issue price"));
			$oclass_stp->setOntologyProperty("structuredproduct_issueprice", array("Ausgabepreis"), "de");
			$oclass_stp->setOntologyDate("structuredproduct_issuedate", array("Issue Date", "Initial Payment Date (Issue Date)", "Issue/Payment Date", "Issue Date / Payment Date"));
			$oclass_stp->setOntologyDate("structuredproduct_issuedate", array("Emissionstag"), "de");
			$oclass_stp->setOntologyDate("structuredproduct_paymentdate", array("Payment Date"));
			$oclass_stp->setOntologyProperty("structuredproduct_denomination", array("Denomination", "Nominal", "Specified Denomination / Nominal", "Nominal Amount"));
			$oclass_stp->setOntologyProperty("structuredproduct_denomination", array("Nennwert"), "de");
			$oclass_stp->setOntologyProperty("structuredproduct_ratio", array("Ratio"), "en");
			$oclass_stp->setOntologyProperty("structuredproduct_quanto", array("Währungsgesichert (Quanto)"), "de");
			$oclass_stp->setOntologyProperty("structuredproduct_currency", array("Settlement Currency"));
			$oclass_stp->setOntologyProperty("structuredproduct_currency", array("Auszahlungswährung"), "de");
			$oclass_stp->setOntologyProperty("structuredproduct_managementfee", array("Management Fee (MF)", "Management Fee p.a."));
			$oclass_stp->setOntologyProperty("structuredproduct_participation", array("Partizipation"), "de");
			$oclass_stp->setOntologyProperty("structuredproduct_deed", array("Form of deed"));
			$oclass_stp->setOntologyProperty("structuredproduct_losspotential", array("Loss Potential"));
			
			$oclass_fi->setRelatedClass($oclass_stp, "extendedBy");
			$oclass_stp->setRelatedClass($oclass_fi, "extends");
		
			$oclass_stp->inherit();
		
			$oclass_stp->setMandatories(array("structuredproduct_isin", "structuredproduct_exchange", "structuredproduct_termsheet"));
			
		$oclass_exchange = new OntologyClass("Exchange");
			$oclass_exchange->setOntologyIdentifier("exchange_mic", array());
			$oclass_exchange->setOntologyProperty("exchange_name", array());
		
			$oc_entity_exchange_1 = new OntologyClassEntity();
				$oc_entity_exchange_1->OntologyClass = $oclass_exchange;
				$oc_entity_exchange_1->setOPEntity("exchange_mic", "XSWX", array());
				$oc_entity_exchange_1->setOPEntity("exchange_name", "SIX Swiss Exchange", array("SIX Swiss Exchange"));
				array_push($oclass_exchange->entities, $oc_entity_exchange_1);
		
			$oc_entity_exchange_2 = new OntologyClassEntity();
				$oc_entity_exchange_2->OntologyClass = $oclass_exchange;
				$oc_entity_exchange_2->setOPEntity("exchange_mic", "XQMH", array());
				$oc_entity_exchange_2->setOPEntity("exchange_name", "SIX Structured Products", array("SIX Swiss Exchange AG; traded on Scoach Schweiz AG", "SIX Swiss Exchange listing will be applied for", "Listing on SIX Swiss Exchange, will be applied for", "SIX Swiss Exchange; traded on Scoach Schweiz AG", "SIX Swiss Exchange Ltd"));
				$oc_entity_exchange_2->setOPEntity("exchange_name", "SIX Structured Products", array("SIX Swiss Exchange AG; gehandelt an SIX Structured Products Exchange AG", "Wird an der SIX Swiss Exchange beantragt", "SIX Swiss Exchange Ltd", "Wird an der SIX Swiss Exchange beantragt"), "de");
				array_push($oclass_exchange->entities, $oc_entity_exchange_2);
				
				$Ontology->addOntologyClass($oclass_exchange);

		
		$oclass_fi->setRelatedClass($oclass_exchange, "hasOne", array("Listing"));
		
		$oclass_underlying = new OntologyClass("Underlying");
			$oclass_underlying->setOntologyIdentifier("underlying_name", array("Underlying"));
		
		$oclass_fi->setRelatedClass($oclass_underlying, "hasOne", array("Underlying Information", "UNDERLYING"));
				
		$oclass_doc = new OntologyClass("Termsheet");
			$oclass_doc->setOntologyProperty("termsheet_status", array());
			
			$oc_entity_doc = new OntologyClassEntity();
				$oc_entity_doc->OntologyClass = $oclass_doc;
				$oc_entity_doc->setOPEntity("termsheet_status", "Final", array("Final Termsheet", "Final Terms", "Termsheet"));
				array_push($oclass_doc->entities, $oc_entity_doc);
		
		$Ontology->addOntologyClass($oclass_doc);
		
		$oclass_fi->setRelatedClass($oclass_doc, "hasOne");
		
		$Ontology->addOntologyClass($oclass_stp);
		$Ontology->addOntologyClass($oclass_fi);
		$Ontology->addOntologyClass($oclass_sec);
		
		return $Ontology;
	}
	function getOntology_NLP() {
		$Ontology = new Ontology();
		$Ontology->name = "NLP";
		
		$Ontology_class = new OntologyClass();
		$Ontology_class->Ontology = $Ontology;
		$Ontology_class->name = "Word";
		$Ontology_class->setOntologyIdentifier("name", array("Name"));
		
		array_push($Ontology->OntologyClasses, $Ontology_class);
	
		return $Ontology;
	}
	
}
?>