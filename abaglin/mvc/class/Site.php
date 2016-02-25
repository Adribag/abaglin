<?php
// Class            = Site
// Auteur           = Adrien
// Date de creation = 2015-12-07 12:50;22

class Site
{
    function __contruct()
    {
        
    }
    
    
    
    function afficherPage ()
    {
        // LA METHODE afficherPage DOIT PRODUIRE LE CODE HTML DE LA PAGE
        $maPage = new Page;
        $header = new Header;
        $footer = new Footer;
        
        list($nomFichier)= getPage($dossierRacine);
        
        $fichierContent = "mvc/view/page-$nomFichier.php";
        $fichierGarage = "mvc/view/garages/page-$nomFichier.php";
        
        if (is_file($fichierContent))
        {
            //include("mvc/view/header.php");
            
            $header->afficheHeader();
        
            $maPage->creerContenu($fichierContent);
        
            $footer->afficheFooter();
        }
        elseif(is_file($fichierGarage))
        {
            $header->afficheHeader();
        
            $maPage->creerContenu($fichierGarage);
        
            $footer->afficheFooter();
        }
        else
        {
                // SIGNALER LE CODE 404
                header("HTTP/1.1 404 Not Found");
                
                $header->afficheHeader();
            
                $maPage->creerContenu("mvc/view/content-404.php");
            
                $footer->afficheFooter();
                
        }
    }
    
}