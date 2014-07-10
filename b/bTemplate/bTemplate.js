blib.build.define(
	{'block':'bTemplate'},
	(function(){
		
		var template = {
			'chunk':{},
			
			'setChunk':function(obj){
				var content = obj['content'];
				
				if(obj['block']=='bTemplate' && obj['elem']=='position'){
					this.chunk[obj['template']] = obj;
					
				}
				
				if(blib.is(content, "array")){
					for(key in content){
						this.setChunk(content[key]);
					}
				}
				
			},

			'compare':function(old, now, oldObj, deep){
				
				if(!deep)deep = now[0];
				
				for(key in now){
					if(key === '0'){
						//console.log(old[key], now[key], old[key] !== now[key]);
						if(old[key] !== now[key]){
							//чистим шаблон главного блока
							for (var prop in old) delete old[prop];
							for (var prop in now) old[prop] = now[prop];
							
							//чистим чаилдов главного блока
							var childs =  oldObj.children.bTemplate__position,
								temp, parent, onDelete;
							for(var j in childs){
								temp = childs[j].template.template;
								if(temp === deep){
									parent = childs[j].parent;
									onDelete = childs[j].dom;
									delete childs[j];
								}else if(temp.indexOf(deep) === 0){
									childs[j].dom.parentNode.removeChild(childs[j].dom);
									delete childs[j];
								}
							}
							
							//эмулируем процесс построения дом дерева с новыми данными
							var blocks = [],
								curentParent = parent;
							
							while(curentParent){
								if(curentParent.template.block && !curentParent.template.elem){
									blocks.push(curentParent);
								}
								curentParent = curentParent.parent;
							}
							
							//строим
							blib(parent.dom).html(blib.build(this.chunk[deep],{'parent':parent, 'blocks':blocks}));
							return;
						}
						
						continue;
					}
					
					
					if(blib.is(now[key],["object","array"]) && blib.is(old[key],["object","array"])){
						this.compare(old[key], now[key], oldObj, deep+'.'+key);
					}else{
						old[key] = now[key];

						//чистим чаилда главного блока
						var childs =  oldObj.children.bTemplate__position,
							temp, parent, onDelete;
						for(var j in childs){
							temp = childs[j].template.template;
							
							if(temp === deep+'.'+key){
								parent = childs[j].parent;
								onDelete = childs[j].dom;
								delete childs[j];
							}
						}
						
						//эмулируем процесс построения дом дерева с новыми данными
						var blocks = [],
							curentParent = parent;
						
						while(curentParent){
							if(curentParent.template.block && !curentParent.template.elem){
								blocks.push(curentParent);
							}
							curentParent = curentParent.parent;
						}
						
						//строим
						parent.dom.insertBefore(blib.build(this.chunk[deep+'.'+key],{'parent':parent, 'blocks':blocks}), onDelete);
						parent.dom.removeChild(onDelete);
						
					}
					
				}
				
			}			
		};
			
		return function(data){
			var oldDom = blib('.bTemplate')[0],
				oldObj = (oldDom)?oldDom.blib:false,
				oldTemp = (oldObj)?oldObj.template.template:false,
				newTemp = data.template;
			
			if(!newTemp[0]){
				this.template = false;
			}else if(oldTemp[0] === newTemp[0]){
				template.chunk = {};
				template.setChunk(data);
				template.compare(oldTemp, newTemp, oldObj);
				this.template = false;
			}else{
				this.template = data;
			}
		};		
	})()
);

blib.build.define(
	{'block':'bTemplate', 'elem':'position'},
	function(data){
		this.template = data;
	}
);