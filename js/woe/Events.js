var GlobalEvents={};

GlobalEvents.Events=new Array();

GlobalEvents.addListener=function(eventID, func){
	GlobalEvents.Events.push({'eventID': eventID, 'func':func});	
}


GlobalEvents.fireEvent=function (eventID, params){
	for(var e=0; e<GlobalEvents.Events.length; e++){
		if(GlobalEvents.Events[e].eventID==eventID) GlobalEvents.Events[e].func(params);
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
		this.onComplete={'func':function(){}, 'response':{}, 'params':{}};
        this.msg=new msgTopStatus("Cargando...");
    },
	setURL: function (url){
		this.url=url;
	},
	setCmd: function (cmd){
		this.cmd=cmd;
	},
	send: function(){
		this.msg.show();
		var params={'cmd':this.cmd, 'args':this.args, 'spool': AjaxCommand.Commands};
		var _this=this;
		new Json.Remote(this.url, {
				onComplete: function(response){ 
					_this.msg.hide();
					_this.process(response); 
				}
			}).send(params);	
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
		}).setText(txt).injectInside(document.body);
	},
	show:function(){
		this.Component.setStyle('display','block');
	},
	hide: function(){
		this.Component.setStyle('display','none');
	}
	
});
