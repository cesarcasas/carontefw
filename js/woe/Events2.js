//Events2 using with mootools 1.2 and compatileLib.js

var GlobalEvents={};

GlobalEvents.Events=new Array();

GlobalEvents.addListener=function(eventID, func){
	GlobalEvents.Events.push({'eventID': eventID, 'func':func});	
}


GlobalEvents.fireEvent=function (eventID, params){
	
	for(var e=0; e<GlobalEvents.Events.length; e++){
		if(GlobalEvents.Events[e].eventID==eventID) {
			GlobalEvents.Events[e].func(params);
			
		}
	}
}


var AjaxCommand = {};

AjaxCommand.Commands=new Array();

AjaxCommand.add=function(cmd, params){
	AjaxCommand.Commands.push({'cmd':cmd, 'params': params});
}

AjaxCommand.reset=function (){
	AjaxCommand.Commands=new Array();
}

AjaxCommand.process=function(events){
	
}


var SuperAjax=new Class({
	 initialize: function(){
	 	this.url="/ajaxProxy";
		this.cmd="";
		this.args={};
                this.showLoading=false;
		this.onComplete={'func':function(){}, 'response':{}, 'params':{}};
        this.msg=new msgTopStatus("Cargando...");
    },
	setURL: function (url){
		this.url=url;
	},
        setShowLoading:function (show){

            this.showLoading=show;
        },
	setCmd: function (cmd){
		this.cmd=cmd;
	},
	send: function(){
            
		if(this.showLoading){
                    this.msg.show();
                }
		var params={'cmd':this.cmd, 'args':this.args, 'spool': AjaxCommand.Commands};
		var _this=this;
		/* cambiamos aca para el nuevo mootools */
		new Json.Remote(this.url, {
				onComplete: function(response){ 
					_this.msg.hide();
					_this.process(response); 
				}
				,urlEncoded:false // can't set Content-type otherwise
				,headers:{
					'Content-type':'application/x-www-form-urlencoded;'
				}
			}).send(params);	
			
			
			
			
		/* fin del cambio*/
		AjaxCommand.reset();
	},
	callBack: function(callBack){
		this.onComplete=callBack;
	},
	process:function(response){
		
		this.onComplete.response=response.original;
		this.onComplete.func(this.onComplete.response, this.onComplete.params);
		
			if(!response.spoolEvents) return;
			for(var i=0; i<response.spoolEvents.length; i++){
				
				GlobalEvents.fireEvent(response.spoolEvents[i].event.toInt(), response.spoolEvents[i].params);
			}
			
		

	}

	
});

var msgTopStatus=new Class({
	initialize: function(txt){
		this.Component=new Element('div',{
			'class':'msgTopStatus'
		}).appendText(txt).injectInside(document.body);
	},
	show:function(){
		this.Component.setStyle('display','block');
	},
	hide: function(){
		this.Component.setStyle('display','none');
		this.Component.removeEvents();
		this.Component.remove();
	}
	
});
