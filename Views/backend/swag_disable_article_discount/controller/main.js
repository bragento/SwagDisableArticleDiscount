Ext.define('Shopware.apps.SwagDisableArticleDiscount.controller.Main', {
	extend: 'Ext.app.Controller',
	mainWindow: null,

	init: function() {
		var me = this;
		me.subApplication.articleStore = me.getStore('Articles');
		me.subApplication.disabledArticleStore = me.getStore('DisabledArticles');

		me.subApplication.disabledArticleStore.load({
			scope:this,
			callback: function (records) {
				me.getView('main.Window').create({
					records: records,
					disabledArticleStore: me.subApplication.disabledArticleStore,
					articleStore: me.subApplication.articleStore
				});
			}
		});

		me.callParent(arguments);
	}
});