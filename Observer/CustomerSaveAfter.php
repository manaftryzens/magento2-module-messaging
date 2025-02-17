<?php

namespace Yotpo\SmsBump\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Safe\Exceptions\DatetimeException;
use Yotpo\SmsBump\Model\Sync\Customers\Processor as CustomersProcessor;
use Yotpo\SmsBump\Model\Config;
use Magento\Framework\App\RequestInterface;

/**
 * Class CustomerSaveAfter
 * Observer when customer data is updated
 */
class CustomerSaveAfter implements ObserverInterface
{
    /**
     * @var CustomersProcessor
     */
    protected $customersProcessor;

    /**
     * @var Config
     */
    protected $yotpoSmsConfig;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * CustomerSaveAfter constructor.
     * @param CustomersProcessor $customersProcessor
     * @param Config $yotpoSmsConfig
     * @param RequestInterface $request
     */
    public function __construct(
        CustomersProcessor $customersProcessor,
        Config $yotpoSmsConfig,
        RequestInterface $request
    ) {
        $this->customersProcessor = $customersProcessor;
        $this->yotpoSmsConfig = $yotpoSmsConfig;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws DatetimeException
     */
    public function execute(Observer $observer)
    {
        if ($this->yotpoSmsConfig->isCustomerSyncActive() &&
                !$this->request->getParam('custSync')) {
            /** @phpstan-ignore-next-line */
            $this->request->setParam('custSync', true);//to avoid multiple calls for a single save.
            /** @var Customer $customer */
            $customer = $observer->getEvent()->getCustomer();
            $this->customersProcessor->processCustomer($customer);
        }
    }
}
