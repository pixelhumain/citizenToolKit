<?php 
class TranslateActivityStream {
	
	/*

	----------------- ACTIVITY STREAM ----------------- 

	https://evanprodromou.example/profile

	{
	  "@context": "http://www.w3.org/ns/activitystreams",
	  "@type": "Person",
	  "@id": "https://evanprodromou.example/profile.html#me",
	  "displayName": "Evan Prodromou",
	  "alias": "eprodrom",
	  "summary": "Founder of Fuzzy.io. Past founder of Wikitravel and StatusNet. Founding CTO of Breather.",
	  "icon": {
	    "@type": "Image",
	    "url": "https://evanprodromou.example/avatar.png",
	    "width": 128,
	    "height": 128
	  },
	  "url": {
	      "@type": "Link",
	      "href": "https://evanprodromou.example/profile.html",
	      "mediaType": "text/html"
	  },
	  "location": {
	       "@type": "Place",
	       "displayName": "Montreal, Quebec, Canada"
	  }
	}*/
	public static $dataBinding_person = array(
	    "name" => array("name" => "name"),
	);
}