<style>
.aa{
	position:absolute;
	top:15;
	left:5;
	width:330px;
	height:30px;
	border:1px solid #000000;
	font-family:Verdana;
	font-size:13px;
	color:#000000;
	z-index:3;
	text-align:center;
	vertical-align: middle;
}
.bb{
	position:absolute;
	top:15;
	left:5;
	width:0px;
	height:30px;
	background-color:#00FF00;
	z-index:2;
}
</style>
<script language="JavaScript" type="text/javascript">
		var niv = 1;
		function barre(){
				if(niv > 330) niv = 1;
				var ch_eval = 'document.getElementById("pourcentage").innerHTML=\'<?php echo recherche_libelle_page('isrunning'); ?> ...\';document.getElementById("progrbar").style.width='+(niv * 1)+'+"px";';
				eval(ch_eval);
				niv++;
				setTimeout("barre()", 1);
		}
</script>
<div id='progress' style = 'visibility:visible' class="div_progress" align="center">
		<div id="pourcentage" class="aa"></div>
		<div id="progrbar" class="bb"></div>
</div>
<script language="JavaScript" type="text/javascript">
	setTimeout("barre()", 1);	
</script> 
