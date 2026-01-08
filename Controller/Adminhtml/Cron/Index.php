<?php
namespace Octocub\PageSpeedOptimizer\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Octocub_PageSpeedOptimizer::cron';

    public function __construct(Action\Context $context, private PageFactory $pageFactory)
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Octocub_PageSpeedOptimizer::cron');
        $page->getConfig()->getTitle()->prepend(__('Cron Tasks List'));
        return $page;
    }
}
