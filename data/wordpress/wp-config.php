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
define( 'DB_NAME', 'db' );

/** Database username */
define( 'DB_USER', 'amine' );

/** Database password */
define( 'DB_PASSWORD', 'amine1337' );

/** Database hostname */
define( 'DB_HOST', 'mariadb:3306' );

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
define( 'AUTH_KEY',          '-WcNmCKwEz^);;.?pW5^+cI215;Dcu7$R5o#b[-3@R&Y(n9Z<pt&o1S%]0cS3GDv' );
define( 'SECURE_AUTH_KEY',   'sq*L,kc~{WCFkrArN<gWEEr2a5Qy-:DU3>.~g`hX>+O;g9BlU?Nl0sjV05[}lrl/' );
define( 'LOGGED_IN_KEY',     'Xy^wqAE455t7`.v%&9{P2Z(W&%2i_XfPIXnSOC !e9f1sBj+;>g;zF7E(!hl9jJr' );
define( 'NONCE_KEY',         '{gz:Q?;Q,7}O-eG h;uh>^MD-*v-ASnY]yz T3Z!brsk,S;j.h-&)51#LbP*c(M#' );
define( 'AUTH_SALT',         '_#mbcp&@0+<0,Ist2.Z O|4/uA[Pgwr8> 1i8&mE10bq9iyvN6>mns{$m<5,cRs/' );
define( 'SECURE_AUTH_SALT',  '6>8d95s4a/AMf8D%Zj+LM{:F7jmHCN1bQkto;~ojQ6)]zo|3Wp9{21[g|}oQ[*B-' );
define( 'LOGGED_IN_SALT',    'IW O|hY?ZK@)2U$[EE/]2j~mpThwqQB g&exNGsho=A&@k3 1m=D6gai],D,sSb4' );
define( 'NONCE_SALT',        ')`2!~xUKbC;5!knql#wvIC,Ck-wX+f*b^j<O(SNyLyaP4KJkpa<:5BV![J?14>#+' );
define( 'WP_CACHE_KEY_SALT', 'WFWYj[Zz<0kbHxywUdsYdbhtB4O Kr8M<~uxWTpN`}XkBelv]jbu&p3$&C|+8^5j' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
