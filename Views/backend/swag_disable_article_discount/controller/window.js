Ext.define('Shopware.apps.SwagDisableArticleDiscount.controller.Window', {
    extend: 'Ext.app.Controller',

	refs:[
		{ ref:'mainWindow', selector:'disableArticleDiscount-window' }
	],

    init: function() {
        var me = this;

        me.control({
			'disableArticleDiscount-window textfield[action=searchArticle]':{
				fieldchange: me.onSearchArticle
			},
			'disableArticleDiscount-window textfield[action=searchDisabledArticle]':{
				fieldchange: me.onSearchDisabledArticle
			},
			'disableArticleDiscount-window button[action=saveSettings]':{
				click: me.onSaveSettings
			}
        });

		me.subApplication.articleStore.on('beforeload', me.onStoreBeforeLoad, me)
    },

	onStoreBeforeLoad: function(articleStore){
		var me = this,
			disabledArticleStore = me.subApplication.disabledArticleStore,
			ids = '';

		disabledArticleStore.each(function(item){
			ids += item.get('articleID')+',';
		});

		Ext.apply(articleStore.getProxy().extraParams, {
			disabledIDs: ids
		});
	},

	onSaveSettings: function(){
		var me = this,
		formPanel = me.getMainWindow().form,
		form = formPanel.getForm(),
		store = form.getRecord();

 		store.each(function(record) {
			 record.setDirty();
		});

		store.save({
			success: function () {
                store.load();
				Shopware.Notification.createGrowlMessage('DisableArticleDiscount','Einträge wurden erfolgreich gespeichert','DisableArticleDiscount');
			},
			failure: function() {
				Shopware.Notification.createGrowlMessage('DisableArticleDiscount','Beim Speichern ist ein Fehler aufgetreten','DisableArticleDiscount');
			}
		});
		
	},

	onSearchArticle: function(field){
		var me = this,
			store = me.getStore('Articles');

		if(field.getValue().length == 0){
			store.clearFilter();
		}
		else{
			store.filters.clear();
			store.filter('searchValue',field.getValue());
		}
	},

	onSearchDisabledArticle: function(field){
		var me = this,
			store = me.getStore('DisabledArticles');

		if(field.getValue().length == 0){
			store.clearFilter();
		}
		else{
			store.filters.clear();
			store.filter('searchValue',field.getValue());
		}
	}
});