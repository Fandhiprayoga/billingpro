<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApproveRejectToActivityLogEnum extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE manager_activity_logs MODIFY COLUMN action_type ENUM('create_order','upload_payment','manage_license','view_profile','assign_customer','unassign_customer','create_trial','approve_order','reject_order') NOT NULL");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE manager_activity_logs MODIFY COLUMN action_type ENUM('create_order','upload_payment','manage_license','view_profile','assign_customer','unassign_customer','create_trial') NOT NULL");
    }
}
