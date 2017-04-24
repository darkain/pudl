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
	//PRIVATE MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $classname;

}
