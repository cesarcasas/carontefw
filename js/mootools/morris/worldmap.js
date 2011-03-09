var WorldMap=new Class({
	
	Implements: [Events, Options],
	options:{
		onStart: $empty
	},
	
	initialize: function(options){
		
		var _this=this;
		this.items=new Array();	
		this.setOptions(options);
		
		this.callbackMoveto=null;
		
		
		this.map=new Array();
		
		this.map[0]={c:1,f:1};
		this.map[1]={c:2,f:1};
		this.map[2]={c:3,f:1};
		
		this.map[3]={c:1,f:2};
		this.map[4]={c:2,f:2};
		this.map[5]={c:3,f:2};
		
		this.map[6]={c:1,f:3};
		this.map[7]={c:2,f:3};
		this.map[8]={c:3,f:3};
		
		this.items=$$("#World .worldmap");
		
		
		this.itemHome=$("Worldhome");
		
		this.visionMap=$("World");
		this.contentWorld=$("contentWorld");
		
		this.current=0;
		this.scroll=  new Fx.Scroll2(_this.visionMap, {
			wait: false,
			duration: 1500,
			//offset: {'x': -200, 'y': -50},
			transition: Fx.Transitions.Quad.easeInOut,
			complete:_this.complete
		});
		
		
		this.makeWorld();
		
		
	},
	
	makeWorld: function(){
		
		if(this.options.width) var x=this.options.width;
		else var x=window.getSize().x;
		
		if(this.options.height) var y=this.options.height;
		else var y=window.getSize().y;
		
		
		var top=0;
		var left=0;
		var change=0;
		var total=1;
		
		if(this.options.discount || this.options.discount==0) var discount=this.options.discount;
		else var discount=150;
		
		
		
		this.visionMap.setStyles({
			'overflow': 'hidden'
			,'position': 'relative'
			,'width':x-discount
			,'height':y-discount
			
			,'padding':0
			
		});
		
		
		this.contentWorld.setStyles({
			'width':x*3
			,'height':y*3
			,'position':'relative'
			,'margin':0
			,'padding':0
		});
		
		
		this.items.each(function(item){
		
			item.setStyles({
				'width':x-discount
				,'height':y-discount
				,'position':'absolute'
				,'top':top
				,'left':left
				,'border':'0'
				,'overflow': 'hidden'
				
			});
			
			
			item.setProperty("x",x-10);
			item.setProperty("y",y-10);
			
			
			left=left+x;
			change++;
			total++;
			
			if(change==3){
				top=top+y;
				left=0;
				change=0;
			}
			
			
		});
		
		this.moveTo(4);	
		
	}, 
	complete: function(current){
		var _this=this;
		_this.current=current;
		
		var call = _this.callbackMoveto || function(){};
		
		call();
		
		_this.callbackMoveto=function(){};
		
	},
	getViewMap: function(col, fil){
		var pos=0;
		var x=0;
		this.map.each(function(m){
			if(m.f==fil && m.c==col) pos=x;
			x++;
		});
		
		return pos;
	},
	
	moveTo:function(element, callback){
		var _this=this;
		
	
		
		callback = callback || function(){  }
		this.callbackMoveto=callback;
		
		if(_this.map[_this.current].f == _this.map[element].f || _this.map[_this.current].c == _this.map[element].c){
			this.scroll.complete=function(){ _this.complete(element);  };
			this.scroll.toElement(this.items[element]);	
						
		}else{
			this.scroll.complete=function(){
					_this.scroll.toElement(_this.items[_this.getViewMap(_this.map[element].c, _this.map[element].f)]);
					_this.complete(element);
					
				};
				
				this.scroll.toElement(this.items[_this.getViewMap(_this.map[element].c, _this.map[_this.current].f)]);
				
				
		}
		
		
		
		
	}
});
