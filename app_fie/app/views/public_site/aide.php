<?php
$page_title  = $page_title  ?? 'FIE Burundi';
$active_menu = '';
require __DIR__ . '/../layouts/header.php';
?>
<div class="fie-page-header"><h1 class="fie-page-title"><?php
if ('aide' == 'aide') echo 'Aide et documentation';
elseif ('aide' == 'contact') echo 'Contact';
elseif ('aide' == 'confidentialite') echo 'Politique de confidentialité';
else echo 'Mentions légales';
?></h1></div>
<div class="fie-card">
<?php if ('aide' == 'confidentialite' || 'aide' == 'mentions'): ?>
<p>Conformément à la loi n°1/03-2026 relative à la protection des données à caractère personnel
au Burundi, toutes les données collectées par le FIE sont utilisées exclusivement à des fins
statistiques et administratives scolaires. Elles sont traitées sous la responsabilité du
Ministère de l'Éducation Nationale et de la Recherche Scientifique (MENERS) — DGESS/SIGE Burundi.</p>
<p>Aucune donnée personnelle d'élève n'est communiquée à des tiers sans autorisation légale.
Les journaux d'audit sont conservés 5 ans. Droit d'accès et de rectification à exercer
auprès de l'administrateur système.</p>
<?php elseif ('aide' == 'contact'): ?>
<p><strong>DGESS / SIGE Burundi</strong><br>
Ministère de l'Éducation Nationale et de la Recherche Scientifique<br>
Bujumbura, Burundi</p>
<p>Pour toute question technique relative au FIE, contacter le support SIGE.</p>
<?php else: ?>
<p>La documentation complète du FIE est disponible dans le dossier <code>docs/</code>
du dépôt source. Elle comprend la feuille de route de déploiement, la note technique
d'architecture MySQL↔SQL Server, et le guide utilisateur.</p>
<?php endif; ?>
</div>
<a href="<?= BASE_URL ?>/" class="fie-btn fie-btn--ghost">Retour à l'accueil</a>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
