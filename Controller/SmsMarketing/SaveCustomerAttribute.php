<?php

namespace Yotpo\SmsBump\Controller\SmsMarketing;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Yotpo\Core\Model\Sync\Orders\Processor as OrdersProcessor;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class SaveCustomerAttribute - Save customer attribute 'yotpo_accepts_sms_marketing'
 */
class SaveCustomerAttribute implements ActionInterface
{
    /**
     * Json Factory
     *
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var OrdersProcessor
     */
    protected $ordersProcessor;

    /**
     * SaveCustomerAttribute constructor.
     * @param JsonFactory $jsonResultFactory
     * @param RequestInterface $request
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CheckoutSession $checkoutSession
     * @param OrdersProcessor $ordersProcessor
     */
    public function __construct(
        JsonFactory $jsonResultFactory,
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CheckoutSession $checkoutSession,
        OrdersProcessor $ordersProcessor
    ) {
        $this->jsonResultFactory = $jsonResultFactory;
        $this->request = $request;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->ordersProcessor = $ordersProcessor;
    }

    /**
     * Updates customer attribute
     *
     * @return ResponseInterface|Json|ResultInterface
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InputMismatchException
     */
    public function execute()
    {
        $acceptsSmsMarketing = $this->request->getParam('acceptsSmsMarketing');
        $customerId = $this->checkoutSession->getQuote()->getCustomerId();

        if ($customerId) {
            $customer = $this->customerRepositoryInterface->getById($customerId);
            $customer->setCustomAttribute(
                \Yotpo\SmsBump\Model\Config::YOTPO_CUSTOM_ATTRIBUTE_SMS_MARKETING,
                $acceptsSmsMarketing
            );
            $this->customerRepositoryInterface->save($customer);
        } else {
            $this->checkoutSession->setYotpoSmsMarketing($acceptsSmsMarketing);
        }

        return $this->jsonResultFactory->create();
    }
}
