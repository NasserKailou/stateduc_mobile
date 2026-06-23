<style>
.aa{
	position: absolute;
	width:300px;
	height:25px;
	border:1px solid #000000;
	font-family:Verdana;
	font-size:13px;
	color:#000000;
	z-index:3;
	vertical-align: middle;
	left: 375px;
	top: 300px;
}
.bb{
	position: absolute;
	width:0px;
	height:25px;
	background-color:#00FF00;
	z-index:2;
	vertical-align: middle;
	left: 375px;
	top: 300px;
}
</style>
<script language="JavaScript" type="text/javascript">
		var niv = 1;
		function barre(){
			if( (document.getElementById("pourcentage")) && (document.getElementById("progrbar")) ){
				if(niv > 300) niv = 1;
				var ch_eval = 'document.getElementById("pourcentage").innerHTML=\'<?php echo recherche_libelle_page('isruuning');?>\';document.getElementById("progrbar").style.width='+(niv * 1)+'+"px";';
				eval(ch_eval);
				niv++;
				setTimeout("barre()", 1);
			}
		}
</script>
<div id='progress' style = 'visibility:visible' class="div_progress" align="center">
		<div id="pourcentage" class="aa" align="center"></div>
		<div id="progrbar" class="bb" align="center"></div>
</div>
<script language="JavaScript" type="text/javascript">
	setTimeout("barre()", 1);	
</script> 
