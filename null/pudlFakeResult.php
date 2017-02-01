<?php


require_once('pudlNullResult.php');


class pudlFakeResult extends pudlNullResult {

	public function __construct($db, $fake) {
		foreach ($fake as $item) {
			$this->fake[] = (object)['name' => $item];
		}

		parent::__construct($db);
	}



	public function listFields() { return $this->fake; }
	public function fields() { return count($this->fake); }


	protected $fake;

}
