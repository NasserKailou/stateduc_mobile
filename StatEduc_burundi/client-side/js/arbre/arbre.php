<?php require '../../../config_app.php';

?>

var xmlhttp=false;

/*@cc_on @*/

/*@if (@_jscript_version >= 5)
// JScript gives us Conditional compilation, we can cope with old IE versions.
// and security blocked creation of the objects.

try {

    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");

} catch (e) {

    try {

        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");

    } catch (E) {

        xmlhttp = false;

    }

}

@end @*/

if (!xmlhttp && typeof(XMLHttpRequest) != 'undefined') {

    xmlhttp = new XMLHttpRequest();

}

function fetchjs(script) {

    xmlhttp.open("GET", script, true);

    xmlhttp.onreadystatechange = function() {

        if(xmlhttp.readyState==4) {

            eval(xmlhttp.responseText);

        }

    }

    xmlhttp.send(null);

    return false;

}

function concat_into(id, data) {

    obj = document.getElementById(id);
    obj.innerHTML = obj.innerHTML + data;

}

function flush_into(id) {

    obj = document.getElementById(id);
    obj.innerHTML = '';

}

arborescences = new Array();
function Arborescence(Atop,Aleft,img_dir) {
	this.all = new Array();
	this.index = new Array();
	this.id = arbo_id();
	this.maxLevel = 0;
	this.addItem = arbo_addItem;
	this.createIndex = arbo_createIndex;
	this.selectedId = null;
	this.selectedColor = "none"; //"#FFEEB0";
	this.heightItem = 22;
	this.top = Atop?Atop:0;
	this.left = Aleft?Aleft:0;
	this.browser = new BrowserSniff();
	this.build = arbo_build;
	this.buildExpanded = arbo_buildExpanded
	this.buildItem = arbo_buildItem;
	this.destroy = arbo_destroy;
	this.destroyItem = arbo_destroyItem;
	this.showHideItem = arbo_showHideItem;
	this.moveEnd = arbo_moveEnd;
	this.selectItem = arbo_selectItem;
	this.expandAll = arbo_expandAll;
	this.expandLevel = arbo_expandLevel;
	this.collapseAll = arbo_collapseAll;
	this.img_dir = img_dir?img_dir:"<?php echo $GLOBALS['SISED_URL_IMG']; ?>arbre/";
	this.img_blank = this.img_dir + "blank.gif";
	this.img_item = this.img_dir + "Item.gif";
	this.img_last_item = this.img_dir + "Item_Last.gif";
	this.img_node_closed = this.img_dir + "Node_Closed.gif";
	this.img_node_open = this.img_dir + "Node_Open.gif";
	this.img_last_node_closed = this.img_dir + "Node_Last_Closed.gif";
	this.img_last_node_open = this.img_dir + "Node_Last_Open.gif";
	this.img_root = this.img_dir + "root.gif";
	this.img_line = this.img_dir + "Line.gif";
	this.loadCookie = arbo_loadCookie;
	this.loadItem = arbo_loadItem;
	this.cookieName = null;
	this.baseTarget = "window.location.href";
	this.itemClass = "itemContent";
	this.nodeClass = "nodeContent";
	this.falseTranspColor = "#FFFFFF";
}
function arbo_id() {
	arborescences[arborescences.length] = this;
	return arborescences.length-1;
}
function arbo_addItem(libelle,link,target,fetchjsurl) {
	var itemObject;
	itemObject = new Item(libelle,link,target,fetchjsurl);
	itemObject.arborescenceId = this.id;
	itemObject.id = this.all.length;
	this.all[this.all.length] = itemObject;
	arborescences[this.id] = this;
	return itemObject;
}
function arbo_createIndex() {
	var level, i, nbItem, childrenId, indexElement;
	this.index = new Array();
	this.all[0].indexId = 0;
	for (level = 1; level <= this.maxLevel; level ++) {
		for (i = 0; i < this.all.length; i++) {
			if (this.all[i].level == level) {
		 		nbItem = 1;
				for (childrenId = 0; childrenId < this.all[i].childrenId; childrenId++) {
					nbItem = nbItem + this.all[this.all[i].parentId].children[childrenId].subItem()+1;
				}
				this.all[i].indexId = this.all[this.all[i].parentId].indexId + nbItem;
			}
		}
	 }
	for (indexElement = 0; indexElement < this.all.length; indexElement++) {
	 	for (i = 0; i < this.all.length; i++) {
	 		if (this.all[i].indexId == indexElement) {
	 			this.index[indexElement] = this.all[i];
	 			break;
	 		}
	 	}
	 }
}
function BrowserSniff() {
	this.dom = (document.getElementById) ? true : false;
	this.ns4 = (document.layers) ? true : false;
	this.ie = (document.all) ? true : false;
	this.ie4 = this.ie && !this.dom;
}
function arbo_build() {
	var count;
	if (this.all[0].style!=null) {return;}
	this.buildExpanded();
	for (count = 1; count < this.index.length ; count++) {
		this.showHideItem(this.index[count],"close");
	}
	arborescences[this.id] = this;
}
function arbo_buildExpanded() {
	var i, count;
	if (this.all[0].style!=null) {return;}

	this.createIndex();

	if (this.browser.ie4) {
		document.write('<div style="position:absolute;top:' + this.top + ';left:' + this.left + '"><img src="' + this.img_blank + '" border="0" width="1" hspace="0" vspace="0" height="' +  (this.heightItem * this.index.length)  + '"></div>');
	}
	if (this.browser.dom) {
		document.write('<div style="position:absolute;top:' + this.top + ';left:' + this.left + '"><img src="' + this.img_blank + '" border="0" width="1" hspace="0" vspace="0" height="' +  (this.heightItem * this.index.length)  + '"></div>');
	}
	for (i = 0; i < this.index.length; i++) {
		this.buildItem(this.index[i]);
	}
	arborescences[this.id] = this;
	if ((this.id==0) && (this.browser.ns4)) {
		extendEvent(window,"onresize","window.location.reload()","after");
	}
}

function arbo_destroy() {
    var i, count;
    if (this.all[0].style!=null) {return;}
    this.createIndex();
	for (i = 0; i < this.index.length; i++) {
		this.destroyItem(this.index[i]);
	}
}

function arbo_destroyItem(objItem) {
    document.getElementById("Arbo" + this.id + "Item" + objItem.id + "Link").outerHTML = '';
    document.getElementById("Arbo" + this.id + "Item" + objItem.id).outerHTML = '';
}

function arbo_buildItem(objItem) {
	//var htmlCode, whithChild, itemName, imageName, linkName, thetop, linkContainerStart, linkContainerEnd, subItemCount, nextItemIndexId;
	htmlCode = "";
	whithChild = objItem.children.length!=0?true:false;
	itemName = "Arbo" + this.id + "Item" + objItem.id;
	imageName = itemName + "Img";
	linkName = itemName + "Link";
	thetop = (this.top + this.heightItem  * objItem.indexId);
	if (this.browser.ie4) {
		linkContainerStart = '<TD CLASS="arbre_td" ID="' +  linkName +  '" NOWRAP>';
		linkContainerEnd = '</TD>';
		htmlCode = '<DIV STYLE="POSITION:ABSOLUTE;VISIBILITY:VISIBLE;TOP:' + thetop + ';LEFT:' + this.left + '" ID="' + itemName + '" HEIGHT="' + this.heightItem + '" ><TABLE CLASS="arbre_table" BORDER="0" CELLPADDING="0" CELLSPACING="0"><TR>';
	}
	if (this.browser.dom) {
		linkContainerStart = '<TD CLASS="arbre_td" ID="' +  linkName +  '" NOWRAP valign="middle">';
		linkContainerEnd = '</TD>';
		htmlCode = '<DIV STYLE="POSITION:ABSOLUTE;VISIBILITY:VISIBLE;TOP:' + thetop + ';LEFT:' + this.left + '" ID="' + itemName + '" HEIGHT="' + this.heightItem + '" ><TABLE CLASS="arbre_table" BORDER="0" CELLPADDING="0" CELLSPACING="0"><TR>';
	}
	if (objItem.level == 0) {
		objItem.leftSide = '';
		htmlCode = htmlCode + '<TD CLASS="arbre_td"><IMG HEIGHT="' + this.heightItem + '" SRC="'+this.img_root+'"></TD>';
	} else {
		subItemCount = objItem.subItem();
		nextItemIndexId = objItem.indexId + subItemCount + 1;
		if (nextItemIndexId >= this.index.length) {
			objItem.lastPosition= true;
			objItem.leftSide = this.all[objItem.parentId].leftSide + '<TD CLASS="arbre_td"><IMG HEIGHT="' + this.heightItem + '" SRC="' + this.img_blank + '"></TD>';
		} else {
			if (objItem.level != this.index[nextItemIndexId].level) {
				objItem.lastPosition= true;
				objItem.leftSide = this.all[objItem.parentId].leftSide + '<TD CLASS="arbre_td"><IMG HEIGHT="' + this.heightItem + '" SRC="' + this.img_blank + '"></TD>';
			} else {
				objItem.lastPosition= false;
				objItem.leftSide = this.all[objItem.parentId].leftSide + '<TD CLASS="arbre_td"><IMG HEIGHT="' + this.heightItem + '" SRC="'+this.img_line+'"></TD>';
			}
		}
		if (whithChild) {
			if (objItem.lastPosition) {
				htmlCode = htmlCode + this.all[objItem.parentId].leftSide + '<TD CLASS="arbre_td"><A HREF="javascript:openItem(' + this.id + ',' + objItem.id + ');void(0);" target="_self"><IMG HEIGHT="' + this.heightItem + '" SRC="'+this.img_last_node_open+'" ID="'  +  imageName + '" NAME="'  +  imageName + '" BORDER="0"></A></TD>';
			} else {
				htmlCode = htmlCode + this.all[objItem.parentId].leftSide + '<TD CLASS="arbre_td"><A HREF="javascript:openItem(' + this.id + ',' + objItem.id + ');void(0);" target="_self"><IMG HEIGHT="' + this.heightItem + '" SRC="'+this.img_node_open+'" ID="'  +  imageName + '" NAME="'  +  imageName + '" BORDER="0"></A></TD>';
			}
		} else {
			if (objItem.lastPosition) {
				htmlCode = htmlCode + this.all[objItem.parentId].leftSide + '<TD CLASS="arbre_td"><IMG HEIGHT="' + this.heightItem + '" SRC="'+this.img_last_item+'" ID="'  +  imageName + '" NAME="'  +  imageName + '"></TD>';
			} else {
				htmlCode = htmlCode + this.all[objItem.parentId].leftSide + '<TD CLASS="arbre_td"><IMG HEIGHT="' + this.heightItem + '" SRC="'+ this.img_item +'" ID="'  +  imageName + '" NAME="'  +  imageName + '"></TD>';
			}
		}
	}
	if (whithChild) {
		htmlCode = htmlCode + linkContainerStart + '&nbsp;<A HREF="javascript:clickOnItem(' + this.id + ',' + objItem.id + ');void(0);" CLASS="' + this.nodeClass + '" target="_self">' + objItem.libelle + '</A>&nbsp;' + linkContainerEnd;
	} else {
		htmlCode = htmlCode + linkContainerStart + '&nbsp;<A HREF="javascript:clickOnItem(' + this.id + ',' + objItem.id + ');void(0);" CLASS="' + this.itemClass + '" target="_self">' + objItem.libelle + '</A>&nbsp;' + linkContainerEnd;
	}
	if (this.browser.ie4) {
		htmlCode = htmlCode + '</TR></TABLE></DIV>';
		document.write(htmlCode);
		objItem.style = document.all.item(itemName).style;
		objItem.linkStyle = document.all.item(linkName).style;
		objItem.image = document.all.item(imageName);
	}
	if (this.browser.dom) {
		htmlCode = htmlCode + '</TR></TABLE></DIV>';
		document.write(htmlCode);
		objItem.style = document.getElementById(itemName).style;
		objItem.linkStyle = document.getElementById(linkName).style;
		objItem.image = document.getElementById(imageName);
	}
	objItem.visible = true;
	objItem.state = "open";
}
function arbo_showHideItem(objItem,state) {
	var whithChild, childWidthChild, nbChild, nextIndexId, visibilityTest, visibilityNew, moveEndSens, imgNewSrc, j, objParent;
	if (objItem.level !=0) {
		whithChild = objItem.children.length!=0?true:false;
		nbChild = objItem.subItem();
		nextIndexId = objItem.indexId + nbChild + 1;
		if (state=="close") {
			if (objItem.state == "closed") {return;}
			objItem.state ="closed";
			visibilityTest = this.browser.ie4?"visible":this.browser.ns4?"show":this.browser.dom?"visible":null;
			visibilityNew = this.browser.ie4?"hidden":this.browser.ns4?"hidden":this.browser.dom?"hidden":null;
			moveEndSens = "up";
		} else if (state=="open") {
			if (objItem.state == "open") {return;}
			objItem.state = "open";
			visibilityTest = this.browser.ie4?"hidden":this.browser.ns4?"hide":this.browser.dom?"hidden":null;
			visibilityNew = this.browser.ie4?"visible":this.browser.ns4?"visible":this.browser.dom?"visible":null;
			moveEndSens = "down";
		}
		if (whithChild) {
			if (objItem.lastPosition) {
				imgNewSrc = eval("this.img_last_node_" + objItem.state);
			} else if (!objItem.lastPosition) {
				imgNewSrc = eval("this.img_node_" + objItem.state)
			}
		} else {
			if (objItem.lastPosition) {
				imgNewSrc = this.img_last_item;
			} else if (!objItem.lastPosition) {
				imgNewSrc = this.img_item;
			}
		}
		for (j = (objItem.indexId + 1); j < nextIndexId; j++) {
			objParent = this.all[this.index[j].parentId];
			if (this.index[j].style.visibility.toLowerCase() == visibilityTest) {
				if ((parseInt(this.index[j].level)-1) == objItem.level) {
					this.index[j].style.visibility = visibilityNew;
					if (state == "close") {this.index[j].visible = false;}
					if (state == "open") {this.index[j].visible = true;}
					this.moveEnd(j+1,moveEndSens,(1 * this.heightItem));
					continue;	
				}
				if (state == "open") {
					if (objParent.visible && objParent.state == "open") {
						this.index[j].style.visibility = visibilityNew;
						this.index[j].visible = true;
						this.moveEnd(j+1,moveEndSens,(1 * this.heightItem));
						continue;
					}
				}
				if (state == "close") {
					this.index[j].style.visibility = visibilityNew;
					this.index[j].visible = false;
					this.moveEnd(j+1,moveEndSens,(1 * this.heightItem));
					continue;
				}
			}
			
		}
		objItem.image.src = imgNewSrc;
	}
}
function arbo_moveEnd(firstIndexId,sens,px) {
	var ope, i, newTop, regPx;
	if (px!=0) {
		if (sens == "up") {ope = "-";}
		if (sens == "down") {ope = "+";}
		for (i=firstIndexId; i < this.index.length; i++) {
			newTop = this.index[i].style.top;
			regPx = /px/;
			if (regPx.test(newTop)) {
				newTop = newTop.substring(0,newTop.length-2);
			}
			newTop = eval('parseInt(newTop)' + ope + px);
			this.index[i].style.top = newTop;
		}
	}
}
function arbo_selectItem(objItem) {
	var parentObject, level, bgCProp, transpColor;
	if (objItem.level > 1) {
		parentObject = this.all[objItem.parentId];
		invLevel = new Array();
		for (level = objItem.level; level > 0; level --) {
			invLevel[invLevel.length] = parentObject;
			parentObject = this.all[parentObject.parentId];
		}
		for (parentCount = (invLevel.length-1); parentCount >= 0; parentCount--) {
			this.showHideItem(invLevel[parentCount],"open");
		}
	}
	if (this.selectedColor!="none") {
		if (this.browser.ie4) {
			bgCProp = ".backgroundColor";
			transpColor = "transparent";
		}
		if (this.browser.ns4) {
			bgCProp = ".bgColor";
			transpColor = null;
		}
		if (this.browser.dom) {
			bgCProp = ".backgroundColor";
			transpColor = "transparent";
		}
		if(this.selectedId!=null) {
			eval("this.all[this.selectedId].linkStyle" + bgCProp + " = " + "this.falseTranspColor");
			eval("this.all[this.selectedId].linkStyle" + bgCProp + " = " + "transpColor");
		}
		eval("objItem.linkStyle" + bgCProp + " = \"" + this.selectedColor + "\"");
	}
	this.selectedId = objItem.id;
}
function arbo_loadCookie() {
	var idItem, objItem;
	if (this.cookieName!=null) {
		idItem = GetCookie(this.cookieName);
	} else {
		idItem = null;	
	}
	if ((idItem != null) && (idItem < this.all.length)) {
	 	objItem = this.all[idItem];
	} else {
	 	objItem = this.all[0];
	}
	if (objItem.style==null) {this.build();}
	this.selectItem(objItem);
	if (objItem.level != 0) this.showHideItem(objItem,"open");
}
function arbo_loadItem(objItem) {
	if (objItem.style==null) {this.build();}
	this.selectItem(objItem);
	if (objItem.level != 0) {
		this.showHideItem(objItem,"open");
	}
}
function arbo_expandAll() {
	for (count = 1; count < this.index.length ; count++) {
		this.showHideItem(this.index[count],"open");
	}
	arborescences[this.id] = this;
}
function arbo_expandLevel(iLevel) {
    for (count = 1; count < this.index.length ; count++) {
        if (this.index[count].level < iLevel)
            this.showHideItem(this.index[count],"open");
        else
            this.showHideItem(this.index[count],"close");
    }
    arborescences[this.id] = this;
}
function arbo_collapseAll() {
	for (count = 1; count < this.index.length ; count++) {
		this.showHideItem(this.index[count],"close");
	}
	arborescences[this.id] = this;
}
function Item(libelle,link,target,fetchjsurl) {
	this.id = null;
	this.indexId = null;
	this.level = 0;
	this.arborescenceId = null;
	this.parentId = null;
	this.childrenId = null;
	this.lastPosition = null;
	this.children = new Array();
	this.subItem = item_subItem;
	this.addItem = item_addItem;
	this.state = null;
	this.libelle = libelle?libelle:"Item";
	this.style = null;
	this.linkStyle = null;
	this.image = null;
	this.leftSide = null;
	this.state = null;
	this.link = link?link:null;
	this.target= target?target:null;
    this.fetchjsurl= fetchjsurl?fetchjsurl:null;
	this.visible = null;
	this.setPopup = item_setPopup;
	this.loadPopup = item_loadPopup;
}
function item_subItem() {
	var i, si;
	i = 0;
	si = this.children.length;
	for (i=0; i < this.children.length; i++){
		si = si + this.children[i].subItem();
	}
	return si;
}
function item_addItem(libelle,link,target,fetchjsurl) {
	var structure, subItemObject;
	if (this.arborescenceId == null) {
		alert("Vous ne pouver rajouter un Item sans creer un objet structure de type \"Arborescence\"");
		return false;
	} else {
		structure = arborescences[this.arborescenceId];
		subItemObject = structure.addItem(libelle,link,target,fetchjsurl);
		this.children[this.children.length] = subItemObject;
		subItemObject.childrenId = this.children.length - 1;
		subItemObject.parentId = this.id;
		subItemObject.level = this.level+1;
		if (structure.maxLevel < subItemObject.level) {structure.maxLevel = subItemObject.level;}
		return subItemObject;
	}
}
function item_setPopup(name) {
	this.target="popup";
	this.poName = name?name:"_blank";
}
function item_loadPopup() {
	if (!this.poName) {this.setPopup();}
	window.open(this.link,this.poName);
}
function clickOnItem(ArboId,ItemId) {
	var objArbo, objItem, action;
	if (ArboId < arborescences.length) {
		objArbo = arborescences[ArboId];
		if (ItemId < objArbo.all.length) {
			objItem = objArbo.all[ItemId];
			objArbo.selectItem(objItem);
			if (objItem.level != 0) {

    if (objItem.fetchjsurl != null) {
        fetchjs(objItem.fetchjsurl);
    }

				action = objItem.state=="closed"?"open":"close";
				objArbo.showHideItem(objArbo.all[ItemId],action);
			}
			if (objItem.link != null) {
				if (objArbo.cookieName!=null) {
					SetCookie(objArbo.cookieName, objItem.id, null,"/");
				}
				if (objItem.target=="popup") {
					objItem.loadPopup();
				} else if (objItem.target!=null) {
					eval(objItem.target + " = objItem.link");
				} else {
					eval(objArbo.baseTarget + " = objItem.link ");
				}
			}
		}
	}
}
function openItem(ArboId,ItemId) {
	var objArbo, objItem, action;
	if (ArboId < arborescences.length) {
		objArbo = arborescences[ArboId];
		if (ItemId < objArbo.all.length) {
			objItem = objArbo.all[ItemId];
			if (objItem.level != 0) {

    if (objItem.fetchjsurl != null) {
        fetchjs(objItem.fetchjsurl);
    }

				action = objItem.state=="closed"?"open":"close";
				objArbo.showHideItem(objArbo.all[ItemId],action);
			}
		}
	}
}
function extendEvent(objDOMobject,strEventName,strNewEvent,strPos) {
	var strOldEvent = new String(eval("objDOMobject." + strEventName));
	var intStart = strOldEvent.indexOf("{") + 1;
	var intLen = (strOldEvent.lastIndexOf("}")- intStart);
	var strOldEventBody = strOldEvent.substr(intStart, intLen);
	if (strPos == "before") {
		var strNewEventBody = strNewEvent + strOldEventBody;
	} else if (strPos == "after") {
		var strNewEventBody = strOldEventBody + strNewEvent;
	}
	eval("objDOMobject." + strEventName + " = new Function(strNewEventBody)");
}
function getCookieVal (offset) {
	var endstr = document.cookie.indexOf (";", offset);
	if (endstr == -1) endstr = document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}
function GetCookie (name) {
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen) {
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg) return getCookieVal (j);
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) break; 
	}
	return null;
}
function SetCookie (name,value,expires,path,domain,secure) {
	document.cookie = name + "=" + escape (value) +
		((expires) ? "; expires=" + expires.toGMTString() : "") +
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "") +
		((secure) ? "; secure" : "");
}
