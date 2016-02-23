<?php

// CHARGER NOS FONCTIONS
include_once("mvc/mesfonctions.php");

// DIRE A PHP D'ACTIVER LA FONCTION loadClass
// QUAND IL AURA BESOIN DE LA DEFINITION D'UNE CLASSE
// http://php.net/manual/fr/function.spl-autoload-register.php

spl_autoload_register("loadClass");