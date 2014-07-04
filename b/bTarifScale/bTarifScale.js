(function(){
	
	var header = ["Услуги", "Тарифы", "Абонентск. плата руб.", "Состав тарифа"];
	
	blib.build.define(
		{'block':'bTarifScale'},
		function(data){

			var content = data.content,
				groups = {},
				temp;
			
			for(key in content){
				temp = content[key]['group'];
				if(!(temp in groups)){
					groups[temp]=[];
				}
				groups[temp].push(content[key]);
			}
			
			temp = [{'elem':'header', 'content':header}];
			
			for(key in groups){
				
				for(i in groups[key]){
					var item = {'elem':'item', 'content':groups[key][i]};
					if(i == '0'){item.first = groups[key].length;}
					temp.push(item);
				}
				
				temp.push({
					'elem':'groupDescription',
					'tag':'tr',
					'content':[
						{'tag':'td', 'attrs':{'colspan':header.length}, 'content':groups[key][0].groupDescription}
					]
				});
			}
			
			this.template = blib.clone(this.template);
			this.template.mods = data.mods;
			this.template.header = header;
			this.template.content = temp;
		},
		{
			'tag':'table'
		},
		{
			'onSetMode':{
				'position':{
					'horizontal':function(){
						console.log('position -> horizontal');
					},
					'vertical':function(){
						console.log('position -> vertical');
					}
				}
			}
		}
	);
	
	blib.build.define(
		{'block':'bTarifScale', 'elem':'header'},
		function(data){
			var temp = [];
			
			for(key in data.content){
				temp.push({'tag':'td', 'content':data.content[key]});
			}
			
			this.template = blib.clone(this.template);
			this.template.content = temp;
		},
		{
			'tag':'tr'
		}
	);
	
	blib.build.define(
		{'block':'bTarifScale', 'elem':'item'},
		function(data){
			var temp = [];
			
			if(data.first){temp.push({'elem':'groupName', 'tag':'td', 'attrs':{'rowspan':data.first}, 'content':data.content.group});}
			temp.push({'tag':'td', 'content':[{'tag':'span', 'content':data.content.name},{'elem':'checker', 'content':data.content}]});
			temp.push({'tag':'td', 'content':data.content.cost});
			temp.push({'tag':'td', 'content':data.content.description});
			
			this.template = blib.clone(this.template);
			this.template.content = temp;
		},
		//template
		{
			'tag':'tr'
		},
		//actions
		{
			'onclick':function(e){
				console.log(e.blib);
			}		
		}
	);
	
	blib.build.define(
		{'block':'bTarifScale', 'elem':'checker'},
		function(data){
			
			//console.log(temp);
			this.template = blib.clone(this.template);
			this.template.content = temp;
		},
		//template
		{
			'tag':'input',
			'attrs':{'type':'checkbox'}
		},
		//actions
		{
			'onclick':function(e){
				console.log(e.blib);
			}		
		}
	);

})(window);
