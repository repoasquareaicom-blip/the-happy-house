<?php
function isMobilePhone() {
    $ua = $_SERVER['HTTP_USER_AGENT'];

        // 1. If it contains "iPad", "Tablet", or "PlayBook", it is NOT a small mobile
        if (preg_match('/iPad|Tablet|PlayBook/i', $ua)) {
            return false;
        }

        // 2. Check for common mobile signatures
        // Note: We check for 'Mobi' which is the standard flag for small screens
        if (preg_match('/Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk-Accelerated/i', $ua)) {
            return true;
        }

        return false;
}
