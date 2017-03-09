<?php 
class Document {

	const COLLECTION = "documents";

	const IMG_BANNIERE 			= "banniere";
	const IMG_PROFIL 			= "profil";
	const IMG_LOGO 				= "logo";
	const IMG_SLIDER 			= "slider";
	const IMG_MEDIA 			= "media";
	const IMG_PROFIL_RESIZED 	= "profil-resized";
	const IMG_PROFIL_MARKER 	= "profil-marker";

	const CATEGORY_PLAQUETTE 	= "Plaquette";

	const DOC_TYPE_IMAGE 		= "image";
	const DOC_TYPE_CSV		= "text/csv";

	const GENERATED_IMAGES_FOLDER 		= "thumb";
	const GENERATED_MEDIUM_FOLDER 		= "medium";
	const GENERATED_ALBUM_FOLDER		= "album";
	const FILENAME_PROFIL_RESIZED 	  	= "profil-resized.png";
	const FILENAME_PROFIL_MARKER 	  	= "profil-marker.png";
	const GENERATED_THUMB_PROFIL 	  	= "thumb-profil";
	const GENERATED_MARKER		 	  	= "marker";

	/**
	 * get an project By Id
	 * @param type $id : is the mongoId of the project
	 * @return type
	 */
	public static function getById($id) {
	  	return PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	}

	public static function getWhere($params) {
	  	return PHDB::find( self::COLLECTION,$params);
	}

	protected static function listMyDocumentByType($userId, $type, $contentKey, $sort=null){
		$params = array("id"=> $userId,
						"type" => $type,
						"contentKey" => $contentKey);
		$listDocuments = PHDB::findAndSort( self::COLLECTION,$params, $sort);
		return $listDocuments;
	}
	// TODO BOUBOULE - TO DELETE ONLY ONE DEPENDENCE WITH getListDocumentsByContentKey
	protected static function listMyDocumentByContentKey($userId, $contentKey, $docType = null, $sort=null)	{	
		$params = array("id"=> $userId,
						"contentKey" => $contentKey);
		
		if (isset($docType)) {
			$params["doctype"] = $docType;
		}

		$listDocuments = PHDB::findAndSort( self::COLLECTION,$params, $sort);
		return $listDocuments;
	}

	public static function listDocumentByCategory($collectionId, $type, $category, $sort=null) {
		$params = array("id"=> $collectionId,
						"type" => $type,
						"category" => new MongoRegex("/".$category."/i"));
		$listDocuments = PHDB::findAndSort( self::COLLECTION,$params, $sort);
		return $listDocuments;	
	}
	
	/**
	 * save document information
	 * @param $params : a set of information for the document (?to define)
	 * $params = array(
			"id" => $_POST['id'],
	  		"type" => $_POST['type'],
	  		"folder" => $_POST['folder'],
	  		"moduleId" => $_POST['moduleId'],
	  		"name" => $_POST['name'],
	  		"size" => (int) $_POST['size'],
	  		"contentKey" => $_POST["contentKey"],
	  		"author" => Yii::app()->session["userId"]
	    );
		$params["parentType"] = $_POST["parentType"];
		$params["parentId"] = $_POST["parentId"];			
		$params["formOrigin"] = $_POST["formOrigin"];
	*/
	public static function save($params){
		
		//check content key
		if (!in_array(@$params["contentKey"], array(self::IMG_BANNIERE,self::IMG_PROFIL,self::IMG_LOGO,self::IMG_SLIDER,self::IMG_MEDIA)))
			throw new CTKException("Unknown contentKey ".$params["contentKey"]." for the document !");
	    $new = array(
			"id" => $params['id'],
	  		"type" => $params['type'],
	  		"folder" => $params['folder'],
	  		"moduleId" => $params['moduleId'],
	  		"doctype" => Document::getDoctype($params['name']),	
	  		"author" => $params['author'],
	  		"name" => $params['name'],
	  		"size" => (int) $params['size'],
	  		"contentKey" => @$params["contentKey"],
	  		'created' => time()
	    );
	    //if item exists
	    //if( PHDB::count($new['type'],array("_id"=>new MongoId($new['id']))) > 0 ){
		if (in_array($params["type"], array(Survey::COLLECTION, ActionRoom::COLLECTION, ActionRoom::COLLECTION_ACTIONS))) {
			 if (!Authorisation::canEditItem( $params['author'], $params['type'], $params['id'], @$params["parentType"],@$params["parentId"])) {
		    	return array("result"=>false, "msg"=>Yii::t('document',"You are not allowed to modify the document of this item !") );
		    }
		} else {
		    if (   ! Authorisation::canEditItem($params['author'], $params['type'], $params['id']) 
		    	&& !Authorisation::isOpenEdition($params['id'], $params['type']) 
		    	&& (!@$params["formOrigin"] || !Link::isLinked($params['id'], $params['type'], $params['author']))) 
		    {
			    if(@$params["formOrigin"] && $params["formOrigin"]=="news")
					return array("result"=>false, "msg"=>Yii::t('document',"You have no rights upload document on this item, just write a message !") );
			    else
		    		return array("result"=>false, "msg"=>Yii::t('document',"You are not allowed to modify the document of this item !"), "params" => $params );
		    }
	    }
		//}
	    if(isset($params["category"]) && !empty($params["category"]))
	    	$new["category"] = $params["category"];

	    PHDB::insert(self::COLLECTION, $new);
	    
	    //Generate small image
	   	self::generateAlbumImages($new);
	    //Generate image profil if necessary
	    if (substr_count(@$new["contentKey"], self::IMG_PROFIL)) {
	    	self::generateProfilImages($new);
	    	$typeNotif="profilImage";
	    }
	    if (substr_count(@$new["contentKey"], self::IMG_SLIDER)) {
	    	self::generateAlbumImages($new, self::GENERATED_IMAGES_FOLDER);
	    	$typeNotif="albumImage";
	    }
    	Notification::constructNotification(ActStr::VERB_ADD, array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), array("type"=>$new["type"],"id"=> $new["id"]), null, $typeNotif);
	    return array("result"=>true, "msg"=>Yii::t('document','Document saved successfully'), "id"=>$new["_id"],"name"=>$new["name"]);	
	}
	
	
	/**
	* get the type of a document
	* @param strname : the name of the document
	*/
	public static function getDoctype($strname){

		$supported_image = array(
		    'gif',
		    'jpg',
		    'jpeg',
		    'png'
		);

		$doctype = "";
		$ext = strtolower(pathinfo($strname, PATHINFO_EXTENSION)); // Using strtolower to overcome case sensitive
		if (in_array($ext, $supported_image)) {
			$doctype = "image";
		}else{
			$doctype = $ext;
		}
		return $doctype;
	}

	/** TODO BOUBOULE 
	*	TO DELETE --- NOT CORRECT BECAUSE OF CONTENTKEY WHICH IS A COMPLEX SEARCH WHEN IT COULD SIMPLE
	* 	Still present in city/detailAction, and survey/entryAction then impact on the rest of documents !!!
	* END TODO
	 * get the list of documents depending on the id of the owner, the contentKey and the docType
	 * @param String $id The id of the owner of the image could be an organization, an event, a person, a project... 
	 * @param String $contentKey The content key is composed with the controllerId, the action where the document is used and a type
	 * @param String $docType The docType represent the type of document (see DOC_TYPE_* constant)
	 * @param array $limit represent the number of document by type that will be return. If not set, everything will be return
	 * @return array a list of documents + URL sorted by contentkey type (IMG_PROFIL...)
	 */
	public static function getListDocumentsByContentKey($id, $contentKey, $docType=null, $limit=null){
		$listDocuments = array();
		$sort = array( 'created' => -1 );
		$explodeContentKey = explode(".", $contentKey);
		$listDocumentsofType = Document::listMyDocumentByContentKey($id, $explodeContentKey[0], $docType, $sort);
		foreach ($listDocumentsofType as $key => $value) {
			$toPush = false;
			if(isset($value["contentKey"]) && $value["contentKey"] != ""){
				$explodeValueContentKey = explode(".", $value["contentKey"]);
				$currentType = (string) $explodeValueContentKey[2];
				if (isset($explodeContentKey[1])) {
					if($explodeContentKey[1] == $explodeValueContentKey[1]){
						if (! isset($limit)) {
							$toPush = true;
						} else {
							if (isset($limit[$currentType])) {
								$limitByType = $limit[$currentType];
								$actuelNbCurrentType = isset($listDocuments[$currentType]) ? count($listDocuments[$currentType]) : 0;
								if ($actuelNbCurrentType < $limitByType) {
									$toPush = true;
								}
							} else {
								$toPush = true;
							}
						}
					}
				} else {
					$toPush = true;
				}
			}
			if ($toPush) {
				$imageUrl = Document::getDocumentUrl($value);
				if (! isset($listDocuments[$currentType])) {
					$listDocuments[$currentType] = array();
				} 
				$value['imageUrl'] = $imageUrl;
				array_push($listDocuments[$currentType], $value);
			}
		}

		return $listDocuments;
	}
	
	public static function listMyDocumentByIdAndType($id, $type, $contentKey= null, $docType = null, $sort=null)	{	
		$params = array("id"=> $id,
						"type" => $type);
		if (isset($contentKey) && $contentKey != null) 
			$params["contentKey"] = $contentKey;
		if (isset($docType)) 
			$params["doctype"] = $docType;
		$listDocuments = PHDB::findAndSort( self::COLLECTION,$params, $sort);
		return $listDocuments;
	}
	/* @Author Bouboule (clement.damiens@gmail.com)
	* get the list of documents depending on the id of the owner, the contentKey and the docType
	 * @param String $id The id of the owner of the image could be an organization, an event, a person, a project... 
	 * @param String $contentKey 
	* @param String $docType The docType represent the type of document (see DOC_TYPE_* constant)
	 * @param array $limit represent the number of document by type that will be return. If not set, everything will be return
	 * @return array a list of documents + URL sorted by contentkey type (IMG_PROFIL, IMG_SLIDER...)
	 */
	public static function getListDocumentsByIdAndType($id, $type, $contentKey=null, $docType=null, $limit=null){
		$listDocuments = array();
		$sort = array( 'created' => -1 );
		$listDocumentsofType = Document::listMyDocumentByIdAndType($id, $type, $contentKey, $docType, $sort);
		foreach ($listDocumentsofType as $key => $value) {
			$toPush = false;
			if(isset($value["contentKey"]) && $value["contentKey"] != ""){
				$currentContentKey = $value["contentKey"];
				if (! isset($limit)) {
					$toPush = true;
				} else {
					if (isset($limit[$currentContentKey])) {
						$limitByType = $limit[$currentContentKey];
						$actuelNbCurrentType = isset($listDocuments[$currentContentKey]) ? count($listDocuments[$currentContentKey]) : 0;
						if ($actuelNbCurrentType < $limitByType)
							$toPush = true;
					} else {
						$toPush = true;
					}
				}
			} else {
					$toPush = true;
			}
			if ($toPush) {
				$pushImage = array();
				if ($value["moduleId"]=="communevent"){
					$pushImage['id'] = $value["objId"];
					$imagePath = Yii::app()->params['communeventBaseUrl']."/".$value["folder"]."/".$value["name"];
					$imageThumbPath = $imagePath."?store=photosLarge";
				}
				else{
					$pushImage['id'] = (string)$value["_id"];
					$imagePath = Yii::app()->baseUrl."/".Yii::app()->params['uploadUrl'].$value["moduleId"]."/".$value["folder"];
					if($value["contentKey"]=="profil")
						$imageThumbPath = $imagePath;
					else
						$imageThumbPath = $imagePath."/".self::GENERATED_IMAGES_FOLDER;
					$imagePath .= "/".$value["name"];
					$imageThumbPath .= "/".$value["name"];
				}
				if (! isset($listDocuments[$currentContentKey])) {
					$listDocuments[$currentContentKey] = array();
				} 
				$pushImage['moduleId'] = $value["moduleId"];
				$pushImage['contentKey'] = $value["contentKey"];
				$pushImage['imagePath'] = $imagePath;
				$pushImage['imageThumbPath'] = $imageThumbPath;
				$pushImage['name'] = $value["name"];
				$pushImage['size'] = self::getHumanFileSize($value["size"]);
				array_push($listDocuments[$currentContentKey], $pushImage);
			}
		}
		return $listDocuments;
	}
	/** author clement.damiens@gmail.com
	 * Controle space storage of each entity
	 * @param string $id The id of the owner of document
	 * @param string $type The type of the owner of document
	 * @param string $docType The kind of document research
	 * @return size of storage used to stock
	 */
	public static function storageSpaceByIdAndType($id, $type,$docType){
		$params = array("id"=> $id,
						"type" => $type);
		if (isset($docType)) 
			$params["doctype"] = $docType;
		$c = Yii::app()->mongodb->selectCollection(self::COLLECTION);
		$result = $c->aggregate( array(
						array('$match' => $params),
						array('$group' => array(
							'_id' => $params,
							'sumDocSpace' => array('$sum' => '$size')))
						));
		$spaceUsed="";
		if (@$result["ok"]) 
			$spaceUsed = @$result["result"][0]["sumDocSpace"];
		return $spaceUsed;

	}
	/** author clement.damiens@gmail.com
	 * Return boolean if entity is authorized to stock
	 * @param string $id The id of the owner of document
	 * @param string $type The type of the owner of document
	 * @param string $docType The kind of document research
	 * @return size of storage used to stock
	 */
	public static function authorizedToStock($id, $type,$docType){
		$storageSpace = self::storageSpaceByIdAndType($id, $type,self::DOC_TYPE_IMAGE);
		$authorizedToStock=true;
		if($storageSpace > (20*1048576))
			$authorizedToStock=false;
		return $authorizedToStock;
	}
	/**
	 * @See getListDocumentsByContentKey. 
	 * @return array Return only the Url of the documents ordered by contentkey type
	 */
	public static function getListDocumentsURLByContentKey($id, $contentKey, $docType=null, $limit=null){
		$res = array();
		$listDocuments = self::getListDocumentsByContentKey($id, $contentKey, $docType, $limit);
		foreach ($listDocuments as $contentKey => $documents) {
			foreach ($documents as $document) {
				if (! isset($res[$contentKey])) {
					$res[$contentKey] = array();
				} 
				array_push($res[$contentKey],$document["imageUrl"]);
			}
		}
		return $res;
	}
	
	/**
	* remove a document by id and delete the file on the filesystem
	* @return
	*/
	public static function removeDocumentById($id, $userId){
		//TODO SBAR - Generate new thumbs if the image is the current image
		$doc = Document::getById($id);
		if ($doc) 
		{
			if (Authorisation::canEditItem($userId, $doc["type"], $doc["id"])) 
			{
				$filepath = self::getDocumentPath($doc);
				if(file_exists ( $filepath )) {
		            if (unlink($filepath)) {
		                PHDB::remove(self::COLLECTION, array("_id"=>new MongoId($id)));
		                $res = array('result'=>true, "msg" => Yii::t("document","Document deleted"), "id" => $id);
		            } else
		                $res = array('result'=>false,'msg'=>Yii::t("common","Something went wrong!"), "filepath" => $filepath);
		        } else {
		        	//even if The file does not exists on the filesystem : we try to delete the document on mongo
		            PHDB::remove(self::COLLECTION, array("_id"=>new MongoId($id)));
		            $res = array('result'=>true, "msg" => Yii::t("document","Document deleted"), "id" => $id);
		        }
		    } else 
		    	$res = array('result'=>false, "msg" => Yii::t("document","You are not allowed to delete this document !"), "id" => $id);
		} else 
		    $res = array('result'=>false,'error'=>Yii::t("common","Something went wrong!"),"id"=>$id);

		return $res;
	}

	/**
	* remove a document by folder and delete the file on the filesystem
	* a test of Authorization must be done higher in the process 
	* testing the user created the element parent of the document 
	* @return
	*/
	public static function removeDocumentByFolder($folder){
		//TODO SBAR - Generate new thumbs if the image is the current image
		$docs = self::getWhere(array("folder"=>$folder));
		if (@$docs) 
		{
			//delete all entries in DB
			foreach ($docs as $key => $doc) 
			{
				PHDB::remove( self::COLLECTION, array("_id"=>$key) );
				$results[$key] = array( 'result'=>true, "entry" => "deleted");
		    }
		    
		    $folder = self::getDocumentFolderPath($doc);
		    //delete folder from disk recursively
		    if(file_exists ( $folder ) ){
		    	CFileHelper::removeDirectory($folder);
                $results["folder"] = "deleted";
            } else 
                $results["folder"] = "something went wrong";

		    $res = array( 'result'=>true, "msg" => Yii::t("document","Document deleted"), "results" => $results );
		} else 
			$res = array( 'result'=>true, "msg" => Yii::t("document","no Documents associated") );

		return $res;
	}


	/**
	* remove a document from communevent by objId
	* @return
	*/
	public static function removeDocumentCommuneventByObjId($id, $userId){
		$doc = Document::getById($id);
		if ($doc) 
		{
			if (Authorisation::canEditItem($userId, $doc["type"], $doc["id"])) 
			{
				//Suppression de l'image dans la collection cfs.photosimg.filerecord
				PHDB::remove("cfs.photosimg.filerecord", array("_id"=>$id));
				//Suppression du document
				PHDB::remove(self::COLLECTION, array("objId"=>$id));
				$res = array('result'=>true, "msg" => Yii::t("document","Document deleted"), "id" => $id);
			} else
				$res = array('result'=>false, "msg" => Yii::t("document","You are not allowed to delete this document !"), "id" => $id);
		}
		return $res;
	}

	/**
	* upload the path of an image
	* @param itemId is the id of the item that we want to update
	* @param itemType is the type of the item that we want to update
	* @param path is the new path of the image
	* @return
	*/
	public static function setImagePath($itemId, $itemType, $path, $contentKey){
		$tabImage = explode('.', $contentKey);

		if(in_array(Document::IMG_PROFIL, $tabImage)){
			return PHDB::update($itemType,
	    					array("_id" => new MongoId($itemId)),
	                        array('$set' => array("imagePath"=> $path))
	                    );
		}
	}

	/**
	* get a list of images with a key depending on limit
	* @param itemId is the id of the item that we want to get images
	* @param itemType is the type of the item that we want to get images
	* @param limit an array containing couple with the imagetype and the numbers of images wanted (see IMG_* for available type)
	* @return return an array of type and urls of a document
	*/
	public static function getImagesByKey($itemId, $itemType, $limit) {
		$imageUrl = "";
		$res = array();

		foreach ($limit as $key => $aLimit) {
			$sort = array( 'created' => -1 );
			$params = array("id"=> $itemId,
						"type" => $itemType,
						"contentKey" => $key);
			$listImagesofType = PHDB::findAndSort( self::COLLECTION,$params, $sort, $aLimit);

			$arrayOfImagesPath = array();
			foreach ($listImagesofType as $id => $document) {
	    		$imageUrl = Document::getDocumentUrl($document);
	    		array_push($arrayOfImagesPath, $imageUrl);
			}
			$res[$key] = $arrayOfImagesPath;
		}
		
		return $res;
	}

	/**
	* get the last images with a key
	* @param itemId is the id of the item that we want to get images
	* @param itemType is the type of the item that we want to get images
	* @param key is the type of image we want to get
	* @return return the url of a document
	*/
	public static function getLastImageByKey($itemId, $itemType, $key){
		$imageUrl = "";
		$sort = array( 'created' => -1 );
		$params = array("id"=> $itemId,
						"type" => $itemType,
						"contentKey" => $key);
		
		$listImagesofType = PHDB::findAndSort( self::COLLECTION,$params, $sort, 1);
		
		foreach ($listImagesofType as $key => $value) {
    		$imageUrl = Document::getDocumentUrl($value);
		}
		return $imageUrl;
	}

	/**
	 * Get the list of categories available for the id and the type (Person, Organization, Event..)
	 * @param String $id Id to search the categories for
	 * @param String $type Collection Type 
	 * @return array of available categories (String)
	 */
	public static function getAvailableCategories($id, $type) {
		$params = array("id"=> $id,
						"type" => $type);
		$sort = array("category" => -1);
		$listCategory = PHDB::distinct(self::COLLECTION, "category", $params);
		
		return $listCategory;

	}

	public static function getHumanFileSize($bytes, $decimals = 2) {
      $sz = 'BKMGTP';
      $factor = floor((strlen($bytes) - 1) / 3);
      return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    public static function clean($string) {
       $string = preg_replace('/  */', '-', $string);
       $string = strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'); // Replaces all spaces with hyphens.
       return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    public static function getDocumentUrl($document){
    	return self::getDocumentFolderUrl($document)."/".$document["name"];
    }

    public static function getDocumentFolderUrl($document){
	    if ($document["moduleId"]=="communevent")
		    $folderUrl = Yii::app()->params['communeventUrl'];
		else
			$folderUrl = "/".Yii::app()->params['uploadUrl'].$document["moduleId"];
    	$folderUrl .= "/".$document["folder"];
    	return $folderUrl;
    }

    public static function getDocumentPath($document){
    	return self::getDocumentFolderPath($document).$document["name"];
    }

    public static function getDocumentFolderPath($document){
    	return Yii::app()->params['uploadDir'].$document["moduleId"]."/".$document["folder"]."/";
    }

    /**
     * This function will generate the thumb and the marker link to a profil image.
     * It will as well update the entity linked to the document and update the path to the images
     * @param array $document will content a well formated document
     * @return array result => booleant, msg => String
     */
    public static function generateProfilImages($document) {
    	$dir = $document["moduleId"];
    	$folder = $document["folder"];

		//The images will be stored in the /uploadDir/moduleId/ownerType/ownerId/thumb (ex : /upload/communecter/citoyen/1242354235435/thumb)
		$upload_dir = Yii::app()->params['uploadDir'].$dir.'/'.$folder.'/'.self::GENERATED_IMAGES_FOLDER;
		$upload_dir_medium = Yii::app()->params['uploadDir'].$dir.'/'.$folder.'/'.self::GENERATED_MEDIUM_FOLDER;
        if(file_exists ( $upload_dir )) {
            CFileHelper::removeDirectory($upload_dir."bck");
            rename($upload_dir, $upload_dir."bck");
        }
        mkdir($upload_dir, 0775);
        // Medium Image
        if(!file_exists ( $upload_dir_medium )) {       
			mkdir($upload_dir_medium, 0775);
		}
   		//GET THUMB IMAGE
        $profilUrl = self::getDocumentUrl($document);
        $profilPath = self::getDocumentPath($document);
     	$imageUtils = new ImagesUtils($profilPath);
    	$destPathThumb = $upload_dir."/".self::FILENAME_PROFIL_RESIZED;
    	$profilThumbUrl = self::getDocumentFolderUrl($document)."/".self::GENERATED_IMAGES_FOLDER."/".self::FILENAME_PROFIL_RESIZED;
    	$imageUtils->resizeImage(50,50)->save($destPathThumb);
    	//GET MEDIUM IMAGE
    	$imageMediumUtils = new ImagesUtils($profilPath);
		$destPathMedium = $upload_dir_medium."/".$document["name"];
    	$profilMediumUrl = self::getDocumentFolderUrl($document)."/".self::GENERATED_MEDIUM_FOLDER."/".$document["name"];
    	$imageMediumUtils->resizePropertionalyImage(400,400)->save($destPathMedium,100);
		
		$destPathMarker = $upload_dir."/".self::FILENAME_PROFIL_MARKER;
		$profilMarkerImageUrl = self::getDocumentFolderUrl($document)."/".self::GENERATED_IMAGES_FOLDER."/".self::FILENAME_PROFIL_MARKER;
    	$markerFileName = self::getEmptyMarkerFileName(@$document["type"], @$document["subType"]);
    	if ($markerFileName) {
    		$srcEmptyMarker = self::getPathToMarkersAsset().$markerFileName;
    		$imageUtils->createMarkerFromImage($srcEmptyMarker)->save($destPathMarker);
    	}
        
        //Update the entity collection to store the path of the profil images
        if (in_array($document["type"], array(Person::COLLECTION, Organization::COLLECTION, Project::COLLECTION, Event::COLLECTION))) {
	        PHDB::update($document["type"], array("_id" => new MongoId($document["id"])), array('$set' => array("profilImageUrl" => $profilUrl, "profilMediumImageUrl" => $profilMediumUrl,"profilThumbImageUrl" => $profilThumbUrl, "profilMarkerImageUrl" =>  $profilMarkerImageUrl)));

	        error_log("The entity ".$document["type"]." and id ". $document["id"] ." has been updated with the URL of the profil images.");
		}
        //Remove the bck directory
        CFileHelper::removeDirectory($upload_dir."bck");
        return array("result" => true, "msg" => "Thumb and markers have been generated");
	}

	// Resize initial image for album size 
	// param type array $document
	// param string $folderAlbum where Image is upload
	public static function generateAlbumImages($document,$folderAlbum=null) {
    	$dir = $document["moduleId"];
    	$folder = $document["folder"];
		if($folderAlbum==self::GENERATED_IMAGES_FOLDER){
			$destination='/'.self::GENERATED_IMAGES_FOLDER;
			$maxWidth=200;
			$maxHeight=200;
			$quality=100;
		} else{
			$destination="";
			$maxWidth=1100;
			$maxHeight=700;
			$quality=50;
		}
		//The images will be stored in the /uploadDir/moduleId/ownerType/ownerId/thumb (ex : /upload/communecter/citoyen/1242354235435/thumb)
		$upload_dir = Yii::app()->params['uploadDir'].$dir.'/'.$folder.$destination; 
		
		if(!file_exists ( $upload_dir )) {       
			mkdir($upload_dir, 0775);
		}
		//echo "iciiiiiii/////////////".$upload_dir;
		$path=self::getDocumentPath($document);
		list($width, $height) = getimagesize($path);
		if ($width > $maxWidth || $height >  $maxHeight){
     		$imageUtils = new ImagesUtils($path);
    		$destPathThumb = $upload_dir."/".$document["name"];
    		if($folderAlbum==self::GENERATED_IMAGES_FOLDER)
    			$imageUtils->resizeImage($maxWidth,$maxHeight)->save($destPathThumb);
    		else
    			$imageUtils->resizePropertionalyImage($maxWidth,$maxHeight)->save($destPathThumb,$quality);
    	}
	}
	
	/**
	 * Return the url of the generated image 
	 * @param String $id Identifier of the object to retrieve the generated image
	 * @param String $type Type of the object to retrieve the generated image
	 * @param String $generatedImageType Type of generated image See GENERATED_*
	 * @param String $subType used for organization (NGO, business)
	 * @return String containing the URL of the generated image of the type 
	 */
	public static function getGeneratedImageUrl($id, $type, $generatedImageType, $subType = null) {
		$sort = array( 'created' => -1 );
		$params = array("id"=> $id,
						"type" => $type,
						"contentKey" => self::IMG_PROFIL);
		$listDocuments = PHDB::findAndSort( self::COLLECTION,$params, $sort, 1);

		$generatedImageExist = false;
		if ($lastProfilImage = reset($listDocuments)) {
			$documentPath = self::getDocumentFolderPath($lastProfilImage);
			if ($generatedImageType == self::GENERATED_THUMB_PROFIL) {
				$documentPath = $documentPath.'/'.self::GENERATED_THUMB_PROFIL.'/'.self::FILENAME_PROFIL_RESIZED;
			} else if ($generatedImageType == self::GENERATED_MARKER) {
				$documentPath = $documentPath.'/'.self::GENERATED_THUMB_PROFIL.'/'.self::FILENAME_PROFIL_MARKER;
			} else if ($generatedImageType == self::GENERATED_MEDIUM_FOLDER){
				$documentPath = $documentPath.'/'.self::GENERATED_MEDIUM_FOLDER.'/'.$lastProfilImage["name"];
			}
			$generatedImageExist = file_exists($documentPath);
		}

		//If there is an existing profil image
		if ($generatedImageExist) {
			$documentUrl = self::getDocumentFolderUrl($lastProfilImage);
			$documentThumb=$documentUrl.'/thumb/';
			$documentMedium=$documentUrl.'/'.self::GENERATED_MEDIUM_FOLDER.'/';
			if ($generatedImageType == self::GENERATED_THUMB_PROFIL) {
				$res = $documentUrl.self::FILENAME_PROFIL_RESIZED;
			} else if ($generatedImageType == self::GENERATED_MARKER) {
				$res = $documentUrl.self::FILENAME_PROFIL_MARKER;
			}else if ($generatedImageType == self::GENERATED_MEDIUM_FOLDER) {
				$res = $documentUrl.$lastProfilImage["name"];
			}

		//Else the default image is returned
		} else {
			if ($generatedImageType == self::GENERATED_MARKER) {
				$markerDefaultName = str_replace("empty", "default", self::getEmptyMarkerFileName($type, $subType));
				//$res = "/communecter/assets/images/sig/markers/icons_carto/".$markerDefaultName;
				//remove the "/ph/" on the assersUrl if there
				$homeUrlRegEx = "/".str_replace("/", "\/", Yii::app()->homeUrl)."/";
				$assetsUrl = preg_replace($homeUrlRegEx, "", @Yii::app()->controller->module->assetsUrl,1);
				$res = "/".$assetsUrl."/images/sig/markers/icons_carto/".$markerDefaultName;
			} else {
				$res = "";
			}
		}
		return $res;
	}

	private static function getEmptyMarkerFileName($type, $subType = null) {
		$markerFileName = "";

		switch ($type) {
			case Person::COLLECTION :
				$markerFileName = "citizen-marker-empty.png";
				break;
			case Organization::COLLECTION :
				if ($subType == "NGO") 
					$markerFileName = "ngo-marker-empty.png";
				else if ($subType == "LocalBusiness") 
					$markerFileName = "business-marker-empty.png";
				else 
					$markerFileName = "ngo-marker-empty.png";
				break;
			case Event::COLLECTION :
				$markerFileName = "event-marker-empty.png";
				break;
			case Project::COLLECTION :
				$markerFileName = "project-marker-empty.png";
				break;
			case City::COLLECTION :
				$markerFileName = "city-marker-empty.png";
				break;
			case Poi::COLLECTION :
				if($subType!=null)
					$markerFileName = "poi-".$subType."-marker-empty.png";
				else
					$markerFileName = "poi-marker-empty.png";
				break;
		}

		return $markerFileName;
	}

	private static function getPathToMarkersAsset() {
		return dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".
				DIRECTORY_SEPARATOR."communecter".DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR.
				"images".DIRECTORY_SEPARATOR."sig".DIRECTORY_SEPARATOR."markers".DIRECTORY_SEPARATOR.
				"icons_carto".DIRECTORY_SEPARATOR;
	}

	public static function retrieveAllImagesUrl($id, $type, $subType = null, $entity = null) {
		$res = array();
		//error_log("Entity Profil image url for the ".$type." with the id ".$id." : ".@$entity["profilImageUrl"] );
		//The profil image URL should be stored in the entity collection 
		if (isset($entity["profilImageUrl"])) {
			$res["profilImageUrl"] = $entity["profilImageUrl"];
			$res["profilThumbImageUrl"] = !empty($entity["profilThumbImageUrl"]) ? $entity["profilThumbImageUrl"]."?_=".time() : "";
			$res["profilMarkerImageUrl"] = !empty($entity["profilMarkerImageUrl"]) ? $entity["profilMarkerImageUrl"]."?_=".time() : ""; 
			$res["profilMediumImageUrl"] = !empty($entity["profilMediumImageUrl"]) ? $entity["profilMediumImageUrl"]."?_=".time() : ""; 

		//If empty than retrieve the URLs from document and store them in the entity for next time
		} else {
			$profil = self::getLastImageByKey($id, $type, self::IMG_PROFIL);
			
			if (!empty($profil)) {
				$profilThumb = self::getGeneratedImageUrl($id, $type, self::GENERATED_THUMB_PROFIL);
				$profilMedium = self::getGeneratedImageUrl($id, $type, self::GENERATED_MEDIUM_FOLDER);
				if ($profil != "") {
					$marker = self::getGeneratedImageUrl($id, $type, self::GENERATED_MARKER);
				} else {
					$marker = "";
				} 

				PHDB::update($type, array("_id" => new MongoId($id)), array('$set' => array("profilImageUrl" => $profil, "profilThumbImageUrl" => $profilThumb, "profilMarkerImageUrl" =>  $marker,"profilMediumImageUrl" =>  $profilMedium)));
				error_log("Add Profil image url for the ".$type." with the id ".$id);
			}
			
			$res["profilImageUrl"] = $profil;
			//Add a time to force relaod of generated images
			$res["profilThumbImageUrl"] = !empty($profilThumb) ? $profilThumb."?_=".time() : "";
			$res["profilMarkerImageUrl"] = !empty($marker) ? $marker."?_=".time() : "";
			$res["profilMediumImageUrl"] = !empty($profilMedium) ? $profilMedium."?_=".time() : "";
		}

		//If empty marker return default marker
		if ($res["profilMarkerImageUrl"] == "") {
			$markerDefaultName = str_replace("empty", "default", self::getEmptyMarkerFileName($type, $subType));
			//$res = "/communecter/assets/images/sig/markers/icons_carto/".$markerDefaultName;
			//remove the "/ph/" on the assersUrl if there
			$homeUrlRegEx = "/".str_replace("/", "\/", Yii::app()->homeUrl)."/";
			$assetsUrl = preg_replace($homeUrlRegEx, "", @Yii::app()->controller->module->assetsUrl,1);
			$res["profilMarkerImageUrl"] = "/".$assetsUrl."/images/sig/markers/icons_carto/".$markerDefaultName;
		}

		return $res;
	}

	public static function getImageByUrl($urlImage, $path, $nameImage) {
		// Ouvre un fichier pour lire un contenu existant
		$current = file_get_contents($urlImage);
		// Écrit le résultat dans le fichier
		$file = "../../modules/cityData/".$nameImage;
		file_put_contents($file, $current);
	}

	/**
	 * Get a file from an URL using curl
	 * @param String $url the complete URL of the file to get
	 * @return file a pointer on the file
	 */
	public static function urlGetContents ($url) {
	    if (!function_exists('curl_init')){ 
	        die('CURL is not installed!');
	    }
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $output["file"] = curl_exec($ch);
	    $output["size"] = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
	    curl_close($ch);
	    return $output;
	}

	/**
	 * Description
	 * @param type $dir 
	 * @param type|null $folder 
	 * @param type|null $ownerId 
	 * @param type $input 
	 * @param type|bool $rename 
	 * @param type $pathFile 
	 * @param type $nameFile 
	 * @return type
	 */
	public static function uploadDocumentFromURL($dir,$folder=null,$ownerId=null,$input,$rename=false, $pathFile, $nameFile) {
		//Check if the file exists on that URL
		$file_headers = @get_headers($pathFile.$nameFile);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
		    $exists = false;
		} else {
		    $exists = true;
		}

		if ($exists) {
			//$file = file_get_contents($pathFile.$nameFile, FILE_USE_INCLUDE_PATH);
			$file = self::urlGetContents($pathFile.$nameFile);
			$res = self::checkFileRequirements($file["file"], $dir, $folder, $ownerId, $input, $nameFile, $file["size"] );
			if ($res["result"]) {
				$res = self::uploadDocument($file, $res["uploadDir"], $input, $rename, $nameFile, $file["size"]);
			}
			return $res;
		}
	}

	public static function uploadDocument($file, $uploadDir,$input,$rename=false, $nameUrl = null, $sizeUrl=null) {
    	$uploadedFile = (!empty($file['tmp_name']) ? true : false);
    	$nameFile = (!empty($nameUrl) ? $nameUrl : $file["name"] );

    	// Move the uploaded file from the temporary 
    	// directory to the uploads folder:
    	// we use a unique Id for the image name Yii::app()->session["userId"].'.'.$ext
        // renaming file
        $cleanfileName = Document::clean(pathinfo($nameFile, PATHINFO_FILENAME)).".".pathinfo($nameFile, PATHINFO_EXTENSION);
    	$name = ($rename) ? Yii::app()->session["userId"].'.'.$ext : $cleanfileName;
        if( file_exists ( $uploadDir.$name ) )
            $name = time()."_".$name;        
            
    	if(isset(Yii::app()->session["userId"]) && $name) {
    		if ($uploadedFile) {
    			move_uploaded_file($file['tmp_name'], $uploadDir.$name);
    		} else {
    			file_put_contents($uploadDir.$name , $file);
			}
    		return array('result'=>true,
                        "success"=>true,
                        'name'=>$name,
                        'uploadDir'=> $uploadDir,
                        'size'=> (int) filesize ($uploadDir.$name) );
    	}

        return array('result'=>false,'error'=>Yii::t("document","Something went wrong with your upload!"));
	}
			
	/**
	 * Check if the file can be uploaded and prepare the folders tree to 
	 * @param file $file a file to upload
	 * @param string $dir the moduleId
	 * @param string $folder the type of the entity linked to the document
	 * @param String $ownerId the Id of the entity linked to the document
	 * @param type $input : ?????
	 * @param String|null $nameUrl The name of the file (not mandatory : could be retrieve from the file when it's not an URL file)
	 * @param type|null $sizeUrl The size of the file (not mandatory : could be retrieve from the file when it's not an URL file)
	 * @return array result => boolean, msg => String, uploadDir => where the file is stored
	 */
	public static function checkFileRequirements($file, $dir, $folder, $ownerId, $input, $nameUrl = null, $sizeUrl=null) {
		//TODO SBAR
		//$dir devrait être calculé : sinon on peut facilement enregistrer des fichiers n'importe où
		$upload_dir = Yii::app()->params['uploadDir'];
        if(!file_exists ( $upload_dir ))
            mkdir ( $upload_dir,0775 );
        
        //ex: upload/communecter
        $upload_dir = Yii::app()->params['uploadDir'].$dir.'/';
        if(!file_exists ( $upload_dir ))
            mkdir ( $upload_dir,0775 );

        //ex: upload/communecter/person
        if( isset( $folder )){
            $upload_dir .= $folder.'/';
            if( !file_exists ( $upload_dir ) )
                mkdir ( $upload_dir,0775 );
        }

        //ex: upload/communecter/person/userId
        if( isset( $ownerId )) {
            $upload_dir .= $ownerId.'/';
            if( !file_exists ( $upload_dir ) )
            	mkdir ( $upload_dir,0775 );

        }
       
        if( @$input=="newsImage"){
	        $upload_dir .= Document::GENERATED_ALBUM_FOLDER.'/';
            if( !file_exists ( $upload_dir ) )
                mkdir ( $upload_dir,0775 );
        }

        //Check extension
        $allowed_ext = array('jpg','jpeg','png','gif',"pdf","xls","xlsx","doc","docx","ppt","pptx","odt");
        
        $nameFile = (!empty($nameUrl) ? $nameUrl : $file["name"] );
        
    	$ext = strtolower(pathinfo($nameFile, PATHINFO_EXTENSION));
    	if(!in_array($ext,$allowed_ext)) {
    		return array('result'=>false,'error'=>Yii::t("document","Only").implode(',',$allowed_ext).Yii::t("document","files are allowed!"));
    	}

    	//Check size
    	$size = (!empty($sizeUrl) ? $sizeUrl : $file["size"] );
    	if ($size > 4000000 ) {
	    	return array('result'=>false,'error'=>"The file size should not be over 4 Mo");
	    }

    	return array('result' => true, 'msg'=>'Files requirements meet', 'uploadDir' => $upload_dir);
	}

}
?>