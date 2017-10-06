<?php

class CreateAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		$tpl = $controller->renderPartial('application.views.pdf.test', array(), true);
		Pdf::createPdf($tpl);
		
    }
}
?>