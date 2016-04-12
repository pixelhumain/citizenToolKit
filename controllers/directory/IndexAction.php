<?php
class IndexAction extends CAction
{
    public function run()
    {
	    $this->layout = "//layouts/mainDirectory";
		// $this->render("dir/indexDirectory");
		$this->render("simplyDirectory");

    }
}