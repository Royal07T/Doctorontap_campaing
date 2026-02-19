<?php

namespace Database\Seeders;

use App\Models\CommunicationTemplate;
use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user as creator, or create a system admin
        $admin = AdminUser::first();
        if (!$admin) {
            $admin = AdminUser::create([
                'name' => 'System Admin',
                'email' => 'admin@doctorontap.com',
                'password' => bcrypt('password'),
            ]);
        }

        $templates = [
            // Consultation-Related Emails
            [
                'name' => 'consultation_confirmation',
                'channel' => 'email',
                'subject' => 'Consultation Request Confirmation - {{reference}}',
                'body' => $this->getConsultationConfirmationTemplate(),
                'variables' => ['first_name', 'last_name', 'reference', 'consultation_type', 'scheduled_date', 'scheduled_time', 'doctor_name'],
                'active' => true,
            ],
            [
                'name' => 'consultation_admin_alert',
                'channel' => 'email',
                'subject' => 'New Consultation Request - {{reference}}',
                'body' => $this->getConsultationAdminAlertTemplate(),
                'variables' => ['reference', 'patient_name', 'consultation_type', 'scheduled_date', 'scheduled_time', 'doctor_name'],
                'active' => true,
            ],
            [
                'name' => 'consultation_doctor_notification',
                'channel' => 'email',
                'subject' => 'New Consultation Assigned - {{reference}}',
                'body' => $this->getConsultationDoctorNotificationTemplate(),
                'variables' => ['doctor_name', 'reference', 'patient_name', 'consultation_type', 'scheduled_date', 'scheduled_time'],
                'active' => true,
            ],
            [
                'name' => 'consultation_reminder',
                'channel' => 'email',
                'subject' => 'Consultation Reminder - {{reference}}',
                'body' => $this->getConsultationReminderTemplate(),
                'variables' => ['first_name', 'last_name', 'reference', 'scheduled_date', 'scheduled_time', 'doctor_name'],
                'active' => true,
            ],
            [
                'name' => 'consultation_status_change',
                'channel' => 'email',
                'subject' => 'Consultation Status Changed - {{reference}}',
                'body' => $this->getConsultationStatusChangeTemplate(),
                'variables' => ['reference', 'old_status', 'new_status', 'patient_name', 'changed_by'],
                'active' => true,
            ],

            // Payment-Related Emails
            [
                'name' => 'payment_request',
                'channel' => 'email',
                'subject' => 'Payment Request - Consultation {{reference}}',
                'body' => $this->getPaymentRequestTemplate(),
                'variables' => ['first_name', 'last_name', 'reference', 'amount', 'payment_link', 'due_date'],
                'active' => true,
            ],
            [
                'name' => 'payment_received_admin',
                'channel' => 'email',
                'subject' => 'Payment Received - {{reference}}',
                'body' => $this->getPaymentReceivedAdminTemplate(),
                'variables' => ['reference', 'amount', 'patient_name', 'payment_method', 'transaction_id'],
                'active' => true,
            ],
            [
                'name' => 'payment_received_doctor',
                'channel' => 'email',
                'subject' => 'Payment Received - Your Consultation {{reference}}',
                'body' => $this->getPaymentReceivedDoctorTemplate(),
                'variables' => ['doctor_name', 'reference', 'amount', 'patient_name', 'doctor_portion'],
                'active' => true,
            ],
            [
                'name' => 'payment_failed',
                'channel' => 'email',
                'subject' => 'Payment Failed - Consultation {{reference}}',
                'body' => $this->getPaymentFailedTemplate(),
                'variables' => ['first_name', 'last_name', 'reference', 'amount', 'failure_reason', 'retry_link'],
                'active' => true,
            ],
            [
                'name' => 'fee_adjustment',
                'channel' => 'email',
                'subject' => 'Consultation Fee Adjusted - {{reference}}',
                'body' => $this->getFeeAdjustmentTemplate(),
                'variables' => ['first_name', 'last_name', 'reference', 'old_amount', 'new_amount', 'reason'],
                'active' => true,
            ],
            [
                'name' => 'fee_adjustment_admin',
                'channel' => 'email',
                'subject' => 'Fee Adjustment - {{reference}}',
                'body' => $this->getFeeAdjustmentAdminTemplate(),
                'variables' => ['reference', 'old_amount', 'new_amount', 'patient_name', 'adjusted_by', 'reason'],
                'active' => true,
            ],

            // Treatment Plan Emails
            [
                'name' => 'treatment_plan_notification',
                'channel' => 'email',
                'subject' => 'Your Treatment Plan is Ready - {{reference}}',
                'body' => $this->getTreatmentPlanNotificationTemplate(),
                'variables' => ['first_name', 'last_name', 'reference', 'doctor_name', 'treatment_plan_link'],
                'active' => true,
            ],
            [
                'name' => 'treatment_plan_ready',
                'channel' => 'email',
                'subject' => 'Treatment Plan Finalized - {{reference}}',
                'body' => $this->getTreatmentPlanReadyTemplate(),
                'variables' => ['first_name', 'last_name', 'reference', 'doctor_name', 'view_link'],
                'active' => true,
            ],

            // Review & Feedback
            [
                'name' => 'review_request',
                'channel' => 'email',
                'subject' => 'Share Your Experience - Consultation {{reference}}',
                'body' => $this->getReviewRequestTemplate(),
                'variables' => ['first_name', 'last_name', 'reference', 'doctor_name', 'review_link'],
                'active' => true,
            ],

            // Account Creation Emails
            [
                'name' => 'canvasser_account_created',
                'channel' => 'email',
                'subject' => 'Welcome to DoctorOnTap - Your Account is Ready',
                'body' => $this->getCanvasserAccountCreatedTemplate(),
                'variables' => ['name', 'email', 'password', 'admin_name', 'login_link'],
                'active' => true,
            ],
            [
                'name' => 'nurse_account_created',
                'channel' => 'email',
                'subject' => 'Welcome to DoctorOnTap - Your Account is Ready',
                'body' => $this->getNurseAccountCreatedTemplate(),
                'variables' => ['name', 'email', 'password', 'admin_name', 'login_link'],
                'active' => true,
            ],
            [
                'name' => 'customer_care_account_created',
                'channel' => 'email',
                'subject' => 'Welcome to DoctorOnTap - Your Account is Ready',
                'body' => $this->getCustomerCareAccountCreatedTemplate(),
                'variables' => ['name', 'email', 'password', 'admin_name', 'login_link'],
                'active' => true,
            ],
            [
                'name' => 'care_giver_account_created',
                'channel' => 'email',
                'subject' => 'Welcome to DoctorOnTap - Your Account is Ready',
                'body' => $this->getCareGiverAccountCreatedTemplate(),
                'variables' => ['name', 'email', 'password', 'admin_name', 'login_link'],
                'active' => true,
            ],

            // Doctor-Related Emails
            [
                'name' => 'doctor_reassignment',
                'channel' => 'email',
                'subject' => 'Doctor Reassignment - Consultation {{reference}}',
                'body' => $this->getDoctorReassignmentTemplate(),
                'variables' => ['name', 'reference', 'old_doctor_name', 'new_doctor_name', 'reason'],
                'active' => true,
            ],
            [
                'name' => 'referral_notification',
                'channel' => 'email',
                'subject' => 'Consultation Referral - {{reference}}',
                'body' => $this->getReferralNotificationTemplate(),
                'variables' => ['name', 'reference', 'referring_doctor', 'referred_to_doctor', 'reason'],
                'active' => true,
            ],
            [
                'name' => 'documents_forwarded_to_doctor',
                'channel' => 'email',
                'subject' => 'Patient Documents Forwarded - {{reference}}',
                'body' => $this->getDocumentsForwardedToDoctorTemplate(),
                'variables' => ['doctor_name', 'reference', 'patient_name', 'document_count', 'view_link'],
                'active' => true,
            ],

            // System & Security
            [
                'name' => 'security_alert',
                'channel' => 'email',
                'subject' => 'Security Alert - {{event_type}}',
                'body' => $this->getSecurityAlertTemplate(),
                'variables' => ['event_type', 'severity', 'timestamp', 'details', 'ip_address', 'user_agent'],
                'active' => true,
            ],
            [
                'name' => 'delay_query_notification',
                'channel' => 'email',
                'subject' => 'Delay Query - Consultation {{reference}}',
                'body' => $this->getDelayQueryNotificationTemplate(),
                'variables' => ['doctor_name', 'reference', 'patient_name', 'query_message', 'response_link'],
                'active' => true,
            ],

            // Campaign & Marketing
            [
                'name' => 'campaign_notification',
                'channel' => 'email',
                'subject' => '{{campaign_title}} - DoctorOnTap',
                'body' => $this->getCampaignNotificationTemplate(),
                'variables' => ['doctor_name', 'campaign_title', 'campaign_details', 'action_link'],
                'active' => true,
            ],

            // Canvasser-Specific
            [
                'name' => 'canvasser_consultation_confirmation',
                'channel' => 'email',
                'subject' => 'Consultation Created Successfully - {{reference}}',
                'body' => $this->getCanvasserConsultationConfirmationTemplate(),
                'variables' => ['canvasser_name', 'reference', 'patient_name', 'consultation_type', 'scheduled_date'],
                'active' => true,
            ],

            // Medical Reports
            [
                'name' => 'vital_signs_report',
                'channel' => 'email',
                'subject' => 'Vital Signs Report - {{report_date}}',
                'body' => $this->getVitalSignsReportTemplate(),
                'variables' => ['first_name', 'last_name', 'report_date', 'vital_signs_summary', 'view_link'],
                'active' => true,
            ],

            // Custom Communication
            [
                'name' => 'custom_communication',
                'channel' => 'email',
                'subject' => 'Message from DoctorOnTap',
                'body' => $this->getCustomCommunicationTemplate(),
                'variables' => ['message', 'subject'],
                'active' => true,
            ],
        ];

        foreach ($templates as $template) {
            CommunicationTemplate::updateOrCreate(
                ['name' => $template['name'], 'channel' => $template['channel']],
                array_merge($template, ['created_by' => $admin->id])
            );
        }

        $this->command->info('Email templates seeded successfully!');
    }

    // Template HTML methods
    private function getConsultationConfirmationTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultation Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Consultation Request Confirmed</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <p>Your consultation request has been confirmed!</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reference:</strong> {{reference}}</p>
            <p><strong>Type:</strong> {{consultation_type}}</p>
            <p><strong>Doctor:</strong> {{doctor_name}}</p>
            <p><strong>Date:</strong> {{scheduled_date}}</p>
            <p><strong>Time:</strong> {{scheduled_time}}</p>
        </div>
        <p>We look forward to serving you.</p>
        <p>Best regards,<br>DoctorOnTap Team</p>
    </div>
</body>
</html>';
    }

    private function getConsultationAdminAlertTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Consultation Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">New Consultation Request</h2>
        <p>A new consultation has been created:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reference:</strong> {{reference}}</p>
            <p><strong>Patient:</strong> {{patient_name}}</p>
            <p><strong>Type:</strong> {{consultation_type}}</p>
            <p><strong>Doctor:</strong> {{doctor_name}}</p>
            <p><strong>Scheduled:</strong> {{scheduled_date}} at {{scheduled_time}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getConsultationDoctorNotificationTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Consultation Assigned</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">New Consultation Assigned</h2>
        <p>Dear Dr. {{doctor_name}},</p>
        <p>A new consultation has been assigned to you:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reference:</strong> {{reference}}</p>
            <p><strong>Patient:</strong> {{patient_name}}</p>
            <p><strong>Type:</strong> {{consultation_type}}</p>
            <p><strong>Scheduled:</strong> {{scheduled_date}} at {{scheduled_time}}</p>
        </div>
        <p>Please review and prepare accordingly.</p>
    </div>
</body>
</html>';
    }

    private function getConsultationReminderTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultation Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Consultation Reminder</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <p>This is a reminder about your upcoming consultation:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reference:</strong> {{reference}}</p>
            <p><strong>Doctor:</strong> {{doctor_name}}</p>
            <p><strong>Date:</strong> {{scheduled_date}}</p>
            <p><strong>Time:</strong> {{scheduled_time}}</p>
        </div>
        <p>We look forward to seeing you!</p>
    </div>
</body>
</html>';
    }

    private function getConsultationStatusChangeTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultation Status Changed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Consultation Status Updated</h2>
        <p>The status of consultation {{reference}} has been changed:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Patient:</strong> {{patient_name}}</p>
            <p><strong>Previous Status:</strong> {{old_status}}</p>
            <p><strong>New Status:</strong> {{new_status}}</p>
            <p><strong>Changed By:</strong> {{changed_by}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getPaymentRequestTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Request</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Payment Request</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <p>Payment is required for your consultation:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reference:</strong> {{reference}}</p>
            <p><strong>Amount:</strong> ₦{{amount}}</p>
            <p><strong>Due Date:</strong> {{due_date}}</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{payment_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Pay Now</a>
        </p>
    </div>
</body>
</html>';
    }

    private function getPaymentReceivedAdminTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Received</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #10b981;">Payment Received</h2>
        <p>Payment has been received for consultation:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reference:</strong> {{reference}}</p>
            <p><strong>Patient:</strong> {{patient_name}}</p>
            <p><strong>Amount:</strong> ₦{{amount}}</p>
            <p><strong>Method:</strong> {{payment_method}}</p>
            <p><strong>Transaction ID:</strong> {{transaction_id}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getPaymentReceivedDoctorTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Received</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #10b981;">Payment Received</h2>
        <p>Dear Dr. {{doctor_name}},</p>
        <p>Payment has been received for consultation {{reference}}:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Patient:</strong> {{patient_name}}</p>
            <p><strong>Total Amount:</strong> ₦{{amount}}</p>
            <p><strong>Your Portion:</strong> ₦{{doctor_portion}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getPaymentFailedTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Failed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #ef4444;">Payment Failed</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <p>Unfortunately, your payment for consultation {{reference}} could not be processed.</p>
        <div style="background: #fee2e2; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Amount:</strong> ₦{{amount}}</p>
            <p><strong>Reason:</strong> {{failure_reason}}</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{retry_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Retry Payment</a>
        </p>
    </div>
</body>
</html>';
    }

    private function getFeeAdjustmentTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Adjustment</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Consultation Fee Adjusted</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <p>The fee for consultation {{reference}} has been adjusted:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Previous Amount:</strong> ₦{{old_amount}}</p>
            <p><strong>New Amount:</strong> ₦{{new_amount}}</p>
            <p><strong>Reason:</strong> {{reason}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getFeeAdjustmentAdminTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Adjustment</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Fee Adjustment Notification</h2>
        <p>A consultation fee has been adjusted:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reference:</strong> {{reference}}</p>
            <p><strong>Patient:</strong> {{patient_name}}</p>
            <p><strong>Previous Amount:</strong> ₦{{old_amount}}</p>
            <p><strong>New Amount:</strong> ₦{{new_amount}}</p>
            <p><strong>Adjusted By:</strong> {{adjusted_by}}</p>
            <p><strong>Reason:</strong> {{reason}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getTreatmentPlanNotificationTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Treatment Plan Ready</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Your Treatment Plan is Ready</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <p>Your treatment plan for consultation {{reference}} has been prepared by Dr. {{doctor_name}}.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{treatment_plan_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View Treatment Plan</a>
        </p>
    </div>
</body>
</html>';
    }

    private function getTreatmentPlanReadyTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Treatment Plan Finalized</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #10b981;">Treatment Plan Finalized</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <p>Your treatment plan for consultation {{reference}} has been finalized by Dr. {{doctor_name}}.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{view_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View Treatment Plan</a>
        </p>
    </div>
</body>
</html>';
    }

    private function getReviewRequestTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Review Request</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Share Your Experience</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <p>We hope you had a great experience with Dr. {{doctor_name}} for consultation {{reference}}.</p>
        <p>Your feedback helps us improve our services.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{review_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Leave a Review</a>
        </p>
    </div>
</body>
</html>';
    }

    private function getCanvasserAccountCreatedTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Welcome to DoctorOnTap</h2>
        <p>Dear {{name}},</p>
        <p>Your Canvasser account has been created by {{admin_name}}.</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Email:</strong> {{email}}</p>
            <p><strong>Password:</strong> {{password}}</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{login_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Login Now</a>
        </p>
        <p><strong>Please change your password after first login.</strong></p>
    </div>
</body>
</html>';
    }

    private function getNurseAccountCreatedTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Welcome to DoctorOnTap</h2>
        <p>Dear {{name}},</p>
        <p>Your Nurse account has been created by {{admin_name}}.</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Email:</strong> {{email}}</p>
            <p><strong>Password:</strong> {{password}}</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{login_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Login Now</a>
        </p>
        <p><strong>Please change your password after first login.</strong></p>
    </div>
</body>
</html>';
    }

    private function getCustomerCareAccountCreatedTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Welcome to DoctorOnTap</h2>
        <p>Dear {{name}},</p>
        <p>Your Customer Care account has been created by {{admin_name}}.</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Email:</strong> {{email}}</p>
            <p><strong>Password:</strong> {{password}}</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{login_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Login Now</a>
        </p>
        <p><strong>Please change your password after first login.</strong></p>
    </div>
</body>
</html>';
    }

    private function getCareGiverAccountCreatedTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Welcome to DoctorOnTap</h2>
        <p>Dear {{name}},</p>
        <p>Your Care Giver account has been created by {{admin_name}}.</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Email:</strong> {{email}}</p>
            <p><strong>Password:</strong> {{password}}</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{login_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Login Now</a>
        </p>
        <p><strong>Please change your password after first login.</strong></p>
    </div>
</body>
</html>';
    }

    private function getDoctorReassignmentTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Doctor Reassignment</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Doctor Reassignment</h2>
        <p>Dear {{name}},</p>
        <p>The doctor for consultation {{reference}} has been reassigned:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Previous Doctor:</strong> {{old_doctor_name}}</p>
            <p><strong>New Doctor:</strong> {{new_doctor_name}}</p>
            <p><strong>Reason:</strong> {{reason}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getReferralNotificationTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultation Referral</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Consultation Referral</h2>
        <p>Dear {{name}},</p>
        <p>Consultation {{reference}} has been referred:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Referring Doctor:</strong> {{referring_doctor}}</p>
            <p><strong>Referred To:</strong> {{referred_to_doctor}}</p>
            <p><strong>Reason:</strong> {{reason}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getDocumentsForwardedToDoctorTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documents Forwarded</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Patient Documents Forwarded</h2>
        <p>Dear Dr. {{doctor_name}},</p>
        <p>Patient documents for consultation {{reference}} have been forwarded to you:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Patient:</strong> {{patient_name}}</p>
            <p><strong>Documents:</strong> {{document_count}} file(s)</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{view_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View Documents</a>
        </p>
    </div>
</body>
</html>';
    }

    private function getSecurityAlertTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Security Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #ef4444;">Security Alert</h2>
        <p>A security event has been detected:</p>
        <div style="background: #fee2e2; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Event Type:</strong> {{event_type}}</p>
            <p><strong>Severity:</strong> {{severity}}</p>
            <p><strong>Timestamp:</strong> {{timestamp}}</p>
            <p><strong>IP Address:</strong> {{ip_address}}</p>
            <p><strong>Details:</strong> {{details}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getDelayQueryNotificationTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delay Query</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Delay Query</h2>
        <p>Dear Dr. {{doctor_name}},</p>
        <p>A delay query has been sent regarding consultation {{reference}}:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Patient:</strong> {{patient_name}}</p>
            <p><strong>Query:</strong> {{query_message}}</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{response_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Respond</a>
        </p>
    </div>
</body>
</html>';
    }

    private function getCampaignNotificationTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Campaign Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">{{campaign_title}}</h2>
        <p>Dear Dr. {{doctor_name}},</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            {{campaign_details}}
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{action_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Learn More</a>
        </p>
    </div>
</body>
</html>';
    }

    private function getCanvasserConsultationConfirmationTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultation Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Consultation Created Successfully</h2>
        <p>Dear {{canvasser_name}},</p>
        <p>You have successfully created a consultation:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reference:</strong> {{reference}}</p>
            <p><strong>Patient:</strong> {{patient_name}}</p>
            <p><strong>Type:</strong> {{consultation_type}}</p>
            <p><strong>Scheduled:</strong> {{scheduled_date}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getVitalSignsReportTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vital Signs Report</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">Vital Signs Report</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <p>Your vital signs report for {{report_date}} is ready:</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            {{vital_signs_summary}}
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{view_link}}" style="background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View Full Report</a>
        </p>
    </div>
</body>
</html>';
    }

    private function getCustomCommunicationTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{subject}}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">{{subject}}</h2>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            {{message}}
        </div>
        <p>Best regards,<br>DoctorOnTap Team</p>
    </div>
</body>
</html>';
    }
}
