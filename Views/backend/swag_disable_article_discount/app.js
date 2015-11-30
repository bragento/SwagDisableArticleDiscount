Ext.define('Shopware.apps.SwagDisableArticleDiscount', {
	extend: 'Enlight.app.SubApplication',
	name: 'Shopware.apps.SwagDisableArticleDiscount',
	bulkLoad: true,
	loadPath:'{url action=load}',
	controllers: [ 'Main' , 'Window'],
	models:[ 'Articles', 'DisabledArticles' ],
	views: [ 'main.Window' ],
	stores:[ 'Articles','DisabledArticles'],

	launch: function() {
		var me = this,
		mainController = me.getController('Main');

//		return mainController.mainWindow;
	}
});