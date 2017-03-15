<?php

class Search {

	/**
	 * Find elements of collection based on criteria (field contains value)
	 * By default the criterias will be separated bay a "OR"
	 * @param array $criterias array (field=>value)
	 * @param String $sortOnField sort on this field name
	 * @param integer $nbResultMax number of results max to return
	 * @return array of elements of collection
	 */
	public static function findByCriterias($collection, $criterias, $sortOnField="", $nbResultMax = 10) {

	  	$seprator = '$or';
	  	$query = array();

	  	//Add the criterias 
	  	foreach ($criterias as $field => $value) {
	  		$aCriteria = array();
	  		$aCriteria[$field] = new MongoRegex("/$value/i");
	  		array_push($query, $aCriteria);
	  	}

	  	if (count($criterias) > 1) {
	  		$where = array($seprator => $query);
	  	} else {
	  		$where = $query;
	  	}

	  	$res = PHDB::findAndSort($collection, $where, array($sortOnField => 1), $nbResultMax);
	  	//$res = PHDB::find($collection, $where);
	  	return $res;
	 }


	 


    static public function accentToRegex($text)
	{

		$from = str_split(utf8_decode('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿ'));
		$to   = str_split(strtolower('SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyy'));
		//‘ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿaeiouçAEIOUÇ';
		//‘SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyyaeioucAEIOUÇ';
		$text = utf8_decode($text);
		$regex = array();

		foreach ($to as $key => $value)
		{
			if (isset($regex[$value]))
				$regex[$value] .= $from[$key];
			else 
				$regex[$value] = $value;
		}

		foreach ($regex as $rg_key => $rg)
		{
			$text = preg_replace("/[$rg]/", "_{$rg_key}_", $text);
		}

		foreach ($regex as $rg_key => $rg)
		{
			$text = preg_replace("/_{$rg_key}_/", "[$rg]", $text);
		}
		return utf8_encode($text);
	}




    static public function removeEmptyWords($search)
	{
        $stopwords = array(" ", "", "-", "?", "!", ",", ".", "/", "le", "la", "les", "un", "une", "des", "mon", "ton", "son", "pour", 
                            "à", "a", "d", "d'", "de", "notre", "votre", "leur", "leurs", "mes", "tes", "ses", "du");

        $arraySearch = explode(" ", $search);
        $resArraySearch = array();
        foreach ($arraySearch as $key => $word) {
            if(!in_array($word, $stopwords)){
                $resArraySearch[] = $word;
            }
        }

        $resStr = "";
        foreach ($resArraySearch as $key => $word) {
            if($resStr != "") $resStr .= " ";
            $resStr .= $word;
        }
        return $resStr;
    }

}