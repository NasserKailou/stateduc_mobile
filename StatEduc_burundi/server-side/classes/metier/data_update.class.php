<?php class data_update {

		public $conn; 

		public $list_tabms_ord = array(); 
		
		public $tab_secteurs = array();
		
		public $champ_maj;
		
		public $min_val;
		
		public $max_val;
		
		public $start_val;
		
		public $tab_recoding;
		
		function __construct($settings){
			
			$this->tab_secteurs = $settings['tab_secteurs'];
			$this->champ_maj 	= $settings['champ_maj'];
			$this->min_val 		= $settings['min_val'];
			$this->max_val 		= $settings['max_val'];
			$this->start_val 	= $settings['start_val'];
			//print_r($settings) ;
			$this->run();
			
		}
		
		function est_ds_tableau($elem,$tab){
			if(is_array($tab)){
				foreach($tab as $elements){
					if( trim($elements) == trim($elem) ){
						return true;
		}	}	}	}
		
		function set_list_tabms_ord($secteur){
		 
			$req = 'SELECT DICO_THEME_SYSTEME.ID_THEME_SYSTEME, DICO_THEME.ID_TYPE_THEME, 
					DICO_THEME.ID, DICO_THEME_SYSTEME.PERE, DICO_THEME_SYSTEME.PRECEDENT
					FROM DICO_THEME_SYSTEME , DICO_THEME 
					WHERE DICO_THEME.ID = DICO_THEME_SYSTEME.ID
					AND  DICO_THEME_SYSTEME.ID_SYSTEME='.$secteur ;
		   
			$res	= $GLOBALS['conn_dico']->GetAll($req); 
	
			
			$list_thms	=   $GLOBALS['theme_manager']->tri_list($res); 
			$temp_list_tabms = 	array();
			
			if( is_array($list_thms) && count($list_thms) ){
				foreach($list_thms as $i_thm => $thm){
					
					if ($thm['ID_TYPE_THEME'] <> 1){
						$reqtbms = "SELECT * FROM DICO_TABLE_MERE_THEME WHERE ID_THEME=" . $thm['ID'] . " ORDER BY PRIORITE";
					}else{
						$reqtbms = "SELECT ID_THEME,ID_TABLE_MERE_THEME ,TABLE_MERE AS NOM_TABLE_MERE,PRIORITE FROM DICO_ZONE WHERE ID_THEME=" . $thm['ID'] . " ORDER BY PRIORITE";
					}
					
					$currtabs = $GLOBALS['conn_dico']->GetAll($reqtbms); 
					if( is_array($currtabs) && count($currtabs) ){
						foreach($currtabs as $i_tabm => $tabm){
							$temp_list_tabms[] = $tabm['NOM_TABLE_MERE'] ;
						}
					}
				}
			}
			
			foreach($temp_list_tabms as $i_tabm => $tabm){
				if(!$this->est_ds_tableau($tabm, $this->list_tabms_ord) && (exist_champ_in_table($this->champ_maj, $tabm))){
					$this->list_tabms_ord[] = $tabm ;
				}
			}
			
			if(!count($this->list_tabms_ord)){
				echo'<br> None of customized tables is containing the specified field ';
				break ;
			}
			//echo'<pre>list tables <br> ';print_r($this->list_tabms_ord);die();
			
		}
    
		function check_setting_values(){
		//die($this->start_val .'/'. $this->min_val);
			//if( $this->start_val > $this->min_val ){
				foreach($this->list_tabms_ord as $i_tab => $table){
					$req_exist	= 'SELECT count('.$this->champ_maj.') as nb_rec FROM '.$table.' WHERE '.$this->champ_maj.' > '.$this->start_val;
					$exists		= $GLOBALS['conn']->GetOne($req_exist);
					if( $exists > 0 ){
						echo'<br> Warning : It exists some values more greater for "'.$this->champ_maj.'" in table : <b>'.$table.'</b> : Risks of conflicts <br>';
						//return false ;
					}
				}
			//}else{
				//echo'<br> Le code de Recodification doit etre renseigné et supérieur au code Max indiqué <br>';
			//}
			return true ;
		}
		
		function get_field_values(){
			($this->max_val>=$this->min_val) ? ($plusstr=' AND '.$this->champ_maj.' <= '.$this->max_val ) : ($plusstr = '') ;
			$codes_traites	= array();
			$recoding		= array();
			$code_attrib	= $this->start_val;
			foreach($this->list_tabms_ord as $i_tab => $table){
				$req_codes		= 'SELECT distinct '.$this->champ_maj.' FROM '.$table.' WHERE '.$this->champ_maj.' >= '.$this->min_val .$plusstr.' ORDER BY '.$this->champ_maj;
				$recod_codes 	= $GLOBALS['conn']->GetAll($req_codes);
				if( is_array($recod_codes) and (count($recod_codes) > 0) ){
					foreach($recod_codes as $i => $row){
						$code = $row[$this->champ_maj];
						if(!$this->est_ds_tableau($code, $codes_traites)){
							$recoding["$code"] = $code_attrib;
							$codes_traites[] = $code;
							//echo  '<br>code_attrib='.$code_attrib.'<br>' ;
							$code_attrib++;
						}
					}
				}
			}
			$this->tab_recoding = $recoding ;
		}
		
		function print_res_recoding(){
			print'
			<br><br>
			<table border="1" width="75%" class="center-table">
			<CAPTION>Re-attribution of Codes for the field : <u>'.$this->champ_maj.'</u>
			</CAPTION>';

				  $i = 1 ;
				foreach($this->tab_recoding as $old_code => $new_code){ 
					print'
					<tr>
						<td><em>'.$i++.'</em></td>
						<td nowrap>Old Code : <strong>'.$old_code.'</strong></td>
						<td nowrap>New Code : <span style="color: #0000FF;font-weight: bold;">'.$new_code.'</span></td>
					</tr>';
				}
			 
			  print'
				</table>';
			
		}
		
		function run(){
			$tables_traitees = array();
			foreach($this->tab_secteurs as $i_sect => $secteur){
				$this->set_list_tabms_ord($secteur);
			}
			if($this->check_setting_values()){
				if(!count($this->tab_recoding)){
					$this->get_field_values() ;
				}
				if(count($this->tab_recoding)){
					$this->print_res_recoding();
					print'<br><table width="75%" class="center-table"><tr><td align="left">';
					print '<u>Result of operations:</u> VAL MIN = "<b>'.$this->min_val.'</b>"  &nbsp; VAL MAX = "<b>'.$this->max_val.'</b>"   &nbsp; STARTING RECOD = "<b>'.$this->start_val.'</b>"</span><br><br>';
					foreach($this->list_tabms_ord as $i_tab => $table){
						if(!$this->est_ds_tableau($table, $tables_traitees)){
							$nb_affect = 0 ;	
							echo 'Treatment of Table : <b>'.$table.'</b> ... ';
							foreach($this->tab_recoding as $old_code => $new_code){
								$strUpd = 'UPDATE '.$table.' SET '.$this->champ_maj.' = '.$new_code.' WHERE '.$this->champ_maj.'='.$old_code;
								//$resUpd = $GLOBALS['conn']->Execute($strUpd); 
								if (($resUpd=$GLOBALS['conn']->Execute($strUpd)) === false){
									print '<span style="color: #FF0000;font-weight: bold;"> Update Error : </span> "<br><em>'.$strUpd.'</em>"<br>'; 
								}else{
									$nb_affect += $GLOBALS['conn']->Affected_Rows() ;
								}   
							}
							$tables_traitees[] = $table ;
							echo '<span style="color: #0000FF;font-weight: bold;"> '. $nb_affect .' </span> record(s) affected <br>';
						}
					}
					print '</td></tr></table>';
				}else{
					echo'<br> It doesnt exist record of field  <b>'.$this->champ_maj.'</b> corresponding to criterias specified'; 
				}
			}
		}
	}
?>
