<?php



if (file_exists('pudl_file_owner')) return;



////////////////////////////////////////////////////////////////////////////////
// VERIFY FILE OWNERSHIP AND GROUP MATCHES
////////////////////////////////////////////////////////////////////////////////
function pudl_file_owner($path) {
	static $owner	= NULL;
	static $group	= NULL;

	if ($owner === NULL  ||  $group === NULL) {
		$paths		= get_included_files();
		$path		= $paths[count($paths)-2];
		$owner		= fileowner($path);
		$group		= filegroup($path);
	}

	if (@fileowner($path) !== $owner  ||  @filegroup($path) !== $group)  {
		throw new Exception(
			"File ownerships do not match: " . $path
		);
		return false;
	}

	return $path;
}




////////////////////////////////////////////////////////////////////////////////
// VALIDATE MAIN ALTAFORM BOOSTRAP FILE AND THIS FILE OWNERSHIP
////////////////////////////////////////////////////////////////////////////////
pudl_file_owner(NULL);
pudl_file_owner(__FILE__);
