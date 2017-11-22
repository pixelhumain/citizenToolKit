<?php
/*
Contains anything generix for the site
 */


class SmartData
{
	const SCALAIR_COLLECTION = "scalair";
	
	public static function getScalairData($indexStep=10, $indexMin=0){

		$datas = PHDB::findAndSortAndLimitAndIndex ( SmartData::SCALAIR_COLLECTION , array(), 
  										  			 array(), $indexStep, $indexMin);
		return $datas;
	}

}

