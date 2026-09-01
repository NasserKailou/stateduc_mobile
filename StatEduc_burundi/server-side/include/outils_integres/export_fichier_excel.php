<?php 
	
	
	use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

	include $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
	include $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     
	require_once ($GLOBALS['SISED_PATH_LIB'].'autoload.php');

 $htmlString = '<table>
                  <tr>
                      <td>Hello World</td>
                  </tr>
                  <tr>
                      <td>Hello<br />World</td>
                  </tr>
                  <tr>
                      <td>Hello<br>World</td>
                  </tr>
              </table>';

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
$spreadsheet = $reader->loadFromString($htmlString);

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
$writer->save($targetPath = $GLOBALS['SISED_PATH'].'server-side/import_export/write.xls'); 
 
?>
