<?php

class CTKAction extends CAction {
    
    protected $currentUserId;
    
    /**
     * Constructor.
     * @param CController $controller the controller who owns this action.
     * @param string $id id of the action.
     */
    public function __construct($controller,$id) {
        parent::__construct($controller,$id);
        // if()
        //     exit;
        $this->currentUserId = Yii::app()->session["userId"];
    }

    /**
     * Check if the user is loggued and his roles are ok
     * @return true if the current user is loggued and valid 
     */
    public function userLogguedAndValid() 
    {
        if (isset(Yii::app()->session["userId"])) {
            return array( "user" => Yii::app()->session["userId"] );
        } else {
            return false;
        }
    }

    function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * get access token from header
     * */
    function getCheckBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $app = PHDB::findOne( Applications::COLLECTION, array("token"=>$matches[1]) );
                if( @$app )
                    return true;
            }
        }
        return null;
    }

}