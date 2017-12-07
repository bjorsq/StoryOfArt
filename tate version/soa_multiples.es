function loop(id)
{
    window.setSrc("soa"+id+".svg");
    return;
}
/***************************************************
*                                                  *
*   TextSpline                                     *
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
  this._defaultWidth = 420;
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
  this._initialized = false;
  this._construct();
}

TextSpline.ns = 'http://www.e-2.org/xmlns/';

TextSpline._instances = new Array();

TextSpline._init = function () {
  var elements = document.documentElement.getElementsByTagNameNS(this.ns, 'soa');
  for (var i=0; i<elements.length; i++) {
    this._instances.push( new TextSpline(i, elements.item(i)) );
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
  this.setTextAlign( style.getPropertyValue('text-align') );
  this.setFontFamily( style.getPropertyValue('font-family') );
  this.setFontSize( style.getPropertyValue('font-size') );
  this.setWidth( this._size * this._defaultWidth );
  this.setTextRendering( style.getPropertyValue('text-rendering') );
  this.setLineInterval( style.getPropertyValue('line-interval') );

  this._splitString();
  this._layout();
  this._setPosition( this._x, this._y );
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
    if (length > this._width || word == "_br_") {
      if (!words.length) {
        line.push(words[0]);
      }
      lines.push( new Line(prevLength, line) );
      line = new Array();
      if (word == "_br_") {
        words.shift();
      }
    } else {
      line.push(words.shift());
    }
    prevLength = length;
    if (words.length == 0) {
      lines.push( new Line(length, line) );
    }
  }
  this._lines = lines;
  this._x = (this._screenWidth / 2) - (this._width / 2);
  this._y = (this._screenHeight / 2) - (this._lines.length * (this._size * 12));
}  

TextSpline.prototype._layout = function () {
  this._clear();
  var lines = (new Array(0)).concat(this._lines);
  for (var i=0; i<lines.length; i++) {
    var dx = 0;
    var x = 0;
    var interval = this._interval ? this._interval + 'em' : (this._size * 1.5) + 'em'
    line = lines[i];
    this._svg.appendChild( document.createTextNode(' ') );
    var tspan = document.createElementNS(SVG.ns, 'tspan');
    tspan.appendChild( document.createTextNode(line._words.join(' ')) ); 
    if ((i + 1) == lines.length) {
      space = 'normal';
      if (i != 0) {
        dx = -((this._width - line._width)/2) - 1;
      } else {
        //single line
        //alert(line._words.join(' ')+'\nthis._y: '+this._y+'\nnew_y: '+(this._y - (this._interval * 12)));
        this._setPosition(this._x, (this._y - (this._size * this._interval * 12)));
      }
    } else {
      space = ((this._width - line._width) / (line._words.length - 1)) / (this._size * 12) +'em';
    }
    tspan.style.setProperty('word-spacing', space);
    this._x = (this._screenWidth/2);
    tspan.setAttribute('x', x);
    tspan.setAttribute('dx', (2 * dx) / (this._size * 12) + 'em');
    tspan.setAttribute('dy', this._interval + 'em');
    this._svg.appendChild(tspan);
  }
  this._svg.style.setProperty('text-anchor', 'middle');
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
TextSpline.prototype._setPosition = function (x, y)
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
    this._svg.style.setProperty('font-size', this._size + 'em');
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
            element.setAttribute('dy', this._interval + 'em');
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

