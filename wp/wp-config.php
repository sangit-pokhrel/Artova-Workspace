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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'artovawo_wp825' );

/** Database username */
define( 'DB_USER', 'artovawo_wp825' );

/** Database password */
define( 'DB_PASSWORD', '46p7z64)!S' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'bhwljrwtzjsh8caoqugap5gztbgopy8iqjfxst37hw18x0zgwewhrp51k7dvpdjs' );
define( 'SECURE_AUTH_KEY',  'vs5rqordcbkh25mskqbr8wckxkvzqmlcwx0i9kbpfm87apyyiw5ltqdxw02i0zak' );
define( 'LOGGED_IN_KEY',    'lm5btcuuv4koxh7ip2kgb5bfodjwwsuv2e0bpx6t8yax1brbdk7afymuzl4a5o7l' );
define( 'NONCE_KEY',        'e5q4zy1xmoleervhk9oahhvs9pnletbac8t99g3ettlolwbb8p3gqkztnhjpkfwz' );
define( 'AUTH_SALT',        'cwxjddntnbtr3muchqntj3b4y9xm9hja0npz2g7tci8narx0dfa8f9lbzaitivyz' );
define( 'SECURE_AUTH_SALT', 'gamn7ezuwqdaczlpx5nhyovdlwx9oglqjymmo5vh36jkpjqhjlhmr3rvhkzsyb4d' );
define( 'LOGGED_IN_SALT',   'seqjciywa7it6mpaqz1talsn6e0xxldvs1cstj74iwwqcjaix3wj4kjteshsunz3' );
define( 'NONCE_SALT',       'h6ncfsvcciw0g7qsfo03qqzepdiufbd2xfwvd84ze4bmctmpu4kfjhltpmasoaah' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpsn_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
