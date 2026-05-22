// JavaScript Document
/////////////////////////////////////////////////////////////// FONCTION CALCUL TOTAL POUR THEME MATRICE 2 DIMENSIONS
function do_concat_dims_ThemeMat2D(nb_dims, tab_dim){
	var tab_concat = new Array();
	for (var i_dim = 0; i_dim < nb_dims; i_dim++){
		if(i_dim == 0){
			tab_concat = tab_dim[i_dim];
		}else{
			var tmp_tab = new Array;
			var k = 0 ;
			for (var i = 0; i < tab_concat.length; i++){
				for (var j = 0; j < tab_dim[i_dim].length; j++){
					tmp_tab[k] = tab_concat[i]+'_'+tab_dim[i_dim][j] ;
					k++;
				}
			}
			tab_concat = tmp_tab ;
		}
	}
	return tab_concat;
}

function calcul_Total_ThemeMat2D(form, champ_total, tab_mes, tab_ligne, tab_col, tot_all, li){

	if( (form != '') && (champ_total != '') && (tab_mes.length > 0) && ( (tab_ligne.length > 0) || (tab_col.length > 0) ) ){
		//alert(' /mes= '+tab_mes.length+' /li= '+tab_ligne.length+' / col='+tab_col.length );
		
		var total 			= 0;
		var nb_dim 			= 0;
		var tab_concat_dims	= new Array();
		var tab_dims		= new Array();
		var affiche_meme_si_zero = false;
		
		if(tab_ligne.length > 0){
			tab_dims[nb_dim] = new Array();
			for (var i = 0; i < tab_ligne.length; i++){
				tab_dims[nb_dim][i] = tab_ligne[i];
				//alert(tab_dims[nb_dim][i]);
			}
			nb_dim++;
		}
		
		if(tab_col.length > 0){
			tab_dims[nb_dim] = new Array();
			for (var i = 0; i < tab_col.length; i++){
				tab_dims[nb_dim][i] = tab_col[i];
			}
			nb_dim++;
		}
		
		tab_concat_dims = do_concat_dims_ThemeMat2D(nb_dim, tab_dims);
		//alert(tab_concat_dims.length);
		//alert(tab_mes.length);
		if((tot_all=='TOT_ALL_MES_LIGNE') || (tot_all=='TOT_ALL_MES') || (tot_all=='TOT_ALL_MES_COLONNE')){
			//alert(tot_all);
			var text = expression.toString() ;
			var resultat='';
			var maReg;
			var text_rech='';
			if(tot_all=='TOT_ALL_MES_LIGNE'){
				for (var i = 0; i < tab_mes.length; i++){
					text_rech=tab_mes[i].toString();
					maReg = new RegExp(text_rech) ;
					if((eval('document.'+form+'.'+tot_tab_mes[li][i]+'.value') != '') && ( text.search( maReg ) != -1 ))
						affiche_meme_si_zero=true;
					if(eval('document.'+form+'.'+tot_tab_mes[li][i]+'.value') == '')
						text = text.replace( maReg, 'parseFloat(0)' ) ;
					else
						text = text.replace( maReg, 'parseFloat('+form+'.'+tot_tab_mes[li][i]+'.value)' ) ;
				}
			}else if(tot_all=='TOT_ALL_MES'){
					for (var i = 0; i < tab_mes.length; i++){
						text_rech=tab_mes[i].toString();
						maReg = new RegExp(text_rech) ;
						if((eval('document.'+form+'.'+tot_tab_col[i]+'.value') != '') && ( text.search( maReg ) != -1 ))
							affiche_meme_si_zero=true;
						if(eval('document.'+form+'.'+tot_tab_col[i]+'.value') == '')
							text = text.replace( maReg, 'parseFloat(0)' ) ;
						else
							text = text.replace( maReg, 'parseFloat('+form+'.'+tot_tab_col[i]+'.value)' ) ;
					}
			}else if(tot_all=='TOT_ALL_MES_COLONNE'){
				for (var i = 0; i < tab_mes.length; i++){
					text_rech=tab_mes[i].toString();
					maReg = new RegExp(text_rech) ;
					if((eval('document.'+form+'.'+tot_tab_col[i]+'_'+li+'.value') != '') && ( text.search( maReg ) != -1 ))
						affiche_meme_si_zero=true;
					if(eval('document.'+form+'.'+tot_tab_col[i]+'_'+li+'.value') == '')
						text = text.replace( maReg, 'parseFloat(0)' ) ;
					else
						text = text.replace( maReg, 'parseFloat('+form+'.'+tot_tab_col[i]+'_'+li+'.value)' ) ;
				}
			}

			if(eval(text))
				resultat=eval(text);
			else
				resultat = 0 ;
			eval ('total = parseFloat(resultat);');
		}else{
			for (var i = 0; i < tab_mes.length; i++){
				if(tab_concat_dims.length > 0){
					for (var j = 0; j < tab_concat_dims.length; j++){
						//alert(tab_concat_dims[j]);
						//alert('document.'+form+'.'+tab_mes[i]+'_'+tab_concat_dims[j]);
						if(eval ('document.'+form+'.'+tab_mes[i]+'_'+tab_concat_dims[j])){
								if(eval ('parseFloat(document.'+form+'.'+tab_mes[i]+'_'+tab_concat_dims[j]+'.value)')){
									eval ('total += parseFloat(document.'+form+'.'+tab_mes[i]+'_'+tab_concat_dims[j]+'.value);');
								}
						}
					}
				}
			}
		}
		if(eval('document.'+form+'.'+champ_total)){
			//alert(total);
			total=Math.round(total*100)/100;
			if((total == 0) && (!affiche_meme_si_zero)){
				total = "''" ;
			}
			eval('document.'+form+'.'+champ_total+'.value='+total+';');
			
		}/**/

	}
}

function set_TOTAL_ThemeMat2D(){
	for (var li = 0; li < tab_ligne.length; li++){
		for (var i_mes = 0; i_mes < tab_mes.length; i_mes++){
			calcul_Total_ThemeMat2D('form1', eval('Array("TOT_'+tab_mes[i_mes]+'_LIGNE_'+li+'")'), eval('Array("'+tab_mes[i_mes]+'")'), eval('Array("'+tab_ligne[li]+'")'), tab_col);
		}
		if(expression != ""){
			calcul_Total_ThemeMat2D('form1', eval('Array("TOT_ALL_MES_LIGNE_'+li+'")'), tab_mes, eval('Array("'+tab_ligne[li]+'")'), tab_col, 'TOT_ALL_MES_LIGNE', li);
		}else{
			calcul_Total_ThemeMat2D('form1', eval('Array("TOT_ALL_MES_LIGNE_'+li+'")'), tab_mes, eval('Array("'+tab_ligne[li]+'")'), tab_col);
		}
	}
	//eval('Array("'++'")')
	for (var col = 0; col < tab_col.length; col++){
		for (var i_mes = 0; i_mes < tab_mes.length; i_mes++){
			calcul_Total_ThemeMat2D('form1', eval('Array("TOT_'+tab_mes[i_mes]+'_COLONNE_'+col+'")'), eval('Array("'+tab_mes[i_mes]+'")'), tab_ligne, eval('Array("'+tab_col[col]+'")') );
		}
		if(expression != ""){
			calcul_Total_ThemeMat2D('form1', eval('Array("TOT_ALL_MES_COLONNE_'+col+'")'), tab_mes, tab_ligne, eval('Array("'+tab_col[col]+'")'), 'TOT_ALL_MES_COLONNE', col);
		}else{
			calcul_Total_ThemeMat2D('form1', eval('Array("TOT_ALL_MES_COLONNE_'+col+'")'), tab_mes, tab_ligne, eval('Array("'+tab_col[col]+'")'));
		}
	}

	if(tab_mes.length > 0){
		for (var i_mes = 0; i_mes < tab_mes.length; i_mes++){
			calcul_Total_ThemeMat2D('form1', eval('Array("TOT_'+tab_mes[i_mes]+'_COLONNE'+'")'), eval('Array("'+tab_mes[i_mes]+'")'), tab_ligne, tab_col);
		}
	}
	if(expression != ""){
		calcul_Total_ThemeMat2D('form1', 'TOT_ALL_MES', tab_mes, tab_ligne, tab_col, 'TOT_ALL_MES');
	}else{
		calcul_Total_ThemeMat2D('form1', 'TOT_ALL_MES', tab_mes, tab_ligne, tab_col);
	}
}

////////////////////////////////////////////////////////////// FIN FONCTION CALCUL TOTAL POUR THEME MATRICE 2 DIMENSIONS



////////////////////////////////////////////////////////////// FONCTION CALCUL TOTAL POUR  MATRICE  DANS FORMULAIRE

function do_concat_dims_MatFrml(nb_dims, tab_dim){
	var tab_concat = new Array();
	for (var i_dim = 0; i_dim < nb_dims; i_dim++){
		if(i_dim == 0){
			tab_concat = tab_dim[i_dim];
		}else{
			var tmp_tab = new Array();
			var k = 0 ;
			for (var i = 0; i < tab_concat.length; i++){
				for (var j = 0; j < tab_dim[i_dim].length; j++){
					tmp_tab[k] = tab_concat[i]+'_'+tab_dim[i_dim][j] ;
					k++;
				}
			}
			tab_concat = tmp_tab ;
		}
	}
	return tab_concat;
}

function calcul_Total_MatFrml(form, num_line, champ_total, tab_mes, tab_ligne, tab_col, tot_all, tot_zone){
	if( (form != '') && (champ_total != '') && (tab_mes.length > 0) && ( (tab_ligne.length > 0) || (tab_col.length > 0) ) ){
		//alert(' /mes= '+tab_mes.length+' /li= '+tab_ligne.length+' / col='+tab_col.length );
		var total 			= 0;
		var nb_dim 			= 0;
		var tab_concat_dims	= new Array();
		var tab_dims		= new Array();
		
		if( parseInt(num_line) >= 0 ){
			var plus_line 	= '_' + num_line ;
		}else{
			var plus_line 	= '' ;	
		}
		//alert('a'+plus_line+'b');
		if(tab_ligne.length > 0){
			tab_dims[nb_dim] = new Array();
			for (var i = 0; i < tab_ligne.length; i++){
				tab_dims[nb_dim][i] = tab_ligne[i];
				//alert(tab_dims[nb_dim][i]);
			}
			nb_dim++;
		}
		
		if(tab_col.length > 0){
			
			tab_dims[nb_dim] = new Array();
			for (var i = 0; i < tab_col.length; i++){
				tab_dims[nb_dim][i] = tab_col[i];
			}
			nb_dim++;
		}
		
		tab_concat_dims = do_concat_dims_MatFrml(nb_dim, tab_dims);
		//alert(tab_concat_dims.length);
		//alert(tab_mes);
		var pos=champ_total.indexOf('_',4);
		var zone=champ_total.substring(4,pos);
		var text = eval('expression_'+zone).toString() ;
		var resultat='';
		var maReg;
		var text_rech='';
		var affiche_meme_si_zero=false;
		if((tot_all=='ALL_MES_LIGNE') || (tot_all=='ALL_MES') || (tot_all=='TOT_ALL_MES') || (tot_all=='ALL_MES_COLONNE')){
			for (var i = 0; i < tab_mes.length; i++){
				if(tot_all=='ALL_MES_LIGNE'){
					if(tab_concat_dims.length == 1){
						//alert(tab_concat_dims.length);
						for (var j = 0; j < tab_concat_dims.length; j++){		
							//alert('document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]);
							if(eval ('document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j])){
								//alert('For Total : '+champ_total+'/ '+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]);
								//if(eval ('parseFloat(document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value)')){
										text_rech=tab_mes[i].toString();
										//alert(text_rech);
										//alert(text);
										maReg = new RegExp(text_rech) ;
										if((eval('document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value') != '') && ( text.search( maReg ) != -1 ))
											affiche_meme_si_zero=true;
										if(eval('document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value') == '')
											text = text.replace( maReg, 'parseFloat(0)' ) ;
										else
											text = text.replace( maReg, 'parseFloat('+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value)' ) ;
										
								//}
							}
						}
					}else if(tab_concat_dims.length > 1){
						if(eval ('document.'+form+'.'+eval('tot_mes_ligne_'+zone)[tot_zone][i])){// A noter tot_zone=numero de la ligne dans ce cas
							//alert('For Total : '+champ_total+'/ '+tot_mes_ligne[tot_zone][i]);
							//if(eval ('parseFloat(document.'+form+'.'+tot_mes_ligne[tot_zone][i]+'.value)')){
									text_rech=tab_mes[i].toString();
									//alert(text_rech);
									//alert(text);
									maReg = new RegExp(text_rech) ;
									if((eval('document.'+form+'.'+eval('tot_mes_ligne_'+zone)[tot_zone][i]+'.value') != '') && ( text.search( maReg ) != -1 ))
										affiche_meme_si_zero=true;
									if(eval('document.'+form+'.'+eval('tot_mes_ligne_'+zone)[tot_zone][i]+'.value') == '')
										text = text.replace( maReg, 'parseFloat(0)' ) ;
									else
										text = text.replace( maReg, 'parseFloat('+form+'.'+eval('tot_mes_ligne_'+zone)[tot_zone][i]+'.value)' ) ;
							//}
						}
					}
				}else if(tot_all=='ALL_MES'){
					
					text_rech=tab_mes[i].toString();
					maReg = new RegExp(text_rech) ;
					
					if((tab_col.length == 0) || ((tab_col.length > 0) && (tab_ligne.length > 0))){
						if((eval('document.'+form+'.'+tot_zone+tab_mes[i]+'_COLONNE'+plus_line+'.value') != '') && ( text.search( maReg ) != -1 ))
							affiche_meme_si_zero=true;
						if(eval('document.'+form+'.'+tot_zone+tab_mes[i]+'_COLONNE'+plus_line+'.value') == '')
							text = text.replace( maReg, 'parseFloat(0)' ) ;
						else
							text = text.replace( maReg, 'parseFloat('+form+'.'+tot_zone+tab_mes[i]+'_COLONNE'+plus_line+'.value)' ) ;	
					}else{
						if((eval('document.'+form+'.'+tot_zone+tab_mes[i]+'_LIGNE'+plus_line+'.value') != '') && ( text.search( maReg ) != -1 ))
							affiche_meme_si_zero=true;
						if(eval('document.'+form+'.'+tot_zone+tab_mes[i]+'_LIGNE'+plus_line+'.value') == '')
							text = text.replace( maReg, 'parseFloat(0)' ) ;
						else
							text = text.replace( maReg, 'parseFloat('+form+'.'+tot_zone+tab_mes[i]+'_LIGNE'+plus_line+'.value)' ) ;	
					}
				}else if(tot_all=='ALL_MES_COLONNE'){
					
					if((!eval('affich_vertic_mes_'+zone)) && (tab_ligne.length > 0)){
						
						if(eval ('document.'+form+'.'+eval('tot_mes_col_'+zone)[tot_zone][i])){// A noter tot_zone=numero de la colonne dans ce cas
							//alert('For Total : '+champ_total+'/ '+tot_mes_col[tot_zone][i]);
							//if(eval ('parseFloat(document.'+form+'.'+tot_mes_col[tot_zone][i]+'.value)')){
									text_rech=tab_mes[i].toString();
									//alert(text_rech);
									//alert(text);
									maReg = new RegExp(text_rech) ;
									if((eval('document.'+form+'.'+eval('tot_mes_col_'+zone)[tot_zone][i]+'.value') != '') && ( text.search( maReg ) != -1 ))
										affiche_meme_si_zero=true;
									if(eval('document.'+form+'.'+eval('tot_mes_col_'+zone)[tot_zone][i]+'.value') == '')
										text = text.replace( maReg, 'parseFloat(0)' ) ;
									else
										text = text.replace( maReg, 'parseFloat('+form+'.'+eval('tot_mes_col_'+zone)[tot_zone][i]+'.value)' ) ;
							//}
						}
					}else{
						if((tot_zone != '') || ((eval('affich_vertic_mes_'+zone)) && (tab_col.length > 0) && (tab_ligne.length > 0))){
							//alert('ici');
							if(eval ('document.'+form+'.'+eval('tot_mes_col_'+zone)[i][tot_zone])){// A noter tot_zone=numero de la colonne dans ce cas
								//alert('For Total : '+champ_total+'/ '+tot_mes_col[i][tot_zone]);
								//if(eval ('parseFloat(document.'+form+'.'+tot_mes_col[i][tot_zone]+'.value)')){
										text_rech=tab_mes[i].toString();
										//alert(text_rech);
										//alert(text);
										maReg = new RegExp(text_rech) ;
										if((eval('document.'+form+'.'+eval('tot_mes_col_'+zone)[i][tot_zone]+'.value') != '') && ( text.search( maReg ) != -1 ))
											affiche_meme_si_zero=true;
										if(eval('document.'+form+'.'+eval('tot_mes_col_'+zone)[i][tot_zone]+'.value') == '')
											text = text.replace( maReg, 'parseFloat(0)' ) ;
										else
											text = text.replace( maReg, 'parseFloat('+form+'.'+eval('tot_mes_col_'+zone)[i][tot_zone]+'.value)' ) ;
								//}
							}
						}else{
							for (var j = 0; j < tab_concat_dims.length; j++){		
								//alert('document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]);
								if(eval ('document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j])){
									//alert('For Total : '+champ_total+'/ '+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]);
									//if(eval ('parseFloat(document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value)')){
											text_rech=tab_mes[i].toString();
											//alert(text_rech);
											//alert(text);
											maReg = new RegExp(text_rech) ;
											if((eval('document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value') != '') && ( text.search( maReg ) != -1 ))
												affiche_meme_si_zero=true;
											if(eval('document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value') == '')
												text = text.replace( maReg, 'parseFloat(0)' ) ;
											else
												text = text.replace( maReg, 'parseFloat('+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value)' ) ;
									//}
								}
							}
						}
					}
				}
			}
			//alert(text)
			if(eval(text))
				resultat=eval(text);
			else
				resultat = 0 ;
			eval ('total = parseFloat(resultat);');
		}else{
			for (var i = 0; i < tab_mes.length; i++){
				if(tab_concat_dims.length > 0){
					for (var j = 0; j < tab_concat_dims.length; j++){
						if(eval ('document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j])){
							//alert('For Total : '+champ_total+'/ '+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]);
							if(eval ('parseFloat(document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value)')){
									eval ('total += parseFloat(document.'+form+'.'+tab_mes[i]+''+plus_line+'_'+tab_concat_dims[j]+'.value);');
							}
						}
					}
				}
			}
		}
		//alert(champ_total);
		//document.write(champ_total + '<br>');
		if(eval('document.'+form+'.'+champ_total)){
			//alert('document.'+form+'.'+champ_total);
			//alert( 'TOT:' + champ_total + '=' + total);
			total=Math.round(total*100)/100;
			if((total == 0) && (!affiche_meme_si_zero)){
				total = "''" ;
			}
			eval('document.'+form+'.'+champ_total+'.value='+total+';');
		}
	}
}

function set_TOTAL_MatFrml(form, zone, num_line, tab_mes, tab_ligne, tab_col){
	
	//alert( ' form='+form +', zone='+ zone +', num_li='+ num_line +', mes='+ tab_mes.length +', tab_li='+ tab_ligne.length +', tab_col='+ tab_col.length );
	if( parseInt(num_line) >= 0 ){
		var plus_line 	= '_' + num_line ;
	}else{
		var plus_line 	= '' ;	
	}
	
	if(tab_ligne.length > 0){
		for (var li = 0; li < tab_ligne.length; li++){
			if(tab_col.length > 0){ // si 2 dims
				for (var i_mes = 0; i_mes < tab_mes.length; i_mes++){
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+tab_mes[i_mes]+'_LIGNE'+plus_line+'_'+tab_ligne[li], eval('Array("'+tab_mes[i_mes]+'")'), eval('Array("'+tab_ligne[li]+'")'), tab_col);
				}
			}
			if(tab_mes.length > 0){
				if(eval('expression_'+zone)==''){
					if(eval('affiche_sous_totaux_'+zone)){
						for(var co = 0; co < tab_col.length; co++){
							var colonne = new Array();
							colonne[0]=tab_col[co];
							calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE_'+tab_ligne[li]+'_'+tab_col[co], tab_mes, eval('Array("'+tab_ligne[li]+'")'), colonne);
						}
					}
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE'+plus_line+'_'+tab_ligne[li], tab_mes, eval('Array("'+tab_ligne[li]+'")'), tab_col);
				}else{
					if(eval('affiche_sous_totaux_'+zone)){
						for(var co = 0; co < tab_col.length; co++){
							var colonne = new Array();
							colonne[0]=tab_col[co];
							calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE_'+tab_ligne[li]+'_'+tab_col[co], tab_mes, eval('Array("'+tab_ligne[li]+'")'), colonne, 'ALL_MES_LIGNE', li);
						}
					}
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE'+plus_line+'_'+tab_ligne[li], tab_mes, eval('Array("'+tab_ligne[li]+'")'), tab_col, 'ALL_MES_LIGNE', li);
				}
			}
		}
		
		if(tab_col.length > 0){
			for (var col = 0; col < tab_col.length; col++){
				for (var i_mes = 0; i_mes < tab_mes.length; i_mes++){
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+tab_mes[i_mes]+'_COLONNE'+plus_line+'_'+tab_col[col], eval('Array("'+tab_mes[i_mes]+'")'), tab_ligne, eval('Array("'+tab_col[col]+'")') );
				}
				if(tab_mes.length > 0){
					if(eval('expression_'+zone)==''){
						if(eval('affiche_sous_totaux_'+zone)){
							for(var l = 0; l < tab_ligne.length; l++){
								var ligne = new Array();
								ligne[0]=tab_ligne[l];
								calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE_'+tab_ligne[l]+'_'+tab_col[col], tab_mes, ligne,eval('Array("'+tab_col[col]+'")') );
							}
						}
						calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE'+plus_line+'_'+tab_col[col], tab_mes, tab_ligne, eval('Array("'+tab_col[col]+'")') );
					}else{
						if(eval('affiche_sous_totaux_'+zone)){
							for(var l = 0; l < tab_ligne.length; l++){
								var ligne = new Array();
								ligne[0]=tab_ligne[l];
								calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE_'+tab_ligne[l]+'_'+tab_col[col], tab_mes, ligne,eval('Array("'+tab_col[col]+'")'), 'ALL_MES_COLONNE', col );
							}
						}
						calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE'+plus_line+'_'+tab_col[col], tab_mes, tab_ligne, eval('Array("'+tab_col[col]+'")'), 'ALL_MES_COLONNE', col );	
					}
				}
			}
			if(tab_mes.length > 0){
				for (var i_mes = 0; i_mes < tab_mes.length; i_mes++){
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+tab_mes[i_mes]+'_COLONNE'+plus_line, eval('Array("'+tab_mes[i_mes]+'")'), tab_ligne, tab_col );
				}
			}

		}else{
			for (var i_mes = 0; i_mes < tab_mes.length; i_mes++){
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+tab_mes[i_mes]+'_COLONNE'+plus_line, eval('Array("'+tab_mes[i_mes]+'")'), tab_ligne, tab_col );
			}
			if(tab_mes.length > 0){
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE'+plus_line, tab_mes, tab_ligne, tab_col );
			}					
		}
		
	}else if(tab_col.length > 0){
		for (var col = 0; col < tab_col.length; col++){
			if(tab_mes.length > 0){
				if(eval('expression_'+zone)==''){
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE'+plus_line+'_'+tab_col[col], tab_mes, tab_ligne, eval('Array("'+tab_col[col]+'")'));
				}else{
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE'+plus_line+'_'+tab_col[col], tab_mes, tab_ligne, eval('Array("'+tab_col[col]+'")'), 'ALL_MES_COLONNE', '' );
				}
			}
		}
		for (var i_mes = 0; i_mes < tab_mes.length; i_mes++){
			if(eval('expression_'+zone)==''){
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+tab_mes[i_mes]+'_LIGNE'+plus_line, eval('Array("'+tab_mes[i_mes]+'")'), tab_ligne, tab_col );
			}else{
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+tab_mes[i_mes]+'_LIGNE'+plus_line, eval('Array("'+tab_mes[i_mes]+'")'), tab_ligne, tab_col );	
			}
		}
		if(tab_mes.length > 0){
			if(eval('expression_'+zone)==''){
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE'+plus_line, tab_mes, tab_ligne, tab_col );
			}else{
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE'+plus_line, tab_mes, tab_ligne, tab_col, 'ALL_MES', 'TOT_'+zone+'_' );	
			}
		}
	}
	if(eval('expression_'+zone) != "")
		calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES'+plus_line, tab_mes, tab_ligne, tab_col, 'ALL_MES', 'TOT_'+zone+'_');
	else
		calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES'+plus_line, tab_mes, tab_ligne, tab_col);
}


function set_TOTAL_MatFrml_Champ(form, zone, num_line, champ, tab_ligne, tab_col, var_li, var_col, tab_mes){
	
	//alert( ' form='+form +', zone='+ zone +', champ='+ champ  +', num_li='+ num_line +', mes='+ tab_mes.length +', tab_li='+ tab_ligne.length +', tab_col='+ tab_col.length +' var_li='+var_li+' var_col='+var_col);
	if( parseInt(num_line) >= 0 ){
		var plus_line 	= '_' + num_line ;
	}else{
		var plus_line 	= '' ;	
	}
	var colonne = new Array();
	var ligne = new Array();
	colonne[0]=var_col;
	ligne[0]=var_li;
	if(tab_ligne.length > 0){
			
		if(tab_col.length > 0){ // si 2 dims
			
			calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+champ+'_LIGNE'+plus_line+'_'+var_li, eval('Array("'+champ+'")'), eval('Array("'+var_li+'")'), tab_col);
			calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+champ+'_COLONNE'+plus_line+'_'+var_col, eval('Array("'+champ+'")'), tab_ligne, eval('Array("'+var_col+'")') );
			
			if(eval('expression_'+zone) == ""){
				if(eval('affiche_sous_totaux_'+zone))
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE_'+var_li+'_'+var_col, tab_mes, eval('Array("'+var_li+'")'), colonne);
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE'+plus_line+'_'+var_li, tab_mes, eval('Array("'+var_li+'")'), tab_col);
			}else{
				
				if(eval('affiche_sous_totaux_'+zone))
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE_'+var_li+'_'+var_col, tab_mes, eval('Array("'+var_li+'")'), colonne, 'ALL_MES_LIGNE', var_li);
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE'+plus_line+'_'+var_li, tab_mes, eval('Array("'+var_li+'")'), tab_col, 'ALL_MES_LIGNE', var_li);
			
			}
			
			if(eval('expression_'+zone) == ""){
				if(eval('affiche_sous_totaux_'+zone))
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE_'+var_li+'_'+var_col, tab_mes, ligne, eval('Array("'+var_col+'")') );
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE'+plus_line+'_'+var_col, tab_mes, tab_ligne, eval('Array("'+var_col+'")') );
			}else{
				if(eval('affiche_sous_totaux_'+zone))
					calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE_'+var_li+'_'+var_col, tab_mes, ligne, eval('Array("'+var_col+'")'), 'ALL_MES_COLONNE', var_col );
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE'+plus_line+'_'+var_col, tab_mes, tab_ligne, eval('Array("'+var_col+'")'), 'ALL_MES_COLONNE', var_col );
			}
			
			calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+champ+'_COLONNE'+plus_line, eval('Array("TOT_'+zone+'_'+champ+'_LIGNE")'), tab_ligne, eval('Array()') );
			
			if(eval('expression_'+zone) == ""){
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES'+plus_line, eval('Array("TOT_'+zone+'_ALL_MES_LIGNE")'), tab_ligne, eval('Array()'));
			}else{
				calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES'+plus_line, tab_mes, tab_ligne, eval('Array()'), 'ALL_MES', 'TOT_'+zone+'_');
				//calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES'+plus_line, eval('Array("TOT_'+zone+'_ALL_MES_LIGNE")'), tab_ligne, eval('Array()'), 'ALL_MES', '');
			}
			
		}else{
			calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+champ+'_COLONNE'+plus_line, eval('Array("'+champ+'")'), tab_ligne, tab_col );
			
			calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_LIGNE'+plus_line+'_'+var_li, tab_mes, eval('Array("'+var_li+'")'), tab_col);
			
			calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES'+plus_line, eval('Array("TOT_'+zone+'_ALL_MES_LIGNE")'), tab_ligne, eval('Array()'));
		}
		
	}else if(tab_col.length > 0){
		
		calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES_COLONNE'+plus_line+'_'+var_col, tab_mes, tab_ligne, eval('Array("'+var_col+'")'));
		
		calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+champ+'_COLONNE'+plus_line, eval('Array("'+champ+'")'), tab_ligne, tab_col );
		
		calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_ALL_MES'+plus_line, eval('Array("TOT_'+zone+'_ALL_MES_COLONNE")'), tab_ligne, eval('Array()'));
	}
	//calcul_Total_MatFrml(form, num_line, 'TOT_'+zone+'_'+num_line+'_ALL_MES', tab_mes, tab_ligne, tab_col);
}
////////////////////////////////////////////////////////////// FIN FONCTION CALCUL TOTAL POUR  MATRICE  DANS FORMULAIRE
////////////////////////////////////////////////////////////// FONCTION CALCUL TOTAL DANS FORMULAIRE
function set_Total_ChpsFrml(form, expr){
	
	if( (form != '') && (expr != '') ){
		var text = expr.toString() ;
		var array_expr = text.split('=');
		var chp_total = array_expr[0];
		//alert(chp_total);
		var maReg;
		var text_rech='';
		var affiche_meme_si_zero=false;
		for (var i = 0; i < tab_chps.length; i++){
			if(eval ('document.'+form+'.'+tab_chps[i]+'_0')){
				text_rech=tab_chps[i].toString();
				maReg = new RegExp(text_rech) ;
				if((eval('document.'+form+'.'+tab_chps[i]+'_0'+'.value') != '') && ( text.search( maReg ) != -1 ))
					affiche_meme_si_zero=true;
				if((eval('document.'+form+'.'+tab_chps[i]+'_0'+'.value') == '') && (tab_chps[i] != chp_total))
					text = text.replace( maReg, 'parseFloat(0)' ) ;
				else if(tab_chps[i] != chp_total)
					text = text.replace( maReg, 'parseFloat('+form+'.'+tab_chps[i]+'_0'+'.value)' ) ;
				else
					text = text.replace( maReg, form+'.'+tab_chps[i]+'_0'+'.value' ) ;
			}
		}
		//alert(text);
		eval(text);
		if(eval('document.'+form+'.'+chp_total+'_0')){
			//alert('document.'+form+'.'+chp_total+'.value;');
			//alert( 'TOT:' + champ_total + '=' + total);
			var total = eval('document.'+form+'.'+chp_total+'_0.value');
			if((total == 0) && (!affiche_meme_si_zero)){
			//if((total == 0) ){
				total = "''" ;
			}
			eval('document.'+form+'.'+chp_total+'_0.value='+total+';');
		}
	}
}
////////////////////////////////////////////////////////////// FIN FONCTION CALCUL TOTAL DANS FORMULAIRE