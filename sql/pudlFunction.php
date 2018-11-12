<?php



////////////////////////////////////////////////////////////////////////////////
// USED TO CALL A BUILT IN FUNCTION INSIDE OF THE SQL SERVER
////////////////////////////////////////////////////////////////////////////////
class			pudlFunction
	implements	pudlValue,
				pudlHelper {




	////////////////////////////////////////////////////////////////////////////
	// SAME AS CALLING pudl::sqlfunction()
	// THIS IS A WORK AROUND FOR PHP'S WONKY SCOPING SYSTEM
	////////////////////////////////////////////////////////////////////////////
	public static function __callStatic($name, $arguments) {
		return forward_static_call_array(['pudl', '_'.$name], $arguments);
	}




	////////////////////////////////////////////////////////////////////////////
	// IF CONVERT_TZ RETURNS NULL, MAKE SURE THE TIMEZONE TABLE OF MYSQL IS
	// POPULATED. RUN THE FOLLOWING COMMANDS TO POPULATE THE TABLE.
	// Note that this might need to be ran on ALL MySQL instances in a cluster!
	//	install mysql-community-server-tools
	//	mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p mysql
	////////////////////////////////////////////////////////////////////////////
	public static function timestamp($time) {
		return pudl::convert_tz(
			static::from_unixtime(
				is_object($time) ? $time->time() : ((int)$time)
			),
			new pudlGlobal('time_zone'),
			'UTC'
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// SQL QUERY GENERATOR - USED BY _VALUE()
	////////////////////////////////////////////////////////////////////////////
	public function pudlValue(pudl $pudl, $quote=true) {
		foreach ($this as $property => $value) {
			$query	= '';
			foreach ($value as $item) {
				if (strlen($query)) $query .= ', ';
				$query .= $pudl->_value($item);
			}
			return ltrim($property, '_') . '(' . $query . ')';
		}

		throw new pudlFunctionException($pudl, 'Invalid pudlFunction');
	}

}
