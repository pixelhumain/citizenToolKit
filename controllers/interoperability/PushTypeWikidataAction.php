<?php

class PushTypeWikidataAction extends CAction {

    public function run() {

		$controller=$this->getController();

		// header('Content-Type: application/json');

		// var_dump($_POST);

		// // $token = "fb87c8ce62b427a68453b3030516b1a85922ca79%2B\\";
		// $token = urlencode("6c2257198310f5cab93cf3c0ff8622a45922de93+\\");
		// // $token = urlencode("aa3c083c6a9efcbd6027e422e79852f45922c2d5+\\");

		// // var_dump($token);



		// OBTENTION DU CENTRALAUTH

		// https://en.wikipedia.org/w/api.php?action=centralauthtoken

		// $curl3 = curl_init();
		// curl_setopt($curl3, CURLOPT_URL, "https://test.wikidata.org/w/api.php?action=centralauthtoken");
		// curl_setopt($curl3, CURLOPT_POST, true);
		// // curl_setopt($curl3, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		// // curl_setopt($curl3, CURLOPT_POSTFIELDS, 'action=query&meta=tokens&format=json'); 
		// curl_setopt($curl3, CURLOPT_RETURNTRANSFER, 1);
		// $result3 = curl_exec($curl3);
		 
		// curl_close($curl3);

		// var_dump($result3);



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


		// CHECK UN TOKEN 

		// $url_test = "https://test.wikidata.org/w/api.php?action=checktoken&type=csrf&format=json&token=fb87c8ce62b427a68453b3030516b1a85922ca79%2B%5C";

		// $curl2 = curl_init();

		// curl_setopt($curl2, CURLOPT_URL, "https://test.wikidata.org/w/api.php?");

		// curl_setopt($curl2, CURLOPT_POST, true);
		// curl_setopt($curl2, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		// curl_setopt($curl2, CURLOPT_POSTFIELDS, 'action=checktoken&type=login&token=7816a7b677618043c45a9925f19a49575922e1c9%2B\\&format=json'); 
		// curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
		// $result2 = curl_exec($curl2);
		 
		// curl_close($curl2);
	
		// var_dump($result2);



		// OBTENTION D'UN LOGIN TOKEN


		// $curl2 = curl_init();
		// curl_setopt($curl2, CURLOPT_URL, "https://test.wikidata.org/w/api.php?");
		// curl_setopt($curl2, CURLOPT_POST, true);
		// // curl_setopt($curl2, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		// curl_setopt($curl2, CURLOPT_POSTFIELDS, 'action=checktoken&type=login&token=958cf6708cadd050b166c241859331a85922e4e8%2B%5C&format=json'); 
		// curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
		// $result2 = curl_exec($curl2);
		 
		// curl_close($curl2);
	
		// var_dump($result2);




		// OBTENTION D'UN TOKEN AVEC LES IDETIFIANTS 

		// 958cf6708cadd050b166c241859331a85922e4e8+\

		// $curl5 = curl_init();
		// curl_setopt($curl5, CURLOPT_URL, "https://www.mediawiki.org/w/api.php?");
		// curl_setopt($curl5, CURLOPT_POST, true);
		// // curl_setopt($curl3, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		// curl_setopt($curl5, CURLOPT_POSTFIELDS, 'action=clientlogin&username=Femuraille&password=h8pxfnmxf8&loginreturnurl=http://example.org/&logintoken=958cf6708cadd050b166c241859331a85922e4e8+\\'); 
		// curl_setopt($curl5, CURLOPT_RETURNTRANSFER, 1);
		// $result5 = curl_exec($curl5);
		 
		// curl_close($curl5);

		// var_dump($result5);

	
		// OBTENIR LES INFOS SUR L'USER (CO OU PAS)

		// $curl4 = curl_init();
		// curl_setopt($curl4, CURLOPT_URL, "https://en.wikipedia.org/w/api.php?");
		// curl_setopt($curl4, CURLOPT_POST, true);
		// // curl_setopt($curl4, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		// curl_setopt($curl4, CURLOPT_POSTFIELDS, 'action=query&meta=userinfo&uiprop=rights&format=json'); 
		// curl_setopt($curl4, CURLOPT_RETURNTRANSFER, 1);
		// $result4 = curl_exec($curl4);
		 
		// curl_close($curl4);

		// echo $result4;
		// echo json_encode($result4, true);
		// var_dump($result4);


		// ACTION CLIENT LOGIN 

		// $curl5 = curl_init();
		// curl_setopt($curl5, CURLOPT_URL, "https://en.wikipedia.org/w/api.php?");
		// curl_setopt($curl5, CURLOPT_POST, true);
		// // curl_setopt($curl5, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		// curl_setopt($curl5, CURLOPT_POSTFIELDS, 'action=login&lgname=Femuraille&lgpassword=h8pxfnmxf8&lgtoken=b5305acf62059a67c6f7e5303afc8c245922daf2%2B%5C&format=json'); 
		// curl_setopt($curl5, CURLOPT_RETURNTRANSFER, 1);
		// $result5 = curl_exec($curl5);
		 
		// curl_close($curl5);

		// var_dump($result5);



		// AUTRE EXEMPLE D'ACTION LOGIN CLIENT

		// $curl6 = curl_init();
		// curl_setopt($curl6, CURLOPT_URL, "https://en.wikipedia.org/w/api.php?");
		// curl_setopt($curl6, CURLOPT_POST, true);
		// // curl_setopt($curl5, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		// curl_setopt($curl6, CURLOPT_POSTFIELDS, 'action=clientlogin&loginreturnurl=http://example.com/&logintoken=5305acf62059a67c6f7e5303afc8c245922daf2%2B%5C&username=Femuraille&password=h8pxfnmxf8&rememberMe=1'); 
		// curl_setopt($curl6, CURLOPT_RETURNTRANSFER, 1);
		// $result6 = curl_exec($curl6);
		 
		// curl_close($curl6);

		// var_dump($result6);	




		// AJOUT D'UNE PROPRIETE DANS UN ELEMENT WIKIDATA

		// $curl = curl_init();

		// curl_setopt($curl, CURLOPT_URL, "https://test.wikidata.org/w/api.php?");

		// curl_setopt($curl, CURLOPT_POST, true);
		// curl_setopt($curl, CURLOPT_POSTFIELDS, 'action=wbcreateclaim&entity=Q64477&property=P82&snaktype=value&value={"entity-type":"item","numeric-id":26}&token='.$token.'&format=json'); 
		// curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		// $result = curl_exec($curl);
		 
		// curl_close($curl);

		// var_dump($result);

		// var_dump("TEST PUSH WIKIDATA");

	}
}

?>

