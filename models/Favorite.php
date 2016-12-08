<?php 
class Favorite {

    public static function add($targetId, $targetType)
    {
        
        $person = Person::getById( Yii::app()->session["userId"] );
        $target = Link::checkIdAndType( $targetId, $targetType );
        $favorites=array("favorites.".$targetType.".".$targetId => new MongoDate(time()),"updated"=>time());
        
        $action = '$set';
        $inc = 1;
        $verb = "Added ".$target["name"]." to";
        if( @$person["favorites"][$targetType][$targetId] )
        {
            $action =  '$unset';
            $inc = -1;
            $verb = "Removed ".$target["name"]." from";
            $favorites=array("favorites.".$targetType.".".$targetId => 1);
        }  

        PHDB::update(Person::COLLECTION, 
                       array("_id" => new MongoId(Yii::app()->session["userId"]) ) , 
                       array($action => $favorites));

        PHDB::update($targetType, 
                       array( "_id" => new MongoId($targetId) ) , 
                       array( '$inc' => array( "favoriteCount" => $inc ) ) );
            
        return array("result"=>true,"list"=>$action, "msg"=>"$verb your Favorites with success");
    }

    //$type is a filter of a type of favorite
    public static function get($userId=null, $type=null)
    {
        if(!$userId)
            $userId = Yii::app()->session["userId"];
        $person = Person::getById( $userId );
        $list = array();
        $count = 0;
        foreach ( @$person["favorites"] as $favtype => $value ) 
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
            
        return array("result"=>true, "count"=>$count,"list"=>$list);
    }
}