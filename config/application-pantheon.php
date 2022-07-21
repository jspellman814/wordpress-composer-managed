<?php

use Roots\WPConfig\Config;
use function Env\env;

/**
 * Directory containing all of the site's files
 *
 * @var string
 */
$root_dir = dirname(__DIR__);

/**
 * Document Root
 *
 * @var string
 */
$webroot_dir = $root_dir . '/web';

/**
 * Use Dotenv to set required environment variables and load .env.pantheon file in root
 * .env.local will override .env.pantheon if it exists
 */
$env_files = file_exists($root_dir . '/.env.local')
    ? ['.env', '.env.pantheon', '.env.local']
    : ['.env.pantheon'];

$dotenv = Dotenv\Dotenv::createUnsafeImmutable($root_dir, $env_files, false);
if (file_exists($root_dir . '/.env.pantheon')) {
    $dotenv->load();
    if (!env('DATABASE_URL')) {
        $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD']);
    }
}

/** A couple extra tweaks to help things run well on Pantheon. **/
if (isset($_SERVER['HTTP_HOST'])) {
    // HTTP is still the default scheme for now.
    $scheme = 'http';
    // If we have detected that the end use is HTTPS, make sure we pass that
    // through here, so <img> tags and the like don't generate mixed-mode
    // content warnings.
    if (isset($_SERVER['HTTP_USER_AGENT_HTTPS']) && $_SERVER['HTTP_USER_AGENT_HTTPS'] == 'ON') {
        $scheme = 'https';
        $_SERVER['HTTPS'] = 'on';
    }
    Config::define('WP_HOME', $scheme . '://' . $_SERVER['HTTP_HOST']);
    Config::define('WP_SITEURL', $scheme . '://' . $_SERVER['HTTP_HOST']);
}

/**
 * Defaults you may override
 *
 * To override, define your constant in your wp-config.php before wp-config-pantheon.php is required.
 */

/** Disable wp-cron.php from running on every page load and rely on Pantheon to run cron via wp-cli */
$network = isset($_ENV["FRAMEWORK"]) && $_ENV["FRAMEWORK"] === "wordpress_network";
if (!env( 'DISABLE_WP_CRON') && $network === false) {
	Config::define('DISABLE_WP_CRON', true);
}