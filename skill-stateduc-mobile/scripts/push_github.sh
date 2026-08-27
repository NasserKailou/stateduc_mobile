#!/usr/bin/env bash
# push_github.sh — Régénération token GitHub App + push + PR update
#
# USAGE:
#   bash push_github.sh "fix(scope): message de commit"
#   bash push_github.sh "feat(ak-xxx): nouvelle fonctionnalité" --force
#
# TAGS: AK-PUSH
#
# PRÉREQUIS:
#   - setup_github_environment doit avoir été exécuté au moins une fois
#   - git configuré avec remote 'origin'
#   - gh CLI installé (optionnel — pour la création/maj de PR)
#
# VARIABLES À ADAPTER:
REPO_OWNER="NasserKailou"
REPO_NAME="stateduc_mobile"
BRANCH_DEV="ak_secure"
BRANCH_BASE="main"
PR_NUMBER="2"     # Numéro de la PR existante (0 pour créer une nouvelle)

set -euo pipefail

# ─────────────────────────────────────────────────────────────────────────────
# COULEURS
# ─────────────────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[OK]${NC} $1"; }
warn()    { echo -e "${YELLOW}[WARN]${NC} $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1" >&2; }

# ─────────────────────────────────────────────────────────────────────────────
# ARGUMENTS
# ─────────────────────────────────────────────────────────────────────────────
COMMIT_MSG="${1:-}"
FORCE_PUSH=false
if [[ "${2:-}" == "--force" ]] || [[ "${1:-}" == "--force" ]]; then
    FORCE_PUSH=true
fi

if [[ -z "$COMMIT_MSG" ]] || [[ "$COMMIT_MSG" == "--force" ]]; then
    error "Message de commit requis"
    echo "Usage: bash push_github.sh \"fix(scope): message\" [--force]"
    exit 1
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 1 — Vérifier l'état git
# ─────────────────────────────────────────────────────────────────────────────
info "=== ÉTAPE 1: État git ==="

CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
info "Branche actuelle: $CURRENT_BRANCH"

if [[ "$CURRENT_BRANCH" != "$BRANCH_DEV" ]]; then
    warn "Pas sur $BRANCH_DEV — passage automatique"
    git checkout "$BRANCH_DEV" || {
        error "Impossible de passer sur $BRANCH_DEV"
        exit 1
    }
fi

# Vérifier si des fichiers sont à committer
GIT_STATUS=$(git status --porcelain)
if [[ -n "$GIT_STATUS" ]]; then
    info "Fichiers modifiés à committer:"
    git status --short
    git add -A
    git commit -m "$COMMIT_MSG"
    success "Commit créé: $COMMIT_MSG"
else
    info "Rien à committer — tentative de push du dernier commit"
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 2 — Régénérer le token GitHub App
# ─────────────────────────────────────────────────────────────────────────────
info "=== ÉTAPE 2: Régénération token GitHub ==="

# Supprimer les credentials périmés
if [[ -f ~/.git-credentials ]]; then
    rm -f ~/.git-credentials
    info "Credentials périmés supprimés"
fi

# Régénérer via setup_github_environment
# Note: Dans le contexte Claude/Genspark, cette commande est un outil spécial.
# Dans un vrai bash, on utiliserait l'API GitHub App directement.
info "Exécuter setup_github_environment pour régénérer le token..."
info "(Dans Claude: utiliser l'outil setup_github_environment)"

# Attendre que le token soit disponible
sleep 2

# Extraire le token du fichier credentials
if [[ ! -f ~/.git-credentials ]]; then
    error "~/.git-credentials introuvable après setup_github_environment"
    error "Exécuter manuellement: setup_github_environment"
    exit 1
fi

TOKEN=$(grep 'github.com' ~/.git-credentials \
    | sed 's|.*x-access-token:\(.*\)@.*|\1|' \
    | head -1)

if [[ -z "$TOKEN" ]]; then
    error "Token introuvable dans ~/.git-credentials"
    error "Contenu actuel:"
    cat ~/.git-credentials 2>/dev/null || echo "(vide)"
    exit 1
fi

TOKEN_PREVIEW="${TOKEN:0:20}..."
success "Token récupéré: $TOKEN_PREVIEW"

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 3 — Synchroniser avec origin
# ─────────────────────────────────────────────────────────────────────────────
info "=== ÉTAPE 3: Synchronisation avec origin ==="

git fetch origin 2>&1 | head -5 || warn "git fetch a échoué — continuer quand même"

# Vérifier si origin/ak_secure existe
if git rev-parse "origin/$BRANCH_DEV" &>/dev/null; then
    AHEAD=$(git log --oneline "origin/$BRANCH_DEV..HEAD" 2>/dev/null | wc -l)
    BEHIND=$(git log --oneline "HEAD..origin/$BRANCH_DEV" 2>/dev/null | wc -l)
    info "Local: +$AHEAD commit(s) d'avance, -$BEHIND commit(s) de retard"

    if [[ "$BEHIND" -gt 0 && "$AHEAD" -gt 0 ]]; then
        warn "Historiques divergents — rebase nécessaire"
        git rebase "origin/$BRANCH_DEV" || {
            error "Rebase échoué — résoudre les conflits manuellement"
            error "Commandes: git status → résoudre → git add → git rebase --continue"
            exit 1
        }
        FORCE_PUSH=true
    elif [[ "$BEHIND" -gt 0 ]]; then
        git merge "origin/$BRANCH_DEV" --ff-only || {
            warn "Fast-forward impossible — rebase"
            git rebase "origin/$BRANCH_DEV"
            FORCE_PUSH=true
        }
    fi
else
    info "Branche $BRANCH_DEV n'existe pas encore sur origin — premier push"
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 4 — Push avec le token frais
# ─────────────────────────────────────────────────────────────────────────────
info "=== ÉTAPE 4: Push vers origin/$BRANCH_DEV ==="

REMOTE_URL="https://x-access-token:${TOKEN}@github.com/${REPO_OWNER}/${REPO_NAME}.git"

PUSH_OPTS=()
if [[ "$FORCE_PUSH" == "true" ]]; then
    PUSH_OPTS+=("-f")
    warn "Force push activé (-f)"
fi

git -c credential.helper= push "${PUSH_OPTS[@]}" \
    "$REMOTE_URL" \
    "$BRANCH_DEV" && success "Push réussi vers origin/$BRANCH_DEV" || {
    error "Push échoué"
    error "Essayer avec --force si l'historique a été réécrit (rebase/squash)"
    exit 1
}

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 5 — Créer ou mettre à jour la PR (optionnel — nécessite gh CLI)
# ─────────────────────────────────────────────────────────────────────────────
info "=== ÉTAPE 5: Pull Request ==="

if ! command -v gh &>/dev/null; then
    warn "gh CLI non installé — skipping PR creation"
    warn "Installer avec: sudo apt install gh"
    info "PR manuelle: https://github.com/$REPO_OWNER/$REPO_NAME/pull/$PR_NUMBER"
    exit 0
fi

# Configurer gh avec le token
export GH_TOKEN="$TOKEN"

if [[ "$PR_NUMBER" -gt 0 ]]; then
    # Mettre à jour une PR existante
    LAST_COMMITS=$(git log --oneline -5 | sed 's/^/  - /')
    gh pr edit "$PR_NUMBER" \
        --repo "$REPO_OWNER/$REPO_NAME" \
        --body "## Derniers commits

$LAST_COMMITS

---
*Mis à jour automatiquement par push_github.sh*" 2>/dev/null && \
        success "PR #$PR_NUMBER mise à jour" || \
        warn "Mise à jour PR #$PR_NUMBER échouée (peut nécessiter gh auth)"

    info "URL PR: https://github.com/$REPO_OWNER/$REPO_NAME/pull/$PR_NUMBER"
else
    # Créer une nouvelle PR
    PR_URL=$(gh pr create \
        --repo "$REPO_OWNER/$REPO_NAME" \
        --head "$BRANCH_DEV" \
        --base "$BRANCH_BASE" \
        --title "$COMMIT_MSG" \
        --body "## Changements\n$COMMIT_MSG" 2>/dev/null) && \
        success "PR créée: $PR_URL" || \
        warn "Création PR échouée — créer manuellement"
fi

success "=== DONE ==="
