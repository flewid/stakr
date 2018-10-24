<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_mainwp');

/** MySQL database username */
define('DB_USER', 'wp_mainwp');

/** MySQL database password */
define('DB_PASSWORD', '5mEM7nEEABbGBP6b');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'k}2!L3TNG_x!V(i,l >RA~3D)i/)k^iqp-tB?coJ=ky].-l*~2H]-9AAlHDWQJ+D');
define('SECURE_AUTH_KEY',  'd/ ]w?2(_uaE=Bia.] zrB(WJ+l3o2s[5}24?`B(R$E~^EGOY:4g|GnY.9*{<e*-');
define('LOGGED_IN_KEY',    'NWawUjt_Z+o)w--zW:}MqN4TT5pWG-%hGa9$qoB865{q+fZS{HwhF+I=1BGQpaGC');
define('NONCE_KEY',        'RlhL6KJr0WO6|vM-s|sF(2|gS-ps4^3<ki{l#;lHfYL9B4@~es;0*xjoI`u{ab&=');
define('AUTH_SALT',        'CK>+#M1bCH,:$zf]+Lji3SwLB/Cv=T^^Gn>c1t!,W[Ip), C%t+z/Xrn,6cq$&jD');
define('SECURE_AUTH_SALT', '/0@xZapCqQ1sr#,).nkh&)Oo![&N`X)69/9/IqWgXWv5-|IU{NA6YH`Eq00]3b^0');
define('LOGGED_IN_SALT',   '*[JB+- |^0<tpOCdR.7xfd+YPl}c559J)r[{HCd-#SQUOF)/!}qOe+1G1j_<&2Y`');
define('NONCE_SALT',       '^(TBS8Zt^cIZ_MW-q4|+@f:| 1Jub,2~vDf!I*,!s7Sft[e]+7Uf:X[WvrNX+,o_');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);
define('WP_MEMORY_LIMIT', '96M');
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
