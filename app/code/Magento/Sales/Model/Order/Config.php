<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Order;

/**
 * Order configuration model
 */
class Config
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Status\Collection
     */
    protected $_collection;

    /**
     * Statuses per state array
     *
     * @var array
     */
    protected $_stateStatuses;

    /**
     * @var array
     */
    private $_states;

    /**
     * @var Status
     */
    protected $_orderStatusFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Status\CollectionFactory
     */
    protected $_orderStatusCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory
     * @param \Magento\Sales\Model\Resource\Order\Status\CollectionFactory $orderStatusCollectionFactory
     */
    public function __construct(
        \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory,
        \Magento\Sales\Model\Resource\Order\Status\CollectionFactory $orderStatusCollectionFactory
    ) {
        $this->_orderStatusFactory = $orderStatusFactory;
        $this->_orderStatusCollectionFactory = $orderStatusCollectionFactory;
    }

    /**
     * @return \Magento\Sales\Model\Resource\Order\Status\Collection
     */
    protected function _getCollection()
    {
        if ($this->_collection == null) {
            $this->_collection = $this->_orderStatusCollectionFactory->create()->joinStates();
        }
        return $this->_collection;
    }

    /**
     * @param string $state
     * @return Status|null
     */
    protected function _getState($state)
    {
        foreach ($this->_getCollection() as $item) {
            if ($item->getData('state') == $state) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Retrieve default status for state
     *
     * @param   string $state
     * @return  string
     */
    public function getStateDefaultStatus($state)
    {
        $status = false;
        $stateNode = $this->_getState($state);
        if ($stateNode) {
            $status = $this->_orderStatusFactory->create()->loadDefaultByState($state);
            $status = $status->getStatus();
        }
        return $status;
    }

    /**
     * Retrieve status label
     *
     * @param   string $code
     * @return  string
     */
    public function getStatusLabel($code)
    {
        $status = $this->_orderStatusFactory->create()->load($code);
        return $status->getStoreLabel();
    }

    /**
     * State label getter
     *
     * @param   string $state
     * @return  string
     */
    public function getStateLabel($state)
    {
        if ($stateItem = $this->_getState($state)) {
            $label = $stateItem->getData('label');
            return __($label);
        }
        return $state;
    }

    /**
     * Retrieve all statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        $statuses = $this->_orderStatusCollectionFactory->create()->toOptionHash();
        return $statuses;
    }

    /**
     * Order states getter
     *
     * @return array
     */
    public function getStates()
    {
        $states = array();
        foreach ($this->_getCollection() as $item) {
            $states[$item->getState()] = __($item->getData('label'));
        }
        return $states;
    }

    /**
     * Retrieve statuses available for state
     * Get all possible statuses, or for specified state, or specified states array
     * Add labels by default. Return plain array of statuses, if no labels.
     *
     * @param mixed $state
     * @param bool $addLabels
     * @return array
     */
    public function getStateStatuses($state, $addLabels = true)
    {
        $key = md5(serialize(array($state, $addLabels)));
        if (isset($this->_stateStatuses[$key])) {
            return $this->_stateStatuses[$key];
        }
        $statuses = array();

        if (!is_array($state)) {
            $state = array($state);
        }
        foreach ($state as $_state) {
            $stateNode = $this->_getState($_state);
            if ($stateNode) {
                $collection = $this->_orderStatusCollectionFactory->create()->addStateFilter($_state)->orderByLabel();
                foreach ($collection as $item) {
                    $status = $item->getData('status');
                    if ($addLabels) {
                        $statuses[$status] = $item->getStoreLabel();
                    } else {
                        $statuses[] = $status;
                    }
                }
            }
        }
        $this->_stateStatuses[$key] = $statuses;
        return $statuses;
    }

    /**
     * Retrieve states which are visible on front end
     *
     * @return array
     */
    public function getVisibleOnFrontStates()
    {
        return $this->_getStates(true);
    }

    /**
     * Get order states, visible on frontend
     *
     * @return array
     */
    public function getInvisibleOnFrontStates()
    {
        return $this->_getStates(false);
    }

    /**
     * @param bool $visibility
     *
     * @return array
     */
    protected function _getStates($visibility)
    {
        $visibility = (bool)$visibility;
        if ($this->_states == null) {
            foreach ($this->_getCollection() as $item) {
                $visible = (bool)$item->getData('visible_on_front');
                $this->_states[$visible][] = $item->getData('state');
            }
        }
        return $this->_states[$visibility];
    }
}
