<?php
/**
* to create statistic
* Can be launch by cron
*/
class GetStatJsonAction extends CAction
{
    public function run() {

    	$res = array();
        if(isset($_REQUEST['sector']) && isset($_REQUEST['chart'])){
             $sector = $_REQUEST['sector'];
             $chart = $_REQUEST['chart'];
             switch ($sector) {
                case 'citoyens':
                    switch ($chart) {
                        case 'global':
                            $data = PHDB::find('stats');
                            foreach ($data as $key => $value) {
                                $res['x'][] = date("d/m/Y",$value['created']->sec);
                                $res['citoyens'][] = $value['global']['citoyens']['total'];
                            };
                        break;
                        case 'cities':
                            if(isset($_REQUEST['insee'])){
                                $data = PHDB::find('stats', array('cities.citoyens.'.$_REQUEST['insee'] => array('$exists'=>1)));
                                foreach ($data as $key => $value) {
                                    $res['x'][] = date("d/m/Y",$value['created']->sec);
                                    $res['citoyens'][] = $value['cities']['citoyens'][$_REQUEST['insee']];
                                };
                            }
                        break;
                        default:
                            # code...
                            break;
                    }
                   
                break;

                case 'organizations':
                    switch ($chart) {
                        case 'global':
                            $data = PHDB::find('stats');
                            foreach ($data as $key => $value) {
                                $res['x'][] = date("d/m/Y",$value['created']->sec);
                                foreach($value['global']['organizations'] as $name => $number){
                                    if($name != 'total')$res[$name][] = $number;
                                }
                            };
                        break;
                        case 'cities':
                            if(isset($_REQUEST['insee'])){
                                $data = PHDB::findAndSort('stats', array('cities.organizations.'.$_REQUEST['insee'] => array('$exists'=>1)), array('created' => -1), 1);
                                foreach ($data as $key => $value) {
                                    foreach ($value['cities']['organizations'][$_REQUEST['insee']] as $type => $count) {
                                        if($type != 'total')$res['organizations'][$type] = $count;
                                    }
                                }
                            }
                        break;
                        default:
                            # code...
                            break;
                    }
                   
                break;
                case 'events':
                    $data = PHDB::find('stats');
                    foreach ($data as $key => $value) {
                        $res['x'][] = date("d/m/Y",$value['created']->sec);
                        foreach($value['global']['events'] as $name => $number){
                            if($name != 'total')$res[$name][] = $number;
                        }
                    }
                 break;
                 case 'projects':
                    $data = PHDB::find('stats');
                    foreach ($data as $key => $value) {
                        $res['x'][] = date("d/m/Y",$value['created']->sec);
                        $res['projects'][] = $value['global']['projects']['total'];
                    }
                 break;
                 default:
                     # code...
                     break;
            }
        }
       

     //    if(isset($_REQUEST['global'])){
     //        if($_REQUEST['global'] == 'all'){
                
     //        }
     //    }elseif(isset($_REQUEST['organizations'])){
    	// 	if($_REQUEST['organizations'] == 'global'){
    	// 		$data = PHDB::find('stats');
		   //  	foreach ($data as $key => $value) {
		   //  		$res['x'][] = date("d/m/Y",$value['created']->sec);
		   //  		foreach($value['global']['organizations'] as $name => $number){
			  //   		if($name != 'total')$res[$name][] = $number;
			  //   	}
		   //  	};
    	// 	}
    	// }
    	// elseif(isset($_REQUEST['citoyens'])){
    	// 	if($_REQUEST['citoyens'] == 'global'){
    			
    	// 	}
    	// }

    	
    	Rest::json($res);

    }
}
