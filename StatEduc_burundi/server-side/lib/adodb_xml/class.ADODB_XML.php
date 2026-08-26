<?php require("class.xml.php"); ?>
<?php
/**
* Class of Manipulation of files XML and SGBD
*
* @author    			Olavo Alexandrino <oalexandrino@yahoo.com.br> - 2004
* @originalAuthor		Ricardo Costa <ricardo.community@globo.com>   - 2002
* @based				MySQL to XML - XML to MySQL - <http://www.phpclasses.org/browse/package/782.html>
* @require				Class XMLFile <Olavo Alexandrino version> - <orignal version: http://www.phpclasses.org/browse/package/79.html>
		- function commentary to strtoupper of the methods:
			1. add_attribute
			2. set_name
* @require				ADOdb Database Library for PHP <http://php.weblogs.com/adodb#downloads>
*/

class ADODB_XML 
{
	/**
	*	Object representation:  File XML
	*
	*	@type		objcet
	*	@access		public
	*/
	var $xml = null; 
	
	var $logs = null;
	var $ko_logs = null;
	var $ok_tables = null;

	/**
	*	Creating the members
	*
	*	@param		string		Version of file XML
	*	@param		string		Codification to be used	
	*	@access		public
	*/
	function __construct($version = "", $encoding = "") 
	{
	  $this->xml = new XMLFile($version, $encoding);
	  $this->ok_tables = array(); 	
	  $this->ko_logs = array();
	}
	
	/**
	*	It converts Table of the SGBD into file XML
	*
	*	@param		object 			Connection of ADOdb Database Library	
	*	@param		string			Query SQL			
	*	@param		string			Name of existing file XML
	*	@access		public
	*	@return 	void	
	*/	
	function ConvertToXML($dbConnection, $strSQL, $fields, $filename) 
	{
	  $dbConnection->SetFetchMode(ADODB_FETCH_ASSOC);
	  $rs = NULL;
	  try {
		$rs = $dbConnection->Execute($strSQL);
	  }
		catch(Exception $e) {
	  }
	  $this->xml->create_root();
	  $this->xml->roottag->name = "ROOT";
	  //print_r($rs->fields);return;
	  if ($rs) {
		  while(!$rs->EOF)
		  {
			 $this->xml->roottag->add_subtag("ROW", array());
			 $tag = &$this->xml->roottag->curtag;
			 
			 for ($i = 0; $i < $rs->_numOfFields ; $i++)
			 {
				list($field, $value) = each($rs->fields);
				$fieldOk = trim($fields[$i]);		 
				$tag->add_subtag($fieldOk); //echo $field."\n\n";
				$tag->curtag->cdata = xmlEncode($value);
			 }	  
		  
			 $rs->moveNext();
		  }
	  }
	   
	  $xml_file = fopen($filename, "w" );   
	  $this->xml->write_file_handle( $xml_file );
	  $xml_data = file_get_contents($filename);
	  // replace '&' followed by a bunch of letters, numbers
	  // and underscores and an equal sign with &amp;
	  $xml_data = preg_replace("|&([^;]+?)[\s&]|","&amp;$1 ",$xml_data);
	  file_put_contents($filename, $xml_data);
	}
	
	/**
	*	It converts Table of the SGBD into file XML
	*
	*	@param		object 			Connection of ADOdb Database Library	
	*	@param		string			Query SQL			
	*	@param		string			Name of existing file XML
	*	@access		public
	*	@return 	void	
	*/	
	function ConvertToXML2($dbConnection, $strSQL, $fields, $tablename, $filename) 
	{
	  $dbConnection->SetFetchMode(ADODB_FETCH_ASSOC);
	  $rs = NULL;
	  try {
		$rs = $dbConnection->Execute($strSQL);
	  }
		catch(Exception $e) {
	  }
	  $this->xml->create_root();
	  $this->xml->roottag->name = "ROOT";
	  //print_r($rs->fields);return;
	  if ($rs) {
			$primaryKeys = MetaPrimaryKeys($tablename);
			if (!$primaryKeys || count($primaryKeys) == 0) {
				$primaryKeys = $dbConnection->MetaPrimaryKeys($tablename); 
			}
		  while(!$rs->EOF)
		  {
			 $this->xml->roottag->add_subtag("ROW", array());
			 $tag = &$this->xml->roottag->curtag;
			 
			 for ($i = 0; $i < $rs->_numOfFields ; $i++)
			 {
				list($field, $value) = each($rs->fields);
				$fieldOk = trim($fields[$i]);		 
				$tag->add_subtag($fieldOk); //echo $field."\n\n";
				$tag->curtag->cdata = xmlEncode($value);
				if (in_array($fieldOk, $primaryKeys)) {
					$tag->curtag->add_attribute("KEY", "1");
				}
			 }	  
		  
			 $rs->moveNext();
		  }
	  }
	   
	  $xml_file = fopen($filename, "w" );   
	  $this->xml->write_file_handle( $xml_file );
	  $xml_data = file_get_contents($filename);
	  // replace '&' followed by a bunch of letters, numbers
	  // and underscores and an equal sign with &amp;
	  $xml_data = preg_replace("|&([^;]+?)[\s&]|","&amp;$1 ",$xml_data);
	  file_put_contents($filename, $xml_data);
	}
	
	
	/**
	*	It inserts XML in table of the SGBD
	*
	*	@param		object 			Connection of ADOdb Database Library	
	*	@param		string			Name of to be created file XML	
	*	@param		string			Table of BD		
	*	@access		public
	*	@return 	void	
	*/	
	function InsertIntoDB($dbConnection, $filename, $tablename) 
	{
	
	  $this->logs = array(); 	
	  $xml_file = fopen($filename, "r"); 
	  $this->xml->read_file_handle($xml_file);
	
	  $numRows = $this->xml->roottag->num_subtags();
    
		$meta_types_integer 	= array('L', 'N', 'I', 'R', 4, 5, 7);
		$meta_types_date		= array('D', 'T');
		
		$fiedsType = array();
		$all_champs_table = $dbConnection->MetaColumns($tablename);
		$primaryKeys = MetaPrimaryKeys($tablename);
		if (!$primaryKeys || count($primaryKeys) == 0) {
			$primaryKeys = $dbConnection->MetaPrimaryKeys($tablename); 
		} 
		//print_r($all_champs_table);
		foreach( $all_champs_table as $champ ) {
			$meta_type 	= $champ->type;
			//echo "<br>$nom_champ, $table, ".$type_champ.", $meta_type<br>";
			
			if(in_array($meta_type , $meta_types_integer)){
			$fiedsType[$champ->name] = "I";
			}elseif(in_array($meta_type, $meta_types_date)){
				$fiedsType[$champ->name] = "D";
			}else{
				$fiedsType[$champ->name] = "S";
		}	}	  
		
	  for ($i = 0; $i < $numRows; $i++) 
	  {
		   $arrFields = null;
		   $arrValues = null; 
		   $arrWhere = null;
		   $arrUpdateFields = null;
	
		   $row = $this->xml->roottag->tags[$i];
		   $numFields = $row->num_subtags();
	
		   for ($ii = 0; $ii < $numFields; $ii++) 
		   {
				$field = $row->tags[$ii];
				$value = null;
				if ($fiedsType[$field->name] == "I") {
					$value = intval($field->cdata);
					if ($value == "") {
						continue;
					}
				} else {
					$value = $dbConnection->qstr($field->cdata);
				}
				$arrFields[] = $field->name;
				$arrValues[] = $value;
			  	if (in_array($field->name, $primaryKeys) || (is_array($field->attributes) && isset($field->attributes["KEY"]))) {
					$arrWhere[] = $field->name."=".$value;
				} else {
					$arrUpdateFields[] = $field->name."=".$value;
				}
		   }
	
		   $fields = join($arrFields, ", ");
		   $values = join($arrValues, ", ");
		   $where = join($arrWhere, " AND ");
		   $updateFields = join($arrUpdateFields, ", ");
	
		   $strSQL = "INSERT INTO $tablename ($fields) VALUES ($values)";
		   if ($dbConnection->Execute($strSQL) === false) {  //if ($tablename == 'SCHOOL') echo $strSQL."\n\n"; 
				if ($tablename != $GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']) {
					$strSQL = "UPDATE " . $tablename . " SET " . $updateFields. " WHERE " .$where;
					//echo $strSQL."\n\n";
					if ($dbConnection->Execute($strSQL) === false) {
						$this->ko_logs[] = $strSQL;
						//return false;
					} else {
					   $this->ok_logs[] = $strSQL;
					   if( !in_array($tablename, $this->ok_tables, true)) $this->ok_tables[] = $tablename;
					    //$this->ok_tables[] = $tablename;
				    }
				} else {
					//$this->ko_logs[] = $strSQL;	
				}
				//return true;
		   } else {
			   $this->ok_logs[] = $strSQL;
			   if( !in_array($tablename, $this->ok_tables, true)) $this->ok_tables[] = $tablename;
			   //$this->ok_tables[] = $tablename;
		   }
	  } 
   	  return true;
		  
	}
   
}// end class
?>