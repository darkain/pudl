<?php


require_once(is_owner(__DIR__.'/pudlCollection.php'));



abstract class	pudlOrm
	extends		pudlObject
	implements	pudlId {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct(pudl $pudl=NULL, $item=false, $fetch=false) {

		// SET THE LOCAL INSTANCE OF DATABASE OBJECT
		$this->__pudl__ = $pudl;


		// LOAD IN DATA
		if (is_array($item)) {
			$fetch ? $this->fetch($item)	: $this->govern($item);

		} else if ($item instanceof pudlResult) {
			$fetch ? $this->fetch($item())	: $this->copy($item());

		} else if ($item instanceof Traversable) {
			$fetch ? $this->fetch($item)	: $this->copy($item);

		} else if (is_object($item)  &&  method_exists($item, 'id')) {
			$this->fetch($item->id());

		} else if ($fetch  &&  (is_int($item)  ||  ctype_digit($item))) {
			$this->fetch($item);

		} else if (!empty($item)) {
			$this->{static::column} = $item;
		}


		// SET DEFAULT OBJECT ID AND FORCE INTEGER
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
	// IF CONVERTING CLASS TO STRING, RETURNS THE CURRENT OBJECT ID NUMBER
	////////////////////////////////////////////////////////////////////////////
	public function __toString() {
		return (string) $this->{static::column};
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE PUDL INSTANCE THIS OBJECT IS TIED TO
	////////////////////////////////////////////////////////////////////////////
	public function pudl() {
		return $this->__pudl__;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURN THE CURRENT OBJECT ID NUMBER
	////////////////////////////////////////////////////////////////////////////
	public function id($default=0) {
		if (static::hash) return $this->{static::column};
		return is_numeric($this->{static::column})
			? $this->{static::column}
			: $default;
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE A NEW INSTANCE OF THIS OBJECT IN THE DATABASE
	/** @suppress PhanNonClassMethodCall */
	////////////////////////////////////////////////////////////////////////////
	public static function create(pudl $pudl, $data=false, $update=false) {
		return static::get($pudl,
			$pudl->insertExtract(
				static::table,
				$data,
				($update === false) ? static::column : $update
			)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET AN INSTANCE OF THIS OBJECT FROM THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public static function get(pudl $pudl, $id) {
		if (pudl_array($id)) {
			if (isset($id[static::column])) {
				$id = $id[static::column];
			}
		}

		if (is_object($id)) {
			if (method_exists($id, 'id')) {
				$id = $id->id();

			} else if (property_exists($id, 'id')) {
				$id = $id->id;

			} else if (method_exists($id, '__toString')) {
				$id = (string) $id;
			}
		}

		if (!is_numeric($id)  &&  !is_string($id)  &&  !is_null($id)) {
			throw new pudlValueException($pudl,
				'Invalid ID for ' . __METHOD__ .
				': ' . gettype($id) .
				': ' . print_r($id, true)
			);
		}

		return static::instance($pudl, $id, true);
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE AN INSTANCE OF THIS CLASS, USING LATE STATIC BINDING
	////////////////////////////////////////////////////////////////////////////
	public static function instance(pudl $pudl, $item=false, $fetch=false) {
		return new static($pudl, $item, $fetch);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET AN INSTANCE OF THIS OBJECT FROM THE DATABASE FOR A GIVEN CLAUSE
	////////////////////////////////////////////////////////////////////////////
	public static function select(pudl $pudl /*, ...$selex */) {
		$args	= func_get_args();
		array_shift($args);
		array_unshift($args, ['limit'=>1], static::schema());

		$data	= call_user_func_array([$pudl,'selex'], $args)->complete();

		return pudl_array($data) ? static::instance($pudl, reset($data)) : $data;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A COLLECTION OF OBJECTS
	////////////////////////////////////////////////////////////////////////////
	public static function collect(pudl $pudl /*, ...$selex */) {
		$args			= func_get_args();
		array_shift($args);
		array_unshift($args, static::schema());

		$collector		= static::collector;
		$return			= new $collector($pudl, get_called_class());
		$result			= call_user_func_array([$pudl,'selex'], $args);

		while ($data	= $result()) {
			$return[]	= new static($pudl, $data);
		}

		$result->free();

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A COLLECTION OF OBJECTS FROM ID NUMBERS
	////////////////////////////////////////////////////////////////////////////
	public static function collection(pudl $pudl, $items /*, ...$selex */) {
		$args		= func_get_args();

		if ($items instanceof pudlStringResult) {
			$items = (string) $items;
		}

		if (is_string($items)) {
			$items	= $pudl($items);
		}

		if ($items instanceof pudlResult) {
			$list	= $items;
			$items	= [];
			foreach ($list as $item) {
				$items[] = $item[static::column];
			}
		}

		$args[1]	= ['clause' => [pudl::column(
			[static::prefix, static::column],
			$items
		)]];

		return call_user_func_array(
			[get_called_class(), 'collect'],
			$args
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE A COLLECTOR FOR EXISTING LIST OF ITEMS
	////////////////////////////////////////////////////////////////////////////
	public static function manage(pudl $pudl, $items) {
		$collector = static::collector;
		return new $collector($pudl, get_called_class(), $items);
	}




	////////////////////////////////////////////////////////////////////////////
	// OVERWRITE THE PUDL PARAMETERS FOR PULLING A COLLECTION
	////////////////////////////////////////////////////////////////////////////
	protected static function schema() {
		return ['table' => [static::prefix => static::table]];
	}




	////////////////////////////////////////////////////////////////////////////
	// COMPARE ITEM TO SEE IF IT IS THE CURRENT OBJECT INSTANCE
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
	// COMPARE ITEM TO SEE IF IT IS *NOT* THE CURRENT OBJECT INSTANCE
	////////////////////////////////////////////////////////////////////////////
	public function isnt( $item=true)	{ return !$this->is($item); }
	public function isnot($item=true)	{ return !$this->is($item); }




	////////////////////////////////////////////////////////////////////////////
	// UPDATE THE OBJECT IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function update($data) {
		if (pudl_array($data)) {
			foreach ($data as $key => $item) {
				if (is_int($key)) continue;
				$this->{$key} = $item;
			}
		}

		return $this->__pudl__->updateExtractId(
			static::table,
			$data,
			$this,
			false,
			1
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// UPDATE JSON VALUE IN THE DATABASE
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
	// GET A NEW INSTANCE OF THIS OBJECT WITH VALUES FROM THE CURRENT SNAPSHOT
	////////////////////////////////////////////////////////////////////////////
	public function _snapclone($snapshot) {
		return new static($this->__pudl__, $snapshot);
	}




	////////////////////////////////////////////////////////////////////////////
	// UPDATE OBJECT'S CHANGES BACK INTO DATABASE - REQUIRES AN EXISTING SNAPSHOT
	////////////////////////////////////////////////////////////////////////////
	public function snapdate($ignore=[]) {
		$data = $this->compareData();
		if (!is_array($data)) return false;

		foreach ($data as $key => $value) {
			if (in_array($key, $ignore)) {
				unset($data[$key]);
			}
		}

		return !empty($data)
			? $this->__pudl__->updateId(static::table, $data, $this, false, 1)
			: true;
	}




	////////////////////////////////////////////////////////////////////////////
	// DELETE THIS OBJECT FROM DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function delete() {
		return $this->__pudl__->deleteId(static::table, $this, false, 1);
	}




	////////////////////////////////////////////////////////////////////////////
	// PUDL INTEGRATION
	////////////////////////////////////////////////////////////////////////////
	public function pudlId() {
		return [static::column => $this->id()];
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE COLUMN NAME WITH THE TABLE ALIAS PREFIX ATTACHED
	////////////////////////////////////////////////////////////////////////////
	public static function prefixed() {
		return implode('.', [static::prefix, static::column]);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE TABLE DEFINITION FOR ICON (SPECIFIC TO ALTAFORM)
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
	// FETCH DATA FROM DATABASE
	/** @suppress PhanUndeclaredProperty, PhanTypeArraySuspicious */
	////////////////////////////////////////////////////////////////////////////
	protected function fetch($id) {
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


		// SET QUERY TO CACHE
		$this->pudl()->cache($this->_fetchCache());

		// EXECUTE QUERY AND RETURN THE DATA
		$data = $this->pudl()->selex(
			static::schema(),
			[
				'clause'	=> $clause,
				'limit'		=> 1,
			]
		)->complete();

		// STORE THE DATA
		!empty($data)
			? $this->govern($data[0])
			: $this->free();
	}




	////////////////////////////////////////////////////////////////////////////
	// HOW LONG SHOULD THE FETCHED DATA BE CACHED FOR (IN SECONDS)
	////////////////////////////////////////////////////////////////////////////
	protected function _fetchCache() { return 0; }




	////////////////////////////////////////////////////////////////////////////
	// SHORTCUT FUNCTIONS FOR ERROR CHECKING.
	// THIS IS RELATED TO THE ALTAFORM LIBRARY.
	// IF YOU'RE NOT USING THAT LIBRARY, THEN THIS IS MOST LIKELY WORTHLESS.
	////////////////////////////////////////////////////////////////////////////
	public function affirm($code, $text=false) {
		\af\affirm($code, (string)$this, $text);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// LOCAL METHOD VARIABLES
	////////////////////////////////////////////////////////////////////////////
	protected $__pudl__;



	////////////////////////////////////////////////////////////////////////////
	// LATE STATIC BINDING VARIABLES, OVERWRITE THESE IN YOUR CLASS
	////////////////////////////////////////////////////////////////////////////
	const	collector	= 'pudlCollection';
	const	column		= 'id';
	const	table		= 'pudl';
	const	prefix		= -1;
	const	hash		= false;
	const	json		= NULL;
	const	icon		= NULL;
}
