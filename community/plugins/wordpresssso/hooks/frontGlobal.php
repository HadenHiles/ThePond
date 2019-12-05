//<?php

/**
 * WordPress SSO - Template Hook
 *
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

class hook7 extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'userBar' =>
  array (
    0 =>
    array (
      'selector' => '#elSignInLink',
      'type' => 'replace',
      'content' => '{{if !empty(\IPS\Settings::i()->wordpress_url)}}
<li id=\'elSignInLink\'>
  <a href=\'{url="app=core&module=system&controller=login" seoTemplate="login" protocol="\IPS\Settings::i()->logins_over_https"}\' id=\'elUserSignIn\'>
    {lang="sign_in"}
  </a>
</li>
{{else}}
<li id=\'elSignInLink\'>
  <a href=\'{url="app=core&module=system&controller=login" seoTemplate="login" protocol="\IPS\Settings::i()->logins_over_https"}\' data-ipsMenu-closeOnClick="false" data-ipsMenu id=\'elUserSignIn\'>
    {lang="sign_in"}  <i class=\'fa fa-caret-down\'></i>
  </a>
  {template="loginPopup" app="core" group="global" params="new \IPS\Login( \IPS\Http\Url::internal( \'app=core&module=system&controller=login\', \'front\', \'login\', NULL, \IPS\Settings::i()->logins_over_https ) )"}
</li>
{{endif}}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */
}