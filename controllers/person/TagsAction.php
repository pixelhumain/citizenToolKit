<?php
/**
* retreive dynamically 
*/
class TagsAction extends CAction
{
    public function run() {
    	try {
			$tags = PHDB::findOneById(Person::COLLECTION , Yii::app()->session['userId'] , array( "tags", "activeTags" ) );
			$res = array("result" => true, "tags"=>$tags["tags"]);
		} catch (CTKException $e) {
			$res = array("result" => false, "msg"=>$e->getMessage());
		}

		Rest::json($res);
		exit;
    }
}