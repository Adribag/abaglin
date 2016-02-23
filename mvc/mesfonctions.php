<?php


function getPage ($dossierRacine)
{
    $result = "";
    
    // $uri va contenir un texte comme
    // /php08/accueil.php?email=toto@gmail.com
    $uri = $_SERVER["REQUEST_URI"];
    
    // http://us3.php.net/manual/fr/function.parse-url.php
    // $path va contenir un texte comme
    // /php08/accueil.php
    $path = parse_url($uri, PHP_URL_PATH);
    
    
     // GERER LE CAS OU ON EST A LA RACINE DU SITE
    if ($path == $dossieRacine)
    {
        $path = "$dossieRacine/index.php";
    }
    // http://us3.php.net/manual/fr/function.pathinfo.php
    // $nomFichier va contenir le texte
    // accueil
    $nomFichier = pathinfo($path, PATHINFO_FILENAME);
    $extension  = pathinfo($path, PATHINFO_EXTENSION);
    
    $result = [$nomFichier, $extension];
    
    return $result;
}


function getInput ($name)
{
    $resultat = "";
    
    // VERIFIER SI L'INFO EST FOURNIE
    if (isset($_REQUEST["$name"]))
    {
        // trim ENLEVES LES ESPACES AU DEBUT ET A LA FIN
        $resultat = trim($_REQUEST["$name"]);
    }
    elseif (isset($_COOKIE["$name"]))
    {
        // trim ENLEVES LES ESPACES AU DEBUT ET A LA FIN
        $resultat = trim($_COOKIE["$name"]);
    }
    
    return $resultat;
}

function getEmail ($name)
{
    $email = getInput($name);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    
    return $email;
}


function verifLogin ($email, $pwd)
{
    // MODE PARANO
    // AU DEPART ON CONSIDERE QUE LA PERSONNE N'EST PAS CONNECTE
    $resultat = [ 0, "", "", "" ];

    // SI UNE INFO EST VIDE
    // ON RENVOIE TOUT DE SUITE false
    if (($email == "") || ($pwd == ""))
    {
        return $resultat;
    }
    
    // VERIFIER SI ON TROUVE UNE CORRESPONDANCE DANS LE FICHIER user.csv
    // LIRE LES INFOS DU FICHIER
    // http://php.net/manual/fr/function.file-get-contents.php
    $contenuUser = file_get_contents("mvc/model/user.csv");
        
    // DECOUPE LIGNE PAR LIGNE
    // http://php.net/manual/fr/function.explode.php
    // \n REPRESENTE LE RETOUR A LA LIGNE
    $tableauLigne = explode("\n", $contenuUser);
        
    // $ligne va contenir chaque valeur du tableau
    foreach($tableauLigne as $ligne)
    {
        // ATTENTION: IL NE FAUT PAS AVOIR DE VIRGULE DANS LE MOT DE PASSE
        // $tableauUser = explode(",", $ligne);
        // $emailUser = $tableauUser[0];
        // $passwordUser = $tableauUser[1];

        // CODE PLUS COMPACT
        // ON DECOUPE CHAQUE LIGNE EN COLONNES
        // ex: long-hai@gmail.com,1234,long-hai,9
        // $emailUser       => long-hai@gmail.com
        // $passwordUser    => 1234
        // $pseudoUser      => long-hai
        // $levelUser       => 9
        list($emailUser, $passwordUser, $pseudoUser, $levelUser) = explode(",", $ligne);
        
        if (($email == $emailUser) && ($pwd == $passwordUser))
        {
            // ON A TROUVE UNE CORRESPONDANCE
            $resultat = [ $levelUser, $pseudoUser, $emailUser, $passwordUser ];
            // ON N'A PAS BESOIN DE POURSUIVRE LA BOUCLE
            break;
        }
    }
    
    return $resultat;
}

function verifLoginMd5 ($email, $md5pwd)
{
    
    $resultat = [ 0, "", "", "" ];
    
    if (($email == "") || ($md5pwd == ""))
    {
        return $resultat;
    }
    
    
    $model = new Model;
    $pdo = $model->getConnexion();
        
        
    $requeteSQL=
<<<CODESQL
SELECT * 
FROM `User`
WHERE `email` = :email
CODESQL;

    $statement = $pdo->prepare($requeteSQL);
    $statement->bindValue(":email", $email, PDO::PARAM_STR);
    $statement->execute();
    //print_r($statement);
    $reponse = $statement->fetch();
    //print_r($reponse);

          $emailBDD = $reponse["email"];
          $pwdBDD   = $reponse["password"];
          $pseudoBDD= $reponse["pseudo"];
          $levelBDD = $reponse["level"];
    
    
   if($md5pwd == md5Prive($pwdBDD))
   {
       $resultat = [ $levelBDD, $pseudoBDD, $emailBDD, $md5pwd ];
   }
/*    
    //////////////////////////////////////////////////////////////
    // MODE PARANO
    // AU DEPART ON CONSIDERE QUE LA PERSONNE N'EST PAS CONNECTE

    $resultat = [ 0, "", "", "" ];

    // SI UNE INFO EST VIDE
    // ON RENVOIE TOUT DE SUITE false
    if (($email == "") || ($md5pwd == ""))
    {
        return $resultat;
    }
    
    // VERIFIER SI ON TROUVE UNE CORRESPONDANCE DANS LE FICHIER user.csv
    // LIRE LES INFOS DU FICHIER
    // http://php.net/manual/fr/function.file-get-contents.php
    $contenuUser = file_get_contents("mvc/model/user.csv");
        
    // DECOUPE LIGNE PAR LIGNE
    // http://php.net/manual/fr/function.explode.php
    // \n REPRESENTE LE RETOUR A LA LIGNE
    $tableauLigne = explode("\n", $contenuUser);
        
    // $ligne va contenir chaque valeur du tableau
    foreach($tableauLigne as $ligne)
    {
        // ATTENTION: IL NE FAUT PAS AVOIR DE VIRGULE DANS LE MOT DE PASSE
        // $tableauUser = explode(",", $ligne);
        // $emailUser = $tableauUser[0];
        // $passwordUser = $tableauUser[1];

        // CODE PLUS COMPACT
        // $passwordUser contient le mot de passe en clair
        list($emailUser, $passwordUser, $pseudoUser, $levelUser) = explode(",", $ligne);
        
        // CALCULE LA CLE PRIVEE CORRESPONDANTE
        $clePriveeUser = md5Prive($passwordUser);
        
        if (($email == $emailUser) && ($md5pwd == $clePriveeUser))
        {
            // ON A TROUVE UNE CORRESPONDANCE
            $resultat = [ $levelUser, $pseudoUser, $emailUser, $clePriveeUser ];
            // ON N'A PAS BESOIN DE POURSUIVRE LA BOUCLE
            break;
        }
    }
    */
    return $resultat;
}


function verifLogin64 ()
{
    // RECUPERER LE COOKIE data64
    $data64 = getInput("data64");
    
    // REPASSER LE CONTENU EN UTF-8
    // http://php.net/manual/en/function.base64-decode.php
    $dataJSON   =  base64_decode($data64);
    
    // REPASSER DE JSON A UN TABLEAU ASSOCIATIF PHP
    // http://php.net/manual/en/function.json-decode.php
    $tableauData = json_decode($dataJSON, true);
    
    // EXTRAIRE CHAQUE INFO DU TABLEAU
    $email      = $tableauData["email"];
    //$pwd    = $tableauData["pwd"];
    $md5pwd     = $tableauData["md5pwd"];
    
    // FAIRE APPEL AU verifLoginMd5
    // RENVOYER LE RESULTAT
    return verifLoginMd5($email, $md5pwd);
    
}


function md5Prive ($pwd)
{
    // LA CLE PRIVEE N'EST ACCESSIBLE QUE PAR PHP
    $clePrivee = 
<<<CLEPRIVEE

MON MOT DE PASSE SUPER DUR A DEVINER
dfjkhfdskgl
dsfgkljsdlk
è_(_ç(ç_

CLEPRIVEE;
    
    // LA SIGNATURE SERA LA CLE PUBLIQUE TRANSMISE DANS LES COOKIES
    $signature = md5($clePrivee.$pwd);
    
    return $signature;
}


function convertMini ($cheminWorkspace, $extension, $cheminMini, $largeurMini, $hauteurMini)
{
    $resultat = "";
    
    // CHARGER L'IMAGE SOURCE $cheminWorkspace DANS LA MEMOIRE PHP
    // http://php.net/manual/en/function.imagecreatefromjpeg.php
    // http://php.net/manual/en/function.imagecreatefrompng.php
    // http://php.net/manual/en/function.imagecreatefromgif.php
    $imageSource = FALSE;
    switch ($extension)
    {
        case 'jpg':
        case 'jpeg':
            $imageSource = imagecreatefromjpeg($cheminWorkspace);
            break;
        case 'gif':
            $imageSource = imagecreatefromgif($cheminWorkspace);
            break;
        case 'png':
            $imageSource = imagecreatefrompng($cheminWorkspace);
            break;
        default:
            $imageSource = FALSE;
            break;
    }
    
    // RESERVER LA MEMOIRE PHP POUR L'IMAGE MINI
    $imageMini = FALSE;
    // http://php.net/manual/en/function.imagecreatetruecolor.php
    $imageMini = imagecreatetruecolor($largeurMini, $hauteurMini);
    
    // GARDER LA TRANSPARENCE
    // http://php.net/manual/en/function.imagesavealpha.php
    // http://php.net/manual/en/function.imagealphablending.php
    imagealphablending($imageMini, false);
    imagesavealpha($imageMini, true);
    
    // CREER LA COPIE EN MINIATURE
    // http://php.net/manual/en/function.imagecopyresampled.php
    if (($imageSource != FALSE) && ($imageMini != FALSE))
    {
        // http://php.net/manual/en/function.imagesx.php
        // http://php.net/manual/en/function.imagesy.php
        $largeurSource = imagesx($imageSource);
        $hauteurSource = imagesy($imageSource);
        
        // CALCULER LE PLUS GRAND CARRE
        $sourceX = 0;
        $sourceY = 0;
        $tailleSource = $hauteurSource;
        
        if ($largeurSource < $hauteurSource)
        {
            $tailleSource = $largeurSource;
            // DECALER L'ORIGINE POUR CENTRER LA MINIATURE
            $sourceY      = ($hauteurSource - $largeurSource) / 2;
        }
        else
        {
            // DECALER L'ORIGINE POUR CENTRER LA MINIATURE
            $sourceX     = ($largeurSource - $hauteurSource) / 2;
        }
        
        imagecopyresampled(
            $imageMini,     $imageSource,
            0,              0,              // ORIGINE MINI
            $sourceX,       $sourceY,       // ORIGINE SOURCE
            $largeurMini,   $hauteurMini,   // LARGEUR ET HAUTEUR MINI
            $tailleSource,  $tailleSource   // LARGEUR ET HAUTEUR SOURCE
        );
        
        // ENREGISTRER L'IMAGE MINI DANS LE FICHIER $cheminMini
        // http://php.net/manual/en/function.imagegif.php
        // http://php.net/manual/en/function.imagejpeg.php
        // http://php.net/manual/en/function.imagepng.php
        switch ($extension)
        {
            case 'jpg':
            case 'jpeg':
                imagejpeg($imageMini, $cheminMini);
                break;
            case 'gif':
                imagegif($imageMini, $cheminMini);
                break;
            case 'png':
                imagepng($imageMini, $cheminMini);
                break;
            default:
                break;
        }

        $resultat = $cheminMini;
    }
    
    
    return $resultat;
}


function uploadImage ($nameInput, $largeurMini, $hauteurMini)
{
    $cheminWorkspace = "";
    $cheminMini      = "";
    //echo $nameInput;
    //echo $largeurMini;
    //echo $hauteurMini;
    // VERIFIER SI IL Y UN FICHIER UPLOADE
    if (isset($_FILES["$nameInput"]))
    {
        // PHP A MIS EN QUARANTAINE LE FICHIER A CET ENDROIT
        $cheminTemporaire   = $_FILES["$nameInput"]["tmp_name"];
        $nomFichierOrigine  = $_FILES["$nameInput"]["name"];
        // CONVERTIR EN NOMBRE
        $tailleFichier      = intval($_FILES["$nameInput"]["size"]);
        $codeErreur         = intval($_FILES["$nameInput"]["error"]);
    
        // ON VEUT UN FICHIER BIEN TRANSFERE ET NON VIDE
        if (($codeErreur == 0) && ($tailleFichier > 0))
        {
            // VERIFIER SI LE SUFFIXE EST AUTORISE
            // http://php.net/manual/en/function.pathinfo.php
            $extension = pathinfo($nomFichierOrigine, PATHINFO_EXTENSION);
            // CONVERTIR EN MINUSCULES
            // http://php.net/manual/en/function.strtolower.php
            $extension = strtolower($extension);
            
            $tabExtensionOk = [ "jpeg", "jpg", "png", "gif", "svg" ];
            
            // http://php.net/manual/en/function.in-array.php
            if (in_array($extension, $tabExtensionOk))
            {
                // DEPLACER LE FICHIER DANS NOTRE WORKSPACE
                // SECURITE: IL FAUT VERIFIER SI LE SUFFIXE EST AUTORISE
                // SECURITE2: IL FAUT NORMALISER LE NOM DU FICHIER
                $cheminWorkspace = "media/upload/$nomFichierOrigine";
                // http://php.net/manual/en/function.move-uploaded-file.php
                move_uploaded_file($cheminTemporaire, $cheminWorkspace);
                
                if ($extension != "svg")
                {
                    // ON PEUT CREER UN VERSION MINIATURE
                    $cheminMini     = "media/upload/mini/mini-$nomFichierOrigine";
                    
                    // LA FONCTION convertMini VA CREER UNE VERSION MINIATURE
                    $cheminMini = convertMini($cheminWorkspace, $extension, $cheminMini, $largeurMini, $hauteurMini);
                    
                }
            }
            
        }
    }
    
    return [ $cheminWorkspace, $cheminMini ];
}


function afficheGalerie ()
{
    $htmlGalerie = "";

    // CHERCHE LA LISTE DE TOUS LES FICHIERS DANS LE DOSSIER mini
    // http://php.net/manual/en/function.glob.php
    $tableauImage = glob("media/upload/mini/*");
    
    // PARCOURIR LES ELEMENTS DU TABLEAU
    foreach($tableauImage as $cheminImage)
    {
        // VERIFIER SON EXTENSION
        // http://php.net/manual/en/function.pathinfo.php
        $extension = pathinfo($cheminImage, PATHINFO_EXTENSION);
        // CONVERTIR EN MINUSCULES
        // http://php.net/manual/en/function.strtolower.php
        $extension = strtolower($extension);
        
        $tabExtensionOk = [ "jpeg", "jpg", "png", "gif", "svg" ];
        // http://php.net/manual/en/function.in-array.php
        if (in_array($extension, $tabExtensionOk))
        {
            // CONSTRUIRE LE HTML POUR AFFICHER L'IMAGE
            $htmlImage =
<<<CODEHTML
<img src="$cheminImage" />
CODEHTML;
            // AJOUTER LE CODE HTML A CELUI DE LA GALERIE
            $htmlGalerie = $htmlGalerie . $htmlImage;
        }
        
    }
    
    // AFFICHER LE CODE HTML DE LA GALERIE
    echo $htmlGalerie;
}

function afficheBlog ($pageAffiche,$articleAffiche){

    $model = new Model;
    $pdo = $model->getConnexion();

    $listArticle = "";
    $limit=($pageAffiche-1)*$articleAffiche;
    $recupArticle=
<<<MYSQL
SELECT *
FROM `Blog`
WHERE `statut` = 'publie'
ORDER BY id DESC
LIMIT $limit , $articleAffiche
MYSQL;
    
    $statement = $pdo->prepare($recupArticle);
    $statement->execute();
    
    //print_r($statement);
    
    //$reponse = $statement->fetch();
    //print_r($reponse);
    
    while($reponse = $statement->fetch())
    {
        $titreBlog=$reponse['titre'];
        $texteBlog=$reponse['texte'];
        $imageBlog=$reponse['image'];
        $dateBlog=$reponse['date'];
        $statutBlog=$reponse['statut'];
        
        if($statutBlog == "publie")
        {
        $listArticle=
<<<ARTICLE
<article>
    <h4>$titreBlog</h4>
    <p>$texteBlog</p>
    <p>$imageBlog</p>
    <p class="dateBlog">$dateBlog</p>
</article>
ARTICLE;
        }
        else
        {
        $listArticle= "";
        }
        echo $listArticle;
    }
    /* $listArticle="";
    //on recupère les fichiers du dossier blog
    $dossierArticle = glob("mvc/model/blog/*");
    //on commence a lire le tableau par la fin
    $dossierArticle = array_reverse($dossierArticle);
    //on recupère les infos de $donne dans chaque $dossierArticle
    foreach($dossierArticle as $donne)
    {
    //on recupère les infos dans les fichiers du dossier blog
    $listArticle = file_get_contents($donne);
    
    echo $listArticle;
    }
    */
    
    
}
function navBlog ($nbPage,$pageAffiche)
{
    $pageLi="";
    
    for($i=1;$i<=$nbPage;$i++)
    {
    
        if($i == $pageAffiche)
        {
        $pageLi .= '<li class="active"><a href="blog.php?page='.$i.'">'.$i.'</a></li>';
        }
        else
        {
        $pageLi .= "<li><a href='blog.php?page=$i'>$i</a></li>";
        }
        
    }
$afficheNav=
<<<NAV
<nav class="pagi">
    <ul>
        $pageLi
    </ul>
</nav>
NAV;
    
    echo $afficheNav;
}

function loadClass($nomClass)
{
    $cheminClass="mvc/class/$nomClass.php";
    
    if(!is_file($cheminClass))
    {
        $cheminTemplate = "mvc/class/Template.php";
        if(is_file($cheminTemplate))
        {
            $codeTemplate = file_get_contents($cheminTemplate);
            
            $codeClass = str_replace("Template", $nomClass              , $codeTemplate);
            $codeClass = str_replace("AUTEUR"  , "Adrien"               , $codeClass);
            $codeClass = str_replace("DATE"    , date("Y-m-d H:i;s")    , $codeClass);
            
            file_put_contents($cheminClass, $codeClass);
        }
    }
    if(is_file($cheminClass))
    {
        include_once($cheminClass);
    }
}
