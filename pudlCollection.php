<?php


require_once(is_owner(__DIR__.'/pudlObject.php'));



class		pudlCollection
	extends
			pudlObject
	implements
			OuterIterator {



	////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($classname, $list=NULL) {
		parent::__construct();

		$this->classname	= $classname;
		$this->first		= true;

		if ($list instanceof pudlResult) {
			$list = $list->complete();
		}

		if (!pudl_array($list)) return;

		foreach ($list as $item) {
			$this[]	= ($item instanceof $classname)
					? $item
					: new $classname($item);
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//INVOKE, ALIAS FOR NEXT ITEM IN COLLECTION. EASY WAY TO WALK THE LIST
	////////////////////////////////////////////////////////////////////////////
	public function __invoke() {
		if (!$this->first) return $this->next();

		$this->first = false;
		return $this->current();
	}




	////////////////////////////////////////////////////////////////////////////
	//RESET INTERNAL POINTER TO FIRST OBJECT IN COLLECTION
	////////////////////////////////////////////////////////////////////////////
	public function rewind() {
		$this->first = true;
		return pudlObject::rewind();
	}




	////////////////////////////////////////////////////////////////////////////
	//MOVE INTERNAL POINTER TO SPECIFIC ITEM WITHIN COLLECTION
	////////////////////////////////////////////////////////////////////////////
	public function seek($row) {
		if (!$row) $this->first = true;
		return pudlObject::seek($row);
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

		foreach ($list as $key => $item) {
			if (!($item instanceof pudlOrm)) continue;

			$return[$key] = call_user_func_array(
				[$item, $name],
				$arguments
			);
		}

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//CLOSURE STYLE PROCESSING
	////////////////////////////////////////////////////////////////////////////
	public function each($callback) {
		$return	= [];
		$list	= $this->raw();

		foreach ($list as $key => &$item) {
			if (!($item instanceof pudlOrm)) continue;
			$return[$key] = call_user_func_array($callback, [&$item, $key]);
		} unset($item);

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//PULL ALL INSTANCES OF A PARTICULAR COLUMN
	////////////////////////////////////////////////////////////////////////////
	public function column($column) {
		$return	= [];
		$list	= $this->raw();

		foreach ($list as $key => &$item) {
			if (!($item instanceof pudlOrm)) continue;
			if (empty($item[$column])) continue;
			$return[$key] = $item[$column];
		} unset($item);

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//RETURNS THE INNER ITERATOR FOR THE CURRENT ENTRY.
	//http://php.net/manual/en/outeriterator.getinneriterator.php
	////////////////////////////////////////////////////////////////////////////
	public function getInnerIterator() {
		return $this->current();
	}




	////////////////////////////////////////////////////////////////////////////
	//PRIVATE MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $classname;
	private $first;

}
