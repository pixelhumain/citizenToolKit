<?php
/**
* to create statistic
* Can be launch by cron
*/
class CreateGlobalStatAction extends CAction
{
    public function run() {
    	echo date('Y-m-d H:i:s')." - Démarrage du script pour calculer les statistiques<br/>";
    	Stat::createGlobalStat();
    	echo date('Y-m-d H:i:s')." - Fin du script pour calculer les statistiques<br/>";

    }
}
