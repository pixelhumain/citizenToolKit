<?php
/**
 */
class GetPodOpenDataAction extends CAction
{
	public function run()
    {
        $chaine = "";
        $where = array('_id'  => new MongoId(Yii::app()->session["userId"]) );
        $user = PHDB::find(PHType::TYPE_CITOYEN, $where);
        foreach ($user as $key => $value) {
            $tabPod = $value["preferences"]["dashboards"]["city/opendata"] ;
            foreach ($value["preferences"]["dashboards"]["city/opendata"] as $namePod => $valuePOD){
                $chaine = $chaine . '<div class="col-sm-6 col-xs-12 '.$namePod.'">
                                        <div class="panel panel-white pulsate">
                                            <div class="panel-heading border-light ">
                                                <h4 class="panel-title"> <i class="fa fa-cog fa-spin fa-2x icon-big text-center"></i> Loading Population Section</h4>
                                                <div class="space5"></div>
                                            </div>
                                        </div>
                                    </div>';
            }
            
        }
        Rest::json(array('chaine' => $chaine,
                            'tabPod' => $tabPod));
       
    }
}
?>