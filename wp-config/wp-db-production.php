<?php
// Prevent file from being accessed directly
if (!defined('ABSPATH')) exit();

/** The name of the database for WordPress */
define( 'DB_NAME', getenv('THEPOND_DB_NAME') );

/** MySQL database username */
define( 'DB_USER', getenv('THEPOND_DB_USER') );

/** MySQL database password */
define( 'DB_PASSWORD', getenv('THEPOND_DB_PASSWORD') );

/** MySQL hostname */
define( 'DB_HOST', getenv('THEPOND_DB_HOST') . ':' . getenv('THEPOND_DB_PORT') );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', getenv('THEPOND_DB_CHARSET') );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/** File save method */
define('FS_METHOD', 'direct');

echo

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'j=?2T@O@L5bp?>)X!~zk-F=_pIy)!jK>Z]BhY8Yr@)CL uGBO8<QzH0v}*+42&Gh');
define('SECURE_AUTH_KEY',  '[c0D_?DvOSVu_~{n:+y!01)-l(*_ 8$<C-ZnWW_9l#vP In]^@RL=.+(~glZdT|c');
define('LOGGED_IN_KEY',    '5O;D*PG:>2C Ae.l*3ACA]h=A-.qQ^!Q>9[{g-!B5o{9+GV6vA.Om#1Eb~^.eO%[');
define('NONCE_KEY',        '2Lh5#V$*)}/~X;V(A8`mM4+cwc$(MT+B2?*#-`Boh]G6)HZ-rCsTh &qP;1qrBx]');
define('AUTH_SALT',        'O,v=O~v)5++qI+yKid*M=vH9>kGdR1nRX9V31lO7}+-67=Ds:0EfE_ICT|~j)AG)');
define('SECURE_AUTH_SALT', '[s4J/$l[TAIvLxkL%{1 h3$cXF!W4P4|(62TX[!xl!B~M,#K,dZap(%Gf#KqGvs0');
define('LOGGED_IN_SALT',   'b-yGI?=|<f9okx*^BN3WJc@0!<|UF|2@w}@pZ(nu;>+aPEC3;Cu[RSMb6.L&fK&A');
define('NONCE_SALT',       '0?&S5DjB_v(IMr$iipeI]!&zi[q3c(%tLuD+}<!UN/fW[N4gVp|+O(#:*|z!av$F');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'hth_';

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
define( 'WP_DEBUG', false );
 ?>
