<?php
class CrudAction extends CAction
{
    public function run($action="new")
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") );
        if( !Person::logguedAndValid() )
            return array("result"=>false, "msg"=>Yii::t("common","Please Login First") );
        else{	
			try {
				if($action == "del")
					$res = Collection::update( @$_POST['name'],null,true);
				else if($action == "update")
					$res = Collection::update( @$_POST['oldname'],@$_POST['name']);
				else
					$res = Collection::create( @$_POST['name']);
			} catch (CTKException $e) {
				$res = array( "result" => false , "msg" => $e->getMessage() );
			}
		}
		return Rest::json($res);
    }
}