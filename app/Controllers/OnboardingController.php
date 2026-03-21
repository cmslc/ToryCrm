<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class OnboardingController extends Controller
{
    public function welcome()
    {
        // Check if onboarding already completed
        $user = $this->user();
        if ($user) {
            try {
                $tenant = Database::fetch(
                    "SELECT settings FROM tenants WHERE id = ?",
                    [$user['tenant_id'] ?? 0]
                );
                if ($tenant && !empty($tenant['settings'])) {
                    $settings = json_decode($tenant['settings'], true);
                    if (!empty($settings['onboarding_completed'])) {
                        return $this->redirect('dashboard');
                    }
                }
            } catch (\Exception $e) {
                // Table may not exist yet, continue to onboarding
            }
        }

        // Check session flag as fallback
        if (!empty($_SESSION['onboarding_completed'])) {
            return $this->redirect('dashboard');
        }

        return $this->view('onboarding.welcome');
    }

    public function complete()
    {
        if (!$this->isPost()) {
            return $this->redirect('onboarding/welcome');
        }

        $companyName = trim($this->input('company_name', ''));
        $companyPhone = trim($this->input('company_phone', ''));
        $companyEmail = trim($this->input('company_email', ''));
        $industry = trim($this->input('industry', ''));

        $user = $this->user();
        $tenantId = $user['tenant_id'] ?? null;

        if ($tenantId) {
            try {
                // Update tenant company info
                $tenant = Database::fetch("SELECT settings FROM tenants WHERE id = ?", [$tenantId]);
                $settings = [];
                if ($tenant && !empty($tenant['settings'])) {
                    $settings = json_decode($tenant['settings'], true) ?: [];
                }

                $settings['company_name'] = $companyName;
                $settings['company_phone'] = $companyPhone;
                $settings['company_email'] = $companyEmail;
                $settings['industry'] = $industry;
                $settings['onboarding_completed'] = true;
                $settings['onboarding_completed_at'] = date('Y-m-d H:i:s');

                Database::update('tenants', [
                    'name' => $companyName ?: ($tenant['name'] ?? 'My Company'),
                    'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                ], 'id = ?', [$tenantId]);
            } catch (\Exception $e) {
                // If tenant table doesn't exist, use session fallback
            }
        }

        // Mark onboarding complete in session as fallback
        $_SESSION['onboarding_completed'] = true;

        $this->setFlash('success', 'Thiet lap hoan tat! Chao mung ban den voi ToryCRM.');
        return $this->redirect('dashboard');
    }
}
