<?php /**
 * Title: Print a HTML Table to PDF file
 * Class: PDFTable
 * Author: vietcom (vncommando at yahoo dot com)
 * Version: 1.1
 *
 * Update to 1.1 (2-Nov-2004):
 *		- Support long table in many page. Repeatable for some rows on each page.
 *		  Limitation: The height of each row can't great than the page height-the height of repeat title
 *		
 */
require_once('fpdf.inc.php');

class PDFTable extends FPDF{
var $left;			//Toa do le trai cua trang
var $right;			//Toa do le phai cua trang
var $top;			//Toa do le tren cua trang
var $bottom;		//Toa do le duoi cua trang
var $width;			//Width of writable zone of page
var $height;		//Height of writable zone of page
var $titre ;
var $outlines=array();
var $OutlineRoot;
var $entete_page;
var $pied_page;

function __construct($orientation='L',$unit='mm',$format='A4'){
	FPDF::FPDF($orientation,$unit,$format);
	$this->SetMargins(20,20,20);
	$this->SetAuthor('Pham Minh Dung');
	$this->_makePageSize();
}

function SetMargins($left,$top,$right=-1){
	FPDF::SetMargins($left, $top, $right);
	
	$this->_makePageSize();
}

function SetLeftMargin($margin){
	FPDF::SetLeftMargin($margin);
	$this->_makePageSize();
}

function SetRightMargin($margin){
	FPDF::SetRightMargin($margin);
	$this->_makePageSize();
}



function Header(){
	$this->_makePageSize();
    $this->SetFont('times','',8);
    $this->SetY(10);
    $this->Cell(10,5,$this->entete_page);
    $this->Cell($this->width-20);
    $this->Cell(0,10,date("d/m/Y"),0,0,'C');
    $this->Ln(10);
}

function Footer() {
    // Check if Footer for this page already exists (do the same for Header())
    if(!$this->footerset[$this->page]) {
        $this->SetY(-15);
        //Page number
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
        $this->Cell(0,10,$this->pied_page,0,0,'C');
        // set footerset
        $this->footerset[$this->page] = 1;
    }
}

function _makePageSize(){
	if ($this->CurOrientation=='P'){
		$this->left		= $this->lMargin;
		$this->right	= $this->fw - $this->rMargin;
		$this->top		= $this->tMargin;
		$this->bottom	= $this->fh - $this->bMargin;
		$this->width	= $this->right - $this->left;
		$this->height	= $this->bottom - $this->tMargin;
	}else{
		$this->left		= $this->tMargin;
		$this->right	= $this->fh - $this->bMargin;
		$this->top		= $this->rMargin;
		$this->bottom	= $this->fw - $this->rMargin;
		$this->width	= $this->right - $this->left;
		$this->height	= $this->bottom - $this->lMargin;
	}
}

/*
    Ajout de signets
*/
function Bookmark($txt,$level=0,$y=0)
{
	if($y==-1)
		$y=$this->GetY();
	$this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
}
function _putbookmarks()
{
	$nb=count($this->outlines);
	if($nb==0)
		return;
	$lru=array();
	$level=0;
	foreach($this->outlines as $i=>$o)
	{
		if($o['l']>0)
		{
			$parent=$lru[$o['l']-1];
			//Set parent and last pointers
			$this->outlines[$i]['parent']=$parent;
			$this->outlines[$parent]['last']=$i;
			if($o['l']>$level)
			{
				//Level increasing: set first pointer
				$this->outlines[$parent]['first']=$i;
			}
		}
		else
			$this->outlines[$i]['parent']=$nb;
		if($o['l']<=$level and $i>0)
		{
			//Set prev and next pointers
			$prev=$lru[$o['l']];
			$this->outlines[$prev]['next']=$i;
			$this->outlines[$i]['prev']=$prev;
		}
		$lru[$o['l']]=$i;
		$level=$o['l'];
	}
	//Outline items
	$n=$this->n+1;
	foreach($this->outlines as $i=>$o)
	{
		$this->_newobj();
		$this->_out('<</Title '.$this->_textstring($o['t']));
		$this->_out('/Parent '.($n+$o['parent']).' 0 R');
		if(isset($o['prev']))
			$this->_out('/Prev '.($n+$o['prev']).' 0 R');
		if(isset($o['next']))
			$this->_out('/Next '.($n+$o['next']).' 0 R');
		if(isset($o['first']))
			$this->_out('/First '.($n+$o['first']).' 0 R');
		if(isset($o['last']))
			$this->_out('/Last '.($n+$o['last']).' 0 R');
		$this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
		$this->_out('/Count 0>>');
		$this->_out('endobj');
	}
	//Outline root
	$this->_newobj();
	$this->OutlineRoot=$this->n;
	$this->_out('<</Type /Outlines /First '.$n.' 0 R');
	$this->_out('/Last '.($n+$lru[0]).' 0 R>>');
	$this->_out('endobj');
}

function _putresources()
{
    parent::_putresources();
    $this->_putbookmarks();
}

function _putcatalog()
{
	parent::_putcatalog();
	if(count($this->outlines)>0)
	{
		$this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
		$this->_out('/PageMode /UseOutlines');
	}
}
/**
 * @return int
 * Tra ve chieu cao cua 1 dong theo font hien hanh
 */
function getLineHeight(){
	return $this->FontSizePt/2;
}

/**
 * @return int
 * @desc Tinh so dong cua $txt khi hien thi trong cell co width la $w
 */
function countLine($w,$txt){
    //Computes the number of lines a MultiCell of width w will take
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 and $s[$nb-1]=="\n")
        $nb--;
    $sep=-1;
    $i=$j=$l=0;
    $nl=1;
    while($i<$nb){
        $c=$s[$i];
        if($c=="\n"){
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
            continue;
        }
        if($c==' ')
            $sep=$i;
        $l+=$cw[$c];
        if($l>$wmax){
            if($sep==-1){
                if($i==$j)
                    $i++;
            }
            else
                $i=$sep+1;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
        }
        else
            $i++;
    }
    return $nl;
}

/**
 * @return array
 * @desc Parse a string in html and return array of attribute of table
 */
function _tableParser($html){
	$align = array('left'=>'L','center'=>'C','right'=>'R','top'=>'T','middle'=>'M','bottom'=>'B');
	require_once('htmlparser.inc.php');
	$t = new TreeHTML(new HTMLParser($html), 0);
	$row	= $col	= -1;
	$table['nc'] = $table['nr'] = 0;
	$table['repeat'] = array();
	$cell   = array();
	foreach ($t->name as $i=>$element){
		if ($t->type[$i] != NODE_TYPE_ELEMENT && $t->type[$i] != NODE_TYPE_TEXT) continue;
		switch ($element){
			case 'table':
				$a	= &$t->attribute[$i];
				if (isset($a['width']))		$table['w']	= intval($a['width']);
				if (isset($a['height']))	$table['h']	= intval($a['height']);
				if (isset($a['align']))		$table['a']	= $align[strtolower($a['align'])];
				if (isset($a['border']))	$table['border']	= $a['border'];
				if (isset($a['bgcolor']))	$table['bgcolor'][-1]	= $a['bgcolor'];
				break;
			case 'tr':
				$row++;
				$table['nr']++;
				$col = -1;
				$a	= &$t->attribute[$i];
				if (isset($a['bgcolor']))	$table['bgcolor'][$row]	= $a['bgcolor'];
				if (isset($a['repeat']))	$table['repeat'][]		= $row;
				break;
			case 'td':
				$col++;while (isset($cell[$row][$col])) $col++;
				//Update number column
				if ($table['nc'] < $col+1)		$table['nc']		= $col+1;
				$cell[$row][$col] = array();
				$c = &$cell[$row][$col];
				$a	= &$t->attribute[$i];
				$c['text'] = array();
				$c['s']	= 2;
				if (isset($a['width']))		$c['w']		= intval($a['width']);
				if (isset($a['height']))	$c['h']		= intval($a['height']);
				if (isset($a['align']))		$c['a']		= $align[strtolower($a['align'])];
				if (isset($a['valign']))	$c['va']	= $align[strtolower($a['valign'])];
				if (isset($a['border']))	$c['border']	= $a['border'];
				if (isset($a['bgcolor']))	$c['bgcolor']	= $a['bgcolor'];
				$cs = $rs = 1;
				if (isset($a['colspan']) && $a['colspan']>1)	$cs = $c['colspan']	= intval($a['colspan']);
				if (isset($a['rowspan']) && $a['rowspan']>1)	$rs = $c['rowspan']	= intval($a['rowspan']);
				//Chiem dung vi tri de danh cho cell span
				for ($k=$row;$k<$row+$rs;$k++) for($l=$col;$l<$col+$cs;$l++){
					if ($k-$row || $l-$col)
						$cell[$k][$l] = 0;
				}
				if (isset($a['nowrap']))	$c['nowrap']= 1;
				break;
			case 'Text':
				$c['text'][]	= $tmp = $this->_html2text($t->value[$i]);
				if ($tmp=='20'){
					echo '';
				}
				$tmp = $this->GetStringWidth($tmp) + 3;
				if (!isset($c['s']) || $c['s'] < $tmp)	$c['s'] = $tmp;
				break;
			case 'br':
				break;
		}
	}
	$table['cells'] = $cell;
	$table['wc']	= array_pad(array(),$table['nc'],array('miw'=>0,'maw'=>0));
	$table['hr']	= array_pad(array(),$table['nr'],0);
	return $table;
}

function _html2text($text){
	$text = str_replace('&nbsp;',' ',$text);
	$text = str_replace('&lt;','<',$text);
	return $text;
}

//table		Array of (w, h, bc, nr, wc, hr, cells)
//w			Width of table
//h			Height of table
//bc		Number column
//nr		Number row
//hr		List of height of each row
//wc		List of width of each column
//cells		List of cells of each rows, cells[i][j] is a cell in table
function _tableColumnWidth(&$table){
	$cs = &$table['cells'];
	$mw = $this->getStringWidth('W');
	$nc = $table['nc'];
	$nr = $table['nr'];
	$listspan = array();
	//Xac dinh do rong cua cac cell va cac cot tuong ung
	for ($j=0;$j<$nc;$j++){
		$wc = &$table['wc'][$j];
		for ($i=0;$i<$nr;$i++){
			if (isset($cs[$i][$j]) && $cs[$i][$j]){
				$c   = &$cs[$i][$j];
				$miw = $mw;
				$c['maw']	= $c['s'];
				if (isset($c['nowrap']))			$miw = $c['maw'];
				if (isset($c['w'])){
					if ($miw<$c['w'])	$c['miw'] = $c['w'];
					if ($miw>$c['w'])	$c['miw'] = $c['w']	  = $miw;
					if (!isset($wc['w'])) $wc['w'] = 1;
				}else{
					$c['miw'] = $miw;
				}
				if ($c['maw']  < $c['miw'])			$c['maw']  = $c['miw'];
				if (!isset($c['colspan'])){
					if ($wc['miw'] < $c['miw'])		$wc['miw']	= $c['miw'];
					if ($wc['maw'] < $c['maw'])		$wc['maw']	= $c['maw'];
				}else $listspan[] = array($i,$j);
			}
		}
	}
	//Xac dinh su anh huong cua cac cell colspan len cac cot va nguoc lai
	$wc = &$table['wc'];
	foreach ($listspan as $span){
		list($i,$j) = $span;
		$c = &$cs[$i][$j];
		$lc = $j + $c['colspan'];
		if ($lc > $nc) $lc = $nc;
		
		$wis = $wisa = 0;
		$was = $wasa = 0;
		$list = array();
		for($k=$j;$k<$lc;$k++){
			$wis += $wc[$k]['miw'];
			$was += $wc[$k]['maw'];
			if (!isset($c['w'])){
				$list[] = $k;
				$wisa += $wc[$k]['miw'];
				$wasa += $wc[$k]['maw'];
			}
		}
		if ($c['miw'] > $wis){
			if (!$wis){//Cac cot chua co kich thuoc => chia deu
				for($k=$j;$k<$lc;$k++) $wc[$k]['miw'] = $c['miw']/$c['colspan'];
			}elseif (!count($list)){//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
				$wi = $c['miw'] - $wis;
				for($k=$j;$k<$lc;$k++) 
					$wc[$k]['miw'] += ($wc[$k]['miw']/$wis)*$wi;
			}else{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
				$wi = $c['miw'] - $wis;
				foreach ($list as $k)
					$wc[$k]['miw'] += ($wc[$k]['miw']/$wisa)*$wi;
			}
		}
		if ($c['maw'] > $was){
			if (!$wis){//Cac cot chua co kich thuoc => chia deu
				for($k=$j;$k<$lc;$k++) $wc[$k]['maw'] = $c['maw']/$c['colspan'];
			}elseif (!count($list)){//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
				$wi = $c['maw'] - $was;
				for($k=$j;$k<$lc;$k++) 
					$wc[$k]['maw'] += ($wc[$k]['maw']/$was)*$wi;
			}else{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
				$wi = $c['maw'] - $was;
				foreach ($list as $k)
					$wc[$k]['maw'] += ($wc[$k]['maw']/$wasa)*$wi;
			}
		}
	}
}

/**
 * @desc Xac dinh chieu rong cua table
 */
function _tableWidth(&$table){
	$wc = &$table['wc'];
	$nc = $table['nc'];
	$a = 0;
	for ($i=0;$i<$nc;$i++){
		$a += isset($wc[$i]['w']) ? $wc[$i]['miw'] : $wc[$i]['maw'];
	}
	if ($a > $this->width) $table['w'] = $this->width;

	if (isset($table['w'])){
		$wis = $wisa = 0;
		$list = array();
		for ($i=0;$i<$nc;$i++){
			$wis += $wc[$i]['miw'];
			if (!isset($wc[$i]['w'])){ $list[] = $i;$wisa += $wc[$i]['miw'];}
		}
		if ($table['w'] > $wis){
			if (!count($list)){//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
				//$wi = $table['w'] - $wis;
				$wi = ($table['w'] - $wis)/$nc;
				for($k=0;$k<$nc;$k++) 
					//$wc[$k]['miw'] += ($wc[$k]['miw']/$wis)*$wi;
					$wc[$k]['miw'] += $wi;
			}else{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
				//$wi = $table['w'] - $wis;
				$wi = ($table['w'] - $wis)/count($list);
				foreach ($list as $k)
					//$wc[$k]['miw'] += ($wc[$k]['miw']/$wisa)*$wi;
					$wc[$k]['miw'] += $wi;
			}
		}
		for ($i=0;$i<$nc;$i++){
			$a = $wc[$i]['miw'];
			unset($wc[$i]);
			$wc[$i] = $a;
		}
	}else{
		$table['w'] = $a;
		for ($i=0;$i<$nc;$i++){
			$a = isset($wc[$i]['w']) ? $wc[$i]['miw'] : $wc[$i]['maw'];
			unset($wc[$i]);
			$wc[$i] = $a;
		}
	}
	$table['w'] = array_sum($wc);
}

function _tableHeight(&$table){
	$cs = &$table['cells'];
	$nc = $table['nc'];
	$nr = $table['nr'];
	$listspan = array();
	for ($i=0;$i<$nr;$i++){
		$hr = &$table['hr'][$i];
		for ($j=0;$j<$nc;$j++){
			if (isset($cs[$i][$j]) && $cs[$i][$j]){
				$c = &$cs[$i][$j];
				list($x,$cw) = $this->_tableGetWCell($table, $i,$j);
				$ch = $this->countLine($cw, implode("\n",$c['text'])) * $this->getLineHeight();
				if (isset($c['h']) && $c['h'] > $ch)
					$ch = $c['h'];
				if (isset($c['rowspan']))
					$listspan[] = array($i,$j);
				elseif ($hr < $ch)
					$hr = $ch;
				$c['mih'] = $ch;
			}
		}
	}
	$hr = &$table['hr'];
	foreach ($listspan as $span){
		list($i,$j) = $span;
		$c = &$cs[$i][$j];
		$lr = $i + $c['rowspan'];
		if ($lr > $nr) $lr = $nr;
		$hs = $hsa = 0;
		$list = array();
		for($k=$i;$k<$lr;$k++){
			$hs += $hr[$k];
			if (!isset($c['h'])){
				$list[] = $k;
				$hsa += $hr[$k];
			}
		}
		if ($c['mih'] > $hs){
			if (!$hs){//Cac dong chua co kich thuoc => chia deu
				for($k=$i;$k<$lr;$k++) $hr[$k] = $c['mih']/$c['rowspan'];
			}elseif (!count($list)){//Khong co dong nao co kich thuoc auto => chia deu phan du cho tat ca
				$hi = $c['mih'] - $hs;
				for($k=$i;$k<$lr;$k++) 
					$hr[$k] += ($hr[$k]/$hs)*$hi;
			}else{//Co mot so dong co kich thuoc auto => chia deu phan du cho cac dong auto
				$hi = $c['mih'] - $hsa;
				foreach ($list as $k)
					$hr[$k] += ($hr[$k]/$hsa)*$hi;
			}
		}
	}
	$table['repeatH'] = 0;
	if (count($table['repeat'])){
		foreach ($table['repeat'] as $i) $table['repeatH'] += $hr[$i];
	}else $table['repeat'] = 0;
}

/**
 * @desc Xac dinh toa do va do rong cua mot cell
 */
function _tableGetWCell(&$table, $i,$j){
	$c = &$table['cells'][$i][$j];
	if ($c){
		if (isset($c['x0'])) return array($c['x0'], $c['w0']);
		$x = 0;
		$wc = &$table['wc'];
		for ($k=0;$k<$j;$k++) $x += $wc[$k];
		$w = $wc[$j];
		if (isset($c['colspan'])){
			for ($k=$j+$c['colspan']-1;$k>$j;$k--)
				$w += $wc[$k];
		}
		$c['x0'] = $x;
		$c['w0'] = $w;
		return array($x, $w);
	}
	return array(0,0);
}

function _tableGetHCell(&$table, $i,$j){
	$c = &$table['cells'][$i][$j];
	if ($c){
		if (isset($c['h0'])) return $c['h0'];
		$hr = &$table['hr'];
		$h = $hr[$i];
		if (isset($c['rowspan'])){
			for ($k=$i+$c['rowspan']-1;$k>$i;$k--)
				$h += $hr[$k];
		}
		$c['h0'] = $h;
		return $h;
	}
	return 0;
}

function _tableRect($x, $y, $w, $h, $type=1){
	if ($type===1)
		$this->Rect($x, $y, $w, $h);
	elseif (strlen($type)==4){
		$x2 = $x + $w; $y2 = $y + $h;
		if (intval($type{0})) $this->Line($x , $y , $x2, $y );
		if (intval($type{1})) $this->Line($x2, $y , $x2, $y2);
		if (intval($type{2})) $this->Line($x , $y2, $x2, $y2);
		if (intval($type{3})) $this->Line($x , $y , $x , $y2);
	}
}

function _tableDrawBorder(&$table){
	//When fill a cell, it overwrite the border of prevous cell, then I have to draw border at the end
	foreach ($table['listborder'] as $c){
		list($x,$y,$w,$h,$s) = $c;
		if ($s) $this->_tableRect($x,$y,$w,$h,$s); else $this->Rect($x, $y, $w, $h);
	}
	$table['listborder'] = array();
}
function _tableWriteRow(&$table,$i,$x0){
	//echo $i.'<br>';
	$maxh = 0;
	for ($j=0;$j<$table['nc'];$j++){
		$h = $this->_tableGetHCell($table, $i, $j);
		if ($maxh < $h) $maxh = $h;
	}
	if ($table['lasty']+$maxh>$this->bottom && $table['multipage']){
		if ($maxh+$table['repeatH'] > $this->height){
			$msg = 'Height of this row is great than page height!';
			$h = $this->countLine(0,$msg) * $this->getLineHeight();
			$this->SetFillColor(255,0,0);
			$this->Rect($this->x, $this->y=$table['lasty'], $table['w'], $h, 'F');
			if ($i==0)
				$this->SetFont('times','B',20);	
			else
				$this->SetFont('times','',12);	
			$this->MultiCell($table['w'],$this->getLineHeight(),$msg);
			$table['lasty'] += $h;
			return ;
		}
		$this->_tableDrawBorder($table);
		$this->AddPage($this->CurOrientation);
		$table['lasty'] = $this->tMargin;
		if ($table['repeat']){
			foreach ($table['repeat'] as $r){
				if ($r==$i) continue;
				$this->_tableWriteRow($table,$r,$x0);
			}
		}
	}
	$y = $table['lasty'];
	for ($j=0;$j<$table['nc'];$j++){
		if (isset($table['cells'][$i][$j]) && $table['cells'][$i][$j]){
			$c = &$table['cells'][$i][$j];
			list($x,$w) = $this->_tableGetWCell($table, $i, $j);
			$h = $this->_tableGetHCell($table, $i, $j);
			$x += $x0;
			//Align
			$this->x = $x; $this->y = $y;
			$align = isset($c['a'])? $c['a'] : 'L';
			//Vertical align
			if (!isset($c['va']) || $c['va']=='M'){
				$this->y += ($h-$c['mih'])/2;
			}elseif (isset($c['va']) && $c['va']=='B'){
				$this->y += $h-$c['mih'];
			}
			//Fill
			$fill = isset($c['bgcolor']) ? $c['bgcolor']
				: (isset($table['bgcolor'][$i]) ? $table['bgcolor'][$i]
				: (isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0));
			if ($fill){
				require_once('color.inc.php');
				$color = Color::HEX2RGB($fill);
				$this->SetFillColor($color[0],$color[1],$color[2]);
				$this->Rect($x, $y, $w, $h, 'F');
			};
			//Content
			//$this->text($w,implode("\n",$c['text']),0,$align);
			$this->MultiCell($w,$this->getLineHeight(),implode("\n",$c['text']),0,$align);
			//Border
			if (isset($c['border'])){
				$table['listborder'][] = array($x,$y,$w,$h,$c['border']);
			}elseif (isset($table['border']) && $table['border'])
				$table['listborder'][] = array($x,$y,$w,$h,0);
		}
	}
	$table['lasty'] += $table['hr'][$i];
}
function _tableWrite(&$table){
	//if ($table['w']>$this->width+5) 
	//debug($this->CurOrientation,$table['w'],$this->width);
	if ($this->CurOrientation == 'P' && $table['w']>$this->width+5) $this->AddPage('L');
	$x0 = $this->x;
	$y0 = $this->y;
	if (isset($table['a'])){
		if ($table['a']=='C'){
			$x0 += (($this->right-$x0) - $table['w'])/2;
		}elseif ($table['a']=='R'){
			$x0 = $this->right - $table['w'];
		}
	}
	$table['lasty'] = $y0;
	$table['listborder'] = array();
	for ($i=0;$i<$table['nr'];$i++) $this->_tableWriteRow($table, $i, $x0);
	$this->_tableDrawBorder($table);
	//echo "<pre>";print_r($table);
}

function htmltable($etat){
    $a = $this->AutoPageBreak;
    $this->SetAutoPageBreak(0,$this->bMargin);
    $table = $this->_tableParser($etat['html']);
    $table['multipage'] = $etat['multipage'];
    $this->_tableColumnWidth($table);
    $this->_tableWidth($table);
    $this->_tableHeight($table);
    $this->_tableWrite($table);
    $this->SetAutoPageBreak($a,$this->bMargin);
}
}
?>

