Ext.define('Shopware.apps.SwagDisableArticleDiscount.store.DisabledArticles', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.SwagDisableArticleDiscount.model.DisabledArticles',
    remoteFilter: true,
	pageSize: 10000,
    batch: true,
	proxy: {
		type: 'ajax',
		url: '{url controller="SwagDisableArticleDiscount" action="getDisabledArticles"}',
		api: {
			read: '{url controller="SwagDisableArticleDiscount" action="getDisabledArticles"}',
			update: '{url controller="SwagDisableArticleDiscount" action="saveDisabledArticles"}',
            destroy: '{url controller="SwagDisableArticleDiscount" action="deleteDisabledArticle"}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});