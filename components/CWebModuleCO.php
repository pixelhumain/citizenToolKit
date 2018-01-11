<?php
class CWebModuleCO extends CWebModule
{
	private $_assetsUrl;

	private $_version;
	private $_versionDate;
	private $_keywords;
	private $_description;
	private $_pageTitle;

	public function getVersion(){return $this->_version;}
	public function getVersionDate(){return $this->_versionDate;}
	public function getKeywords(){return $this->_keywords;}
	public function getDescription(){return $this->_description;}
	public function getPageTitle(){return $this->_pageTitle;}
	public function getAssetsUrl()
	{
		if ($this->_assetsUrl === null)
	        $this->_assetsUrl = Yii::app()->getAssetManager()->publish(
	            Yii::getPathOfAlias($this->id.'.assets') );
		return $this->_assetsUrl;
	}

	public function beforeControllerAction($controller, $action)
	{
		if (parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}
}