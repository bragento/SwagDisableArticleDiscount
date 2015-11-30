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
 * Shopware SwagDisableArticleDiscount Plugin - Bootstrap
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagDisableArticleDiscount
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Frontend_SwagDisableArticleDiscount_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installs all events and the necessary table.
     *
     * @return array|bool
     */
    public function install()
	{
		$this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_SwagDisableArticleDiscount', 'onGetControllerPathBackend');

		$this->subscribeEvent('sBasket::sInsertDiscount::before', 'beforeInsertDiscount');
		$this->subscribeEvent('sBasket::sInsertDiscount::after', 'afterInsertDiscount');

        $parent = $this->Menu()->findOneBy(array('label' => 'Einstellungen'));

		$this->createMenuItem(array(
			'label' => 'Artikel vom Rabatt ausschließen',
			'controller' => 'SwagDisableArticleDiscount',
			'action' => 'index',
			'class' => 'sprite-shopping-basket',
			'active' => 1,
			'parent' => $parent
			)
		);

		Shopware()->Db()->query("CREATE TABLE IF NOT EXISTS `s_plugin_articles_disable` (
			`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`articleID` INT( 11 ) NOT NULL ,
			`name` VARCHAR( 255 ) NOT NULL ,
			`ordernumber` VARCHAR( 255 ) NOT NULL
		)");

		return array('success' => true, 'invalidateCache' => array('backend'));
	}


	/**
	  * Standard update method.
	  */
	public function update()
	{
		return true;
	}

    /**
     * Returns the path to the plugin-controller.
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
	{
		return $this->Path().'SwagDisableArticleDiscount.php';
	}

    /**
     * Returns the meta information about the plugin
     * as an array.
     * Keep in mind that the plugin description located
     * in the info.txt.
     *
     * @public
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version'     => $this->getVersion(),
            'label'       => $this->getLabel(),
            'link'        => 'http://www.shopware.de',
            'description' => file_get_contents($this->Path() . 'info.txt')
        );
    }

    /**
     * Returns the newest version of the plugin.
     *
     * @return string
     */
    public function getVersion()
	{
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .'plugin.json'), true);

        if ($info) {
            return $info['currentVersion'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
	}

    /**
     * Returns the plugin-label.
     *
     * @return string
     */
    public function getLabel()
    {
        return "Artikel vom Rabatt ausschließen";
    }

	/**
	 * Lists all disabled articles from s_plugin_articles_disable and compares them with those in the basket
	 * Returns the matches
     *
	 * @return array with all disabled articles
	 */
	public function getDisabledArticles()
	{
		// Content of the basket
		$sql = "SELECT articleID, ordernumber FROM s_order_basket WHERE sessionID = ? AND articleID != 0";
		$resultsBasket = Shopware()->Db()->fetchAll($sql, array(Shopware()->SessionID()));

		$disableArticleOrdernumbers = array();

		$sql = "SELECT ordernumber, articleID FROM s_plugin_articles_disable WHERE articleID = ? AND ordernumber = ?";
		foreach($resultsBasket as $result) {
			//Content of basket and s_plugin_articles_disable, compares those two
			$disabledArticles = Shopware()->Db()->fetchAll($sql, array($result['articleID'], $result['ordernumber']));
			if(!empty($disabledArticles)) {
				foreach($disabledArticles as $disabledArticle) {
					$disableArticleOrdernumbers[] = $disabledArticle["ordernumber"];
				}
			}
		}

		return $disableArticleOrdernumbers;
	}

	/**
	 * Before adding the discount to the basket to stop adding the discount
     *
	 * @param Enlight_Hook_HookArgs $args
	 * @return void
	 */
	public function beforeInsertDiscount(Enlight_Hook_HookArgs $args)
	{
		// Content of the basket
		$sql = "SELECT articleID, ordernumber FROM s_order_basket WHERE sessionID = ? AND articleID != 0";
		$resultsBasket = Shopware()->Db()->fetchAll($sql, array(Shopware()->SessionID()));

		$sql = "SELECT ordernumber, articleID FROM s_plugin_articles_disable WHERE articleID = ? AND ordernumber = ?";

		foreach($resultsBasket as $result) {
			//Content of basket and s_plugin_articles_disable, compares those two
			$disabledArticles = Shopware()->Db()->fetchAll($sql, array($result['articleID'], $result['ordernumber']));

			if(!empty($disabledArticles)) {
				foreach($disabledArticles as $disabledArticle) {
					$statement = "UPDATE s_order_basket Set modus=4 WHERE modus=0 AND ordernumber=?";
					Shopware()->Db()->query($statement, array($disabledArticle['ordernumber']));
                }
			}
		}
	}

	/**
	 * After trying to add a discount and resetting the changes
     *
	 * @param Enlight_Hook_HookArgs $args
	 * @return void
	 */
	public function afterInsertDiscount(Enlight_Hook_HookArgs $args)
	{
		// Content of the basket
		$sql = "SELECT articleID, ordernumber FROM s_order_basket WHERE sessionID = ? AND articleID != 0";
		$resultsBasket = Shopware()->Db()->fetchAll($sql, array(Shopware()->SessionID()));

		$sql = "SELECT ordernumber, articleID FROM s_plugin_articles_disable WHERE articleID = ? AND ordernumber = ?";

		foreach($resultsBasket as $result) {
			//Content of basket and s_plugin_articles_disable, compares those two
			$disabledArticles = Shopware()->Db()->fetchAll($sql, array($result['articleID'], $result['ordernumber']));
			if(!empty($disabledArticles)) {
				foreach($disabledArticles as $disabledArticle) {
					$statement = "UPDATE s_order_basket Set modus=0 WHERE modus=4 AND ordernumber=?";
					Shopware()->Db()->query($statement, array($disabledArticle['ordernumber']));
				}
			}
		}
	}
}