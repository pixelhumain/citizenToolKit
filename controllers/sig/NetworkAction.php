<?php
class NetworkAction extends CAction
{
    public function run()
    {
      $sigParams = array(
        "sigKey" => "SV",

        /* MAP */
        "mapHeight" => 450,
        "mapTop" => 0,
        "mapColor" => '',  //ex : '#456074', //'#5F8295', //'#955F5F', rgba(69, 116, 88, 0.49)
        "mapOpacity" => 1, //ex : 0.4

        /* MAP LAYERS (FOND DE CARTE) */
        "mapTileLayer" 	  => 'http://{s}.tile.thunderforest.com/landscape/{z}/{x}/{y}.png',//'http://{s}.tile.thunderforest.com/landscape/{z}/{x}/{y}.png', //'http://{s}.tile.stamen.com/toner/{z}/{x}/{y}.png'
        "mapAttributions" => '<a href="http://www.opencyclemap.org">OpenCycleMap</a>',	 	//'Map tiles by <a href="http://stamen.com">Stamen Design</a>'

        /* MAP BUTTONS */
        //"mapBtnBgColor" => '#E6D414',
        //"mapBtnColor" => '#213042',
        //"mapBtnBgColor_hover" => '#5896AB',

        /* USE */
        "usePanel" => true,
        "useRightList" => true,
        "useZoomButton" => true,
        "useHomeButton" => true,
        "useHelpCoordinates" => false,
        "useFullScreen" => true,
        "useResearchTools" => true,
        "useFilterType"=>true,
        "useChartsMarkers" => true,

        /* TYPE NON CLUSTERISÉ (liste des types de données à ne pas inclure dans les clusters sur la carte (marker seul))*/
        "notClusteredTag" => array("citoyens"),

        /* COORDONNÉES DE DÉPART (position géographique de la carte au chargement) && zoom de départ */
        "firstView"		  => array(  "coordinates" => array(-21.13318, 55.5314),
                       "zoom"		  => 9),
          );

        $controller=$this->getController();
        $controller->renderPartial("networkSV", array("sigParams" => $sigParams));
    }
}
