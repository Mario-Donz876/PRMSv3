<?php

// config/app.php
date_default_timezone_set('America/Jamaica');

// Application URL for emails and redirects
define('APP_URL', isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST']) 
    ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] 
    : 'http://localhost');

define('APP_ENV', 'prod'); // dev | prod
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'dgcjamaica@gmail.com');
define('MAIL_PASS', 'nyeoaounnzwhdlui');
define('MAIL_FROM', 'dgcjamaica@gmail.com');
define('MAIL_FROM_NAME', 'DGC Procurement System');


// Role constants
const ROLE_VIEWER = 1;
const ROLE_PROCUREMENT_OFFICER = 2;
const ROLE_FINANCE_OFFICER = 3;
const ROLE_HOD = 4;
const ROLE_ADMIN = 5;
const ROLE_SUPERADMIN = 6;
const ROLE_EVALUATION_COMMITTEE_MEMBER = 7;
const ROLE_PROCUREMENT_COMMITTEE = 8;
const ROLE_DEPUTY_GC = 9;
const ROLE_DIRECTOR_HRMA = 10;
const ROLE_DIRECTOR_PROCUREMENT = 11;

// Application roles (for reference)
const ROLE_REQUESTOR = 12;
$APP_ROLES = [
	ROLE_VIEWER => 'Viewer',
	ROLE_PROCUREMENT_OFFICER => 'Procurement Officer',
	ROLE_FINANCE_OFFICER => 'Finance Officer',
	ROLE_HOD => 'HOD',
	ROLE_ADMIN => 'Admin',
	ROLE_SUPERADMIN => 'SuperAdmin',
	ROLE_EVALUATION_COMMITTEE_MEMBER => 'Evaluation Committee Member',
	ROLE_PROCUREMENT_COMMITTEE => 'Procurement Committee',
	ROLE_DEPUTY_GC => 'Deputy Government Chemist',
	ROLE_DIRECTOR_HRMA => 'Director HRM&A',
	ROLE_DIRECTOR_PROCUREMENT => 'Director Procurement',
	ROLE_REQUESTOR => 'Requestor',
];

