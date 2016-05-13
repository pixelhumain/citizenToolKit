<?php
/**
* to create statistic
* Can be launch by cron
*/
class GetStatJsonAction extends CAction
{
    public function run() {


    	$res = array();
    	if(isset($_REQUEST['organizations'])){
    		if($_REQUEST['organizations'] == 'global'){
    			$data = PHDB::find('stats');
		    	foreach ($data as $key => $value) {
		    		$res['x'][] = date("d/m/Y",$value['created']->sec);
		    		foreach($value['global']['organizations'] as $name => $number){
			    		if($name != 'total')$res[$name][] = $number;
			    	}
		    	};
    		}
    	}
    	elseif(isset($_REQUEST['citoyens'])){
    		if($_REQUEST['citoyens'] == 'global'){
    			$data = PHDB::find('stats');
		    	foreach ($data as $key => $value) {
		    		$res['x'][] = date("d/m/Y",$value['created']->sec);
		    		$res['citoyens'][] = $value['global']['citoyens']['total'];
		    	};
    		}
    	}

    	
    	Rest::json($res);

    }
}
