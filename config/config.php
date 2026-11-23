<?php
/**
 * config.php - project settings, loaded first by index.php
 */
const APP_NAME = 'BloodLink';
const VIEWS = __DIR__ . '/views';

// Database (adjust to your XAMPP / phpMyAdmin setup)
const DB_HOST = 'localhost';
const DB_NAME = 'bloodlink';
const DB_USER = 'root';
const DB_PASS = '';

const SESSION_LIFETIME = 1800;   // 30 minutes of inactivity
const MIN_AGE = 18;              // minimum age to register as a donor

// Authorization: role code => permissions. The role itself is stored in the database (table roles).
const PERMISSIONS = [
    'donneur'           => ['profile.edit'],
    'support'           => ['backoffice', 'users.view', 'users.edit', 'users.suspend'],
    'moderateur'        => ['backoffice'],
    'admin_gestion'     => ['backoffice', 'users.view', 'users.create', 'users.edit', 'users.suspend', 'users.delete'],
    'admin_supervision' => ['backoffice', 'users.view'],
    'super_admin'       => ['backoffice', 'users.view', 'users.create', 'users.edit', 'users.suspend', 'users.delete', 'roles.assign'],
];

// Which roles an actor may edit / suspend / delete.
const MANAGEABLE = [
    'support'       => ['donneur'],
    'admin_gestion' => ['donneur', 'support', 'moderateur'],
    'super_admin'   => ['donneur', 'support', 'moderateur', 'admin_gestion', 'admin_supervision', 'super_admin'],
];
