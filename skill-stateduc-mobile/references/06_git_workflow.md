# Git Workflow — StatEduc Mobile (GitHub App Token)

## Contexte

Le dépôt `stateduc_mobile` utilise un **GitHub App** pour l'authentification.
Les tokens GitHub App expirent **toutes les heures**. Un token périmé produit
une erreur `401 Unauthorized` au push — même si le dernier push a réussi.

### Symptôme token périmé
```
remote: Invalid username or password.
fatal: Authentication failed for 'https://github.com/...'
# Ou dans git credential store :
error: 401 Unauthorized
```

---

## Structure de branches

```
main                    ← branche de production (protégée)
  └── ak_secure         ← branche de développement AI Developer
        └── PR #2       ← pull request ouverte vers main
```

**Règle :** Tous les commits vont sur `ak_secure`. On ne pousse jamais
directement sur `main`.

---

## Workflow complet — étape par étape

### Étape 0 — Vérifier l'état du dépôt

```bash
cd /home/user/webapp
git status
git log --oneline -5
git remote -v
```

### Étape 1 — Faire les modifications de code

Modifier les fichiers nécessaires. Valider avec le script de syntaxe :
```bash
python3 /home/user/webapp/skill-stateduc-mobile/scripts/syntax_check.py \
  stateduc_flutter/lib/services/api_service.dart
```

### Étape 2 — Commit immédiat après chaque modification

```bash
cd /home/user/webapp
git add <fichiers modifiés>
# OU
git add -A

# Format conventionnel :
# type(scope): description courte
# Types : fix / feat / refactor / docs / chore
git commit -m "fix(ak-xxx): description claire de la correction"
```

**Convention de tags dans les messages de commit :**

| Tag | Signification |
|-----|---------------|
| `AK-FIX-PORT` | Correction port detection `_sised_local_port()` |
| `AK-FIX-MEM` | Augmentation memory_limit PHP |
| `AK-FIX-SESSION` | Guard `session_status()` avant `session_start()` |
| `AK-FIX-TIMEOUT` | Augmentation CURLOPT_TIMEOUT |
| `AK-CURL28` | Fix cURL error 28 (composite) |
| `AK-YEAR-MULTI` | Fonctionnalité multi-année |
| `AK-ANNEE-001` | Correction année active (conn vs conn_dico) |

### Étape 3 — Régénérer le token GitHub App

```bash
# Toujours supprimer les credentials en cache AVANT de générer
rm -f ~/.git-credentials

# Outil intégré Genspark
setup_github_environment  # ou via l'outil Claude

# Extraire le token du fichier credentials
TOKEN=$(grep 'github.com' ~/.git-credentials | sed 's/.*x-access-token:\(.*\)@.*/\1/')
echo "Token: ${TOKEN:0:20}..."  # afficher seulement le début
```

### Étape 4 — Synchroniser avec origin avant push

```bash
# Récupérer les changements distants
git fetch origin

# Vérifier si ak_secure est en avance ou en retard
git log --oneline origin/ak_secure..HEAD   # commits locaux non poussés
git log --oneline HEAD..origin/ak_secure   # commits distants non récupérés
```

Si des commits distants existent :
```bash
# Rebase (préféré pour garder un historique propre)
git rebase origin/ak_secure

# En cas de conflits :
git status                          # voir les fichiers en conflit
# Éditer les fichiers, résoudre les conflits
git add <fichiers résolus>
git rebase --continue
```

### Étape 5 — Squash optionnel avant PR

Si plusieurs petits commits locaux : les combiner en un seul commit propre.

```bash
# Compter les commits locaux non poussés
NB=$(git log --oneline origin/ak_secure..HEAD | wc -l)
echo "Commits à squasher : $NB"

# Squash non-interactif (N = nombre de commits à combiner)
git reset --soft HEAD~$NB
git commit -m "feat(ak-xxx): description complète de tous les changements

- Changement 1
- Changement 2
- Changement 3"
```

### Étape 6 — Push avec le token frais

```bash
# Méthode recommandée : injecter le token directement dans l'URL
git -c credential.helper= push \
  "https://x-access-token:${TOKEN}@github.com/NasserKailou/stateduc_mobile.git" \
  ak_secure

# Si first push (branche nouvelle) :
git -c credential.helper= push \
  --set-upstream \
  "https://x-access-token:${TOKEN}@github.com/NasserKailou/stateduc_mobile.git" \
  ak_secure

# Si rebase a réécrit l'historique (force push nécessaire) :
git -c credential.helper= push -f \
  "https://x-access-token:${TOKEN}@github.com/NasserKailou/stateduc_mobile.git" \
  ak_secure
```

### Étape 7 — Créer ou mettre à jour la Pull Request

```bash
# Vérifier si une PR existe déjà
gh pr list --repo NasserKailou/stateduc_mobile --head ak_secure

# Créer une nouvelle PR
gh pr create \
  --repo  NasserKailou/stateduc_mobile \
  --head  ak_secure \
  --base  main \
  --title "fix: description des corrections" \
  --body  "## Changements
...description détaillée..."

# Mettre à jour la description d'une PR existante
gh pr edit <numéro> \
  --repo NasserKailou/stateduc_mobile \
  --body "## Changements mis à jour
..."
```

---

## Script tout-en-un

Voir `scripts/push_github.sh` — exécuter avec :
```bash
bash /home/user/webapp/skill-stateduc-mobile/scripts/push_github.sh "fix(scope): message"
```

---

## Erreurs fréquentes et solutions

### 401 Unauthorized au push

```bash
# Cause: token périmé dans ~/.git-credentials
# Solution:
rm -f ~/.git-credentials
# → Régénérer le token (Étape 3)
# → Repousser (Étape 6)
```

### `error: failed to push some refs`

```bash
# Cause: origin/ak_secure a des commits que local n'a pas
# Solution:
git fetch origin
git rebase origin/ak_secure
# Résoudre conflits si nécessaire
git push ...
```

### `fatal: refusing to merge unrelated histories`

```bash
# Cause: historiques divergents (rare sur ak_secure)
# Solution:
git fetch origin
git merge origin/ak_secure --allow-unrelated-histories
# Résoudre conflits, puis:
git push ...
```

### `gh: command not found`

```bash
# Installer GitHub CLI
curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | \
  sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] \
  https://cli.github.com/packages stable main" | \
  sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null
sudo apt update && sudo apt install gh -y
```

### Brace imbalance faux-positif dans Dart

```bash
# Exécuter le vérificateur de syntaxe
python3 scripts/syntax_check.py lib/services/api_service.dart

# Le script exclut les commentaires // et /* */ avant de compter
# Un net +1 dans un fichier avec des ${...} dans des string templates
# est souvent un faux-positif — vérifier les lignes signalées
```

---

## Checklist pre-push

- [ ] `git status` → aucun fichier non stagé non intentionnel
- [ ] `git log --oneline -5` → commits lisibles et cohérents
- [ ] Syntaxe Dart vérifiée (`scripts/syntax_check.py`)
- [ ] `rm -f ~/.git-credentials` → credentials propres
- [ ] Token régénéré via `setup_github_environment`
- [ ] `git fetch origin` → synchronisé avec remote
- [ ] Push réussi (pas d'erreur 401/422)
- [ ] PR créée ou mise à jour
- [ ] URL de la PR communiquée à l'utilisateur
