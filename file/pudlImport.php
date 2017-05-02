<?php


require_once(__DIR__.'/../pudlObject.php');


abstract class	pudlImport
	extends		pudlObject {




	////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	function __construct($type=false) {
		if (!empty($type)) $this->translate($type);
	}




	////////////////////////////////////////////////////////////////////////////
	//CALLED AFTER PARSE() - RETURN TRUE FOR VALID, FALSE FOR INVALID
	////////////////////////////////////////////////////////////////////////////
	public function validate() { return true; }




	////////////////////////////////////////////////////////////////////////////
	//CALLED AFTER VALIDATE() - GREAT FOR INHERITING THIS CLASS
	////////////////////////////////////////////////////////////////////////////
	public function import() { return true; }




	////////////////////////////////////////////////////////////////////////////
	//PARSING DONE, NOW VALIDATE AND PROCESS
	////////////////////////////////////////////////////////////////////////////
	protected function importing() {
		//VALIDATE CONTENTS OF FILE
		$valid = $this->validate();

		if (!empty($this->errors)) {
			throw new pudlException(
				"FILE FAILED DATA VALIDATION\n" .
				implode("\n", $this->errors)
			);
			return false;
		}

		//PROCESS CONTENTS OF FILE
		return $valid ? $this->import() : false;
	}




	////////////////////////////////////////////////////////////////////////////
	//SET THE TRANSLATION TYPE
	////////////////////////////////////////////////////////////////////////////
	public function translate($type) {
		$this->translate = $type;
	}




	////////////////////////////////////////////////////////////////////////////
	//PARSE THE FILE
	////////////////////////////////////////////////////////////////////////////
	abstract public function parse($file=false);




	////////////////////////////////////////////////////////////////////////////
	//PARSING DONE, NOW VALIDATE AND PROCESS
	////////////////////////////////////////////////////////////////////////////
	protected function _importing() {
		//VALIDATE CONTENTS OF FILE
		$valid = $this->validate();

		if (!empty($this->errors)) {
			throw new pudlException(
				"FILE FAILED DATA VALIDATION\n" .
				implode("\n", $this->errors)
			);
			return false;
		}

		//PROCESS CONTENTS OF FILE
		return $valid ? $this->import() : false;
	}




	////////////////////////////////////////////////////////////////////////////
	//RETURNS: STRING=NEW NAME - TRUE=IGNORE - FALSE=ERROR
	////////////////////////////////////////////////////////////////////////////
	protected function _translate($name) {
		global $db;

		if ($name instanceof SimpleXMLElement) {
			$name = (string) $name;
		}

		if (!is_string($name)) {
			return $this->invalid('Invalid Name: ' . print_r($name,true));
		}

		if (empty($name)) return true;
		if (empty($this->translate)) return $name;

		if (is_array($this->translate)) {
			if (!isset($this->translate[$name])) return false;
			return $this->translate[$name];
		}

		$item = $db->cell([
			's' => 'pudl_translate',
			't' => 'pudl_translate_type'
		], 'string_new', [
			's.translate_type_id=t.translate_type_id',
			't.translate_type'	=> $this->translate,
			's.string_old'		=> $name,
		]);

		if ($item !== false) return (empty($item)) ? $name : $item;

		return false !== $db->cell([
			's' => 'pudl_translate',
			't' => 'pudl_translate_type'
		], 'string_new', [
			's.translate_type_id=t.translate_type_id',
			's.string_old'		=> $name,
		]);
	}




	////////////////////////////////////////////////////////////////////////////
	//STORE ERROR MESSAGE
	////////////////////////////////////////////////////////////////////////////
	protected function invalid($text, $row=0) {
		if ($row) $text = '• ROW: ' . $row . ' - ' . $text;
		$this->errors[] = $text;
		return false;
	}




	protected $header		= [];
	protected $errors		= [];
	protected $translate	= false;

}
