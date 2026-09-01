#!/usr/bin/env python3
"""
fix_html_values.py - Session 26
Corrige les attributs VALUE dans tous les fichiers ws_mob_*.html de questionnaire/fr/

Problème: VALUE='CODE_TYPE_ACCES_0_6' au lieu de VALUE='6'
          VALUE='ELECTRICITE_0_1'      au lieu de VALUE='1'
          VALUE='CODE_TYPE_SECTION_0_57' (checkbox) au lieu de VALUE='57'

Règle: Pour tout INPUT radio ou checkbox dont VALUE='[A-Z0-9_]+_[0-9]+'
       → extraire le dernier segment numérique après le dernier underscore
       → remplacer par VALUE='<N>'

Fichiers concernés: StatEduc_burundi/questionnaire/fr/ws_mob_*.html
"""

import re
import os
import glob
import sys

def fix_file(filepath):
    """Corrige les VALUE dans un fichier HTML et retourne (nb_fixes, modifié)"""
    with open(filepath, 'r', encoding='latin-1') as f:
        content = f.read()
    
    original = content
    count = 0

    # Pattern: VALUE='QUELQUE_CHOSE_0_N' ou VALUE='QUELQUE_CHOSE_N'
    # où N est un ou plusieurs chiffres
    # ID contient toujours le nom de champ + _N → on extrait le N de l'ID correspondant
    # 
    # Regex: VALUE='([A-Z][A-Z0-9_]*_([0-9]+))'
    # Le dernier groupe de chiffres après un underscore est la valeur numérique
    #
    # Ex: VALUE='CODE_TYPE_MILIEU_0_1'  → captures: ('CODE_TYPE_MILIEU_0_1', '1')
    # Ex: VALUE='ELECTRICITE_0_1'       → captures: ('ELECTRICITE_0_1', '1')
    # Ex: VALUE='CODE_TYPE_SECTION_0_57' → captures: ('CODE_TYPE_SECTION_0_57', '57')
    #
    # On s'assure de ne pas toucher:
    #   VALUE=""  (text inputs, déjà corrects)
    #   VALUE='0' ou VALUE='1' etc. (déjà corrects, chiffre seul)
    
    def replacer(m):
        full_value = m.group(1)
        # Extraire le dernier segment numérique
        parts = full_value.split('_')
        # Le dernier segment doit être numérique
        if parts and parts[-1].isdigit():
            return f"VALUE='{parts[-1]}'"
        # Sinon on ne touche pas
        return m.group(0)
    
    # Appliquer le remplacement: VALUE='[A-Z][A-Z0-9_]*_[0-9]+'
    # On exclut les VALUE qui sont déjà juste des chiffres ou vides
    pattern = re.compile(r"VALUE='([A-Z][A-Z0-9_]*_([0-9]+))'")
    
    new_content, count = pattern.subn(replacer, content)
    
    if count > 0:
        with open(filepath, 'w', encoding='latin-1') as f:
            f.write(new_content)
        return count, True
    
    return 0, False


def main():
    # Trouver tous les ws_mob_*.html
    base_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 
                            'StatEduc_burundi', 'questionnaire', 'fr')
    
    pattern = os.path.join(base_dir, 'ws_mob_*.html')
    files = sorted(glob.glob(pattern))
    
    if not files:
        print(f"ERREUR: Aucun fichier trouvé dans {base_dir}")
        sys.exit(1)
    
    print(f"=== Correction des VALUE dans {len(files)} fichiers ws_mob_*.html ===")
    print()
    
    total_fixes = 0
    modified_files = 0
    
    for filepath in files:
        nb_fixes, modified = fix_file(filepath)
        filename = os.path.basename(filepath)
        if modified:
            print(f"  ✓ CORRIGÉ  {filename}: {nb_fixes} occurrences")
            total_fixes += nb_fixes
            modified_files += 1
        else:
            print(f"  ✓ OK       {filename}: déjà correct")
    
    print()
    print(f"=== RÉSUMÉ ===")
    print(f"  Fichiers modifiés : {modified_files}/{len(files)}")
    print(f"  Total corrections : {total_fixes}")
    print()
    
    # Vérification post-correction
    print("=== VÉRIFICATION POST-CORRECTION ===")
    remaining_broken = 0
    for filepath in files:
        with open(filepath, 'r', encoding='latin-1') as f:
            content = f.read()
        broken = re.findall(r"VALUE='[A-Z][A-Z0-9_]*_[0-9]+'", content)
        if broken:
            filename = os.path.basename(filepath)
            print(f"  ⚠ ENCORE CASSÉ: {filename}: {len(broken)} occurrences")
            for b in broken[:5]:
                print(f"    → {b}")
            remaining_broken += len(broken)
    
    if remaining_broken == 0:
        print("  ✓ TOUS LES FICHIERS SONT CORRECTS!")
    else:
        print(f"  ⚠ {remaining_broken} occurrences encore cassées!")
    
    return 0 if remaining_broken == 0 else 1


if __name__ == '__main__':
    sys.exit(main())
