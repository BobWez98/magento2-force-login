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
 * @copyright  Copyright (c) 2019
 */

namespace HieuNC\ForceLogin\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckForce implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_adminSession;

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
     * CheckForce constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Auth\Session $adminSession
    )
    {
        $this->_messageManager = $messageManager;
        $this->_url = $url;
        $this->_responseFactory = $responseFactory;
        $this->_customerSession = $customerSession;
        $this->_adminSession = $adminSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        /* This feature is not available if you are logged in to the back end */
        if ($this->_adminSession->isLoggedIn() || $actionName == 'adminhtml_auth_login') {
            return $this;
        }
        /* If you are logged in already we should be happy \m/ */
        if (!$this->_customerSession->isLoggedIn()) {
            /* Some pages should not be restricted */
            if ($actionName == 'customer_account_create'
                || $actionName == 'customer_account_login'
                || $actionName == 'customer_account_createpost'
                || $actionName == 'customer_account_loginPost'
                || $actionName == 'customer_section_load'
            ) {
                return $this;
            } else {
                $this->_messageManager->addWarningMessage('Kindly login to your account before proceeding.');
                $redirectionUrl = $this->_url->getUrl('customer/account/login');
                $this->_responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                exit;
            }
        }
        return $this;
    }
}