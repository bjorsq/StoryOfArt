/***************************************************
*                                                  *
*   TextSpline || <flow:text>                      *
*                                                  *
*   A class for naive text-wrapping with SVG 1.0   *
*   incorporating text animation                   *
*                                                  *
***************************************************/

/**
 *
 * SVG
 *
 **/
 
function SVG () {
}

SVG.ns = 'http://www.w3.org/2000/svg';

/**
 *
 * TextSpline
 *
 **/

function TextSpline (id, node) {
  this._screenWidth = 1024;
  this._screenHeight = 768;
  this._id = id;
  this._node = node;
  this._string = null;
  this._x = null;
  this._y = null;
  this._width = null;
  this._font = null;
  this._size = null;
  this._align = null;
  this._quality = null;
  this._interval = null;
  this._svg = null;
  this._lines = null;
  this._totalLines = 0;
  this._softLineBreaks = new Array();
  this._indentLine = new Array();
  this._aType = false;
  this._initialized = false;
  this._construct();
}

TextSpline.ns = 'http://www.e-2.org/xmlns/';

TextSpline._instances = new Array();
var TextSplineIDs = new Array();

TextSpline._init = function () {
  var elements = document.documentElement.getElementsByTagNameNS(this.ns, 'soa');
  for (var i=0; i<elements.length; i++) {
    this._instances.push( new TextSpline(i, elements.item(i)) );
    TextSplineIDs.push(elements.item(i).getAttribute('id'));
  }
}

/**
 *
 * Processing methods
 *
 **/

TextSpline.prototype._construct = function () {
  this._build();
  this._svg.setAttribute('style', this._node.getAttribute('style'));
  var style = this._svg.style;
  this._node.normalize();
  
  this.setString( this._node.firstChild.data );
  this.setWidth( parseInt(this._node.getAttribute('width')) );
  this.setTextAlign( style.getPropertyValue('text-align') );
  this.setFontFamily( style.getPropertyValue('font-family') );
  this.setFontSize( style.getPropertyValue('font-size') );
  this.setTextRendering( style.getPropertyValue('text-rendering') );
  this.setLineInterval( style.getPropertyValue('line-interval') );

  this._splitString();
  this._layout();
  this.setPosition( this._x, this._y );
  //alert( (this._x/2) + ' ' + ((this._screenHeight/2) - this._y) + '\n' + ((this._x/2)*19) +  ' ' +  (((this._screenHeight/2) - this._y)*19));
  //this._constructAnimation();
  //probeStartPoints();
  this._initialized = true;
}

TextSpline.prototype._build = function () {
  var element = document.createElementNS(SVG.ns, 'text');
  var node = this._node;
  var nextElement = null;
  while (node.nextSibling) {
    if (node.nextSibling.nodeType == 1) {
      nextElement = node.nextSibling;
      break;
    } else {
      node = node.nextSibling;
    }
  }
  if (nextElement) {
    var test = this._node.parentNode.insertBefore(element, nextElement);
  } else {
    this._node.parentNode.appendChild(element);
  }
  element.appendChild(document.createTextNode(''));
  this._svg = element;
}

TextSpline.prototype._splitString = function () {
  this._hide();
  this._clear();
  var words = this._string.split(' ');
  var lines = new Array();
  var line_lengths = new Array();
  var line = new Array();
  var length = 0;
  var prevLength = 0;  
  while (words.length) {
    var word = words[0];
    this._svg.firstChild.data = line.join(' ') + ' ' + word;
    length = this._svg.getComputedTextLength();
    if ((length > this._width) || (word == "_br_")) {
      if (!words.length) {
        line.push(words[0]);
      }
      if (word == "_br_") {
        this._softLineBreaks[lines.length] = true;
        this._indentLine[lines.length + 1] = true;
        words.shift();
      } else {
        this._softLineBreaks[lines.length] = false;
        this._indentLine[lines.length + 1] = false;
      }
      lines.push( new Line(prevLength, line) );
      line = new Array();
    } else {
      line.push(words.shift());
    }
    line_lengths.push(length);
    prevLength = length;
    if (words.length == 0) {
      lines.push( new Line(length, line) );
    }
  }
  this._lines = lines;
  this._line_lengths = line_lengths;
  this._totalLines = lines.length;
  this._x = (this._screenWidth / 2) - (this._width / 2);
  this._y = (this._screenHeight /2) - (this._lines.length * 9);
}  

TextSpline.prototype._layout = function () {
  this._clear();
  var lines = (new Array(0)).concat(this._lines);
  var anchor = 'middle';
  if (this._align == 'center') {
    anchor = 'middle';  
  } else if (this._align == 'right') {
    anchor = 'end';
  }
  for (var i=0; i<lines.length; i++) {
    var dx = 0;
    var x = 0;    
    line = lines[i]; 
    this._svg.appendChild( document.createTextNode(' ') );
    var tspan = document.createElementNS(SVG.ns, 'tspan');
    tspan.appendChild( document.createTextNode(line._words.join(' ')) ); 
    if (this._align == 'justify') {
      var space;
      /*if (this._indentLine[i] || ((i == 0) && (lines.length > 1))) {
        space = (this._width - 12 - line._width) / (line._words.length - 1);
        x += 1 + 'em';
      } else {*/
      if ((i + 1) == lines.length) {
        space = 'normal';
        if (i == 0) {
          //dx = -(line._width/4);
          //alert(this._width+' '+line._width+' '+dx);
        } else {
          dx = -((this._width - line._width)/2)-1;
        }
      } else {
        space = ((this._width - line._width) / (line._words.length - 1)) / 12 +'em';
      }
      /*}
      space = (i != lines.length - 1 && !this._softLineBreaks[i]) ? space : 0;*/
      tspan.style.setProperty('word-spacing', space);
    } else if (this._align == 'center') {
      anchor = 'middle';
      this._x = (this._screenWidth/2);
      if (lines.length == 1) {
        this._singleLineWidth = line._width;
        this._width = line._width;
      }
    } else if (this._align == 'right') {
      anchor = 'end';
      x = this._width;
    }
    this._x = (this._screenWidth/2);
    tspan.setAttribute('x', x);
    tspan.setAttribute('dx', dx/6+'em');
    tspan.setAttribute('dy', i ? this._interval : '1em');
    this._svg.appendChild(tspan);
  }
  this._svg.style.setProperty('text-anchor', anchor);
  this._show();
}

/**
 *
 * Utility methods
 *
 **/

TextSpline.prototype._hide = function () {
  this._svg.style.setProperty('opacity', '0');
}

TextSpline.prototype._show = function () {
  this._svg.style.setProperty('opacity', '1');
}

TextSpline.prototype._clear = function () {
  while (this._svg.hasChildNodes()) {
    this._svg.removeChild(this._svg.firstChild);
  }
  this._svg.appendChild(document.createTextNode(''));
}

/**
 *
 * GET / SET
 *
 * Getters and setters for CSS properties and XML attributes,
 * will be updated when mutation events will be implemented
 *
 **/
TextSpline.prototype.setPosition = function (x, y)
{
    if (x != this._x) this._x = x;
    if (y != this._y) this._y = y;
    this._svg.setAttribute('transform', 'translate(' + this._x + ' ' + this._y + ')');
}

TextSpline.prototype.getX = function () {
  return this._x;
}

TextSpline.prototype.getY = function () {
  return this._y;
}

TextSpline.prototype.getWidth = function () {
  return this._width;
}

TextSpline.prototype.setWidth = function (width) {
  if (width != this._width) {
    this._width = width;
    if (this._initialized) {
      this._splitString();
      this._layout();
    }
  } 
}

TextSpline.prototype.getAnimationType = function () {
  return this._aType;
}

TextSpline.prototype.setAnimationType = function (aType) {
  for (i = 0; i < this._animations.length; i++) {
    if (aType == this._animations[i]) {
      this._aType = aType;
    }
  }
}

TextSpline.prototype.getTextAlign = function () {
  return this._align;
}

TextSpline.prototype.setTextAlign = function (align) {
  if (align != this._align) {
    this._align = align;
    if (this._initialized) {
      this._layout();
    }
  } 
}

TextSpline.prototype.getString = function () {
  return this._string;
}

TextSpline.prototype.setString = function (string) {
  if (string != this._string) {
    this._string = string;
    if (this._initialized) {
      this._splitString();
      this._layout();
    }
  } 
}

TextSpline.prototype.getFontFamily = function () {
  return this._font;
}

TextSpline.prototype.setFontFamily = function (font) {
  if (font != this._font) {
    this._font = font;
    this._svg.style.setProperty('font-family', this._font);
    if (this._initialized) {
      this._splitString();
      this._layout();
    }
  } 
}

TextSpline.prototype.getFontSize = function () {
  return this._size;
}

TextSpline.prototype.setFontSize = function (size) {
  if (size != this._size) {
    this._size = size;
    this._svg.style.setProperty('font-size', this._size);
    if (this._initialized) {
      this._splitString();
      this._layout();
    }
  } 
}

TextSpline.prototype.getTextRendering = function () {
  return this._quality;
}

TextSpline.prototype.setTextRendering = function (quality) {
  if (quality != this._quality) {
    this._quality = quality;
    this._svg.style.setProperty('text-rendering', this._quality);
    if (this._initialized) {
      this._splitString();
      this._layout();
    }
  } 
}

TextSpline.prototype.getLineInterval = function () {
  return this._interval;
}

TextSpline.prototype.setLineInterval = function (interval) {
  if (interval != this._interval) {
    this._interval = interval;
    if (this._initialized) {
      var element = this._svg.firstChild;
      var count = 0;
      while (element) {
        if (element.nodeName == 'tspan') {
          if (count) {
            element.setAttribute('dy', this._interval);
          }
          count++;
          if (count == this._lines.length) {
            break;
          }
        }
        element = element.nextSibling;
      }
    }
  }
}

/*****
 *
 * Line
 *
 *****/
 
function Line (width, words) {
  this._width = width;
  this._words = words;
}

/*****
 *
 * Animation
 *
 *****/
TextSpline.prototype._constructAnimation = function ()
{
  this._aType = this._node.getAttribute('animateType').toLowerCase();
  switch (this._aType) {
    case "zoom":
      var fadeDuration = parseInt( this._node.getAttribute('fadeDuration'));
      var afterId = parseInt( this._node.getAttribute('startsAfter'));
      var startPause = parseInt( this._node.getAttribute('startPause'));
      var dur1 = parseInt( this._node.getAttribute('firstZoomLength'));
      var readPause = parseInt( this._node.getAttribute('readPause'));
      var dur2 = parseInt( this._node.getAttribute('secondZoomLength'));
      if (fadeDuration > dur2) fadeDuration = dur2;
      var moveLeft = this._singleLineWidth? ((this._width - this._singleLineWidth) / 2): (this._width / 2);
      var moveUp = this._totalLines * 9;
      //initial animation - set element - shows the text
      var set1 = document.createElement('set');
      set1.setAttribute('attributeName', 'visibility');
      set1.setAttribute('id', 'showIt' + this._id);
      set1.setAttribute('attributeType', 'CSS');
      var startTime = afterId? 'hideIt' + afterId + '.begin+' + startPause + 's': '0s';
      set1.setAttribute('begin', startTime);
      set1.setAttribute('end', 'indefinite');
      set1.setAttribute('to', 'visible');
      this._node.parentNode.appendChild(set1);
      // first animation - zooms in
      var scale1 = document.createElement('animate');
      scale1.setAttribute('attributeName', 'font-size');
      scale1.setAttribute('id', 'scale1_' + this._id);
      scale1.setAttribute('attributeType', 'CSS');
      scale1.setAttribute('begin', 'showIt' + this._id + '.begin');
      scale1.setAttribute('dur', dur1 + 's');
      scale1.setAttribute('fill', 'remove');
      scale1.setAttribute('calcMode', 'spline');
      scale1.setAttribute('keyTimes', '0;1');
      scale1.setAttribute('values', '0em;1em');
      scale1.setAttribute('keySplines', '0,0.1,0.1,1');
      this._node.parentNode.appendChild(scale1);
      //first animation - motion path
      var path1 = document.createElement('animateMotion');
      path1.setAttribute('id', 'path1_' + this._id);
      path1.setAttribute('path', 'M ' + moveLeft + ' ' + moveUp + ' L 0 0');
      path1.setAttribute('begin', 'showIt' + this._id + '.begin');
      path1.setAttribute('dur', dur1 + 's');
      path1.setAttribute('fill', 'remove');
      path1.setAttribute('calcMode', 'spline');
      path1.setAttribute('keyPoints', '0;1');
      path1.setAttribute('keySplines', '0,0.1,0.1,1');
      this._node.parentNode.appendChild(path1);
      // second animation - zooms to finish
      var scale2 = document.createElement('animate');
      scale2.setAttribute('attributeName', 'font-size');
      scale2.setAttribute('id', 'scale2_' + this._id);
      scale2.setAttribute('attributeType', 'CSS');
      scale2.setAttribute('begin', 'scale1_' + this._id + '.end+' + readPause + 's');
      scale2.setAttribute('dur', dur2 + 's');
      scale2.setAttribute('fill', 'remove');
      scale2.setAttribute('calcMode', 'spline');
      scale2.setAttribute('keyTimes', '0;1');
      scale2.setAttribute('values', '1em;20em');
      scale2.setAttribute('keySplines', '0.6,0,1,0.4');
      this._node.parentNode.appendChild(scale2);
      // second animation - motion path
      var path2 = document.createElement('animateMotion');
      path2.setAttribute('id', 'path2_' + this._id);
      path2.setAttribute('path', 'M 0 0 L -' + (moveLeft * 19) + ' -' + (moveUp * 19));
      path2.setAttribute('begin', 'scale2_' + this._id + '.begin');
      path2.setAttribute('dur', dur2);
      path2.setAttribute('fill', 'remove');
      path2.setAttribute('calcMode', 'spline');
      path2.setAttribute('keyPoints', '0;1');
      path2.setAttribute('keySplines', '0.6,0,1,0.4');
      this._node.parentNode.appendChild(path2);
      // final animation - 3 second fade out
      var fadeout = document.createElement('animate');
      fadeout.setAttribute('attributeName', 'fill');
      fadeout.setAttribute('id', 'fadeOut' + this._id);
      fadeout.setAttribute('attributeType', 'CSS');
      fadeout.setAttribute('begin', 'scale2_' + this._id + '.begin+' + (dur2 - fadeDuration) + 's');
      fadeout.setAttribute('dur', fadeDuration + 's');
      fadeout.setAttribute('fill', 'remove');
      fadeout.setAttribute('from', '#000');
      fadeout.setAttribute('to', '#fff');
      this._node.parentNode.appendChild(fadeout);
      // final animation - set element - hides the text
      var set2 = document.createElement('set');
      set2.setAttribute('attributeName', 'visibility');
      set2.setAttribute('id', 'hideIt' + this._id);
      set2.setAttribute('attributeType', 'CSS');
      set2.setAttribute('begin', 'fadeOut' + this._id + '.end');
      set2.setAttribute('end', 'indefinite');
      set2.setAttribute('to', 'hidden');
      this._node.parentNode.appendChild(set2);
      break;
    case "slide":
      var afterId = parseInt( this._node.getAttribute('startsAfter'));
      var enterFrom = this._node.getAttribute('enterFrom').toLowerCase();
      var exitTo = this._node.getAttribute('exitTo').toLowerCase();
      var startPause = parseInt( this._node.getAttribute('startPause'));
      var dur1 = parseInt( this._node.getAttribute('firstSlideLength'));
      var readPause = parseInt( this._node.getAttribute('readPause'));
      var dur2 = parseInt( this._node.getAttribute('secondSlideLength'));
      var moveLeft = this._singleLineWidth? this._singleLineWidth / 2: (this._width / 2);
      var moveUp = this._totalLines * 9;
      var path_1, path_2;
      switch (enterFrom) {
        case "top":
          path_1 = 'M 0 -' + ((this._totalLines * 18) + (this._screenHeight / 2)) + ' L 0 0';
          break;
        case "bottom":
          path_1 = 'M 0 ' + ((this._totalLines * 18) + (this._screenHeight / 2)) + ' L 0 0';
          break;
        case "right":
          path_1 = 'M ' + ((this._screenWidth / 2) + (this._width / 2)) + ' 0 L 0 0';
          break;
        default:
          path_1 = 'M -' + ((this._screenWidth / 2) + (this._width / 2)) + ' 0 L 0 0';
          break;
      }
      switch (exitTo) {
        case "top":
          path_2 = 'M 0 0 L 0 -' + ((this._totalLines * 18) + (this._screenHeight / 2));
          break;
        case "bottom":
          path_2 = 'M 0 0 L 0 ' + ((this._totalLines * 18) + (this._screenHeight / 2));
          break;
        case "left":
          path_2 = 'M 0 0 L -'+ ((this._screenWidth / 2) + (this._width / 2)) + ' 0';
          break;
        default:
          path_2 = 'M 0 0 L '+ ((this._screenWidth / 2) + (this._width / 2)) + ' 0';
          break;
      }
      //initial animation - set element - shows the text
      var set1 = document.createElement('set');
      set1.setAttribute('attributeName', 'visibility');
      set1.setAttribute('id', 'showIt' + this._id);
      set1.setAttribute('attributeType', 'CSS');
      var startTime = afterId? 'hideIt' + afterId + '.begin+' + startPause + 's': '0s';
      set1.setAttribute('begin', startTime);
      set1.setAttribute('end', 'indefinite');
      set1.setAttribute('to', 'visible');
      this._node.parentNode.appendChild(set1);
      //first animation - motion path
      var path1 = document.createElement('animateMotion');
      path1.setAttribute('id', 'path1_' + this._id);
      path1.setAttribute('path', path_1);
      path1.setAttribute('begin', 'showIt' + this._id + '.begin');
      path1.setAttribute('dur', dur1 + 's');
      path1.setAttribute('fill', 'remove');
      path1.setAttribute('calcMode', 'spline');
      path1.setAttribute('keyPoints', '0;1');
      path1.setAttribute('keySplines', '0,0.5,0.5,1');
      this._node.parentNode.appendChild(path1);
      // second animation - motion path
      var path2 = document.createElement('animateMotion');
      path2.setAttribute('id', 'path2_' + this._id);
      path2.setAttribute('path', path_2);
      path2.setAttribute('begin', 'path1_' + this._id + '.end+' + readPause + 's');
      path2.setAttribute('dur', dur2);
      path2.setAttribute('fill', 'remove');
      path2.setAttribute('calcMode', 'spline');
      path2.setAttribute('keyPoints', '0;1');
      path2.setAttribute('keySplines', '0.5,0,1,0.5');
      this._node.parentNode.appendChild(path2);
      // final animation - set element - hides the text
      var set2 = document.createElement('set');
      set2.setAttribute('attributeName', 'visibility');
      set2.setAttribute('id', 'hideIt' + this._id);
      set2.setAttribute('attributeType', 'CSS');
      set2.setAttribute('begin', 'path2_' + this._id + '.end');
      set2.setAttribute('end', 'indefinite');
      set2.setAttribute('to', 'hidden');
      this._node.parentNode.appendChild(set2);
      break;
    case "appear":
      var afterId = parseInt( this._node.getAttribute('startsAfter'));
      var fadeInDuration = parseInt( this._node.getAttribute('fadeInDuration'));
      var startPause = parseInt( this._node.getAttribute('startPause'));
      var readPause = parseInt( this._node.getAttribute('readPause'));
      var fadeOutDuration = parseInt( this._node.getAttribute('fadeOutDuration'));
       //initial animation - set element - shows the text
      var set1 = document.createElement('set');
      set1.setAttribute('attributeName', 'visibility');
      set1.setAttribute('id', 'showIt' + this._id);
      set1.setAttribute('attributeType', 'CSS');
      var startTime = afterId? 'hideIt' + afterId + '.begin+' + startPause + 's': '1s';
      set1.setAttribute('begin', startTime);
      set1.setAttribute('end', 'indefinite');
      set1.setAttribute('to', 'visible');
      this._node.parentNode.appendChild(set1);
      // final animation - set element - hides the text
      var set2 = document.createElement('set');
      set2.setAttribute('attributeName', 'visibility');
      set2.setAttribute('id', 'hideIt' + this._id);
      set2.setAttribute('attributeType', 'CSS');
      set2.setAttribute('begin', 'showIt' + this._id + '.end');
      set2.setAttribute('end', 'indefinite');
      set2.setAttribute('to', 'hidden');
      this._node.parentNode.appendChild(set2);
      break;      
  }
}
/*var beginTriggers = new Array();
var firstElement;
var startElements = new Array();
function probeStartPoints()
{
  for (var i = 0; i < TextSplineIDs.length; i++) {
    startElements[i] = document.getElementById('showIt' + TextSplineIDs[i]);
    beginTriggers[i] = startElements[i].getAttribute('begin');
    if (beginTriggers[i].indexOf('end') == -1) {
       firstElement = i;
    }
  }
  startClock();
}
var startTime = new Date();
var elapsedTime = 0;
function startClock()
{
  setInterval("updateElapsedTime()", 1000);
}
function updateElapsedTime()
{
  var timeNow = new Date();
  elapsedTime = (timeNow - startTime) / 1000;
}
function pause()
{
  for (var i = 0; i < startElements.length; i++) {
    startElements[i].setAttribute('begin', 'never');
  }
}
function start()
{
  for (var i = 0; i < startElements.length; i++) {
    startElements[i].setAttribute('begin', beginTriggers[i]);
  }
  startElements[firstElement].setAttribute('begin', (elapsedTime + 2) + 's');
}*/