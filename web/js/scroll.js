/*
Author:
-------*Netsnower(设计)
-------*Jin.DH(前端)
Date:
-------*2012/4
*/
	//tool
	//获取窗口
	function getW() {
		var client_h, client_w, scrollTop;
		client_h = document.documentElement.clientHeight || document.body.clientHeight;
		client_w = document.documentElement.clientWidth || document.body.clientWidth;
		scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
		return o = {
			w: client_w,
			h: client_h,
			s: scrollTop
		};
	}
	
	
	//创建标签
	function $C(tag){
		return document.createElement(tag||"div");
	};

	//添加dom
	function $D(tag,css,father){
		if(!tag || !css)return;
		var tag = $C(tag);
		if(css){
			setStyle(tag,css);
		};
		father = father || document.body;
		father.appendChild(tag);
		return tag;
	}

	//获取CSS
	function getStyles(ele) {
		var style;
		if (document.defaultView && document.defaultView.getComputedStyle) style = document.defaultView.getComputedStyle(ele, null);
		else style = ele.currentStyle;
		return style;
	};

	//获取对象
	function g(id) {
		if (!id) return;
		return (typeof id == "string" ? document.getElementById(id) : id);
	}

	//设置样式
	function setStyle(ele, css) {
		if (!ele) return;
		var x, y, z, m;
		z = ele;
		x = z.style;
		for (var s in css) {
			m = css[s];
			y = m + "px";
			switch (s) {
			case "w":x.width = y;		break;
			case "h":x.height = y;		break;
			case "l":x.left = y;		break;
			case "t":x.top = y; 		break;
			case "z":x.zIndex = m; 		break;
			case "a":x.position = m;	break;
			case "d":x.display = m;		break;
			case "i":z.innerhtml = m;	break;
			case "bg":x.background = m;	break;
			case "b":x.border = m;		break;
			case "d":x.display = m;		break;
			case "m":x.margin = m;		break;
			case "p":x.padding = m;		break;
			case "rotate" :
				x["MozTransform"]     = "rotate("+m+"deg)";
				x["OTransform"]       = "rotate("+m+"deg)";
				x["WebkitTransform"]  = "rotate("+m+"deg)";
				break;
			default:
				x[s] = m;
				break;
			};

		};
	};

	//事件处理
	var EventUtil = {
		addHandler: function(ele, type, handler) { //绑定事件
			var list = {
				a: ["addEventListener", "attachEvent"],
				r: ["removeEventListener", "datachEvent"]
			};
			var name = arguments[3] === true ? list["r"] : list["a"];

		if (ele[name[0]]) {
			ele[name[0]](type, handler, false)
			} else if (ele.attachEvent) {
				ele[name[1]]("on" + type, handler);
			} else {
			var handler = arguments[3] === true ? handler: null;
			ele["on" + type] = handler;
		}
		},
		removeHandler: function(ele, type, handler) { //删除绑定
			this.addHandler(ele, type, handler, true);
		},
		getEvent: function(event) { //获取事件对象
			return event ? event: window.event;
		},
		getTarget: function(event) { //获取正在处理发生事件的对象
			return event.target || event.srcElement;
		},
		stopPropagation: function(event) { //阻止冒泡
			if (event.stopPropagation) {
				event.stopPropagation();
			} else {
				event.cancelBubble = true;
			}
		},
		preventDefalut: function(event) { //阻止默认行为
			if (event.preventDefalut) {
				event.preventDefault();
			} else {
				event.returnValue = false;
			}
		},
		getRelatedTarget: function(event) { //获取当前目标(mouseover Or mouseout)的时候有效
			if (event.getRelatedTarget) {
				return event.getRelatedTarget;
			} else if (event.toElement) {
				return event.toElement;
			} else if (event.fromElement) {
				return event.fromElement;
			} else {
				return null;
			}
		},
		addLoad: function(fun) { //window.onload处理
			var nowLoad = window.onload;
			if (typeof nowLoad != "function") {
				window.onload = fun;
			} else {
				window.onload = function() {
					nowLoad();
					fun();
				};
			};
		},
		getWheelDelta:function(event){ //获取滑轮方向			
			if(event.wheelDelta){
				return event.wheelDelta;
			}else{
				return -event.detail * 40;
			}
	}
};

//dom           

//滚动类
	var bodyScroll = function(o){
		this.wrapBox = g(o.wrapBox);
		this.wrap = g(o.wrap);
		this.scrollTool = g(o.scrollTool);
		this.init();
	}

	bodyScroll.prototype = {
		init: function() {			
			var that = this;
			
			if(this.wrapBox.offsetHeight>=this.wrap.offsetHeight){
				this.scrollTool.style.display = "none";
				return false;
			};			
						
			EventUtil.addHandler(that.scrollTool,"mousedown",function(e){
				var e = EventUtil.getEvent(e);
				that.scrollTool.lastY = e.clientY - that.scrollTool.offsetTop;			
				that.scrollTool.down = true;
			
				
				//设置拖动必备条件
				setMove(that.scrollTool,e);
				document.onmousemove = function(e){
					var e = EventUtil.getEvent(e);
					var posT =  e.clientY -that.scrollTool.lastY;
					if(posT<=0){
						posT = 0;	
					}else if(posT>=that.maxScrollT){
						posT = that.maxScrollT;
					};				
					setStyle(that.scrollTool, {t:posT});
					that.scrollMove();				
				};
				
				document.onmouseup = function(){
					that.scrollTool.stop();
				}
			});		
			
			EventUtil.addHandler(this.wrapBox, "mousewheel", function (e) {
				var e = EventUtil.getEvent(e);
				that.wheelScroll(e);
				if (e && e.preventDefault) {
				  e.preventDefault();
				  e.stopPropagation();
				} else {
				  e.returnvalue = false;
				  return false;
				}
			  });
			//ff兼容
			  EventUtil.addHandler(this.wrapBox, "DOMMouseScroll", function (e) {
				var e = EventUtil.getEvent(e);
				that.wheelScroll(e);
				if (e && e.preventDefault) {
				  e.preventDefault();
				  e.stopPropagation();
				} else {
				  e.returnvalue = false;
				  return false;
				}
			  });
		
		that.resizeTimer = 0;		
		that.wrapTtimer = null;
		that.getData();
		
	},
	getData:function(){
		var that = this;
			var client = getW();				
			this.wrapH = this.wrap.offsetHeight;
			this.scrollToolH =this.scrollTool.offsetHeight;
			
			
			that.maxScrollT = this.wrapBox.offsetHeight - that.scrollToolH;
			//滚动比例
			
			this.scrollScale = (this.wrapH - this.wrapBox.offsetHeight)/that.maxScrollT;						
			this.toolT = that.scrollTool.offsetTop;	
			
		},
		wheelScroll:function(e){
			var that = this;
			var e = EventUtil.getEvent(e);
			var wheelDelta = EventUtil.getWheelDelta(e);
			var nowToolT = that.scrollTool.offsetTop;		
			if(wheelDelta>0){
				nowToolT-=15;	
			}else if(wheelDelta<0){
				nowToolT+=15;	
			};

			if(nowToolT<=0){
				nowToolT = 0;
			};		
			if(nowToolT >= that.maxScrollT){
				nowToolT = that.maxScrollT;
			};
			setStyle(that.scrollTool, {
				t: nowToolT
			});
			that.scrollMove();
		},
		move: function(num){
			var that = this;
			var tNum = num;
			var b,t_b;
			clearInterval(that.moveTimer);
			if(arguments.length===2){	
				 var posT = tNum/that.scrollScale;
				 clearInterval(that.moveScrollTimer);
				 that.moveScrollTimer = setInterval(function() {
					//滚动条
					t_b = Math.floor(Math.abs(parseInt(getStyles(that.scrollTool).top)));
					t_b += (posT - t_b) / 5;
					if (Math.abs((Math.abs(t_b)-Math.abs(posT)))<4) {
						t_b = posT;
						clearInterval(that.moveScrollTimer);
					};

					setStyle(that.scrollTool, {
						t: t_b
					});					

					//内容
					b = Math.floor(Math.abs(parseInt(getStyles(that.wrap).top)));
					b += Math.floor((tNum - b) /5);
					if (Math.abs((Math.abs(b)-Math.abs(tNum)))<5) {
						b = tNum;
						clearInterval(that.moveTimer);
					};

					//showMsg(b);
					setStyle(that.wrap, {
						t: -b
					});
				},
				30);
			}else{
				setStyle(that.wrap, {
						t: -tNum
				});
			};

		},

		scrollMove:function(){
			var that = this;
			that.toolT = that.scrollTool.offsetTop;	
			var posT = Math.floor(that.toolT*that.scrollScale);			
				that.move(posT);			
		}
	};



	
	
	//拖拽必备条件(清除焦点，设置鼠标范围,obj为拖动对象，event为事件对象)。
	function setMove(obj,event){
		//清除选择
		 window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
		 if(document.all){ //is IE
			//焦点丢失
			obj.onlosecapture = function(){obj.stop();}
			//设置鼠标捕获
			obj.setCapture();
		}else{
			//焦点丢失
			window.onblur =function(){obj.stop();}
			//阻止默认动作
			
			event.preventDefault();
		};
		obj.stop = function(){
			if(obj.releaseCapture){
				obj.releaseCapture();
			};
			document.onmousemove = null;
			document.onmouseup = null;
			window.onblur = null;
		}
	
	}