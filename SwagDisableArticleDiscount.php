<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware SwagDisableArticleDiscount Plugin - SwagDisableArticleDiscount Backend Controller
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagDisableArticleDiscount\Controllers\Backend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */

class Shopware_Controllers_Backend_SwagDisableArticleDiscount extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Sets a new default-template-directory.
     */
    public function init()
	{
		$this->View()->addTemplateDir(dirname(__FILE__) . "/Views/");
		parent::init();
	}

    /**
     * Loads the app-file of the backend-module.
     */
    public function indexAction()
	{
		$this->View()->loadTemplate("backend/swag_disable_article_discount/app.js");
	}

    /**
     * Function to get all normal articles for the backend-module.
     */
    public function getArticlesAction()
	{
		$searchOptions = $this->getSearchOptions();
		$limit = $searchOptions['limit'];
		$start = $searchOptions['start'];
		$disabledIDs = substr_replace($this->request()->disabledIDs ,"",-1);
		$searchValue = $searchOptions['searchValue'];

		$sql_search = 'WHERE a.id NOT IN (SELECT articleID FROM s_plugin_articles_disable)';

		if(!empty($disabledIDs)){
			$disabledIDs = explode(',', $disabledIDs);

			foreach($disabledIDs as &$disabledID){
				$disabledID = intval($disabledID);
			}

			$disabledIDs = implode($disabledIDs, ',');
			
			$sql_search .= " AND a.id NOT IN ( {$disabledIDs} ) ";
		}

		if(!empty($searchValue)) {
			$search = Shopware()->Db()->quote('%' . $searchValue . '%');
			$sql_search .= sprintf("
				AND (ad.ordernumber LIKE %s
				OR a.name LIKE %s)
			", $search, $search);
		}

		$sql = "
			SELECT
				SQL_CALC_FOUND_ROWS
				a.id, ad.articleID, a.name, ad.ordernumber
			FROM s_articles as a

			INNER JOIN s_articles_details as ad
			ON ad.articleID = a.id
			AND ad.kind = 1

			{$sql_search}

			GROUP BY a.`id`
			ORDER BY ad.articleID ASC
			LIMIT :start, :limit
		";

		$prepared = Shopware()->Db()->prepare($sql);
		$prepared->bindParam(':start', $start, PDO::PARAM_INT);
		$prepared->bindParam(':limit', $limit, PDO::PARAM_INT);
		$prepared->execute();

		$articles = $prepared->fetchAll();

		$countArticles = Shopware()->Db()->fetchOne(" SELECT FOUND_ROWS() ");

		$this->View()->assign(array("success" => true, "data" => $articles, "total" => $countArticles));
	}

    /**
     * Function to get all disabled articles from s_plugin_articles_disable.
     */
    public function getDisabledArticlesAction()
	{
		$searchOptions = $this->getSearchOptions();
		$limit = $searchOptions['limit'];
		$start = $searchOptions['start'];
		$searchValue = $searchOptions['searchValue'];

		$sql_search = '';

		if(!empty($searchValue)) {
			$search = Shopware()->Db()->quote('%' . $searchValue . '%');
			$sql_search = sprintf("
				WHERE ad.ordernumber LIKE %s
				OR ad.name LIKE %s
			", $search, $search);
		}

		$sql = "
			SELECT
				SQL_CALC_FOUND_ROWS
				ad.id, ad.articleID, ad.name, ad.ordernumber
			FROM s_plugin_articles_disable as ad

			{$sql_search}

			ORDER BY ad.articleID ASC
			LIMIT :start, :limit
		";

		$prepared = Shopware()->Db()->prepare($sql);
		$prepared->bindParam(':start', $start, PDO::PARAM_INT);
		$prepared->bindParam(':limit', $limit, PDO::PARAM_INT);
		$prepared->execute();

		$disabledArticles = $prepared->fetchAll();

		$countDisabledArticles = Shopware()->Db()->fetchOne(" SELECT FOUND_ROWS() ");

		$this->View()->assign(array("success" => true, "data" => $disabledArticles, "total" => $countDisabledArticles));
	}

    /**
     * Helper method to convert the filter to an useful array.
     *
     * @return array
     */
    private function getSearchOptions()
	{
		$request = $this->request();
		$searchOptions = array();

		$filter = $request->filter;
		$searchField = $filter[0];

		$searchOptions['searchValue'] = $searchField['value'];

		$start = intval($request->start);
		$limit = intval($request->limit);
		if(empty($start)) {
			$start = 0;
		}
		if(empty($limit)) {
			$limit = 20;
		}
		$searchOptions['start'] = $start;
		$searchOptions['limit'] = $limit;

		return $searchOptions;
	}

    /**
     * Method to save the disabled articles.
     * This method always removes all articles before re-setting them.
     */
    public function saveDisabledArticlesAction()
	{
		$params = $this->Request()->getPost();

		if(!is_array($params[0])){
			$params = array($params);
		}

		Shopware()->Db()->query("TRUNCATE TABLE `s_plugin_articles_disable`");

		$query = 'INSERT INTO s_plugin_articles_disable (articleID, name, ordernumber) VALUES (?, ?, ?)';
        $query = Shopware()->Db()->prepare($query);

		foreach($params as $param){
            $query->execute(array(
                $param['articleID'],
                $param['name'],
                $param['ordernumber'],
            ));
		}

		$this->View()->assign(array("success" => true));
	}

    /**
     * Method to delete the disabled articles.
     * They're only removed from s_plugin_articles_disable.
     */
    public function deleteDisabledArticleAction(){
        $params = $this->Request()->getPost();

        if(!is_array($params[0])){
            $params = array($params);
        }
        $ids = array();
        foreach($params as $param) {
            $ids[] = $param['articleID'];
        }
        $ids = Shopware()->Db()->quote($ids);
        $sql = "DELETE FROM s_plugin_articles_disable WHERE articleID IN ($ids)";
        Shopware()->Db()->exec($sql);
    }
}