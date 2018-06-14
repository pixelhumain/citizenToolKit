<?php
class SendMailNotifAction extends CAction {
	

	public function run() {
		$book = Bookmark::sendMailNotif();
		Rest::json($book);
	}
}