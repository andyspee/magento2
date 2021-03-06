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
 * @package     Magento_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block;

/**
 * Test class for \Magento\Backend\Block\Widget
 *
 * @magentoAppArea adminhtml
 */
class WidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Backend\Block\Widget::getButtonHtml
     */
    public function testGetButtonHtml()
    {
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Layout',
            array('area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
        );
        $layout->getUpdate()->load();
        $layout->generateXml()->generateElements();

        $widget = $layout->createBlock('Magento\Backend\Block\Widget');

        $this->assertRegExp(
            '/<button.*onclick\=\"this.form.submit\(\)\".*\>[\s\S]*Button Label[\s\S]*<\/button>/iu',
            $widget->getButtonHtml('Button Label', 'this.form.submit()')
        );
    }

    /**
     * Case when two buttons will be created in same parent block
     *
     * @covers \Magento\Backend\Block\Widget::getButtonHtml
     */
    public function testGetButtonHtmlForTwoButtonsInOneBlock()
    {
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Layout',
            array('area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
        );
        $layout->getUpdate()->load();
        $layout->generateXml()->generateElements();

        $widget = $layout->createBlock('Magento\Backend\Block\Widget');

        $this->assertRegExp(
            '/<button.*onclick\=\"this.form.submit\(\)\".*\>[\s\S]*Button Label[\s\S]*<\/button>/iu',
            $widget->getButtonHtml('Button Label', 'this.form.submit()')
        );

        $this->assertRegExp(
            '/<button.*onclick\=\"this.form.submit\(\)\".*\>[\s\S]*Button Label2[\s\S]*<\/button>/iu',
            $widget->getButtonHtml('Button Label2', 'this.form.submit()')
        );
    }

    public function testGetSuffixId()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Backend\Block\Widget');
        $this->assertStringEndsNotWith('_test', $block->getSuffixId('suffix'));
        $this->assertStringEndsWith('_test', $block->getSuffixId('test'));
    }
}
