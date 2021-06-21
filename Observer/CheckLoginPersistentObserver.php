<?php
/**
 * Magento 2.x.
 *
 * NOTICE OF LICENSE
 *
 * It is also available through the world-wide-web at this URL:
 * https://hieunc09.github.io/
 *
 * @package    HieuNC_ForceLogin
 * @author     HieuNC
 * @copyright  Copyright (c) 2019 HieuNC. All rights reserved.
 */

namespace HieuNC\ForceLogin\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class CheckLoginPersistentObserver
 * @package HieuNC\ForceLogin\Observer
 */
class CheckLoginPersistentObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     *
     * CheckLoginPersistentObserver constructor.
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\State $state
    )
    {
        $this->_messageManager = $messageManager;
        $this->_url = $url;
        $this->_responseFactory = $responseFactory;
        $this->_state = $state;
    }

    /**
     * Get area code : adminhtml or frontend
     *
     * @return mixed
     */
    public function getArea()
    {
        return $this->_state->getAreaCode();
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $config = $this->scopeConfig->getValue('justbetter/general/login_store_view', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        dd($config);


        $actionName = $observer->getEvent()->getRequest()->getActionName();
        $openActions = array(
            'create',
            'createpost',
            'login',
            'loginpost',
            'logoutsuccess',
            'forgotpassword',
            'forgotpasswordpost',
            'resetpassword',
            'resetpasswordpost',
            'confirm',
            'confirmation'
        );
        /* This feature is not available if you are logged in to the admin area */
        if ($this->getArea() === 'adminhtml') {
            return $this;
        } else {
            /* If you are logged in already we should be happy \m/ */
            /* Some pages should not be restricted */
            if ($actionName === 'account' || in_array($actionName, $openActions)) {
                return $this;
            } else {
                $this->_messageManager->addWarningMessage('Kindly login to your account before proceeding.');
                $redirectionUrl = $this->_url->getUrl('customer/account/login');
                $this->_responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                exit;
            }
        }
    }
}
