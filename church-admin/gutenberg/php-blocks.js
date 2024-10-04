( function( blocks, editor, element , components,blockEditor,serverSideRender) 
{
	
	const el = element.createElement;
    const { registerBlockType } = blocks;
	const { serverSideRender: ServerSideRender } = wp;
    const { Fragment } = element;
	const {
		
        TextControl,
        CheckboxControl,
        RadioControl,
        SelectControl,
        TextareaControl,
        ToggleControl,
        RangeControl,
        Panel,
        PanelBody,
        PanelRow
    } = components;
    const {ToolbarControls,InspectorControls, PlainText, RichText,withColors, PanelColorSettings, getColorClassName } = blockEditor;
   
	const churchAdminIcon = el('svg', { width: 36, height: 36 ,viewBox:"0 0 36 36"},
	el('path', { d:"M256,0L238.3,22L147.7,135.3L142.7,141.7L142.7,284L17.3,362.6L41.4,400.8L52,394.4L52,512L233.3,512L233.3,421.4C233.3,408.6 243.1,398.7 256,398.7C268.8,398.7 278.7,408.5 278.7,421.4L278.7,512L460,512L460,394.4L470.6,400.8L494.7,362.6L369.3,284L369.3,141.6L364.3,135.2L273.7,22L256,0ZM256,72.2L324,157.2L324,255.6L268,220.9L256,213.1L244,220.9L188,255.6L188,157.2L256,72.2ZM256,149.4C243.5,149.4 233.3,159.6 233.3,172.1C233.3,184.6 243.5,194.8 256,194.8C268.5,194.8 278.7,184.6 278.7,172.1C278.7,159.6 268.5,149.4 256,149.4ZM256,267L414.6,366.1L414.6,466.7L324,466.7L324,421.4C324,384.1 293.3,353.4 256,353.4C218.7,353.4 188,384.1 188,421.4L188,466.7L97.4,466.7L97.4,366.1L256,267Z",transform:"matrix(0.0710178,0,0,0.0666237,-0.228738,0.992188)",fill:"#F7931D"
			} ) );

		
	/*
	* Here's where we register the block in JavaScript.
	*
	* It's not yet possible to register a block entirely without JavaScript, but
	* that is something I'd love to see happen. This is a barebones example
	* of registering the block, and giving the basic ability to edit the block
	* attributes. (In this case, there's only one attribute, 'foo'.)
			*/


			registerBlockType( 'church-admin/event-booking', {
				category: 'widgets',
				title: 'Church Admin Event Booking',
				icon: churchAdminIcon,
				
				/*
				* In most other blocks, you'd see an 'attributes' property being defined here.
				* We've defined attributes in the PHP, that information is automatically sent
				* to the block editor, so we don't need to redefine it here.
				*/
	
				edit: function( props ) {
					console.log('Event Booking Attributes:'+props.attributes);
					return [
						/*
						* The ServerSideRender element uses the REST API to automatically call
						* php_block_render() in your PHP code whenever it needs to get an updated
						* view of the block.
						*/
						el( ServerSideRender, {
							block: 'church-admin/event-booking',
							attributes: props.attributes,
							key:'event-booking-server-side'
						} ),
						/*
						* InspectorControls lets you add controls to the Block sidebar. In this case,
						* we're adding a TextControl, which lets us edit the 'foo' attribute (which
						* we defined in the PHP). The onChange property is a little bit of magic to tell
						* the block editor to update the value of our 'foo' property, and to re-render
						* the block.
						*/
						el( InspectorControls, {key:'controls'},
							
						el( TextControl, {
							key:'event-id',
							label:'Event ID',
							value: props.attributes.event_id,
							onChange: ( value ) => { props.setAttributes( { event_id: value } ); },
						} ),
							
							el( SelectControl, {
								key:'color-scheme',
								label:  'Color scheme?' ,
								value: props.attributes.colorscheme,
								onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
								options: [
									{ value: '', label:  'No color theming'  }, 
									{ value: 'white', label:  'White background'  },   
								{ value: 'bluegrey', label:  'Dark theme blue grey'  },
								{ value: 'coolgrey', label:  'Dark theme cool grey'  },
								{ value: 'warmgrey', label:  'Dark theme warm grey'  }
								
								],
							}),
						
						),
					];
				},
	
				// We're going to be rendering in PHP, so save() can just return null.
				save: function() {
					return null;
				},
			} );		
		registerBlockType( 'church-admin/sermon-series', {
			category: 'widgets',
			title: 'Church Admin Sermon Series',
			icon: churchAdminIcon,
			
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log('Sermon-Series Attributes:'+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/sermon-series',
						attributes: props.attributes,
						key:'sermon-series-server-side'
					} ),
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					el( InspectorControls, {key:'controls'},
						
						el( TextControl, {
							key:'url',
							label: 'Sermon Page URL',
							value: props.attributes.sermon_page,
							onChange: ( value ) => { props.setAttributes( { sermon_page: value } ); },
						} ),
						
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/sermon-podcast', {
			title: 'Church Admin Sermon Podcast',
			icon: churchAdminIcon,
			category: 'widgets',
			
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log('Sermon Podcast Attributes:'+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/sermon-podcast',
						attributes: props.attributes,
						key:'sermons-server-side'
					} ),
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					el( InspectorControls, {key:'controls'},
					
					el( SelectControl, {
							key:'series_id',
							label:  'Choose a series' ,
							value: props.attributes.series_id,
							onChange: ( value ) => { props.setAttributes( { series_id: value } ); },
							options: seriesOptions,
						}),
					el( TextControl, {
							key:'sermon-title',
							label:'One sermon only - Sermon title',
							value: props.attributes.sermon_title,
							onChange: ( value ) => { props.setAttributes( { sermon_title: value } ); },
						} ),
					el( TextControl, {
							key:'order-by',
							label: 'Order By',
							value: props.attributes.order,
							onChange: ( value ) => { props.setAttributes( { order: value } ); },
						} ),
					el( TextControl, {
							key:'exclude',
							label:'Exclude sections',
							value: props.attributes.exclude,
							onChange: ( value ) => { props.setAttributes( { exclude: value } ); },
						} ),
					el( TextControl, {
							key:'how-many',
							label: 'How many to show',
							value: props.attributes.howmany,
							onChange: ( value ) => { props.setAttributes( { howmany: value } ); },
						} ),
						el( CheckboxControl, {
							key:'nowhite',
							label: 'Video automatic aspect ratio ',
							value: props.attributes.nowhite,
							checked: props.attributes.nowhite,
							onChange: ( value ) => { props.setAttributes( { nowhite: value } ); },
						} ),
					el( CheckboxControl, {
							key:'most-popular',
							label: 'Show most popular',
							value: props.attributes.most_popular,
							checked: props.attributes.most_popular,
							onChange: ( value ) => { props.setAttributes( { most_popular: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',	
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
						
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/sermons', {
			title: 'Church Admin Sermons (new style)',
			icon: churchAdminIcon,
			category: 'widgets',
			
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log('Sermon Podcast Attributes:');
				console.log(props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/sermons',
						attributes: props.attributes,
						key:'sermons-server-side'
					} ),
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					el( InspectorControls, {key:'controls'},
						
					
						el( TextControl, {
							key:'how-many',
							label: 'How many to show e.g.9 ',
							value: props.attributes.howmany,
							onChange: ( value ) => { props.setAttributes( { howmany: value } ); },
						} ),
						el( TextControl, {
							key:'rolling',
							label: 'Previous months to show e.g.12',
							value: props.attributes.rolling,
							onChange: ( value ) => { props.setAttributes( { rolling: value } ); },
						} ),
						el( TextControl, {
							key:'start-date',
							label: 'Optional Start date yyyy-mm-dd',
							value: props.attributes.start_date,
							onChange: ( value ) => { props.setAttributes( { start_date: value } ); },
						} ),
						el( CheckboxControl, {
							key:'nowhite',
							label: 'Video automatic aspect ratio ',
							value: props.attributes.nowhite,
							checked: props.attributes.nowhite,
							onChange: ( value ) => { props.setAttributes( { nowhite: value } ); },
						} ),
						el( CheckboxControl, {
							key:'playnoshow',
							label: "Don't show MP3 plays",
							value: props.attributes.playnoshow,
							checked: props.attributes.playnoshow,
							onChange: ( value ) => { props.setAttributes( { playnoshow: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',	
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
						
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		//register

		registerBlockType('church-admin/basic-register', {
		title: 'Church Admin Basic Register',
			icon: churchAdminIcon,
			category: 'widgets',
				edit: function( props ) {
					console.log("basic register attributes: "+props.attributes);
					return [
						/*
						* The ServerSideRender element uses the REST API to automatically call
						* php_block_render() in your PHP code whenever it needs to get an updated
						* view of the block.
						*/
						el( ServerSideRender, {
							block: 'church-admin/basic-register',
							attributes:props.attributes,
							key:'basic-register-server-side'
						} ),
						el( InspectorControls, {key:'controls'},
							el( SelectControl, {
								key:'color-scheme',
								label:  'Color scheme?' ,
								value: props.attributes.colorscheme,
								onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
								options: [
									{ value: '', label:  'No color theming'  }, 
									{ value: 'white', label:  'White background'  },   
								{ value: 'bluegrey', label:  'Dark theme blue grey'  },
								{ value: 'coolgrey', label:  'Dark theme cool grey'  },
								{ value: 'warmgrey', label:  'Dark theme warm grey'  }
								
								],
							}),
							el( SelectControl, {
								key:'member-type',
								label:  'Which member type to save as?' ,
								value: props.attributes.member_type_id,
								onChange: ( value ) => { props.setAttributes( { member_type_id: value } ); },
								options: membTypeOptions,
							}),
							el( CheckboxControl, {
								key:'admin-email',
								label: 'Send notification to admin email',
								value: props.attributes.admin_email,
								checked: props.attributes.admin_email,
								onChange: ( value ) => { props.setAttributes( { admin_email: value } ); },
							} ),
							el( CheckboxControl, {
								key:'allow-registrations',
								label: 'Allow registrations',
								value: props.attributes.allow_registrations,
								checked: props.attributes.allow_registrations,
								onChange: ( value ) => { props.setAttributes( { allow_registrations: value } ); },
							} ),
							el( CheckboxControl, {
								key:'exclude-dob',
								label: "Don't show  date of birth",
								value: props.attributes.dob,
								checked: props.attributes.dob,
								onChange: ( value ) => { props.setAttributes( { dob: value } ); },
							} ),
							el( CheckboxControl, {
								key:'exclude-gender',
								label: "Don't show gender",
								value: props.attributes.gender,
								checked: props.attributes.gender,
								onChange: ( value ) => { props.setAttributes( { gender: value } ); },
							} ),
							el( CheckboxControl, {
								key:'exclude-custom',
								label: "Don't show custom fields",
								value: props.attributes.custom,
								checked: props.attributes.custom,
								onChange: ( value ) => { props.setAttributes( { custom: value } ); },
							} ),
							el( CheckboxControl, {
								key:'show-onboarding',
								label: "Show only onboarding custom fields on first registration",
								value: props.attributes.onboarding,
								checked: props.attributes.onboarding,
								onChange: ( value ) => { props.setAttributes( { onboarding: value } ); },
							} ),
							el( CheckboxControl, {
								key:'full-privacy-show',
								label: "Show all privacy form fields",
								value: props.attributes.full_privacy_show,
								checked: props.attributes.full_privacy_show,
								onChange: ( value ) => { props.setAttributes( { full_privacy_show: value } ); },
							} ),
							el( CheckboxControl, {
								key:'allow-sites',
								label: 'Show sites to logged in users',
								value: props.attributes.sites,
								checked: props.attributes.sites,
								onChange: ( value ) => { props.setAttributes( { sites: value } ); },
							} ),
							el( CheckboxControl, {
								key:'allow-groups',
								label: 'Show groups to logged in users',
								value: props.attributes.groups,
								checked: props.attributes.groups,
								onChange: ( value ) => { props.setAttributes( { groups: value } ); },
							} ),
							el( CheckboxControl, {
								key:'allow-ministries',
								label: 'Show ministries to logged in users',
								value: props.attributes.ministries,
								checked: props.attributes.ministries,
								onChange: ( value ) => { props.setAttributes( { ministries: value } ); },
							} ),
						)
					];
				},

				// We're going to be rendering in PHP, so save() can just return null.
				save: function() {
					return null;
				},
		} );

		registerBlockType( 'church-admin/register', {
			title: 'Church Admin Register',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Register attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/register',
						attributes: props.attributes,
						key:'register-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'controls'},
						
						el( SelectControl, {
							key:'member-type',
							label:  'Which member type to save as?' ,
							value: props.attributes.member_type_id,
							onChange: ( value ) => { props.setAttributes( { member_type_id: value } ); },
							options: membTypeOptions,
						}),
						el( CheckboxControl, {
							key:'admin-email',
							label: 'Send Admin email',
							value: props.attributes.admin_email,
							checked: props.attributes.admin_email,
							onChange: ( value ) => { props.setAttributes( { admin_email: value } ); },
						} ),
						el( CheckboxControl, {
							key:'allow-registrations',
							label: 'Allow registrations',
							value: props.attributes.allow_registrations,
							checked: props.attributes.allow_registrations,
							onChange: ( value ) => { props.setAttributes( { allow_registrations: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		//rota
		registerBlockType( 'church-admin/rota', {
			title: 'Church Admin Schedule',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Rota attributes: ");
				console.log(props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/rota',
						attributes: props.attributes,
						key:'rota-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'controls'},
						el( SelectControl, {
							key:'service-id',
							label:  'Which Service?' ,
							value: props.attributes.service_id,
							onChange: ( value ) => { props.setAttributes( { service_id: value } ); },
							options:serviceOptions,
						}),
						el( TextControl, {
							key:'weeks',
							label: 'How many weeks to show?',
							value: props.attributes.weeks,
							onChange: ( value ) => { props.setAttributes( { weeks: value } ); },
						} ),
					el( TextControl, {
							key:'title',
							label: 'Title',
							value: props.attributes.title,
							onChange: ( value ) => { props.setAttributes( { title: value } ); },
						} ),
						el( CheckboxControl, {
							key:'login',
							label: 'Require Login?',
							value: props.attributes.logged_in,
							checked: props.attributes.logged_in,
							onChange: ( value ) => { props.setAttributes( { logged_in: value } ); },
						} ),
						el( CheckboxControl, {
							key:'links',
							label: 'Show PDF/CSV links?',
							value: props.attributes.links,
							checked: props.attributes.links,
							onChange: ( value ) => { props.setAttributes( { links: value } ); },
						} ),
					

						el( SelectControl, {
							key:'name_style',
							label:  'How to show names?' ,
							value: props.attributes.name_style,
							onChange: ( value ) => { props.setAttributes( { name_style: value } ); },
							options: [
							{ value: 'Full', label:  'Full Name'  }, 
							{ value: 'Initials', label:  'Initials'  },   
							{ value: 'FirstNameFirstLetterLastName', label:  'First name and last name initial'  },
											
							
							],
						}),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
								{ value: 'bluegrey', label:  'Dark theme blue grey'  },
								{ value: 'coolgrey', label:  'Dark theme cool grey'  },
								{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		//small groups
		registerBlockType( 'church-admin/small-groups-list', {
			title: 'Church Admin Small Groups',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Small groups list attributes: ");
				console.log(props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/small-groups-list',
						attributes: props.attributes,
						key:'small-groups-list-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'controls'},
						el( CheckboxControl, {
							key:'map',
							label: 'Show map?',
							value: props.attributes.map,
							checked: props.attributes.map,
							onChange: ( value ) => { props.setAttributes( { map: value } ); },
						} ),
						el( TextControl, {
							key:'zoom',
							label: 'Map zoom level?',
							value: props.attributes.zoom,
							onChange: ( value ) => { props.setAttributes( { zoom: value } ); },
						} ),
					el( CheckboxControl, {
							key:'photo-style',
							label: 'Photo Style?',
							value: props.attributes.photo,
							checked: props.attributes.photo,
							onChange: ( value ) => { props.setAttributes( { photo: value } ); },
						} ),
					el( CheckboxControl, {
							key:'logged-in',	
							label: 'Logged in?',
							value: props.attributes.loggedin,
							value: props.attributes.loggedinadd,
							checked: props.attributes.loggedin,
							onChange: ( value ) => { props.setAttributes( { loggedin: value } ); },
						} ),
					el( CheckboxControl, {
							key:'pdf-link',
							label: 'PDF link?',
							value: props.attributes.pdf,
							
							checked: props.attributes.pdf,
							onChange: ( value ) => { props.setAttributes( { pdf: value } ); },
						} ),
					el( TextControl, {
							key:'pdf-title',
							label: 'Title for PDF',
							value: props.attributes.title,
							onChange: ( value ) => { props.setAttributes( { title: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
						
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		//small group signup
		registerBlockType( 'church-admin/small-groups-signup', {
			title: 'Church Admin Small Groups Sign Up',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("groups signup attributes: "+ props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/small-groups-signup',
						attributes: props.attributes,
						key:'signup-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'controls'},
						
						
						el( SelectControl, {
							key:'people-types',
							label:  'People Types?' ,
							value: props.attributes.people_types,
							onChange: ( value ) => { props.setAttributes( { people_types: value } ); },
							options: peopleTypeOptions,
						}),
						el( TextControl, {
							key:'title',
							label: 'Title for Signup',
							value: props.attributes.title,
							onChange: ( value ) => { props.setAttributes( { title: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
						
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		//small groups
		registerBlockType( 'church-admin/small-group-members', {
			title: 'Church Admin Small Group Members',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Group members attributes "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/small-group-members',
						attributes: props.attributes,
						key:'member-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'controls'},
						el( CheckboxControl, {
							key:'show-map',
							label: 'Show map?',
							value: props.attributes.map,
							onChange: ( value ) => { props.setAttributes( { map: value } ); },
						} ),
						el( TextControl, {
							key:'zoom',
							label: 'Map zoom level?',
							value: props.attributes.zoom,
							onChange: ( value ) => { props.setAttributes( { zoom: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
						
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		//address list
		registerBlockType( 'church-admin/address-list', {
			title: 'Church Admin Address List',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Address list attributes:");
				console.log(props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/address-list',
						attributes: props.attributes,
						key:'address-server-side',
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'controls'},
						el( SelectControl, {
							key:'address-style',
							label:  'Address Style?' ,
							value: props.attributes.address_style,
							onChange: ( value ) => { props.setAttributes( { address_style: value } ); },
							options: [
							
								{ value: 'one', label:  'One line'  },   
								{ value: 'multi', label:  'Multi line' }
						
							
							],
						}),
						el( TextControl, {
							key:'member-types',
							label: 'Which member types?',
							value: props.attributes.member_type_id,
							onChange: ( value ) => { props.setAttributes( { member_type_id: value } ); },
						} ),
						el( SelectControl, {
							key:'site-id',
							label: 'Which Site?',
							value: props.attributes.site_id,
							onChange: ( value ) => { props.setAttributes( { site_id: value } ); },
							options:  siteOptions
						} ),
					el( CheckboxControl, {
							key:'pdf-links',
							label: 'PDF links?',
							value: props.attributes.pdf,
							onChange: ( value ) => { props.setAttributes( { pdf: value } ); },
							checked: props.attributes.pdf
						} ),
					el( CheckboxControl, {
							key:'vcf-link',
							label: 'VCF link?',
							value: props.attributes.vcf,
							onChange: ( value ) => {
											props.setAttributes( { vcf: value } );
										},
							checked: props.attributes.vcf
							
						} ),
						el( CheckboxControl, {
							key:'require-login',
							label: 'Require Login?',
							value: props.attributes.logged_in,
							checked: props.attributes.logged_in,
							onChange: ( value ) => { props.setAttributes( { logged_in: value } ); },
						} ),
						el( CheckboxControl, {
							key:'show-maps',
							label: 'Show maps?',
							value: props.attributes.map,
							checked: props.attributes.map,
							onChange: ( value ) => { props.setAttributes( { map: value } ); },
						} ),
						el( CheckboxControl, {
							key:'show-kids',
							label: 'Show kids?',
							value: props.attributes.kids,
							checked: props.attributes.kids,
							onChange: ( value ) => { props.setAttributes( { kids: value } ); },
						} ),
						el( CheckboxControl, {
							key:'first-initial',
							label: 'Show initial with last name?',
							value: props.attributes.first_initial,
							checked: props.attributes.first_initial,
							onChange: ( value ) => { props.setAttributes( { first_initial: value } ); },
						} ),
						el( CheckboxControl, {
							key:'updateable',
							label: 'Updateable?',
							value: props.attributes.updateable,
							checked: props.attributes.updateable,
							onChange: ( value ) => { props.setAttributes( { updateable: value } ); },
						} ),
						
						
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
						
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );

		registerBlockType( 'church-admin/serving', {
			title: 'Church Admin Serving Request',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log('Serving' +props.attrubutes)
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/serving',
						attributes: props.attributes,
						key:'serving-server-side'
					} ),
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					el( InspectorControls, {key:'controls'},
						
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					)
				,];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );


		registerBlockType( 'church-admin/sessions', {
			title: 'Church Admin Sessions',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Sessions attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/sessions',
						attributes: props.attributes,
						key:'sessions-server-side'
					} ),
					el( InspectorControls, {key:'controls'},
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					)

				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );

		registerBlockType( 'church-admin/recent', {
			title: 'Church Admin Recent Directory activity',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Recent attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/recent',
						attributes: props.attributes,
						key:'recent-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'control'},
						el( TextControl, {
							key:'weeks',
							label: 'How many weeks?',
							value: props.attributes.weeks,
							onChange: ( value ) => { props.setAttributes( { weeks: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/calendar', {
			title: 'Church Admin Calendar',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Calendar attributes: ");
				console.log(props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
				
					el( ServerSideRender, {
						block: 'church-admin/calendar',
						attributes: props.attributes,
						key:'calendar-server-side'
					} ),
				
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {
						key:'controls'
					},
						el( CheckboxControl, {
							key: 'style',
							label: 'Table Style?',
							value: props.attributes.style,
							checked: props.attributes.style,
							onChange: ( value ) => { props.setAttributes( { style: value } ); },

						} ),
						el( TextControl, {
							key:'Category-ids',
							label: 'Category IDs',
							value: props.attributes.cat_id,
							onChange: ( value ) => { props.setAttributes( { cat_id: value } ); },
						} ),
						el( TextControl, {
							key:'facility-ids',
							label: 'Facility IDs',
							value: props.attributes.fac_id,
							onChange: ( value ) => { props.setAttributes( { fac_id: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/calendar-list', {
			title: 'Church Admin Calendar List',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Calendar attributes: ");
				console.log(props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
				
					el( ServerSideRender, {
						block: 'church-admin/calendar-list',
						attributes: props.attributes,
						key:'calendar-server-side'
					} ),
				
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {
						key:'controls'
					},
						el( TextControl, {
							key:'days',
							label: 'Days to show?',
							value: props.attributes.days,
							onChange: ( value ) => { props.setAttributes( { days: value } ); },
						} ),
						el( TextControl, {
							key:'Category-ids',
							label: 'Category IDs',
							value: props.attributes.cat_id,
							onChange: ( value ) => { props.setAttributes( { cat_id: value } ); },
						} ),
						el( TextControl, {
							key:'facility-ids',
							label: 'Facility IDs',
							value: props.attributes.fac_id,
							onChange: ( value ) => { props.setAttributes( { fac_id: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/custom-fields', {
			title: 'Church Admin Custom Fields display',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Custom field attributes: ");
				console.log(props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
				
					el( ServerSideRender, {
						block: 'church-admin/custom-fields',
						attributes: props.attributes,
						key:'custom-fields-server-side'
					} ),
				
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {
						key:'controls'
						},
						el( CheckboxControl, {
							key:'loggedin',
							label: 'Require login?',
							value: props.attributes.loggedin,
							checked: props.attributes.loggedin,
							onChange: ( value ) => { props.setAttributes( { loggedin: value } ); },
						} ),
						el( TextControl, {
							key:'days',
							label: 'Days to show?',
							value: props.attributes.days,
							onChange: ( value ) => { props.setAttributes( { days: value } ); },
						} ),
						el( TextControl, {
							key:'Custom-field-id',
							label: 'Custom field',
							value: props.attributes.customField,
							onChange: ( value ) => { props.setAttributes( { customField: value } ); },
						} ),
						el( CheckboxControl, {
							key:'showYears',
							label: 'Show years?',
							value: props.attributes.showYears,
							checked: props.attributes.showYears,
							onChange: ( value ) => { props.setAttributes( { showYears: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					)
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/video-embed', {
			title: 'Church Admin Video Embed',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Video embed attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/video-embed',
						attributes: props.attributes,
						key:'video-embed-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'controls'},
						el( TextControl, {
							key:'url',
							label: 'Video share url?',
							value: props.attributes.url,
							onChange: ( value ) => { props.setAttributes( { url: value } ); },
						} ),
						
						el( CheckboxControl, {
							key:'views',
							label: 'Show number of views?',
							value: props.attributes.show_views,
							checked: props.attributes.show_views,
							onChange: ( value ) => { props.setAttributes( { show_views: value } ); },
						} ),
					el( SelectControl, {
							key:'page-alignment',
							label:  'Page Alignment (will adjust on live site!)' ,
							value: props.attributes.container,
							onChange: ( value ) => { props.setAttributes( { container: value } ); },
							options: [
								{ value: 'aligncenter', label:  'Centered'  },    
							{ value: 'alignwide', label:  'Wide'  },
							{ value: 'alignfull', label:  'Full width'  },
							],
						}),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						})
					)
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/ministries', {
			title: 'Church Admin Ministries',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Minsitries attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/ministries',
						attributes: props.attributes,
						key:'ministries-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'control'},
						el( TextControl, {
							key:'member-types',
							label: 'Member Types?',
							value: props.attributes.member_type_id,
							onChange: ( value ) => { props.setAttributes( { member_type_id: value } ); },
						} ),
						el( TextControl, {
							key:'ministry-ids',
							label: 'Ministry ids?',
							value: props.attributes.ministry_id,
							onChange: ( value ) => { props.setAttributes( { ministry_id: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						})
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/my-rota', {
			title: 'Church Admin My Rota',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("My rota attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/my-rota',
						attributes: props.attributes,
						key:'rota-server-side'
					} ),
					el( InspectorControls, {key:'controls'},
						
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						})
					),
					
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/attendance', {
			title: 'Church Admin Attendance',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Attendance attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/attendance',
						attributes: props.attributes,
						key:'attendance-server-side'
					} ),
					el( InspectorControls, {key:'control'},
						
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/giving', {
			title: 'Church Admin Giving',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Giving attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/giving',
						attributes: props.attributes,
						key:'giving-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'control'},
						el( TextControl, {
							key:'fund',
							label: 'Fund (optional)?',
							value: props.attributes.fund,
							onChange: ( value ) => { props.setAttributes( { fund: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
					
				];
			},
			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/pledges', {
			title: 'Church Admin Pledges',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Pledges attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/pledges',
						attributes: props.attributes,
						key:'pledges-server-side'
					} ),
					el( InspectorControls, {key:'control'},
						
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
					
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/volunteer', {
			title: 'Church Admin Volunteer',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Volunteer attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/volunteer',
						attributes: props.attributes,
						key:'volunteer-server-side'
					} ),
					el( InspectorControls, {key:'controls'},
						
						el( CheckboxControl, {
							key:'background',
							label: 'White background?',
							value: props.attributes.background,
							checked: props.attributes.background,
							onChange: ( value ) => { props.setAttributes( { background: value } ); },
						} )
					),
					
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/contact-form', {
			title: 'Church Admin Contact form',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Contact form attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/contact-form',
						attributes: props.attributes,
						key:'contact-server-side'
					} ),
					el( InspectorControls, {key:'controls'},
						
						el( CheckboxControl, {
							key:'background',
							label: 'White background?',
							value: props.attributes.background,
							checked: props.attributes.background,
							onChange: ( value ) => { props.setAttributes( { background: value } ); },
						} )
					),
					
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/graph', {
			title: 'Church Admin Graph',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Graph: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/graph',
						attributes: props.attributes,
						key:'graph-server-side'
					} ),
					el( InspectorControls, {},
						el( TextControl, {
							key:'width',
							label: 'Width in pixels',
							value: props.attributes.width,
							onChange: ( value ) => { props.setAttributes( { width: value } ); },
						} ),
					
					
						el( TextControl, {
							key:'height',
							label: 'Height in pixels',
							value: props.attributes.height,
							onChange: ( value ) => { props.setAttributes( { height: value } ); },
						} ),

						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),			
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/anniversaries', {
			title: 'Church Admin Anniversaries',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Anniversaries attributes: ")
				console.log(props.attributes);
				
					
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					
					el( ServerSideRender, {
						block: 'church-admin/anniversaries',
						attributes: props.attributes,
						key:'anniversaries-server-side'
					} ),
					el( InspectorControls, {key:'control'},
						el( TextControl, {
							key:'member-types',
							label: 'Member Types',
							multiple:true,
							value: props.attributes.member_type_id,
							onChange: ( value ) => { props.setAttributes( { member_type_id: value } ); },
							options: membTypeOptions,
						} ),
						
						el( TextControl, {
							key:'people-types',
							label:  'People Types?' ,
							multiple:true,
							value: props.attributes.people_types,
							onChange: ( value ) => { props.setAttributes( { people_types: value } ); },
							
						}),
						
						el( TextControl, {
							key:'days',
							label: 'Days to show',
							value: props.attributes.days,
							onChange: ( value ) => { props.setAttributes( { days: value } ); },
						} ),
					
						el( CheckboxControl, {
								key:'show-age',
								label: 'Show age',
								value: props.attributes.show_age,
								checked: props.attributes.show_age,
								onChange: ( value ) => { props.setAttributes( { show_age: value } ); },
							} ),
						el( CheckboxControl, {
							key:'show-email',
							label: 'Show email',
							value: props.attributes.show_email,
							checked: props.attributes.show_email,
							onChange: ( value ) => { props.setAttributes( { show_email: value } ); },
						} ),
						el( CheckboxControl, {
							key:'show-phone',
							label: 'Show phone',
							value: props.attributes.show_phone,
							checked: props.attributes.show_phone,
							onChange: ( value ) => { props.setAttributes( { show_phone: value } ); },
						} ),
						el( CheckboxControl, {
							key:'loggedin',
							label: 'Require login?',
							value: props.attributes.loggedin,
							checked: props.attributes.loggedin,
							onChange: ( value ) => { props.setAttributes( { loggedin: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
								{ value: 'bluegrey', label:  'Dark theme blue grey'  },
								{ value: 'coolgrey', label:  'Dark theme cool grey'  },
								{ value: 'warmgrey', label:  'Dark theme warm grey'  }
								
							],
						}),
					)
				];
			},
			

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/birthdays', {
			title: 'Church Admin Birthdays',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Birthdays attributes: ")
				console.log(props.attributes);
				
					
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					
					el( ServerSideRender, {
						block: 'church-admin/birthdays',
						attributes: props.attributes,
						key:'birthday-server-side'
					} ),
					el( InspectorControls, {key:'control'},
						el( TextControl, {
							key:'member-types',
							label: 'Member Types',
							multiple:true,
							value: props.attributes.member_type_id,
							onChange: ( value ) => { props.setAttributes( { member_type_id: value } ); },
							options: membTypeOptions,
						} ),
						
						el( TextControl, {
							key:'people-types',
							label:  'People Types?' ,
							multiple:true,
							value: props.attributes.people_types,
							onChange: ( value ) => { props.setAttributes( { people_types: value } ); },
							
						}),
						
						el( TextControl, {
							key:'days',
							label: 'Days to show',
							value: props.attributes.days,
							onChange: ( value ) => { props.setAttributes( { days: value } ); },
						} ),
					
						el( CheckboxControl, {
								key:'show-age',
								label: 'Show age',
								value: props.attributes.show_age,
								checked: props.attributes.show_age,
								onChange: ( value ) => { props.setAttributes( { show_age: value } ); },
							} ),
						el( CheckboxControl, {
							key:'show-email',
							label: 'Show email',
							value: props.attributes.show_email,
							checked: props.attributes.show_email,
							onChange: ( value ) => { props.setAttributes( { show_email: value } ); },
						} ),
						el( CheckboxControl, {
							key:'show-phone',
							label: 'Show phone',
							value: props.attributes.show_phone,
							checked: props.attributes.show_phone,
							onChange: ( value ) => { props.setAttributes( { show_phone: value } ); },
						} ),
						el( CheckboxControl, {
							key:'loggedin',
							label: 'Require login?',
							value: props.attributes.loggedin,
							checked: props.attributes.loggedin,
							onChange: ( value ) => { props.setAttributes( { loggedin: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
								{ value: 'bluegrey', label:  'Dark theme blue grey'  },
								{ value: 'coolgrey', label:  'Dark theme cool grey'  },
								{ value: 'warmgrey', label:  'Dark theme warm grey'  }
								
							],
						}),
					)
				];
			},
			

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/member-map', {
			title: 'Church Admin Member Map',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Member maps: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/member-map',
						attributes: props.attributes,
						show_in_rest:true,
						key:'member-map-server-side'
					} ),
					el( InspectorControls, {key:'control'},
						el( TextControl, {
							key:'member-types',
							label: 'Member Types',
							value: props.attributes.member_type_id,
							onChange: ( value ) => { props.setAttributes( { member_type_id: value } ); },
						} ),
						el( TextControl, {
							key:'zoom',
							label: 'Zoom',
							value: props.attributes.zoom,
							onChange: ( value ) => { props.setAttributes( { zoom: value } ); },
						} ),
						el( TextControl, {
							key:'width',
							label: 'Width',
							value: props.attributes.width,
							onChange: ( value ) => { props.setAttributes( { width: value } ); },
						} ),
						el( TextControl, {
							key:'height',
							label: 'Height',
							value: props.attributes.height,
							onChange: ( value ) => { props.setAttributes( { height: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
								{ value: 'bluegrey', label:  'Dark theme blue grey'  },
								{ value: 'coolgrey', label:  'Dark theme cool grey'  },
								{ value: 'warmgrey', label:  'Dark theme warm grey'  }
								
							],
						}),
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/spiritual-gifts', {
			title: 'Church Admin Spiritual gifts',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Gifts attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/spiritual-gifts',
						attributes: props.attributes,
						key:'gifts-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'control'},
						el( TextControl, {
							key:'admin-emails',
							label: 'Admin Emails',
							value: props.attributes.admin_email,
							onChange: ( value ) => { props.setAttributes( { admin_email_address: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
					
				];
			},
			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		registerBlockType( 'church-admin/not-available', {
			title: 'Church Admin user not available to serve',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/not-available',
						attributes: props.attributes,
						key:'not-available-server-side'
					} ),
					
					/*
					* InspectorControls lets you add controls to the Block sidebar. In this case,
					* we're adding a TextControl, which lets us edit the 'foo' attribute (which
					* we defined in the PHP). The onChange property is a little bit of magic to tell
					* the block editor to update the value of our 'foo' property, and to re-render
					* the block.
					*/
					
					el( InspectorControls, {key:'control'},
						
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
							{ value: 'bluegrey', label:  'Dark theme blue grey'  },
							{ value: 'coolgrey', label:  'Dark theme cool grey'  },
							{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							
							],
						}),
					),
					
				];
			},
			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} );
		//rota
		registerBlockType( 'church-admin/service-booking', {
			title: 'Church Admin Service Booking',
			icon: churchAdminIcon,
			category: 'widgets',
		
			/*
			* In most other blocks, you'd see an 'attributes' property being defined here.
			* We've defined attributes in the PHP, that information is automatically sent
			* to the block editor, so we don't need to redefine it here.
			*/

			edit: function( props ) {
				console.log("Booking attributes: "+props.attributes);
				return [
					/*
					* The ServerSideRender element uses the REST API to automatically call
					* php_block_render() in your PHP code whenever it needs to get an updated
					* view of the block.
					*/
					el( ServerSideRender, {
						block: 'church-admin/service-booking',
						attributes: props.attributes,
						key:'booking-server-side'
					} ),
					
					el( InspectorControls, {key:'control'},
						el( SelectControl, {
							key:'service-id',
							label:  'Which Service?' ,
							value: props.attributes.service_id,
							onChange: ( value ) => { props.setAttributes( { service_id: value } ); },
							options:serviceOptions,
						}),
						el( TextControl, {
							key:'days',
							label: 'How many days to show?',
							value: props.attributes.days,
							onChange: ( value ) => { props.setAttributes( { days: value } ); },
						} ),
					el( TextControl, {
							key:'admin-emails',
							label: 'Admin Emails',
							value: props.attributes.admin_email,
							onChange: ( value ) => { props.setAttributes( { admin_email_address: value } ); },
						} ),
						el( TextareaControl, {
							key:'email-text',
							label: 'Email text with more details',
							value: props.attributes.email_text,
							onChange: ( value ) => { props.setAttributes( { email_text: value } ); },
						} ),
						el( TextControl, {
							key:'max-people',
							label: 'Max people per booking',
							value: props.attributes.max_fields,
							onChange: ( value ) => { props.setAttributes( { max_fields: value } ); },
						} ),
					el( SelectControl, {
							key:'booking-mode',
							label:  'Booking mode' ,
							value: props.attributes.booking_mode,
							onChange: ( value ) => { props.setAttributes( { booking_mode: value } ); },
							options: [
								{ value: 'bubbles', label:  'Bubbles'  },    
							{ value: 'individuals', label:  'Individuals'  }
							
							],
						}),
						el( CheckboxControl, {
							key:'loggedin',
							label: 'Require login',
							value: props.attributes.loggedin,
							checked: props.attributes.loggedin,
							onChange: ( value ) => { props.setAttributes( { loggedin: value } ); },
						} ),
						el( SelectControl, {
							key:'color-scheme',
							label:  'Color scheme?' ,
							value: props.attributes.colorscheme,
							onChange: ( value ) => { props.setAttributes( { colorscheme: value } ); },
							options: [
								{ value: '', label:  'No color theming'  }, 
								{ value: 'white', label:  'White background'  },   
								{ value: 'bluegrey', label:  'Dark theme blue grey'  },
								{ value: 'coolgrey', label:  'Dark theme cool grey'  },
								{ value: 'warmgrey', label:  'Dark theme warm grey'  }
							],
						}),
					),
				];
			},

			// We're going to be rendering in PHP, so save() can just return null.
			save: function() {
				return null;
			},
		} )

    
})
(
	window.wp.blocks,
    window.wp.editor,
	window.wp.element,
    window.wp.components,
    window.wp.blockEditor,
	window.wp.serverSideRender,
);
