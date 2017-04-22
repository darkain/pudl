<?php


require_once('pudlObject.php');



class		pudlCollection
	extends	pudlObject {




	////////////////////////////////////////////////////////////////////////////
	//FORWARD METHOD CALL TO ALL OBJECTS WITHIN COLLECTION
	////////////////////////////////////////////////////////////////////////////
	public function __call($name, $arguments) {
		$return	= [];
		$list	= $this->_get();

		foreach ($list as $item) {
			$return[] = call_user_func_array(
				[$item, $name],
				$arguments
			);
		}

		return $return;
	}

}
