<?php

class InteropProposedAction extends CAction {
    public function run() {
        $controller = $this->getController();
    
	    echo $controller->renderPartial("interopProposed",array(),true);

    }
}

?>