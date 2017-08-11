<?php


use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\Token;

class WikidataPutDescriptionAction extends CAction {

    public function run() {

        $controller=$this->getController();

        ini_set('display_errors', 1); 
        require __DIR__ . '/oauthclient-php/vendor/autoload_wiki.php';


        $endpoint = 'https://www.wikidata.org/w/index.php?title=Special:OAuth';
        $redir = 'https://www.wikidata.org/view/Special:OAuth?';

        $conf = new ClientConfig( $endpoint );
        $conf->setRedirURL( $redir );
        $conf->setConsumer( new Consumer( "fcb1af94a2bf2a51a432c60791646ed3", "766fd485b13ea3716e4da9b4ee63b19544e30d9d" ) );

        $accessToken = new Token( "90ae47add9c7c6590f443fd23a17125d", "ee3166d75fd55205a5f55a934f6aa658afcae7fd" );

        $client = new Client( $conf );

        // Make an Exchangedit
        $editToken = json_decode( $client->makeOAuthCall(
            $accessToken,
            'https://www.wikidata.org/w/api.php?action=tokens&format=json'
        ) )->tokens->edittoken;

        $apiParams = array(
        	'action' => 'wbsetdescription',
        	// 'id' => 'Q64476',
            'id' => $_POST['wikidataID'],
        	'language' => 'fr',
            'value' => $_POST['description'],
        	'token' => $editToken,
        );

        $client->setExtraParams( $apiParams ); // sign these too

        $client->makeOAuthCall(
            $accessToken,
            'https://www.wikidata.org/w/api.php',
            true,
            $apiParams
        );

        Rest::json(array("result"=>true, "Vous avez ajouté la description suivante : ".$_POST['description'] . " à l'élément Wikidata suivant : " . $_POST['wikidataID']));

        Yii::app()->end();
    }
}

?>