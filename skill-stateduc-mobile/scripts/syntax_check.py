#!/usr/bin/env python3
"""
syntax_check.py — Vérificateur de balance accolades/parenthèses Dart

USAGE:
    python3 syntax_check.py fichier.dart
    python3 syntax_check.py lib/services/api_service.dart lib/providers/*.dart

TAGS: AK-FLUTTER

FONCTIONNALITÉS:
    - Compte { } ( ) en excluant les commentaires // et /* */
    - Exclut les chaînes de caractères ("..." et '...')
    - Exclut les string interpolations ${...} (ne pas confondre avec accolades)
    - Signale les lignes problématiques avec contexte
    - Mode verbeux (-v) pour voir chaque accolade comptée

PROBLÈME RÉSOLU:
    Les $ { } dans les string interpolations Dart ("${variable}") sont des
    caractères littéraux, pas des délimiteurs structurels. Un faux-positif
    peut apparaître si on compte naïvement.
    Ce script distingue: ${expr} (interpolation) vs {code} (bloc).
"""

import sys
import re
from pathlib import Path


# ─────────────────────────────────────────────────────────────────────────────
# STRIPPEUR DE COMMENTAIRES ET CHAÎNES
# ─────────────────────────────────────────────────────────────────────────────

def strip_comments_and_strings(source: str) -> str:
    """
    Supprime les commentaires // et /* */ et les littéraux string
    du code source Dart. Remplace par des espaces pour conserver
    la numérotation de lignes.

    Attention: gestion simplifiée — ne couvre pas 100% des cas edge
    (raw strings r'...', etc.) mais couvre les cas courants.
    """
    result = []
    i = 0
    n = len(source)

    while i < n:
        # Commentaire single-line: //
        if source[i] == '/' and i + 1 < n and source[i + 1] == '/':
            # Remplacer jusqu'à la fin de ligne par des espaces
            while i < n and source[i] != '\n':
                result.append(' ')
                i += 1
            continue

        # Commentaire multi-ligne: /* ... */
        if source[i] == '/' and i + 1 < n and source[i + 1] == '*':
            result.append(' ')
            result.append(' ')
            i += 2
            while i < n:
                if source[i] == '*' and i + 1 < n and source[i + 1] == '/':
                    result.append(' ')
                    result.append(' ')
                    i += 2
                    break
                result.append('\n' if source[i] == '\n' else ' ')
                i += 1
            continue

        # String double-quote: "..." (avec gestion des échappements)
        if source[i] == '"':
            result.append(' ')
            i += 1
            while i < n and source[i] != '"':
                if source[i] == '\\':
                    result.append(' ')
                    i += 1
                    if i < n:
                        result.append(' ')
                        i += 1
                    continue
                # String interpolation ${...} : on veut conserver les accolades?
                # NON — on supprime tout le contenu de la string
                result.append('\n' if source[i] == '\n' else ' ')
                i += 1
            if i < n:
                result.append(' ')
                i += 1
            continue

        # String single-quote: '...'
        if source[i] == "'":
            result.append(' ')
            i += 1
            while i < n and source[i] != "'":
                if source[i] == '\\':
                    result.append(' ')
                    i += 1
                    if i < n:
                        result.append(' ')
                        i += 1
                    continue
                result.append('\n' if source[i] == '\n' else ' ')
                i += 1
            if i < n:
                result.append(' ')
                i += 1
            continue

        result.append(source[i])
        i += 1

    return ''.join(result)


# ─────────────────────────────────────────────────────────────────────────────
# VÉRIFICATEUR PRINCIPAL
# ─────────────────────────────────────────────────────────────────────────────

def check_balance(filepath: str, verbose: bool = False) -> bool:
    """
    Vérifie la balance { } et ( ) dans un fichier Dart.
    Retourne True si tout est balancé, False sinon.
    """
    path = Path(filepath)
    if not path.exists():
        print(f"❌ Fichier introuvable: {filepath}")
        return False

    source = path.read_text(encoding='utf-8')
    cleaned = strip_comments_and_strings(source)
    lines   = cleaned.split('\n')
    orig_lines = source.split('\n')

    # Compteurs
    brace_depth = 0   # { }
    paren_depth = 0   # ( )
    errors = []

    for lineno, line in enumerate(lines, start=1):
        for col, ch in enumerate(line, start=1):
            if ch == '{':
                brace_depth += 1
                if verbose:
                    print(f"  L{lineno:4}:{col:3} {{ → depth={brace_depth}")
            elif ch == '}':
                brace_depth -= 1
                if verbose:
                    print(f"  L{lineno:4}:{col:3} }} → depth={brace_depth}")
                if brace_depth < 0:
                    errors.append(
                        f"  L{lineno}: accolade fermante '}' sans ouvrante\n"
                        f"    → {orig_lines[lineno-1].strip()}"
                    )
            elif ch == '(':
                paren_depth += 1
            elif ch == ')':
                paren_depth -= 1
                if paren_depth < 0:
                    errors.append(
                        f"  L{lineno}: parenthèse fermante ')' sans ouvrante\n"
                        f"    → {orig_lines[lineno-1].strip()}"
                    )

    # Rapport
    ok = (brace_depth == 0 and paren_depth == 0 and not errors)

    if ok:
        print(f"✅ {filepath}")
        print(f"   Accolades {{ }}: équilibrées (profondeur finale = 0)")
        print(f"   Parenthèses ( ): équilibrées (profondeur finale = 0)")
    else:
        print(f"❌ {filepath}")
        if brace_depth != 0:
            print(f"   ⚠ Accolades: déséquilibre net = {brace_depth:+d}")
            print(f"     (positif = manque des '}}', négatif = manque des '{{')")
        if paren_depth != 0:
            print(f"   ⚠ Parenthèses: déséquilibre net = {paren_depth:+d}")
        for err in errors:
            print(err)

    return ok


# ─────────────────────────────────────────────────────────────────────────────
# POINT D'ENTRÉE
# ─────────────────────────────────────────────────────────────────────────────

def main():
    args = sys.argv[1:]

    if not args or args[0] in ('-h', '--help'):
        print(__doc__)
        sys.exit(0)

    verbose = '-v' in args
    files   = [a for a in args if not a.startswith('-')]

    if not files:
        print("Usage: python3 syntax_check.py [-v] fichier.dart ...")
        sys.exit(1)

    all_ok = True
    for filepath in files:
        print(f"\n{'─' * 60}")
        ok = check_balance(filepath, verbose=verbose)
        if not ok:
            all_ok = False

    print(f"\n{'═' * 60}")
    if all_ok:
        print("✅ Tous les fichiers sont syntaxiquement équilibrés.")
        sys.exit(0)
    else:
        print("❌ Certains fichiers ont des déséquilibres.")
        sys.exit(1)


if __name__ == '__main__':
    main()
