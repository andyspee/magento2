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
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order history tab
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class History extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/history.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Compose and get order full history.
     * Consists of the status history comments as well as of invoices, shipments and creditmemos creations
     *
     * @return array
     */
    public function getFullHistory()
    {
        $order = $this->getOrder();

        $history = array();
        foreach ($order->getAllStatusHistory() as $orderComment) {
            $history[] = $this->_prepareHistoryItem(
                $orderComment->getStatusLabel(),
                $orderComment->getIsCustomerNotified(),
                $orderComment->getCreatedAtDate(),
                $orderComment->getComment()
            );
        }

        foreach ($order->getCreditmemosCollection() as $_memo) {
            $history[] = $this->_prepareHistoryItem(
                __('Credit memo #%1 created', $_memo->getIncrementId()),
                $_memo->getEmailSent(),
                $_memo->getCreatedAtDate()
            );

            foreach ($_memo->getCommentsCollection() as $_comment) {
                $history[] = $this->_prepareHistoryItem(
                    __('Credit memo #%1 comment added', $_memo->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAtDate(),
                    $_comment->getComment()
                );
            }
        }

        foreach ($order->getShipmentsCollection() as $_shipment) {
            $history[] = $this->_prepareHistoryItem(
                __('Shipment #%1 created', $_shipment->getIncrementId()),
                $_shipment->getEmailSent(),
                $_shipment->getCreatedAtDate()
            );

            foreach ($_shipment->getCommentsCollection() as $_comment) {
                $history[] = $this->_prepareHistoryItem(
                    __('Shipment #%1 comment added', $_shipment->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAtDate(),
                    $_comment->getComment()
                );
            }
        }

        foreach ($order->getInvoiceCollection() as $_invoice) {
            $history[] = $this->_prepareHistoryItem(
                __('Invoice #%1 created', $_invoice->getIncrementId()),
                $_invoice->getEmailSent(),
                $_invoice->getCreatedAtDate()
            );

            foreach ($_invoice->getCommentsCollection() as $_comment) {
                $history[] = $this->_prepareHistoryItem(
                    __('Invoice #%1 comment added', $_invoice->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAtDate(),
                    $_comment->getComment()
                );
            }
        }

        foreach ($order->getTracksCollection() as $_track) {
            $history[] = $this->_prepareHistoryItem(
                __('Tracking number %1 for %2 assigned', $_track->getNumber(), $_track->getTitle()),
                false,
                $_track->getCreatedAtDate()
            );
        }

        usort($history, array(__CLASS__, "_sortHistoryByTimestamp"));
        return $history;
    }

    /**
     * Status history date/datetime getter
     *
     * @param array $item
     * @param string $dateType
     * @param string $format
     * @return string
     */
    public function getItemCreatedAt(array $item, $dateType = 'date', $format = 'medium')
    {
        if (!isset($item['created_at'])) {
            return '';
        }
        if ('date' === $dateType) {
            return $this->formatDate($item['created_at'], $format);
        }
        return $this->formatTime($item['created_at'], $format);
    }

    /**
     * Status history item title getter
     *
     * @param array $item
     * @return string
     */
    public function getItemTitle(array $item)
    {
        return isset($item['title']) ? $this->escapeHtml($item['title']) : '';
    }

    /**
     * Check whether status history comment is with customer notification
     *
     * @param array $item
     * @param bool $isSimpleCheck
     * @return bool
     */
    public function isItemNotified(array $item, $isSimpleCheck = true)
    {
        if ($isSimpleCheck) {
            return !empty($item['notified']);
        }
        return isset($item['notified']) && false !== $item['notified'];
    }

    /**
     * Status history item comment getter
     *
     * @param array $item
     * @return string
     */
    public function getItemComment(array $item)
    {
        $allowedTags = array('b', 'br', 'strong', 'i', 'u');
        return isset($item['comment']) ? $this->escapeHtml($item['comment'], $allowedTags) : '';
    }

    /**
     * Map history items as array
     *
     * @param string $label
     * @param bool $notified
     * @param \Magento\Framework\Stdlib\DateTime\DateInterface $created
     * @param string $comment
     * @return array
     */
    protected function _prepareHistoryItem($label, $notified, $created, $comment = '')
    {
        return array('title' => $label, 'notified' => $notified, 'comment' => $comment, 'created_at' => $created);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Comments History');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order History');
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('sales/*/commentsHistory', array('_current' => true));
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Customer Notification Applicable check method
     *
     * @param array $historyItem
     * @return bool
     */
    public function isCustomerNotificationNotApplicable($historyItem)
    {
        return $historyItem['notified'] ==
            \Magento\Sales\Model\Order\Status\History::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
    }

    /**
     * Comparison For Sorting History By Timestamp
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    private static function _sortHistoryByTimestamp($a, $b)
    {
        $createdAtA = $a['created_at'];
        $createdAtB = $b['created_at'];

        /** @var $createdAta \Magento\Framework\Stdlib\DateTime\DateInterface */
        if ($createdAtA->getTimestamp() == $createdAtB->getTimestamp()) {
            return 0;
        }
        return $createdAtA->getTimestamp() < $createdAtB->getTimestamp() ? -1 : 1;
    }
}
