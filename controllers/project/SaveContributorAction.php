<?php
class SaveContributorAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		//$res = array( "result" => false , "content" => Yii::t("common", "Something went wrong!") );
		if(isset( $_POST["id"]) )
		{
			$project = (isset($_POST["id"])) ? PHDB::findOne( PHType::TYPE_PROJECTS,array("_id"=>new MongoId($_POST["id"]))) : null;
			if($project)
			{
				if(preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$_POST['email']))
				{
					if($_POST['type'] == "citoyens"){
						$member = PHDB::findOne( Person::COLLECTION , array("email"=>$_POST['email']));
						$memberType = Person::COLLECTION;
					}
					else
					{
						$member = PHDB::findOne( Organization::COLLECTION , array("email"=>$_POST['email']));
						$memberType = Organization::COLLECTION;
					}

					if( !$member )
					{
						if($_POST['type'] == "citoyens"){
							$member = array(
								'name'=>$_POST['name'],
								'email'=>$_POST['email'],
								'invitedBy'=>Yii::app()->session["userId"],
								'created' => time()
						 	);
						 	$memberId = Person::createAndInvite($member);
						 	$isAdmin = (isset($_POST["contributorIsAdmin"])) ? $_POST["contributorIsAdmin"] : false;
						  	if ($isAdmin == "1") {
								$isAdmin = true;
							} else {
								$isAdmin = false;
							}
					 	} else {
							$member = array(
								'name'=>$_POST['name'],
								'email'=>$_POST['email'],
								'invitedBy'=>Yii::app()->session["userId"],
								'created' => time(),
								'type'=> $_POST["organizationType"]
							);

							$memberId = Organization::createAndInvite($member);
							$isAdmin = false;
						}
						$member["id"]=$memberId["id"];
						Link::connect($memberId["id"], $memberType,$_POST["id"], PHType::TYPE_PROJECTS, Yii::app()->session["userId"], "projects",$isAdmin);		
						Link::connect($_POST["id"], PHType::TYPE_PROJECTS,$memberId["id"], $memberType, Yii::app()->session["userId"], "contributors",$isAdmin );
						$res = array("result"=>true,"msg"=>Yii::t("common", "Your data has been saved"),"member"=>$member, "reload"=>true);
					}else{
						if( isset($project['links']["contributors"]) && isset( $project['links']["contributors"][(string)$member["_id"]] ))
							$res = array( "result" => false , "content" => "member allready exists" );
						else {
							$isAdmin = (isset($_POST["contributorIsAdmin"])) ? $_POST["contributorIsAdmin"] : false;
							if ($isAdmin == "1") {
								$isAdmin = true;
							} else {
								$isAdmin = false;
							}
							Link::connect($member["_id"], $memberType, $_POST["id"], PHType::TYPE_PROJECTS, Yii::app()->session["userId"], "projects",$isAdmin);
							Link::connect($_POST["id"], PHType::TYPE_PROJECTS, $member["_id"], $memberType, Yii::app()->session["userId"], "contributors",$isAdmin);
							$res = array("result"=>true,"msg"=>Yii::t("common", "Your data has been saved"),"member"=>$member,"reload"=>true);
						}
					}
				}else
					$res = array( "result" => false , "content" => "email must be valid" );
			}
		}
		Rest::json( $res );
	}
}
?>