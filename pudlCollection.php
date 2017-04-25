<?php


require_once('pudlObject.php');



class		pudlCollection
	extends	pudlObject {




	////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($classname) {
		$this->classname = $classname;
	}




	////////////////////////////////////////////////////////////////////////////
	//NAME OF CLASS WE'RE COLLECTING
	////////////////////////////////////////////////////////////////////////////
	public function classname() {
		return $this->classname;
	}




	////////////////////////////////////////////////////////////////////////////
	//FORWARD METHOD CALL TO ALL OBJECTS WITHIN COLLECTION
	//TODO: single underscore prefix: remove prefix, and call parent
	//TODO: only if method_exists(this->classname, $func)
	////////////////////////////////////////////////////////////////////////////
	public function __call($name, $arguments) {
		$method = new ReflectionMethod($this->classname, $name);
		if ($method->isStatic()) {
			return call_user_func_array(
				[$this->classname, $name],
				$arguments
			);
		}

		$return	= [];
		$list	= $this->raw();

		foreach ($list as $item) {
			if (!($item instanceof pudlOrm)) continue;

			$return[] = call_user_func_array(
				[$item, $name],
				$arguments
			);
		}

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//EXTRACT DATA FROM AN OBJECT AND PUT IT IN ALL OBJECTS IN THIS COLLECTION
	////////////////////////////////////////////////////////////////////////////
	public function extend() {
		$arguments = func_get_args();
		foreach ($this->raw() as $item) {
			if (!($item instanceof pudlOrm)) continue;
			call_user_func_array([$item,'extractFrom'],	$arguments);
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//PRIVATE MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $classname;

}
