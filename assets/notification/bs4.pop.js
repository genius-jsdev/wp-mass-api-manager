let bs4pop = {};
// if(!$) $ = jQuery;

(function(pop){

	pop.dialog = function(opts){

		opts = jQuery.extend( true, {
			id: '',//'#xxx'，Dialog ID，
			title: '',
			content: '', //Can be string、element，$object
			className: '', //Custom style
			width: 500,
			height: '',
			target: 'body',//Create a dialog in what dom

			closeBtn: true, //Is there a close button?
			hideRemove: true,//Remove dom when closed
			escape: true, //Esc Exit
			autoFocus: true,//Automatically get focus during initialization
			show: true,//Whether to display the dialog box at the beginning
			backdrop: true,//Modal background true: display modal, false: no modal, 'static': display modal, click modal without closing dialog
			btns: [], //Footer button [{label: 'Button', className: 'btn-primary', onClick(cb){}}]
			drag: true,//Whether to enable drag and drop

			onShowStart(){},
			onShowEnd(){},
			onHideStart(){},
			onHideEnd(){},
			onClose(){},
			onDragStart(){},
			onDragEnd(){},
			onDrag(){}
		}, opts);

		//Get $el
		let $el = opts.id !== '' ? jQuery('#'+opts.id) : undefined;
		if(!$el || !$el.length){
			$el = jQuery(`
				<div class="modal fade" tabindex="-1" role="dialog" data-backdrop="${opts.backdrop}">
					<div class="modal-dialog ">
						<div class="modal-content">
							<div class="modal-body"></div>
						</div>
					</div>
				</div>
			`);
		}

		//Get $body
		let $body = $el.find('.modal-body');

		//Create header
		if(opts.closeBtn || opts.title){

			let $header = jQuery('<div class="modal-header"></div>');

			if(opts.title){
				$header.append(`<h5 class="modal-title"> ${opts.title} </h5>`);
			}

			if(opts.closeBtn){
				$header.append(`
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				`);
			}

			$body.before($header);

		}

		//Create footer
		if(opts.btns.length){

			let $footer = jQuery('<div class="modal-footer"></div>');
			opts.btns.forEach(btn => {

				btn = jQuery.extend(true, {
					label: 'Button',
					className: 'btn-primary',
					onClick(cb){},
				}, btn);

				let $btn = jQuery('<button type="button" class="btn '+btn.className+' pl-5 pr-5">'+btn.label+'</button>');

				$btn.on('click', evt => {

					//Provides a way to manually close the dialog so that the dialog is delayed
					evt.hide = ()=>{
						$el.modal('hide');
					};

					//If the return is not false, the dialog is automatically hidden.
					if(btn.onClick(evt) !== false){
						$el.modal('hide');
					}

				});

				$footer.append($btn);

			});

			$body.after($footer);

		}

		//Create content
		if(typeof opts.content === 'string'){
			$body.html(opts.content);
		}else if(typeof opts.content === 'object'){
			$body.empty();
			jQuery(opts.content).contents().appendTo($body);//Move dom to modal-body
		}

		//Setting properties
		opts.id && $el.attr('id', opts.id);
		opts.className && $el.addClass(opts.className);
		opts.width && $el.find('.modal-dialog').width(opts.width).css('max-width', opts.width);
		opts.height && $el.find('.modal-dialog').height(opts.height);
		opts.isCenter && $el.find('.modal-dialog').addClass('modal-dialog-centered');//Dialog screen centered

		//Binding event
		opts.hideRemove && $el.on('hidden.bs.modal',  function(){
			$el.modal('dispose').remove();//Remove dom
		});
		$el.on('show.bs.modal', opts.onShowStart);
		$el.on('shown.bs.modal', opts.onShowEnd);
		$el.on('hide.bs.modal', opts.onHideStart);
		$el.on('hidden.bs.modal', opts.onHideEnd);
		opts.closeBtn && $el.find('.close').on('click', function(){
			return opts.onClose();
		});

		//Drag and drop
		if(opts.drag){
			$el.attr('data-drag', 'drag');
			drag({
				el: $el.find('.modal-content'),
				handle: $el.find('.modal-header'),
				onDragStart(){
					$el.attr('data-drag', 'draged');
					opts.onDragStart();
				},
				onDragEnd(){
					opts.onDragEnd();
				},
				onDraging(){
					opts.onDrag();
				}
			});
		}

		jQuery(opts.target).append($el);

		$el.modal({
			backdrop: opts.backdrop, //boolean or the string 'static'.Includes a modal-backdrop element. Alternatively, specify static for a backdrop which doesn't close the modal on click.
			keyboard: opts.escape, //Closes the modal when escape key is pressed
			focus: opts.autoFocus, //Puts the focus on the modal when initialized.
			show: opts.show //Shows the modal when initialized.
		});

		let result = {
			$el: $el,
			show(){
				$el.modal('show');
			},
			hide(){
				$el.modal('hide');
			},
			toggle(){
				$el.modal('toggle');
			},
			destory(){
				$el.modal('dispose');
			}
		};

		return result;

	};

	pop.removeAll = function(){
		jQuery('[role="dialog"],.modal-backdrop').remove();
	};

	//Drag and drop
	function drag(opts){

		opts = jQuery.extend(true, {
			el: '',
			handle: '',
			onDragStart(){},
			onDraging(){},
			onDragEnd(){}

		}, opts);

		opts.el = jQuery(opts.el);
		opts.handle = jQuery(opts.handle);
		let $root = jQuery(document);
		let isFirstDrag = true;

		jQuery(opts.handle).on('touchstart mousedown', startEvt=>{

			startEvt.preventDefault();

			let pointEvt = startEvt;
			if(startEvt.type === 'touchstart'){
				pointEvt = startEvt.touches[0];
			}

			let startData = {
				pageX: pointEvt.pageX,
				pageY: pointEvt.pageY,
				targetPageX: opts.el.offset().left, //Current dom location information
				targetPageY: opts.el.offset().top,
			};

			let move = moveEvt => {

				let pointEvt = moveEvt;
				if(moveEvt.type === 'touchmove'){
					pointEvt = moveEvt.touches[0];
				}

				let moveData = {
					pageX: pointEvt.pageX, //For the entire page, including the length of the body part that was rolled up
					pageY: pointEvt.pageY,
					moveX: pointEvt.pageX - startData.pageX,
					moveY: pointEvt.pageY - startData.pageY,
				};

				if(isFirstDrag){
					opts.onDragStart(startData);
					isFirstDrag = false;
				}else{
					opts.onDraging();
				}

				opts.el.css({
					left: startData.targetPageX + moveData.moveX,
					top: startData.targetPageY + moveData.moveY,
				});

			};

			let up = () =>{
				$root.off('touchmove mousemove', move);
				$root.off('touchend mouseup', up);
				opts.onDragEnd();
			};

			$root.on('touchmove mousemove', move).on('touchend mouseup', up);

		});

	}

})(bs4pop);


(function(pop){

	//Dialog
	pop.alert = function(content, cb, opts){

		let dialogOpts = jQuery.extend(true, {
			title: 'Alert Dialog',
			content: content,
			hideRemove: true,
			width: 500,
			btns: [
				{
					label: 'Okay',
					onClick(){
						if(cb){
							return cb();
						}
					}
				}
			]
		}, opts);

		return pop.dialog(dialogOpts);

	};

	//Confirmation box
	pop.confirm = function(content, cb, opts){

		let dialogOpts = jQuery.extend(true, {
			title: 'Confirmation Dialog',
			content: content,
			hideRemove: true,
			btns: [
				{
					label: 'Confirm',
					onClick(){
						if(cb){
							return cb(true);
						}
					}
				},
				{
					label: 'Cancel',
					className: 'btn-default',
					onClick(){
						if(cb){
							return cb(false);
						}
					}
				}
			]
		}, opts);

		return this.dialog(dialogOpts);

	};

	//Input box
	pop.prompt = function(content, value, cb, opts){

		let $content = jQuery(`
			<div>
				<p>${content}</p>
				<input type="text" class="form-control" value="${value}"/>
			</div>
		`);

		let $input = $content.find('input');

		let dialogOpts = jQuery.extend(true, {
			title: 'Prompt Dialog',
			content: $content,
			hideRemove: true,
			width: 500,
			btns: [
				{
					label: 'Okay',
					onClick(){
						if(cb){
							return cb(true, $input.val());
						}
					}
				},
				{
					label: 'Cancel',
					className: 'btn-default',
					onClick(){
						if(cb){
							return cb(false, $input.val());
						}
					}
				}
			]
		}, opts);

		return pop.dialog(dialogOpts);

	};

	// Message Box
	pop.notice = function(content, opts){

		opts = jQuery.extend( true, {

			type: 'primary', //primary, secondary, success, danger, warning, info, light, dark
			position: 'topcenter', //topleft, topcenter, topright, bottomleft, bottomcenter, bottonright, center,
			appendType: 'append', //append, prepend
			closeBtn: false,
			autoClose: 2000,
			className: '',

		}, opts);

		// Get the container $container
		let $container = jQuery('#alert-container-'+ opts.position);
		if(!$container.length){
			$container = jQuery('<div id="alert-container-' + opts.position + '" class="alert-container"></div>');
			jQuery('body').append($container);
		}

		// alert $el
		let $el = jQuery(`
			<div class="alert fade alert-${opts.type}" role="alert">${content}</div>
		`);

		// Close button
		if(opts.autoClose){
			$el
				.append(`
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				`)
				.addClass('alert-dismissible');
		}

		//Timed off
		if(opts.autoClose){

			let t = setTimeout(() => {
				$el.alert('close');
			}, opts.autoClose);

		}

		opts.appendType === 'append' ? $container.append($el) : $container.prepend($el);

		setTimeout(() => {
			$el.addClass('show');
		}, 50);

		return;

	};

})(bs4pop);



if( typeof module === "object" && typeof module.exports === "object" ){
	module.exports = bs4pop
}
