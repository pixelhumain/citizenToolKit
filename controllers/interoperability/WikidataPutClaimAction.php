<?php

use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\Token;

class WikidataPutClaimAction extends CAction {

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

       if ($_POST['claim_value'] == "location") {
            $apiParams = array(
                'action' => 'wbcreateclaim',
                'entity' => $_POST['wikidataID'],
                'property' => 'P31',
                'snaktype' => 'value',
                'value' => '{"entity-type":"item","numeric-id":17334923}',
                'token' => $editToken,
                'format' => 'json',
            );
        } elseif ($_POST['claim_value'] == "human") {
            $apiParams = array(
                'action' => 'wbcreateclaim',
                'entity' => $_POST['wikidataID'],
                'property' => 'P31',
                'snaktype' => 'value',
                'value' => '{"entity-type":"item","numeric-id":5}',
                'token' => $editToken,
                'format' => 'json',
            );
        } elseif ($_POST['claim_value'] == "organization") {
            $apiParams = array(
                'action' => 'wbcreateclaim',
                'entity' => $_POST['wikidataID'],
                'property' => 'P31',
                'snaktype' => 'value',
                'value' => '{"entity-type":"item","numeric-id":43229}',
                'token' => $editToken,
                'format' => 'json',
            );
        } elseif ($_POST['claim_value'] == "event") {
            $apiParams = array(
                'action' => 'wbcreateclaim',
                'entity' => $_POST['wikidataID'],
                'property' => 'P31',
                'snaktype' => 'value',
                'value' => '{"entity-type":"item","numeric-id":1190554}',
                'token' => $editToken,
                'format' => 'json',
            );
        }

        $client->setExtraParams( $apiParams ); // sign these too

        $client->makeOAuthCall(
            $accessToken,
            'https://www.wikidata.org/w/api.php',
            true,
            $apiParams
        );

        Rest::json(array("result"=>true, "Vous avez ajouté une propriété à l'élément Wikidata : " . $_POST['wikidataID']));

        Yii::app()->end();
    }
}

?>