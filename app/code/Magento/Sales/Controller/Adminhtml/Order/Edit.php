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
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * Adminhtml sales order edit controller
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Edit extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Start edit order initialization
     *
     * @return void
     */
    public function startAction()
    {
        $this->_getSession()->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);

        try {
            if ($order->getId()) {
                $this->_getSession()->setUseOldShippingMethod(true);
                $this->_getOrderCreateModel()->initFromOrder($order);
                $this->_redirect('sales/*');
            } else {
                $this->_redirect('sales/order/');
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('sales/order/view', array('order_id' => $orderId));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, $e->getMessage());
            $this->_redirect('sales/order/view', array('order_id' => $orderId));
        }
    }

    /**
     * Index page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Orders'));
        $this->_title->add(__('Edit Order'));
        $this->_view->loadLayout();

        $this->_initSession()->_setActiveMenu('Magento_Sales::sales_order');
        $this->_view->renderLayout();
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::actions_edit');
    }
}
