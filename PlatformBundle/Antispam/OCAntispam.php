<?php
//-- PlatformBundle/Antispam/OCAntispam.php
/* 
 * Gestion de l'Antispam
 */

namespace OC\PlatformBundle\Antispam;

class OCAntispam
{
    private $mailer;
    private $locale;
    private $minLenght;
    
    /**
     * Constructeur
     */
    
    public function __construct(\Swift_Mailer $mailer, $locale, $minLength)
    {
        $this->mailer = $mailer;
        $this->locale = $locale;
        $this->minLenght = $minLength;
    }//-- Fin constructeur

    /**
     * Vérifie si le texte est un spam ou non
     *
     * @param string $text
     * @return bool
     */
    public function isSpam($text)
    {
        return strlen($text) < $this->minLenght;
    }

}//-- fin class
