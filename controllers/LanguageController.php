<?php

namespace aReports\Controllers;

use aReports\Core\Controller;

class LanguageController extends Controller
{
    public function switch(string $lang): void
    {
        $allowed = ['en', 'ru'];
        if (!in_array($lang, $allowed)) {
            $lang = 'en';
        }

        $this->session->set('locale', $lang);
        setcookie('areports_locale', $lang, time() + 86400 * 365, '/areports', '', false, true);

        $referer = $_SERVER['HTTP_REFERER'] ?? '/areports/dashboard';
        header('Location: ' . $referer);
        exit;
    }
}
