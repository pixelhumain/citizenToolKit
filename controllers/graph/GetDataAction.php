<?php
class GetDataAction extends CAction
{
    public function run($id=null, $type=null)
    {
    	if(!@$id && isset(Yii::app()->session["userId"])){
    		$id = Yii::app()->session["userId"];
    		$type = Person::COLLECTION;
    	}
        $item = PHDB::findOne( $type ,array("_id"=>new MongoId($id)));
  		echo Rest::json($item);
    }
}