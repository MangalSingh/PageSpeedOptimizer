<?php
namespace Octocub\PageSpeedOptimizer\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Cron\Model\ScheduleFactory;

class RunNow extends Action
{
    const ADMIN_RESOURCE = 'Octocub_PageSpeedOptimizer::cron';

    public function __construct(
        Action\Context $context,
        private ScheduleFactory $scheduleFactory
    ) { parent::__construct($context); }

    public function execute()
    {
        $job = (string)$this->getRequest()->getParam('job_code');
        if ($job === '') {
            $this->messageManager->addErrorMessage(__('Missing job_code.'));
            return $this->_redirect('octocub_gps/cron/index');
        }

        $schedule = $this->scheduleFactory->create();
        $schedule->setJobCode($job);
        $schedule->setStatus(\Magento\Cron\Model\Schedule::STATUS_PENDING);
        $schedule->setCreatedAt(date('Y-m-d H:i:s'));
        $schedule->setScheduledAt(date('Y-m-d H:i:s'));
        $schedule->save();

        $this->messageManager->addSuccessMessage(__('Scheduled "%1" to run now.', $job));
        return $this->_redirect('octocub_gps/cron/index');
    }
}
