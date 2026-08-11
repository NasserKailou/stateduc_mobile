<?php
/**
 * FIE — PublicController
 * Site public accessible sans authentification.
 */
declare(strict_types=1);
namespace App\Controllers;

class PublicController
{
    public function home(): void
    {
        $page_title  = 'FIE — Fichier Informatisé des Élèves du Burundi';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/home.php';
    }

    public function aide(): void
    {
        $page_title  = 'Aide — FIE';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/aide.php';
    }

    public function contact(): void
    {
        $page_title  = 'Contact — FIE';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/contact.php';
    }

    public function confidentialite(): void
    {
        $page_title  = 'Politique de confidentialité — FIE';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/confidentialite.php';
    }

    public function mentions(): void
    {
        $page_title  = 'Mentions légales — FIE';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/mentions.php';
    }
}
