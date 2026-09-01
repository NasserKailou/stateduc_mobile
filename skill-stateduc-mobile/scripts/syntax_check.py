#!/usr/bin/env python3
"""
syntax_check.py — Vérificateur de balance accolades/parenthèses Dart

USAGE:
    python3 scripts/syntax_check.py <fichier.dart>
    python3 scripts/syntax_check.py lib/services/api_service.dart
    python3 scripts/syntax_check.py lib/  # analyse tous les .dart du répertoire

TAGS: AK-DART-SYNTAX

PROBLÈME RÉSOLU:
    Un net +1 d'accolades dans api_service.dart semblait indiquer une erreur
    syntaxique. L'investigation a montré que c'était un faux-positif causé par
    des caractères { dans des commentaires // (lignes 450-451, 555-557).
    Ce script exclut les commentaires avant de compter, donnant un résultat fiable.

LOGIQUE:
    1. Supprimer les commentaires de ligne (// ...)
    2. Supprimer les commentaires de bloc (/* ... */)
    3. Supprimer les string literals ("..." et '...')
       SAUF les interpolations ${...} dans les strings — conserver le texte brut
    4. Compter { vs } et ( vs )
    5. Signaler les déséquilibres avec numéro de ligne approximatif
"""

import sys
import re
import os
from pathlib import Path


def strip_comments_and_strings(source: str) -> tuple[str, dict[int, str]]:
    """
    Retourne le code sans commentaires ni string literals.
    Retourne aussi un dict {position: commentaire_original} pour debug.
    """
    result    = []
    i         = 0
    n         = len(source)
    removed   = {}  # position → contenu supprimé (pour debug)

    while i < n:
        # Commentaire de ligne: // ... \n
        if source[i:i+2] == '//':
            j = source.find('\n', i)
            end = j if j != -1 else n
            removed[i] = source[i:end]
            result.append('\n')  # garder le saut de ligne pour les numéros de ligne
            i = end
            continue

        # Commentaire de bloc: /* ... */
        if source[i:i+2] == '/*':
            j = source.find('*/', i + 2)
            end = j + 2 if j != -1 else n
            removed[i] = source[i:end]
            # Garder les sauts de ligne pour compter les lignes correctement
            newlines = source[i:end].count('\n')
            result.append('\n' * newlines)
            i = end
            continue

        # String triple quote: '''...''' ou """..."""
        for triple in ('"""', "'''"):
            if source[i:i+3] == triple:
                j = source.find(triple, i + 3)
                end = j + 3 if j != -1 else n
                newlines = source[i:end].count('\n')
                result.append('\n' * newlines)
                i = end
                break
        else:
            # String simple quote: "..." ou '...'
            # Gérer les interpolations ${...} : CONSERVER les accolades internes
            if source[i] in ('"', "'"):
                quote_char = source[i]
                result.append(quote_char)
                i += 1
                while i < n:
                    if source[i] == '\\':
                        i += 2  # séquence d'échappement
                        continue
                    if source[i] == quote_char:
                        result.append(quote_char)
                        i += 1
                        break
                    if source[i:i+2] == '${':
                        # Interpolation: conserver les accolades
                        result.append('${')
                        i += 2
                        depth = 1
                        while i < n and depth > 0:
                            if source[i] == '{':
                                depth += 1
                                result.append(source[i])
                            elif source[i] == '}':
                                depth -= 1
                                result.append(source[i])
                            else:
                                result.append(source[i])
                            i += 1
                        continue
                    # Caractère normal dans la string → supprimer (sauf \n)
                    if source[i] == '\n':
                        result.append('\n')
                    i += 1
                continue

            # Caractère normal
            result.append(source[i])
            i += 1

    return ''.join(result), removed


def check_balance(source: str) -> dict:
    """Vérifie la balance { } et ( ) dans le code source épuré."""
    brace_count  = 0  # { = +1, } = -1
    paren_count  = 0  # ( = +1, ) = -1
    brace_issues = []
    paren_issues = []

    lines = source.split('\n')
    for lineno, line in enumerate(lines, 1):
        for char in line:
            if char == '{':
                brace_count += 1
            elif char == '}':
                brace_count -= 1
                if brace_count < 0:
                    brace_issues.append(f'L{lineno}: }} inattendue (net={brace_count})')
            elif char == '(':
                paren_count += 1
            elif char == ')':
                paren_count -= 1
                if paren_count < 0:
                    paren_issues.append(f'L{lineno}: ) inattendue (net={paren_count})')

    return {
        'brace_net':    brace_count,
        'paren_net':    paren_count,
        'brace_issues': brace_issues,
        'paren_issues': paren_issues,
    }


def analyse_file(filepath: str) -> dict:
    """Analyse un fichier .dart et retourne le rapport."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            source = f.read()
    except FileNotFoundError:
        return {'error': f'Fichier non trouvé: {filepath}'}
    except Exception as e:
        return {'error': str(e)}

    # Statistiques brutes (AVANT suppression commentaires)
    raw_braces = source.count('{') - source.count('}')
    raw_parens = source.count('(') - source.count(')')

    # Code épuré
    stripped, removed_sections = strip_comments_and_strings(source)

    # Balance sur code épuré
    balance = check_balance(stripped)

    return {
        'file':              filepath,
        'lines':             source.count('\n') + 1,
        'raw_brace_net':     raw_braces,
        'raw_paren_net':     raw_parens,
        'stripped_brace_net': balance['brace_net'],
        'stripped_paren_net': balance['paren_net'],
        'brace_ok':          balance['brace_net'] == 0,
        'paren_ok':          balance['paren_net'] == 0,
        'brace_issues':      balance['brace_issues'][:5],  # max 5
        'paren_issues':      balance['paren_issues'][:5],
        'comments_removed':  len(removed_sections),
    }


def print_report(report: dict) -> bool:
    """Affiche le rapport et retourne True si OK, False si erreur."""
    if 'error' in report:
        print(f'  ❌ ERREUR: {report["error"]}')
        return False

    ok = report['brace_ok'] and report['paren_ok']
    status = '✅ OK' if ok else '❌ ERREUR'

    print(f'\n  Fichier : {report["file"]}')
    print(f'  Lignes  : {report["lines"]}')
    print(f'  Commentaires supprimés : {report["comments_removed"]}')
    print()
    print(f'  ACCOLADES {{ }}')
    print(f'    Brut (avec commentaires) : net = {report["raw_brace_net"]:+d}')
    print(f'    Épuré (sans commentaires): net = {report["stripped_brace_net"]:+d}', end='')
    if report['brace_ok']:
        print(' ✅')
    else:
        print(f' ❌  → DÉSÉQUILIBRE: {report["stripped_brace_net"]:+d}')
        for issue in report['brace_issues']:
            print(f'      {issue}')

    print(f'  PARENTHÈSES ( )')
    print(f'    Brut (avec commentaires) : net = {report["raw_paren_net"]:+d}')
    print(f'    Épuré (sans commentaires): net = {report["stripped_paren_net"]:+d}', end='')
    if report['paren_ok']:
        print(' ✅')
    else:
        print(f' ❌  → DÉSÉQUILIBRE: {report["stripped_paren_net"]:+d}')
        for issue in report['paren_issues']:
            print(f'      {issue}')

    print(f'\n  Résultat : {status}')

    if report['raw_brace_net'] != report['stripped_brace_net']:
        diff = report['raw_brace_net'] - report['stripped_brace_net']
        print(f'\n  ℹ️  Note: {diff:+d} accolades dans commentaires/strings (faux-positifs exclus)')

    return ok


def main():
    if len(sys.argv) < 2:
        print('Usage: python3 syntax_check.py <fichier.dart | répertoire/>')
        print('       python3 syntax_check.py lib/services/api_service.dart')
        print('       python3 syntax_check.py lib/')
        sys.exit(1)

    target = sys.argv[1]
    files  = []

    if os.path.isdir(target):
        files = list(Path(target).rglob('*.dart'))
        print(f'Analyse de {len(files)} fichier(s) .dart dans {target}')
    elif os.path.isfile(target):
        files = [Path(target)]
    else:
        print(f'Erreur: {target} n\'est ni un fichier ni un répertoire')
        sys.exit(1)

    all_ok     = True
    ok_count   = 0
    fail_count = 0

    for f in sorted(files):
        report = analyse_file(str(f))
        ok     = print_report(report)
        if ok:
            ok_count += 1
        else:
            fail_count += 1
            all_ok = False

    if len(files) > 1:
        print(f'\n{"="*50}')
        print(f'RÉSUMÉ: {ok_count} OK, {fail_count} ERREUR(S) sur {len(files)} fichiers')

    sys.exit(0 if all_ok else 1)


if __name__ == '__main__':
    main()
