<?php


require_once('pudlObject.php');



class		pudlCollection
	extends	pudlObject {




	////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($classname, $list=NULL) {
		$this->classname = $classname;

		if (!pudl_array($list)) return;

		foreach ($list as $item) {
			$this[]	= $item instanceof pudlOrm
					? $item
					: new $classname($item);
		}
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
		if (empty($name)) return;


		//ALLOW FORWARDING CALLS TO PUDLOBJECT METHEDS BY UNDERSCORE PREFIXING
		if ($name[0] === '_') {
			$newname = substr($name, 1);
			if (method_exists($this->classname, $newname)) {
				$name = $newname;
			}
		}


		//ALLOW FORWARDING TO STATIC FUNCTION, ONLY CALLED ONCE PER COLLECTION
		//INSTEAD OF ONCE PER OBJECT INSTANCE
		$method = new ReflectionMethod($this->classname, $name);
		if ($method->isStatic()) {
			return call_user_func_array(
				[$this->classname, $name],
				$arguments
			);
		}


		//FORWARD CALL TO ALL OBJECTS WITHIN COLLECTION
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
