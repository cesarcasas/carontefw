/*========== Scroll2.js ==========*/
/* A workaround for IE issues in mootools 1.2.1
 * - Recreates FX.Scroll() but utilises 1.2.0's getPosition/getOffset routines.
 */
Fx.Scroll2 = new Class({
 
    'Extends': Fx.Scroll,
 
    'styleString': Element.getComputedStyle,
    'styleNumber': function(element, style) {
        return this.styleString(element, style).toInt() || 0;
    },
    'borderBox': function(element) {
        return this.styleString(element, '-moz-box-sizing') == 'border-box';
    },
    'topBorder': function(element) {
        return this.styleNumber(element, 'border-top-width');
    },
    'leftBorder': function(element) {
        return this.styleNumber(element, 'border-left-width');
    },
    'isBody': function(element) {
        return (/^(?:body|html)$/i).test(element.tagName);
    }, 
    'toElement': function(el) {
        var offset   = {x: 0, y: 0};
        var element  = $(el);
 
        if (this.isBody(element)) {
            return offset;
        }
        var scroll = element.getScrolls();
 
        while (element && !this.isBody(element)){
            offset.x += element.offsetLeft;
            offset.y += element.offsetTop;
 
            if (Browser.Engine.gecko){
                if (!this.borderBox(element)){
                    offset.x += this.leftBorder(element);
                    offset.y += this.topBorder(element);
                }
                var parent = element.parentNode;
                if (parent && this.styleString(parent, 'overflow') != 'visible'){
                    offset.x += this.leftBorder(parent);
                    offset.y += this.topBorder(parent);
                }
            } else if (Browser.Engine.trident || Browser.Engine.webkit){
                offset.x += this.leftBorder(element);
                offset.y += this.topBorder(element);
            }
 
            element = element.offsetParent;
            if (Browser.Engine.trident) {
                while (element && !element.currentStyle.hasLayout) {
                    element = element.offsetParent;
                }
            }
        }
        if (Browser.Engine.gecko && !this.borderBox(element)){
            offset.x -= this.leftBorder(element);
            offset.y -= this.topBorder(element);
        }
 
        var relative = this.element;
        var relativePosition = (relative && (relative = $(relative))) ? relative.getPosition() : {x: 0, y: 0};
        var position = {x: offset.x - scroll.x, y: offset.y - scroll.y};
 
        return this.start(position.x - relativePosition.x, position.y - relativePosition.y);
    }
});
