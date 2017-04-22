<?php


require_once('pudlCollection.php');



abstract class	pudlOrm
	extends		pudlObject
	implements	pudlId {




	public function __construct($item=false, $fetch=false) {
		assert500(static::table !== 'pudl');

		if (is_array($item)) {
			$fetch ? $this->fetch($item)		: $this->_replace($item);

		} else if (is_a($item, 'af_url')) {
			$this->fetch($item->id);

		} else if (is_a($item, 'getvar')) {
			$this->fetch($item->id());

		} else if (is_a($item, 'pudlResult')) {
			$fetch ? $this->fetch($item())		: $this->_clone($item());

		} else if (is_a($item, 'Traversable')) {
			$fetch ? $this->fetch($item)		: $this->_clone($item);

		} else if ($fetch  &&  (is_int($item)  ||  ctype_digit($item))) {
			$this->fetch($item);

		} else if (!empty($item)) {
			$this->{static::column} = $item;
		}


		//SET DEFAULT OBJECT ID AND FORCE INTEGER
		if (static::hash) {
			if (empty($this->{static::column})) {
				$this->{static::column} = '';
			}

		} else {
			$this->{static::column} = !empty($this->{static::column})
									?  (int) $this->{static::column}
									: 0;
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//IF CONVERTING CLASS TO STRING, RETURNS THE CURRENT OBJECT ID NUMBER
	////////////////////////////////////////////////////////////////////////////
	public function __toString() {
		return (string) $this->{static::column};
	}




	////////////////////////////////////////////////////////////////////////////
	//RETURN THE CURRENT OBJECT ID NUMBER
	////////////////////////////////////////////////////////////////////////////
	public function id() {
		return (int) $this->{static::column};
	}




	////////////////////////////////////////////////////////////////////////////
	//CREATE A NEW INSTANCE OF THIS OBJECT IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public static function create($data=false) {
		global $db;
		return static::get($db->insert(static::table, $data));
	}




	////////////////////////////////////////////////////////////////////////////
	//GET AN INSTANCE OF THIS OBJECT FROM THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public static function get($id=false) {
		global $get, $afurl;

		if (tbx_array($id))	$id = $id[static::column];

		if (empty($id)  &&  $get instanceof getvar) {
			$id = $get->id();
		}

		if (empty($id)  &&  $afurl instanceof af_url) {
			$id = $afurl->id;
		}

		$id = (int) $id;

		$class = static::classname;
		return new $class($id, true);
	}




	////////////////////////////////////////////////////////////////////////////
	//GET AN INSTANCE OF THIS OBJECT FROM THE DATABASE FOR A GIVEN CLAUSE
	////////////////////////////////////////////////////////////////////////////
	public static function select($clause, $pudl=[]) {
		global $db;

		$data = $db(array_merge_recursive(
			static::schema(),
			$pudl,
			[
				'clause'	=> is_array($clause) ? $clause : [$clause],
				'limit'		=> 1,
			]
		))->complete();

		$class = static::classname;
		return new $class(reset($data));
	}




	////////////////////////////////////////////////////////////////////////////
	//GET A COLLECTION OF OBJECTS
	////////////////////////////////////////////////////////////////////////////
	public static function collect($clause, $pudl=[]) {
		global $db;

		$result = $db(array_merge_recursive(
			static::schema(),
			$pudl,
			['clause' => is_array($clause) ? $clause : [$clause]]
		));

		$class	= static::classname;
		$return	= new pudlCollection;

		while ($data = $result()) {
			$return[] = new $class($data);
		}

		$result->free();

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//GET A COLLECTION OF PARTS FROM ID NUMBERS
	////////////////////////////////////////////////////////////////////////////
	public static function group($items, $pudl=[]) {
		return static::collect([static::column=>$items], $pudl);
	}




	////////////////////////////////////////////////////////////////////////////
	//OVERWRITE THE PUDL PARAMETERS FOR PULLING A COLLECTION
	////////////////////////////////////////////////////////////////////////////
	protected static function schema() {
		return ['table' => [static::prefix => static::table]];
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPARE ITEM TO SEE IF IT IS THE CURRENT OBJECT INSTANCE
	////////////////////////////////////////////////////////////////////////////
	public function is($item=true) {
		if ($item === true)		return $this->id() > 0;
		if ($item === false)	return $this->id() === 0;
		if (empty($item))		return false;

		if (is_array($item)) {
			if (empty($item[static::column])) return false;
			$item = $item[static::column];

		} else if (is_object($item)) {
			if (empty($item->{static::column})) return false;
			$item = $item->{static::column};
		}

		$item = (int) $item;
		if ($item === 0) return false;

		return $item === $this->id();
	}




	////////////////////////////////////////////////////////////////////////////
	//UPDATE THE OBJECT IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function update($data) {
		global $db;

		if (tbx_array($data)) {
			foreach ($data as $key => $item) {
				$this->{$key} = $item;
			}
		}

		return $db->updateId(static::table, $data, $this);
	}




	////////////////////////////////////////////////////////////////////////////
	//UPDATE OBJECT'S CHANGES BACK INTO DATABASE - REQUIRES AN EXISTING SNAPSHOT
	////////////////////////////////////////////////////////////////////////////
	public function push($ignore=[]) {
		global $db;

		$data = $this->compare();

		foreach ($data as $key => $value) {
			if (in_array($key, $ignore)) {
				unset($data[$key]);
			}
		}

		return !empty($data)
			? $db->updateId(static::table, $data, $this)
			: true;
	}




	////////////////////////////////////////////////////////////////////////////
	//DELETE THIS OBJECT FROM DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function delete() {
		global $db;
		return $db->deleteId(static::table, $this);
	}




	////////////////////////////////////////////////////////////////////////////
	//PUDL INTEGRATION
	////////////////////////////////////////////////////////////////////////////
	public function pudlId() {
		return [static::column => $this->id()];
	}




	////////////////////////////////////////////////////////////////////////////
	//FETCH DATA FROM DATABASE
	////////////////////////////////////////////////////////////////////////////
	protected function fetch($id) {
		global $db;

		if (is_array($id)) {
			$id = !empty($id[static::column])
				? $id[static::column]
				: 0;

		} else if (is_object($id)) {
			$id = !empty($id->{static::column})
				? $id->{static::column}
				: 0;
		}

		if (is_int(static::prefix)) {
			$clause = [static::column => $id];
		} else {
			$clause = [static::prefix.'.'.static::column => $id];
		}

		$data = $db	->cache($this->_fetchCache())
					->query(static::schema(), [
						'clause'	=> $clause,
						'limit'		=> 1,
					])
					->complete();

		!empty($data)
			? $this->_replace($data[0])
			: $this->_clear();
	}




	////////////////////////////////////////////////////////////////////////////
	//HOW LONG SHOULD THE FETCHED DATA BE CACHED FOR (IN SECONDS)
	////////////////////////////////////////////////////////////////////////////
	protected function _fetchCache() { return 0; }




	////////////////////////////////////////////////////////////////////////////
	//SHORTCUT FUNCTIONS FOR ERROR CHECKING
	////////////////////////////////////////////////////////////////////////////
	public function assert401($text=false) { assert401((string)$this, $text); return $this; }
	public function assert402($text=false) { assert402((string)$this, $text); return $this; }
	public function assert403($text=false) { assert403((string)$this, $text); return $this; }
	public function assert404($text=false) { assert404((string)$this, $text); return $this; }
	public function assert405($text=false) { assert405((string)$this, $text); return $this; }
	public function assert422($text=false) { assert422((string)$this, $text); return $this; }
	public function assert500($text=false) { assert500((string)$this, $text); return $this; }




	////////////////////////////////////////////////////////////////////////////
	//LATE STATIC BINDING VARIABLES, OVERWRITE THESE IN YOUR CLASS
	////////////////////////////////////////////////////////////////////////////
	const classname	= __CLASS__;
	const column	= 'id';
	const table		= 'pudl';
	const prefix	= -1;
	const hash		= false;
}
