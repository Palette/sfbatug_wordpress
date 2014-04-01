<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
    define('ENVIRONMENT', 'local');
}
else {
    define('ENVIRONMENT', 'staging');
}

if (ENVIRONMENT == 'local') {
    define('DB_NAME', 'sfbatug_wp_local');
    define('DB_USER', 'sfbatug_wp');
    define('DB_PASSWORD', 'sp1xLL09');
    define('DB_HOST', 'mysql.staging.sfbatug.org');
    define('DB_CHARSET', 'utf8');
    define('DB_COLLATE', '');
}
else if (ENVIRONMENT == 'staging'){
    define('DB_NAME', 'sfbatug_wp_staging');
    define('DB_USER', 'sfbatug_wp');
    define('DB_PASSWORD', 'sp1xLL09');
    define('DB_HOST', 'mysql.staging.sfbatug.org');
    define('DB_CHARSET', 'utf8');
    define('DB_COLLATE', '');
}

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'qka0ymad6d6x4aitcrtsxyz6qiotybmhd4vekqoddnzsn6ugsx3qpzacfug6zshs');
define('SECURE_AUTH_KEY',  'nqwizuqikpwnozwtp6ehaklwnk2nhju0cei61p9mjemghvksxijmr4emdcg05t08');
define('LOGGED_IN_KEY',    'btcabf7vskweik8b0eac1yset3dssg28tskp4g7chio5uwzcdjmlbsa0kj9rgcv3');
define('NONCE_KEY',        'my2nts5zcbtx5g6ovtihval00ggfts9ffqe1agalirwj1lnnsyhkmnoavt0at6m2');
define('AUTH_SALT',        'i37zrkvib4urnzf7teghbo4ahgyrfejjx5pp9icfmzwdu139vzsheccl8ur1pun2');
define('SECURE_AUTH_SALT', 'ndd2ao3k1kye56dadwp91g8posgj3s1ijbb4jxddvczpltt9qej4gqvtj9jw5ftz');
define('LOGGED_IN_SALT',   'bcsj7mgiep9bjseegdnpmok9ih1sv8kczidpouyxavjkzjpzpktcgfnzcgohwvid');
define('NONCE_SALT',       'dxcfsfnov1qyxuzgfhh6rtnvgijj6ftdorxpakjn4eqsr8prw34ncvbtwydf7ehl');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'sfbatug_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

/** Domain Agnostic Wordpressness **/
define('WP_HOME',       "http://{$_SERVER['SERVER_NAME']}/");
define('WP_SITEURL',    "http://{$_SERVER['SERVER_NAME']}/");
