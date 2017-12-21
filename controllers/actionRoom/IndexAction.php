<?php
class IndexAction extends CAction
{

    //http://127.0.0.1/ph/communecter/rooms/index/type/citoyens/id/xxxxxx
    public function run( $type=null, $id= null, $view=null, $archived=null,$fields=null )
    {
        error_log("room index Action ".$type);
        $controller=$this->getController();
        
        $nameParentTitle = "";
        $parent = null;
        if( $type == Project::COLLECTION ) 
            $parent = Project::getById($id);
        else if( $type == Person::COLLECTION ) 
            $parent = Person::getById($id);
        else if( $type == Organization::COLLECTION ) 
            $parent = Organization::getById($id);
        else if( $type == Event::COLLECTION ) 
            $parent = Event::getById($id);
        else if( $type == City::COLLECTION ) {
            $parent = City::getByUnikey($id);
        }
                
    
        if($parent != null && isset($parent['name'])){
            //error_log("test ok ? ".$parent['name']);
            $nameParentTitle = $parent['name'];
        }
        else{
            throw new CTKException("Impossible to find this DDA");
        }

        //gère l'activation du DDA sur project et orga
        // if((!isset($parent["modules"]) || !in_array("survey", $parent["modules"])) 
        //     && $type != City::COLLECTION 
        //     && $type != Person::COLLECTION ){ 
        //     echo $controller->renderPartial("../pod/roomsList" , 
        //                                     array(  "empty"=>true, 
        //                                             "parent" => $parent, 
        //                                             "parentId" => $id, 
        //                                             "parentType" => $type,
        //                                             "type"=>$type,
        //                                             ), true);
        //     return;
        // }

        //$urlParams = ( @$type && @$id ) ? "/type/".$type."/id/".$id : "";

        $rooms = ActionRoom::getAllRoomsByTypeId($type, $id, $archived);
        $discussions = $rooms["discussions"];
        $votes = $rooms["votes"];
        $actions = $rooms["actions"];
        $history = $rooms["history"];
        
        if( $type == City::COLLECTION && count($rooms["votes"]) < 10) 
        {
            //initialisze les premiere category sur un DDA city
            foreach ( OpenData::$categ as $key => $c) 
            {
                PHDB::insert( Survey::PARENT_COLLECTION, array("email" => "contact@communecter.org",
                                                                "name" => $c["name"],
                                                                "type" => ActionRoom::TYPE_VOTE ,
                                                                "parentType" => City::COLLECTION,
                                                                "parentId" => $id,
                                                                "created" => time(),
                                                                "updated" => time(),
                                                                "tags" => $c["tags"],
                                                                "modified" => new MongoDate(time()) ) );
            }
            PHDB::insert( Survey::PARENT_COLLECTION, array("email" => "contact@communecter.org",
                                                            "name" => "Actions divers",
                                                            "type" => ActionRoom::TYPE_ACTIONS ,
                                                            "parentType" => City::COLLECTION,
                                                            "parentId" => $id,
                                                            "created" => time(),
                                                            "updated" => time(),
                                                            "modified" => new MongoDate(time()) ) );
            
            $rooms = ActionRoom::getAllRoomsByTypeId($type, $id, $archived);
            $discussions = $rooms["discussions"];
            $votes = $rooms["votes"];
            $actions = $rooms["actions"];
            $history = $rooms["history"];
        }

        function mySort($a, $b){ 
            if( isset($a['updated']) && isset($b['updated']) ){
                return (strtolower(@$b['updated']) > strtolower(@$a['updated']));
            }else{
                return false;
            }
        }
        
       usort($discussions,"mySort");
       usort($votes,"mySort");
       usort($actions,"mySort");

        $params = array(    "discussions" => $discussions, 
                            "votes" => $votes, 
                            "actions" => $actions, 
                            "history" => $history, 
                            "nameParentTitle" => $nameParentTitle, 
                            "parent" => $parent, 
                            "parentId" => $id, 
                            "parentType" => $type );


        if( isset($actionHistory) )
            $params["history"] = $actionHistory;

		if(Yii::app()->request->isAjaxRequest){
            if($view == "pod"){
                echo $controller->renderPartial("../pod/roomsList" , $params, true);
            }else if($view == "data"){
                $res = array();
                if(@$fields){
                    foreach (explode(",", $fields) as $key => $value) {
                        if(@$params[$value])
                            $res[$value] = $params[$value];
                    }
                }
                else
                    $res = $params;
                Rest::json( $res );
            }else {
                echo $controller->renderPartial("indexDDA" , $params,true);
            }
        }
        else{
            if($view == "data"){
                $res = array();
                if(@$fields){
                    foreach (explode(",", $fields) as $key => $value) {
                        if(@$params[$value])
                            $res[$value] = $params[$value];
                    }
                }
                else
                    $res = $params;
                Rest::json( $res );
            } else
                $controller->render( "index" , $params );
        }
    }
}