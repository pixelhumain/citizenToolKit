<?php

class PushTypeWikidataAction extends CAction {

    public function run() {

		$controller=$this->getController();

		// OBTENTION DU TOKEN 

		$curl3 = curl_init();
		curl_setopt($curl3, CURLOPT_URL, "https://test.wikidata.org/w/api.php?");
		curl_setopt($curl3, CURLOPT_POST, true);
		curl_setopt($curl3, CURLOPT_HTTPHEADER, array('Authorization: oauth_consumer_key="6de4ed0b711655295ecfcc4b807c187d6530c2f4", oauth_token="ad180jjd733klru7"'));
		curl_setopt($curl3, CURLOPT_POSTFIELDS, 'action=query&meta=tokens&format=json'); 
		curl_setopt($curl3, CURLOPT_RETURNTRANSFER, 1);
		$result3 = curl_exec($curl3);
		 
		curl_close($curl3);

		var_dump($result3);

	}
}

?>

