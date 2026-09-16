#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script : add_auteur_comments.py
Ajoute le bloc PHPDoc @auteur a tous les fichiers PHP du projet mobile StatEduc Burundi.
Auteur : kailounasser@gmail.com - Abdoul Nasser Kailou
"""

import os
import sys

BASE = '/home/user/webapp/StatEduc_burundi'

# ──────────────────────────────────────────────────────────────────────────────
# Blocs a injecter pour chaque fichier
# Chaque entree : (chemin_relatif_au_BASE, description, sessions, role_court)
# ──────────────────────────────────────────────────────────────────────────────
FILES = [
    {
        'path': 'user_ident.php',
        'desc': (
            "Web Service REST - Authentification utilisateur mobile.\n"
            " * Route : POST /user/login\n"
            " * Verifie identifiant + mot de passe dans la base Access (dico_DB.mdb).\n"
            " * Retourne un token de session JSON si les credentials sont valides."
        ),
        'sessions': '1-14',
    },
    {
        'path': 'user_camp.php',
        'desc': (
            "Web Service REST - Gestion des campagnes et etablissements pour l'app mobile.\n"
            " * Routes : GET /new_camp, /theme_camp, /list_etab, etc.\n"
            " * Renvoie la liste des campagnes disponibles, les themes et les ecoles\n"
            " * assignes a l'agent collecteur connecte."
        ),
        'sessions': '1-19',
    },
    {
        'path': 'data_camp.php',
        'desc': (
            "Web Service REST - Generation et envoi des frames (templates HTML) pour formulaires mobiles.\n"
            " * Route : GET /theme_frame/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}\n"
            " * Utilise frame_mobile.class.php pour produire les fichiers ws_mob_*.frame.\n"
            " * MODIFIE session 23 : integration de _mobile_libelle_clean() via frame_mobile\n"
            " *   pour corriger Bug A (entites HTML brutes) et Bug B (mojibake ISO-8859-1)."
        ),
        'sessions': '1-23',
    },
    {
        'path': 'data_save.php',
        'desc': (
            "Web Service REST - Persistance des donnees collectees par l'app mobile.\n"
            " * Route : POST /data_save\n"
            " * Enregistre les reponses du formulaire dans la base de donnees Access.\n"
            " * Gere les validations, les doublons et les erreurs de contrainte."
        ),
        'sessions': '4-17',
    },
    {
        'path': 'data_controle.php',
        'desc': (
            "Web Service REST - Controle de coherence des donnees (API REST pour app mobile).\n"
            " * Route : GET /theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}\n"
            " * Execute les regles de coherence du theme contre les donnees deja sauvegardees.\n"
            " * Retourne la liste des violations detectees avec messages explicatifs."
        ),
        'sessions': '10-21',
    },
    {
        'path': 'data_reload.php',
        'desc': (
            "Web Service REST - Rechargement et pre-remplissage des donnees pour l'app mobile.\n"
            " * Route : GET /data_reload/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}\n"
            " * Renvoie les valeurs deja saisies afin de pre-remplir le formulaire a la reprise.\n"
            " * Corrige l'encodage ISO-8859-1 vers UTF-8 avant serialisation JSON."
        ),
        'sessions': '4-19',
    },
    {
        'path': 'data_rules.php',
        'desc': (
            "Web Service REST - Exposition des regles de coherence pour evaluation offline.\n"
            " * Route : GET /theme_rules/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}\n"
            " * Retourne toutes les regles de coherence d'un theme avec les SQLs interpoles.\n"
            " * Permet a l'app mobile de stocker les regles localement (mode hors-ligne)."
        ),
        'sessions': '9-21',
    },
    {
        'path': 'data_save_sms.php',
        'desc': (
            "Web Service REST - Sauvegarde des donnees collectees via SMS.\n"
            " * Route : POST /data_save_sms\n"
            " * Traite et persiste les donnees envoyees par SMS depuis les zones sans Internet.\n"
            " * Decodage et validation du message SMS avant insertion en base."
        ),
        'sessions': '1-17',
    },
    {
        'path': 'params_ws.php',
        'desc': (
            "Fichier de configuration des constantes des Web Services mobiles.\n"
            " * Definit les cles JSON communes (se_status, se_message, se_data),\n"
            " * les codes de statut HTTP (200 = OK, 500 = KO) et les parametres globaux\n"
            " * utilises par tous les WS REST de l'application mobile StatEduc Burundi."
        ),
        'sessions': '1-17',
    },
    {
        'path': 'common_ws.php',
        'desc': (
            "Fichier d'amorçage et middleware commun a tous les Web Services mobiles.\n"
            " * Charge config_app.php, params.php, params_sys.php, params_ws.php.\n"
            " * Initialise les connexions base de donnees et les chemins globaux.\n"
            " * Point d'entree unique pour toute la couche WS REST de l'app mobile."
        ),
        'sessions': '1-19',
    },
]

# Fichier HttpAuth dans un sous-repertoire different
HTTPAUTH = {
    'path': 'server-side/include/web_services/HttpAuth.php',
    'desc': (
        "Middleware d'authentification HTTP Basic pour les Web Services mobiles Slim v2.\n"
        " * Etend \\Slim\\Middleware et intercepte chaque requete avant le routeur.\n"
        " * Verifie les entetes Authorization (Basic base64) et bloque les acces\n"
        " * non autorises avec une reponse 401 WWW-Authenticate."
    ),
    'sessions': '1-19',
}

# frame_mobile.class.php
FRAME_MOBILE = {
    'path': 'server-side/classes/affichage/frame_mobile.class.php',
    'desc': (
        "Classe de generation des frames HTML pour les formulaires mobiles StatEduc.\n"
        " * Produit les fichiers ws_mob_*.frame utilises par le WebView Flutter.\n"
        " * MODIFIE session 23 : ajout de _mobile_libelle_clean() pour corriger\n"
        " *   Bug A (entites HTML brutes &lt;b&gt;) et Bug B (mojibake ISO-8859-1).\n"
        " * Les libelles de zones passent desormais par mb_convert_encoding + strip_tags\n"
        " *   avant ecriture dans le fichier frame (UTF-8 propre, sans balises)."
    ),
    'sessions': '1-23',
}


def make_block(filename, desc, sessions):
    """Construit le bloc PHPDoc @auteur a inserer apres <?php."""
    block = (
        "\n/**\n"
        " * {filename}\n"
        " *\n"
        " * {desc}\n"
        " *\n"
        " * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou\n"
        " * @projet  StatEduc Burundi -- Application mobile de collecte scolaire\n"
        " * @sessions {sessions}\n"
        " * @modifie Modifie par kailounasser@gmail.com Abdoul Nasser Kailou\n"
        " *          Toutes les modifications et nouveautes sont documentees\n"
        " *          directement dans le code avec des commentaires en francais.\n"
        " */\n"
    )
    return block.format(
        filename=filename,
        desc=desc,
        sessions=sessions,
    )


def inject_block(filepath, block):
    """
    Lit le fichier, localise la ligne '<?php' et insere le bloc juste apres.
    Preserve les fins de ligne originales (CRLF ou LF).
    """
    with open(filepath, 'rb') as f:
        raw = f.read()

    # Detecter BOM UTF-8
    bom = b''
    if raw.startswith(b'\xef\xbb\xbf'):
        bom = b'\xef\xbb\xbf'
        raw = raw[3:]

    # Decoder en latin-1 (preserve tous les octets tels quels)
    text = raw.decode('latin-1')

    # Trouver la premiere ligne <?php
    if '<?php' not in text:
        print(f"  SKIP  {filepath} -- pas de balise <?php trouvee")
        return False

    idx = text.index('<?php')
    end_of_first_line = text.find('\n', idx)
    if end_of_first_line == -1:
        end_of_first_line = len(text)
    else:
        end_of_first_line += 1  # inclure le \n

    before = text[:end_of_first_line]
    after  = text[end_of_first_line:]

    # Verifier si un bloc @auteur existe deja
    if '@auteur' in text or 'kailounasser' in text:
        print(f"  SKIP  {filepath} -- bloc @auteur deja present")
        return False

    new_text = before + block + after

    # Re-encoder en latin-1 (les blocs injectes sont ASCII-safe)
    with open(filepath, 'wb') as f:
        f.write(bom)
        f.write(new_text.encode('latin-1', errors='replace'))

    return True


def main():
    modified = []
    errors   = []

    all_entries = []
    for entry in FILES:
        all_entries.append((os.path.join(BASE, entry['path']), entry))

    # HttpAuth
    httpauth_path = os.path.join(BASE, HTTPAUTH['path'])
    all_entries.append((os.path.normpath(httpauth_path), HTTPAUTH))

    # frame_mobile
    fm_path = os.path.join(BASE, FRAME_MOBILE['path'])
    all_entries.append((os.path.normpath(fm_path), FRAME_MOBILE))

    for filepath, entry in all_entries:
        filename = os.path.basename(filepath)
        print(f"\n--- Traitement de : {filename}")

        if not os.path.isfile(filepath):
            print(f"  ERREUR : fichier introuvable -- {filepath}")
            errors.append(filepath)
            continue

        block = make_block(filename, entry['desc'], entry['sessions'])

        try:
            ok = inject_block(filepath, block)
            if ok:
                print(f"  OK    : bloc @auteur ajoute")
                modified.append(filepath)
            else:
                print(f"  INFO  : deja presente ou ignoree")
        except Exception as e:
            print(f"  ERREUR : {e}")
            errors.append(filepath)

    print("\n" + "="*60)
    print(f"MODIFIES  : {len(modified)} fichiers")
    print(f"ERREURS   : {len(errors)} fichiers")
    if modified:
        print("\nFichiers modifies :")
        for p in modified:
            print(f"  {p}")
    if errors:
        print("\nFichiers en erreur :")
        for p in errors:
            print(f"  {p}")
    return 0 if not errors else 1


if __name__ == '__main__':
    sys.exit(main())
