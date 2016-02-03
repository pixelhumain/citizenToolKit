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
						"contentKey" => new MongoRegex("/".$contentKey."/i"));
		$listDocuments = PHDB::findAndSort( self::COLLECTION,$params, $sort);
		return $listDocuments;
	}

	protected static function listMyDocumentByContentKey($userId, $contentKey, $docType = null, $sort=null){		
		$params = array("id"=> $userId,
						"contentKey" => new MongoRegex("/".$contentKey."/i"));
		
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
	*/
	public static function save($params){
		//$id = Yii::app()->session["userId"];
		if(!isset($params["contentKey"])){
			$params["contentKey"] = "";
		}

	    $new = array(
			"id" => $params['id'],
	  		"type" => $params['type'],
	  		"folder" => $params['folder'],
	  		"moduleId" => $params['moduleId'],
	  		"doctype" => Document::getDoctype($params['name']),	
	  		"author" => $params['author'],
	  		"name" => $params['name'],
	  		"size" => $params['size'],
	  		'created' => time()
	    );

	    if(isset($params["category"]) && !empty($params["category"]))
	    	$new["category"] = $params["category"];
	    if(isset($params["contentKey"]) && !empty($params["contentKey"])){
	    	$new["contentKey"] = $params["contentKey"];
	    }

	    PHDB::insert(self::COLLECTION,$new);
	    //Generate image profil if necessary
	    if (substr_count(@$new["contentKey"], self::IMG_PROFIL)) {
	    	self::generateProfilImages($new);
	    }
	    return array("result"=>true, "msg"=>Yii::t('document','Document saved successfully',null,Yii::app()->controller->module->id), "id"=>$new["_id"]);	
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

	/**
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
	* remove a document by id
	* @return
	*/
	public static function removeDocumentById($id){
		return PHDB::remove(self::COLLECTION, array("_id"=>new MongoId($id)));
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
						"contentKey" => new MongoRegex("/".$key."/i"));
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
						"contentKey" => new MongoRegex("/".$key."/i"));
		
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
    	return "/".Yii::app()->params['uploadUrl'].$document["moduleId"]."/".$document["folder"];
    }

    public static function getDocumentPath($document){
    	return self::getDocumentFolderPath($document).$document["name"];
    }

    public static function getDocumentFolderPath($document){
    	return Yii::app()->params['uploadDir'].$document["moduleId"]."/".$document["folder"]."/";
    }

    public static function generateProfilImages($document) {
    	$dir = $document["moduleId"];
    	$folder = $document["folder"];

		//The images will be stored in the /uploadDir/moduleId/ownerType/ownerId/thumb (ex : /upload/communecter/citoyen/1242354235435/thumb)
		$upload_dir = Yii::app()->params['uploadDir'].$dir.'/'.$folder.'/'.self::GENERATED_IMAGES_FOLDER;
        if(file_exists ( $upload_dir )) {
            CFileHelper::removeDirectory($upload_dir."bck");
            rename($upload_dir, $upload_dir."bck");
        }
        mkdir($upload_dir, 0775);
        
     	$imageUtils = new ImagesUtils(self::getDocumentPath($document));
    	$destPathThumb = $upload_dir."/".self::FILENAME_PROFIL_RESIZED;
    	$imageUtils->resizeImage(50,50)->save($destPathThumb);
		
		$destPathMarker = $upload_dir."/".self::FILENAME_PROFIL_MARKER;
    	$markerFileName = self::getEmptyMarkerFileName(@$document["type"], @$document["subType"]);
    	if ($markerFileName) {
    		$srcEmptyMarker = self::getPathToMarkersAsset().$markerFileName;
    		$imageUtils->createMarkerFromImage($srcEmptyMarker)->save($destPathMarker);
    	}
        
        //Remove the bck directory
        CFileHelper::removeDirectory($upload_dir."bck");
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
						"contentKey" => new MongoRegex("/".self::IMG_PROFIL."/i"));
		$listDocuments = PHDB::findAndSort( self::COLLECTION,$params, $sort, 1);

		$generatedImageExist = false;
		if ($lastProfilImage = reset($listDocuments)) {
			$documentPath = self::getDocumentFolderPath($lastProfilImage).'/thumb/';
			if ($generatedImageType == self::GENERATED_THUMB_PROFIL) {
				$documentPath = $documentPath.self::FILENAME_PROFIL_RESIZED;
			} else if ($generatedImageType == self::GENERATED_MARKER) {
				$documentPath = $documentPath.self::FILENAME_PROFIL_MARKER;
			}
			$generatedImageExist = file_exists($documentPath);
		}

		//If there is an existing profil image
		if ($generatedImageExist) {
			$documentUrl = self::getDocumentFolderUrl($lastProfilImage).'/thumb/';
			if ($generatedImageType == self::GENERATED_THUMB_PROFIL) {
				$res = $documentUrl.self::FILENAME_PROFIL_RESIZED;
			} else if ($generatedImageType == self::GENERATED_MARKER) {
				$res = $documentUrl.self::FILENAME_PROFIL_MARKER;
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
		}

		return $markerFileName;
	}

	private static function getPathToMarkersAsset() {
		return dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".
				DIRECTORY_SEPARATOR."communecter".DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR.
				"images".DIRECTORY_SEPARATOR."sig".DIRECTORY_SEPARATOR."markers".DIRECTORY_SEPARATOR.
				"icons_carto".DIRECTORY_SEPARATOR;
	}

	public static function retrieveAllImagesUrl($id, $type, $subType = null) {
		$res = array();
		//images
		$profil = self::getLastImageByKey($id, $type, self::IMG_PROFIL);
		$profilThumb = self::getGeneratedImageUrl($id, $type, self::GENERATED_THUMB_PROFIL);
		$marker = self::getGeneratedImageUrl($id, $type, self::GENERATED_MARKER);
		$res["profilImageUrl"] = $profil;
		$res["profilThumbImageUrl"] = $profilThumb;
		$res["profilMarkerImageUrl"] = $marker;
		return $res;
	}

}
?>