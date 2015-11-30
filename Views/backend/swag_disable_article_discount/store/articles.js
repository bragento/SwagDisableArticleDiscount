Ext.define('Shopware.apps.SwagDisableArticleDiscount.store.Articles', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.SwagDisableArticleDiscount.model.Articles',
    pageSize: 20,
    remoteFilter: true,
    batch: true
});