Ext.define('Shopware.apps.SwagDisableArticleDiscount.model.Articles', {
	extend: 'Ext.data.Model',
	idProperty: 'ordernumber',
	fields: [ 'articleID', 'name', 'ordernumber' ],
	proxy: {
		type: 'ajax',
        actionMethods: 'POST',
        api: {
            read: '{url controller="SwagDisableArticleDiscount" action="getArticles"}'
        },
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});