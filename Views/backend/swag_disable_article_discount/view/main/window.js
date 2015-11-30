Ext.define('Shopware.apps.SwagDisableArticleDiscount.view.main.Window', {
	extend: 'Enlight.app.Window',
    title: 'Artikel von Rabatt ausschließen',
    alias: 'widget.disableArticleDiscount-window',
    border: 0,
    autoShow: true,
	layout: 'fit',
	height: 600,
    width: 1200,
	resizable: false,
	stateful:true,
	stateId:'disableArticleDiscount-window',

    initComponent: function() {
        var me = this;

		me.form = Ext.create('Ext.form.Panel', {
            border: 0,
			items:[
				me.getFormItems(),
				me.getSaveToolbar()
			]
		});

		me.items = [
			me.form
		];

		me.callParent(arguments);

		me.form.loadRecord(me.disabledArticleStore);
    },

	getFormItems: function() {
		var me = this;
		return {
			xtype:'ddselector',
			hideHeaders: false,
            refreshStore: Ext.emptyFn,
			fromTitle: 'Artikel mit Rabatt',
			toTitle: 'Ausgeschlossen vom Rabatt',
			fromStore: me.articleStore,
			selectedItems: me.disabledArticleStore,
			fromColumns: me.getColumns(),
			toColumns: me.getColumns(),
			gridHeight: 529,
			fromFieldDockedItems: [
				me.getToolbar(me.getSearchField('searchArticle')),
				me.getPagingToolbar(me.articleStore)],
			toFieldDockedItems: [
				me.getToolbar(me.getSearchField('searchDisabledArticle'))],
			buttons:[ 'add','remove' ],
			buttonsText: {
				add: "Add",
				remove: "Remove"
			}
		}
	},

	getPagingToolbar: function(store) {
		return {
			dock: 'bottom',
			xtype: 'pagingtoolbar',
			displayInfo: true,
			store: store
		}
	},

	getSaveToolbar: function() {
		return Ext.create('Ext.toolbar.Toolbar', {
			dock: 'bottom',
			ui: 'shopware-ui',
			items:[
				'->',
				{
					xtype: 'button',
                    cls: 'primary',
					text: 'Speichern',
					name: 'saveBtn',
					action: 'saveSettings',
					width: 170
				},
				{ xtype: 'tbspacer', width: 6 }
			]
		});
	},

	getColumns: function() {
		return [
			{
				header: 'Name',
				dataIndex: 'name',
				flex: 3
			}, {
				header: 'Artikel ID',
				dataIndex: 'articleID',
				flex: 1
			}, {
				header: 'Bestellnummer',
				dataIndex: 'ordernumber',
				flex: 2
			}
		];
	},

	getToolbar:function (items) {
	    return Ext.create('Ext.toolbar.Toolbar', {
	        dock: 'top',
			ui: 'shopware-ui',
			items: [
				'->',
				items,
				{ xtype: 'tbspacer', width: 6 }
			]
		});
	},

	getSearchField: function(action){
		var searchField = Ext.create('Ext.form.field.Text',{
			name: 'searchfield',
			cls: 'searchfield',
			action: action,
			width: 170,
			enableKeyEvents: true,
			emptyText: 'Suchen...',
			listeners: {
				buffer: 500,
				keyup: function() {
					if(this.getValue().length >= 3 || this.getValue().length<1) {
						this.fireEvent('fieldchange', this);
					}
				}
			}
		});
		searchField.addEvents('fieldchange');
		return searchField;
	}
});