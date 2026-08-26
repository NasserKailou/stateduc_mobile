<?php
/**
 * app_fie/index.php — Point d'entrée de secours (sans mod_rewrite)
 * ─────────────────────────────────────────────────────────────────
 * PROBLÈME RÉSOLU : Apache renvoie 403 Forbidden sur /app_fie/
 *   → Cause : AllowOverride None dans httpd.conf → .htaccess ignoré
 *   → Apache ne trouve pas de fichier index → 403 (car Options -Indexes)
 *
 * SOLUTION : Ce fichier index.php est servi directement par Apache
 * (DirectoryIndex index.php). Il re-route l'URI vers public/index.php
 * via include(), sans aucune dépendance à mod_rewrite.
 *
 * FONCTIONNEMENT :
 *   http://localhost:8085/app_fie/          → ce fichier → public/index.php (route /)
 *   http://localhost:8085/app_fie/connexion → .htaccess si mod_rewrite actif
 *                                           → sinon, utiliser /app_fie/?r=connexion
 *
 * ROUTES PROPRES SANS MOD_REWRITE :
 *   Le Router de public/index.php lit REQUEST_URI.
 *   Pour les URL propres (/connexion, /inscription…), mod_rewrite reste
 *   nécessaire. Ce fichier résout uniquement le 403 sur la racine /app_fie/.
 *
 * POUR ACTIVER MOD_REWRITE (routes propres complètes) :
 *   Voir : app_fie/docs/XAMPP_SETUP.md
 * ─────────────────────────────────────────────────────────────────
 */

// Inclure directement le front controller.
// BASE_PATH sera défini par public/index.php (dirname(__DIR__) = app_fie/).
// SCRIPT_NAME sera toujours /app_fie/public/index.php côté router
// car on fait un include interne — transparent pour PHP.

// Correction : simuler le contexte de public/index.php
// pour que BASE_URL et la normalisation URI fonctionnent correctement.
$_SERVER['SCRIPT_NAME']     = str_replace('/index.php', '/public/index.php', $_SERVER['SCRIPT_NAME'] ?? '/app_fie/index.php');
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
$_SERVER['PHP_SELF']        = str_replace('/index.php', '/public/index.php', $_SERVER['PHP_SELF'] ?? '/app_fie/index.php');

// Charger le front controller
require __DIR__ . '/public/index.php';
