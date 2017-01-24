<?php
class SaveContributorAction extends CAction
{
	/** TODO CDA - TODELETE---- ++*/
    public function run() {
		$controller=$this->getController();
		$res = Project::addContributor($_POST["id"]);
		Rest::json( $res );
	}
}
?>