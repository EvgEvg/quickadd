<?php
/**
 * @class Index
 *
 * Allow for link based add to cart and redirect to checkout
 *
 * Copyright © 2015 Evgeny Budakov, Siberian. All rights reserved.
 */

namespace Siberian\QuickAdd\Controller\Index;

use Magento\Checkout\Model\Cart as CustomerCart;
use \Siberian\QuickAdd\Helper\ControlCookie as CookieHelper;

class Index extends \Magento\Framework\App\Action\Action
{

    // Class properties
    protected $_prodFactory;
    protected $_cart;
    protected $_CookieHelper;

    /**
     * Construct - passing in dependencies via DI
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        CookieHelper $CookieHelper,
        CustomerCart $cart
    ) {
        // Set variables
        $this->_prodFactory = $productFactory;
        $this->_cart = $cart;
        $this->_CookieHelper = $CookieHelper;
        // Proceed the chain
        parent::__construct($context);
    }


    /**
     * Åction execution
     */
    public function execute()
    {
        // Init vars
        $urlKey = $this->getRequest()->getParam('product', false);
        $qty = (int) $this->getRequest()->getParam('qty', 1);
        $resultRedirect = $this->resultRedirectFactory->create();
        // Get product by its URL code
        // Try to see if there is a match.
        // If product is found proceed to add it to card and redirect to checkout
        $searchedProduct = $this->_prodFactory->create()
            ->getCollection()
            ->addAttributeToFilter('url_key', $urlKey)
            ->getFirstItem();
        if (!$searchedProduct->getId()) {
            return $resultRedirect->setPath('/');
        }
        // Add product to cart
        $this->_cart->addProduct($searchedProduct->getId(), $qty);
        $this->_cart->save();
        $this->_eventManager->dispatch(
            'checkout_cart_add_product_complete',
            [
                'product' => $searchedProduct,
                'request' => $this->getRequest(),
                'response' => $this->getResponse()
            ]
        );
        // Refresh cart section
        $this->_CookieHelper->triggerSectionRefresh(CookieHelper::COOKIE_CART_DATAID);
        // Redirect to checkout
        return $resultRedirect->setPath('checkout');
    }
}
