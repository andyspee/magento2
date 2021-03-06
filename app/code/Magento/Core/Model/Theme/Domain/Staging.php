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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Staging theme model class
 */
namespace Magento\Core\Model\Theme\Domain;

class Staging implements \Magento\Framework\View\Design\Theme\Domain\StagingInterface
{
    /**
     * Staging theme model instance
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    protected $_theme;

    /**
     * @var \Magento\Theme\Model\CopyService
     */
    protected $_themeCopyService;

    /**
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Theme\Model\CopyService $themeCopyService
     */
    public function __construct(
        \Magento\Framework\View\Design\ThemeInterface $theme,
        \Magento\Theme\Model\CopyService $themeCopyService
    ) {
        $this->_theme = $theme;
        $this->_themeCopyService = $themeCopyService;
    }

    /**
     * Copy changes from 'staging' theme
     *
     * @return \Magento\Framework\View\Design\Theme\Domain\StagingInterface
     */
    public function updateFromStagingTheme()
    {
        $this->_themeCopyService->copy($this->_theme, $this->_theme->getParentTheme());
        return $this;
    }
}
