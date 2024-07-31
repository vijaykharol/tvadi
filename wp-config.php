<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'hipldemo1_tvadimarket' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'D381IA|bI)hHPr7]le9OU)892j6l6k59Hm;_DAH-m89Sc)FU19JfTVqlflRF:m#i');
define('SECURE_AUTH_KEY', '/2B+Lr30k2+NUrSH&3/rE;y)&Lr051H!A3640)_A|p*!19090*gB2*993;PqC:@9');
define('LOGGED_IN_KEY', '1RQ[c4~%90+Nu%9xNP35XqaUm+uJ;f[cl4TSY+IC5]:1F*U%LIUI22b9cs~90LhG');
define('NONCE_KEY', 'oCO1VW@9]-:5uZe~tji4G~k_7&g53)59Yrw3C[D063*ukZobgflQe7t!i9%dxSKL');
define('AUTH_SALT', 'p72/O@*&;%DW/ALnp&1S734O&k6|U9pgt2(TZ@/-36V2O09%1w5S@8~9v/!N[o2C');
define('SECURE_AUTH_SALT', '95!PBF7!9kwXCy]lr@2AEk[!8~suwn4D0~2SIKo4@35@y8!7*xrwt&KF29);Kc]~');
define('LOGGED_IN_SALT', '1ixk7Q]8k&f|0Q;&3qV4i@_)_E0n972!Cak290nI3wx7yzeXGA3Z/yzCyJ(0-)RN');
define('NONCE_SALT', 'B69b@@*32u;1v0DRlNXZ3G%@gp#8+Z0r58Dl7Y#/5frx579-tjGklidT_U5n)uW8');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'tJe3Quj_'; 


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', false);
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

define( 'DISABLE_WP_CRON', true );
define( 'DISALLOW_FILE_EDIT', true );
define( 'CONCATENATE_SCRIPTS', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
