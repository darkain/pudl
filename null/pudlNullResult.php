<?php


////////////////////////////////////////////////////////////////////////////////
// EQUIV TO /dev/null - NOTHING IS READ, NOTHING IS WRITTEN, ANYWHERE
////////////////////////////////////////////////////////////////////////////////
class pudlNullResult extends pudlResult {

	////////////////////////////////////////////////////////////////////////////
	// REQUIRED METHODS, RETURN DEFAULT VALUES FOR ALL
	////////////////////////////////////////////////////////////////////////////
	public function free()						{}
	public function cell($row=0, $column=0)		{ return false; }
	public function count()						{ return 0; }
	public function fields()					{ return false; }
	public function getField($column)			{ return false; }
	public function seek($row)					{}
	public function row()						{ return false; }
	public function error()						{ return 0; }
	public function errormsg()					{ return ''; }

}
