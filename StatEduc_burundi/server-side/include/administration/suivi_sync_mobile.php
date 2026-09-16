<?php
/**
 * suivi_sync_mobile.php  —  AK-SYNC (branche ak_burundi)  —  U14 / U15 / U18
 *
 * Ecran administrateur « Suivi synchronisation mobile ».
 *   administration.php?val=suivi_sync_mobile
 *
 * Matrice etablissement x theme alimentee par DATA_SAVING_LOGS (lecture seule),
 * filtres annee / campagne / secteur / periode / chaine-regroupement / agent / statut,
 * detail par etablissement (historique complet des tentatives), export CSV,
 * actions administratives (U18) : demander une correction, valider, verrouiller, rouvrir.
 *
 * Toute la donnee est chargee en AJAX via suivi_sync_mobile_service.php
 * (administration.php?val=suivi_sync_mobile_svc&action=...). jQuery 1.9 est
 * deja charge par header.php. Aucune dependance nouvelle.
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 */
set_time_limit(0);
include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
require_once $GLOBALS['SISED_PATH'] . 'sync_lib.php';

$ak_svc_url = 'administration.php?val=suivi_sync_mobile_svc';
$ak_migration_ok = ak_sync_logs_extended($GLOBALS['conn_dico']);
$ak_statut_ok    = ak_sync_table_exists($GLOBALS['conn_dico'], 'SYNC_ETAB_STATUT');
?>
<style type="text/css">
  #aksync { font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
  #aksync .ak-filters { background:#f3f3f3; border:1px solid #ccc; padding:8px; margin-bottom:8px; }
  #aksync .ak-filters label { font-weight:bold; margin-right:4px; }
  #aksync .ak-filters select, #aksync .ak-filters input { margin-right:12px; margin-bottom:4px; }
  #aksync .ak-warn { background:#fff3cd; border:1px solid #e0b000; padding:6px; margin-bottom:8px; color:#6b4f00; }
  #aksync .ak-kpi { display:inline-block; padding:4px 10px; margin:0 6px 6px 0; border-radius:4px; color:#fff; font-weight:bold; cursor:pointer; }
  #aksync .ak-kpi.total   { background:#555; }
  #aksync .ak-kpi.COMPLET { background:#2e7d32; }
  #aksync .ak-kpi.PARTIEL { background:#ef8f00; }
  #aksync .ak-kpi.ERREUR  { background:#c62828; }
  #aksync .ak-kpi.AUCUN   { background:#9e9e9e; }
  #aksync .ak-kpi.active  { outline:3px solid #1565c0; }
  #aksync table.ak-matrix { border-collapse:collapse; width:100%; }
  #aksync table.ak-matrix th, #aksync table.ak-matrix td { border:1px solid #bbb; padding:3px 5px; white-space:nowrap; }
  #aksync table.ak-matrix th { background:#ddd; position:sticky; top:0; }
  #aksync table.ak-matrix th.theme { writing-mode:vertical-rl; transform:rotate(180deg); max-height:160px; font-weight:normal; }
  #aksync td.st-OK     { background:#c8e6c9; text-align:center; }
  #aksync td.st-VIDE   { background:#e1f5fe; text-align:center; }
  #aksync td.st-ERREUR { background:#ffcdd2; text-align:center; }
  #aksync td.st-AUCUN  { background:#f5f5f5; text-align:center; color:#999; }
  #aksync td.glob-COMPLET { font-weight:bold; color:#2e7d32; }
  #aksync td.glob-PARTIEL { font-weight:bold; color:#ef8f00; }
  #aksync td.glob-ERREUR  { font-weight:bold; color:#c62828; }
  #aksync td.glob-AUCUN   { color:#9e9e9e; }
  #aksync .adm-A_CORRIGER { color:#c62828; font-weight:bold; }
  #aksync .adm-VALIDE     { color:#2e7d32; font-weight:bold; }
  #aksync .adm-VERROUILLE { color:#6a1b9a; font-weight:bold; }
  #aksync .adm-RECU       { color:#1565c0; }
  #aksync a.ak-etab { color:#1565c0; text-decoration:underline; cursor:pointer; }
  #aksync .ak-scroll { max-height:520px; overflow:auto; border:1px solid #bbb; }
  #aksync .ak-actions button { margin-right:6px; }
  #aksync .ak-bc a { cursor:pointer; color:#1565c0; }
  #ak-detail-panel { display:none; border:2px solid #1565c0; background:#fff; padding:8px; margin-top:10px; }
  #ak-detail-panel h3 { margin:4px 0 8px 0; }
  #ak-detail-panel table { border-collapse:collapse; width:100%; }
  #ak-detail-panel th, #ak-detail-panel td { border:1px solid #ccc; padding:2px 4px; font-size:11px; }
  #ak-detail-panel .ak-hist { max-height:220px; overflow:auto; }
  #ak-loading { color:#1565c0; font-weight:bold; display:none; }
</style>

<div id="aksync">
  <h2>Suivi synchronisation mobile</h2>

  <?php if (!$ak_migration_ok): ?>
  <div class="ak-warn">&#9888; La migration <b>migration_sync_tracabilite_*.sql</b> n'est pas appliquee sur la base dico :
    les compteurs de lignes, l'idempotence et le detail des erreurs ne sont pas disponibles (seuls OKSAVE / KOSAVE / KO le sont).</div>
  <?php endif; ?>
  <?php if (!$ak_statut_ok): ?>
  <div class="ak-warn">&#9888; La table <b>SYNC_ETAB_STATUT</b> est absente : les actions administratives (correction / validation / verrou) sont desactivees.</div>
  <?php endif; ?>

  <div class="ak-filters">
    <label>Ann&eacute;e</label><select id="f_annee"></select>
    <label>Campagne</label><select id="f_camp"></select>
    <label>Secteur</label><select id="f_sect"></select>
    <label>P&eacute;riode</label><select id="f_per"><option value="">Toutes</option></select>
    <label>Agent</label><select id="f_agent"><option value="">Tous</option></select>
    <br/>
    <label>Cha&icirc;ne</label><select id="f_chaine"><option value="">Tous les &eacute;tablissements du secteur</option></select>
    <span class="ak-bc" id="f_bc"></span>
    <select id="f_reg" style="display:none"></select>
    <button type="button" id="btn_load">Afficher</button>
    <button type="button" id="btn_csv">Export CSV</button>
    <span id="ak-loading">Chargement&hellip;</span>
  </div>

  <div id="ak-kpis"></div>
  <div class="ak-scroll"><table class="ak-matrix" id="ak-matrix"><thead></thead><tbody></tbody></table></div>

  <div id="ak-detail-panel">
    <h3 id="akd-title"></h3>
    <div class="ak-actions" id="akd-actions">
      <label>Th&egrave;me :</label><select id="akd-theme"><option value="">Tous les th&egrave;mes de l'&eacute;tablissement</option></select>
      <input type="text" id="akd-comment" size="50" placeholder="Commentaire (obligatoire pour une demande de correction)"/>
      <button type="button" class="akd-set" data-st="A_CORRIGER">Demander correction</button>
      <button type="button" class="akd-set" data-st="VALIDE">Valider</button>
      <button type="button" class="akd-set" data-st="VERROUILLE">Verrouiller</button>
      <button type="button" id="akd-reopen">Rouvrir (RECU)</button>
      <button type="button" id="akd-close" style="float:right">Fermer</button>
    </div>
    <h4>Statuts administratifs actifs</h4><div id="akd-statuts"></div>
    <h4>Historique des transmissions (500 derni&egrave;res)</h4><div class="ak-hist" id="akd-logs"></div>
    <h4>Historique des d&eacute;cisions</h4><div class="ak-hist" id="akd-hist"></div>
  </div>
</div>

<script type="text/javascript">
(function($){
  var SVC = '<?php echo $ak_svc_url; ?>';
  var F = null, THEMES = [], ETABS = [], CUR = null, BC = [], STATUT_OK = <?php echo $ak_statut_ok ? 'true' : 'false'; ?>;
  var STATUT_FILTER = '';

  function esc(s){ return $('<div/>').text(s == null ? '' : String(s)).html(); }
  function loading(b){ $('#ak-loading').toggle(!!b); }
  function ajax(params, cb, method){
    loading(true);
    $.ajax({ url: SVC, type: method || 'GET', data: params, dataType: 'json',
      success: function(r){ loading(false); if(r && r.se_statut === 200) cb(r.se_data); else alert((r && r.se_message) || 'Erreur service'); },
      error: function(x){ loading(false); alert('Erreur AJAX ' + x.status); } });
  }

  // ── Filtres ─────────────────────────────────────────────────────────────
  ajax({action:'filters'}, function(d){
    F = d;
    $.each(d.annees, function(i,a){ $('#f_annee').append('<option value="'+a.code+'"'+(a.code==d.annee_active?' selected':'')+'>'+esc(a.libelle)+'</option>'); });
    $.each(d.campagnes, function(i,c){ $('#f_camp').append('<option value="'+c.id+'">'+esc(c.libelle)+'</option>'); });
    $.each(d.secteurs, function(i,s){ $('#f_sect').append('<option value="'+s.id+'"'+(s.id==d.secteur_actif?' selected':'')+'>'+esc(s.libelle)+'</option>'); });
    $.each(d.periodes, function(i,p){ $('#f_per').append('<option value="'+p.id+'">'+esc(p.libelle)+'</option>'); });
    $.each(d.agents, function(i,a){ if(a.groupe == <?php echo (int)(isset($GLOBALS['PARAM']['MOBILE_CODE_GROUPE']) ? $GLOBALS['PARAM']['MOBILE_CODE_GROUPE'] : 4); ?> || true) $('#f_agent').append('<option value="'+esc(a.login)+'">'+esc(a.login)+' — '+esc(a.nom)+'</option>'); });
    $.each(d.chaines, function(i,c){ $('#f_chaine').append('<option value="'+c.id+'">'+esc(c.libelle)+'</option>'); });
  });

  // ── Drill-down regroupements ────────────────────────────────────────────
  function renderBc(){
    var h = '';
    $.each(BC, function(i,b){ h += ' &raquo; <a data-i="'+i+'">'+esc(b.libelle)+'</a>'; });
    $('#f_bc').html(h);
  }
  function loadRegroups(parent, depht){
    var ch = $('#f_chaine').val(); if(!ch){ $('#f_reg').hide(); return; }
    ajax({action:'regroups', id_chaine: ch, code_parent: parent || '', depht: depht}, function(list){
      var s = $('#f_reg').empty().show();
      s.append('<option value="">— choisir un regroupement (optionnel) —</option>');
      $.each(list, function(i,r){ s.append('<option value="'+r.code+'" data-depht="'+r.depht+'" data-next="'+(r.has_next?1:0)+'">'+esc(r.libelle)+'</option>'); });
    });
  }
  $('#f_chaine').change(function(){ BC = []; renderBc(); loadRegroups('', 0); });
  $('#f_reg').change(function(){
    var o = $(this).find('option:selected'); if(!o.val()) return;
    BC.push({code:o.val(), libelle:o.text(), depht:parseInt(o.data('depht'),10)}); renderBc();
    if(o.data('next') == 1) loadRegroups(o.val(), BC[BC.length-1].depht + 1); else $('#f_reg').hide();
  });
  $('#f_bc').on('click','a',function(){ var i = parseInt($(this).data('i'),10); BC = BC.slice(0, i+1); renderBc(); var b = BC[i]; loadRegroups(b.code, b.depht+1); });

  function params(){
    var p = {action:'matrix', id_annee:$('#f_annee').val(), id_camp:$('#f_camp').val(), id_sector:$('#f_sect').val(),
             id_periode:$('#f_per').val(), agent:$('#f_agent').val(), statut: STATUT_FILTER};
    if(BC.length){ p.id_chaine = $('#f_chaine').val(); p.code_reg = BC[BC.length-1].code; p.depht = BC[BC.length-1].depht; }
    return p;
  }

  // ── Matrice ─────────────────────────────────────────────────────────────
  function render(d){
    THEMES = d.themes; ETABS = d.etabs;
    var k = '<span class="ak-kpi total'+(STATUT_FILTER===''?' active':'')+'" data-st="">Total : '+d.totaux.total+'</span>';
    $.each(['COMPLET','PARTIEL','ERREUR','AUCUN'], function(i,s){ k += '<span class="ak-kpi '+s+(STATUT_FILTER===s?' active':'')+'" data-st="'+s+'">'+s+' : '+d.totaux[s]+'</span>'; });
    $('#ak-kpis').html(k);
    var th = '<tr><th>#</th><th>&Eacute;tablissement</th><th>Code</th><th>Statut</th><th>OK/Att.</th><th>Derni&egrave;re r&eacute;ception</th><th>Admin</th>';
    $.each(THEMES, function(i,t){ th += '<th class="theme" title="'+esc(t.title)+'">'+esc(t.title)+'</th>'; });
    th += '</tr>'; $('#ak-matrix thead').html(th);
    var tb = '';
    $.each(ETABS, function(i,e){
      tb += '<tr><td>'+(i+1)+'</td><td><a class="ak-etab" data-i="'+i+'">'+esc(e.nom)+'</a></td><td>'+esc(e.code||e.id)+'</td>';
      tb += '<td class="glob-'+e.statut+'">'+e.statut+'</td><td>'+e.nb_ok+'/'+e.nb_attendus+(e.nb_ko?' <span style="color:#c62828">('+e.nb_ko+' err)</span>':'')+'</td>';
      tb += '<td>'+esc(e.derniere_reception||'')+'</td><td class="adm-'+(e.admin?e.admin.statut:'')+'" title="'+esc(e.admin?e.admin.commentaire:'')+'">'+(e.admin?e.admin.statut:'')+'</td>';
      $.each(THEMES, function(j,t){
        var c = e.themes[t.id] || {statut:'AUCUN'};
        var sym = c.statut==='OK'?'&#10004;':(c.statut==='ERREUR'?'&#10008;':(c.statut==='VIDE'?'&#9675;':'&middot;'));
        var tip = c.statut + (c.date?' — '+c.date:'') + (c.user?' — '+c.user:'') + (c.nb_rows!=null?' — '+c.nb_rows+' lignes':'') + (c.nb_tentatives?' — '+c.nb_tentatives+' tentative(s)':'') + (c.message?'\n'+c.message:'');
        tb += '<td class="st-'+c.statut+'" title="'+esc(tip)+'">'+sym+'</td>';
      });
      tb += '</tr>';
    });
    $('#ak-matrix tbody').html(tb || '<tr><td colspan="'+(7+THEMES.length)+'">Aucun &eacute;tablissement</td></tr>');
  }
  $('#btn_load').click(function(){ STATUT_FILTER=''; ajax(params(), render); });
  $('#ak-kpis').on('click','.ak-kpi',function(){ STATUT_FILTER = $(this).data('st'); ajax(params(), render); });
  $('#btn_csv').click(function(){ var p = params(); p.action='export'; window.location.href = SVC + '&' + $.param(p); });

  // ── Detail etablissement ────────────────────────────────────────────────
  $('#ak-matrix').on('click','a.ak-etab',function(){
    CUR = ETABS[parseInt($(this).data('i'),10)];
    $('#akd-title').text(CUR.nom + ' (' + (CUR.code||CUR.id) + ') — statut global ' + CUR.statut);
    var s = $('#akd-theme').empty().append('<option value="">Tous les th&egrave;mes de l\'&eacute;tablissement</option>');
    $.each(THEMES, function(i,t){ s.append('<option value="'+t.id+'">'+esc(t.title)+'</option>'); });
    $('#akd-actions button.akd-set, #akd-reopen').prop('disabled', !STATUT_OK);
    loadDetail(); $('#ak-detail-panel').show();
  });
  function loadDetail(){
    ajax({action:'detail', id_annee:$('#f_annee').val(), id_camp:$('#f_camp').val(), code_etab:CUR.id}, function(d){
      var h = '<table><tr><th>Th&egrave;me</th><th>P&eacute;riode</th><th>Statut</th><th>Commentaire</th><th>Admin</th><th>Date</th></tr>';
      $.each(d.statuts, function(i,s){ h += '<tr><td>'+esc(s.id_theme||'Tous')+'</td><td>'+esc(s.code_filtre||'')+'</td><td class="adm-'+s.statut+'">'+s.statut+'</td><td>'+esc(s.commentaire)+'</td><td>'+esc(s.admin)+'</td><td>'+esc(s.date)+'</td></tr>'; });
      $('#akd-statuts').html(d.statuts.length ? h+'</table>' : '<i>Aucun statut administratif</i>');
      h = '<table><tr><th>Date</th><th>Agent</th><th>Th&egrave;me</th><th>P&eacute;r.</th><th>Statut</th><th>I</th><th>U</th><th>D</th><th>=</th><th>Message</th><th>APK</th><th>IP</th></tr>';
      $.each(d.logs, function(i,l){ var thn = ''; $.each(THEMES,function(j,t){ if(t.id==l.id_theme) thn=t.title; });
        h += '<tr><td>'+esc(l.date)+'</td><td>'+esc(l.user)+'</td><td title="'+esc(thn)+'">'+l.id_theme+'</td><td>'+esc(l.periode||'')+'</td><td class="glob-'+(l.statut==='OKSAVE'?'COMPLET':(l.statut==='DUPLICATE'?'AUCUN':'ERREUR'))+'">'+esc(l.statut)+'</td>'
           + '<td>'+esc(l.nb_insert==null?'':l.nb_insert)+'</td><td>'+esc(l.nb_update==null?'':l.nb_update)+'</td><td>'+esc(l.nb_delete==null?'':l.nb_delete)+'</td><td>'+esc(l.nb_unchanged==null?'':l.nb_unchanged)+'</td>'
           + '<td title="'+esc(l.message)+'">'+esc((l.message||'').substring(0,60))+'</td><td>'+esc(l.app_version||'')+'</td><td>'+esc(l.ip||'')+'</td></tr>'; });
      $('#akd-logs').html(d.logs.length ? h+'</table>' : '<i>Aucune transmission journalis&eacute;e</i>');
      h = '<table><tr><th>Date</th><th>Th&egrave;me</th><th>Ancien</th><th>Nouveau</th><th>Commentaire</th><th>Admin</th></tr>';
      $.each(d.historique, function(i,x){ h += '<tr><td>'+esc(x.date)+'</td><td>'+esc(x.id_theme||'Tous')+'</td><td>'+esc(x.ancien||'')+'</td><td class="adm-'+x.nouveau+'">'+esc(x.nouveau)+'</td><td>'+esc(x.commentaire)+'</td><td>'+esc(x.admin)+'</td></tr>'; });
      $('#akd-hist').html(d.historique.length ? h+'</table>' : '<i>Aucune d&eacute;cision</i>');
    });
  }
  function postStatut(st, comment){
    ajax({action:'set_statut', id_annee:$('#f_annee').val(), id_camp:$('#f_camp').val(), id_sector:$('#f_sect').val(), code_etab:CUR.id,
          id_theme:$('#akd-theme').val(), code_filtre:$('#f_per').val(), statut:st, commentaire:comment}, function(){
      $('#akd-comment').val(''); loadDetail(); ajax(params(), render);
    }, 'POST');
  }
  $('#akd-actions').on('click','button.akd-set',function(){
    var st = $(this).data('st'), c = $('#akd-comment').val();
    if(st==='A_CORRIGER' && !c){ alert('Le commentaire est obligatoire pour une demande de correction (il sera affich\u00e9 \u00e0 l\'agent).'); return; }
    if(!confirm('Confirmer le statut ' + st + ' pour ' + CUR.nom + ($('#akd-theme').val()?' (th\u00e8me '+$('#akd-theme option:selected').text()+')':' (tous th\u00e8mes)') + ' ?')) return;
    postStatut(st, c);
  });
  $('#akd-reopen').click(function(){
    if(!confirm('Rouvrir ' + CUR.nom + ' : le statut repasse \u00e0 RECU (les envois mobiles sont de nouveau accept\u00e9s) ?')) return;
    ajax({action:'reopen', id_annee:$('#f_annee').val(), id_camp:$('#f_camp').val(), id_sector:$('#f_sect').val(), code_etab:CUR.id, id_theme:$('#akd-theme').val(), code_filtre:$('#f_per').val()},
         function(){ loadDetail(); ajax(params(), render); }, 'POST');
  });
  $('#akd-close').click(function(){ $('#ak-detail-panel').hide(); });
})(jQuery);
</script>
