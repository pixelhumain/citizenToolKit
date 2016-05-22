<?php 
class Element {

	public static function getControlerByCollection ($type) { 

		$ctrls = array(
	    	Organization::COLLECTION => Organization::CONTROLLER,
	    	Person::COLLECTION => Person::CONTROLLER,
	    	Event::COLLECTION => Event::CONTROLLER,
	    	Project::COLLECTION => Project::CONTROLLER,
			News::COLLECTION => News::COLLECTION,
	    	Need::COLLECTION => Need::CONTROLLER,
	    	City::COLLECTION => City::CONTROLLER,
	    	Survey::COLLECTION => Survey::CONTROLLER,
	    	ActionRoom::COLLECTION => ActionRoom::CONTROLLER,
	    	ActionRoom::COLLECTION_ACTIONS => ActionRoom::CONTROLLER,
	    );	    
    	return @$ctrls[$type];
    }

    public static function getLink( $type, $id )
    {	    
    	$link = ""; 
    	if(@$type && @$id && $type != City::COLLECTION){
    		$el = PHDB::findOne ( $type , array( "_id" => new MongoId($id) ) );
	    	$ctrl = self::getControlerByCollection($type);
	    	if( @$el && @$ctrl )
	    		$link = "<a href='javascript:;' onclick='loadByHash(\"#".$ctrl.".detail.id.".$id."\")'>".$el['name']."</a>";
	    }
	    else if($type == City::COLLECTION){
	    	$el = City::getByUnikey($id);
	    	$ctrl = self::getControlerByCollection($type);
	    	if( @$el && @$ctrl )
	    		$link = "<a href='javascript:;' onclick='loadByHash(\"#".$ctrl.".detail.insee.".$el['insee'].".cp.".$el['cp']."\")'>".$el['name']."</a>";
	    }
    	return $link;
    }
}