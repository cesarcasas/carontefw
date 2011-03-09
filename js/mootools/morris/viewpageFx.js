var viewPageFx=new Class({
	
	Implements: [Events, Options],
	options:{
		onStart: $empty
	},
	
	initialize: function(options){
		
	this.items=new Array();	
		
		this.Container=$("pageView");
				
		this.setOptions(options);
		this.viewPage=$empty;
		
		this.menu=$empty;
		
		this.widthPageBody=300;
		
		this.bodyOpen=true;
	},
	show:function(){
		this.getPages();
		this.makeOptions();
		this.makePages();
	},
	
	makePages: function(){
		
		var _this=this;
		
		var div=new Element("div", {
			styles:{
				'width':'600px'
				,'height':'500px'
				,'border':'1px solid #000000'
				,'float':'left'
			}
		});
		
		this.viewPage=div;
		this.Container.adopt(this.viewPage);
		
		
		
		var body=new Element("div", {
			styles:{
				'position':'relative'
				,'left':'0'
				,'top':'0'
				,"width":_this.widthPageBody
				,"height":_this.viewPage.getStyle("height")
				,"background":"#EFEFEF"
				,"opacity":"0.5"
				
				
			}
		});
		
		_this.viewPage.adopt(body);
		
		var p=new Element("p", {
			styles:{
				'text-align':'right'
				,'font-size':'12px'
				,'cursor':'pointer'
			}
		}).appendText("[X]");
		
		
		var pbody=new Element("p", {
			styles:{
				'width':_this.widthPageBody-20
			}
		});
		
		
		
		p.addEvent("click", function(){
			
			if(_this.bodyOpen){
				var fx=new Fx.Tween(body,{});
				
				fx.start("width", _this.widthPageBody, 20 );
				_this.bodyOpen=false;
			}else{
				var fx=new Fx.Tween(body,{});
				
				fx.start("width", 20, _this.widthPageBody);
				_this.bodyOpen=true;
			}
		});
		
		body.adopt(p);
		body.adopt(pbody);
		_this.viewPage.body=pbody;
		
		this.items.each(function(item){
			
			
			item.Container.addEvent("click", function(){
				var bg= "url("+item.options.background+")";
				
				_this.viewPage.setStyle("background-image", bg);
				_this.viewPage.body.innerHTML=item.options.body;
			});
		});
	},
	makeOptions: function(){
		
		var ul=new Element("ul", {
			styles:{
				'float':'left'
				,"z-index":"100000"
			}
		});
		this.items.each(function(item){
			var li=new Element("li", {
				styles:{
					'cursor':'pointer'
				}	
			});
			
			li.appendText(item.options.label);
			item.Container=li;
			ul.adopt(li);
		});
		
		this.Container.adopt(ul);
		this.menu=ul;
	},
	getPages: function(){

		var _this=this;
		for(var x=1; x<=5; x++){
			
			_this.items.push(new viewPageFxItem({
				label:"Opcion "+x
				,background: 	"/images/viewpagefx/page"+x+".jpg"
				,title:"pagina 1"
				,body:"Todo lo que se lee en este apartado es simplemente un ejemplo de la pagina "+x
			}));
		}	
	}
});


var viewPageFxItem=new Class({
	
	Implements: [Events, Options],
	options:{
		label:"Label"
		,background:""
		,title:"Title"
		,body:"Body"
	},
	
	initialize: function(options){
	this.Container=$empty;	
	this.body=$empty;
	this.setOptions(options);
		
	}
	
	
});

var pagefx;
window.addEvent("domready", function(){
	 pagefx=new viewPageFx({});
	pagefx.show();
});