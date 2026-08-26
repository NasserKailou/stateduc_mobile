/**
 * FIE — Script JavaScript utilitaire commun
 * Vanilla JS uniquement — zéro dépendance jQuery
 * Chargé en pied de page par layouts/footer.php
 */
'use strict';

/* ── Burger menu mobile ──────────────────────────────────────────────────── */
(function () {
    const burger = document.querySelector('.fie-navbar__burger');
    const nav    = document.querySelector('.fie-navbar__nav');
    if (!burger || !nav) return;

    burger.addEventListener('click', function () {
        const open = nav.classList.toggle('fie-navbar__nav--open');
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // Fermer si clic en dehors
    document.addEventListener('click', function (e) {
        if (!burger.contains(e.target) && !nav.contains(e.target)) {
            nav.classList.remove('fie-navbar__nav--open');
            burger.setAttribute('aria-expanded', 'false');
        }
    });
}());

/* ── Utilitaire fetch JSON (POST) ────────────────────────────────────────── */
/**
 * Envoie une requête POST JSON et retourne la réponse parsée.
 * @param {string} url
 * @param {Object} data
 * @returns {Promise<Object>}
 */
window.postJSON = async function postJSON(url, data) {
    const resp = await fetch(url, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body:    JSON.stringify(data),
    });
    if (!resp.ok) throw new Error('HTTP ' + resp.status);
    return resp.json();
};

/* ── Utilitaire fetch JSON (GET) ─────────────────────────────────────────── */
window.getJSON = async function getJSON(url, params) {
    if (params && Object.keys(params).length) {
        url += '?' + new URLSearchParams(params).toString();
    }
    const resp = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!resp.ok) throw new Error('HTTP ' + resp.status);
    return resp.json();
};

/* ── Spinner overlay ─────────────────────────────────────────────────────── */
(function () {
    const overlay = document.createElement('div');
    overlay.className = 'fie-spinner-overlay';
    overlay.innerHTML = '<div class="fie-spinner" role="status" aria-label="Chargement…"></div>';
    document.body.appendChild(overlay);

    window.fieShowSpinner = function () {
        overlay.classList.add('fie-spinner-overlay--visible');
    };
    window.fieHideSpinner = function () {
        overlay.classList.remove('fie-spinner-overlay--visible');
    };
}());

/* ── Dismiss auto-hide des alertes flash ─────────────────────────────────── */
(function () {
    document.querySelectorAll('[data-autohide]').forEach(function (el) {
        const delay = parseInt(el.dataset.autohide, 10) || 4000;
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity    = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, delay);
    });
}());

/* ── Confirmation avant suppression ─────────────────────────────────────── */
document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
        if (!confirm(el.dataset.confirm || 'Êtes-vous sûr ?')) {
            e.preventDefault();
        }
    });
});

/* ── Sélect dépendant générique (réutilisé par new.php + search.php) ─────── */
/**
 * Charge les options d'un <select> cible via l'endpoint AJAX FIE.
 * @param {string}   targetId   — id du select à remplir
 * @param {string}   type       — 'communes'|'zones'|'collines'|'etablissements'
 * @param {Object}   params     — paramètres de filtrage (province, commune, zone…)
 * @param {string}   [emptyLabel]
 */
window.fieLoadSelect = async function fieLoadSelect(targetId, type, params, emptyLabel) {
    const sel = document.getElementById(targetId);
    if (!sel) return;

    sel.disabled = true;
    sel.innerHTML = '<option value="">Chargement…</option>';
    sel.classList.add('fie-select--loading');

    try {
        const data = await getJSON(FIE_BASE_URL + '/inscription/ajax/' + type, params);
        sel.innerHTML = '';
        const opt0 = new Option(emptyLabel || '— Choisir —', '');
        sel.appendChild(opt0);
        (data.items || []).forEach(function (item) {
            sel.appendChild(new Option(item.libelle, item.code));
        });
    } catch (err) {
        sel.innerHTML = '<option value="">Erreur de chargement</option>';
        console.error('fieLoadSelect error', type, err);
    } finally {
        sel.disabled = false;
        sel.classList.remove('fie-select--loading');
    }
};
