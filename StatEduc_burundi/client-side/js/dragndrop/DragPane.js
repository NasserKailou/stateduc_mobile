/**
 * This file contains SortableListItem 
 * and DragPane classes.
 * @ 2003-2004 Garrett Smith
 * Requires:
 *  - drag.js
 */
 
 /** SortableListItem is 
 *  a sort of "dummy" class which has a listItem that is a DragObj.
 */
 SortableListItem = function(el) {

	this.el = el;
	this.el.oncontextmenu = function() {
		return false;
	};
	
	this.listItem = DragObj.getInstance(el, DragObj.constraints.VERT);
	this.listItem.useDropTargetCoords = true; // Use dragObj position, not cursor position.
	this.listItem.isRel = true;
	this.listItem.keepInContainer = true;
	
	this.dragPane = DragPane.getInstance(findAncestorWithClass(el, "dragPane"));
	
	var dragPane = this.dragPane;
	this.listItem.onbeforedragstart = function() {
		
		if(DragPane.focusedElement != this.el) {
			if(DragPane.focusedElement != null)
				removeClass(DragPane.focusedElement, "focusedItem");
			this.el.className += " focusedItem";
			DragPane.focusedElement = this.el;
		}
		return true;
	};
	
	this.listItem.onfocus = function() { };
	
	this.listItem.ondragstart = function() {
		this.el.className += " activeDrag";
		this.el.style.zIndex = 1000;
	};	
		
	var items = this.dragPane.items();
	
	
	// TODO: move this code to dragPane class.
	for(var i = 0; i < items.length; i++) {
		if(el != items[i]) {
			var dt = this.listItem.addDropTarget(items[i]);
			var ths = this;
			dt.ondrop = function(e) {
				dragPane.resort(ths.listItem, this);
				dragPane.updateForm();
			};
			
			dt.ondragout = function(e, dragObject) {
				// removeClass(DragPane.activeDropTarget, "itemInsertBefore");
			};
		}
	}
		
	// define accessible variable;
	var dragPane = this.dragPane;
	
	// all done dragging.
	this.listItem.ondragend = function(e) {
		
		// Mac IE offsetTop returns clientTop in css1 mode.
		var macIEAdj = (ua.macIE ? parseInt(getStyle(this.el, "border-top-width"))||1 : 0);
		
		if(this.el.offsetTop < 1 + macIEAdj) {
			dragPane.el.insertBefore(this.el, dragPane.item(0));
		}
		else if(this.el.offsetTop > dragPane.items()[dragPane.items().length - 1].offsetTop) {
			dragPane.el.appendChild(this.el);
		}
		
		this.el.style.top = 0;
		this.el.style.zIndex = "";
		removeClass(this.el, "activeDrag");
	};

	Listener.add(this.listItem, "ondrag", listItemDragged);
	Listener.add(this.listItem, "ondragstop", listItemStopped);
	
};

function isBelowElement(a, b){
	return (getOffsetTop(a) + a.offsetHeight >= getOffsetTop(b) + b.offsetHeight);
}

listItemDragged = function(e) { 
	var isAbove = isAboveVisArea(this.el, 3);
	var padding = ua.winIE ? 3 : 8;
	
	 // IE has problems with scrollIntoView();
	if(ua.ie || (!this.el.scrollIntoView)) { 
		if(isAbove) {
			scrollBy(0, -this.el.offsetHeight);
		}
		if(isBelowVisArea(this.el, padding)) {
			scrollBy(0, this.el.offsetHeight);
		}
	}
	else if(this.el.scrollIntoView) {
		this.el.scrollIntoView(isAbove);
	}
};


/** When the listItem is at the top or bottom of the list, 
 *  scroll the viewport so that the dragPane's respective edge 
 *  (top or bottom) can be viewed.
 */
listItemStopped = function() {

	if(this.isDragStopped) return;
	
	var dy = this.el.offsetHeight * .5;
	
	var isTopViewable = getOffsetTop(this.container) - getScrollTop() >= dy;
	
	if(!isTopViewable && this.el.offsetTop < 1 && getScrollTop() > 0)
		scrollBy(0, -dy);
									
	else {
		var isBottomViewable = getOffsetTop(this.container) 
								+ this.container.offsetHeight + dy 
								- getScrollTop() <= getViewportHeight();
		if(!isBottomViewable && this.el.offsetTop + this.el.offsetHeight > this.container.clientHeight - dy)	
			scrollBy(0, dy);
	}

};

SortableListItem.instances =  {};

SortableListItem.prototype = {

	onfocus : function(){},
	
	focus : function() {
		this.listItem.onbeforedragstart();
		if(ua.safari) 
			preventKbdScroll();
		this.onfocus();
	},
	
	yPosOverlap : function() {
		return getOffsetTop(this.el) - (getViewportHeight() + getScrollTop() - 15);
	},
	
	yNegOverlap : function() { return getOffsetTop(this.el) - getScrollTop(); }

};


SortableListItem.getInstance = function(el) { 
	var x = SortableListItem.instances[el.id];
	if(!x)
		x= SortableListItem.instances[el.id] = new SortableListItem(el);
	return x; 
};


DragPane = function(el) {
	this.el = el;
	if(typeof this.el.style.MozUserSelect == "string")
	this.el.style.MozUserSelect = "none";
	this.form = document.getElementById(this.el.id+"Form");
	this.elements = this.form.elements;

	this.id = el.id;		
	
	// Mac IE returns "undefined" for clientHeight on list elements...
	if(ua.macIE) 
		setGetContainerHeight(this);
	
	DragPane.instances[this.id] = this;
};
DragPane.instances = {};
DragPane.getInstance = function(el) {
	var instance = DragPane.instances[el.id];
	if(instance == null)
		instance = DragPane.instances[el.id] = new DragPane(el);
	return instance;
}

// Mac IE returns "undefined" for clientHeight on list elements,
// so we instead get the distance to the last listItem's bottom edge.
function setGetContainerHeight(dragPane) {
	// Get a reference to the dragPane through a reference to an SLI.
	
	var tagName = dragPane.el.tagName.toLowerCase();
	if(tagName == "ul" || tagName == "ol") {
		DragObj.prototype.getContainerHeight = function() {
		
			var items = dragPane.items();
			var lastItem = items[ items.length-1 ]
			return lastItem.offsetTop + lastItem.offsetHeight 
					+ (parseInt(getStyle(lastItem, "margin-bottom"))||0)
		};
	}
}

DragPane.prototype = {
	
	id : "",
	form : null,
	elements : null,
		
	items : function() { return getElementsWithClass(this.el, "*", "orderingItem"); },
	item : function(i) { return this.items()[i]; },
	resort : function(listItem, dropTarget) {
		// If the element is on the top half of the drop target, insert it before.
		// otherwise, append it.
		if(listItem.el.offsetTop >= dropTarget.el.offsetTop) {
			this.el.insertBefore(listItem.el, dropTarget.el.nextSibling);
		}
		
		else {
 			this.el.insertBefore(listItem.el, dropTarget.el);
 		}
				
		listItem.el.style.top = dropTarget.el.style.top = 0;		
	},
	
	updateForm : function() {
		
		var items = this.items();
		for(var i = 0, len = items.length; i < len; i++) {
			if(!this.elements[items[i].id])
				alert("Form Element \""+items[i].id+"\" does not exist.");
			this.elements[items[i].id].value = i;
		}
	}
};
keyPressed = function(e) {

	
	if(DragPane.focusedElement == null)
		return;
	
	if(!e)
		e = window.event;
	
	var sibling;
	
	var isUpArrow = e.keyCode == 38;
	var isDownArrow = e.keyCode == 40;
	if(isUpArrow) { // up arrow key.
	
		sibling = findPreviousSiblingWithClass(DragPane.focusedElement, "orderingItem");
	}
	else if(isDownArrow) { // down arrow key.
	
		sibling = findNextSiblingWithClass(DragPane.focusedElement, "orderingItem");
		
	}
	if(sibling != null) {

		if(e.altKey && !ua.moz)
			preventKbdScroll();
		else if(ua.safari) { // no alt key, have to scroll manually.
			var dy = DragPane.focusedElement.offsetHeight;
			scrollBy(0, (isUpArrow ? -dy : dy));
		}
		
		var sli = SortableListItem.getInstance(sibling);
		
		if(e.ctrlKey) {
			if(isUpArrow)
				sli.dragPane.el.insertBefore(DragPane.focusedElement, sibling);
			else if(isDownArrow)
				sli.dragPane.el.insertBefore(sibling, DragPane.focusedElement);
		}
		else 
			sli.focus();
	}
	
	if(DragPane.focusedElement.scrollIntoView) {
		var isAbove = isAboveVisArea(DragPane.focusedElement);
		if(isAbove || isBelowVisArea(DragPane.focusedElement)) 
			DragPane.focusedElement.scrollIntoView(isAbove);
	}
};

// Mac IE fires keydown repeatedly onkeypress (keydown mimics keypress).
// Safari doesn't handle keyPress well here.
if(ua.moz) 
	Listener.add(document, "onkeypress", keyPressed);
else // IE (any) doesn't recognize up or down arrows for keypres.
	Listener.add(document, "onkeydown", keyPressed);

// Not quite working in Safari, because we can't capture the 
// altKey before the element is scrolled.
// For Safari, call this in the listItem focus.
function preventKbdScroll() {
	
	if(!preventKbdScroll.dmmy) {
		
		var tagName =  ua.safari ? "input" : "a";
		preventKbdScroll.dmmy = document.createElement(tagName);
		if(!ua.safari)
			preventKbdScroll.dmmy.href = "#";
		
		var sty = preventKbdScroll.dmmy.style;
		sty.outline = 0;
		sty.zIndex =  10;
		if(ua.safari)
	 		sty.visibility = "hidden";
		// We must position the dummy element to prevent 
		// the browser from scrolling to it.
 		var parent = document.forms[0] || document.body;
		parent.appendChild(preventKbdScroll.dmmy);
		preventKbdScroll.parentTop = getOffsetTop(parent);
		
		if(ua.safari)
			document.addEventListener("mouseup", function() { 
				preventKbdScroll.dmmy.focus(); }, true);
	}
	// Put it in middle of the screen.
	preventKbdScroll.dmmy.style.marginTop = 
		(-preventKbdScroll.parentTop + (getViewportHeight()/2) + getScrollTop()) + "px";
	preventKbdScroll.dmmy.focus();
}

DragPane.hoveredElement = null;
DragPane.focusedElement = null;

/**
 * DragPane.documentMouseMove handles mousemove for the dragPane's parent element
 * so we can track when the cursor leaves the dragPane element.
 */
DragPane.documentMouseMove = function(e) {

 	var target = getTarget(e);
	
	var dragPaneTarget = null;
	if(target.className && hasToken(target.className, "dragPane"))
		dragPaneTarget = target;
	else 
		dragPaneTarget = findAncestorWithClass(target, "dragPane");
	if(dragPaneTarget == null)
		return;


	var dragPane = DragPane.getInstance(dragPaneTarget);
	if(!contains(dragPane.el, target) && DragPane.hoveredElement != null){
		removeClass(DragPane.hoveredElement, "hoveredItem"); 
		DragPane.hoveredElement = null;
	}
	
};
Listener.add(document, "mousemove", DragPane.documentMouseMove);


//---------------------------------------------------------------------------


function findPreviousSiblingWithClass(el, className) {

	for(var ps = el.previousSibling; ps != null; ps = ps.previousSibling)
		if(hasToken(ps.className, className)) 
			return ps;
		return null;
}

function findNextSiblingWithClass(el, className) {

	for(var ns = el.nextSibling; ns != null; ns = ns.nextSibling)
		if(hasToken(ns.className, className)) 
			return ns;
		return null;
}

/**
 * Returns an Array of elements with the specified tagName and className.
 */
function getElementsWithClass(parent, tagName, klass){
	var returnedCollection = [];

	var exp = getTokenizedExp(klass,"");
	var collection = (tagName == "*" && parent.all) ?
		parent.all : parent.getElementsByTagName(tagName);
	
	for(var i = 0, counter = 0, len = collection.length; i < len; i++){
		
		if(exp.test(collection[i].className))
			returnedCollection[counter++] = collection[i];
	}
	return returnedCollection;
}


function findAncestorWithClass(el, klass) {
	
	if(el == null)
		return null;
	var exp = getTokenizedExp(klass,"");
	for(var parent = el.parentNode; parent != null;){
	
		if( exp.test(parent.className) )
			return parent;
			
		parent = parent.parentNode;
	}
	return null;
}
function isAboveVisArea(el, adj) { return getOffsetTop(el) - (adj || 0) < getScrollTop(); }
function isBelowVisArea(el, adj) {
	return getOffsetTop(el) + el.offsetHeight + (adj || 0) > getScrollTop() 
		+ (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight);
}
var TRIM_EXP = /^\s+|\s+$/g;
String.prototype.trim = function(){
		return this.replace(TRIM_EXP, "");
};
var WS_MULT_EXP = /\s\s+/g;
String.prototype.normalize = function(){
		return this.trim().replace(WS_MULT_EXP, " ");
};

TokenizedExps = {};
function getTokenizedExp(token, flag){
	var x = TokenizedExps[token];
	if(!x)
		x = TokenizedExps[token] = new RegExp("(^|\\s)"+token+"($|\\s)", flag);
	return x;
}

function hasToken(s, token){
	return getTokenizedExp(token,"").test(s);
}
function removeClass(el, klass){
	el.className = el.className.replace(getTokenizedExp(klass, "g")," ").normalize();
}
function findNextSiblingWithClass(el, className) {

	for(var ns = el.nextSibling; ns != null; ns = ns.nextSibling)
		if(hasToken(ns.className, className)) 
			return ns;
		return null;
}

function getViewportHeight() {
	if(window.innerHeight)
		return window.innerHeight;
		
	// IE treats the root node is the initial containing block.
	if(typeof window.document.documentElement.clientHeight=="number")
		return window.document.documentElement.clientHeight;
	return window.document.body.clientHeight;
}