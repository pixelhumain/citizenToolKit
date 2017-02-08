<?php

class Tags {

	/**
	 * From an array of tags (String), the new tags will be saved 
	 * Filter the array and return all the valid tags
	 * @param array of tags (String)
	 * @return all the valid tags 
	 */
	public static function filterAndSaveNewTags($tags) {

	    $res = array();
	    $existingTags = Tags::getActiveTags();

	    if(!empty($tags)){
	      foreach($tags as $tag) {
	      	$newTags = self::checkAndGetTag($tag);
	        if(!in_array($newTags, $existingTags)) {
	          //TODO : Add here how to define if a tag is valid or not
	          PHDB::update( PHType::TYPE_LISTS,array("name"=>"tags"), array('$push' => array("list"=>$newTags)));
	        }
	      	array_push($res, $newTags);
	      }
	    }

	    return $res;
	}

  /**
   * Retrieve the active tags list
   * @return array of tags (String)
   */
  public static function getActiveTags() {

  	$res = array();
  	//The tags are found in the list collection, key tags
  	//TODO : écrire la liste de suggestion de tags
  	$tagsList = PHDB::findOne( PHType::TYPE_LISTS,array("name"=>"tags"), array('list'));
  	
  	if (!empty($tagsList['list']))
  		$res = $tagsList['list'];

  	return $res;
  }

	public static function checkAndGetTag($tag) {
  		$carac = array("\r\n", "\n");
		$newtags = str_replace($carac, " ", $tag);
		$newtags = str_replace("#", "", $newtags);
		return $newtags;
 	}

}
?>