<?php
// Class            = Page
// Auteur           = Adrien
// Date de creation = 2015-12-07 11:15;59

class Page
{
    function __contruct()
    {
        
    }
    function creerContenu($laPage)
    {
      include($laPage);
    }
    
}
