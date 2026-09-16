#!/usr/bin/env python3
# fix_value_attributes.py
# Session 25 — Fix VALUE generation bug in frame_mobile.class.php
# Root cause: all radio/checkbox VALUE attributes contain the full field ID string
#             instead of the numeric code. This causes Flutter POST to send
#             ELECTRICITE_0=ELECTRICITE_0_1 instead of ELECTRICITE_0=1.
# @auteur kailounasser@gmail.com - Abdoul Nasser Kailou

import re

FILE = '/home/user/webapp/StatEduc_burundi/server-side/classes/affichage/frame_mobile.class.php'

with open(FILE, 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

original = content
changes = []

# ============================================================
# HELPER: track changes
# ============================================================
def replace_once(content, old, new, label):
    if old not in content:
        print(f"  [WARN] Not found: {label}")
        return content
    count = content.count(old)
    if count > 1:
        print(f"  [WARN] Found {count} occurrences for: {label} — will replace ALL")
    result = content.replace(old, new)
    changes.append((label, count))
    print(f"  [OK] {count}x replaced: {label}")
    return result

# ============================================================
# FIX 1 — formulaire_concat_zone_html() — TYPE booleen
# Lines 1395, 1399
# VALUE='CHAMP_0_1' → VALUE='1'
# VALUE='CHAMP_0_0' → VALUE='0'
# ============================================================

# Line 1395 — booleen OUI (value=1)
old = (
    "$html.= \"\".' ID=\\''. $element['CHAMP_PERE'].\"_0_1\" .'\\' '."
    "\"VALUE='\".$element['CHAMP_PERE'].\"_0_1\".\"' data-mini='true'>\\n\";"
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE booleen OUI\n"
    "\t\t\t\t\t\t$html.= \"\".' ID=\\''. $element['CHAMP_PERE'].\"_0_1\" .'\\' '."
    "\"VALUE='1' data-mini='true'>\\n\";"
)
content = replace_once(content, old, new, "formulaire_concat_zone_html booleen VALUE OUI")

# Line 1399 — booleen NON (value=0)
old = (
    "$html.= \"\".' ID=\\''. $element['CHAMP_PERE'].\"_0_0\" .'\\' '."
    "\"VALUE='\".$element['CHAMP_PERE'].\"_0_0\".\"' data-mini='true'>\\n\";"
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE booleen NON\n"
    "\t\t\t\t\t\t$html.= \"\".' ID=\\''. $element['CHAMP_PERE'].\"_0_0\" .'\\' '."
    "\"VALUE='0' data-mini='true'>\\n\";"
)
content = replace_once(content, old, new, "formulaire_concat_zone_html booleen VALUE NON")

# ============================================================
# FIX 2 — formulaire_concat_zone_html() — TYPE checkbox
# Line 1406
# VALUE='CHAMP_0' → VALUE='1'
# ============================================================
old = (
    "$html.= \"\".' ID=\\''. $element['CHAMP_PERE'].\"_0\" .'\\' '."
    "\"VALUE='\".$element['CHAMP_PERE'].\"_0\".\"' data-mini='true'>\\n\";"
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE checkbox\n"
    "\t\t\t\t\t\t$html.= \"\".' ID=\\''. $element['CHAMP_PERE'].\"_0\" .'\\' '."
    "\"VALUE='1' data-mini='true'>\\n\";"
)
content = replace_once(content, old, new, "formulaire_concat_zone_html checkbox VALUE")

# ============================================================
# FIX 3 — formulaire_concat_zone_html() — TYPE combo
# Line 1445: OPTION VALUE=CHAMP_0_N → VALUE='N'
# ============================================================
old = (
    "$html \t.= \"\\t\\t\\t<OPTION VALUE=\".$element['CHAMP_PERE'].\"_0_\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\">\"; "
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE combo option\n"
    "\t\t\t\t\t\t\t$html \t.= \"\\t\\t\\t<OPTION VALUE='\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"'>\"; "
)
content = replace_once(content, old, new, "formulaire_concat_zone_html combo OPTION VALUE")

# ============================================================
# FIX 4 — get_cell_matrice() — TYPE booleen
# Lines 1498, 1502
# VALUE='CHAMP_L_D_1' → VALUE='1'
# VALUE='CHAMP_L_D_0' → VALUE='0'
# ============================================================
old = (
    "$html.= \"VALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"_\".$code_dims.\"_1' "
    "data-mini='true' tabindex='\".$GLOBALS['cell_mat_index'].\"'>\\n\";"
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE get_cell_matrice booleen OUI\n"
    "\t\t\t\t\t\t$html.= \"VALUE='1' "
    "data-mini='true' tabindex='\".$GLOBALS['cell_mat_index'].\"'>\\n\";"
)
content = replace_once(content, old, new, "get_cell_matrice booleen VALUE OUI")

old = (
    "$html.= \"VALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"_\".$code_dims.\"_0' "
    "data-mini='true' tabindex='\".$GLOBALS['cell_mat_index'].\"'>\\n\";"
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE get_cell_matrice booleen NON\n"
    "\t\t\t\t\t\t$html.= \"VALUE='0' "
    "data-mini='true' tabindex='\".$GLOBALS['cell_mat_index'].\"'>\\n\";"
)
content = replace_once(content, old, new, "get_cell_matrice booleen VALUE NON")

# ============================================================
# FIX 5 — get_cell_matrice() — TYPE checkbox
# Line 1509: VALUE='CHAMP_L_D' → VALUE='1'
# ============================================================
old = (
    "$html.= \"VALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"_\".$code_dims.\"' "
    "data-mini='true' tabindex='\".$GLOBALS['cell_mat_index'].\"'>\\n\";"
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE get_cell_matrice checkbox\n"
    "\t\t\t\t\t\t$html.= \"VALUE='1' "
    "data-mini='true' tabindex='\".$GLOBALS['cell_mat_index'].\"'>\\n\";"
)
content = replace_once(content, old, new, "get_cell_matrice checkbox VALUE")

# ============================================================
# FIX 6 — get_cell_matrice() — TYPE combo OPTION
# Line 1526: VALUE='CHAMP_L_D_N' → VALUE='N'
# ============================================================
old = (
    "$html \t.= \"\\t\\t\\t<OPTION VALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"_\".$code_dims.\"_\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"'>\"; "
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE get_cell_matrice combo OPTION\n"
    "\t\t\t\t\t\t\t\t$html \t.= \"\\t\\t\\t<OPTION VALUE='\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"'>\"; "
)
content = replace_once(content, old, new, "get_cell_matrice combo OPTION VALUE")

# ============================================================
# FIX 7 — get_cell_matrice() — TYPE liste_radio
# Line 1543: VALUE='CHAMP_L_D_N' → VALUE='N'
# ============================================================
old = (
    "\t\t\t\t\t\t\t\t\t\t\tVALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"_\".$code_dims.\"_\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true' "
    "tabindex='\".$GLOBALS['cell_mat_index'].\"'></td>\\n\"; "
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE get_cell_matrice liste_radio\n"
    "\t\t\t\t\t\t\t\t\t\t\tVALUE='\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true' "
    "tabindex='\".$GLOBALS['cell_mat_index'].\"'></td>\\n\"; "
)
content = replace_once(content, old, new, "get_cell_matrice liste_radio VALUE")

# ============================================================
# FIX 8 — generer_frame_formulaire — nomenclature radio
# Line 2709: VALUE='CHAMP_0_N' → VALUE='N'
# ============================================================
old = (
    "$html.= \"\".' ID=\\''. $element['CHAMP_PERE'].\"_0_\".$liste_choix[$ordre][$this->get_champ_extract($element['CHAMP_PERE'])] .'\\' '."
    "\"VALUE='\".$element['CHAMP_PERE'].\"_0_\".$liste_choix[$ordre][$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'>\";"
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE generer_frame_formulaire nomenclature radio\n"
    "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t$html.= \"\".' ID=\\''. $element['CHAMP_PERE'].\"_0_\".$liste_choix[$ordre][$this->get_champ_extract($element['CHAMP_PERE'])] .'\\' '."
    "\"VALUE='\".$liste_choix[$ordre][$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'>\";"
)
content = replace_once(content, old, new, "generer_frame_formulaire nomenclature radio VALUE")

# ============================================================
# FIX 9 — generer_frame_grille — TYPE liste_radio/booleen
# Line 3556 (inline radio in TD): 
# value='".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[...]
# → VALUE='N'
# ============================================================
old = (
    "\"' name='\".$element['CHAMP_PERE'].\"_\".$j.\"' type='radio' value='\"."
    "$element['CHAMP_PERE'].\"_\".$j.\"_\".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'>\";"
)
new = (
    "\"' name='\".$element['CHAMP_PERE'].\"_\".$j.\"' type='radio' value='\"."
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE grille liste_radio inline\n"
    "\t\t\t\t\t\t\t\t\t\t\t\t\t$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'>\";"
)
# This one is complex inline — let's use a different approach
# Read the actual line first
pass

# ============================================================
# FIX 9 — generer_frame_grille — TYPE liste_radio/booleen — line 3556
# The full matching string for this radio value
# ============================================================

# Let me search for the exact text in the file
idx = content.find("value='\".$element['CHAMP_PERE'].\"_\".$j.\"_\".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'>\"")
print(f"\nSearching grille radio line 3556 at char {idx}")

# Exact match for the grille inline radio (line 3556 area)
old = (
    "\"' name='\".$element['CHAMP_PERE'].\"_\".$j.\"' type='radio' "
    "value='\".$element['CHAMP_PERE'].\"_\".$j.\"_\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'>\";"
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE grille radio inline\n"
    "\t\t\t\t\t\t\t\t\t\t\t\t\"' name='\".$element['CHAMP_PERE'].\"_\".$j.\"' type='radio' "
    "value='\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'>\";"
)
content = replace_once(content, old, new, "generer_frame_grille radio VALUE inline")

# ============================================================
# FIX 10 — generer_frame_grille — TYPE liste_checkbox
# Line 3565: VALUE='CHAMP_J_N' → VALUE='N'
# ============================================================
old = (
    "$html       .= \" VALUE='\".$element['CHAMP_PERE'].\"_\".$j.\"_\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'\"; "
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE grille liste_checkbox\n"
    "\t\t\t\t\t\t\t\t$html       .= \" VALUE='\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'\"; "
)
content = replace_once(content, old, new, "generer_frame_grille liste_checkbox VALUE")

# ============================================================
# FIX 11 — generer_frame_grille — TYPE combo OPTION
# Line 3600: VALUE='CHAMP_J_N' → VALUE='N'
# ============================================================
old = (
    "$html \t.= \"\\t\\t\\t<OPTION VALUE='\".$element['CHAMP_PERE'].\"_\".$j.\"_\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"'>\"; "
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE grille combo OPTION\n"
    "\t\t\t\t\t\t\t\t\t\t$html \t.= \"\\t\\t\\t<OPTION VALUE='\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"'>\"; "
)
content = replace_once(content, old, new, "generer_frame_grille combo OPTION VALUE")

# ============================================================
# FIX 12 — generer_frame_grille — TYPE checkbox (line 3620)
# VALUE='CHAMP_J' → VALUE='1'
# ============================================================
old = (
    "$html \t        .= \" VALUE='\".$element['CHAMP_PERE'].\"_\".$j.\"' data-mini='true'\"; "
)
new = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE grille checkbox\n"
    "\t\t\t\t\t\t\t\t$html \t        .= \" VALUE='1' data-mini='true'\"; "
)
content = replace_once(content, old, new, "generer_frame_grille checkbox VALUE")

# ============================================================
# FIX 13 — generer_frame_grille_eff_1 — TYPE liste_radio (line 4340)
# VALUE='CHAMP_ligne_N' → VALUE='N'
# ============================================================
old = (
    "$html       .= \" VALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"_\"."
    "$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'\"; \n"
    "\t\t\t\t\t\t\t\t\t$html       .= \"\".$element['ATTRIB_OBJET'].\"</TD>\\n\";\n"
    "\t\t\t\t\t\t\t}\n"
    "\t\t\t\t\t\t\t$html \t\t\t\t\t\t.= \"\\t\".'</TR>'.\"\\n\";\t\t\n"
    "\t\t\t\t\t\t\t$pass ++;\n"
    "\t\t\t\t\t\t}\n"
    "\t\t\t\t\t}"
)
# This is too broad — let's use line-specific approach based on context

# For grille_eff_1 liste_radio (line 4340), the VALUE is:
# $html .= " VALUE='".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[...]."' data-mini='true'";
# We need to be careful about multiple occurrences — let's count first
print(f"\nCounting occurrences for grille_eff_1 patterns...")
pat_eff1_radio = "\" VALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"_\".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'\""
count_eff1 = content.count(pat_eff1_radio)
print(f"  grille_eff_1/eff_2/eff_3/mat_grille radio pattern: {count_eff1} occurrences")

# There are multiple methods with the same pattern — we must fix ALL of them at once
old = pat_eff1_radio
new = "\" VALUE='\".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'\""
# This is the generic fix — VALUE='N' instead of VALUE='CHAMP_ligne_N'
# Let's do a replace_all
if count_eff1 > 0:
    content = content.replace(old, new)
    changes.append((f"generer_frame_grille_eff* radio VALUE (all {count_eff1} occurrences)", count_eff1))
    print(f"  [OK] {count_eff1}x replaced: generer_frame_grille_eff* radio VALUE")
else:
    print("  [WARN] Pattern not found: generer_frame_grille_eff* radio VALUE")

# ============================================================
# FIX 14 — generer_frame_grille_eff_1 — TYPE checkbox (line 4661)
# Also applies to eff_1_fix_col (5518), eff_3 (8156), mat_grille (8761)
# VALUE='CHAMP_ligne' → VALUE='1'
# ============================================================
pat_eff1_checkbox = "\" VALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"' data-mini='true'\""
count_eff1_cb = content.count(pat_eff1_checkbox)
print(f"  grille_eff_* checkbox pattern: {count_eff1_cb} occurrences")
if count_eff1_cb > 0:
    content = content.replace(pat_eff1_checkbox, "\" VALUE='1' data-mini='true'\"")
    changes.append((f"generer_frame_grille_eff* checkbox VALUE (all {count_eff1_cb} occurrences)", count_eff1_cb))
    print(f"  [OK] {count_eff1_cb}x replaced: generer_frame_grille_eff* checkbox VALUE")
else:
    print("  [WARN] Pattern not found: grille_eff checkbox VALUE")

# ============================================================
# FIX 15 — generer_frame_grille_eff_2 — mat_nomenc_col radio (line 6232)
# VALUE='CHAMP_0_colNomenc_N' → VALUE='N'
# Also: eff_3 (7194)
# ============================================================
pat_mat_col = "\" VALUE='\".$dico[$i_elem]['CHAMP_PERE'].\"_0_\".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].\"_\".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])].\"' data-mini='true'\""
count_mat_col = content.count(pat_mat_col)
print(f"  grille_eff_2/3 mat_nomenc_col radio pattern: {count_mat_col} occurrences")
if count_mat_col > 0:
    content = content.replace(pat_mat_col, "\" VALUE='\".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])].\"' data-mini='true'\"")
    changes.append((f"grille_eff_2/3 mat_nomenc_col radio VALUE (all {count_mat_col})", count_mat_col))
    print(f"  [OK] {count_mat_col}x replaced: grille_eff_2/3 mat_nomenc_col radio VALUE")
else:
    print("  [WARN] Not found: grille_eff_2/3 mat_nomenc_col radio VALUE")

# ============================================================
# FIX 16 — generer_frame_grille_eff_2 — inline liste_radio in TD (line 6244)
# VALUE='CHAMP_ligne_D_N' → VALUE='N'
# ============================================================
pat_eff2_inline = "VALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"_\".$code_dims.\"_\".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'></td>\\n\""
count_eff2_inline = content.count(pat_eff2_inline)
print(f"  grille_eff_2 inline liste_radio pattern: {count_eff2_inline} occurrences")
if count_eff2_inline > 0:
    content = content.replace(pat_eff2_inline, "VALUE='\".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].\"' data-mini='true'></td>\\n\"")
    changes.append((f"grille_eff_2 inline liste_radio VALUE (all {count_eff2_inline})", count_eff2_inline))
    print(f"  [OK] {count_eff2_inline}x replaced: grille_eff_2 inline liste_radio VALUE")
else:
    print("  [WARN] Not found: grille_eff_2 inline liste_radio VALUE")

# ============================================================
# FIX 17 — generer_frame_grille_eff_2 — dico[$i_elem] radio checkbox (lines 6588, 7495)
# VALUE='CHAMP_ligne_N' → VALUE='N'
# ============================================================
pat_dico_ielem = "\" VALUE='\".$dico[$i_elem]['CHAMP_PERE'].\"_\".$ligne.\"_\".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])].\"' data-mini='true'\""
count_dico = content.count(pat_dico_ielem)
print(f"  grille_eff_2/3 dico[$i_elem] radio pattern: {count_dico} occurrences")
if count_dico > 0:
    content = content.replace(pat_dico_ielem, "\" VALUE='\".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])].\"' data-mini='true'\"")
    changes.append((f"grille_eff_2/3 dico[$i_elem] radio VALUE (all {count_dico})", count_dico))
    print(f"  [OK] {count_dico}x replaced: grille_eff_2/3 dico[$i_elem] radio VALUE")
else:
    print("  [WARN] Not found: grille_eff_2/3 dico[$i_elem] radio VALUE")

# ============================================================
# FIX 18 — generer_frame_grille_eff_2 — dico[$i_elem] booleen (lines 6608, 7516)
# VALUE='CHAMP_0_colNomenc' → VALUE=colNomenc code
# These are booleen in mat context: VALUE = mat_nomenc_col code
# ============================================================
pat_dico_bool = "\" VALUE='\".$dico[$i_elem]['CHAMP_PERE'].\"_0_\".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].\"' data-mini='true'\""
count_dico_bool = content.count(pat_dico_bool)
print(f"  grille_eff_2/3 dico[$i_elem] booleen pattern: {count_dico_bool} occurrences")
if count_dico_bool > 0:
    content = content.replace(pat_dico_bool, "\" VALUE='\".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].\"' data-mini='true'\"")
    changes.append((f"grille_eff_2/3 dico[$i_elem] booleen VALUE (all {count_dico_bool})", count_dico_bool))
    print(f"  [OK] {count_dico_bool}x replaced: grille_eff_2/3 dico[$i_elem] booleen VALUE")
else:
    print("  [WARN] Not found: grille_eff_2/3 dico[$i_elem] booleen VALUE")

# ============================================================
# FIX 19 — generer_frame_grille_eff_3 — radio (line 7843)
# Also mat_grille (8741) — same pattern as eff_1 but $element not $dico[$i_elem]
# Already covered by FIX 13 above if same pattern
# ============================================================

# ============================================================
# FIX 20 — generer_frame_mat_grille — radio (line 9232)
# VALUE='CHAMP_ligne_N' → already handled by FIX 13
# ============================================================

# ============================================================
# FIX 21 — generer_frame_mat_grille — checkbox (line 9207)
# VALUE='CHAMP_ligne' → VALUE='1'
# Check if already handled
# ============================================================
pat_mat_grille_cb = "\" VALUE='\".$element['CHAMP_PERE'].\"_\".$ligne.\"' data-mini='true'\""
count_mg_cb = content.count(pat_mat_grille_cb)
print(f"  mat_grille checkbox remaining: {count_mg_cb} occurrences (should be 0 if already fixed)")

# ============================================================
# FIX 22 — generer_frame_matrice_2D — TYPE checkbox (lines 743, 759)
# VALUE='MESURE1_L_I' → VALUE='1' (for checkbox in 2D matrix)
# ============================================================
old_m2d_1 = (
    "$html\t\t.= \"VALUE='\".$this->dico[0][MESURE1].'_'.$Ligne.\"_\".$i.\"' data-mini='true' \";"
)
new_m2d_1 = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE matrice_2D MESURE1 checkbox\n"
    "\t\t\t\t\t$html\t\t.= \"VALUE='1' data-mini='true' \";"
)
content = replace_once(content, old_m2d_1, new_m2d_1, "generer_frame_matrice_2D MESURE1 checkbox VALUE")

old_m2d_2 = (
    "$html\t\t.= \"VALUE='\".$this->dico[0][MESURE2].'_'.$Ligne.\"_\".$i.\"' data-mini='true' \";"
)
new_m2d_2 = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE matrice_2D MESURE2 checkbox\n"
    "\t\t\t\t\t$html\t\t.= \"VALUE='1' data-mini='true' \";"
)
content = replace_once(content, old_m2d_2, new_m2d_2, "generer_frame_matrice_2D MESURE2 checkbox VALUE")

# ============================================================
# FIX 23 — generer_frame_matrice_1D — booleen/radio (lines 1020, 1044, 1151, 1204, 1222)
# VALUE='MESURE1_ligne' → VALUE='1' (for booleen OUI in 1D matrix)
# VALUE='MESURE2_ligne' → VALUE='1' (for booleen OUI MESURE2)
# VALUE='MESURE1_col' → VALUE='1' (for booleen in column traversal)
# ============================================================
old_m1d_1 = (
    "$html\t\t.= \"VALUE='\".$this->dico[0][MESURE1].'_'.$ligne.\"' data-mini='true'  \";"
)
new_m1d_1 = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE matrice_1D MESURE1 radio\n"
    "\t\t\t\t\t\t\t\t\t\t\t$html\t\t.= \"VALUE='1' data-mini='true'  \";"
)
content = replace_once(content, old_m1d_1, new_m1d_1, "generer_frame_matrice_1D MESURE1 radio VALUE (ligne)")

old_m1d_2 = (
    "$html\t\t.= \"VALUE='\".$this->dico[0][MESURE2].'_'.$ligne.\"' data-mini='true' \";"
)
new_m1d_2 = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE matrice_1D MESURE2 radio\n"
    "\t\t\t\t\t\t\t\t\t\t\t\t\t$html\t\t.= \"VALUE='1' data-mini='true' \";"
)
content = replace_once(content, old_m1d_2, new_m1d_2, "generer_frame_matrice_1D MESURE2 radio VALUE (ligne)")

old_m1d_3 = (
    "$html\t\t.= \"VALUE='\".$this->dico[0][MESURE1].'_'.$Ligne.\"' data-mini='true' \";"
)
new_m1d_3 = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE matrice_1D MESURE1 radio (Ligne)\n"
    "\t\t\t\t\t$html\t\t.= \"VALUE='1' data-mini='true' \";"
)
content = replace_once(content, old_m1d_3, new_m1d_3, "generer_frame_matrice_1D MESURE1 radio VALUE (Ligne)")

old_m1d_4 = (
    "$html\t\t.= \"VALUE='\".$this->dico[0][MESURE1].'_'.$colonne.\"' data-mini='true' \";"
)
new_m1d_4 = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE matrice_1D MESURE1 radio (colonne)\n"
    "\t\t\t\t\t$html\t\t.= \"VALUE='1' data-mini='true' \";"
)
content = replace_once(content, old_m1d_4, new_m1d_4, "generer_frame_matrice_1D MESURE1 radio VALUE (colonne)")

old_m1d_5 = (
    "$html\t\t.= \"VALUE='\".$this->dico[0][MESURE2].'_'.$colonne.\"' data-mini='true' \";"
)
new_m1d_5 = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE matrice_1D MESURE2 radio (colonne)\n"
    "\t\t\t\t\t$html\t\t.= \"VALUE='1' data-mini='true' \";"
)
content = replace_once(content, old_m1d_5, new_m1d_5, "generer_frame_matrice_1D MESURE2 radio VALUE (colonne)")

# ============================================================
# Add @auteur comment block at the top of the critical functions
# ============================================================

# Add auteur tag to formulaire_concat_zone_html
old_func_tag = "function formulaire_concat_zone_html($element,$langue,$id_systeme){"
new_func_tag = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE attributes (session 25)\n"
    "\t\tfunction formulaire_concat_zone_html($element,$langue,$id_systeme){"
)
content = replace_once(content, old_func_tag, new_func_tag, "formulaire_concat_zone_html @auteur tag")

old_func_tag2 = "function get_cell_matrice($ligne ,$code_dims, $element, $fonc_total, $langue, $id_systeme){"
new_func_tag2 = (
    "// @auteur kailounasser@gmail.com - Abdoul Nasser Kailou — fix VALUE attributes (session 25)\n"
    "\t\tfunction get_cell_matrice($ligne ,$code_dims, $element, $fonc_total, $langue, $id_systeme){"
)
content = replace_once(content, old_func_tag2, new_func_tag2, "get_cell_matrice @auteur tag")

# ============================================================
# Final verification — count remaining broken patterns
# ============================================================
print("\n=== POST-FIX VERIFICATION ===")

# Check for remaining broken VALUE patterns (field ID as value)
remaining_broken = re.findall(
    r"VALUE='\"\.\$(?:element|dico)\[(?:'CHAMP_PERE'|\$i_elem)\]\['CHAMP_PERE'\]\.\""
    r"_\"[^']*data-mini",
    content
)
print(f"Remaining 'CHAMP_PERE string as VALUE' patterns: {len(remaining_broken)}")
for r in remaining_broken:
    print(f"  {r[:120]}")

# ============================================================
# Write the fixed file
# ============================================================
if content != original:
    with open(FILE, 'w', encoding='utf-8', errors='replace') as f:
        f.write(content)
    print(f"\n✅ File written successfully with {len(changes)} fix groups applied")
    print("\nChanges summary:")
    for label, count in changes:
        print(f"  [{count}x] {label}")
else:
    print("\n⚠️  No changes made — all patterns may already be correct or not found")
