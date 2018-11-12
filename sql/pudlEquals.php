<?php


////////////////////////////////////////////////////////////////////////////////
// THE MAIN CUSTOM PUDL SQL QUERY GENERATOR MODIFIER.
// MOST OTHER MODIFIERS INHERIT FROM THIS CLASS AND EXTEND ITS FUNCTIONALITY.
////////////////////////////////////////////////////////////////////////////////
class pudlEquals implements pudlValue, pudlHelper {



	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	// $value		THE PHP VALUE TO COMPARE
	// $compare		THE TYPE OF COMPARISON TO BE MADE
	// $equals		EQUALITY, SUCH AS "EQUALS" VS "NOT EQUALS"
	////////////////////////////////////////////////////////////////////////////
	public function __construct($value=false, $compare=false, $equals='=') {
		$this->value	= $compare === false ? $value : $compare;
		$this->compare	= $compare === false ? $compare : $value;
		$this->equals	= $equals;

		if (is_null($this->value)) {
			if ($equals === '=') {
				$this->equals = ' IS ';
			} else if ($equals === '!=') {
				$this->equals = ' IS NOT ';
			}
		}
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT THIS VALUE TO A STRING
	////////////////////////////////////////////////////////////////////////////
	public function __toString() { return (string) $this->value; }




	////////////////////////////////////////////////////////////////////////////
	// PULL VALUE FROM PUDL STATIC CALL
	// THIS GENERATES A CALL TO A BUILT IN SQL SERVER FUNCTION
	////////////////////////////////////////////////////////////////////////////
	public function __call($name, $arguments) {
		$this->value	= forward_static_call_array(['pudl',$name], $arguments);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// CHANGE EQUALITY TO "NOT EQUALS"
	////////////////////////////////////////////////////////////////////////////
	public function not() {
		$this->equals	= $this->equals === '='
						? '!='
						: (' NOT' . $this->equals);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// PUDL SQL QUERY GENERATOR
	////////////////////////////////////////////////////////////////////////////
	public function pudlValue(pudl $pudl, $quote=true) {
		if (pudl_array($this->value)) {
			return '(' . $pudl->_inSet($this->value) . ')';
		}
		return $pudl->_value($this->value, $quote);
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	public	$value;
	public	$compare;
	public	$equals;
}
