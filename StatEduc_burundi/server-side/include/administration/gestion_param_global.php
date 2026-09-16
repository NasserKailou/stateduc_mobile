<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_PARAM', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_param', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CODE_PARAM', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'code_param', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_PARAM', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_param', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_PARAM', 'type'=>'int', 'cle'=>'', 'incr'=>'1', 'val'=>'', 'lib'=>'ordre_param', 'obli'=>'', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'DESC_PARAM', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'desc_param', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$table 						= new gestion_table_simple() ;
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table							= 'DICO_PARAM_GLOBAL';
		$table->champs 						= $champs;
		$table->nom_champ_combo		= 'CODE_PARAM';
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_param_global.php';
		$table->taille_ecran			= '450';
		$table->btn_quit					= false;
		$table->titre_ecran				= 'TitPGlob';
		$table->run();
		if($table->act_Regen_params === true){
			$file ='';
			$file .= '<?php '."\n";
			//$file .= "\n\t".'$GLOBALS[\'PARAM\'] = array();'."\n";
			if(is_array($table->donnees)){
				foreach($table->donnees as $i => $ligne){
					if(!$table->putQuote(trim($ligne['NOM_PARAM']))){
						$file .= "\n\t".$ligne['CODE_PARAM'].'	=	\''.$ligne['NOM_PARAM'].'\';';
						(trim($ligne['DESC_PARAM']) <> '') ? ($file .= ' // ' . $ligne['DESC_PARAM']) : ($file .= '') ;
					}else{
						$file .= "\n\t".$ligne['CODE_PARAM'].'	=	'.$ligne['NOM_PARAM'].';';
						(trim($ligne['DESC_PARAM']) <> '') ? ($file .= ' // ' . $ligne['DESC_PARAM']) : ($file .= '') ;
					}
				}
			}
			$file .= "\n\n".'?>';
			
			if(!file_exists('params_original.php')){
				rename('params.php','params_original.php');
			}
			file_put_contents($GLOBALS['SISED_PATH'] . 'params.php', $file);
		
		}
				
?>

