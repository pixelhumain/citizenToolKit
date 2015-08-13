<?php
/**
 */
class GetListOptionAction extends CAction
{
	public function run()
    {
        if(isset($_POST['insee']) && isset($_POST['typeData']) && isset($_POST['name_id']))
        {
            $where = array("insee"=>$_POST['insee'], $_POST['typeData'] => array( '$exists' => 1 ));
            $fields = array($_POST['typeData']);
            $option = City::getWhereData($where, $fields);
            $chaine = "" ;
            foreach ($option as $key => $value) 
            {
                foreach ($value as $k => $v) 
                {
                    if($k == $_POST['typeData'])
                    {
                        $chaine = CityOpenData::listOption($v, $chaine, true, $_POST['name_id']);
                    }   
                }
            }
            Rest::json($chaine);
        }
        else
        {
            Rest::json(array('result' => false));
        }
    }
}