window['Q'] = {};

(function($){

	var Q = window['Q'];
	
	Q['globals']={};

	$.fn['set_unselectable'] = function() {
		return $(this).each(function() {
			if (typeof this.onselectstart!="undefined") {
				//IE route 
				this.onselectstart = function(){return false;};
			}
			else if (typeof this.style.MozUserSelect!="undefined") {
				//Firefox route 
				this.style.MozUserSelect = "none";
			}
			else {
				//All other route (ie: Opera) 
				this.onmousedown = function(){return false;}; 
			}
			//$el[0].style.cursor = "default";
		});
	};

	// 获取类属性 get value from class="name:value" by name
	$.fn['classAttr'] = function(name) {
		var $el = $(this);
		var value = $el.attr('q-'+name);
		if (value) return decodeURIComponent(value)||value;
		var cls = $el.attr('class') || ""; //fix for jQuery 1.6
		var parts = cls.match(new RegExp("\\b" + Q.escape(name) + ":(\\S+)"));
		if(parts) {
			return decodeURIComponent(parts[1]) || parts[1];
		}
		return null;
	};
	
	//设置类属性 class="name:value"
	$.fn['setClassAttr'] = function(name, value) {
		var $el = $(this);
		var cls = $el.attr('class');
		cls = cls.replace(new RegExp("\\b" + Q.escape(name) + ":(\\S+)"), '');
		if (value !== undefined && value !== null) {
			cls = [cls, ' ', name, ':', encodeURIComponent(value)].join('');
		}
		$el.attr('class', cls);
	};

	$('form.autosubmit :input.autosubmittable, .autosubmit:input').livequery('change', function(){
		var $submit = $(':submit', this.form);
		if ($submit.length > 0) {
			$submit.click();
		}
		else {
			$(this.form).submit();
		}
	});

	// 页面全局唯一ID
	var _uniqid_count=0;
	
	Q['uniqid'] = function() {
		return 'uniq' + (_uniqid_count++);
	};
	
	Q['toQueryParams'] = function(str, separator) {
		var hash={};
		if (typeof(str) == 'string') $.each(str.split(separator || '&'), function(i, pair_str) {
			if ((pair = pair_str.split('='))[0]) {
				var key = decodeURIComponent(pair.shift());
				var value = pair.length > 1 ? pair.join('=') : pair[0];
				if (value !== undefined) {
					value = decodeURIComponent(value.replace('+', ' '));
				}
				else {
					value = null;
				}
				
				if (key in hash) {
					if (!Object.isArray(hash[key])) {
						hash[key] = [hash[key]];
					}
					hash[key].push(value);
				}
				else { 
					hash[key] = value;
				}
			}
		});

		return hash;
	};
	
	Q['escape'] = function(str) {
		return (str || "").replace(/([!"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~])/g, "\\$1");
	};
	
	Q['dynamicData'] = function(dynamics) {
		var data = {};
		for (var k in dynamics) {
			if (dynamics.hasOwnProperty(k)) {
				data[k] = $(dynamics[k]).val();
			}
		}
		return data;
	};
	
	Q['clone'] = function(html, suffix, conversions) {
		var $dummy = $('<div/>');
		$dummy.html(html);
	
		$('[id]', $dummy).each(function() {
			var $el = $(this);
			var currentId = $el.attr('id');
			var newId = currentId + '_' + suffix;
			
			var pattern = new RegExp('(["])(.*?)'+ Q.escape(currentId) + '(.*?)\\1', 'g');
			
			html = html.replace(pattern, ['$1$2', newId, '$3$1'].join(''));
		});
		
		if (conversions && conversions.length > 0) {
			for (var i=0; i<conversions.length; i++) {
				html = html.replace(conversions[i].pattern, conversions[i].value);
			}
		}
		
		return $(html);
	};
	
	Q['refresh'] = function(selector) {
		var $el = selector ? $(selector) : null;
		if ($el && $el.length > 0) {
			$el.load($el.attr('src'));
		}
		else {
			if (window.location.href.match(/#.*$/)) {
				window.location.reload();
			}
			else {
				window.location.href = window.location.href;
			}
		}
	};

})(jQuery);


(function($){

	var Q = window['Q'];

	var _loaded_css = {};
	Q['require_css'] = function(css) {
		var i;
		if (typeof(css) !== 'string') {
			//数字
			for (i in css) {
				if (css.hasOwnProperty(i)) {
					Q.require_css(css[i]);
				}
			}
		}
		else {
			//单个文件
			if (_loaded_css[css]) { return true; }
			
			if (document.createStyleSheet) {
				document.createStyleSheet(css);
			}
			else {
				$('<link rel="stylesheet" type="text/css" media="screen" />').attr('href', css).appendTo('head');
			}
			
			_loaded_css[css] = true;
		}
	};

	var _loaded_js = {};
	var _js_ready = {};
	
	Q['require_js'] = function(js, key) {
		var i;
		if (typeof(js) !== 'string') {
			//数字
			for (i in js) {
				if (js.hasOwnProperty(i)) {
					Q.require_js(js[i], i);
				}
			}
		}
		else {
			//单个文件
			if (key === undefined) { key = js; }
			
			if (_loaded_js.hasOwnProperty(key)) { return true; }
			_loaded_js[key] = true;

			$.getScript(js, function(){
				var js_cb = _js_ready[key];
				if (js_cb && js_cb.handlers.length > 0) {
					for (var i in js_cb.handlers) {
						js_cb.handlers[i].callback.call(Q, js_cb.handlers[i].data);
					}
					js_cb.loaded = true;
				}
				_js_ready[key] = _js_ready[key] || {handlers:[], loaded:true};
			});
			
		}
	};
		
	Q['js_ready'] = function (key, data, func) {
		if (arguments.length == 2) {
			func = data;
			data = {};
		}
		
		_js_ready[key] = _js_ready[key] || {handlers:[], loaded:false};
		
		if (_js_ready[key].loaded) {
				func.call(Q, data);
		}
		else {
			_js_ready[key].handlers.push({
				callback: func,
				data: data
			});
		}
		
	};
	
	
	//用于框架下个模块之间的通信
	Q['broadcast'] = function(el, message, params) {
		if (!_broadcast_handlers[message]) return true;
		var handlers = _broadcast_handlers[message] || [];
		for (var i=0; i < handlers.length; i++) {
			handlers[i].apply(el, [message, params]);
		}
	};
	
	//设置消息听众函数
	var _broadcast_handlers = {};
	Q['on_broadcasting'] = function(message, func) {
		_broadcast_handlers[message] = _broadcast_handlers[message] || [];
		_broadcast_handlers[message].push(func);
	};
	
	Q['leave_broadcast'] = function(message, func) {
		var handlers = _broadcast_handlers[message] || [];
		for (var i=0; i < handlers.length; i++) {
			if (handlers[i] === func) {
				handlers.splice(i, 1);
				break;
			}
		}
	};
	
})(jQuery);
(function($){

	var Q = window['Q'];

	var _triggerQueue = {};

	Q['trigger'] = function(opt) {
		//o, event, data, func, url
		opt = opt || {};
		opt.url = opt.url || window.location.href;
		if (opt.global !== false) {
			opt.global = true;
		}

		Q.triggered = opt;

		var e = opt.event;
		
		if (typeof(e)=='string') {
			e = $.Event(e);
		}

		var key = opt.url + ":" + (opt.widget || '*') + ':' +  opt.object + ":" + e.type;

		var req = _triggerQueue[key];
		if (!opt.parallel && req != undefined) {
			req.abort();
		}

		var post={
			_ajax: 1, 
			_object: opt.object,
			_event: e.type
		};

		if (opt.widget) {
			post._widget = opt.widget;
		}

		if (e.pageX) {
			post._mouse=$.toJSON({x:e.pageX, y:e.pageY});	
		}

		if (e.view) {
			post._view = $.toJSON({ 
				left:e.view.pageXOffset, 
				top:e.view.pageYOffset, 
				width:e.view.innerWidth, 
				height:e.view.innerHeight
			});
		}

		data = opt.data || {};
		var form;
		if(data._form){
			form = data._form;
			delete data._form;
		}

		var p = {};
		$.extend(p, Q.globals, data);

		//post._data=$.toJSON(p);
		$.extend(post, p);
		var url = opt.url;

		function onSuccess(data, status){

			setTimeout(function() {
				if (opt.success) {
					switch (typeof opt.success) {
						case 'function':
							opt.success.apply(this, [data, status, url]);
							break;
						case 'string':
							eval(opt.success).apply(this, [data, status, url]);
							break;
					}
				}

				for (var key in data) {
					if (data.hasOwnProperty(key)) {
						if(Q.ajaxProcess[key]) {

							Q.ajaxProcess[key].apply(this, [data[key], status, url]);
						} else {
							//其他
							Q.ajaxProcess.content.apply($(key), [data[key]]);
						}
					}
				}

				if(opt.postAJAX) { opt.postAJAX.apply(this, [data, status, url]); }
			}, 1);
		}

		function onComplete() {
			setTimeout(function() {
				if (opt.complete) {
					switch (typeof opt.complete) {
						case 'function':
							opt.complete.apply(this, [data, status, url]);
							break;
						case 'string':
							eval(opt.complete).apply(this, [data, status, url]);
							break;
					}
				}
			}, 2);

			delete _triggerQueue[key];
		}

		if (form) {
			var $form = $(form);

			$form.attr({
				action: url,
				enctype: 'multipart/form-data',
				method: 'post'
			});

			$form.find('input:not(:file)').addClass('temp_disabled').attr('disabled', 'true');	//temporarily disable them

			_triggerQueue[key] = {
				abort: function() {
					   }
			};

			$form.ajaxSubmit({
				dataType: 'json',
				success: onSuccess,
				data: post,
				global: opt.global,
				complete: function(){
					$form.find('input.temp_disabled').removeAttr('disabled').removeClass('temp_disabled');
					$form.removeAttr('action').removeAttr('enctype').removeAttr('method');
					onComplete.apply(this);
				}
			});

		}
		else {
			// 记录该AJAX请求, 再下次请求同样类型的时候abort
			_triggerQueue[key] = $.ajax({
				global: opt.global,
				url: url,
				data: post,
				type: "POST",
				dataType: "json",
				success: onSuccess,
				complete: onComplete,
				cache: false
			});

		}

	};

	Q['retrigger'] = function(data,remember){
		data = data||{};
		if (Q.triggered) {
			$.extend(Q.triggered.data, data);
			Q.trigger(Q.triggered);
			//移除相关数据
			for (var i in data) {
				if (data.hasOwnProperty(i)) {
					delete Q.triggered.data[i];
				}
			}
		}
	};

	Q['ajaxProcess'] = {
		script: function(data){ eval(data); },
		content: function(data){
			if(typeof(data) == "object"){
				if(Q.ajaxContent[data.mode]){
					Q.ajaxContent[data.mode].apply(this, [data.data]);
				} else {
					this.empty().append(data.data);
				}
			}else{
				this.empty().append(data);
			}
		}
	};

	Q['ajaxContent'] = {
		replace: function(data) { this.replaceWith(data); },
		append: function(data) { this.append(data); },
		prepend: function(data) { this.prepend(data); },
		after: function(data) { this.after(data); },
		before: function(data) { this.before(data); },
		textarea_insert: function(data) {
			this.filter('textarea').each(function(){
				// 将文本插入当前选择
				$(this).focus();
				if(document.selection){
					document.selection.createRange().text= data;
				}else{
					var start = this.selectionStart;
					var end = this.selectionEnd;
					this.value=[this.value.substr(0, start), data, this.value.substr(end)].join('');
					this.selectionStart = this.selectionEnd = start + data.length;
				}
			});
		}
	};

	$('.view, [q-object]').livequery(function(){

		var $el = $(this);

		var object = $el.classAttr('object') || this.id;
		var _events = Q.toQueryParams($el.classAttr('event')) || {};
		var _data = Q.toQueryParams($el.classAttr('static')) || {};
		var dynamics = Q.toQueryParams($el.classAttr('dynamic')) || {};
		var url = $el.classAttr('src') || $el.parents('div[src]:first').attr('src');
		var success_func = $el.classAttr('success') || null;
		var complete_func = $el.classAttr('complete') || null;

		var global = true;
		if ($el.classAttr('global') == false) global = false;

		var widget = $el.classAttr('widget');

		$el.data('_data', _data);

		if ($el.is('form')) {
			// store options in hash
			$(":submit, input:image", $el).bind('click', function() {
				var $submit = $(this);
				$el.data('view_form.submit', $submit.attr('name'));
				if (!$.support.submitBubbles) {
					$el.submit();
					return false;
				}
			});
			_events.submit = _events.submit || 0;
		}
		else {
			$el.set_unselectable();
		}

		$.each(_events, function(event, delay) {
			$el.bind(event, function(e) {
				var data = $.extend({}, $el.data('_data') || {});

			$.extend(data, Q.dynamicData(dynamics));

			if ($el.is('form') && e.type=='submit') {
					//check if it's a form containing files				
					var $files = $('input:file', $el);

					var found = false;

					$files.each(function() {
						if (this.value) {
							found=true;
						}
					});

					if (found) {
						$.extend(data, {_form: $el[0]});
					}

					$(':input:not(:submit, :image, :disabled)', $el).each(function(){
						if(this.name) {
							var $this = $(this);
							if($this.is(':radio')) {
								if ($this.is(':checked')) {
									data[this.name]=$this.val();
								}
							}
							else if($this.is(':checkbox')) {
								data[this.name]=$this.is(':checked') ? 'on' : null ;
							}
							else if (!$this.hasClass('hint')) {
								data[this.name]=$this.val();
							}					
						}
					});

					data.submit = $el.data('view_form.submit') || $el.find(':submit:eq(0)').attr('name');

				} 
				else if ($el.is(':input')) {
					if($el.attr('name')) {
						data[$el.attr('name')]=$el.val();
					}
				}

				delay = parseInt(delay, 10); //确保delay是整数

				var opt = {
					object:object, 
					event:e, 
					data:data, 
					success: success_func,
					complete: complete_func,
					url: url,
					global: global
				};

				if (widget) {
					opt.widget = widget;
				}

				window.setTimeout(function(){
					Q.trigger(opt, delay);
				}, delay);

				e.preventDefault();
				return false;
			});
			
		});
		
	});
	
	/**
	 * @brief 对于具备src属性的div节点, 自动加载src指定的内容, 节点如包含noload, 则不进行加载
	 *
	 * @param empty
	 */
	$('div[src]:not(.noload)').livequery(function(){
		var $div = $(this);
		$div.load($div.attr('src'), function() {
			$(window).resize();
		});
	});

	$('div[src] a[href]:not(.view, [q-object], .prevent_default, .group_prevent_default a)').live('click', function(){
		var $div = $(this).parents('div[src]:first');
		if ($div.data('ajaxing')) return false;
		$div.data('ajaxing', true);
		$div.attr('src', this.href);
		$div.load(this.href, function() {
			$div.data('ajaxing', false);
			$(window).resize();
		});
		return false;
	});
	
	$('div[src] form:not(.view, [q-object], .prevent_default)').livequery(function(){
		var $form = $(this);
		var $div = $form.parents('div[src]:first');
		$form.ajaxForm({
			target: $div,
			url: $div.attr('src'),
			beforeSubmit: function() {
				if ($form.data('submitting')) return false;
				$form.data('submitting', true);
				//提交的时候阻止该表单的其他提交
			},
			complete: function() {
				$form.data('submitting', false);
			}
		});
	});
	
})(jQuery);
(function($){

	var Q = window['Q'];

	//browser兼容的东西
	var userAgent = navigator.userAgent.toLowerCase();
	var version = (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [0,'0'])[1];
	var msie = /msie/.test( userAgent ) 
			&& !/opera/.test( userAgent )
			&& !/chromeframe/.test( userAgent );
	var chrome = /chrome/.test( userAgent ) || (/chromeframe/.test( userAgent ));
	var safari = !chrome && (/safari/.test( userAgent ) || (/opera/.test( userAgent )));

	Q['browser'] = {
		safari: safari,
		msie: msie,
		chrome: chrome,
		version: version
	};

	var supportTouch = null;
		
	Q['supportTouch'] = function() {
		var body = document.body || document.documentElement;
		if (null === supportTouch) {
			supportTouch = body.ontouchstart !== undefined && userAgent.match(/ipad|iphone|android/) !== null;
		}
		return supportTouch;
	};

	Q['event'] = function(e) {
		
		if (e.originalEvent.touches && e.originalEvent.touches.length) {
			e.pageX = e.originalEvent.touches[0].pageX;
			e.pageY = e.originalEvent.touches[0].pageY;
			e.touches = e.originalEvent.touches;
		}
		else if (e.originalEvent.changedTouches && e.originalEvent.changedTouches.length) {
			e.pageX = e.originalEvent.changedTouches[0].pageX;
			e.pageY = e.originalEvent.changedTouches[0].pageY;
			e.touches = e.originalEvent.changedTouches; 
		}
		else {
			e.touches = [];
		}
		
		e.isTouch = Q.supportTouch();

		/*
		if (e.originalEvent.targetTouches && e.originalEvent.targetTouches.length) {
			e.pageX = e.originalEvent.targetTouches[0].pageX;
			e.pageY = e.originalEvent.targetTouches[0].pageY;
		}
		*/
		return e;
	};
	
})(jQuery);
