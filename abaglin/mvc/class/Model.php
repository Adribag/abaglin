<?php

// LES NOMS DE CLASSE COMMENCENT PAR UNE MAJUSCULE
class Model 
{
    // ATTRIBUTS OU PROPRIETES
    protected $pdo;
    
    // METHODES
    function __construct ()
    {
        // null EST MOT CLE DE PHP POUR DIRE QU'IL N'Y A PAS D'OBJET
        $this->pdo = null;
    }
    
     function getConnexion ()
    {
        if ($this->pdo == null)
        {
            // AU DEPART ON N'A PAS DE CONNEXION
            // ALORS ON LA CREE UNE SEULE FOIS
            
            // GENERALEMENT ON CREE UNE BASE DE DONNEES PAR PROJET
            $nomDatabase  = "garagescore";
            $userDatabase = "adrienb";
            $mdpDatabase  = "";
            $hostDatabase = "127.0.0.1";
            
            // Data Source Name
            $dsn = "mysql:dbname=$nomDatabase;host=$hostDatabase;charset=utf8;";
            
            // LA CLASSE PDO GERE LA CONNEXION ENTRE PHP ET MYSQL
            // ON CREE UN OBJET DE LA CLASSE PDO (PHP Data Object)
            $this->pdo = new PDO($dsn, $userDatabase, $mdpDatabase);
            
            // https://openclassrooms.com/courses/pdo-comprendre-et-corriger-les-erreurs-les-plus-frequentes
            // AFFICHER LES MESSAGES D'ERREUR DE MYSQL
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        }
        
        // RENVOIE LA CONNEXION EN COURS
        return $this->pdo;
    }
    
    function readContentSQL ($nomPage)
    {
        $contenu = "";
        // ON CHERCHE DANS LA TABLE Page
        $ligne = $this->readRow("Page", $nomPage, "url");
        if ($ligne !== FALSE)
        {
            $contenu = $ligne["contenu"];
        }
        else
        {
            // ON CHERCHE ENSUITE DANS LA TABLE Blog
            $ligne = $this->readRow("Blog", $nomPage, "url");
            if ($ligne !== FALSE)
            {
                $contenu = $ligne["contenu"];
                $image   = $ligne["image"];
                
                $htmlImage = "";
                if ($image != "")
                {
                    $htmlImage =
<<<CODEHTML
<div>
    <img class="img-responsive" src="$image" alt="$image"/>
</div>
CODEHTML;
                }
                
                $contenu =
<<<CODEHTML
<div class="col-sm-6">
    $htmlImage
</div>
<div class="col-sm-6">
    $contenu
</div>
CODEHTML;
            }
        }
        return $contenu;
    }
    
    function verifUserLevel ($pageLevel)
    {
        $resultat = null;
        
        $userLevel = 0;

        list($userLevel, $user) = $this->verifUserCookie();
        if ($userLevel > $pageLevel)
        {
            $resultat = $user;    
        }
        
        return $resultat;
    }
    
    function verifUserCookie ()
    {
        $userLevel      = 0;
        $user           = null;
        $email          = "";
        $passwordMd5    = "";
        
        // LES INFOS DE EMAIL ET PASSWORD
        // SONT MAINTENANT DANS LE COOKIE data64
        $data64     = getInput("dataCookie");
        $texteJSON  = base64_decode($data64);
        $tabData    = json_decode($texteJSON, true);
        
        if (isset($tabData["email"]))
        {
            $email = $tabData["email"];
        }

        if (isset($tabData["pwd"]))
        {
            $passwordMd5 = $tabData["pwd"];
        }
        if (isset($tabData["level"]))
        {
            $level = $tabData["level"];
        }
       
        if (isset($tabData["pseudo"]))
        {
            $user = $tabData["pseudo"];
        }
        
        return [ $level, $user, $email ];
        
    }
    
    function verifNewsletter ($email)
    {
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

SELECT * FROM Newsletter
WHERE
`email` = :email

CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ":email" => $email,  
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
        return $statement;
    }


    function verifUser ($email, $password)
    {
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

SELECT * FROM User
WHERE
`email` = :email
AND
`password` = :password

CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ":email"     => $email,  
                ":password"  => $password,  
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
        return $statement;
    }

    function verifUserMd5 ($email, $passwordMd5)
    {
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

SELECT * FROM User
WHERE
`email` = :email

CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ":email"     => $email,  
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);

        $user = null;
        if ($statement != FALSE)
        {
            // ON A TROUVE UN UTILISATEUR
            // RECUPERER LES INFOS DE LA LIGNE TROUVEE
            $user = $statement->fetch();
            // VERIFIER LES MD5
            $userPasswordMd5 = md5Prive($user["password"]);
            if ($passwordMd5 != $userPasswordMd5)
            {
                // ON N'A PAS LES BONS IDENTIFIANTS
                $user = null;
            }
            
        }
        
        return $user;
    }


    function createNewsletter ($email, $nom)
    {
        // AJOUTER LES INFOS MANQUANTES
        $date   = date('Y-m-d H:i:s');
        $statut = '0';
        $ip     = $_SERVER['REMOTE_ADDR'];
        
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

INSERT INTO Newsletter
(`email`, `nom`, `ip`, `date`, `statut`)
VALUES
(:email, :nom, '$ip', '$date', '$statut')

CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ":email" => $email,  
                ":nom"   => $nom,  
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
    }
    
    function createContact ($email, $nom, $message)
    {
        // AJOUTER LES INFOS MANQUANTES
        $date   = date('Y-m-d H:i:s');
        $statut = '0';
        $ip     = $_SERVER['REMOTE_ADDR'];
        
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

INSERT INTO Contact
(`email`, `nom`, `message`, `ip`, `date`, `statut`)
VALUES
(:email, :nom, :message, '$ip', '$date', '$statut')

CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ":email"     => $email,  
                ":nom"       => $nom,  
                ":message"   => $message,  
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
    }
    
    function createBlog ($titre, $url, $contenu, $image, $auteur)
    {
        // AJOUTER LES INFOS MANQUANTES
        $date   = date('Y-m-d H:i:s');

        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

INSERT INTO Blog
(`titre`, `url`, `contenu`, `auteur`, `date`, `image`)
VALUES
(:titre, :url, :contenu, '$auteur', '$date', '$image')

CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ":titre"     => $titre,  
                ":url"       => $url,  
                ":contenu"   => $contenu,  
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
    }

    function updateBlog ($id, $titre, $url, $contenu, $image, $auteur, $date)
    {
        $sqlImage = "";
        if ($image != "")
        {
            $sqlImage = "`image` = '$image',";
        }
        
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

UPDATE Blog
SET
`titre`     = :titre, 
`url`       = :url, 
`contenu`   = :contenu, 
`auteur`    = :auteur, 
$sqlImage
`date`      = :date

WHERE `id` = $id

CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ":titre"     => $titre,  
                ":url"       => $url,  
                ":contenu"   => $contenu,  
                ":date"      => $date,  
                ":auteur"    => $auteur,  
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
    }
    
    function readNewsletter ()
    {
        $resultat = "";
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

SELECT * FROM Newsletter
ORDER BY id DESC

CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
        while($ligne = $statement->fetch())
        {
            $id         = $ligne["id"]; 
            $email      = $ligne["email"]; 
            $nom        = $ligne["nom"];
            
            // ON INSERE DANS LE HTML LES INFOS LUES DEPUIS MYSQL 
            $codeHtml   = 
<<<CODEHTML
<tr class="ligne-$id">
    <td>$id</td>
    <td>$email</td>
    <td>$nom</td>
    <td>
<a class="action-delete" data-table="Newsletter" data-id="$id" href="#delete-newsletter">
    supprimer
</a>
    </td>
</tr>
CODEHTML;

            $resultat .= $codeHtml;
        }
        
        return $resultat;
    }
    
    function readTable ($nomTable, $indice, $quantite)
    {
        $resultat = "";
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

SELECT * FROM `$nomTable`
ORDER BY id DESC
LIMIT $indice, $quantite
CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
        // NE PRENDRE QUE LE TABLEAU ASSOCIATIF
        while($ligne = $statement->fetch(PDO::FETCH_ASSOC))
        {
            $htmlBlocTd = "";
            // ON AURA TOUJOURS LA COLONNE id
            $id         = $ligne["id"];
            
            foreach($ligne as $cle => $valeur)
            {
                $htmlLigne =
<<<HTMLLIGNE
<td class="$cle">$valeur</td>
HTMLLIGNE;
                $htmlBlocTd = $htmlBlocTd . $htmlLigne;
            }
            
            // ON INSERE DANS LE HTML LES INFOS LUES DEPUIS MYSQL 
            $codeHtml   = 
<<<CODEHTML
<tr class="ligne-$id">
    $htmlBlocTd
    <td>
        <a class="action-edit" data-table="$nomTable" data-id="$id" href="#edit-contact-$id" data-toggle="modal" data-target="#myModal">
            modifier
        </a>
    </td>
    <td>
        <a class="action-delete" data-table="$nomTable" data-id="$id" href="#delete-contact-$id">
            supprimer
        </a>
    </td>
</tr>
CODEHTML;

            $resultat .= $codeHtml;
        }
        
        return $resultat;
    
    }
    
    
    function readContact ()
    {
        $resultat = "";
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

SELECT * FROM Contact
ORDER BY id DESC

CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
        while($ligne = $statement->fetch())
        {
            $id         = $ligne["id"]; 
            $email      = $ligne["email"]; 
            $nom        = $ligne["nom"];
            $message    = $ligne["message"];
            
            // ON INSERE DANS LE HTML LES INFOS LUES DEPUIS MYSQL 
            $codeHtml   = 
<<<CODEHTML
<tr class="ligne-$id">
    <td>$id</td>
    <td>$email</td>
    <td>$nom</td>
    <td><pre>$message</pre></td>
    <td>
<a class="action-delete" data-table="Contact" data-id="$id" href="#delete-contact">
    supprimer
</a>
    </td>
</tr>
CODEHTML;

            $resultat .= $codeHtml;
        }
        
        return $resultat;
    }

    function getNbTotal ($nomTable)
    {
        $pdo = $this->getConnexion();
        $total = $pdo->query("SELECT COUNT(*) as total FROM $nomTable")->fetchColumn();
        
        return $total;
    }

    function getHtmlPagination($nomTable, $index, $quantite)
    {
        $resultat = "";
        if ($quantite > 0)
        {
            $total = $this->getNbTotal($nomTable);
            $nbPage = ceil($total / $quantite);
            // ON N'A PAS BESOIN DE PAGINATION POUR UNE SEULE PAGE
            if ($nbPage > 1)
            {
                $resultat .= 
<<<CODEHTML
<ul class="pagination">
    <li class="prev">
      <a href="#" aria-label="Previous">
        <span aria-hidden="true">&laquo;</span>
      </a>
    </li>
CODEHTML;
                for ($lien = 1; $lien <= $nbPage; $lien++)
                {
                    $indexLien = ($lien-1) * $quantite;
                    
                    $classLien = "index";
                    if (($indexLien <= $index) && ($index < $indexLien + $quantite))
                    {
                        $classLien = "index active";
                    }
    
                    $resultat .= 
<<<CODEHTML
<li class="$classLien"><a href="?index=$indexLien&quantite=$quantite">$lien</a></li>
CODEHTML;
    
                }
                $resultat .=
<<<CODEHTML
    <li class="next">
      <a href="#" aria-label="Next">
        <span aria-hidden="true">&raquo;</span>
      </a>
    </li>
</ul>
CODEHTML;
            }
        }
        return $resultat;
    }
    
    function readBlogPublic ($index, $quantite)
    {
        $resultat = "";
        // CREER LA REQUETE SQL
        $requeteSQL = 
<<<CODESQL

SELECT * FROM Blog
ORDER BY date DESC, id DESC
LIMIT $index, $quantite
CODESQL;

        // PREPARER LES BINDS
        // PROTECTION CONTRE LES INJECTIONS SQL
        $tableauBind = [ 
                ];
        
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
        while($ligne = $statement->fetch())
        {
            $id         = $ligne["id"]; 
            $titre      = $ligne["titre"]; 
            $url        = $ligne["url"]; 
            $contenu    = $ligne["contenu"];
            $date       = $ligne["date"]; 
            $image      = $ligne["image"];
            
            $htmlImage  = "";
            if ($image != "")
            {
                $htmlImage =
<<<HTMLIMAGE
    <div>
        <img class="img-responsive" src="$image" alt="$image" />
    </div>
HTMLIMAGE;
            }
            
            $htmldate = "";
            if ($date != "")
            {
                $time = strtotime($date);
                $maDate = date("d/m/Y", $time);
                
                $htmlDate =
<<<HTMLDATE
<blockquote>
<footer>publié le $maDate</footer>
</blockquote>
HTMLDATE;
            }
            
            $htmlTitre = "";
            if ($url != "")
            {
                $htmlTitre = '<a href="'.$url.'.html">'.$titre.'</a>';
            }
            else
            {
                $htmlTitre = '<a href="#article-'.$id.'">'.$titre.'</a>';
            }
            
            // ON INSERE DANS LE HTML LES INFOS LUES DEPUIS MYSQL 
            $codeHtml   = 
<<<CODEHTML
<div class="row ligne-$id">
    <div class="col-sm-4">
        $htmlImage
    </div>
    <div class="col-sm-6">
        <h4 title="$id">$htmlTitre</h4>
        <content>$contenu</content>
        $htmlDate
    </div>
</div>
CODEHTML;

            $resultat .= $codeHtml;
        }
        
        return $resultat;
    }

    function readRow ($table, $valeur, $colonne = "id")
    {
        $requeteSQL =
<<<CODESQL
SELECT * FROM `$table`
WHERE `$colonne` = :$colonne

CODESQL;
        $tableauBind = [
            ":$colonne"     => $valeur,
                ];
        $statement = $this->executeSQL($requeteSQL, $tableauBind);
        
        // ON NE VEUT QU'UNE SEULE LIGNE
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
    
    function deleteRow ($table, $id)
    {
        // ON VA EFFACER LA LIGNE $id DANS LA TABLE $table
        $requeteSQL =
<<<CODESQL
DELETE FROM `$table`
WHERE `id` = :id

CODESQL;
        $tableauBind = [
                ":id"     => $id,
                ];
        $this->executeSQL($requeteSQL, $tableauBind);
            
    }
   
   function executeSQL ($requeteSQL, $tableauBind)
    {
        // CONNEXION A LA BASE DE DONNEES
        $pdo = $this->getConnexion();

        // PREPARATION DE LA FUTURE REQUETE SQL
        // SECURITE CONTRE LES INJECTIONS SQL
        $statement = $pdo->prepare($requeteSQL);
        
        // $statement->bindValue(":nom", $nom);
        foreach($tableauBind as $cle => $valeur)
        {
            // REMPLACER CHAQUE JETON PAR SA VALEUR
            $statement->bindValue($cle, $valeur);
        }
        
        // EXECUTER LA REQUETE SQL
        $statement->execute();
        
        // RENVOIE L'OBJET $statement 
        // QUI PERMETTRA DE PARCOURIR LES RESULTATS
        return $statement;
    }
   
}