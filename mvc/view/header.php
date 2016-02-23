<?php

include_once("mvc/mesfonctions.php");
$model = new Model;
$cookie = $model->verifUserCookie();

$pseudo = $cookie[1];
$lvl = $cookie[0];
?>
<!DOCTYPE Html>
<html>
    
    <head>
        <link rel="shortcut icon" href="favicon.png">
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
        <meta charset="utf-8">
        <title>Abaglin.com</title>
        <link rel="stylesheet" href="media/css/fonts.css" type="text/css" />
        <link rel="stylesheet" href="media/css/style.css" type="text/css" />
        <script type="text/javascript" src="media/js/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="media/js/script.js"></script>
    </head>
    <body>
        
    
        <header>
            <div class="contentHeader">
                <p class="construc"> Site en construction !</p>
                <h1>Abaglin.com</h1>
                <?php
                if($lvl>0)
                {
                $affichLog=
<<<HTML
<h2>
    $pseudo
    <a href="logout.php">Déconnexion</a>
</h2>
HTML;
            }
            echo $affichLog
            ?>
            <nav>
                <ul>
                    <li>
                        <a href="index.php" active>Accueil</a>
                    </li>
                    
                    
                    <?php /*
                    $_COOKIE["$pseudoBDD"];
                    echo $pseudoBDD;
                    if($lvlBDD == 0)
                    {
                        $html=
<<<HTML
<li>
<a href="#">Deconnexion</a>
</li>
HTML;
                    }
                    else
                    {
                        $html=
<<<HTML
<li>
<a href="login.php">Connexion</a>
</li>
HTML;
                    }
                    echo $html;*/
                    ?>
                         <li>
                            <a href="graphisme.php">Graphisme</a>
                        </li>
                        <li>
                            <a href="web.php">Web</a>
                        </li>
                        <li>
                            <a href="#">Mon CV</a>
                        </li>
                        <li>
                            <a href="#">Contact</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>
        