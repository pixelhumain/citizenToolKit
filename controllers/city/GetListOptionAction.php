<?php
/**
 */
class GetListOptionAction extends CAction
{
	public function run()
    {
        if(isset($_POST['insee']) && isset($_POST['typeData']))
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
                        $chaine = CityOpenData::listOption2($v, $chaine, true, "");
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