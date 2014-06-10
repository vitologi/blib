blib.build.define(
	{'block':'bMenu'},
	function(data){
		var self = this,
			content = {},
			item,
			glueMenuItem = function(elem){
				var itemContent = content[elem.id],
					temp;
					
				if(!itemContent)return elem;
				
				elem.content = [];
				
				for(key in itemContent){
					elem.content.push(glueMenuItem(itemContent[key]));
				};
				
				return elem;
			};
			
		for(key in data.content){
			item = data.content[key];
			item.elem = 'item';
			if(+item.parent === 0){combine = item; continue;}
			if(!content[item.parent]){content[item.parent] = [];}
			content[item.parent].push(item);
		}
		
		temp = glueMenuItem(combine);
		this.template = blib.clone(this.template);
		this.template.mods = data.mods;
		temp.content.push({'elem':'clear'});
		this.template.content = temp.content;
	},
	{
		'tag':'ul'
	},
	//actions
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
	{'block':'bMenu', 'elem':'item'},
	function(data){
		var content = [{'elem':'link', 'tag':'a', 'attrs':{'href':data.link}, 'content':data.name}];
		
		if(data.content){
			content.push({'elem':'child', 'content':data.content});
		}		
		
		this.opened = false;
		this.template = blib.clone(this.template);
		this.template.content = content;
		
	},
	//template
	{
		'tag':'li'
	},
	//actions
	{
		'onclick':function(e){
			var concurents = e.blib.dom.parentNode.children;
			
			for(key in concurents){
				console.log(concurents[key]);
			}
			
			e.blib.opened = !e.blib.opened;
			e.blib.setMode('active',e.blib.opened);
			e.blib.setMode('opened',e.blib.opened);
			e.stopPropagation?e.stopPropagation():e.cancelBubble = true;
		},
		'closeitem':function(e){
			if(e.blib.opened){
				e.blib.opened=false;
				e.blib.setMode('active',e.blib.opened);
				e.blib.setMode('opened',e.blib.opened);
			}
		}
		
	}
);

blib.build.define(
	{'block':'bMenu', 'elem':'link'},
	function(data){
		this.template = data;		
	},
	false,
	{
		'onclick':function(e){
			var link = e.blib.template.attrs.href;
			if(!link || blib.is(link,"null")){
				e.preventDefault ? e.preventDefault() : e.returnValue = false;
				return false;
			}
		}
	}
);

blib.build.define(
	{'block':'bMenu', 'elem':'child'},
	function(data){
		this.template = blib.clone(this.template);
		this.template.content = data.content;
	},
	//template
	{
		'tag':'ul'
	},
	//actions
	false
);