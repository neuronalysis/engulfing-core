<?php
class REST_TaskExecutor {

	function __construct() {
	}
	

}
class Processing {
	var $tasks = array();
	var $metrics;
	
	function __construct() {
		
	}
	function addTask($task) {
		$task->end();
		
		$duration = $task->end - $task->start;
		
		unset($task->start);
		unset($task->end);
		
		if ($duration > 0.005) {
			array_push($this->tasks, $task);
		}
		
	}
}
class Task {
	
	var $name;
	var $start;
	var $end;
	var $duration;
	var $tasks = array();
	
	function __construct($name = null) {
		$this->name = $name;
		$this->start = microtime(true);
	}
	function addTask($task) {
		$task->end();
		
		$duration = $task->end - $task->start;
		
		unset($task->start);
		unset($task->end);
		
		if ($duration > 0.005) {
			array_push($this->tasks, $task);
		}
		
	}
	function end() {
		$this->end = microtime(true);
		
		$this->duration = number_format($this->end - $this->start, 3) . " seconds";
	}
}

?>
