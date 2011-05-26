Ext.namespace('Ext.fc');
/**
 * A nice way to label your images
 * 
 * @class Ext.fc.labeler
 * @version    0.2
 * @author     Nuno Costa - sven@francodacosta.com
 * @copyright  Copyright (c) 2009
 * @license    GPL v3 - http://www.gnu.org/licenses/gpl-3.0.html
 * @link       http://www.francodacosta.com/extjs/labeler
 * @since      2009-01-28
 */
Ext.fc.labeler = function(){
    
    var defaultConfig = function(){
        return {
            /**
             * @cfg: {string} position : the label position (top, left, right, bottom)
             */
            position  : 'bottom'
            /**
             * @cfg: {boolean} animateOnStart : Label starts animated
             */
            ,animateOnStart: true
            /**
             * @cfg: {boolean} animateOnOver : Animate the label on mouse over / out
             */
            ,animateOnOver: true
            /**
             * @cfg: {boolean} hideLabel : The label starts hidden (animateOnOver should be true)
             */
            ,hideLabel: false
            /**
             * @cfg: {boolean} animation : The type of animation (slidein, fadein)
             */
            ,animation : 'slideIn'
            /**
             * @cfg: {object} animationAnchor : Any valid Ext.Fx anchor
             */
            ,animationAnchor : '{}'
            /**
             * @cfg: {object} animationOptions : Any valid Ext.Fx config options
             */
            ,animationOptions : '{}'
            /**
             * @cfg: {integer} width : The Object's width, use if having problems with automatic detection
             */
            ,width : null
            /**
             * @cfg: {integer} height : The Object's height, use if having problems with automatic detection
             */
            ,height : null
            /**
             * @cfg: {string} nsdrClass : This css class will be added to the label container, usefull if you hant to have different label styles
             */
            ,baseClass : ''
            /**
             * @cfg: {integer} hideTimeout : After x miliseconds the label is hidden, use 0 to disable
             */
            ,hideTimeout: 0
            /**
             * @cfg: {string} text : The label text
             */
            ,text: ''
        }
    };
    
    var parseOptions = function(value){
        value = value.replace(/il/i, '').replace(/[\r\n]+/g,'').replace(/;/,'').replace(/ /g,'');
        
        var o = defaultConfig() ;
        
        var options = value.split(';');
        for (var n = 0; n < options.length ; n++){
            if(options[n].length > 0 ){
                if (options[n].indexOf('{') > 0){
                    var option = [];
                    
                    name = options[n].split(':')[0];
                    start = options[n].indexOf('{') ;
                    end   = options[n].indexOf('}') 
                    value =  ''+options[n].substr(start, end);

                    o[name] = value;
                    
                }else{
                    var option = options[n].split(':');
                    o[option[0]] = option[1]
                }
                
            }
        }
        
        o.animationOptions.concurrent = false;
        
        return o;
    }
    
    var labelSetup = function (el, position){
        
        //console.log(el.width);
        var w = el.width  - parseInt( el.getStyle('padding-left') ) - parseInt( el.getStyle('padding-right')  ) ;
        var h = el.height - parseInt( el.getStyle('padding-top')  ) - parseInt( el.getStyle('padding-bottom') )
        
        switch(position.toLowerCase()){
            case 'left':
                el.applyStyles({
                    position : 'absolute'
                    ,top : '0px'
                    ,left : '0px'
                    ,height: h + 'px'
                    ,width : ( w / 4 ) + 'px'
                });
            break;
                
            case 'right':
                el.applyStyles({
                    position : 'absolute'
                    ,top : '0px'
                    ,left : (3 * w /4)+'px'
                    ,height: h + 'px'
                    ,width : ( w / 4 ) + 'px'
                });
            break;
                
            case 'bottom':
                el.applyStyles({
                    position : 'absolute'
                    ,top : ( el.height - el.getHeight() ) + 'px'
                    ,left :  '0px'
                    ,width : w + 'px'
                });
            break;
                
            case 'top':
            default :
                el.applyStyles({
                    position : 'absolute'
                    ,top : '0px'
                    ,left : '0px'
                    ,width : w + 'px'
                });
            break;
        }
    }
    
    var getAnchor = function (position){
        switch( position.toLowerCase() ) {
            case 'top' :
                return 'T';
                break;
            
            case 'left' :
                return 'L';
                break;
            
            case 'bottom' :
                return 'B';
                break;
            
            case 'right' :
                return 'R';
                break;
        }
    }
    
    return {
            items : []
            ,index : 0 
            ,_apply : function (el, index, options){

                
                el.height = options.height || el. getComputedHeight(true);
                el.width  = options.width  || el.getWidth(true); 
        
                var container = el.insertHtml('beforeBegin','<div></div>', true);
                container.addClass(['fc-labeler-container', options.baseClass]);
                container.applyStyles({
                    width    : el.width + 'px'
                    ,height   : el.height + 'px'
                    ,position : 'relative'
                });
                
                
                
                //add il element to container
                container.appendChild(el);
                
                var p = container.createChild({
                    'tag' : 'div'
                    ,'class' : 'label'
                });
                p.update(el.getAttributeNS('', 'title') || options.text || '');
                
                p.height =  el.height;
                p.width  =  el.width; 
                labelSetup(p, options.position)
                 //console.log(options);
                
                var mouseWrapper = container.createChild({'class' : 'fc-labeler-color-filter'});
                mouseWrapper.applyStyles({
                    width    : el.width + 'px'
                    ,height   : el.height + 'px'
                    ,position : 'absolute'
                    ,top: 0
                    ,left: 0
                    ,zindex: 100000
                });
                if(options.onclick) {
                    mouseWrapper.applyStyles({
                        'cursor': 'hand'
                        ,'cursor':'pointer'
                    });
                    
                    mouseWrapper.on('click', function(){
                        if(typeof (options.onclick) == 'function') {
                            options.onclick.call();
                        }else{
                            eval ( options.onclick );
                        }
                        
                     }, this);    
                }
                
                
                container.index = index;
                var obj ={};
                obj.container = container ;
                obj.p = p;
                obj.el = el;
                obj.options = options;
                obj.animState = 'off';
                this.items[index] = obj ;
                
                
                if ( (options.hideLabel == 'false' ) || (options.hideLabel == false) ){
                    if ( ( options.animateOnStart == 'true' ) || (options.animateOnStart == true) )
                        this.animate(this.items[index].container, this.items[index].options);
                    this.items[index].animState = 'on';
                }else{
                    p.setStyle('visibility' , 'hidden');
                }
                
                
                if ( (options.animateOnOver == 'true') || (options.animateOnOver == true) ){
                    mouseWrapper.on('mouseover',function(){
                        this.items[index].ignoreAnim = false ;
                        this.animate(this.items[index].container, this.items[index].options);
                    },this);
                    
                    mouseWrapper.on('mouseout',function(){
                        this.items[index].ignoreAnim = false ;
                        this.deAnimate(this.items[index].container, this.items[index].options);
                    },this);
                    
                }
                
                return this.items[index];
            }//apply
    
            ,applyTo : function (elId, config){
                return this._apply(Ext.get(elId), this.index++, Ext.apply({}, config , defaultConfig()) );
            }
            ,init : function() {
                // get all rel = il elements //
                Ext.select('*[rel^=il]').each(function(el, item, collectionIindex){
                    var options = parseOptions(el.getAttributeNS('', 'rel'));
                    var index = this.index++;
                    this._apply(el, index, options);
                }, this);
            }//init
            
            ,animate: function(container, options){
                
                if(this.items[container.index].animState == 'on') return;
                this.items[container.index].animState = 'on';
                
                if( options.hideTimeout > 0 ) {
                    if(this.items[container.index].timerId) clearInterval(this.items[container.index].timerId);
                    this.items[container.index].timerId = this.deAnimate.createDelegate (this, [container, options]).defer(options.hideTimeout)
                }
                
                var el = this.items[container.index].p;
                el.stopFx();
                
                if (! options.animationAnchor ) options.animationAnchor = getAnchor(options.position);
                
                switch(options.animation.toLowerCase()){
                    case 'slidein':
                        if(typeof(options.animationOptions) == 'string'){
                            eval('el.slideIn("'+ options.animationAnchor+'",'  +options.animationOptions +')');
                        }else{
                            el.slideIn(options.animationAnchor ,options.animationOptions );
                        }
                    break;
                    
                    case 'fadein':
                        el.setStyle('visibility', 'visible');//needed for IE or it will not fade in after fading out
                        if(typeof(options.animationOptions) == 'string'){
                            eval('el.fadeIn('  + options.animationOptions  +')');
                        }else{
                            el.fadeIn( );
                        }
                    break;
                }
            }
            
            ,deAnimate: function(container, options){
               
                    if(this.items[container.index].animState == 'off') return;
                    this.items[container.index].animState = 'off';
                    
                    if( options.hideTimeout > 0 ) {
                        if(this.items[container.index].timerId) clearInterval(this.items[container.index].timerId);
                    }
                    
                    var el = this.items[container.index].p; 
                    el.stopFx();
                    
                    if (! options.animationAnchor) options.animationAnchor = getAnchor(options.position);
                    
                    switch(options.animation.toLowerCase()){
                        case 'slidein':
                           
                            if(typeof(options.animationOptions) == 'string'){
                                eval('el.slideOut("'+ options.animationAnchor+'",'  +options.animationOptions +')');
                            }else{
                                el.slideOut(options.animationAnchor, options.animationOptions );
                            }
                        break;
                        
                        case 'fadein':
                            
                            if(typeof(options.animationOptions) == 'string'){
                                eval('el.fadeOut('  + options.animationOptions.replace(/opacity/i,'')  +')');
                            }else{
                                el.fadeOut( options.animationOptions );
                            }
                           
                           
                        break;
                    }
                
                
            }
            
            ,reset: function(retEl){
                //retEl : the return from this_apply()
                retEl.container.insertSibling(retEl.el);
                retEl.container.remove()
            }
           
    }//return
}