<?php
class EditAction extends CAction
{
    public function run($id)
    {
        $res = array( "result" => false , "content" => Yii::t("common","Something went wrong!") );
        if(Yii::app()->request->isAjaxRequest && isset( $_POST["id"]) )
        {
          $event = (isset($_POST["id"])) ? PHDB::findOne( PHType::TYPE_EVENTS,array("_id"=>new MongoId($_POST["id"]))) : null;
        
          if($event)
          {
            $memberEmail = $_POST['email'];

            if($_POST['type'] == "persons"){
              $memberType = PHType::TYPE_CITOYEN;
            }else{
              $memberType = Organization::COLLECTION;
            }
            if(isset($_POST["id"]) && $_POST["id"] != ""){
              $memberEmailObject = PHDB::findOne( $type , array("_id" =>new MongoId($_POST["id"])), array("email"));
              $memberEmail = $memberEmailObject['email'];
            }

            if(preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$memberEmail))
            {
              
              $member = PHDB::findOne($memberType, array("email"=>$_POST['email']));
              
              if( !$member )
              {
                if($_POST['type'] == "persons"){
                 $member = array(
                 'name'=>$_POST['name'],
                 'email'=>$memberEmail,
                 'invitedBy'=>Yii::app()->session["userId"],
                 'tobeactivated' => true,
                 'created' => time(),
                 'links'=>array( 'events' => array($_POST["id"] =>array("type" => $type,
                                              "tobeconfirmed" => true,
                                              "invitedBy" => Yii::app()->session["userId"]
                                            )
                                  )
                      )
                 );
                  Person::createAndInvite($member);
                 } else {
                   $member = array(
                   'name'=>$_POST['name'],
                   'email'=>$memberEmail,
                   'invitedBy'=>Yii::app()->session["userId"],
                   'tobeactivated' => true,
                   'created' => time(),
                   'type'=>'Group',
                   'links'=>array( 'events' => array($_POST["id"] =>array("type" => $type,
                                              "tobeconfirmed" => true,
                                              "invitedBy" => Yii::app()->session["userId"]
                                            )
                                  )
                      )
                   );

                   Organization::createAndInvite($member);
                 }

                Link::connect($_POST["id"], PHType::TYPE_EVENTS,$member["_id"], $type, Yii::app()->session["userId"], "attendees" );
                $res = array("result"=>true,"msg"=>"Vos données ont bien été enregistré.","reload"=>true);

              }else{

                if( isset($event['links']["events"]) && isset( $event['links']["events"][(string)$member["_id"]] ))
                  $res = array( "result" => false , "content" => "member allready exists" );
                else {
                  Link::connect($member["_id"], $type, $_POST["id"], PHType::TYPE_EVENTS, Yii::app()->session["userId"], "events" );
                  Link::connect($_POST["id"], PHType::TYPE_EVENTS, $member["_id"], $type, Yii::app()->session["userId"], "attendees" );
                  $res = array("result"=>true,"msg"=>Yii::t("common","Your data has been saved!"),"reload"=>true);
                }
              }
            }else
              $res = array( "result" => false , "content" => "email must be valid" );
          }
        }
        Rest::json( $res );
    }
}