<?php
require_once '/var/www/private/db.php';
session_start();
\['user_id'] = 1;
session_write_close();
echo session_id();
