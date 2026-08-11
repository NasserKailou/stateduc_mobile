/**
 * Drag Module
 * @ 2002-2004 Garrett Smith
 *
 * You may use this for evaluation and testing.
 * 
 * Listener by Aaron Boodman,
 * http://www.youngpup.net/projects/dhtml/listener/
 */


/** DragObj
 *  is used for dragging absolutely positioned elements.
 *  constructing a DragObj will allow realtively or 
 *  absolutely positioned elements to be dragged.
 *
 *  @param el         type: HTMLElement the element to drag
 *  @param constraint type: int 
 *  @see DragObj.constraints
 */
DragObj = function(el, constraint){

	this.isRel = getStyle(el, "position").toLowerCase() == "relative";
	this.container = (this.isRel ? el.parentNode : getContainingBlock(el));
	this.el = el;
	this.dropTargets = [];
	this.css = el.style;
	this.handle = this.el;
	if(constraint) 
		this.constraint = constraint;
	this.origConstraint = this.constraint;
	
	this.zIndex = getStyle(el, "z-index") || DragObj.highestZIndex++;
	this.css.zIndex = this.zIndex;
	this.id = el.id;
	if(ua.ie)
		setIeTopLeft(el);
};

DragObj.instances = [];
DragObj.getInstance = function(el, constraint) {

	if(!el.id)
		el.id = "DragObj" + DragObj.instances.length;

	var instance = DragObj.instances[el.id];
	if(instance == null)
		instance = DragObj.instances[el.id] 
			= DragObj.instances[DragObj.instances.length] = new DragObj(el, constraint);
	return instance;
};

DragObj.constraints = {

	NONE : 0,

	HORZ : 1,
	LEFT : 3,
	RIGHT: 5,
	
	VERT : 2,
	UP   : 4,
	DOWN : 6
	
};

DragObj.prototype = {

	x : 0,
	y : 0,
	_origX : 0,
	_origY : 0,
	
	/** Where it will move to next. onbeforedrag */
	newX : 0, 
	newY : 0,

	/**
	 * returns position of where element is initially dragged from.
	 */
	origX : function() { return this._origX; },
	origY : function() { return this._origY; },
		
	isDragEnabled : true,
	
	dropTargets : [],
	
	onbeforedragstart : function(e){ return true; },
	ondragstart : function(e){},
	
	/** Being dragged */
	ondrag : function(e){},
	/** Will be dragged */
	onbeforedrag : function(e){ return true; },

	/** Dragging stopped before it escaped its container. */
	ondragstop : function(e){},

	/** Dragging completed (as a result of mouseup). */
	ondragend : function(e){},

	/** Hit a droptarget. */
	ondragdrop : function(e){ },

	keepInContainer : false,
	onbeforeexitcontainer : function() { return !this.keepInContainer; },
	
	setOrigX : function(origX) {
		this._origX = origX;
	},
	setOrigY : function(origY) {
		this._origY = origY;
	},
	
	getPosLeft : function() {
		return this.css.pixelLeft || parseInt(getStyle(this.el, "left"))||0;
	},
	
	getPosTop : function() {
		return this.css.pixelTop || parseInt(getStyle(this.el, "top"))||0;
	},
	

	constraint : DragObj.constraints.NONE,
	
	enableDrag : function() {
		this.isDragEnabled = true;
	},
		
	disableDrag : function() {
		this.isDragEnabled = false;		
	},
	
	isBeingDragged : false,
	
	handle : null,
	
	hasHandleSet : false,
	isDragStopped : false,
	
	useHandleTree : true,
	
	setHandle : function(el, setHandleTree){
		this.handle = el;
		this.hasHandleSet = true;
		this.useHandleTree = setHandleTree;
	},
	
	getContainerWidth : function() {
		return this.container.clientWidth || parseInt(getStyle(this.container, "width"))
	},
	
	getContainerHeight : function() {
		return this.container.clientHeight || parseInt(getStyle(this.container, "height"));
	},
		
	addDropTarget : function(el) {
		return this.dropTargets[this.dropTargets.length] = 
			this.dropTargets[el.id] = new DropTarget(el, this);
	},
	
	dropCoords : function(e) {
			if(this.useDropTargetCoords) 
				return { x:getOffsetLeft(this.el), y: getOffsetTop(this.el) };
			else // Default: Use cursor position.
				return { x:eventPageX(e), y:eventPageY(e) };
	},
	
	moveToX : function(x) {
		this.css.left = (this.x = x) + "px";
	},
	
	moveToY : function(y) {
		this.css.top = (this.y = y) + "px";
	},

	setContainer : function(el) {
		//var newEl = this.el.parentNode.removeChild(this.el);
		//(this.container = el).appendChild();
		this.container = el;
	},
	
	removeDropTarget : function(el){
		
		var newTargets = new Array(this.dropTargets.length-1);
		var removed = null;
		
		for(var i = 0, len = this.dropTargets.length; i < len; i++)
			if(this.dropTargets[i].el == el)
				removed = el;
			else newTargets[i] = new DropTarget(el);
		return removed;
	},
			
	getDropTarget : function(el){
		return this.dropTargets[el.id];
	},
	
	toString : function() {
		return this.id;
	}
};

DragObj.highestZIndex = 1000;


/** DropTarget
 *
 * properties:
 *  el  -  the HtmlElement
 * 
 * methods: 
 * getX()
 * getY()
 * getWidth()
 * getHeight()
 */
DropTarget = function(el, dragObj){

	this.el = el;
	
	this.id = el.id ? 
		el.id : dragObj.id 
			+ "$DropTarget"+dragObj.dropTargets.length;
};
DropTarget.prototype = {

	getX : function(){ return getOffsetLeft(this.el) - 
		(ua.isNotUsingPaddingEdge ? parseInt(getStyle(this.el, "padding-left"))||0 : 0);
	},
	getY : function(){ return getOffsetTop(this.el) - 
		(ua.isNotUsingPaddingEdge ? parseInt(getStyle(this.el, "padding-top"))||0 : 0);
	},
	getWidth : function(){ return this.el.offsetWidth; },
	getHeight : function(){ return this.el.offsetHeight; },

	/** Dragged over a droptarget **/
	ondragover : function(e){ }, 
	ondragout : function(e){ },

	ondrop : function(e){ }
};
DropTarget.prototype.ondragover.isSet = false;

DropTarget.instances = {};

DropTarget.getInstance = function(el) {
	var instance = DropTarget.instances[el.id];
	if(instance == null)
		instance = DropTarget.instances[el.id] = new DropTarget(el);
	return instance;
};

/** DragHandlers
 *
 * properties:
 *   
 * 
 * methods: 
 * mouseDown - initializes dragging
 *
 * mouseMove - tracks the mouse position and updates dO
 *
 * mouseUp   - releases any dO and calls ondragend,
 *             passing the event. The event has a dropTarget 
 *             property, which may be null.
 * 
 */

DragHandlers = {
	
	dO : null,

	instances : [],
	
	inited : false,
	
	init : function(){
		
		if(this.inited)
			return;
		
		Listener.add(document, "onmousedown", this.mouseDown);
		Listener.add(document, "onmousemove", this.mouseMove);
		Listener.add(document, "onmouseup", this.mouseUp);
		
 // prevent text selection while dragging.
		document.onselectstart = document.ondragstart = function() { return DragHandlers.dO == null; };
		
		this.inited = true;
	},

	
	mouseDown : function(e) {
		
		if(!e) 
			e = window.event;
			
		var target = getTarget(e);			

		var dO = null; 
		for(var testNode = target;dO == null && testNode != null;
		                            testNode = findAncestorWithAttribute(testNode, "id", "*")) {
		    if(testNode != null)
				dO = DragObj.instances[testNode.id];
		}
		
 		if(dO == null)
 			return true;
 		
 		var handle = dO.handle;

 		function isInHandle() { 
 			return target == dO.el || dO.useHandleTree && contains( dO.handle, target );
 		}
 		
 		if(  dO.hasHandleSet && !isInHandle( dO ) )
 			return false;
 		
		if(e && e.preventDefault)
			e.preventDefault();

 		DragHandlers.dO = dO;
		if(false == dO.onbeforedragstart(e)) return false;
		
		dO.setOrigX(dO.getPosLeft());
  		dO.setOrigY(dO.getPosTop());
		
		dO.css.zIndex = ++DragObj.highestZIndex;
		
		DragHandlers.mousedownX = (e.pageX ? e.pageX : e.clientX + getScrollLeft());
		DragHandlers.mousedownY = (e.pageY ? e.pageY : e.clientY + getScrollTop());
		
		// subtract in-flow offsets.
		var inFlowOffsetX = dO.el.offsetLeft - dO.getPosLeft() 
			+ getOffsetLeft(getContainingBlock(dO.el), dO.container); 

		var inFlowOffsetY = dO.el.offsetTop - dO.getPosTop()
			+ getOffsetTop(getContainingBlock(dO.el), dO.container); 
		
		// Impl Note: Don't use margins for absolutely positioned elements for Safari.
		// Safari calculates offsetTop from parentNode border edge (not padding edge).

  		// Safari can't read style values from styleSheets.
  		// Safari also adds parentNode border-width to offsetLeft. 
  		// This poor approach can only work if the parentNode has no border.
		if(ua.safari && !dO.isRel) {
			if(!getStyle(dO.el, "left")) {
				dO.setOrigX(dO.el.offsetLeft);
				inFlowOffsetX = 0;
			}

			if(!getStyle(dO.el, "top")) {
				dO.setOrigY(dO.el.offsetTop);
				inFlowOffsetY = 0;
			}
		}
		
		if(ua.macIE || ua.safari) {
			// Safari should need this patch, but this patch fails because safari can't read styles.
			inFlowOffsetX -= parseInt(getStyle(dO.el, "border-left-width"))||0;
			inFlowOffsetY -= parseInt(getStyle(dO.el, "border-top-width"))||0;
		}
		
		if(ua.isNotUsingPaddingEdge)  { // Mac IE and Opera.
			inFlowOffsetX -= parseInt(getStyle(dO.el, "padding-left")) || 0;
			inFlowOffsetY -= parseInt(getStyle(dO.el, "padding-top")) || 0;
		}
		
		dO.minX = 0 - inFlowOffsetX;
		dO.maxX = dO.getContainerWidth() - dO.el.offsetWidth - inFlowOffsetX;
		dO.minY = 0 - inFlowOffsetY;
		dO.maxY = dO.getContainerHeight() - dO.el.offsetHeight - inFlowOffsetY;
		
 		dO.isBeingDragged = false;
		
		return false;
	},

	mouseMove : function(e) {

		var dO = DragHandlers.dO;
		
		if(e == null)
			e = event;
		
		if(dO == null || dO.css == null || !dO.isDragEnabled)
			return;
 
		var distX = eventPageX(e) - DragHandlers.mousedownX;
		var distY = eventPageY(e) - DragHandlers.mousedownY;
				
 		dO.newX = dO.origX() + distX;
		dO.newY = dO.origY() + distY;
		
		// drag the bitch.
		
		if(dO.isBeingDragged == false)
			dO.ondragstart(e);
		dO.isBeingDragged = true;
		
		
		// Drag constraints. 
		//if(dO.container != null)
		//	dO.keepInContainer();
		
		var constraints = DragObj.constraints;
				
		var isLeft = dO.newX < dO.minX;
		var isRight = dO.newX > dO.maxX;
		var isAbove = dO.newY < dO.minY;
		var isBelow = dO.newY > dO.maxY;
 		
		if(dO.onbeforedrag(e) == false) return;
		
		var isOutsideContainer = dO.container != null;
		
		if(dO.constraint == DragObj.constraints.NONE) { // no constraint. Life is hard.
			
			isOutsideContainer &= ( isLeft || isRight || isAbove || isBelow );

			
			if(isOutsideContainer && dO.onbeforeexitcontainer() == false) {
				
				var isDragged = false;
				if(isLeft) {  
					if(!dO.isAtLeft) {
						dO.moveToX( dO.minX );
						dO.isAtRight = false;
						dO.isAtLeft = true;
					}
				}
				else if(isRight) {
					if(!dO.isAtRight) {
						dO.moveToX( dO.maxX );
						dO.isAtRight = true;
						dO.isAtLeft = false;
					}
				}
				else {
					dO.isAtLeft = dO.isAtRight = false;
					dO.moveToX( dO.newX );
					isDragged = true;
				}
				if(isAbove) {
					if(!dO.isAtTop) {
						dO.moveToY( dO.minY );
						dO.isAtTop = true;
						dO.isAtBottom = false;
					}
				}
				else if(isBelow) {
					if(!dO.isAtBottom) {
						dO.moveToY( dO.maxY );
						dO.isAtTop = false;
						dO.isAtBottom = true;
					}
				}
				else {
					dO.isAtTop = dO.isAtBottom = false;
					dO.moveToY( dO.newY );
				}
					
				if(!dO.isDragStopped) {
					if(isDragged)
						dO.ondrag(e);
					dO.ondragstop(e);
					dO.isDragStopped = true;
				}
			}
			else {			// In container.
				dO.ondrag(e);
				dO.isDragStopped = dO.isAtLeft = dO.isAtRight =
					dO.isAtTop = dO.isAtBottom = false;
				dO.moveToX( dO.newX );
				dO.moveToY( dO.newY );
			}
			
		}
		
		else {  // A constraint. 
		
			// A VERT type constraint? 
			if(dO.constraint % 2 == 0) {
			  
				isOutsideContainer &= (isAbove || isBelow);
				if(isOutsideContainer && dO.onbeforeexitcontainer() == false) {
					if(isAbove) {
						if(!dO.isAtTop) {
							dO.moveToY( dO.minY );
							dO.isAtTop = !(dO.isAtBottom = false);
						}
					}
					else if(isBelow) {
						if(!dO.isAtBottom) {
							dO.moveToY( dO.maxY );
							dO.isAtBottom = !(dO.isAtTop = false);
						}
					}

					if(!dO.isDragStopped) {
						dO.ondragstop(e);
						dO.isDragStopped = true;
					}
				}
				else { // in container.
					dO.isAtTop = dO.isAtBottom = false;
					dO.isDragStopped = false;
					dO.moveToY( dO.newY );
					dO.ondrag(e);
				}
			}
			
			// A HORZ type constraint? 
			else {
			  
				isOutsideContainer &= (isLeft || isRight);
				
				if(isOutsideContainer && dO.onbeforeexitcontainer() == false) {
	
					if(isLeft) {  
						if(!dO.isAtLeft) {
							dO.moveToX( dO.minX );
							dO.isAtLeft = !(dO.isAtRight = false);
						}
					}
					else if(isRight) {
						if(!dO.isAtRight) {
							dO.moveToX( dO.maxX );
							dO.isAtRight = !(dO.isAtLeft = false);
						}
					}
					
					
					if(!dO.isDragStopped) {
						dO.ondragstop(e);
						dO.isDragStopped = true;
					}
 				}
				else {			// In container.
					dO.isAtLeft = dO.isAtRight = false;
					dO.isDragStopped = false;
					dO.moveToX( dO.newX );
					dO.ondrag(e);
				}
				
			}
		}
		
		// Handle dropTarget dragOver
		var dropTargets = dO.dropTargets;
		if(dropTargets.length > 0)  {
	
			for(var i = 0, j = dropTargets.length; i < j; i++) {
				var dt = dropTargets[i];
				if(dt.ondragover.isSet == false) continue;
				var isInTarget = isInside(dO.dropCoords(e), dt);
				
				// Did we just move over this dropTarget?
				if(!dt.isDropTargetOn && isInTarget) {
					dt.isDropTargetOn = true;
					dt.ondragover(e, dO);
				}
				else { // Were we previously over this dropTarget?
					if(dt.isDropTargetOn && !isInTarget) { 
						dt.ondragout(e, dO);
						dt.isDropTargetOn = false;
					}
				}
			}
		}
		return false;
	},
	
	mouseUp : function(e) {
		
		if(DragHandlers.dO == null)
			return true;

		if(e == null)
			e = window.event;
		
		var dO = DragHandlers.dO;

		var el = dO.el;

		var targets = dO.dropTargets;
		if(targets.length > 0) {

			var dropTarget;
			for(var i = 0, len = targets.length; i < len; i++)
				if(targets[i].isDropTargetOn || isInside(dO.dropCoords(e), targets[i])) {
					dropTarget = targets[i];
					dropTarget.ondrop(e, dO);
					break;
				}
		}

		// Deprecated line - ondrop handler now on dropTarget.
		e.dropTarget = dropTarget;
		
		dO.ondragend(e);
		
		DragHandlers.dO = null;

		// Deprecated line - ondrop handler now on dropTarget.
		if(dropTarget != null) 
			dO.ondragdrop(e);
	}
};

/** isInside checks to see if the coordinates 
 *   x and y are both inside dropTarget
 */
function isInside(curs, dropTarget){
		 // check for x, then y.
		return (curs.x >= dropTarget.getX()
		 && curs.x < dropTarget.getX() + dropTarget.getWidth())
		
		&& // now check for y.
			
		(curs.y >= dropTarget.getY()
		 && curs.y < dropTarget.getY() + dropTarget.getHeight());
}

function findAncestorWithAttribute(el, attrName, value) {

	for(var parent = el.parentNode;parent != null;){
	
		if(parent[attrName])
			if(parent[attrName] == value || value == "*")
				return parent;
			
		parent = parent.parentNode;
	}
	return null;
}

function getContainingBlock(el) {

	var root = document.documentElement;
	for(var parent = el.parentNode; parent != null && parent != root;) {
	
		if(getStyle(parent, "position") != "static")
			return parent;
		parent = parent.parentNode;
	}
	return root;
}

function getStyle(el, style) {
	if(!el.style) return;
    var value = el.style[toCamelCase(style)];
	    // IE provides "medium" as default inline-style border value for all border props.
	    // This bogus value should be ignored.
    if(!value || ua.ie && style.indexOf("border") == 0) 
        if(typeof document.defaultView == "object")
            value = document.defaultView.
                 getComputedStyle(el, "").getPropertyValue(style);
       
        else if(el.currentStyle)
            value = el.currentStyle[toCamelCase(style)];
     	
     return value || "";
}

function toCamelCase( sInput ) {
	var sArray = sInput.split('-');
	if(sArray.length == 1)	
		return sArray[0];
	var ret = sArray[0];
	for(var i = 1, len = sArray.length; i < len; i++){
		var s = sArray[i];
		ret += s.charAt(0).toUpperCase()+s.substring(1);
	}
	return ret;
}

//--------------------------- Element location functions ------------------------

/** Returns true if a contains b.
 */
function contains(a, b) {
	while(a != b && (b = b.parentNode) != null);
	return a == b;
}

var ua = new function() {
	var s = navigator.userAgent, d = document, dt = d.doctype ? d.doctype.name : "";
	this.css1 = d.compatMode != "BackCompat" || (/http/.test(d) && d.all);
	this.ie = /MSIE/.test(s);
	this.safari = /Safari/.test(s);
	this.winIE = /Win/.test(s) && this.ie;
	this.macIE = /Mac/.test(s) && this.ie;
	this.moz = /Gecko/.test(s) && !this.safari && !this.ie;
	this.toString=function(){return s;};
	this.isNotUsingPaddingEdge = /Opera/.test(s) || (this.ie && !this.winIE);
};

function getOffsetTop(el, container) {
		if(!container) container = document;
		for(var offsetTop = 0; el && el != container; el = el.offsetParent)
			offsetTop += el.offsetTop;
		return offsetTop;
}

function getOffsetLeft(el, container) {
		if(!container) container = document;
		for(var offsetLeft = 0; el && el != container; el = el.offsetParent) 
			offsetLeft += el.offsetLeft;
		return offsetLeft;
}

//--------------------------- Viewport functions ------------------------

function getScrollTop() {
	return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
}
function getScrollLeft() {
	return window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft;
}

//--------------------------- IE Functions ---------------------------
function setIeTopLeft(el) { 
	// For IE, set top/left values when declared values are auto
	// and right/bottom values are given.
	var cs = el.currentStyle;
	var cb = getContainingBlock(el);
	var curL = cs.left, curR = cs.right, curT = cs.top, curB = cs.bottom;
	
	// Calculate left when right is given pixel value and left is "auto".
	if((curL == "" || curL == "auto") && curR && curR != "auto")
		el.style.left = cb.clientWidth - el.offsetWidth - parseInt(curR) + "px";

	// Calculate top when bottom is given pixel value and top is "auto".
	if((curT == "" || curT == "auto") && curB && curB != "auto")
		el.style.top = cb.clientHeight - el.offsetHeight - parseInt(curB) + "px";
}

//--------------------------- Event functions ------------------------
function getTarget(e){
	return window.event ? 
		window.event.srcElement : e.target.tagName 
			? e.target : e.target.parentNode;
}

function eventPageX(e) { return e.pageX || e.clientX + getScrollLeft(); }
function eventPageY(e) { return e.pageY || e.clientY + getScrollTop(); }

/**
 * Listener - by Aaron Boodman
 * 5/23/2002; Queens, NY.
 * http://www.youngpup.net/projects/dhtml/listener/
 **/

function Listener(fp, oScope) {
	this.id = fp.toString() + oScope;
	this.fp = fp;
	this.scope = oScope;
}

Listener.add = function(oSource, sEvent, fpListener, oScope) {
	if (!oSource[sEvent] || oSource[sEvent] == null || !oSource[sEvent]._listeners) {
		oSource[sEvent] = function() { Listener.fire(oSource, sEvent, arguments) };
		oSource[sEvent]._listeners = [];
	}
	
	if(!oScope) oScope = oSource;
	var idx = this.findForEvent(oSource[sEvent], fpListener, oScope);
	if (idx == -1) idx = oSource[sEvent]._listeners.length;
	
	oSource[sEvent]._listeners[idx] = new Listener(fpListener, oScope);
	oSource[sEvent].isSet = true;
}

Listener.remove = function(oSource, sEvent, fpListener, oScope) {
	var idx = this.findForEvent(oSource[sEvent], fpListener, oScope);
	if (idx != -1) {
		var iLast = oSource[sEvent]._listeners.length - 1;
		oSource[sEvent]._listeners[idx] = oSource[sEvent]._listeners[iLast];
		oSource[sEvent]._listeners.length--;
	}
	oSource[sEvent].isSet = oSource[sEvent]._listeners.length != 0;
};

Listener.findForEvent = function(fpEvent, fpListener, oScope) {
	var listsners = fpEvent._listeners;
	if (listsners) {
		for (var i = 0, len = listsners.length; i < len; i++) {
			var l = fpEvent._listeners[i];
			if (l.scope == oScope && l.fp == fpListener) 
				return i;
		}
	}
	return -1;
};

Listener.fire = function(oSourceObj, sEvent, args) {

	if(!oSourceObj || !oSourceObj[sEvent] || !oSourceObj[sEvent]._listeners) return;

	var lstnr, fp;
	var last = oSourceObj[sEvent]._listeners.length - 1;

	var ret = true;

	// must loop in reverse, because we might be removing elements as we go.

	for (var i = last; i >= 0; i--) {
		lstnr = oSourceObj[sEvent]._listeners[i];
		fp = lstnr.fp;
	
		if(ret != false)
			ret = fp.apply(lstnr.scope, args);
	}
	return ret;
};

// impliment function apply for browsers which don't support it natively
if (!Function.prototype.apply) {
	Function.prototype.apply = function(oScope, args) {
		var sarg = [];
		var rtrn, call;

		if (!oScope) oScope = window;
		if (!args) args = [];

		for (var i = 0; i < args.length; i++) {
			sarg[i] = "args["+i+"]";
		}

		call = "oScope.__applyTemp__(" + sarg.join(",") + ");";

		oScope.__applyTemp__ = this;
		rtrn = eval(call);
		delete oScope.__applyTemp__;
		return rtrn;
	};
};

//DragHandlers.__initString = eval("\x61\x6c\x65\x72\x74\('\x64\x65\x6d\x6f')");
DragHandlers.init();