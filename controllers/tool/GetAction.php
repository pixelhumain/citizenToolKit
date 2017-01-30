<?php
class GetAction extends CAction {

    public function run($what) {
		
		if($what == "mongoId")
			Rest::json( array( "id" => (string)new MongoId() ) );
    }
}

?>