#!/usr/bin/env bash
# push_github.sh — Régénération token GitHub App + push + mise à jour PR
#
# USAGE:
#   bash scripts/push_github.sh "fix(scope): message de commit"
#   bash scripts/push_github.sh "feat(scope): nouvelle fonctionnalité" --force
#
# OPTIONS:
#   --force   : git push -f (après rebase/squash)
#   --no-pr   : ne pas créer/mettre à jour la PR
#
# TAGS: AK-PUSH-001
#
# PROBLÈME RÉSOLU:
#   Les tokens GitHub App (ghs_NNNN_JWT) expirent après 1h.
#   Ce script régénère toujours un token frais avant chaque push.
#
# PRÉREQUIS:
#   - Outil Genspark `setup_github_environment` disponible dans l'env
#   - GitHub CLI `gh` installé (pour les opérations PR)
#   - Être sur la branche ak_secure

set -euo pipefail

# ─────────────────────────────────────────────────────────────────────────────
# CONFIGURATION — adapter selon le projet
# ─────────────────────────────────────────────────────────────────────────────
REPO_OWNER="NasserKailou"
REPO_NAME="stateduc_mobile"
BRANCH="ak_secure"
BASE_BRANCH="main"
PR_NUMBER=""  # sera détecté automatiquement

WORKDIR="/home/user/webapp"
FORCE_PUSH=false
CREATE_PR=true
COMMIT_MSG="${1:-}"

# ─────────────────────────────────────────────────────────────────────────────
# PARSE ARGUMENTS
# ─────────────────────────────────────────────────────────────────────────────
for arg in "${@:2}"; do
    case "$arg" in
        --force)  FORCE_PUSH=true  ;;
        --no-pr)  CREATE_PR=false  ;;
    esac
done

# ─────────────────────────────────────────────────────────────────────────────
# FONCTIONS UTILITAIRES
# ─────────────────────────────────────────────────────────────────────────────

log()  { echo "$(date '+%H:%M:%S') [PUSH] $*"; }
err()  { echo "$(date '+%H:%M:%S') [PUSH] ❌ ERREUR: $*" >&2; }
ok()   { echo "$(date '+%H:%M:%S') [PUSH] ✅ $*"; }
warn() { echo "$(date '+%H:%M:%S') [PUSH] ⚠️  $*"; }

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 0 — Vérifications préalables
# ─────────────────────────────────────────────────────────────────────────────

log "=== Démarrage push_github.sh ==="
cd "$WORKDIR"

# Vérifier qu'on est sur la bonne branche
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
    warn "Branche courante: $CURRENT_BRANCH (attendu: $BRANCH)"
    read -rp "Continuer quand même? [y/N] " confirm
    [ "$confirm" = "y" ] || { err "Push annulé."; exit 1; }
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 1 — Validation du message de commit
# ─────────────────────────────────────────────────────────────────────────────

if [ -z "$COMMIT_MSG" ]; then
    err "Message de commit manquant."
    echo "Usage: bash $0 \"fix(scope): message\""
    exit 1
fi

# Valider le format conventionnel (avertissement seulement)
if ! echo "$COMMIT_MSG" | grep -qE '^(fix|feat|refactor|docs|chore|test|style|perf|revert)\([^)]+\): .+'; then
    warn "Message ne respecte pas le format conventionnel: type(scope): description"
    warn "Continuer quand même..."
fi

log "Message commit: $COMMIT_MSG"

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 2 — Vérification syntaxe Dart
# ─────────────────────────────────────────────────────────────────────────────

log "Vérification syntaxe Dart..."
DART_DIR="$WORKDIR/stateduc_flutter/lib"
if [ -d "$DART_DIR" ]; then
    SYNTAX_OK=true
    for dart_file in $(git diff --name-only HEAD | grep '\.dart$' || true); do
        if [ -f "$dart_file" ]; then
            log "  Vérification: $dart_file"
            if ! python3 "$WORKDIR/skill-stateduc-mobile/scripts/syntax_check.py" "$dart_file" > /tmp/dart_check.txt 2>&1; then
                cat /tmp/dart_check.txt
                err "Erreur syntaxe Dart dans $dart_file"
                SYNTAX_OK=false
            fi
        fi
    done
    if [ "$SYNTAX_OK" = false ]; then
        err "Corriger les erreurs de syntaxe Dart avant de pousser."
        exit 1
    fi
    ok "Syntaxe Dart validée"
else
    warn "Répertoire lib/ introuvable — vérification Dart ignorée"
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 3 — Commit des modifications en attente
# ─────────────────────────────────────────────────────────────────────────────

# Vérifier s'il y a des fichiers modifiés non committés
MODIFIED=$(git diff --name-only && git diff --name-only --staged)
if [ -n "$MODIFIED" ]; then
    log "Fichiers modifiés détectés — staging et commit..."
    git add -A
    git commit -m "$COMMIT_MSG"
    ok "Commit créé: $(git log --oneline -1)"
else
    log "Pas de modification à committer — push du HEAD existant"
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 4 — Synchronisation avec origin
# ─────────────────────────────────────────────────────────────────────────────

log "Récupération des commits distants..."
git fetch origin 2>&1 | grep -v "^$" || true

# Vérifier si origin/ak_secure est en avance
BEHIND=$(git rev-list --count "HEAD..origin/$BRANCH" 2>/dev/null || echo 0)
if [ "$BEHIND" -gt 0 ]; then
    log "$BEHIND commits distants non intégrés — rebase en cours..."
    if ! git rebase "origin/$BRANCH"; then
        err "Conflits de rebase détectés. Résoudre manuellement:"
        echo "  git status"
        echo "  # Éditer les fichiers en conflit"
        echo "  git add <fichiers>"
        echo "  git rebase --continue"
        echo "  bash $0 '$COMMIT_MSG' ${FORCE_PUSH:+--force}"
        exit 1
    fi
    ok "Rebase terminé"
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 5 — Régénération token GitHub App
# ─────────────────────────────────────────────────────────────────────────────

log "Régénération token GitHub App..."

# Supprimer les credentials en cache (tokens périmés)
rm -f ~/.git-credentials

# Régénérer via l'outil Genspark (setup_github_environment)
# NOTE: dans un contexte non-Genspark, remplacer par :
#   git config credential.helper store
#   echo "https://x-access-token:VOTRE_TOKEN@github.com" > ~/.git-credentials
if command -v setup_github_environment &>/dev/null; then
    setup_github_environment 2>/dev/null || true
else
    warn "setup_github_environment non disponible — utiliser le token manuellement"
    echo ""
    echo "Entrer le token GitHub App (format: ghs_...):"
    read -rs TOKEN_MANUAL
    echo ""
    echo "https://x-access-token:${TOKEN_MANUAL}@github.com" > ~/.git-credentials
    chmod 600 ~/.git-credentials
fi

# Extraire le token du fichier credentials
TOKEN=$(grep 'github.com' ~/.git-credentials 2>/dev/null | \
    sed 's|.*x-access-token:\([^@]*\)@.*|\1|' | head -1)

if [ -z "$TOKEN" ]; then
    err "Token GitHub App non trouvé dans ~/.git-credentials"
    err "Vérifier que setup_github_environment a réussi"
    exit 1
fi

ok "Token GitHub App extrait (${#TOKEN} caractères, début: ${TOKEN:0:15}...)"

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 6 — Push
# ─────────────────────────────────────────────────────────────────────────────

PUSH_URL="https://x-access-token:${TOKEN}@github.com/${REPO_OWNER}/${REPO_NAME}.git"
PUSH_FLAGS=""
if [ "$FORCE_PUSH" = true ]; then
    PUSH_FLAGS="-f"
    warn "Force push activé (--force)"
fi

log "Push vers origin/$BRANCH..."
# -c credential.helper= : désactiver les helpers credential (évite les hangs)
if git -c credential.helper= push $PUSH_FLAGS "$PUSH_URL" "$BRANCH" 2>&1; then
    ok "Push réussi → origin/$BRANCH"
else
    err "Push échoué"
    echo ""
    echo "Diagnostics:"
    echo "  git log --oneline origin/$BRANCH..HEAD  # commits non poussés"
    echo "  git remote -v                            # vérifier URL remote"
    exit 1
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 7 — Créer ou mettre à jour la Pull Request
# ─────────────────────────────────────────────────────────────────────────────

if [ "$CREATE_PR" = false ]; then
    log "PR ignorée (--no-pr)"
    exit 0
fi

# Vérifier que gh est installé
if ! command -v gh &>/dev/null; then
    warn "GitHub CLI (gh) non disponible — PR non créée/mise à jour"
    echo "Installer avec: apt install gh"
    exit 0
fi

# Configurer gh avec le token
echo "$TOKEN" | gh auth login --with-token 2>/dev/null || true

log "Vérification PR existante..."
EXISTING_PR=$(gh pr list \
    --repo "${REPO_OWNER}/${REPO_NAME}" \
    --head "$BRANCH" \
    --base "$BASE_BRANCH" \
    --json number \
    --jq '.[0].number' 2>/dev/null || echo "")

# Construire la description de la PR
LAST_COMMITS=$(git log --oneline "origin/$BASE_BRANCH..$BRANCH" 2>/dev/null | head -10 | \
    sed 's/^/- /' || echo "- (commits)")
PR_BODY="## Changements — branche \`$BRANCH\`

$LAST_COMMITS

---
*Généré automatiquement par push_github.sh*"

if [ -n "$EXISTING_PR" ]; then
    log "Mise à jour PR #${EXISTING_PR}..."
    gh pr edit "$EXISTING_PR" \
        --repo "${REPO_OWNER}/${REPO_NAME}" \
        --body "$PR_BODY" 2>/dev/null && \
        ok "PR #${EXISTING_PR} mise à jour: https://github.com/${REPO_OWNER}/${REPO_NAME}/pull/${EXISTING_PR}"
else
    log "Création d'une nouvelle PR..."
    PR_URL=$(gh pr create \
        --repo  "${REPO_OWNER}/${REPO_NAME}" \
        --head  "$BRANCH" \
        --base  "$BASE_BRANCH" \
        --title "$COMMIT_MSG" \
        --body  "$PR_BODY" 2>/dev/null || echo "")
    if [ -n "$PR_URL" ]; then
        ok "PR créée: $PR_URL"
    else
        warn "Création PR échouée — vérifier les droits GitHub"
    fi
fi

log "=== push_github.sh terminé ==="
