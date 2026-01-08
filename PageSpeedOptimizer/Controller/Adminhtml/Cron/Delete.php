<?php
namespace Octocub\PageSpeedOptimizer\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Octocub_PageSpeedOptimizer::cron';

    public function __construct(Action\Context $context, private ResourceConnection $rc)
    { parent::__construct($context); }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('schedule_id');
        if ($id <= 0) {
            $this->messageManager->addErrorMessage(__('Missing schedule_id.'));
            return $this->_redirect('octocub_gps/cron/index');
        }

        $conn = $this->rc->getConnection();
        $conn->delete($this->rc->getTableName('cron_schedule'), ['schedule_id = ?' => $id]);

        $this->messageManager->addSuccessMessage(__('Deleted schedule row #%1.', $id));
        return $this->_redirect('octocub_gps/cron/index');
    }
}
