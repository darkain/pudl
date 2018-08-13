<?php




require_once(__DIR__.'/pudlData.php');
require_once(__DIR__.'/pudlInterfaces.php');
require_once(__DIR__.'/pudlConstants.php');




class	pudlObject
	implements
		ArrayAccess,
		pudlData {




	////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct(&$data=NULL, $process=false) {
		switch (true) {
			case is_string($data)  &&  is_string($process):
				$x = @explode($process, $data);
				$x === false ? $this->free() : $this->govern($x);
			break;

			case $process === PUDL_CSV:
				$x = @str_getcsv($data);
				$x === [NULL] ? $this->free() : $this->govern($x);
			break;

			case !!$process:
				$this->copy($data);
			break;

			default:
				$this->govern($data);
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//CLEARS ALL DATA WITHIN OBJECT - RESETTING BACK TO DEFAULTS
	////////////////////////////////////////////////////////////////////////////
	public function free() {
		$this->__array		= [];
		$this->__snapshot	= false;
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//MANAGE THE GIVEN ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function govern(&$data) {
		$this->free();

		if (is_array($data)) {
			$this->__array = &$data;
		} else {
			$this->merge($data);
		}

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//CLEARS THE OBJECT'S ARRAY, AND THEN COPIES THE GIVEN ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function copy($data) {
		return $this->free()->merge($data);
	}




	////////////////////////////////////////////////////////////////////////////
	//MERGE THE GIVEN ARRAY VALUES INTO THIS OBJECT
	//http://php.net/manual/en/function.array-merge.php
	////////////////////////////////////////////////////////////////////////////
	public function merge($array, $nulls=true) {
		if ($array instanceof pudlObject) $array = $array->raw();
		if ($array instanceof pudlResult) $array = $array->complete();
		if (empty($array)  ||  !pudl_array($array)) return $this;

		if ($nulls) {
			$this->__array = array_merge($this->__array, $array);

		} else {
			foreach ($array as $key => $value) {
				if (isset($this->__array[$key])) continue;
				if (is_null($value)) continue;

				is_int($key)
					? ($this->__array[]		= $value)
					: ($this->__array[$key]	= $value);
			}
		}

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//MERGE THE GIVEN ARRAY VALUES INTO THIS OBJECT, RECUSIVELY
	//http://php.net/manual/en/function.array-merge-recursive.php
	////////////////////////////////////////////////////////////////////////////
	public function mergeRecursive($array) {
		if ($array instanceof pudlObject) $array = $array->raw();
		if (empty($array)  ||  !pudl_array($array)) return $this;
		$this->__array = array_merge_recursive($this->__array, $array);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//MERGE THIS OBJECT INTO THE GIVEN ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function mergeInto(&$array, $nulls=true) {
		if ($array instanceof pudlObject) {
			$arr = &$array->raw();
		} else if (pudl_array($array)) {
			$arr = &$array;
		} else {
			return $this;
		}

		if ($nulls) {
			$arr = array_merge($arr, $this->__array);

		} else {
			foreach ($this->__array as $key => $value) {
				if (isset($arr[$key])) continue;
				if (is_null($value)) continue;

				is_int($key)
					? ($arr[]		= $value)
					: ($arr[$key]	= $value);
			}
		}

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//REPLACE THE GIVEN ARRAY VALUES INTO THIS OBJECT
	//http://php.net/manual/en/function.array-replace.php
	////////////////////////////////////////////////////////////////////////////
	public function replace($array) {
		if ($array instanceof pudlObject) $array = $array->raw();
		if (empty($array)  ||  !pudl_array($array)) return $this;
		$this->__array = array_replace($this->__array, $array);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//REPLACE THE GIVEN ARRAY VALUES INTO THIS OBJECT, RECUSIVELY
	//http://php.net/manual/en/function.array-replace-recursive.php
	////////////////////////////////////////////////////////////////////////////
	public function replaceRecursive($array) {
		if ($array instanceof pudlObject) $array = $array->raw();
		if (empty($array)  ||  !pudl_array($array)) return $this;
		$this->__array = array_replace_recursive($this->__array, $array);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//COPY THE GIVEN ARRAY INTO THIS OBJECT, ONLY FOR KEYS THAT ARE MISSING
	////////////////////////////////////////////////////////////////////////////
	public function append($array) {
		if (empty($array)  ||  !pudl_array($array)) return $this;
		foreach($array as $key => $value) {
			if (array_key_exists($key, $this->__array)) continue;
			$this->__array[$key] = $value;
		}
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//COPIES THIS OBJECT INTO THE GIVEN ARRAY, ONLY FOR KEYS THAT ARE MISSING
	////////////////////////////////////////////////////////////////////////////
	public function appendInto(&$array) {
		if (empty($array)  ||  !pudl_array($array)) return $this;
		foreach($this->__array as $key => $value) {
			if (array_key_exists($key, $array)) continue;
			$array[$key] = $value;
		}
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//GET THE RAW ARRAY FOR THIS OBJECT
	////////////////////////////////////////////////////////////////////////////
	public function &raw() {
		return $this->__array;
	}




	////////////////////////////////////////////////////////////////////////////
	//COUNT ALL ELEMENTS IN AN ARRAY, OR SOMETHING IN AN OBJECT
	//http://php.net/manual/en/function.count.php
	////////////////////////////////////////////////////////////////////////////
	public function count() {
		return count($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//JOIN ARRAY ELEMENTS WITH A STRING
	//http://php.net/manual/en/function.implode.php
	////////////////////////////////////////////////////////////////////////////
	public function implode($glue=',') {
		return implode($glue, $this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//CHECKS IF A VALUE EXISTS IN AN ARRAY
	//http://php.net/manual/en/function.in-array.php
	////////////////////////////////////////////////////////////////////////////
	public function in($value, $strict=false) {
		return in_array($value, $this->__array, $strict);
	}




	////////////////////////////////////////////////////////////////////////////
	//PUSH ONE OR MORE ELEMENTS ONTO THE END OF ARRAY
	//http://php.net/manual/en/function.array-push.php
	////////////////////////////////////////////////////////////////////////////
	public function push() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_push', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//POP THE ELEMENT OFF THE END OF ARRAY
	//http://php.net/manual/en/function.array-pop.php
	////////////////////////////////////////////////////////////////////////////
	public function pop() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_pop', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//POP ITEMS OUT OF THE END OF THIS OBJECT
	//http://php.net/manual/en/function.array-shift.php
	////////////////////////////////////////////////////////////////////////////
	public function shift() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_shift', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//PREPEND ONE OR MORE ELEMENTS TO THE BEGINNING OF AN ARRAY
	//http://php.net/manual/en/function.array-unshift.php
	////////////////////////////////////////////////////////////////////////////
	public function unshift() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_unshift', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//EXCHANGES ALL KEYS WITH THEIR ASSOCIATED VALUES IN AN ARRAY
	//http://php.net/manual/en/function.array-flip.php
	////////////////////////////////////////////////////////////////////////////
	public function flip() {
		$this->__array = array_flip($this->__array);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//RETURN AN ARRAY WITH ELEMENTS IN REVERSE ORDER
	//http://php.net/manual/en/function.array-reverse.php
	////////////////////////////////////////////////////////////////////////////
	public function reverse($preserve_keys=true) {
		$this->__array = array_reverse($this->__array, $preserve_keys);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//SORT AN ARRAY
	//http://php.net/manual/en/function.sort.php
	////////////////////////////////////////////////////////////////////////////
	public function sort($sort_flags=SORT_REGULAR) {
		sort($this->__array, $sort_flags);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//SORT AN ARRAY IN REVERSE ORDER
	//http://php.net/manual/en/function.rsort.php
	////////////////////////////////////////////////////////////////////////////
	public function rsort($sort_flags=SORT_REGULAR) {
		rsort($this->__array, $sort_flags);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//SORT AN ARRAY AND MAINTAIN INDEX ASSOCIATION
	//http://php.net/manual/en/function.asort.php
	////////////////////////////////////////////////////////////////////////////
	public function asort($sort_flags=SORT_REGULAR) {
		asort($this->__array, $sort_flags);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//SORT AN ARRAY IN REVERSE ORDER AND MAINTAIN INDEX ASSOCIATION
	//http://php.net/manual/en/function.arsort.php
	////////////////////////////////////////////////////////////////////////////
	public function arsort($sort_flags=SORT_REGULAR) {
		arsort($this->__array, $sort_flags);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//SORT AN ARRAY BY KEY
	//http://php.net/manual/en/function.ksort.php
	////////////////////////////////////////////////////////////////////////////
	public function ksort($sort_flags=SORT_REGULAR) {
		ksort($this->__array, $sort_flags);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//SORT AN ARRAY BY KEY IN REVERSE ORDER
	//http://php.net/manual/en/function.krsort.php
	////////////////////////////////////////////////////////////////////////////
	public function krsort($sort_flags=SORT_REGULAR) {
		krsort($this->__array, $sort_flags);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//SHUFFLE AN ARRAY
	//http://php.net/manual/en/function.shuffle.php
	////////////////////////////////////////////////////////////////////////////
	public function shuffle() {
		shuffle($this->__array);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPUTES THE DIFFERENCE OF ARRAYS
	//http://php.net/manual/en/function.array-diff.php
	////////////////////////////////////////////////////////////////////////////
	public function diff() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPUTES THE DIFFERENCE OF ARRAYS WITH ADDITIONAL INDEX CHECK
	//http://php.net/manual/en/function.array-diff-assoc.php
	////////////////////////////////////////////////////////////////////////////
	public function diff_assoc() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff_assoc', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPUTES THE DIFFERENCE OF ARRAYS WITH ADDITIONAL INDEX CHECK RECURSIVELY
	//http://php.net/manual/en/function.array-diff-assoc.php#111675
	////////////////////////////////////////////////////////////////////////////
	public function diff_assoc_recursive() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff_assoc_recursive', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPUTES THE DIFFERENCE OF ARRAYS USING KEYS FOR COMPARISON
	//http://php.net/manual/en/function.array-diff-key.php
	////////////////////////////////////////////////////////////////////////////
	public function diff_key() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff_key', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPUTES THE INTERSECTION OF ARRAYS
	//http://php.net/manual/en/function.array-intersect.php
	////////////////////////////////////////////////////////////////////////////
	public function intersect() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_intersect', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPUTES THE INTERSECTION OF ARRAYS WITH ADDITIONAL INDEX CHECK
	//http://php.net/manual/en/function.array-intersect-assoc.php
	////////////////////////////////////////////////////////////////////////////
	public function intersect_assoc() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_intersect_assoc', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPUTES THE INTERSECTION OF ARRAYS USING KEYS FOR COMPARISON
	//http://php.net/manual/en/function.array-intersect-key.php
	////////////////////////////////////////////////////////////////////////////
	public function intersect_key() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_intersect_key', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//RETURN ALL THE KEYS OR A SUBSET OF THE KEYS OF AN ARRAY
	//http://php.net/manual/en/function.array-keys.php
	////////////////////////////////////////////////////////////////////////////
	public function keys($search_value=null, $strict=false) {
		return array_keys($this->__array, $search_value, $strict);
	}




	////////////////////////////////////////////////////////////////////////////
	//EXTRACT A SLICE OF THE ARRAY
	//http://php.net/manual/en/function.array-slice.php
	////////////////////////////////////////////////////////////////////////////
	public function slice($offset, $length=NULL, $preserve_keys=false) {
		return array_slice($this->__array, $offset, $length, $preserve_keys);
	}




	////////////////////////////////////////////////////////////////////////////
	//REMOVE A PORTION OF THE ARRAY AND REPLACE IT WITH SOMETHING ELSE
	//http://php.net/manual/en/function.array-splice.php
	////////////////////////////////////////////////////////////////////////////
	public function splice($offset, $length=NULL, $replacement) {
		if (is_null($length)) $length = count($this->__array);
		return array_splice($this->__array, $offset, $length, $replacement);
	}




	////////////////////////////////////////////////////////////////////////////
	//INJECT AN ITEM INTO THE MIDDLE OF THE ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function inject($offset, $items) {
		return array_splice($this->__array, $offset, 0, $items);
	}




	////////////////////////////////////////////////////////////////////////////
	//MAGIC METHOD - RUN WHEN WRITING DATA TO INACCESSIBLE PROPERTIES
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function __set($key, $value) {
		$this->__array[$key]		= $value;
	}




	////////////////////////////////////////////////////////////////////////
	//ARRAY ACCESS - ASSIGN A VALUE TO THE SPECIFIED OFFSET
	//http://php.net/manual/en/arrayaccess.offsetset.php
	////////////////////////////////////////////////////////////////////////////
	public function offsetSet($key, $value) {
		if (is_null($key)) {
			$this->__array[]		= $value;
		} else {
			$this->__array[$key]	= $value;
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//MAGIC METHOD - UTILIZED FOR READING DATA FROM INACCESSIBLE PROPERTIES
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function &__get($key) {
		return $this->__array[$key];
	}




	////////////////////////////////////////////////////////////////////////////
	//ARRAY ACCESS - OFFSET TO RETRIEVE
	//http://php.net/manual/en/arrayaccess.offsetget.php
	////////////////////////////////////////////////////////////////////////////
	public function &offsetGet($key) {
		return $this->__array[$key];
	}




	////////////////////////////////////////////////////////////////////////////
	//MAGIC METHOD - CALLING ISSET() OR EMPTY() ON INACCESSIBLE PROPERTIES
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function __isset($key) {
		return isset($this->__array[$key]);
	}




	////////////////////////////////////////////////////////////////////////////
	//ARRAY ACCESS - WHETHER AN OFFSET EXISTS
	//http://php.net/manual/en/arrayaccess.offsetexists.php
	////////////////////////////////////////////////////////////////////////////
	public function offsetExists($key, $isset=true) {
		return $isset
			? isset($this->__array[$key])
			: array_key_exists($key, $this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//MAGIC METHOD - INVOKED WHEN UNSET() IS USED ON INACCESSIBLE PROPERTIES
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function __unset($key) {
		unset($this->__array[$key]);
	}




	////////////////////////////////////////////////////////////////////////////
	//ARRAY ACCESS - UNSET AN OFFSET
	//http://php.net/manual/en/arrayaccess.offsetunset.php
	////////////////////////////////////////////////////////////////////////////
	public function offsetUnset($key) {
		unset($this->__array[$key]);
	}




	////////////////////////////////////////////////////////////////////////////
	//SEEKABLE ITERATOR - MOVE THE ARRAY POINTER TO THE GIVEN ROW NUMBER
	//http://php.net/manual/en/seekableiterator.seek.php
	////////////////////////////////////////////////////////////////////////////
	public function seek($row) {
		$row = (int) $row;
		reset($this->__array);
		while ($row-- > 0) next($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//ITERATOR - REWIND THE ITERATOR TO THE FIRST ELEMENT
	//http://php.net/manual/en/iterator.rewind.php
	////////////////////////////////////////////////////////////////////////////
	public function rewind() {
		reset($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//ITERATOR - RETURN THE CURRENT ELEMENT
	//http://php.net/manual/en/iterator.current.php
	////////////////////////////////////////////////////////////////////////////
	public function current() {
		return current($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//ITERATOR - RETURN THE KEY OF THE CURRENT ELEMENT
	//http://php.net/manual/en/iterator.key.php
	////////////////////////////////////////////////////////////////////////////
	public function key() {
		return key($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//ITERATOR - MOVE FORWARD TO NEXT ELEMENT
	//http://php.net/manual/en/iterator.next.php
	////////////////////////////////////////////////////////////////////////////
	public function next() {
		return next($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//ITERATOR - CHECKS IF CURRENT POSITION IS VALID
	//http://php.net/manual/en/iterator.valid.php
	////////////////////////////////////////////////////////////////////////////
	public function valid() {
		$key = key($this->__array);
		return ($key !== NULL && $key !== FALSE);
	}




	////////////////////////////////////////////////////////////////////////////
	//RETURNS THE JSON REPRESENTATION OF A VALUE
	//http://php.net/manual/en/function.json-encode.php
	////////////////////////////////////////////////////////////////////////////
	public function json() {
		return pudl::jsonEncode($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//GET AN ARRAY FROM THIS OBJECT OF THE GIVEN KEYS ONLY
	////////////////////////////////////////////////////////////////////////////
	public function extract($keys) {
		$return = [];

		if (!pudl_array($keys)) $keys = func_get_args();

		foreach ($keys as $key => $value) {
			if (!is_string($key)) $key = $value;
			if (!array_key_exists($key, $this->__array)) continue;
			$return[$key] = $this->__array[$key];
		}

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//GET AN ARRAY FROM THIS OBJECT OF ALL KEYS, EXCLUDING ONES LISTED IN $KEYS
	////////////////////////////////////////////////////////////////////////////
	public function exclude($keys) {
		$return = [];

		if (!pudl_array($keys)) $keys = func_get_args();

		foreach ($this->__array as $key => $value) {
			if (array_key_exists($key, $keys)) continue;
			$return[$key] = $value;
		}

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//COPY SOURCE ARRAY INTO OBJECT, BUT ONLY FOR A GIVEN SET OF KEYS
	////////////////////////////////////////////////////////////////////////////
	public function extend($source, $keys) {
		if ($source instanceof pudlObject) $source = $source->raw();

		if (!pudl_array($keys)) {
			$keys = func_get_args();
			array_shift($keys);
		}

		foreach ($keys as $key => $value) {
			if (!is_string($key)) $key = $value;
			if (!array_key_exists($key, $source)) continue;
			$this->__array[$key] = $source[$key];
		}

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//RUN A CALLBACK FUNCTION FOR EVERY ITEM
	////////////////////////////////////////////////////////////////////////////
	public function each($callback) {
		$return	= [];

		foreach ($this->__array as $key => &$item) {
			$return[$key] = call_user_func_array($callback, [&$item, $key]);
		} unset($item);

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//TRUE		CHECK TO SEE IF THE KEY EXISTS
	//FALSE		CHECK TO SEE IF THE KEY DOESN'T EXIST
	//OTHER		CHECK TO SEE IF THE KEY'S VALUE === GIVEN VALUE
	////////////////////////////////////////////////////////////////////////////
	public function has($key, $value=true) {
		if (pudl_array($value)) {
			if (!isset($value[$key])) return false;
			$value = $value[$key];

		} else if (is_object($value)) {
			if (!isset($value->{$key})) return false;
			$value = $value->{$key};
		}


		if (!isset($this->__array[$key])) {
			return $value === false;
		}

		if ($value === true) {
			return !empty($this->__array[$key]);
		}

		return $this->__array[$key] === $value;
	}




	////////////////////////////////////////////////////////////////////////////
	//PARTITION THE ARRAY INTO MULTIPE EQUAL SIZED CHUNKS
	//http://php.net/manual/en/function.array-chunk.php#75022
	////////////////////////////////////////////////////////////////////////////
	public function partition($columns) {
		$columns = (int) $columns;
		if ($columns < 1) return [];

		$count	= count($this->__array);
		$length	= (int)($count / $columns);
		$mod	= $count % $columns;
		$return	= [];
		$offset	= 0;

		for ($i=0; $i<$columns; $i++) {
			$width		= ($i < $mod) ? $length + 1 : $length;
			$return[]	= array_slice($this->__array, $offset, $width);
			$offset		+= $width;
		}

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//pudlData - GET TOTAL NUMBER OF FIELDS FROM FIRST OBJECT IN LIST
	////////////////////////////////////////////////////////////////////////////
	public function fields() {
		if (empty($this->__array[0]))		return 0;
		if (!pudl_array($this->__array[0]))	return 0;
		return count($this->__array[0]);
	}




	////////////////////////////////////////////////////////////////////////////
	//pudlData - GET FIELD NAME FROM FIRST OBJECT IN LIST
	//FORMAT MIRRORS: http://php.net/manual/en/mysqli-result.fetch-field.php
	////////////////////////////////////////////////////////////////////////////
	public function getField($column) {
		$column	= (int) $column;
		$count	= 0;

		if ($column >= $this->fields()) return false;

		foreach ($this->__array[0] as $key => $value) {
			if ($column !== $count++) continue;

			return [
				'name'			=> $key,
				'orgname'		=> $key,
				'table'			=> get_class(),
				'orgtable'		=> get_class(),
				'def'			=> '',
				'db'			=> 'pudlObject',
				'catalog'		=> 'def',
				'max_length'	=> PHP_INT_MAX,
				'length'		=> PHP_INT_MAX,
				'charsetnr'		=> 0,
				'flags'			=> 0,
				'type'			=> gettype($value),
				'decimals'		=> 0,
			];
		}

		return false;
	}




	////////////////////////////////////////////////////////////////////////////
	//pudlData - GET INFORMATION ON EVERY FIELD
	////////////////////////////////////////////////////////////////////////////
	public function listFields() {
		$fields	= [];
		$total	= $this->fields();

		for ($i=0; $i<$total; $i++) {
			$fields[] = $this->getField($i);
		}

		return $fields;
	}




	////////////////////////////////////////////////////////////////////////////
	//pudlData - GET THE CURRENT ROW, THEN ADVANCE THE INTERNAL ARRAY POINTER
	////////////////////////////////////////////////////////////////////////////
	public function row($type=PUDL_ARRAY) {
		$row = current($this->__array);
		if ($row === false) return false;

		next($this->__array);

		if ($type === PUDL_ARRAY) return $row;

		$array = ($row instanceof pudlObject)
				? $row->raw()
				: (array) $row;

		return ($type === PUDL_NUMBER)
			? array_values($array)
			: array_merge(array_values($array), $array);
	}




	////////////////////////////////////////////////////////////////////////////
	//TRUE:		TAKE A NEW SNAPSHOT
	//FALSE:	GET THE CURRENT SNAPSHOT
	//STRING:	GET ITEM FROM CURRENT SNAPSHOT
	//INT:		GET INDEX FROM CURRENT SNAPSHOT
	////////////////////////////////////////////////////////////////////////////
	public function snapshot($snapshot=true) {
		if ($snapshot === true) $this->__snapshot = $this->__array;

		if (is_bool($snapshot)) {
			$class = get_class($this);
			return new $class($this->__snapshot);
		}

		if (!is_string($snapshot)  &&  !is_int($snapshot)) {
			return NULL;
		}

		return array_key_exists($snapshot, $this->__snapshot)
			? $this->__snapshot[$snapshot]
			: NULL;
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPARE CURRENT DATA WITH SNAPSHOT DATA, RETURNING AN ARRAY OF CHANGES
	////////////////////////////////////////////////////////////////////////////
	public function compareData() {
		if (empty($this->__snapshot)) return [];
		return array_diff_assoc_recursive($this->__array, $this->__snapshot);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPARE SNAPSHOT DATA WITH CURRENT DATA, RETURNING AN ARRAY OF CHANGES
	////////////////////////////////////////////////////////////////////////////
	public function compareSnap() {
		if (empty($this->__snapshot)) return [];
		return array_diff_assoc_recursive($this->__snapshot, $this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//MAGIC METHOD - CALLED BY VAR_DUMP() WHEN DUMPING AN OBJECT
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function __debugInfo() {
		return $this->__array;
	}




	////////////////////////////////////////////////////////////////////////////
	//PRIVATE MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $__array	= [];
	private $__snapshot	= false;

}
