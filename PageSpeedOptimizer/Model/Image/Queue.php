<?php
namespace Octocub\PageSpeedOptimizer\Model\Image;

use Magento\Framework\App\ResourceConnection;

class Queue
{
    public function __construct(private ResourceConnection $rc) {}

    public function enqueue(string $absPath, string $device): void
    {
        if (!is_file($absPath)) return;

        $conn = $this->rc->getConnection();
        $table = $this->rc->getTableName('octocub_gps_image_queue');

        $exists = $conn->fetchOne(
            "SELECT entity_id FROM {$table} WHERE file_path = ? AND device = ? AND status IN ('pending','processing')",
            [$absPath, $device]
        );
        if ($exists) return;

        $conn->insert($table, [
            'file_path' => $absPath,
            'device' => $device,
            'status' => 'pending',
            'attempts' => 0
        ]);
    }
}
