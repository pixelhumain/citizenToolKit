<?php
class AbuseProcessAction extends CAction
{
    public function run() {
        $controller=$this->getController();

        //User must be login to do action
        $userId = Yii::app()->session["userId"];
        if ( Person::logguedAndValid() ) { 
            if ($_POST['action'] == Action::ACTION_REPORT_ABUSE) {
                $res = Comment::reportAbuse($userId, $_POST['id'], $_POST['reason']);
            } else {
                $res = Comment::changeStatus($userId , $_POST['id'], $_POST['action']);
            }
        } else {
            $res = array("result" => false, "msg" => Yii::t("common","Please Log in order to vote !"));
        }
        Rest::json( $res );
        Yii::app()->end();
    }
}