<?php
namespace Octocub\PageSpeedOptimizer\Cron;

use Magento\Framework\App\ResourceConnection;
use Octocub\PageSpeedOptimizer\Service\Image\Optimizer;

class ProcessImageQueue
{
    public function __construct(
        private ResourceConnection $rc,
        private Optimizer $optimizer
    ) {}

    public function execute(): void
    {
        $conn = $this->rc->getConnection();
        $table = $this->rc->getTableName('octocub_gps_image_queue');

        $rows = $conn->fetchAll(
            "SELECT * FROM {$table} WHERE status='pending' ORDER BY entity_id ASC LIMIT 25"
        );

        foreach ($rows as $row) {
            $id = (int)$row['entity_id'];
            try {
                $conn->update($table, ['status' => 'processing'], ['entity_id = ?' => $id]);

                $this->optimizer->optimize((string)$row['file_path'], (string)$row['device']);

                $conn->update($table, ['status' => 'done', 'last_error' => null], ['entity_id = ?' => $id]);
            } catch (\Throwable $e) {
                $conn->update($table, [
                    'status' => 'error',
                    'attempts' => ((int)$row['attempts']) + 1,
                    'last_error' => substr($e->getMessage(), 0, 2000),
                ], ['entity_id = ?' => $id]);
            }
        }
    }
}
