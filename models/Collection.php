<?php 
class Collection {

    public static function create($name)
    {
        
        $person = Person::getById( Yii::app()->session["userId"] );
        $action = '$set';        
        PHDB::update(Person::COLLECTION, 
                       array("_id" => new MongoId(Yii::app()->session["userId"]) ) , 
                       array($action => array("collections.".$name => new stdClass() )));

        return array("result"=>true, "msg"=>Yii::t("common","Collection {what} created with success",array("{what}"=>$name)));
    }

    public static function update($name,$newName,$del=false)
    {
        
        $person = Person::getById( Yii::app()->session["userId"] );     

        if( isset($person["collections"][$name]) )
        {
            $actions = array();
            $action = "deleted";
            if(!$del){
                $actions['$set'] = array("collections.".$newName => $person["collections"][$name]);
                $action = "updated";
            }

            $actions['$unset'] = array("collections.".$name => true);
            PHDB::update(Person::COLLECTION, 
                           array("_id" => new MongoId(Yii::app()->session["userId"]) ) , 
                           $actions
                           );
            return array("result"=>true, "msg"=>Yii::t("common","Collection {what} ".$action." with success",array("{what}"=>$name)));
        } else 
            return array("result"=>false, "collection"=>"collections.".$name, "msg"=>"Collection $name doesn't exist");
        
    }

    public static function add($targetId, $targetType,$collection="favorites")
    {
        
        $person = Person::getById( Yii::app()->session["userId"] );
        $target = Element::checkIdAndType( $targetId, $targetType );
        $collections=array("collections.".$collection.".".$targetType.".".$targetId => new MongoDate(time()),"updated"=>time());
        
        $action = '$set';
        $inc = 1;
        $verb = "added";
        $linkVerb=Yii::t("common","to")." ".$collection;
        if($collection=="favorites")
            $linkVerb=Yii::t("common","to favorites");
        
        if( @$person["collections"][$collection][$targetType][$targetId] )
        {
            $action =  '$unset';
            $inc = -1;
            $verb = "removed";
            $linkVerb=Yii::t("common","from")." ".$collection;
            if($collection=="favorites")
                $linkVerb=Yii::t("common","from favorites");
        
            $collections=array("collections.".$collection.".".$targetType.".".$targetId => 1);
        }  

        PHDB::update(Person::COLLECTION, 
                       array("_id" => new MongoId(Yii::app()->session["userId"]) ) , 
                       array($action => $collections));

        PHDB::update($targetType, 
                       array( "_id" => new MongoId($targetId) ) , 
                       array( '$inc' => array( "collectionCount" => $inc ) ) );
            
        return array("result"=>true,"list"=>$action, "msg"=>Yii::t("common", "{what} ".$verb." {where} with success",array("{what}"=>$target["name"],"{where}"=>$linkVerb)));
    }

    //$type is a filter of a type of favorite
    public static function get($userId=null, $type=null,$collection="favorites")
    {
        if(!$userId)
            $userId = Yii::app()->session["userId"];
        $person = Person::getById( $userId );
        $list = array();
        $count = 0;
        if(@$person["collections"][$collection]){
            foreach ( @$person["collections"][$collection] as $favtype => $value ) 
            {
                $ids = array();
                if(!$type || $type == $favtype )
                {
                    foreach ($value as $id => $date) 
                    {
                        array_push($ids, new MongoId($id) );
                    }
                    if( count($ids) > 0)
                    {
                        $count += count($ids);
                        $list[$favtype] = PHDB::find($favtype,array( "_id" => array( '$in'=>$ids ) ));//,array("name","tags","profilMediumImageUrl")
                    }
                }
            }
        }
            
        return array("result"=>true, "count"=>$count,"list"=>$list);
    }


    public static function createDocument($targetId,$targetType,$name,$colType="collections",$docType=null)
    {
        
        $target = Element::getByTypeAndId( $targetType, $targetId);
        if(!empty($target)){   
            $pathToCreate=$colType.".";
            if($docType!=null)
                $pathToCreate.=$docType.".";
            $pathToCreate.=$name;
            PHDB::update($targetType, 
                           array("_id" => new MongoId($targetId) ) , 
                           array('$set' => array($pathToCreate => array("updated"=>new MongoDate(time())) )));

            return array("result"=>true, "msg"=>Yii::t("common","Collection {what} created with success",array("{what}"=>$name)));
        } else 
            return array("result"=>false,  "msg"=>Yii::t("common","Something went wrong"));
        
    }
    //////////////////////////////BOUBOULLLEEEEE - MANAGE PORTFOLIO//////////////////////////////////
    public static function updateCollectionNameDocument($targetId,$targetType,$name,$newName,$colType="collections",$docType=null)
    {
        $target=Element::getElementSimpleById($params["target"]["id"], $params["target"]["type"],null, array($colType));   
        $targetCollection=$target[$colType];
        $pathToUp=$colType.".";
        if($docType != null && @$targetCollection[$docType]){
            $targetCollection=$targetCollection[$docType];
            $pathToUp.=$docType.".";
        }
        if( @$targetCollection[$name] )
        {
            $actions = array();
            $actions['$set'] = array($pathToUp.$newName => array("updated"=>new MongoDate(time())));
            $actions['$unset'] = array($pathToUp.$name => true);
            $findListDocument=Document::updateCollectionDocument($targetId,$targetType,$name,$newName,$docType);
            PHDB::update($targetType, 
                           array("_id" => new MongoId($targetId) ) , 
                           $actions
                           );
            return array("result"=>true, "msg"=>Yii::t("common","Collection {what} updated with success",array("{what}"=>$name)), "newName"=> $newName);
        } else 
            return array("result"=>false, "collection"=>"collections.".$name, "msg"=>Yii::t("common","Collection {what} doesn't exist",array("{what}"=>$name)));
        
    }
    public static function deleteDocument($targetId,$targetType,$name,$colType="collections",$docType=null,$subtype=null)
    {
        $target=Element::getElementSimpleById($params["target"]["id"], $params["target"]["type"],null, array($colType));   
        $targetCollection=$target[$colType];
        $pathToUp=$colType.".";
        if($docType != null && @$targetCollection[$docType]){
            $targetCollection=$targetCollection[$docType];
            $pathToUp.=$docType.".";
        }
        if( @$targetCollection[$name] )
        {
            $actions = array();
            $actions['$unset'] = array($pathToUp.$name => true);
            PHDB::update($targetType, 
                           array("_id" => new MongoId($targetId) ) , 
                           $actions
                           );
            if($docType==Document::COLLECTION){
                Document::removeAllDocument($params["target"]["id"], $params["target"]["type"],$name,$docType);
            }
            return array("result"=>true, "msg"=>Yii::t("common","Collection {what} deleted with success",array("{what}"=>$name)));
        } else 
            return array("result"=>false, "collection"=>"collections.".$name, "msg"=>Yii::t("common","Collection {what} doesn't exist",array("{what}"=>$name)));
        
    }
    public static function deleteFromCollection($targetId,$targetType,$fileId,$colType="collections",$docType=null,$nameCol=array())
    {
        $target=Element::getElementSimpleById($params["target"]["id"], $params["target"]["type"],null, array($colType));   
        $targetCollection=$target[$colType];
        $pathToUp=$colType.".";
        if($docType != null && @$targetCollection[$docType]){
            $targetCollection=$targetCollection[$docType];
            $pathToUp.=$docType.".";
        }
        if($nameCol != null && @$targetCollection[$nameCol]){
            $targetCollection=$targetCollection[$docType];
            $pathToUp.=$nameCol.".";
        }
        if( @$targetCollection[$fileId] )
        {
            $actions = array();
            $actions['$unset'] = array($pathToUp.$name => true);
            PHDB::update($targetType, 
                           array("_id" => new MongoId($targetId) ) , 
                           $actions
                           );
            return array("result"=>true, "msg"=>Yii::t("common",$docType." deleted with success",array("{what}"=>$name)));
        } else 
            return array("result"=>false, "msg"=>Yii::t("common","Something went wrong")); 
    }
    public static function addDocumentToColection($ids,$nameCol=null)
    {
        foreach($ids as $id){
            Document::moveDocumentToCollection($id, $nameCol);
        }     
        return array("result"=>true, "msg"=>Yii::t("common","Documents added with success to {what}",array("{what}"=>$nameCol))); 
    }
}