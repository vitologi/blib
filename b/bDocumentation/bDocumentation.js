(function(){
	
	blib.build.define(
		{'block':'bDocumentation'},
		(function(){
			var singleton = false;
			
			
			return function(data){
				var navigation = data.navigation || false,
					item = data.item || false;
				
				if(navigation)navigation.elem = "navigation";
				if(item)item.elem = "item";
				item.content = "dsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asddsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asddsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asddsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfasdsfasdf asdf asdf asdf asdfas";
				
				
				if(singleton){
					if(navigation)singleton.setNavigation(navigation);
					if(item)singleton.setItem(item);
				}else{
					singleton = this;
					this.template = blib.clone(this.template);
					this.template.content = [
						{
							"elem":"outer",
							"content":[
								{
									"elem":"inner",
									"content":[
										navigation,
										item
									]
								}
							]
						}
					];
				}
			};			
		})(),
		false,
		{
			'setNavigation':function(elem){
				this.children.bDocumentation__navigation[0]._replace(elem);
			},
			'setItem':function(elem){
				this.children.bDocumentation__item[0]._replace(elem);
			}
		}
		
	);
	
	blib.build.define(
		{'block':'bDocumentation', 'elem':'navigation'},
		function(data){
			this.template = blib.clone(this.template);	
			this.id = data.start;
			this.navigation = {};
			
			var navigation = data.content,
				header = "Content";
				
			for(key in navigation){
				if(!this.navigation[navigation[key].parent])this.navigation[navigation[key].parent]=[];
				this.navigation[navigation[key].parent].push(navigation[key]);
				if(navigation[key].id == this.id)header=navigation[key].name;
			}
			
			console.log(navigation);
			
			this.template.content = [
				{"elem":"header", "mods":{"center":true}, "content":header},
				{"tag":"hr", "attrs":{"style":"width:95%,margin:2px auto;"}},
				this.getChild(this.id)
			];
			
		},
		false,
		{
			'getChild':function(id,deep){
				if(!id)return {};
				deep = deep || 0;
				
				var nav = this.navigation[id],
					content = [],
					temp;
				
				for(key in nav){
					innerList = this.getChild(nav[key].id, deep+1);
					
					temp = {'elem':'li', 'tag':'li', 'content':[
						{'elem':'link', 'item':nav[key].id, 'content':nav[key].name}
					]};
					
					if(innerList){
						temp.content.unshift({'elem':'opener', 'content':"+"});
						temp.content.push(innerList);
					}
					
					content.push(temp);
				}
				
				temp = {'elem':'ul', 'tag':'ul', 'content':content};
				if(!deep)temp.mods = {"opened":true};
				
				return content.length?temp:false;
			}
		}
	);
	
	blib.build.define(
		{'block':'bDocumentation', 'elem':'item'},
		function(data){
			this.template = blib.clone(this.template);	
			this.template.content = data.content;
		}
	);
	
	blib.build.define(
		{'block':'bDocumentation', 'elem':'link'},
		function(data){
			this.item = data.item;
			this.template = blib.clone(this.template);	
			this.template.content = data.content;
		},
		{
			'tag':'a',
			'attrs':{
				'href':'#'
			}
		},
		{
			'onclick':function(){
				var self = this,
					links = self.block.children['bDocumentation__link'];
				
				for(key in links)links[key]._setMode("active",false);
				
				blib.ajax({
					'data':{'blib':'bDocumentation', 'id':this.item, 'ajax':true},
					'success':function(data){
						var item = {
							'elem':'item',
							'content':data.item
						};
						//self.block.setItem(item);
						self._setMode("active",true);
					}
				});				
			}
		}
	);
	
	blib.build.define(
		{'block':'bDocumentation', 'elem':'ul'},
		function(data){
			this.template = blib.clone(this.template);	
			this.template.content = data.content;
			this.template.mods = data.mods || {};
		},
		{
			"tag":"ul"
		}
	);
	
	blib.build.define(
		{'block':'bDocumentation', 'elem':'opener'},
		function(data){
			this.template = blib.clone(this.template);	
			this.template.content = data.content;
		},
		false,
		{
			'onclick':function(){
				var ul = this.parent.children.bDocumentation__ul[0],
					status = ul._getMode("opened");
								
				if(status){
					ul._setMode("opened",false);
					this._setMode("opened",false);
					this.template.content = "+";
					this.dom.innerHTML = "+";
				}else{
					ul._setMode("opened",true);
					this._setMode("opened",true);
					this.template.content = "-";
					this.dom.innerHTML = "-";
				}
			}
		}
	);

})(window)