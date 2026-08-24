<?php
require_once __DIR__ . '/config.php';
send_json(['status' => 'ok', 'time' => date('c')]);
