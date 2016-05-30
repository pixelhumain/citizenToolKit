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
                            $data = PHDB::find('stats', array('global.citoyens.total' => array('$exists' => 1)));
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
                case 'links':
                    $data = PHDB::find('stats', array('global.links' => array('$exists' => 1)));
                    foreach ($data as $key => $value) {
                        $res['x'][] = date("d/m/Y",$value['created']->sec);
                        foreach($value['global']['links'] as $name => $number){
                            if($name != 'total')$res[$name][] = $number;
                        }
                    };
                break;
                case 'organizations':
                    switch ($chart) {
                        case 'global':
                            $data = PHDB::find('stats', array('global.organizations' => array('$exists' => 1)));
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
                    $data = PHDB::find('stats', array('global.events' => array('$exists' => 1)));
                    foreach ($data as $key => $value) {
                        $res['x'][] = date("d/m/Y",$value['created']->sec);
                        foreach($value['global']['events'] as $name => $number){
                            if($name != 'total')$res[$name][] = $number;
                        }
                    }
                 break;
                 case 'projects':
                    $data = PHDB::find('stats', array('global.projects.total' => array('$exists' => 1)));
                    foreach ($data as $key => $value) {
                        $res['x'][] = date("d/m/Y",$value['created']->sec);
                        $res['projects'][] = $value['global']['projects']['total'];
                    }
                 break;
                 case 'actionRooms':
                    $data = PHDB::find('stats', array('global.actionRooms' => array('$exists' => 1)));
                    foreach ($data as $key => $value) {
                        $res['x'][] = date("d/m/Y",$value['created']->sec);
                        foreach($value['global']['actionRooms'] as $name => $number){
                            if($name != 'total')$res[$name][] = $number;
                        }
                    };
                 break;
                 case 'survey':
                    $data = PHDB::find('stats', array('global.survey' => array('$exists' => 1)));
                    foreach ($data as $key => $value) {
                        $res['x'][] = date("d/m/Y",$value['created']->sec);
                        $res['survey'][] = $value['global']['survey']['total'];
                    };
                 break;
                 case 'modules':
                    $data = PHDB::find('stats', array('global.modules' => array('$exists' => 1)));
                    foreach ($data as $key => $value) {
                        $res['x'][] = date("d/m/Y",$value['created']->sec);
                        foreach($value['global']['modules'] as $name => $number){
                            if($name != 'total')$res[$name][] = $number;
                        }
                    }
                 break;

                 //Log is a bit different because it's not included in global values
                 case 'logs':
                    switch ($chart) {
                        case 'global':
                            $data = PHDB::find('stats', array('logs' => array('$exists' => 1)));
                            if(is_array($data))foreach ($data as $key => $stat) {
                                $res['x'][] = date("d/m/Y",$stat['created']->sec);
                                $total = 0;
                                if(is_array($stat['logs']))foreach ($stat['logs'] as $action => $listResult) {
                                    if(is_array($listResult)){
                                        foreach ($listResult as $value => $total) {
                                            $total += $total;
                                        }
                                    }
                                    else{
                                        $total += $listResult;
                                    } 
                                }
                                $res['logs'][] = $total; 
                            }
                        break;
                        case 'action':
                            $paramAction = str_replace("_", "/", $_REQUEST['action']);
                            $data = PHDB::find('stats', array('logs.'.$paramAction => array('$exists' => 1)));
                            if(is_array($data))foreach ($data as $key => $stat) {
                                $total = 0;
                                $res['x'][] = date("d/m/Y",$stat['created']->sec);
                                if(is_array($stat['logs'][$paramAction])){
                                    foreach ($stat['logs'][$paramAction] as $result => $count) {
                                        $res[$result][] = $count;
                                    }
                                }
                                else{
                                    $res['logs'][] = $stat['logs'][$paramAction];
                                }
                                
                            }
                         break;
                    }
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
