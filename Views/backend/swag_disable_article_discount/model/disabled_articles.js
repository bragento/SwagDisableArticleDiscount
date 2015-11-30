Ext.define('Shopware.apps.SwagDisableArticleDiscount.model.DisabledArticles', {
	extend: 'Ext.data.Model',
	idProperty: 'ordernumber',
	fields: [ 'articleID', 'name', 'ordernumber' ]
});