<?php
    class GetAction extends CAction {

        public function run($insee = null) {

            if (strpos($insee, "_") > 0) {
                $new_insee = substr($insee, (strpos($insee, "_") + 1));
                $new_insee = substr($new_insee, (strpos($new_insee, "-") + 1));
            } 
            else { 
                $new_insee = $insee;
            }

            // $params = array('postalCodes' => array('$elemMatch' => array('postalCode' => $new_insee ) ) );

            // $res = City::getWhere($params);
            $res = City::getByPostalCode($new_insee);

      		if (isset($res)) {
    			Rest::json($res);
    		}

    		Yii::app()->end();
    	}
    }

?>


