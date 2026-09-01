# Patterns app_fie — PHP 8 / PDO / CSS / CSRF

## Vue d'ensemble

`app_fie` est l'application web complémentaire à StatEduc Mobile.
Elle gère : inscription des élèves, administration des établissements,
import/export de données, dashboard statistique, bibliothèque de documents.

**Stack** : PHP 8.x · PDO/SQLite (ou MySQL selon déploiement) · Bootstrap 5 · JavaScript vanilla

---

## APP-FIE-001 — Migration PHP 7→8 : ereg() → preg_match()

### Règle
`ereg()` et `eregi()` ont été supprimées en PHP 7.0. En PHP 8, elles causent un
`Fatal Error` immédiat. Remplacer **systématiquement** avant tout déploiement.

### Mapping de remplacement

| Ancienne fonction | Remplacement PHP 8 | Notes |
|---|---|---|
| `ereg($pattern, $str)` | `preg_match('/'.preg_quote($pattern,'/').'/', $str)` | Ajouter délimiteurs `/` |
| `eregi($pattern, $str)` | `preg_match('/'.preg_quote($pattern,'/').'/'.'i', $str)` | Flag `i` = insensible casse |
| `ereg_replace($p,$r,$s)` | `preg_replace('/'.preg_quote($p,'/').'/', $r, $s)` | |
| `eregi_replace($p,$r,$s)` | `preg_replace('/'.preg_quote($p,'/').'/'.'i', $r, $s)` | |
| `split($delim, $str)` | `preg_split('/'.$delim.'/', $str)` | Ou `explode()` si délimiteur fixe |

### Commande audit
```bash
# Trouver tous les fichiers avec ereg/eregi dans le projet
grep -rn --include="*.php" "ereg\|eregi\|split(" StatEduc_burundi/ | \
  grep -v "preg_\|//\|/*" | \
  grep -v ".bak\|.old\|.classold"
```

### Piège : ereg avec ancres `^` et `$`
```php
// AVANT (ereg supporte les ancres sans délimiteurs):
if (ereg('^[0-9]+$', $code)) { ... }

// APRÈS (preg_match nécessite délimiteurs):
if (preg_match('/^[0-9]+$/', $code)) { ... }
// NE PAS faire: preg_match('/^' . preg_quote('^[0-9]+$') . '$/')
// preg_quote() escaperait les ^ et $ — les garder tels quels pour les ancres
```

---

## APP-FIE-002 — Migration PHP 4→8 : constructeurs de classe

### Règle
PHP 4 permettait de nommer le constructeur comme la classe. PHP 8 : `Fatal Error`.

### Detection
```bash
# Trouver les constructeurs PHP4 dans les bibliothèques
grep -rn "function [A-Z][a-zA-Z_]*(" StatEduc_burundi/server-side/lib/ | \
  grep -v "__construct\|static\|abstract"
```

### Remplacement systématique
```php
// AVANT (PHP4):
class PclZip {
    function PclZip($p_path) {
        $this->path = $p_path;
    }
}

// APRÈS (PHP8):
class PclZip {
    function __construct($p_path) {
        $this->path = $p_path;
    }
}
```

### Bibliothèques concernées dans StatEduc
`fpdf.inc.php`, `htmlparser.inc.php`, `pclzip.lib.php`, `pdftable.inc.php`,
`sms.inc.php`, `class.ADODB_XML.php`, `class.xml.php`, `oleread.inc.php`, `reader.php`

---

## APP-FIE-003 — CSRF : utiliser FIE_CSRF_TOKEN_NAME + getCsrfToken()

### Règle
`app_fie` définit une constante `FIE_CSRF_TOKEN_NAME` et une fonction `getCsrfToken()`.
**Ne jamais** utiliser de literals string `'csrf_token'` ni `SecurityHelper::csrfToken()` (inexistant).

### Pattern correct
```php
// Dans tous les formulaires HTML:
'<input type="hidden" name="' . FIE_CSRF_TOKEN_NAME . '" value="' . getCsrfToken() . '">'

// Dans les contrôleurs (validation):
if (!validateCsrfToken($_POST[FIE_CSRF_TOKEN_NAME] ?? '')) {
    // Rejeter la requête
    http_response_code(403);
    die('CSRF token invalid');
}

// NE PAS FAIRE:
'<input type="hidden" name="csrf_token" value="...">'  // literal — bug si nom change
$token = SecurityHelper::csrfToken();                    // méthode inexistante
```

### Audit CSRF dans le projet
```bash
grep -rn --include="*.php" "csrf_token\|csrfToken\|SecurityHelper" app_fie/
# Toutes les occurrences doivent utiliser FIE_CSRF_TOKEN_NAME / getCsrfToken()
```

---

## APP-FIE-004 — PDO : cohérence paramètres nommés vs positionnels

### Règle
Ne jamais mélanger paramètres nommés (`:nom`) et positionnels (`?`) dans la même requête.
L'un ou l'autre, pas les deux.

### Erreur SQLSTATE HY093
```
PDOException: SQLSTATE[HY093]: Invalid parameter number
```
Cause : paramètres nommés avec execute() positionnel, ou inverse.

### Patterns corrects

```php
// ✅ POSITIONNEL (recommandé pour requêtes simples)
$stmt = $pdo->prepare("SELECT * FROM eleves WHERE nom = ? AND prenom = ?");
$stmt->execute([$nom, $prenom]);

// ✅ NOMMÉ (recommandé pour requêtes complexes — meilleure lisibilité)
$stmt = $pdo->prepare("SELECT * FROM eleves WHERE nom = :nom AND prenom = :prenom");
$stmt->execute([':nom' => $nom, ':prenom' => $prenom]);
// Note: le ':' est optionnel dans l'array mais recommandé pour la clarté

// ❌ MIXTE (HY093):
$stmt = $pdo->prepare("SELECT * FROM eleves WHERE nom = :nom AND prenom = :prenom");
$stmt->execute([$nom, $prenom]);  // ERREUR: nommés + positionnels
```

### Règle INSERT multi-colonnes
```php
// Pour un INSERT avec 10+ colonnes, utiliser nommés pour la lisibilité:
$stmt = $pdo->prepare("
    INSERT INTO eleves (nom, prenom, date_naissance, sexe, classe, iue)
    VALUES (:nom, :prenom, :dob, :sexe, :classe, :iue)
");
$stmt->execute([
    ':nom'    => $nom,
    ':prenom' => $prenom,
    ':dob'    => $dateNaissance,
    ':sexe'   => $sexe,
    ':classe' => $classe,
    ':iue'    => $iue,
]);
```

---

## APP-FIE-005 — CSS couleurs institutionnelles Burundi

### Palette officielle
```css
/* fie.css — variables CSS racine */
:root {
    /* Couleurs institutionnelles Burundi */
    --bi-red:     #CE1126;  /* rouge officiel Burundi */
    --bi-green:   #1EB53A;  /* vert officiel Burundi */
    --bi-white:   #FFFFFF;
    --bi-blue:    #1a56db;  /* bleu institutionnel UNESCO/navigation */
    --bi-blue-d:  #1041b3;  /* bleu foncé pour états actifs */

    /* Rôles dans l'UI */
    --fie-primary:      var(--bi-red);     /* accents, boutons principaux */
    --fie-app-blue:     var(--bi-blue);    /* navbar de navigation */
    --fie-app-blue-dark: var(--bi-blue-d); /* hover navbar */
}
```

### Règle rouge vs bleu
- **Rouge** (`--bi-red` / `--fie-primary`) : accents UI, boutons call-to-action,
  badge actif, couleurs de la marque BPSE/Burundi
- **Bleu** (`--fie-app-blue`) : navbar de navigation, topbar de l'app
- **Ne jamais** mettre `--bi-red` dans la topbar (illisible), ni `--bi-blue` sur des
  éléments qui doivent rappeler l'identité Burundi

### Drapeau Burundi dans l'UI
```html
<!-- Bande de drapeau rouge-blanc-vert (dans l'entête de connexion) -->
<div class="flag-strip">
    <div class="fie-auth-flag__red"></div>    <!-- rouge -->
    <div class="fie-auth-flag__white"></div>  <!-- blanc -->
    <div class="fie-auth-flag__green"></div>  <!-- vert -->
</div>
```

---

## APP-FIE-006 — Import élèves CSV/Excel avec génération IUE automatique

### IUE — Identifiant Unique Élève
Format : `{CODE_ECOLE}-{ANNEE}-{SEQUENCE_5CHIFFRES}`
Exemple : `BDI-2025-00042`

### Structure CSV d'import
```csv
nom,prenom,date_naissance,sexe,classe,nationalite
NDAYISHIMIYE,Pierre,2010-03-15,M,6ème,burundaise
HAKIZIMANA,Marie,2011-07-22,F,5ème,congolaise
```

### Endpoint PHP
```php
// POST /admin/import-eleves
// Accept: multipart/form-data (fichier CSV ou Excel .xlsx)

$controller->importEleves($file, $codeEtablissement, $annee);
// → lit chaque ligne
// → génère IUE si absent: {CODE_ECOLE}-{ANNEE}-{seq auto-incrémenté}
// → INSERT INTO eleves avec upsert sur (nom, prenom, dob, classe)
// → retourne JSON: {importes: N, doublons: M, erreurs: []}
```

### Génération IUE côté PHP
```php
function genererIUE(string $codeEcole, int $annee, PDO $pdo): string {
    // Obtenir le prochain numéro de séquence
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM eleves WHERE code_etablissement = ? AND annee = ?"
    );
    $stmt->execute([$codeEcole, $annee]);
    $seq = (int)$stmt->fetchColumn() + 1;

    return sprintf('%s-%d-%05d', $codeEcole, $annee, $seq);
}
```

---

## APP-FIE-007 — Synchronisation des nationalités depuis StatEduc

`app_fie` maintient une table `ref_type_nationalite` synchronisée depuis l'API StatEduc.

### 7 nationalités par défaut (migration 006)
```sql
INSERT INTO ref_type_nationalite (code, libelle) VALUES
('burundaise',   'Burundaise'),
('congolaise',   'Congolaise (RDC)'),
('rwandaise',    'Rwandaise'),
('tanzanienne',  'Tanzanienne'),
('ugandaise',    'Ougandaise'),
('kenyane',      'Kenyane'),
('autre',        'Autre (préciser)');
```

### SyncService::syncNationalites()
```php
// Appelé périodiquement ou au démarrage admin
public function syncNationalites(string $apiBaseUrl): array {
    $response = file_get_contents($apiBaseUrl . '/nationalites_ws.php/liste');
    $data = json_decode($response, true);

    $upserted = 0;
    foreach ($data as $item) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO ref_type_nationalite (code, libelle)
             VALUES (?, ?)
             ON CONFLICT(code) DO UPDATE SET libelle = excluded.libelle"
        );
        $stmt->execute([$item['code'], $item['libelle']]);
        $upserted++;
    }
    return ['synced' => $upserted];
}
```

### UI inscription : champ libre si "Autre"
```html
<select name="nationalite" id="nationalite" onchange="toggleAutreNationalite(this)">
    {% for nat in nationalites %}
    <option value="{{ nat.code }}">{{ nat.libelle }}</option>
    {% endfor %}
</select>
<input type="text" name="nationalite_autre" id="nationalite_autre"
    placeholder="Préciser la nationalité" style="display:none">

<script>
function toggleAutreNationalite(sel) {
    const autre = document.getElementById('nationalite_autre');
    autre.style.display = (sel.value === 'autre') ? 'block' : 'none';
    autre.required = (sel.value === 'autre');
}
</script>
```

---

## APP-FIE-008 — Bibliothèque documents : popup PDF inline

### Pattern popup avec iframe
```html
<!-- Bouton déclencheur -->
<button class="btn btn-sm btn-outline-primary"
        onclick="openPDFPopup('{{ doc.url }}?inline=1')">
    <i class="fas fa-book-open"></i> Consulter
</button>

<!-- Modal Bootstrap 5 -->
<div class="modal fade" id="pdfModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalTitle">Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="pdfFrame" src="" width="100%"
                        style="height:80vh;border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
function openPDFPopup(url) {
    document.getElementById('pdfFrame').src = url;
    new bootstrap.Modal(document.getElementById('pdfModal')).show();
}
// Vider l'iframe à la fermeture (éviter chargement inutile)
document.getElementById('pdfModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('pdfFrame').src = '';
});
</script>
```

### Endpoint PHP pour servir le PDF inline
```php
// ?inline=1 → Content-Disposition: inline (affichage dans l'iframe)
// Sans paramètre → Content-Disposition: attachment (téléchargement)
$inline = isset($_GET['inline']) && $_GET['inline'] == '1';
header('Content-Type: application/pdf');
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="doc.pdf"');
readfile($pdfPath);
```

---

## APP-FIE-009 — QR Code de connexion

Génération d'un QR code contenant l'URL de connexion app_fie pour faciliter
l'accès depuis les smartphones des directeurs.

```php
// Utiliser bacon/bacon-qr-code (composer)
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$renderer = new ImageRenderer(
    new RendererStyle(200),
    new SvgImageBackEnd()
);
$writer = new Writer($renderer);
$svgQr  = $writer->writeString($loginUrl);
// Retourner en data URI pour embed direct dans HTML:
echo '<img src="data:image/svg+xml;base64,' . base64_encode($svgQr) . '">';
```

---

## APP-FIE-010 — Migration agents mobiles (DICO_FIXE_REGROUPEMENT multi-année)

Quand une nouvelle année de collecte est créée, les agents mobiles doivent être
migrés vers la nouvelle année. Le formulaire admin simplifié :

```php
// gestion_user.php — formulaire minimaliste
// 1 seule combo "Année cible" + bouton "Migrer"
// Handler: UPDATE DICO_FIXE_REGROUPEMENT SET ID_ANNEE = :new_annee

// Enrichissement hiérarchie manquante (AK-PHP-01):
// Si ID_REGROUP_PARENTS vide → reconstruire depuis ETABLISSEMENT_REGROUPEMENT
$sql = "
    SELECT r.id, r.code, h.niveau_chaine
    FROM REGROUPEMENT r
    JOIN HIERARCHIE h ON h.id_regroupement = r.id
    WHERE h.id_chaine = :id_chaine
    ORDER BY h.niveau_chaine ASC
";
// Résultat: colline (niveau 1) → commune (niveau 2) → province (niveau 3)
```

---

## Checklist déploiement app_fie sur nouveau serveur PHP 8

- [ ] `grep -rn "ereg\|eregi" --include="*.php"` → 0 résultat attendu
- [ ] `grep -rn "function [A-Z]" server-side/lib/ --include="*.php"` → 0 constructeurs PHP4
- [ ] `grep -rn "get_magic_quotes_gpc\|set_magic_quotes" --include="*.php"` → 0 résultat
- [ ] Toutes les defines `ADODB_ASSOC_CASE` wrappées avec `if(!defined())`
- [ ] Toutes les occurrences CSRF utilisent `FIE_CSRF_TOKEN_NAME` + `getCsrfToken()`
- [ ] Aucun `SecurityHelper::csrfToken()` dans le code
- [ ] PDO : aucun mélange nommé/positionnel (`SQLSTATE HY093`)
- [ ] `memory_limit` ≥ 256M dans `php.ini` ou `.htaccess`
- [ ] Extension `pdo_sqlite` (ou `pdo_mysql`) activée
- [ ] Extension `zip`, `simplexml` activées (import Excel)
