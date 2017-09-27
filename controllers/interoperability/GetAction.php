<?php
    class GetAction extends CAction {

        public function run($id = null, $insee = null) {

            if ($id != null) {

                $res = City::getById($id);

            } elseif ($insee != null) {

                $by_insee = true;

                if (strpos($insee, "_") > 0) {
                    $new_insee = substr($insee, (strpos($insee, "_") + 1));
                    if (strpos($insee, "-"))
                        $new_insee = substr($new_insee, 0, (strpos($new_insee, "-")));
                } 
                else { 
                    $by_insee = false;
                    $new_insee = $insee;
                }

                if ($by_insee == false)
                    $res = City::getByPostalCode($new_insee);
                else
                    $res = City::getByInsee($new_insee);

            }

      		if (isset($res)) {
    			Rest::json($res);
    		}

    		Yii::app()->end();
    	}
    }

?>


