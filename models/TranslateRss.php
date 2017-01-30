<?php 
class TranslateRss {
	

	public static $dataBinding_news = array(
	    
		"title" 		=> array(	"type" => "description",
									"authorId" => 'target.id',
						   			"authorType" 	=> "target.type"),
		
		"description" 	=> array(	"type" => "description",
									"authorId" => 'target.id',
						   			"authorType" 	=> "target.type"),
		"lastBuildDate" => array(	"valueof" => "date"),
		"link" 			=> array(	"communecter" 	=> array(	
											"valueOf" => 'target.id',
				   							"type" 	=> "url",
				   							"authorType" 	=> "target.type")),
	);
}