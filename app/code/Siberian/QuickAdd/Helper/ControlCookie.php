<?php
/**
 * @class Data
 *
 * Cookie management helper
 *
 * Copyright © 2015 Evgeny Budakov, Siberian. All rights reserved.
 */

namespace Siberian\QuickAdd\Helper;

class ControlCookie extends \Magento\Framework\App\Helper\AbstractHelper
{

    const COOKIE_PATH = '/';
    const COOKIE_SECTION_DATAIDS = 'section_data_ids';
    const COOKIE_CART_DATAID = 'cart';
    const SECTION_INCREMENT = 10;

    protected $_cookieManager;
    protected $_cookieMetadataFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;

        // Proceed the chain
        parent::__construct($context);
    }

    /**
     * triggerSectionRefresh -- refresh timestamp of data section cookie
     *
     * @param (string, required) $sectionId -- id of a section to be refreshed
     *
     * @return void
     */
    public function triggerSectionRefresh($sectionId): self
    {
        // Cookie update
        $sectionDataIds = json_decode(
            $this->_cookieManager->getCookie(self::COOKIE_SECTION_DATAIDS),
            true
        );
        // Try to increment the section
        if (isset($sectionDataIds[$sectionId])) {
            $sectionDataIds[$sectionId] = $sectionDataIds[$sectionId] + self::SECTION_INCREMENT;
        }
        // Write a cookie with incremented data
        $metadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath(self::COOKIE_PATH)
            ->setHttpOnly(false);
        $this->_cookieManager->setPublicCookie(
            self::COOKIE_SECTION_DATAIDS,
            json_encode($sectionDataIds),
            $metadata
        );

        return $this;
    }

}
