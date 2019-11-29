<?php
// Prevent file from being accessed directly
if (!defined('ABSPATH')) exit();

/** The name of the database for WordPress */
define( 'DB_NAME', 'theponddb' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost:3308' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/** File save method */
define('FS_METHOD', 'direct');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '*Vh:.9mGC0g=VG@3=<s_:JIE+8VgIGxD ahgX,:ld)5?LE/9[&K>Bv,| ch+kOf/');
define('SECURE_AUTH_KEY',  '?giom!R7>M}ss|,x*3FX8%~h(p7#J-bxK_0c|]{a9JP#{7Jh6NC0+l)sxB[MO{{}');
define('LOGGED_IN_KEY',    '[+,wBSI)tRRKmpn=?j*Aw,C%[fa/p@=#^-|OqC{UZ7|PaNN1,!($)+7EJ+[/-6@k');
define('NONCE_KEY',        '{$,Y$ZD_Kg@u1hh+Akj-6Gd&3Yy<kqzUBpT,S%1+DG8RcU+[ZO~F:/.-(fbNs1YI');
define('AUTH_SALT',        ']!a%NGN?)?*-U3e)/yfv$c }jehABflp<ECc=1q#b?0};>6@+as4|.wBgk_]@1mj');
define('SECURE_AUTH_SALT', 'a X<wDFY_&P?T/ly!h)!^n$1PhiSSQP.}#3I`Wa4DyK__jUW?TG+W/w~Hs$$9L(6');
define('LOGGED_IN_SALT',   'D$m|>F-/Vn0?qC5UwUg%>mPD$/Ph#J!(Fdgc}4g.<-tP|(,*|8Kh{R%|F0Xljf,0');
define('NONCE_SALT',       'aHn<5[SAeZ?(0N,qxs{9>_8x+ SJh%g/c8bwS,m`qAcNw-O*<jPpBFWX|6XrU1p,');

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
