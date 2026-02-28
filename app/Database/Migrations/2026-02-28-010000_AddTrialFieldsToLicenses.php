<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTrialFieldsToLicenses extends Migration
{
    public function up()
    {
        // Make order_id nullable (trial licenses don't have orders)
        $this->forge->modifyColumn('licenses', [
            'order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'plan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        // Add trial-specific columns
        $this->forge->addColumn('licenses', [
            'is_trial' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'status',
                'comment'    => '1 = trial license, 0 = regular',
            ],
            'trial_duration_days' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'is_trial',
                'comment'    => 'Durasi trial dalam hari',
            ],
            'trial_notes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'after'   => 'trial_duration_days',
                'comment' => 'Catatan admin untuk trial',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'trial_notes',
                'comment'    => 'Admin yang membuat trial license',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('licenses', ['is_trial', 'trial_duration_days', 'trial_notes', 'created_by']);

        $this->forge->modifyColumn('licenses', [
            'order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'plan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}
