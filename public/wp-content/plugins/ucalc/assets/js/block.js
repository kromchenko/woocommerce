var el 					= wp.element.createElement,
	Fragment 			= wp.element.Fragment,
    BlockControls 		= wp.editor.BlockControls,
    Toolbar 			= wp.components.Toolbar,
    registerBlockType 	= wp.blocks.registerBlockType;

var ucalc_projects = [];

function getOptions() {
	if (ucalc_projects.length)
		return ucalc_projects.map(function( item ) {
			return el('option', {
				value: item.calc_id
			}, item.calc_name);
		});
	else
		return el('option', {
			value: 0
		}, uCalcLang.loading);
}

function getIconUcalc() {
	return el("svg", {
			width: "20",
			height: "20",
			viewBox: "0 0 20 20",
			fill: "none",
			xmlns: "http://www.w3.org/2000/svg",
		}, [
			el("path", {
				d: "M19.3846 2H13.0769H12C11.3231 2 10.7692 2.55385 10.7692 3.23077V4.30769V11.2308C10.7692 12.9308 9.39231 14.3077 7.69231 14.3077C5.99231 14.3077 4.61538 12.9308 4.61538 11.2308V8.15385V7.07692C4.61538 6.4 4.06154 5.84615 3.38462 5.84615H2.30769H0.615385C0.276923 5.84615 0 6.12308 0 6.46154V7.53846C0 7.87692 0.276923 8.15385 0.615385 8.15385H2C2.16923 8.15385 2.30769 8.29231 2.30769 8.46154V11.2308C2.30769 14.2077 4.71538 16.6154 7.69231 16.6154C10.6692 16.6154 13.0769 14.2077 13.0769 11.2308V4.61538C13.0769 4.44615 13.2154 4.30769 13.3846 4.30769H19.3846C19.7231 4.30769 20 4.03077 20 3.69231V2.61538C20 2.27692 19.7231 2 19.3846 2Z",
				fill: "black",
			}),
		]);
}

function getIconChange() {
	return el("svg", {
			width: "20",
			height: "20",
			viewBox: "0 0 20 20",
			fill: "none",
			xmlns: "http://www.w3.org/2000/svg",
		}, [
			el("path", {
				d: "M17.907 2.00001H2.09302C0.930233 2.00001 0 2.9091 0 4.04546V14.9546C0 16.0909 0.930233 17 2.09302 17H17.907C19.0698 17 20 16.0909 20 14.9546V4.04546C20 2.9091 19.0698 2.00001 17.907 2.00001Z",
				stroke: "black",
				'stroke-width': '4',
			}),
			el("path", {
				d: "M16.1628 7.34884C16.2791 7.55814 16.2791 7.83721 16.1628 8.04651L14.093 11.6047C13.9767 11.814 13.7442 11.9535 13.4884 11.9535C13.2326 11.9535 13 11.814 12.8837 11.6047L10.814 8.04651C10.6977 7.83721 10.6977 7.55814 10.814 7.34884C10.9302 7.13953 11.1628 7 11.4186 7H15.5349C15.7907 7 16.0233 7.13953 16.1628 7.34884Z",
				fill: "black",
			}),
		]);
}

function getCalcWidget( id ) {
	return [
        	el( 'div', {
        		className: 'uCalc_' + id
        	}),
        	el( 'script', null, 'var widgetOptions' + id + ' = { bg_color: "transparent" }; (function() { var a = document.createElement("script"), h = "head"; a.async = true; a.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//ucalc.pro/api/widget.js?id=' + id + '&t="+Math.floor(new Date()/18e5); document.getElementsByTagName(h)[0].appendChild(a) })();')
        ];
}

function callToForEach (elems, fn) {
    [].forEach.call(elems, fn);
}


registerBlockType( 'ucalc/block', {
    title: 'uCalc',

    icon: getIconUcalc(),

    category: 'widgets',

    attributes: {
        id: {
        	type: 'integer',
        	default: 0,
        },
        idSelect: {
        	type: 'integer',
        	default: 0,
        },
        mode: {
        	type: 'integer',
        	default: 0,
        },
        count: {
        	type: 'integer',
        	default: 0,
        },
        message: {
        	type: 'string',
        	default: '',
        },
        settings_link: {
        	type: 'string',
        	default: '',
        },
    },

    edit: function( props ) {
    	var attributes = props.attributes;

    	function onChangeId( event ) {
    		var select = event.target;
    		var newId = select.options[select.selectedIndex].value / 1;

    		props.setAttributes( { idSelect: newId } );
        }

        function onClickSave( event ) {
        	props.setAttributes( { id: attributes.idSelect, mode: 1 } );

        	setTimeout(function() {
        		props.setAttributes( { mode: 2 } );
        	}, 1000);

        	setTimeout(function() {
        		props.setAttributes( { mode: 3 } );
        	}, 1500);

        	event.preventDefault();
        }

        function onClickTool() {
        	if (attributes.mode === 3) {
        		props.setAttributes( { id: attributes.idSelect, mode: 4 } );

        		setTimeout(function() {
	        		props.setAttributes( { mode: 5 } );
	        	}, 600);

	        	setTimeout(function() {
	        		props.setAttributes( { mode: 0 } );
	        	}, 1200);
        	}

        	event.preventDefault();
        }

        function getBlockTools() {
	        return el(Toolbar, {
	        	isCollapsed: false,
	        	icon: getIconChange(),
	        	label: uCalcLang.edit,
	        	controls: [{
	        		icon: getIconChange(),
	        		title: uCalcLang.change,
	        		isActive: false,
	        		onClick: onClickTool,
	        		className: props.className + '-tool ' + props.className + '-tool-' + attributes.mode,
	        	}]
	        })
        }

        function getBlockSettings() {
        	return el( 'div', {
	        	className: props.className + '-edit',
	        }, [
		        el('div', {
		        	className: 'components-placeholder__label',
		        }, [
		        	el('span', {
		        		className: 'editor-block-icon has-colors',
		        	}, getIconUcalc()),
		        	'uCalc'
		        ]),
		        el('div', {
		        	className: 'components-placeholder__fieldset',
		        }, el('form', null, (
		        	attributes.message
		        	? el('a', {
		        		href: attributes.settings_link,
		        	}, attributes.message)
		        	: [
			        	el('select', {
			        		className: 'components-placeholder__input',
				        	onChange: onChangeId,
				        	value: attributes.idSelect,
				        }, getOptions()),
				        el('button', {
				        	className: 'components-button is-button is-default is-large',
				        	onClick: onClickSave,
				        }, uCalcLang.select)
			        ]
			     ))),
	        ] );
        }

        function getBlockZone() {
        	return el( 'div', null, [
		        el('div', {
		        	className: props.className + '-mode ' + props.className + '-mode-' + attributes.mode,
		        }),
		        el( 'div', {
		        	className: props.className
		        }, getCalcWidget(attributes.id) ),
		        el( 'div', {
		        	className: props.className + '-overlay'
		        }, ''),
		        getBlockSettings(),
	        ] );
        }

        function updProjects() {
    		if (ucalc_projects.length)
    			return;

    		var data = {
				action: 'ucalc_load_project'
			};
			jQuery.post( ajaxurl, data, function(response) {
				var json = JSON.parse(response);

				if (json.status == 'ok') {
					ucalc_projects = json.data;
					ucalc_projects.unshift({"calc_id": 0, "calc_name": uCalcLang.selector});

					props.setAttributes( { message: '', count: attributes.count++ } );
				}
				else {
					props.setAttributes( { message: json.message, settings_link: json.settings_link } );
				}
			});
    	}

    	function updFrames() {
    		setTimeout(function() {
    			var widgets = document.getElementsByClassName('wp-block-ucalc-block');
				callToForEach(widgets, function(widget) {
					eval(widget.getElementsByTagName('SCRIPT')[0].innerHTML);
				});
    		}, 10);
    	}

    	updProjects();
    	updFrames();

        return (
            el(
                Fragment,
                null,
                el(
                    BlockControls,
                    null,
                    getBlockTools()
                ),
                getBlockZone()
            )
        );
    },

    save: function( props ) {
    	var id = props.attributes.id;

		if (id)
        	return el( 'div', {
	        	className: props.className
	        }, getCalcWidget(id) );
        else
        	return '';
    }
} );