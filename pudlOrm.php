<?php


require_once(__DIR__.'/pudlCollection.php');



abstract class	pudlOrm
	extends		pudlObject
	implements	pudlId {




	public function __construct($item=false, $fetch=false) {
		if (static::classname === __CLASS__) {
			throw new pudlException('ORM const parameters were not overwritten');
		}

		if (is_array($item)) {
			$fetch ? $this->fetch($item)		: $this->govern($item);

		} else if ($item instanceof afUrl) {
			$this->fetch($item->id);

		} else if ($item instanceof getvar) {
			$this->fetch($item->id());

		} else if ($item instanceof pudlResult) {
			$fetch ? $this->fetch($item())		: $this->copy($item());

		} else if ($item instanceof Traversable) {
			$fetch ? $this->fetch($item)		: $this->copy($item);

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
	public function id($default=0) {
		if (static::hash) return $this->{static::column};
		return is_numeric($this->{static::column})
			? $this->{static::column}
			: $default;
	}




	////////////////////////////////////////////////////////////////////////////
	//CREATE A NEW INSTANCE OF THIS OBJECT IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public static function create($data=false, $update=false) {
		global $db;

		return static::get(
			$db->insertExtract(
				static::table,
				$data,
				($update === false) ? static::column : $update
			)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	//GET AN INSTANCE OF THIS OBJECT FROM THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public static function get($id=false) {
		global $get, $afurl;

		if ($id instanceof getvar)	$id = $get->id();
		if ($id instanceof afUrl)	$id = $afurl->id;

		if (pudl_array($id))	$id = $id[static::column];

		if ($id === false  &&  $get instanceof getvar) {
			$id = $get->id();
			if ($id === 0) $id = false;
		}

		if ($id === false  &&  $afurl instanceof afUrl) {
			$id = $afurl->id;
		}


		$id = (int) $id;

		return static::instance($id, true);
	}




	////////////////////////////////////////////////////////////////////////////
	//CREATE AN INSTANCE OF THIS CLASS, USING LATE STATIC BINDING
	////////////////////////////////////////////////////////////////////////////
	public static function instance($item=false, $fetch=false) {
		$class = static::classname;
		return new $class($item, $fetch);
	}




	////////////////////////////////////////////////////////////////////////////
	//GET AN INSTANCE OF THIS OBJECT FROM THE DATABASE FOR A GIVEN CLAUSE
	////////////////////////////////////////////////////////////////////////////
	public static function select(/* ...$selex */) {
		global $db;

		$args	= func_get_args();
		array_unshift($args, ['limit'=>1], static::schema());

		$data	= call_user_func_array([$db,'selex'], $args)->complete();

		return pudl_array($data) ? static::instance(reset($data)) : $data;
	}




	////////////////////////////////////////////////////////////////////////////
	//GET A COLLECTION OF OBJECTS
	////////////////////////////////////////////////////////////////////////////
	public static function collect(/* ...$selex */) {
		global $db;

		if (static::collector === __CLASS__) {
			throw new pudlException('ORM const parameters were not overwritten');
		}

		$args			= func_get_args();
		array_unshift($args, static::schema());

		$collector		= static::collector;
		$class			= static::classname;
		$return			= new $collector($class);
		$result			= call_user_func_array([$db,'selex'], $args);

		while ($data	= $result()) {
			$return[]	= new $class($data);
		}

		$result->free();

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//GET A COLLECTION OF OBJECTS FROM ID NUMBERS
	////////////////////////////////////////////////////////////////////////////
	public static function collection($items /*, ...$selex */) {
		global $db;

		$args		= func_get_args();

		if (is_string($items)) {
			$items	= $db($items);
		}

		if ($items instanceof pudlResult) {
			$list	= $items;
			$items	= [];
			foreach ($list as $item) {
				$items[] = $item[static::column];
			}
		}

		$args[0]	= ['clause' => [pudl::column(
			[static::prefix, static::column],
			$items
		)]];

		return call_user_func_array([static::classname,'collect'], $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//CREATE A COLLECTOR FOR EXISTING LIST OF ITEMS
	////////////////////////////////////////////////////////////////////////////
	public static function manage($items) {
		$collector = static::collector;
		return new $collector(static::classname, $items);
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
		$id = $this->id();

		if (is_bool($item)) {
			if (static::hash) {
				return ($item === true)	? !empty($id)	: empty($id);
			} else {
				return ($item === true)	? ($id != 0)	: ($id === 0);
			}
		}

		if (pudl_array($item)) {
			if (empty($item[static::column])) return false;
			$item = $item[static::column];

		} else if (is_object($item)) {
			if (empty($item->{static::column})) return false;
			$item = $item->{static::column};
		}

		if (!static::hash) $item = (int) $item;

		return !empty($item) ? ($item === $id) : false;
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPARE ITEM TO SEE IF IT IS *NOT* THE CURRENT OBJECT INSTANCE
	////////////////////////////////////////////////////////////////////////////
	public function isnt( $item=true)	{ return !$this->is($item); }
	public function isnot($item=true)	{ return !$this->is($item); }




	////////////////////////////////////////////////////////////////////////////
	//UPDATE THE OBJECT IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function update($data) {
		global $db;

		if (pudl_array($data)) {
			foreach ($data as $key => $item) {
				if (is_int($key)) continue;
				$this->{$key} = $item;
			}
		}

		return $db->updateExtractId(static::table, $data, $this, false, 1);
	}




	////////////////////////////////////////////////////////////////////////////
	//UPDATE JSON VALUE IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function updateJson(/* ...$keys, $values OR [$keys => $values]*/) {
		$args		= func_get_args();

		if (count($args) === 1  &&  pudl_array($args[0])) {
			$args	= reset($args);
		}

		if (empty($this->{static::json})) $this->{static::json} = [];

		return $this->update([
			static::json => array_replace_recursive(
				$this->{static::json},
				$args
			)
		]);
	}




	////////////////////////////////////////////////////////////////////////////
	//UPDATE OBJECT'S CHANGES BACK INTO DATABASE - REQUIRES AN EXISTING SNAPSHOT
	////////////////////////////////////////////////////////////////////////////
	public function push($ignore=[]) {
		global $db;

		$data = $this->compareData();
		if (!is_array($data)) return false;

		foreach ($data as $key => $value) {
			if (in_array($key, $ignore)) {
				unset($data[$key]);
			}
		}

		return !empty($data)
			? $db->updateId(static::table, $data, $this, false, 1)
			: true;
	}




	////////////////////////////////////////////////////////////////////////////
	//DELETE THIS OBJECT FROM DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function delete() {
		global $db;
		return $db->deleteId(static::table, $this, false, 1);
	}




	////////////////////////////////////////////////////////////////////////////
	//PUDL INTEGRATION
	////////////////////////////////////////////////////////////////////////////
	public function pudlId() {
		return [static::column => $this->id()];
	}




	////////////////////////////////////////////////////////////////////////////
	//GET THE COLUMN NAME WITH THE TABLE ALIAS PREFIX ATTACHED
	////////////////////////////////////////////////////////////////////////////
	public static function prefixed() {
		return implode('.', [static::prefix, static::column]);
	}




	////////////////////////////////////////////////////////////////////////////
	//GET THE TABLE DEFINITION FOR ICON (SPECIFIC TO ALTAFORM)
	////////////////////////////////////////////////////////////////////////////
	public static function icon($type=200) {
		if (!static::icon) return NULL;

		$column = pudl::column(static::prefix . '.' . static::icon);

		return [
			static::prefix			=> static::table,

			static::prefix . '_th'	=> [
				'left'	=> ['th'	=> 'pudl_file_thumb'],
				'on'	=> [
					'th.file_hash'	=> $column,
					'th.thumb_type'	=> (string) $type,
				],
			],

			static::prefix . '_fl'	=> [
				'left'	=> ['tx'	=> 'pudl_file'],
				'on'	=> [
					'tx.file_hash'	=> $column,
				],
			],
		];
	}




	////////////////////////////////////////////////////////////////////////////
	//FETCH DATA FROM DATABASE
	////////////////////////////////////////////////////////////////////////////
	protected function fetch($id) {
		global $db;

		if (pudl_array($id)) {
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
			? $this->govern($data[0])
			: $this->free();
	}




	////////////////////////////////////////////////////////////////////////////
	//HOW LONG SHOULD THE FETCHED DATA BE CACHED FOR (IN SECONDS)
	////////////////////////////////////////////////////////////////////////////
	protected function _fetchCache() { return 0; }




	////////////////////////////////////////////////////////////////////////////
	//SHORTCUT FUNCTIONS FOR ERROR CHECKING
	//THESE ARE RELATED TO ALTAFORM. IF YOU'RE NOT USING THAT LIBRARY, THESE
	//ARE THEN WORTHLESS
	////////////////////////////////////////////////////////////////////////////
	public function assert400($text=false) { assert400((string)$this, $text); return $this; }
	public function assert401($text=false) { assert401((string)$this, $text); return $this; }
	public function assert402($text=false) { assert402((string)$this, $text); return $this; }
	public function assert403($text=false) { assert403((string)$this, $text); return $this; }
	public function assert404($text=false) { assert404((string)$this, $text); return $this; }
	public function assert405($text=false) { assert405((string)$this, $text); return $this; }
	public function assert422($text=false) { assert422((string)$this, $text); return $this; }
	public function assert500($text=false) { assert500((string)$this, $text); return $this; }
	public function assert503($text=false) { assert503((string)$this, $text); return $this; }




	////////////////////////////////////////////////////////////////////////////
	//LATE STATIC BINDING VARIABLES, OVERWRITE THESE IN YOUR CLASS
	////////////////////////////////////////////////////////////////////////////
	const	collector	= 'pudlCollection';
	const	classname	= __CLASS__;
	const	column		= 'id';
	const	table		= 'pudl';
	const	prefix		= -1;
	const	hash		= false;
	const	json		= NULL;
	const	icon		= NULL;
}
