<?php
/**
 * [actionGetWatcher get the user data based on his id]
 */
class ListAction extends CAction
{
    public function run($type=null, $id=null, $actionType=null)
    {
        $controller=$this->getController();
        //User must be login to do action
        //$userId = Yii::app()->session["userId"];
        //if ( Person::logguedAndValid() ) {
            // Detail array concern satus of voting (sad, like, enjoy) and moderation reason and comment
          //  $detail = (@$_POST["details"]) ? $_POST["details"] : null;
          
        try {
            $res = Action::getList($type , $id, $actionType, @$_POST["indexStep"]);  
        } catch (CTKException $e) {
            $res = array("result" => false, "msg" => $e->getMessage());
        }

            //Notification situations
            //TODO => Move to model ?
            /*if( @$res["result"] && stripos($_POST['action'], "vote") !== false && $_POST['collection'] == Survey::COLLECTION ){
                //get survey parentType if Orga, Project or Event
                $survey = Survey::getById( $_POST['id'] );
                if( @$survey ){
                    $room = ActionRoom::getById( $survey['survey'] );
                    if( in_array( $room["parentType"], array( Organization::COLLECTION, Project::COLLECTION  ) ) )
                        Notification::constructNotification ( ActStr::VERB_VOTE, array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), array( "type"=>Survey::COLLECTION,"id"=> $_POST['id'] ) );
                }
            }      
        } else {
        	$res = array("result" => false, "msg" => Yii::t("common","Please Log in order to vote !"));
        }*/
        if(@$indexStep)
            Rest::json( $res );
        else
            echo $controller->renderPartial("/pod/horizontalList", $res,true);
        Yii::app()->end();
    }
}