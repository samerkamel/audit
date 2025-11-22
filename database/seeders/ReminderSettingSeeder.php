<?php

namespace Database\Seeders;

use App\Models\ReminderSetting;
use Illuminate\Database\Seeder;

class ReminderSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Internal Audit Reminders
            [
                'name' => 'Audit Start Reminder',
                'entity_type' => 'audit_plan',
                'event_type' => 'start',
                'intervals' => [72, 24, 1], // 3 days, 1 day, 1 hour before
                'send_email' => true,
                'send_database' => true,
                'notification_template_code' => 'audit_scheduled',
                'is_active' => true,
            ],

            // External Audit Reminders
            [
                'name' => 'External Audit Start Reminder',
                'entity_type' => 'external_audit',
                'event_type' => 'start',
                'intervals' => [168, 72, 24], // 7 days, 3 days, 1 day before
                'send_email' => true,
                'send_database' => true,
                'notification_template_code' => 'external_audit_scheduled',
                'is_active' => true,
            ],

            // CAR Reminders
            [
                'name' => 'CAR Due Date Reminder',
                'entity_type' => 'car',
                'event_type' => 'due',
                'intervals' => [72, 24, 4], // 3 days, 1 day, 4 hours before
                'send_email' => true,
                'send_database' => true,
                'notification_template_code' => 'car_due',
                'is_active' => true,
            ],

            // Certificate Reminders
            [
                'name' => 'Certificate Expiry Reminder',
                'entity_type' => 'certificate',
                'event_type' => 'expiry',
                'intervals' => [2160, 720, 168], // 90 days, 30 days, 7 days before
                'send_email' => true,
                'send_database' => true,
                'notification_template_code' => 'certificate_expiry',
                'is_active' => true,
            ],

            // Document Reminders
            [
                'name' => 'Document Review Reminder',
                'entity_type' => 'document',
                'event_type' => 'review',
                'intervals' => [168, 72, 24], // 7 days, 3 days, 1 day before
                'send_email' => true,
                'send_database' => true,
                'notification_template_code' => 'document_review',
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            ReminderSetting::updateOrCreate(
                [
                    'entity_type' => $setting['entity_type'],
                    'event_type' => $setting['event_type'],
                ],
                $setting
            );
        }

        $this->command->info('Reminder settings seeded successfully!');
    }
}
