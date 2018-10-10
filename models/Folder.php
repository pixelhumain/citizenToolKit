<?php 
class Folder {
    const COLLECTION = "folders";
    const CONTROLLER = "folder";

    public static function getById($id){
        return PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
    }
    public static function create($contextId, $contextType, $name, $docType=null, $parentId=null)
    {
        
        //$person = Person::getById( Yii::app()->session["userId"] );
        //$action = '$set';     
        $params=array("contextId"=>$contextId, "contextType"=> $contextType, "name"=>$name, "created"=>time(),"updated"=>time());
        if(@$parentId)
            $params["parentId"]=$parentId;
        if(@$docType)
            $params["docType"]=$docType;
        PHDB::insert(self::COLLECTION,  
                    $params);
        self::createFolder($params);
        return array("result"=>true, "folder"=>$params, "msg"=>Yii::t("common","Folder {what} created with success",array("{what}"=>$name)));
    }
    public static function createFolder($folder){
        $folderPath=self::getFolderPath($folder);
        $upload_dir = Yii::app()->params['uploadDir']."communecter/";
        $folderPath=str_replace ( $upload_dir , "" , $folderPath ); 
        $folderPathExp=explode("/", $folderPath);
        foreach($folderPathExp as $v){
            $upload_dir .= $v.'/';
            if( !file_exists ( $upload_dir ) )
                mkdir ( $upload_dir,0775 );
        }
        // if( !file_exists ( $folderPath ) )
          //  mkdir ( $folderPath,0775 );
        return true;
    }
    public static function update($id,$name,$del=false)
    {
            PHDB::update(self::COLLECTION, 
                           array("_id" => new MongoId($id) ) , 
                           array('$set'=>array("name" => $name))
                           );
            $fold = array('name' => $name, "id"=> $id );
            return array("result"=>true, "name"=>$name, "folder"=>$fold, "msg"=>Yii::t("common","Collection {what} renamed with success",array("{what}"=>$name)));
        //} else 
          //  return array("result"=>false, "collection"=>"collections.".$name, "msg"=>"Collection $name doesn't exist");
        
    }
    public static function getSubfoldersById($id){
        return PHDB::find(self::COLLECTION, array("parentId"=>$id));
    }
    public static function getSubfoldersByContext($contextId, $contextType, $docType){
        return PHDB::find(self::COLLECTION, array("contextId"=>$contextId, "contextType"=>$contextType,"docType"=>$docType, "parentId"=>array('$exists'=>false)));
    }
    public static function delete($id, $removeDir=true)
    {
        
        $folder = self::getById( $id );     
        if(@$folder["docType"]){
            //process of deleted document id db and children

            // Remove all document in Db link to this folder
            self::removeDocumentByFolder($id);

            //Find sub folders and give the same process of deleted
            $subFolders=self::getSubfoldersById($id, true);
            if(!empty($subFolders)){
                foreach($subFolders as $key => $data){
                    self::delete($key, null);
                }
            }
        }  
        // Deleted file in upload (only one time no on subfolders)
        if(!empty($removeDir)){
            $folderPath=self::getFolderPath($folder);
            if($folderPath && file_exists ( $folderPath ) )
                CFileHelper::removeDirectory($folderPath);
        }
        // remove the current folder in DB
        PHDB::remove(self::COLLECTION, array("_id" => new MongoId($id) ));
        return array("result"=>true, "folder"=>array("id"=>$id), "msg"=>Yii::t("common","Folder {what} and all its elements deleted with success",array("{what}"=>$folder["name"])));
    }
    /**
    * remove a document by folder and delete the file on the filesystem
    * a test of Authorization must be done higher in the process 
    * testing the user created the element parent of the document 
    * @return
    */
    public static function removeDocumentByFolder($folder){
        //TODO SBAR - Generate new thumbs if the image is the current image
        $docs = Document::getWhere(array("folderId"=>$folder));
        if (@$docs) 
        {
            //delete all entries in DB
            foreach ($docs as $key => $doc) 
            {
                PHDB::remove( Document::COLLECTION, array("_id"=>$key) );
                //$results[$key] = array( 'result'=>true, "entry" => "deleted");
            }  
            $res= array( 'result'=>true, "msg" => Yii::t("document","no Documents associated") );    
        } else 
            $res = array( 'result'=>true, "msg" => Yii::t("document","no Documents associated") );

        return $res;
    }
    /**
    * aims to return path of parent folders 
    * @string $id to find the parent folder
    * @boolean $onlyPathName is return only url with name of parent or url with name + id for view
    *  return url 
    **/
    public static function getParentsFoldersById($id){
        $parentFolder=self::getById($id);
        $parents=[$parentFolder];
        if(@$parentFolder["parentId"])
            $parents = array_merge($parents, self::getById($parentFolder["parentId"]));
        return $parents;
    }
    /**
    * aims to return path of parent folders 
    * @string $id to find the parent folder
    * @boolean $onlyPathName is return only url with name of parent or url with name + id for view
    *  return url 
    **/
    public static function getParentFoldersPath($id){
        $parentFolder=self::getById($id);
        $result="";
        if(@$parentFolder["parentId"])
            $result .= self::getParentFoldersPath($parentFolder["parentId"]);
        
        return $result.$id."/";
    }
    public static function getFolderPath($folder, $thumb=false){
        $path=Yii::app()->params['uploadDir']."communecter"."/";
        if(@$folder["contextType"] && @$folder["contextId"] && @$folder["docType"]){
            $docTypePath=($folder["docType"]=="image") ? "album" : $folder["docType"];
            $path.=$folder["contextType"]."/".$folder["contextId"]."/".$docTypePath."/";
            if(@$folder["parentId"])
                $path.=self::getParentFoldersPath($folder["parentId"]);
            $path.=(string)$folder["_id"]."/";
            if($thumb)
                $path.=Document::GENERATED_IMAGES_FOLDER."/";
        }else 
            $path=false;
        return $path;
    }
    public static function moveToFolder($ids, $idFolder=null, $type=Document::COLLECTION)
    {   
        if(!empty($idFolder)){
            $folder=self::getById($idFolder);
            $folderName=$folder["name"];
            $newFolderPath=self::getFolderPath($folder);
            $action='$set';
        }else{
            $folderName=Yii::t("common","the root");
            $action='$unset';
        }

        foreach($ids as $id){
            if($type==Document::COLLECTION){
                $movedEl=Document::getById($id);
                $tmp_path=Document::getDocumentPath($movedEl);
                if(!@$newFolderPath)
                    $newFolderPath= Yii::app()->params['uploadDir'].$movedEl["moduleId"]."/".$movedEl["folder"]."/";
                $new_path=$newFolderPath.$movedEl["name"];
                //thumb move
                if($movedEl["doctype"]==Document::DOC_TYPE_IMAGE){
                    $tmp_documentPath=Document::getDocumentPath($movedEl, false, Document::GENERATED_IMAGES_FOLDER."/");
                    $new_documentPath=$newFolderPath.Document::GENERATED_IMAGES_FOLDER."/".$movedEl["name"];
                    if(!file_exists($newFolderPath.Document::GENERATED_IMAGES_FOLDER."/"))
                        mkdir($newFolderPath.Document::GENERATED_IMAGES_FOLDER."/", 0775);
                    rename($tmp_documentPath, $new_documentPath);
                }
                //Move element in its folder
                rename($tmp_path, $new_path);
                $labelUpdate="folderId";
            }else{
                $movedEl=self::getById($id);
                $tmp_path=self::getFolderPath($movedEl);
                 if(!@$newFolderPath){
                    $newFolderPath= Yii::app()->params['uploadDir']."communecter/";//".$movedEl["folder"]."/";
                    if(@$movedEl["contextType"] && @$movedEl["contextId"] && @$movedEl["docType"]){
                        $docTypePath=($movedEl["docType"]=="image") ? "album" : $movedEl["docType"];
                        $newFolderPath.=$movedEl["contextType"]."/".$movedEl["contextId"]."/".$docTypePath."/";
                    }
                }
                $new_path=$newFolderPath.(string)$movedEl["_id"];
                CFileHelper::copyDirectory( $tmp_path, $new_path);
                CFileHelper::removeDirectory($tmp_path);
                $labelUpdate="parentId";
            }
                
            PHDB::update($type,
                            array("_id"=>new MongoId($id)),
                            array( $action=> array($labelUpdate => $idFolder))
                        );
            if($idFolder!="")
                $movedEl[$labelUpdate]=$idFolder;
            else
                $movedEl[$labelUpdate]=(@$movedEl["docType"]=="image" || @$movedEl["doctype"]=="image") ? "album" : "file";
        }     
        return array("result"=>true, "movedEl"=> $movedEl, "msg"=>Yii::t("common","Documents added with success to {what}",array("{what}"=>$folderName)),"movedIn"=>$idFolder); 
    }

    public static function createDocument($targetId,$targetType,$name,$colType="collections",$docType=null,$subDir=array()){
        $target = Element::getByTypeAndId( $targetType, $targetId);
        if(!empty($target)){   
            $pathToCreate=$colType.".";
            if($docType!=null)
                $pathToCreate.=$docType.".";
            if(@$subDir && !empty($subDir)){
                foreach($subDir as $dir){
                    $pathToCreate.=$dir.".";
                    $createdIn=$dir;
                }
            }

            $pathToCreate.=$name;
            PHDB::update($targetType, 
                           array("_id" => new MongoId($targetId) ) , 
                           array('$set' => array($pathToCreate => array("updated"=>new MongoDate(time())) )));
            
            return array("result"=>true, "msg"=>Yii::t("common","Collection {what} created with success",array("{what}"=>$name)),"createdIn"=>@$createdIn);
        } else 
            return array("result"=>false,  "msg"=>Yii::t("common","Something went wrong"));
        
    }
    public static function countSubfolders($id){
        return PHDB::count(self::COLLECTION,array("parentId"=>$id));
    }
    public static function countSubfoldersByContext($id, $type, $docType){
        return PHDB::count(self::COLLECTION,array("contextId"=>$id, "contextType"=>$type, "docType"=>$docType, "parentId"=>array('$exists'=>false)));
    }


}