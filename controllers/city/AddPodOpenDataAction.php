<?php
/**
 */
class AddPodOpenDataAction extends CAction
{
	public function run($modify)
    {
        $where = array('_id'  => new MongoId(Yii::app()->session["userId"]) );
        $user = PHDB::find(PHType::TYPE_CITOYEN, $where);
        $error = false ;
       
        if($modify == "add")
        {
            foreach ($user as $key => $value) {
                $test = $value["preferences"]["dashboards"]["city/opendata"] ;
                foreach ($value["preferences"]["dashboards"]["city/opendata"] as $namePod => $valuePOD){
                    if($valuePOD["url"] == $_POST['urlPod'])
                    {
                        $error = true ;
                        $msgError = "Ce graphique existes déjà.";
                        break ;
                    }
                }
            }
        }

        if($error == true)
        {   
            Rest::json(array('result' => false,
                            'msgError' => $msgError));
        }
        else
        {
            $msgSuccess = "";
            if(isset($_POST['tabPod']))
                $tabPod = $_POST['tabPod'];
            else
                $tabPod = array();

            /*var_dump($tabPod);
            var_dump(count($test));*/

            if($modify == "add")
            {
                $infopod['title'] = $_POST['titlePod'];
                $infopod['url'] = $_POST['urlPod'];
                $tabPod["pod".(count($test)+1)] = $infopod;
                $msgSuccess = "Votre graphique a été ajouté.";
            }
            else if($modify == "delete")
            {
                $msgSuccess = "Votre graphique a été supprimer";
            }

            $addpod = PHDB::update(City::CITOYENS,
                            array("_id"=>new MongoId(Yii::app()->session["userId"])), 
                            array('$set' => array('preferences.dashboards.city/opendata' => $tabPod)),
                            array("upsert" => true));
            Rest::json(array('result' => true,
                            'msgSuccess' => $msgSuccess));
                            
            //Rest::json(array('result' => true));
        }
        
    }
}
?>