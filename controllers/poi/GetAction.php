<?php

class GetAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type=null,$id=null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) { 
        if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
        {
        	 try {
                  $res = array("result" => true, "map" =>Element::getByTypeAndId($type,$id) );
             } catch (CTKException $e) {
                  $res = array("result"=>false, "msg"=>$e->getMessage());
             }
            echo json_encode( $res );  
        }else{
			$controller=$this->getController();
			if( $format == Translate::FORMAT_SCHEMA)
				$bindMap = (empty($id)?TranslateSchema::$dataBinding_allOrganization:TranslateSchema::$dataBinding_organization);
			else if( $format == Translate::FORMAT_NETWORK){
				$bindMap = TranslateNetwork::$dataBinding_poi;
			}
			else
				$bindMap = (empty($id)?TranslateCommunecter::$dataBinding_allOrganization:TranslateCommunecter::$dataBinding_organization);

			//var_dump($bindMap);
			$result = Api::getData($bindMap, $format, Organization::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

			Rest::json($result);
			Yii::app()->end();
        }
    }
}

?>