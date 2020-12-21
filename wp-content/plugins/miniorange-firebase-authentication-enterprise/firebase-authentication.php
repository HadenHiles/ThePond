<?php
/**
 *
 * @link              https://miniorange.com
 * @since             1.0.0
 * @package           Firebase_Authentication
 *
 * @wordpress-plugin
 * Plugin Name:       Firebase Authentication
 * Plugin URI:        firebase-authentication
 * Description:       This plugin allows login into Wordpress using Firebase as Identity provider.
 * Version:           21.2.4
 * Author:            miniOrange
 * Author URI:        https://miniorange.com
 * License:           GPL2
 */


if (defined("\x57\x50\x49\116\x43")) {
    goto xq;
}
die;
xq:
define("\x4d\117\x5f\x46\111\x52\x45\102\101\123\105\137\x41\125\x54\x48\105\x4e\x54\x49\103\x41\x54\111\x4f\x4e\137\126\x45\122\x53\111\x4f\116", "\x32\61\x2e\62\56\64");
function mo_firebase_activate_firebase_authentication()
{
    require_once plugin_dir_path(__FILE__) . "\x69\156\143\x6c\165\144\145\163\57\x63\154\141\x73\163\55\x66\151\162\145\x62\141\163\145\x2d\x61\x75\x74\x68\x65\x6e\164\x69\x63\x61\x74\151\x6f\156\x2d\x61\x63\164\151\x76\141\x74\x6f\x72\x2e\x70\x68\x70";
    MO_Firebase_Authentication_Activator::activate();
}
function mo_firebase_deactivate_firebase_authentication()
{
    require_once plugin_dir_path(__FILE__) . "\x69\156\143\x6c\165\x64\145\163\x2f\143\x6c\141\163\x73\x2d\146\x69\162\145\x62\141\163\x65\55\141\165\x74\x68\x65\156\164\151\143\141\x74\x69\157\156\x2d\144\145\x61\x63\164\x69\166\x61\164\x6f\162\56\x70\x68\160";
    MO_Firebase_Authentication_Deactivator::deactivate();
}
register_activation_hook(__FILE__, "\x6d\x6f\137\146\x69\162\145\x62\x61\x73\145\137\x61\x63\x74\151\166\141\164\145\137\x66\x69\x72\x65\x62\x61\163\x65\x5f\x61\x75\164\150\x65\156\164\x69\x63\x61\x74\x69\x6f\156");
register_deactivation_hook(__FILE__, "\x6d\x6f\137\x66\x69\x72\145\142\x61\163\145\x5f\144\x65\x61\x63\164\151\166\141\x74\x65\137\146\x69\x72\x65\x62\x61\x73\x65\137\141\165\x74\150\145\156\164\x69\143\x61\164\x69\x6f\156");
require plugin_dir_path(__FILE__) . "\151\x6e\143\154\x75\x64\x65\x73\57\143\x6c\x61\163\x73\55\146\x69\x72\145\x62\x61\x73\145\55\141\x75\x74\x68\145\156\x74\x69\143\x61\x74\151\x6f\x6e\56\x70\150\160";
require_once "\x63\154\x61\163\163\x2d\155\x6f\x2d\x66\x69\162\145\142\x61\163\145\x2d\143\157\x6e\146\151\x67\x2e\x70\x68\x70";
require "\x76\151\x65\167\x73\57\146\x65\x65\144\x62\141\143\153\x5f\146\157\x72\155\x2e\160\150\160";
require "\141\144\155\x69\156\x2f\143\154\x61\163\x73\x2d\x66\151\162\145\142\x61\x73\145\55\141\x75\x74\x68\145\x6e\164\x69\x63\x61\164\151\x6f\156\x2d\x63\165\x73\164\x6f\x6d\x65\x72\56\x70\x68\x70";
function mo_firebase_run_firebase_authentication()
{
    $I7 = new MO_Firebase_Authentication();
    $I7->run();
}
mo_firebase_run_firebase_authentication();
function mo_firebase_authentication_is_customer_registered()
{
    $Sl = get_option("\155\x6f\x5f\146\151\x72\145\142\141\163\145\137\x61\x75\x74\x68\145\156\164\151\143\x61\x74\x69\x6f\156\137\141\144\155\151\156\x5f\145\x6d\141\x69\x6c");
    $q4 = get_option("\x6d\x6f\x5f\146\151\x72\x65\x62\141\163\145\x5f\x61\165\164\x68\145\x6e\164\151\x63\141\x74\151\x6f\x6e\137\x61\x64\155\x69\x6e\x5f\143\x75\x73\x74\157\x6d\x65\x72\137\x6b\145\x79");
    if (!$Sl || !$q4 || !is_numeric(trim($q4))) {
        goto AC;
    }
    return 1;
    goto GX;
    AC:
    return 0;
    GX:
}
function mo_firebase_authentication_is_clv()
{
    $s2 = get_option("\x6d\157\x5f\146\x69\162\x65\x62\141\x73\x65\x5f\141\165\164\x68\x65\x6e\164\x69\x63\x61\164\x69\x6f\156\x5f\154\x6b");
    $cu = get_option("\155\157\137\146\x69\162\145\x62\141\x73\x65\x5f\141\x75\164\x68\145\156\x74\151\143\141\x74\151\x6f\x6e\137\154\166");
    if (!$cu) {
        goto hF;
    }
    $cu = mo_firebase_authentication_decrypt($cu);
    hF:
    if (!(!empty($s2) && $cu == "\x74\162\165\x65")) {
        goto hP;
    }
    return 1;
    hP:
    return 0;
}
function mo_firebase_authentication_encrypt($RJ)
{
    $A0 = get_option("\x6d\x6f\x5f\x66\x69\x72\x65\x62\141\163\145\x5f\141\x75\164\150\x65\x6e\164\151\x63\141\164\x69\x6f\x6e\137\143\165\163\x74\157\x6d\x65\162\x5f\164\x6f\153\145\156");
    $A0 = str_split(str_pad('', strlen($RJ), $A0, STR_PAD_RIGHT));
    $dn = str_split($RJ);
    foreach ($dn as $Xb => $gW) {
        $Om = ord($gW) + ord($A0[$Xb]);
        $dn[$Xb] = chr($Om > 255 ? $Om - 256 : $Om);
        Bo:
    }
    tt:
    return base64_encode(join('', $dn));
}
function mo_firebase_authentication_decrypt($RJ)
{
    $RJ = base64_decode($RJ);
    $A0 = get_option("\155\157\137\x66\x69\x72\145\142\x61\163\x65\137\x61\x75\x74\150\145\156\x74\x69\x63\141\164\x69\157\156\137\143\x75\x73\164\157\155\x65\162\137\164\157\x6b\145\x6e");
    $A0 = str_split(str_pad('', strlen($RJ), $A0, STR_PAD_RIGHT));
    $dn = str_split($RJ);
    foreach ($dn as $Xb => $gW) {
        $Om = ord($gW) - ord($A0[$Xb]);
        $dn[$Xb] = chr($Om < 0 ? $Om + 256 : $Om);
        nq:
    }
    g2:
    return join('', $dn);
}
class mo_firebase_authentication_login
{
    function __construct()
    {
        add_action("\x69\156\x69\164", array($this, "\x70\x6f\163\164\x52\x65\163\x67\x69\x74\x65\162"));
        add_action("\141\144\x6d\151\156\x5f\x69\156\x69\x74", array($this, "\x6d\x6f\137\x66\x69\162\x65\142\141\x73\145\x5f\x61\165\x74\150\137\163\x61\x76\x65\x5f\x73\x65\x74\x74\x69\x6e\x67\163"));
        if (!(get_option("\155\x6f\137\145\156\141\142\154\145\x5f\x66\x69\162\x65\x62\x61\x73\x65\x5f\141\x75\x74\x68") == 1)) {
            goto sS;
        }
        if (!(strpos($_SERVER["\x52\105\121\125\105\123\x54\x5f\125\122\x49"], "\x2f\x77\x70\55\152\163\157\156") === false)) {
            goto pN;
        }
        remove_filter("\141\165\164\150\x65\156\164\x69\143\141\x74\x65", "\167\160\x5f\x61\165\164\150\145\x6e\x74\x69\143\141\164\x65\137\165\163\x65\x72\156\x61\x6d\x65\x5f\x70\141\x73\163\x77\x6f\x72\144", 20, 3);
        remove_filter("\x61\165\x74\150\x65\156\164\x69\x63\141\164\x65", "\x77\160\137\141\x75\164\150\145\x6e\x74\151\143\141\x74\x65\137\x65\x6d\141\151\154\x5f\160\141\x73\x73\x77\157\x72\144", 20, 3);
        add_filter("\x61\x75\x74\x68\145\156\164\151\x63\x61\x74\x65", array($this, "\x6d\x6f\x5f\146\x69\x72\145\x62\141\163\x65\137\x61\165\164\150"), 0, 3);
        pN:
        sS:
        add_action("\x6c\x6f\x67\x69\x6e\137\146\157\162\x6d", array($this, "\155\x6f\x5f\x66\151\162\x65\x62\141\x73\x65\x5f\141\165\164\x68\x5f\x77\160\154\157\x67\x69\x6e\x5f\x66\x6f\x72\x6d\137\142\165\164\164\157\156"));
        add_shortcode("\155\157\137\x66\151\162\x65\142\141\x73\145\137\141\x75\164\150\x5f\154\x6f\x67\x69\x6e", array($this, "\155\157\137\x66\x69\x72\x65\x62\x61\x73\x65\137\x61\165\x74\x68\137\x73\x68\157\162\x74\143\x6f\x64\145\137\x6c\x6f\x67\x69\x6e"));
        remove_action("\x61\144\x6d\x69\156\137\156\157\164\x69\143\x65\163", array($this, "\x6d\157\x5f\146\x69\162\145\142\x61\x73\145\x5f\141\165\x74\x68\137\163\x75\143\x63\145\163\x73\x5f\155\145\x73\x73\x61\147\x65"));
        remove_action("\x61\x64\x6d\x69\x6e\x5f\156\x6f\164\x69\143\x65\163", array($this, "\x6d\157\137\x66\151\162\145\142\141\163\145\x5f\141\165\x74\150\137\x65\x72\162\x6f\x72\x5f\155\x65\x73\163\x61\x67\145"));
        add_action("\x61\x64\155\x69\156\x5f\146\x6f\x6f\x74\145\162", array($this, "\x6d\x6f\137\x66\151\162\145\142\x61\x73\x65\137\141\165\x74\x68\x5f\x66\x65\x65\x64\x62\141\143\x6b\137\162\x65\161\165\x65\x73\164"));
    }
    function postResgiter()
    {
        if (!(isset($_POST["\166\x65\x72\151\x66\171\137\165\163\x65\x72"]) && isset($_REQUEST["\x70\x61\x67\x65"]) && sanitize_text_field(wp_unslash($_REQUEST["\x70\141\x67\x65"])) == "\x6d\x6f\x5f\x66\151\162\x65\x62\x61\x73\145\x5f\141\x75\164\x68\x65\156\x74\151\143\x61\x74\151\157\x6e\x5f\163\145\x74\x74\151\156\x67\163" && isset($_REQUEST["\155\157\x5f\146\x69\162\145\x62\141\x73\x65\x5f\x61\165\x74\150\137\x63\157\156\146\151\x67\137\x66\151\x65\154\x64"]) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST["\x6d\157\137\146\151\162\x65\142\141\163\x65\x5f\141\x75\x74\150\x5f\143\157\x6e\146\x69\x67\x5f\146\x69\x65\x6c\144"])), "\x6d\x6f\x5f\146\151\x72\x65\142\x61\163\145\137\x61\x75\164\x68\137\143\157\x6e\146\x69\147\137\x66\157\x72\x6d"))) {
            goto jw;
        }
        if (!current_user_can("\x61\x64\x6d\151\x6e\x69\163\164\162\x61\164\x6f\162")) {
            goto h5;
        }
        update_option("\155\x6f\137\x66\x69\162\x65\x62\x61\163\x65\x5f\x61\x75\x74\x68\137\144\x69\163\x61\142\x6c\145\137\x77\157\162\x64\160\x72\x65\163\163\x5f\x6c\x6f\147\x69\x6e", isset($_POST["\144\x69\x73\141\x62\x6c\x65\137\x77\157\162\x64\x70\x72\145\x73\163\137\x6c\157\147\x69\156"]) ? (int) filter_var($_POST["\144\x69\x73\141\x62\x6c\x65\x5f\167\x6f\162\144\160\x72\145\x73\163\137\154\157\x67\151\156"], FILTER_SANITIZE_NUMBER_INT) : 0);
        update_option("\155\157\x5f\x66\151\x72\145\142\x61\x73\145\137\141\165\164\150\x5f\145\156\141\142\x6c\145\x5f\x61\x64\x6d\151\x6e\137\167\x70\x5f\x6c\x6f\x67\151\x6e", isset($_POST["\x6d\157\x5f\146\151\162\145\x62\141\163\x65\x5f\141\x75\164\150\x5f\x65\156\x61\142\x6c\x65\x5f\x61\x64\x6d\151\156\x5f\x77\160\137\x6c\x6f\147\151\x6e"]) ? $_POST["\x6d\157\x5f\x66\151\x72\x65\x62\x61\163\x65\137\141\165\x74\150\x5f\x65\156\141\142\x6c\145\137\141\144\155\151\156\x5f\x77\160\137\154\x6f\147\x69\156"] : 0);
        $OQ = isset($_POST["\160\162\x6f\152\x65\x63\164\151\x64"]) ? sanitize_text_field($_POST["\x70\162\157\x6a\145\143\x74\151\x64"]) : '';
        update_option("\x6d\x6f\x5f\x66\x69\162\x65\142\141\163\145\137\141\165\164\150\137\160\162\157\152\x65\143\x74\x5f\x69\144", $OQ);
        $IA = isset($_POST["\141\160\151\153\x65\x79"]) ? sanitize_text_field($_POST["\x61\160\x69\x6b\145\171"]) : '';
        update_option("\155\157\137\146\x69\x72\x65\x62\x61\x73\145\137\141\165\x74\150\137\141\x70\151\x5f\x6b\145\x79", $IA);
        $this->mo_firebase_auth_store_certificates();
        update_option("\155\x6f\137\x66\x69\162\x65\x62\141\x73\x65\137\141\x75\x74\150\x5f\155\145\x73\x73\141\147\145", "\x43\157\156\146\x69\x67\165\162\x61\164\x69\157\156\163\40\x73\141\x76\145\x64\40\x73\165\x63\x63\x65\163\163\x66\x75\154\154\x79\x2e\x20\x50\154\145\x61\163\x65\x20\74\x61\x20\150\x72\x65\x66\x3d\x22" . admin_url("\x61\x64\x6d\151\x6e\x2e\160\x68\x70\x3f\160\141\147\145\x3d\155\x6f\137\x66\x69\x72\145\142\141\163\145\x5f\141\x75\x74\x68\145\x6e\x74\151\143\x61\164\151\x6f\156\x5f\x73\145\164\x74\151\x6e\147\163\x26\x74\x61\x62\75\143\157\x6e\146\x69\147\43\164\145\163\x74\x5f\x61\165\164\150\145\x6e\x74\x69\x63\x61\164\x69\157\156") . "\x22\x3e\124\x65\x73\164\40\x41\x75\x74\150\145\x6e\x74\x69\143\x61\x74\x69\157\x6e\x3c\x2f\141\76\x20\x62\145\146\157\162\x65\40\164\x72\x79\x69\156\147\x20\164\157\40\114\x6f\x67\151\156\x2e");
        $this->mo_firebase_auth_show_success_message();
        h5:
        jw:
    }
    function mo_firebase_auth_store_certificates()
    {
        $UT = wp_remote_get("\x68\x74\164\160\x73\x3a\57\x2f\x77\x77\x77\x2e\x67\x6f\157\147\x6c\145\141\x70\151\163\56\143\x6f\155\57\x72\x6f\142\x6f\164\57\166\x31\x2f\x6d\145\164\141\144\141\164\x61\57\170\65\60\71\x2f\163\x65\x63\165\x72\x65\x74\x6f\153\x65\x6e\x40\163\171\163\x74\x65\155\56\147\x73\x65\x72\x76\151\x63\145\141\x63\143\157\165\x6e\x74\56\143\x6f\155");
        if (!is_array($UT)) {
            goto Yb;
        }
        $B0 = $UT["\150\145\141\144\145\x72\163"];
        $Hm = $UT["\142\x6f\x64\x79"];
        $Gc = explode("\72", $Hm);
        $mv = count($Gc);
        $i5 = substr($Gc[0], 5, 40);
        $l2 = explode("\x2c", $Gc[1]);
        $r0 = substr($l2[0], 2, 1158);
        $r0 = str_replace("\134\x6e", '', $r0);
        update_option("\155\157\x5f\x66\x69\162\145\142\x61\163\x65\137\141\x75\164\x68\x5f\153\x69\144\x31", $i5);
        update_option("\155\157\137\x66\151\162\145\x62\x61\x73\145\x5f\141\x75\164\x68\x5f\143\145\x72\x74\x31", $r0);
        if ($mv == 3) {
            goto EB;
        }
        if (!($mv > 3)) {
            goto X2;
        }
        $ak = substr($l2[1], 4, 40);
        $v3 = explode("\54", $Gc[2]);
        $tL = substr($v3[0], 2, 1158);
        $Y2 = substr($v3[1], 4, 40);
        $Hk = explode("\x7d", $Gc[3]);
        $Hk[0] = substr($Hk[0], 2, 1158);
        $tL = str_replace("\x5c\156", '', $tL);
        update_option("\155\157\x5f\146\x69\x72\145\x62\x61\x73\x65\x5f\x61\165\x74\150\137\x6b\151\144\62", $ak);
        update_option("\155\x6f\x5f\x66\x69\x72\145\142\x61\x73\x65\137\141\165\164\150\x5f\143\145\162\164\x32", $tL);
        $Hk[0] = str_replace("\134\156", '', $Hk[0]);
        update_option("\155\x6f\137\146\151\x72\x65\x62\141\163\x65\x5f\141\x75\x74\150\137\153\x69\x64\63", $Y2);
        update_option("\155\157\x5f\x66\151\162\x65\142\x61\163\145\x5f\x61\165\x74\150\137\x63\x65\162\x74\x33", $Hk[0]);
        X2:
        goto lj;
        EB:
        $ak = substr($l2[1], 4, 40);
        $tL = explode("\x7d", $Gc[2]);
        $tL[0] = substr($tL[0], 2, 1158);
        $tL[0] = str_replace("\134\156", '', $tL[0]);
        update_option("\155\x6f\x5f\146\151\x72\x65\142\x61\x73\145\x5f\x61\165\164\x68\137\153\x69\144\62", $ak);
        update_option("\x6d\x6f\x5f\146\x69\x72\x65\x62\141\x73\x65\137\141\x75\x74\150\x5f\143\x65\162\164\x32", $tL[0]);
        lj:
        Yb:
    }
    function mo_firebase_auth_wplogin_form_style()
    {
        wp_enqueue_style("\x6d\157\137\x66\x69\162\x65\142\141\x73\145\x5f\x61\165\164\x68\137\x66\x6f\156\x74\141\167\145\163\x6f\155\x65", plugins_url("\143\x73\x73\57\146\x6f\156\x74\55\x61\167\145\x73\157\x6d\145\56\x63\163\163", __FILE__));
        wp_enqueue_style("\x6d\157\137\x66\151\162\145\142\x61\163\145\x5f\x61\165\164\x68\x5f\x77\160\x6c\157\x67\151\x6e\146\157\x72\155", plugins_url("\143\163\x73\x2f\154\157\147\151\156\55\x70\141\147\145\x2e\143\163\163", __FILE__));
    }
    function mo_firebase_auth_wplogin_form_button()
    {
        $c5 = 1;
        $uf = get_option("\x6d\x6f\x5f\x66\x69\x72\145\x62\x61\163\x65\137\141\165\x74\x68\x5f\x70\162\157\166\151\x64\145\x72\137\x6d\x65\x74\150\157\x64\137\x6c\151\x73\x74");
        $Qb = get_option("\155\x6f\x5f\x66\x69\162\145\142\x61\163\145\x5f\141\x75\164\x68\x5f\x73\x68\157\167\137\x6f\x6e\137\154\x6f\x67\151\156\x5f\160\141\x67\145");
        if (!($uf !== false)) {
            goto mH;
        }
        foreach ($uf as $QB => $Fl) {
            if (!(isset($Qb) && $Qb == 1)) {
                goto r1;
            }
            if (!($c5 === 1)) {
                goto R5;
            }
            echo "\74\x62\162\76";
            echo "\x3c\x68\64\76\103\x6f\x6e\156\x65\x63\x74\x20\x77\x69\164\150\x20\x3a\74\x2f\150\64\76\x3c\x62\162\76";
            echo "\x3c\x64\151\166\x20\143\x6c\141\x73\x73\75\x22\162\x6f\x77\x22\x3e";
            $c5 = 0;
            R5:
            $this->mo_firebase_auth_wplogin_form_style();
            $sQ = "\146\141\x20\146\x61\55\x6c\157\143\x6b";
            if ($Fl == "\107\x6f\157\x67\154\x65") {
                goto UE;
            }
            if ($Fl == "\x46\141\x63\145\142\157\x6f\x6b") {
                goto Rm;
            }
            if ($Fl == "\x47\151\164\150\x75\x62") {
                goto HR;
            }
            if ($Fl == "\124\x77\151\164\x74\x65\x72") {
                goto Mx;
            }
            if ($Fl == "\115\151\143\162\157\x73\x6f\x66\x74") {
                goto KP;
            }
            if ($Fl == "\x59\141\x68\157\x6f") {
                goto lk;
            }
            if ($Fl == "\101\160\x70\x6c\x65") {
                goto Ba;
            }
            goto Jd;
            UE:
            $sQ = "\x66\x61\x20\x66\x61\55\x67\157\157\x67\154\145\55\160\154\x75\x73";
            goto Jd;
            Rm:
            $sQ = "\x66\141\40\146\x61\x2d\146\141\x63\x65\142\x6f\157\x6b";
            goto Jd;
            HR:
            $sQ = "\146\141\x20\x66\x61\x2d\x67\x69\164\x68\x75\x62";
            goto Jd;
            Mx:
            $sQ = "\146\x61\x20\x66\x61\55\x74\x77\x69\164\164\145\x72";
            goto Jd;
            KP:
            $sQ = "\x66\x61\40\x66\x61\x2d\x77\x69\x6e\x64\157\167\x73\x6c\151\x76\145";
            goto Jd;
            lk:
            $sQ = "\x66\x61\x20\146\x61\x2d\171\x61\x68\x6f\157";
            goto Jd;
            Ba:
            $sQ = "\146\x61\40\x66\x61\x2d\141\x70\x70\154\x65";
            Jd:
            echo "\74\x61\40\163\x74\x79\x6c\x65\75\x22\x74\x65\170\x74\55\144\x65\143\157\162\141\164\x69\x6f\x6e\72\x6e\157\156\x65\x22\x20\150\162\x65\x66\x3d\42\152\x61\166\141\x73\x63\162\x69\160\164\x3a\166\x6f\151\x64\50\x30\51\x22\x20\x69\x64\x3d\42\155\x6f\x5f\146\151\162\x65\142\x61\x73\x65\x5f" . ucwords($Fl) . "\137\x70\162\x6f\166\x69\144\145\162\x5f\154\157\147\151\x6e\42\x3e\74\x64\x69\166\40\143\x6c\x61\163\x73\75\42\155\157\137\146\x69\x72\145\x62\x61\x73\x65\137\x61\x75\164\150\x5f\154\x6f\x67\151\x6e\137\x62\165\164\164\157\156\42\x3e\x3c\151\40\143\154\141\x73\x73\x3d\42" . $sQ . "\x20\155\x6f\x5f\x66\x69\x72\x65\x62\141\x73\145\x5f\x61\x75\x74\150\x5f\x6c\x6f\147\x69\156\137\142\x75\164\164\x6f\x6e\137\151\x63\x6f\x6e\x22\76\74\x2f\x69\76\x3c\150\x33\40\143\154\141\163\x73\x3d\x22\x6d\x6f\x5f\x66\151\162\145\142\141\163\x65\137\141\x75\164\x68\137\x6c\157\x67\151\x6e\137\x62\x75\164\x74\x6f\x6e\137\164\x65\170\164\x22\76\114\x6f\147\151\x6e\40\167\x69\x74\150\x20" . ucwords($Fl) . "\x3c\x2f\x68\x33\x3e\x3c\57\x64\x69\x76\x3e\x3c\x2f\x61\x3e";
            r1:
            v4:
        }
        O_:
        if (!($c5 === 0)) {
            goto xk;
        }
        echo "\74\x2f\x64\151\166\76";
        echo "\x3c\x62\x72\x3e\x3c\x62\x72\x3e";
        $c5 = 1;
        xk:
        mH:
    }
    function mo_firebase_auth_shortcode_login()
    {
        $M8 = '';
        $uf = get_option("\x6d\x6f\137\146\151\162\x65\x62\141\163\145\137\141\165\x74\150\x5f\160\162\x6f\166\151\x64\145\162\x5f\155\145\x74\150\157\144\x5f\x6c\151\163\x74");
        $Qb = get_option("\155\x6f\x5f\146\x69\162\145\142\141\x73\145\137\141\165\x74\x68\137\x73\150\157\x77\x5f\x6f\156\x5f\x6c\157\x67\x69\x6e\137\160\141\x67\x65");
        if (!is_user_logged_in()) {
            goto kZ;
        }
        $current_user = wp_get_current_user();
        $ou = "\x48\x6f\x77\144\x79\54\x20\43\43\x75\x73\145\x72\x23\43";
        $ou = str_replace("\43\43\x75\x73\x65\162\x23\x23", $current_user->display_name, $ou);
        $C9 = site_url();
        $d1 = __($ou, "\x66\154\x77");
        $M8 .= $d1 . "\40\174\40" . wp_loginout($C9, false);
        goto Wc;
        kZ:
        if ($uf) {
            goto fj;
        }
        $M8 .= "\116\x6f\164\40\163\x65\x6c\145\143\x74\x65\144\x20\x61\x6e\x79\x20\x61\x75\x74\x68\x65\x6e\x74\151\143\141\x74\151\157\x6e\40\x6d\145\x74\x68\157\144\x2e";
        return $M8;
        fj:
        $this->mo_firebase_auth_load_provider_login_script();
        foreach ($uf as $QB => $Fl) {
            $this->mo_firebase_auth_wplogin_form_style();
            $sQ = "\x66\x61\40\146\x61\55\x6c\x6f\x63\153";
            if ($Fl == "\107\157\x6f\x67\154\x65") {
                goto TI;
            }
            if ($Fl == "\x46\141\143\x65\142\x6f\157\x6b") {
                goto lD;
            }
            if ($Fl == "\107\151\x74\x68\165\142") {
                goto Y0;
            }
            if ($Fl == "\124\x77\151\x74\164\145\x72") {
                goto b5;
            }
            if ($Fl == "\115\x69\143\x72\x6f\x73\x6f\x66\x74") {
                goto tT;
            }
            if ($Fl == "\131\x61\150\x6f\157") {
                goto xA;
            }
            if ($Fl == "\x41\160\x70\154\145") {
                goto fu;
            }
            goto V7;
            TI:
            $sQ = "\x66\x61\x20\x66\141\x2d\x67\x6f\x6f\x67\x6c\x65\x2d\x70\x6c\165\x73";
            goto V7;
            lD:
            $sQ = "\146\x61\40\x66\141\x2d\146\141\143\145\142\x6f\x6f\x6b";
            goto V7;
            Y0:
            $sQ = "\146\141\40\146\141\55\147\x69\x74\150\x75\x62";
            goto V7;
            b5:
            $sQ = "\x66\x61\40\146\x61\55\164\167\151\x74\x74\x65\x72";
            goto V7;
            tT:
            $sQ = "\146\141\40\146\141\x2d\x77\x69\156\144\157\x77\163\154\x69\x76\145";
            goto V7;
            xA:
            $sQ = "\146\141\x20\x66\141\55\171\141\x68\x6f\x6f";
            goto V7;
            fu:
            $sQ = "\146\x61\40\x66\x61\55\x61\x70\x70\154\x65";
            V7:
            $CU = "\x34\x30\60\160\170";
            $Xc = "\x31\x32";
            $BN = "\x35";
            $Ph = "\x34";
            $WD = "\43\61\x62\67\x30\142\61";
            $fh = "\114\157\x67\x69\x6e\40\x77\x69\x74\150\x20" . ucwords($Fl);
            $M8 .= "\x3c\141\x20\150\x72\x65\146\x3d\42\x6a\141\x76\141\x73\143\x72\x69\x70\x74\x3a\166\157\x69\144\x28\x30\x29\42\x20\151\x64\x3d\42\x6d\157\137\146\x69\162\145\142\x61\163\x65\137" . ucwords($Fl) . "\137\160\x72\157\x76\151\x64\x65\162\x5f\154\157\147\x69\x6e\x22\x20\x73\164\x79\154\145\x3d\x22\143\157\154\x6f\x72\72\167\x68\x69\x74\145\73\164\x65\170\x74\55\144\145\143\157\162\x61\x74\151\x6f\156\x3a\x20\156\157\x6e\145\x3b\x20\x64\x69\x73\x70\154\x61\171\72\142\154\157\x63\153\x3b\x6d\x61\162\147\151\x6e\x3a\60\x3b\167\151\x64\164\150\72" . $CU . "\x20\41\x69\155\160\x6f\x72\x74\x61\x6e\164\73\x70\141\144\144\x69\156\x67\55\164\157\x70\72" . $Xc . "\40\41\151\x6d\160\157\162\164\x61\x6e\164\x3b\x70\x61\x64\144\x69\x6e\x67\x2d\x62\x6f\164\x74\x6f\x6d\72" . $Xc . "\x20\x21\x69\x6d\x70\157\162\164\141\x6e\164\x3b\155\141\x72\147\x69\x6e\55\x62\x6f\x74\164\157\155\72" . $BN . "\x20\41\x69\x6d\160\x6f\162\x74\x61\156\x74\73\x62\157\162\x64\x65\162\x2d\162\141\144\x69\165\x73\x3a" . $Ph . "\40\41\x69\155\160\x6f\162\164\x61\x6e\x74\73\142\x61\x63\153\x67\x72\x6f\x75\x6e\x64\x2d\143\157\x6c\157\x72\x3a\43\x30\x30\x38\145\x63\x32\73\155\x61\162\x67\x69\x6e\55\x62\157\164\x74\157\155\x3a\x38\160\x78\73\x6d\x61\162\147\x69\x6e\x2d\154\x65\146\x74\x3a\62\x30\x70\x78\73\42\40\x63\154\x61\163\x73\x3d\42\x62\164\156\x20\142\x74\156\x2d\160\162\x69\x6d\x61\162\171\x22\76\40";
            $M8 .= $sQ ? "\x3c\x69\x20\x63\x6c\141\x73\x73\75\x22" . $sQ . "\40\x6d\157\x5f\146\151\162\145\142\x61\163\x65\x5f\141\165\x74\150\x5f\154\157\x67\151\156\x5f\142\x75\x74\164\157\x6e\137\151\143\x6f\156\42\76\74\57\x69\x3e" : '';
            $M8 .= $fh . "\x20\74\x2f\141\76";
            DS:
        }
        kS:
        Wc:
        return $M8;
    }
    private function mo_firebase_auth_load_provider_login_script()
    {
        wp_enqueue_script("\x6d\157\x5f\146\x69\162\145\142\x61\x73\145\x5f\141\x70\x70\x5f\x73\143\162\x69\x70\164", plugins_url("\141\144\x6d\151\156\x2f\152\x73\x2f\x66\x69\x72\x65\142\141\x73\145\55\x61\x70\160\56\x6a\x73", __FILE__));
        wp_enqueue_script("\x6d\157\x5f\x66\x69\x72\x65\x62\x61\x73\x65\137\x61\165\164\x68\x5f\x73\143\162\151\x70\164", plugins_url("\141\144\x6d\x69\x6e\x2f\152\163\57\146\151\162\145\x62\x61\163\145\x2d\141\165\x74\x68\56\x6a\x73", __FILE__));
        wp_enqueue_script("\155\x6f\x5f\x66\151\162\x65\142\x61\x73\x65\x5f\x66\x69\x72\145\163\x74\157\x72\x65\137\x73\143\x72\151\x70\x74", plugins_url("\141\x64\x6d\151\x6e\x2f\x6a\x73\x2f\x66\151\x72\x65\x62\141\x73\x65\x2d\146\x69\162\145\x73\x74\157\x72\x65\x2e\152\163", __FILE__), array("\x6a\161\165\145\162\171"));
        wp_register_script("\155\x6f\x5f\x66\151\x72\145\142\141\163\145\x5f\141\160\160\x5f\x6d\x61\151\x6e\x5f\x73\x63\x72\151\x70\164", plugins_url("\141\x64\x6d\151\x6e\x2f\x6a\x73\x2f\146\x69\x72\145\x62\x61\x73\x65\55\141\x75\x74\x68\55\x6d\x61\x69\156\55\x73\x63\x72\x69\160\164\x2e\152\x73", __FILE__), array("\152\x71\165\145\x72\x79"));
        wp_enqueue_script("\x6d\x6f\137\146\151\162\x65\x62\141\x73\x65\137\141\160\160\x5f\x6d\141\151\x6e\137\x73\143\162\151\x70\x74");
        wp_register_script("\155\157\137\146\x69\162\145\142\141\x73\145\x5f\141\160\x70\x5f\154\x6f\147\x69\x6e\137\163\x63\x72\x69\160\x74", plugins_url("\141\144\155\151\156\57\152\163\x2f\x66\x69\x72\145\x62\141\x73\145\55\167\160\x2d\154\157\x67\x69\x6e\56\152\163", __FILE__), array("\x6a\x71\x75\145\162\171"));
        $iB = array();
        $iB["\x61\x70\x69\x5f\x6b\145\171"] = get_option("\155\x6f\x5f\x66\151\162\145\x62\x61\163\145\x5f\141\x75\164\150\137\x61\x70\x69\137\153\x65\x79");
        $iB["\160\162\157\152\145\x63\x74\x5f\151\x64"] = get_option("\155\157\x5f\x66\x69\x72\x65\x62\141\163\x65\x5f\x61\165\x74\150\x5f\160\x72\157\x6a\145\143\164\x5f\151\144");
        $iB["\x65\156\141\142\x6c\145\x5f\146\151\x72\x65\x62\x61\163\145\137\x6c\157\x67\151\156"] = get_option("\x6d\x6f\137\145\156\141\x62\154\x65\137\146\x69\162\145\x62\x61\x73\145\137\x61\165\x74\150");
        $iB["\144\151\163\141\142\x6c\x65\137\167\x70\x5f\154\157\147\151\156"] = get_option("\155\157\x5f\146\151\162\145\x62\x61\163\145\137\x61\165\164\150\x5f\144\x69\163\141\142\154\x65\137\x77\x6f\162\x64\160\162\x65\163\163\x5f\154\x6f\147\151\156");
        $iB["\145\x6e\x61\142\x6c\145\x5f\x61\144\x6d\x69\x6e\x5f\167\160\x5f\x6c\x6f\x67\x69\x6e"] = get_option("\155\x6f\137\x66\x69\x72\145\142\x61\x73\x65\137\141\x75\x74\x68\137\x65\x6e\141\x62\x6c\x65\x5f\x61\x64\155\151\156\x5f\x77\x70\x5f\154\x6f\147\151\156");
        wp_localize_script("\155\x6f\x5f\146\151\x72\x65\142\141\163\x65\x5f\141\x70\x70\x5f\x6c\157\147\151\156\x5f\x73\143\162\x69\x70\x74", "\146\151\162\x65\142\141\x73\145\137\x64\x61\x74\141", $iB);
        wp_enqueue_script("\155\x6f\x5f\146\x69\162\x65\142\141\x73\145\x5f\x61\160\x70\x5f\x6c\157\x67\151\156\137\x73\143\x72\x69\x70\164");
    }
    function mo_firebase_auth($user, $C6, $lK)
    {
        if (!("\x50\x4f\123\x54" !== sanitize_text_field(wp_unslash($_SERVER["\x52\105\121\x55\105\123\124\x5f\115\x45\x54\x48\117\x44"])))) {
            goto Jm;
        }
        return $user;
        Jm:
        if (!(empty($C6) || empty($lK))) {
            goto HT;
        }
        $Wp = new WP_Error();
        if (isset($_POST["\x66\142\137\x65\162\162\x6f\162\x5f\155\x73\147"])) {
            goto wC;
        }
        if (empty($C6)) {
            goto jM;
        }
        if (!empty($lK)) {
            goto Ze;
        }
        $Wp->add("\x65\155\160\x74\171\137\x70\141\x73\163\167\x6f\x72\144", __("\x3c\163\164\x72\x6f\156\147\76\105\x52\x52\117\122\74\57\163\x74\162\157\156\x67\76\72\40\120\141\163\x73\x77\157\162\x64\x20\x66\151\145\x6c\144\40\151\x73\x20\145\x6d\x70\x74\x79\56"));
        Ze:
        goto j1;
        jM:
        $Wp->add("\x65\155\160\x74\171\x5f\x75\163\x65\x72\156\x61\155\x65", __("\74\x73\164\162\157\156\x67\76\105\122\x52\x4f\x52\74\x2f\x73\x74\162\157\156\x67\76\x3a\x20\105\x6d\x61\x69\x6c\x20\146\151\145\x6c\144\x20\x69\x73\40\x65\x6d\x70\164\x79\x2e"));
        j1:
        goto nk;
        wC:
        $Wp->add("\x66\151\x72\145\x62\141\163\145\137\x65\x72\162\157\162\137\155\163\x67", __("\74\x73\x74\x72\x6f\156\x67\x3e\x45\122\122\117\122\74\x2f\x73\164\162\x6f\156\x67\x3e\72\40" . esc_html(wp_unslash($_POST["\146\x62\x5f\145\x72\x72\157\162\137\x6d\x73\147"]))));
        nk:
        return $Wp;
        HT:
        if (get_option("\x6d\157\x5f\x66\151\x72\145\x62\141\163\145\x5f\x61\165\164\x68\x5f\144\151\163\141\x62\x6c\145\137\167\157\162\144\160\162\145\163\x73\137\154\x6f\147\x69\156") == false) {
            goto Vx;
        }
        if (!get_option("\155\x6f\x5f\146\x69\x72\x65\x62\x61\x73\x65\x5f\141\165\x74\150\137\x65\156\x61\142\154\145\137\x61\144\x6d\151\156\137\x77\160\x5f\x6c\157\x67\151\156")) {
            goto hG;
        }
        $user = get_user_by("\154\x6f\x67\151\156", $C6);
        if ($user) {
            goto By;
        }
        $user = get_user_by("\x65\155\141\151\x6c", $C6);
        By:
        if (!($user && $this->is_administrator_user($user))) {
            goto os;
        }
        if (!wp_check_password($lK, $user->data->user_pass, $user->ID)) {
            goto ci;
        }
        return $user;
        ci:
        os:
        hG:
        goto Mi;
        Vx:
        $user = get_user_by("\154\157\147\151\x6e", $C6);
        if ($user) {
            goto O2;
        }
        $user = get_user_by("\145\155\x61\x69\x6c", $C6);
        O2:
        if (!($user && wp_check_password($lK, $user->data->user_pass, $user->ID))) {
            goto Mk;
        }
        return $user;
        Mk:
        Mi:
    }
    function mo_firebase_auth_success_message()
    {
        $y3 = "\x65\162\162\x6f\x72";
        $rC = get_option("\155\157\137\x66\151\162\145\142\x61\x73\x65\137\x61\165\164\150\137\x6d\145\163\x73\x61\x67\x65");
        echo "\74\x62\x72\x3e\x3c\x64\x69\x76\x20\143\x6c\x61\x73\163\75\x27" . $y3 . "\47\76\x20\x3c\160\76" . $rC . "\74\57\160\x3e\x3c\x2f\x64\x69\x76\x3e";
    }
    function mo_firebase_auth_error_message()
    {
        $y3 = "\165\160\x64\141\x74\145\144";
        $rC = get_option("\155\x6f\x5f\146\151\162\145\142\x61\163\145\137\141\x75\x74\150\x5f\155\145\x73\x73\x61\147\x65");
        echo "\x3c\142\x72\x3e\x3c\x64\151\x76\x20\143\154\x61\x73\163\75\47" . $y3 . "\x27\x3e\x3c\160\76" . $rC . "\74\x2f\x70\x3e\x3c\57\144\151\166\x3e";
    }
    function is_administrator_user($user)
    {
        $p_ = $user->roles;
        if (!is_null($p_) && in_array("\x61\144\x6d\151\x6e\x69\x73\x74\x72\141\x74\157\162", $p_)) {
            goto II;
        }
        return false;
        goto LQ;
        II:
        return true;
        LQ:
    }
    private function mo_firebase_auth_show_success_message()
    {
        remove_action("\141\144\155\x69\156\137\156\157\x74\x69\x63\x65\163", array($this, "\155\157\137\146\x69\162\x65\142\141\x73\x65\x5f\141\165\164\x68\137\163\x75\x63\x63\x65\x73\x73\137\155\145\163\163\x61\x67\145"));
        add_action("\141\x64\x6d\151\x6e\137\156\157\164\x69\x63\145\163", array($this, "\x6d\157\137\146\151\x72\145\142\x61\x73\145\x5f\141\x75\164\150\x5f\145\162\x72\157\162\x5f\155\x65\163\163\141\x67\x65"));
    }
    private function mo_firebase_auth_show_error_message()
    {
        remove_action("\x61\144\155\151\x6e\x5f\x6e\x6f\x74\151\143\x65\163", array($this, "\x6d\157\137\146\x69\162\x65\x62\141\163\145\x5f\x61\x75\x74\150\137\145\x72\x72\x6f\162\137\155\145\163\x73\x61\x67\145"));
        add_action("\141\144\x6d\151\x6e\x5f\x6e\x6f\164\x69\x63\x65\163", array($this, "\155\157\x5f\x66\x69\x72\145\142\141\163\x65\137\x61\165\x74\x68\x5f\x73\165\x63\143\x65\163\x73\x5f\x6d\x65\x73\x73\141\x67\x65"));
    }
    function mo_firebase_auth_feedback_request()
    {
        mo_firebase_auth_display_feedback_form();
    }
    private function mo_firebase_authentication_check_empty_or_null($z3)
    {
        if (!(!isset($z3) || empty($z3))) {
            goto g6;
        }
        return true;
        g6:
        return false;
    }
    function mo_firebase_auth_save_settings()
    {
        if (!isset($_POST["\157\x70\x74\151\157\x6e"])) {
            goto G4;
        }
        if (!(isset($_POST["\x6d\x6f\137\x66\x69\162\x65\142\x61\163\145\x5f\x61\x74\164\162\x5f\155\141\x70\x70\x69\x6e\x67\137\146\x69\145\x6c\x64"]) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST["\x6d\x6f\137\146\151\162\145\142\141\x73\145\137\141\x74\164\x72\x5f\155\141\x70\x70\151\x6e\147\x5f\146\151\x65\154\144"])), "\155\157\x5f\x66\151\x72\145\142\x61\163\145\x5f\x61\x74\164\x72\x5f\x6d\141\160\x70\x69\x6e\x67\x5f\x66\x6f\162\x6d") && sanitize_text_field(wp_unslash($_POST["\157\x70\164\151\x6f\156"])) == "\x6d\x6f\137\x66\151\x72\145\142\141\163\145\137\141\164\x74\162\x5f\x6d\x61\x70\160\151\156\x67")) {
            goto Lb;
        }
        $ga = array();
        $ga["\x6d\x6f\137\146\151\162\x65\x62\x61\163\145\x5f\x75\x73\x65\162\156\141\x6d\x65\137\141\x74\x74\x72"] = $_POST["\165\163\x65\x72\x6e\141\155\x65\137\141\x74\164\162"];
        $ga["\155\157\137\x66\151\162\145\x62\141\163\145\137\x65\x6d\141\x69\x6c\x5f\x61\164\x74\x72"] = $_POST["\x65\155\141\x69\x6c\137\x61\164\x74\162"];
        update_option("\155\x6f\137\146\151\x72\145\x62\141\x73\145\x5f\x6d\141\160\160\145\144\137\141\164\x74\162", json_encode($ga));
        Lb:
        if (!(sanitize_text_field(wp_unslash($_POST["\x6f\160\x74\x69\157\156"])) == "\x6d\157\137\146\x69\x72\x65\142\x61\163\145\x5f\141\165\164\x68\x65\x6e\164\x69\143\141\164\x69\x6f\156\x5f\x63\x68\141\156\x67\x65\x5f\x65\155\141\151\154")) {
            goto HN;
        }
        update_option("\155\x6f\x5f\x66\151\162\x65\142\141\x73\x65\x5f\141\165\164\x68\x65\156\x74\x69\143\x61\164\151\x6f\x6e\x5f\x76\x65\x72\x69\x66\171\137\x63\165\x73\164\x6f\155\x65\162", '');
        update_option("\x6d\157\137\x66\151\x72\145\142\141\163\x65\137\141\x75\x74\x68\145\156\x74\x69\x63\141\164\x69\157\156\137\162\x65\x67\151\163\x74\x72\141\164\x69\x6f\156\x5f\x73\164\141\164\165\x73", '');
        update_option("\x6d\x6f\137\x66\x69\x72\145\142\141\x73\x65\x5f\141\x75\x74\150\145\x6e\164\151\x63\141\164\151\x6f\x6e\137\x6e\145\167\137\x72\145\x67\151\x73\x74\x72\x61\164\x69\x6f\156", "\x74\162\x75\145");
        HN:
        if (!(sanitize_text_field(wp_unslash($_POST["\157\x70\x74\x69\157\156"])) == "\x63\150\x61\x6e\x67\x65\137\155\151\x6e\151\x6f\x72\141\156\147\x65")) {
            goto Au;
        }
        require_once plugin_dir_path(__FILE__) . "\x69\x6e\x63\x6c\x75\x64\145\163\x2f\x63\x6c\x61\163\x73\55\x66\x69\x72\x65\142\141\163\x65\x2d\141\165\164\150\x65\156\x74\x69\143\141\164\x69\157\x6e\55\x64\145\141\143\164\x69\166\x61\164\x6f\162\56\x70\150\x70";
        MO_Firebase_Authentication_Deactivator::deactivate();
        return;
        Au:
        if (!(sanitize_text_field(wp_unslash($_POST["\157\160\164\151\157\156"])) == "\155\157\x5f\x66\151\x72\x65\x62\141\163\145\137\141\x75\x74\150\x65\x6e\x74\x69\x63\141\x74\151\157\x6e\137\162\145\147\151\x73\x74\145\162\137\143\165\163\x74\x6f\x6d\145\162")) {
            goto yL;
        }
        $Sl = '';
        $Sy = '';
        $lK = '';
        $VU = '';
        $Dt = '';
        $KH = '';
        $bQ = '';
        if ($this->mo_firebase_authentication_check_empty_or_null($_POST["\x65\x6d\x61\x69\154"]) || $this->mo_firebase_authentication_check_empty_or_null($_POST["\x70\x61\163\x73\x77\157\x72\144"]) || $this->mo_firebase_authentication_check_empty_or_null($_POST["\143\157\156\146\151\x72\x6d\120\141\163\x73\x77\157\x72\x64"])) {
            goto iC;
        }
        if (strlen($_POST["\x70\141\x73\x73\167\x6f\x72\144"]) < 8 || strlen($_POST["\143\x6f\156\x66\151\x72\x6d\x50\141\163\x73\x77\157\x72\x64"]) < 8) {
            goto ce;
        }
        $Sl = sanitize_email($_POST["\x65\x6d\x61\x69\x6c"]);
        $Sy = stripslashes($_POST["\160\150\157\x6e\x65"]);
        $lK = stripslashes($_POST["\x70\x61\x73\x73\167\157\162\144"]);
        $VU = stripslashes($_POST["\143\157\x6e\x66\x69\162\155\120\x61\163\163\167\x6f\x72\144"]);
        $Dt = stripslashes($_POST["\x66\156\141\155\x65"]);
        $KH = stripslashes($_POST["\x6c\156\x61\x6d\145"]);
        $bQ = stripslashes($_POST["\143\157\x6d\x70\141\x6e\x79"]);
        goto UL;
        ce:
        update_option("\x6d\157\137\146\x69\x72\145\142\x61\163\x65\x5f\x61\165\x74\150\137\x6d\145\163\x73\141\147\x65", "\x43\150\157\157\x73\x65\40\x61\x20\x70\141\x73\x73\167\157\162\x64\40\167\151\x74\150\x20\x6d\x69\x6e\x69\x6d\x75\x6d\40\x6c\145\x6e\x67\x74\x68\40\x38\x2e");
        $this->mo_firebase_auth_show_error_message();
        return;
        UL:
        goto Gj;
        iC:
        update_option("\155\x6f\137\x66\x69\x72\x65\x62\141\163\145\x5f\141\x75\x74\x68\137\155\x65\x73\x73\141\147\145", "\x41\154\154\x20\164\150\x65\x20\146\x69\x65\x6c\x64\163\x20\x61\162\x65\x20\162\145\161\165\x69\x72\x65\x64\56\x20\x50\x6c\145\141\x73\x65\x20\145\156\164\x65\162\x20\x76\x61\x6c\151\x64\x20\x65\156\164\x72\x69\x65\163\56");
        $this->mo_firebase_auth_show_error_message();
        return;
        Gj:
        update_option("\x6d\x6f\137\146\151\162\145\x62\x61\163\145\x5f\141\x75\164\150\x65\x6e\x74\151\143\141\164\151\x6f\156\137\141\144\155\x69\156\x5f\x65\x6d\x61\x69\154", $Sl);
        update_option("\x6d\157\137\x66\151\x72\145\142\x61\163\145\137\141\165\x74\150\145\156\x74\x69\143\141\164\x69\x6f\156\137\141\144\155\151\156\137\160\x68\x6f\x6e\x65", $Sy);
        update_option("\155\x6f\137\x66\151\162\145\142\x61\163\145\137\141\165\x74\150\145\156\x74\151\x63\141\x74\x69\x6f\156\137\x61\x64\x6d\151\156\137\x66\156\141\155\145", $Dt);
        update_option("\155\157\x5f\146\x69\x72\145\x62\x61\163\145\x5f\141\x75\x74\150\145\156\x74\x69\x63\x61\x74\x69\x6f\156\137\x61\144\x6d\151\156\137\154\x6e\x61\x6d\x65", $KH);
        update_option("\155\x6f\x5f\146\x69\x72\x65\x62\x61\163\x65\137\141\165\164\x68\145\156\x74\151\143\141\x74\151\157\x6e\x5f\x61\144\155\x69\x6e\x5f\143\x6f\155\x70\x61\156\171", $bQ);
        if (strcmp($lK, $VU) == 0) {
            goto Q0;
        }
        update_option("\155\x6f\137\x66\x69\x72\145\142\141\163\x65\x5f\x61\165\164\x68\137\155\145\163\x73\x61\147\145", "\120\x61\x73\163\167\x6f\162\144\163\x20\144\x6f\40\156\x6f\x74\40\x6d\x61\x74\143\150\x2e");
        delete_option("\155\x6f\137\146\151\x72\145\142\141\x73\x65\137\141\x75\x74\150\145\156\164\151\x63\141\x74\151\x6f\156\x5f\x76\x65\162\151\146\171\x5f\x63\x75\x73\164\x6f\x6d\x65\162");
        $this->mo_firebase_auth_show_error_message();
        goto i3;
        Q0:
        update_option("\x70\x61\x73\x73\x77\x6f\x72\x64", $lK);
        $fr = new MO_Firebase_Customer();
        $Sl = get_option("\x6d\x6f\x5f\146\x69\162\145\142\x61\163\145\x5f\141\165\x74\x68\145\156\x74\151\x63\x61\164\151\x6f\x6e\137\x61\144\155\x69\156\x5f\145\x6d\141\x69\x6c");
        $DF = json_decode($fr->check_customer(), true);
        if (strcasecmp($DF["\x73\164\141\x74\165\x73"], "\x43\x55\123\124\117\x4d\x45\122\x5f\x4e\x4f\x54\x5f\x46\x4f\125\x4e\104") == 0) {
            goto E4;
        }
        if (strcasecmp($DF["\163\164\141\x74\x75\163"], "\x53\x55\x43\x43\105\123\123") == 0) {
            goto sf;
        }
        update_option("\155\x6f\x5f\146\x69\162\x65\x62\141\163\145\137\141\x75\164\150\137\x6d\145\x73\163\141\x67\145", $DF["\163\164\x61\164\x75\x73"]);
        goto aE;
        E4:
        $UT = json_decode($fr->create_customer(), true);
        if (!(strcasecmp($UT["\x73\164\x61\x74\x75\x73"], "\123\x55\x43\x43\x45\x53\x53") != 0)) {
            goto JN;
        }
        update_option("\x6d\157\x5f\x66\151\162\145\142\x61\x73\145\137\x61\x75\164\150\x5f\x6d\x65\x73\x73\141\147\145", "\x46\x61\151\154\145\x64\x20\164\157\40\143\162\x65\141\164\x65\x20\143\165\x73\x74\x6f\155\x65\162\x2e\40\124\x72\x79\40\141\147\x61\x69\156\56");
        JN:
        $this->mo_firebase_auth_show_success_message();
        goto aE;
        sf:
        update_option("\155\157\137\x66\x69\162\145\142\x61\x73\x65\137\141\165\164\150\137\155\145\163\163\x61\x67\x65", "\x41\143\x63\157\165\x6e\x74\40\x61\x6c\x72\x65\x61\144\171\40\x65\x78\x69\x73\164\56\40\120\x6c\x65\x61\x73\145\x20\114\157\x67\151\156\56");
        aE:
        $this->mo_firebase_auth_show_success_message();
        i3:
        yL:
        if (!(sanitize_text_field(wp_unslash($_POST["\157\x70\164\x69\157\156"])) == "\155\x6f\137\x66\x69\162\145\142\x61\x73\145\137\141\165\164\x68\145\x6e\x74\151\x63\x61\164\151\x6f\156\137\x67\157\164\x6f\x5f\x6c\157\x67\151\x6e")) {
            goto hW;
        }
        delete_option("\x6d\157\x5f\146\151\x72\x65\142\141\163\145\x5f\141\x75\164\x68\145\x6e\x74\x69\143\x61\164\x69\x6f\156\137\x6e\x65\x77\x5f\x72\x65\x67\151\163\164\x72\141\x74\151\157\x6e");
        $this->mo_firebase_authentication_get_current_customer();
        hW:
        if (sanitize_text_field(wp_unslash($_POST["\x6f\x70\164\151\x6f\156"])) == "\155\157\x5f\145\156\x61\142\x6c\x65\137\x66\151\162\x65\142\141\163\x65\137\141\165\164\x68" && wp_verify_nonce($_REQUEST["\x6d\x6f\137\x66\151\162\x65\x62\x61\x73\x65\x5f\141\165\164\150\137\145\x6e\x61\x62\x6c\145\137\146\151\145\x6c\144"], "\155\x6f\137\146\151\162\x65\142\141\163\145\x5f\141\165\x74\150\x5f\x65\156\141\x62\x6c\x65\137\x66\x6f\162\x6d")) {
            goto iN;
        }
        if (sanitize_text_field(wp_unslash($_POST["\157\x70\x74\151\157\156"])) == "\x6d\157\137\x66\151\x72\145\x62\x61\163\145\137\141\x75\x74\150\145\x6e\x74\151\x63\x61\164\x69\157\x6e\x5f\x73\151\147\156\137\x69\x6e\x5f\x6f\160\x74\x69\x6f\x6e" && wp_verify_nonce($_REQUEST["\155\x6f\137\146\151\162\x65\x62\141\163\x65\137\141\165\x74\x68\137\x73\x69\x67\x6e\x5f\151\x6e\x5f\157\160\164\151\x6f\x6e\x5f\x66\151\x65\154\x64"], "\155\157\x5f\146\151\x72\x65\142\141\163\145\x5f\141\x75\164\150\x5f\163\x69\147\156\x5f\x69\x6e\137\x6f\160\164\151\x6f\156\137\x66\157\x72\x6d")) {
            goto id;
        }
        if (sanitize_text_field(wp_unslash($_POST["\157\160\164\x69\157\156"])) == "\x6d\x6f\137\146\x69\162\x65\142\141\x73\x65\x5f\141\x75\164\150\137\141\x75\164\x6f\163\171\x6e\x63" && wp_verify_nonce($_REQUEST["\155\157\137\x66\x69\162\x65\x62\x61\163\145\x5f\x61\165\164\150\x5f\x65\x6e\141\x62\x6c\x65\x5f\141\x75\164\x6f\x73\x79\156\x63\x5f\x66\x69\145\x6c\x64"], "\x6d\x6f\137\146\151\x72\x65\x62\141\x73\x65\x5f\x61\165\x74\150\137\x65\156\x61\142\154\x65\x5f\x61\x75\x74\x6f\163\x79\156\x63\137\x66\x6f\x72\x6d")) {
            goto AS1;
        }
        if (sanitize_text_field(wp_unslash($_POST["\x6f\x70\x74\151\x6f\x6e"])) == "\155\x6f\137\x66\x69\x72\145\142\141\163\145\x5f\141\x75\164\x68\137\x69\156\x74\x65\147\162\x61\x74\x69\x6f\x6e") {
            goto Xc;
        }
        if (sanitize_text_field(wp_unslash($_POST["\x6f\160\x74\x69\x6f\x6e"])) == "\155\157\x5f\x66\151\x72\x65\x62\141\163\145\137\x61\165\164\150\x5f\160\x72\x6f\x76\151\144\145\162\x5f\x6d\145\164\x68\x6f\144") {
            goto l6;
        }
        if (isset($_POST["\157\x70\x74\x69\x6f\156"]) and $_POST["\x6f\x70\x74\151\x6f\x6e"] == "\x6d\x6f\137\146\151\162\145\142\x61\x73\x65\x5f\x61\x75\x74\x68\145\156\164\x69\143\x61\x74\151\x6f\156\x5f\x76\x65\162\x69\146\171\137\x6c\x69\x63\145\x6e\163\x65") {
            goto Rz;
        }
        if (sanitize_text_field(wp_unslash($_POST["\x6f\x70\164\151\157\156"])) == "\x6d\157\137\x66\151\x72\x65\x62\x61\x73\x65\137\x61\x75\x74\x68\137\143\157\156\164\x61\143\164\137\165\x73" && isset($_REQUEST["\x6d\157\137\x66\151\x72\145\x62\141\163\x65\x5f\x61\165\164\x68\137\x63\x6f\x6e\164\x61\x63\x74\x5f\165\163\x5f\146\x69\145\154\144"]) && wp_verify_nonce($_REQUEST["\155\x6f\x5f\146\x69\162\145\142\x61\163\x65\x5f\x61\x75\164\x68\x5f\x63\x6f\x6e\164\141\143\164\x5f\165\163\x5f\x66\x69\145\x6c\144"], "\155\x6f\137\x66\x69\162\x65\x62\x61\163\145\137\141\165\164\x68\137\143\x6f\156\x74\x61\143\x74\x5f\165\x73\x5f\146\157\162\x6d")) {
            goto cq;
        }
        if (sanitize_text_field(wp_unslash($_POST["\x6f\160\164\151\x6f\156"])) == "\x6d\x6f\137\x66\151\162\145\142\141\x73\x65\137\141\165\164\150\x65\x6e\164\151\143\141\164\151\x6f\x6e\137\166\x65\162\x69\146\171\137\x63\165\x73\x74\x6f\155\x65\x72") {
            goto BJ;
        }
        if (sanitize_text_field(wp_unslash($_POST["\157\160\x74\x69\x6f\156"])) == "\155\x6f\x5f\146\151\162\145\142\x61\163\x65\x5f\x61\165\164\150\137\163\153\151\x70\137\146\145\145\x64\x62\141\143\x6b") {
            goto f5;
        }
        if (!(sanitize_text_field(wp_unslash($_POST["\157\160\x74\x69\157\156"])) == "\x6d\x6f\x5f\146\x69\162\145\142\141\x73\x65\x5f\x61\165\x74\x68\137\x66\x65\x65\x64\x62\x61\x63\x6b" && isset($_REQUEST["\x6d\x6f\137\x66\151\x72\x65\142\x61\x73\145\137\x61\165\x74\x68\137\146\x65\145\144\x62\141\x63\x6b\137\x66\x69\x65\x6c\144"]) && wp_verify_nonce($_REQUEST["\x6d\157\137\146\x69\x72\145\x62\x61\x73\x65\137\x61\165\164\150\x5f\146\x65\x65\x64\142\x61\x63\x6b\137\x66\x69\x65\154\144"], "\x6d\x6f\137\146\151\162\145\142\141\x73\x65\137\141\165\164\x68\x5f\x66\x65\145\144\142\x61\143\153\x5f\146\x6f\162\x6d"))) {
            goto tN;
        }
        $user = wp_get_current_user();
        $rC = "\120\x6c\165\147\x69\x6e\x20\x44\145\x61\143\164\151\x76\x61\164\x65\144\72";
        $PP = array_key_exists("\x64\145\x61\x63\x74\x69\x76\x61\x74\x65\x5f\x72\x65\x61\163\x6f\156\x5f\162\x61\x64\151\157", $_POST) ? $_POST["\x64\x65\x61\x63\164\x69\166\141\x74\145\x5f\x72\145\141\163\x6f\x6e\137\x72\x61\144\x69\x6f"] : false;
        $zW = array_key_exists("\x71\165\x65\x72\x79\137\x66\145\145\144\x62\x61\x63\153", $_POST) ? $_POST["\x71\165\145\162\171\x5f\x66\x65\x65\x64\x62\141\143\x6b"] : false;
        if ($PP) {
            goto fD;
        }
        update_option("\x6d\157\137\146\151\162\145\142\x61\163\145\137\141\x75\164\x68\137\x6d\x65\x73\x73\x61\x67\x65", "\120\x6c\x65\141\163\x65\x20\123\145\x6c\x65\x63\164\x20\x6f\x6e\x65\x20\157\x66\40\164\x68\x65\x20\162\145\x61\x73\157\156\163\40\54\x69\x66\x20\171\157\165\162\x20\x72\145\141\x73\x6f\156\x20\x69\163\40\156\x6f\164\x20\155\x65\156\x74\151\x6f\x6e\x65\x64\x20\x70\154\145\141\163\145\40\x73\145\x6c\x65\143\x74\x20\117\x74\150\145\x72\40\x52\145\141\x73\157\x6e\x73");
        $this->mo_firebase_auth_show_error_message();
        goto Du;
        fD:
        $rC .= $PP;
        if (!isset($zW)) {
            goto FA;
        }
        $rC .= "\72" . $zW;
        FA:
        $Sl = $user->user_email;
        $VD = new MO_Firebase_Customer();
        $VS = json_decode($VD->mo_firebase_auth_send_email_alert($Sl, $rC, "\x46\x65\145\144\142\x61\143\x6b\72\40\x57\x6f\162\x64\120\x72\145\x73\163\40\x46\151\x72\145\142\x61\x73\x65\40\x41\x75\x74\x68\x65\x6e\x74\151\143\141\x74\x69\x6f\x6e"), true);
        deactivate_plugins(__FILE__);
        update_option("\x6d\157\137\146\151\x72\145\x62\x61\x73\145\137\141\x75\x74\x68\x5f\155\x65\163\x73\141\147\145", "\x54\x68\141\x6e\x6b\x20\171\x6f\x75\x20\x66\157\162\40\x74\150\145\40\146\145\145\x64\x62\x61\x63\153\56");
        $this->mo_firebase_auth_show_success_message();
        Du:
        tN:
        goto vL;
        f5:
        deactivate_plugins(__FILE__);
        update_option("\155\x6f\137\146\x69\x72\x65\x62\x61\163\145\137\141\x75\x74\x68\x5f\x6d\x65\163\x73\x61\x67\x65", "\120\x6c\165\147\151\x6e\40\x64\145\141\x63\x74\151\x76\141\x74\x65\144\x20\163\x75\x63\143\x65\163\163\x66\165\x6c\x6c\171");
        $this->mo_firebase_auth_show_success_message();
        vL:
        goto XM;
        BJ:
        $Sl = '';
        $lK = '';
        if ($this->mo_firebase_authentication_check_empty_or_null($_POST["\x65\155\141\151\x6c"]) || $this->mo_firebase_authentication_check_empty_or_null($_POST["\160\x61\163\163\x77\157\162\x64"])) {
            goto O3;
        }
        $Sl = sanitize_email($_POST["\145\x6d\141\151\154"]);
        $lK = stripslashes($_POST["\x70\141\x73\x73\167\157\162\144"]);
        goto iT;
        O3:
        update_option("\155\x6f\137\146\x69\x72\145\142\x61\163\145\137\141\x75\x74\150\x5f\155\145\x73\163\x61\147\x65", "\101\154\154\x20\x74\x68\145\x20\146\151\145\x6c\x64\163\40\141\x72\x65\40\162\x65\x71\165\151\162\x65\144\56\x20\120\154\145\141\x73\x65\x20\145\156\x74\145\x72\x20\166\141\x6c\151\x64\x20\145\156\164\162\151\145\x73\56");
        $this->mo_firebase_auth_show_error_message();
        return;
        iT:
        update_option("\155\157\x5f\x66\x69\162\x65\142\x61\163\145\137\x61\x75\164\x68\145\x6e\x74\x69\143\141\x74\x69\157\156\137\x61\x64\155\x69\156\x5f\145\155\x61\x69\154", $Sl);
        update_option("\x70\x61\163\x73\167\x6f\162\x64", $lK);
        $fr = new MO_Firebase_Customer();
        $DF = $fr->mo_firebase_auth_get_customer_key();
        $q4 = json_decode($DF, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            goto su;
        }
        update_option("\155\x6f\x5f\146\x69\x72\145\142\141\163\x65\x5f\x61\x75\164\x68\x5f\x6d\145\163\x73\141\147\145", "\111\156\166\141\x6c\151\144\x20\x75\163\x65\162\156\141\x6d\145\40\157\162\40\160\x61\163\163\x77\157\162\144\56\40\x50\x6c\x65\x61\163\145\x20\x74\162\x79\x20\141\147\x61\151\x6e\x2e");
        $this->mo_firebase_auth_show_error_message();
        goto oa;
        su:
        update_option("\x6d\157\137\146\x69\x72\x65\x62\x61\163\x65\137\x61\165\x74\150\x65\156\164\x69\143\x61\x74\x69\157\x6e\x5f\141\144\x6d\x69\x6e\137\x63\x75\163\164\157\x6d\145\162\x5f\x6b\145\x79", $q4["\151\144"]);
        update_option("\155\x6f\x5f\x66\x69\162\145\142\141\163\x65\x5f\x61\x75\164\x68\145\x6e\x74\x69\x63\x61\x74\151\157\156\x5f\141\144\x6d\x69\156\137\141\160\x69\137\153\x65\x79", $q4["\141\x70\151\x4b\145\x79"]);
        update_option("\x6d\x6f\137\146\151\162\145\x62\x61\163\145\137\x61\x75\x74\x68\145\156\x74\x69\143\x61\x74\x69\x6f\156\x5f\143\165\163\164\157\x6d\x65\162\x5f\164\157\153\145\x6e", $q4["\x74\x6f\x6b\145\156"]);
        if (!isset($q4["\x70\150\157\156\145"])) {
            goto kU;
        }
        update_option("\155\157\x5f\146\151\162\x65\x62\141\163\x65\x5f\x61\x75\164\x68\145\156\164\x69\x63\141\x74\151\x6f\x6e\137\141\144\x6d\x69\x6e\x5f\x70\150\x6f\x6e\145", $q4["\x70\150\x6f\156\x65"]);
        kU:
        delete_option("\160\x61\x73\163\x77\x6f\162\x64");
        update_option("\155\x6f\137\x66\x69\x72\x65\x62\x61\163\145\137\141\165\164\x68\137\155\x65\163\163\x61\147\x65", "\x43\x75\163\x74\x6f\155\x65\x72\x20\162\x65\x74\x72\x69\145\x76\145\x64\x20\x73\165\x63\143\x65\x73\163\146\165\154\154\x79");
        delete_option("\155\157\x5f\146\x69\162\x65\142\141\x73\x65\x5f\141\165\x74\x68\145\156\164\151\x63\141\x74\x69\157\x6e\x5f\166\x65\162\x69\146\171\x5f\x63\165\x73\164\157\x6d\x65\x72");
        $this->mo_firebase_auth_show_success_message();
        oa:
        XM:
        goto ob;
        cq:
        $Sl = isset($_POST["\155\x6f\x5f\x66\151\x72\x65\142\141\x73\145\x5f\141\x75\164\150\137\x63\x6f\x6e\x74\141\143\164\x5f\x75\163\137\x65\x6d\141\x69\154"]) ? sanitize_email($_POST["\x6d\157\x5f\x66\151\x72\145\x62\x61\x73\145\x5f\141\x75\164\x68\137\x63\x6f\156\x74\x61\x63\x74\x5f\x75\163\x5f\x65\x6d\141\151\x6c"]) : '';
        $Sy = "\53\x20" . preg_replace("\x2f\x5b\136\60\x2d\x39\135\57", '', $_POST["\x6d\157\x5f\146\151\162\x65\142\x61\163\145\137\x61\165\164\x68\x5f\143\x6f\156\x74\141\143\164\x5f\x75\163\137\x70\150\x6f\156\x65"]);
        $tk = isset($_POST["\x6d\157\x5f\x66\x69\162\x65\142\141\163\145\137\141\x75\164\x68\x5f\143\x6f\x6e\x74\141\x63\164\137\165\x73\x5f\x71\x75\145\x72\x79"]) ? sanitize_textarea_field($_POST["\x6d\x6f\x5f\146\151\x72\x65\x62\141\163\x65\x5f\x61\x75\164\150\x5f\143\x6f\156\x74\x61\x63\164\x5f\x75\163\x5f\x71\x75\x65\162\171"]) : '';
        if ($this->mo_firebase_authentication_check_empty_or_null($Sl) || $this->mo_firebase_authentication_check_empty_or_null($tk)) {
            goto BZ;
        }
        $VD = new MO_Firebase_Customer();
        $VS = $VD->mo_firebase_auth_contact_us($Sl, $Sy, $tk);
        if ($VS == false) {
            goto XZ;
        }
        echo "\x3c\x62\x72\x3e\74\142\40\163\x74\171\154\145\x3d\143\157\154\157\x72\72\147\x72\145\x65\156\76\124\150\x61\156\153\x73\40\x66\x6f\x72\40\x67\x65\164\164\x69\x6e\x67\40\151\156\40\x74\x6f\165\x63\150\x21\x20\127\x65\x20\163\x68\141\x6c\154\x20\x67\x65\164\x20\142\141\143\x6b\x20\164\157\40\171\157\165\x20\x73\150\157\162\164\154\x79\56\74\57\142\76";
        goto eU;
        XZ:
        echo "\x3c\x62\162\76\74\142\40\163\x74\x79\x6c\145\x3d\x63\157\154\x6f\x72\72\x72\145\144\x3e\131\x6f\165\162\40\161\x75\x65\162\171\40\x63\157\x75\x6c\144\40\156\x6f\x74\40\142\145\40\163\165\x62\155\x69\x74\x74\x65\x64\56\x20\120\154\x65\141\x73\x65\x20\x74\x72\x79\40\x61\147\x61\x69\156\56\74\x2f\x62\76";
        eU:
        goto qB;
        BZ:
        echo "\74\x62\x72\76\74\x62\40\x73\x74\x79\x6c\x65\75\143\157\x6c\157\162\72\x72\145\x64\x3e\x50\x6c\145\x61\x73\x65\x20\146\x69\154\x6c\40\165\x70\40\105\x6d\141\x69\x6c\x20\141\156\144\x20\x51\165\145\x72\x79\40\146\x69\x65\154\144\163\40\x74\157\40\x73\165\142\155\x69\164\x20\171\x6f\x75\x72\x20\x71\165\x65\x72\171\56\x3c\57\142\76";
        qB:
        ob:
        goto Oq;
        Rz:
        if (!(!isset($_POST["\x6d\157\x5f\x66\151\162\x65\x62\141\x73\x65\137\141\165\x74\x68\145\156\x74\x69\x63\141\x74\151\x6f\x6e\x5f\x6c\x69\x63\x65\x6e\163\x65\x5f\153\145\171"]) || empty($_POST["\155\157\x5f\146\151\162\145\x62\x61\x73\145\x5f\x61\x75\x74\150\x65\156\164\x69\x63\141\164\x69\157\x6e\137\154\151\143\145\x6e\163\x65\x5f\153\x65\x79"]))) {
            goto TY;
        }
        update_option("\155\157\137\x66\151\162\x65\142\x61\163\145\x5f\x61\x75\x74\x68\137\155\145\163\x73\141\147\x65", "\120\x6c\x65\x61\x73\x65\x20\x65\x6e\x74\145\162\40\x76\x61\x6c\x69\144\40\154\151\x63\x65\x6e\163\x65\40\153\x65\171\x2e");
        $this->mo_firebase_auth_show_error_message();
        return;
        TY:
        $pW = trim($_POST["\155\157\137\146\151\x72\145\x62\x61\163\x65\x5f\141\x75\164\150\x65\156\x74\151\x63\x61\164\151\157\156\x5f\x6c\x69\143\x65\156\163\x65\x5f\x6b\145\171"]);
        $fr = new MO_Firebase_Customer();
        $DF = json_decode($fr->mo_firebase_authentication_check_customer_ln(), true);
        $DF["\163\x74\141\x74\165\x73"] = "\x53\125\x43\x43\105\x53\x53";
        if (strcasecmp($DF["\x73\164\x61\164\165\163"], "\123\x55\x43\x43\x45\x53\x53") == 0) {
            goto F3;
        }
        update_option("\x6d\x6f\137\x66\x69\162\145\x62\x61\163\x65\137\x61\165\164\150\137\155\x65\163\163\141\147\145", "\x49\156\x76\141\x6c\151\144\x20\154\x69\143\145\156\x73\145\x2e\40\x50\x6c\x65\141\163\x65\40\164\162\x79\40\141\147\x61\x69\156\x2e");
        $this->mo_firebase_auth_show_error_message();
        goto aN;
        F3:
        $DF = json_decode($fr->mo_firebase_auth_XfsZkodsfhHJ($pW), true);
        if (strcasecmp($DF["\x73\164\141\x74\165\163"], "\123\125\x43\103\x45\123\x53") == 0) {
            goto m3;
        }
        if (strcasecmp($DF["\x73\x74\x61\x74\x75\x73"], "\x46\x41\x49\x4c\x45\x44") == 0) {
            goto DB;
        }
        update_option("\x6d\x6f\x5f\146\151\162\x65\x62\x61\163\x65\x5f\141\x75\164\x68\137\155\145\x73\x73\x61\x67\145", "\x41\x6e\40\x65\x72\162\x6f\x72\x20\x6f\x63\x63\165\162\145\144\40\x77\150\151\x6c\145\x20\x70\x72\157\143\145\163\163\x69\156\147\x20\x79\157\x75\162\x20\162\x65\x71\165\x65\x73\x74\56\40\120\154\x65\x61\x73\145\40\x54\162\x79\40\x61\147\x61\x69\x6e\x2e");
        $this->mo_firebase_auth_show_error_message();
        goto ue;
        DB:
        update_option("\x6d\x6f\x5f\x66\x69\162\x65\142\x61\x73\145\x5f\141\x75\164\x68\137\x6d\x65\163\163\x61\x67\x65", "\x59\157\x75\40\x68\141\x76\x65\x20\145\x6e\164\x65\162\145\144\40\x61\x6e\40\x69\156\166\x61\154\151\x64\x20\x6c\151\x63\x65\156\x73\x65\40\153\x65\x79\x2e\x20\x50\x6c\145\141\163\145\40\x65\x6e\x74\145\162\40\x61\40\x76\x61\x6c\151\x64\40\x6c\151\143\145\156\x73\x65\40\x6b\x65\x79\56");
        $this->mo_firebase_auth_show_error_message();
        ue:
        goto Wb;
        m3:
        update_option("\155\x6f\137\x66\151\x72\x65\x62\x61\163\145\x5f\x61\165\x74\150\x65\x6e\x74\151\x63\141\164\151\x6f\156\137\154\153", mo_firebase_authentication_encrypt($pW));
        update_option("\155\157\137\146\151\x72\145\x62\x61\x73\145\x5f\141\165\x74\150\145\156\x74\x69\143\x61\x74\151\157\156\x5f\154\166", mo_firebase_authentication_encrypt("\x74\x72\x75\x65"));
        update_option("\155\157\x5f\x66\151\x72\x65\142\x61\x73\145\x5f\141\165\164\150\x5f\x6d\145\x73\x73\141\147\145", "\131\x6f\x75\x72\40\154\x69\143\x65\156\x73\145\40\x69\163\40\x76\x65\162\x69\x66\151\145\x64\56\40\131\157\x75\40\x63\x61\x6e\x20\x6e\x6f\x77\x20\163\145\164\165\160\x20\x74\x68\x65\40\160\154\x75\x67\151\x6e\56");
        $this->mo_firebase_auth_show_success_message();
        Wb:
        aN:
        Oq:
        goto hy;
        l6:
        update_option("\x6d\157\x5f\x66\x69\162\145\142\x61\163\x65\x5f\x6f\141\165\x74\150\137\x67\157\x6f\147\x6c\x65\x5f\160\162\157\x76\x69\144\x65\x72\137\154\157\x67\x69\156\x5f\155\x65\x74\x68\x6f\x64", isset($_POST["\147\157\x6f\147\154\145\x5f\160\x72\x6f\x76\x69\x64\x65\x72\137\x6d\145\x74\150\x6f\144"]) ? (int) filter_var($_POST["\147\x6f\157\147\x6c\x65\x5f\x70\162\157\166\151\x64\145\162\x5f\155\x65\x74\150\157\x64"], FILTER_SANITIZE_NUMBER_INT) : 0);
        update_option("\x6d\157\137\146\151\162\145\x62\141\x73\x65\137\x6f\x61\165\164\150\137\x66\141\143\x65\142\157\x6f\153\x5f\160\x72\x6f\166\x69\144\145\x72\137\x6c\x6f\147\x69\156\x5f\155\x65\164\x68\157\144", isset($_POST["\x66\x61\x63\145\142\x6f\x6f\153\137\160\162\x6f\x76\151\144\x65\x72\137\155\x65\x74\150\157\x64"]) ? (int) filter_var($_POST["\x66\x61\143\145\x62\x6f\x6f\x6b\x5f\x70\x72\157\x76\x69\144\145\x72\137\x6d\x65\164\150\x6f\144"], FILTER_SANITIZE_NUMBER_INT) : 0);
        update_option("\x6d\x6f\137\146\151\162\x65\142\141\x73\145\x5f\157\x61\x75\164\150\137\147\x69\x74\x68\165\142\137\160\162\157\166\151\x64\x65\x72\137\154\x6f\x67\151\x6e\x5f\155\x65\164\x68\x6f\x64", isset($_POST["\147\151\x74\x68\165\x62\137\160\x72\x6f\166\151\144\145\162\137\155\x65\164\x68\157\144"]) ? (int) filter_var($_POST["\x67\151\x74\150\165\142\x5f\x70\162\157\166\151\144\x65\x72\137\x6d\145\164\150\157\x64"], FILTER_SANITIZE_NUMBER_INT) : 0);
        update_option("\155\x6f\x5f\146\x69\162\145\x62\141\163\x65\137\x6f\141\165\164\150\137\x74\167\x69\164\x74\x65\162\x5f\x70\x72\157\166\x69\x64\x65\162\137\154\157\x67\151\x6e\137\x6d\145\x74\x68\x6f\x64", isset($_POST["\x74\167\151\x74\x74\x65\162\x5f\160\162\x6f\166\x69\144\x65\x72\x5f\x6d\145\x74\150\x6f\x64"]) ? (int) filter_var($_POST["\164\x77\x69\x74\x74\x65\162\137\x70\162\x6f\x76\x69\144\x65\x72\x5f\x6d\145\164\150\157\144"], FILTER_SANITIZE_NUMBER_INT) : 0);
        update_option("\x6d\x6f\137\146\x69\162\x65\x62\141\163\145\137\157\x61\x75\164\150\x5f\x6d\x69\143\x72\x6f\163\157\146\164\137\x70\x72\x6f\x76\151\x64\x65\x72\x5f\x6c\x6f\147\x69\156\x5f\155\145\x74\150\x6f\144", isset($_POST["\x6d\151\x63\x72\x6f\x73\157\146\164\137\160\162\x6f\166\x69\144\145\x72\137\x6d\145\164\150\157\144"]) ? (int) filter_var($_POST["\155\151\x63\162\x6f\x73\157\146\164\x5f\160\162\157\166\151\144\x65\162\137\x6d\x65\x74\150\157\144"], FILTER_SANITIZE_NUMBER_INT) : 0);
        update_option("\155\x6f\137\x66\x69\x72\145\142\x61\163\145\137\x6f\x61\x75\164\x68\137\x79\141\150\x6f\x6f\137\160\162\157\166\x69\144\x65\162\137\154\x6f\147\151\x6e\137\x6d\145\164\x68\157\x64", isset($_POST["\171\x61\150\157\157\137\160\x72\x6f\x76\151\144\145\x72\x5f\155\x65\164\x68\x6f\x64"]) ? (int) filter_var($_POST["\171\141\x68\x6f\157\x5f\160\x72\157\166\x69\144\x65\162\x5f\x6d\145\x74\x68\x6f\144"], FILTER_SANITIZE_NUMBER_INT) : 0);
        update_option("\155\x6f\x5f\146\151\162\x65\x62\141\x73\145\137\157\x61\x75\164\x68\x5f\141\160\160\154\x65\137\x70\162\157\x76\x69\x64\x65\162\x5f\x6c\x6f\x67\151\156\x5f\x6d\145\164\150\x6f\144", isset($_POST["\141\160\x70\x6c\x65\137\x70\162\157\x76\x69\x64\x65\162\x5f\155\x65\164\x68\157\144"]) ? (int) filter_var($_POST["\x61\160\160\x6c\x65\x5f\x70\x72\157\x76\151\144\145\162\137\x6d\x65\164\150\157\144"], FILTER_SANITIZE_NUMBER_INT) : 0);
        update_option("\155\157\x5f\x66\x69\162\x65\142\x61\x73\x65\x5f\141\165\x74\x68\137\x73\x68\x6f\x77\x5f\x6f\156\x5f\x6c\157\147\x69\156\x5f\x70\141\147\145", isset($_POST["\x6d\x6f\x5f\146\x69\x72\145\x62\141\x73\x65\137\141\x75\164\150\137\x73\x68\157\167\137\157\156\137\154\157\147\151\156\x5f\x70\141\147\x65"]) ? (int) filter_var($_POST["\155\157\137\x66\x69\162\x65\x62\x61\x73\x65\137\141\x75\164\x68\137\x73\x68\157\167\x5f\x6f\156\137\x6c\x6f\147\x69\x6e\x5f\x70\x61\x67\x65"], FILTER_SANITIZE_NUMBER_INT) : 0);
        hy:
        goto rd;
        Xc:
        update_option("\x6d\157\x5f\x66\x69\162\145\x62\x61\x73\x65\137\141\165\164\x68\x5f\x77\x6f\157\143\x6f\x6d\155\145\x72\x63\x65\x5f\x69\156\x74\151\x67\162\x61\x74\x69\x6f\156", isset($_POST["\x6d\157\x5f\146\151\162\x65\x62\141\x73\145\137\141\x75\x74\x68\137\167\x6f\x6f\x63\157\x6d\x6d\x65\162\x63\145\x5f\151\x6e\164\151\147\162\141\x74\151\x6f\x6e"]) ? (int) filter_var($_POST["\x6d\x6f\x5f\x66\151\162\x65\142\x61\x73\x65\x5f\141\x75\164\150\x5f\x77\157\x6f\x63\x6f\x6d\155\145\x72\x63\145\x5f\151\x6e\x74\x69\x67\162\x61\164\x69\x6f\156"], FILTER_SANITIZE_NUMBER_INT) : 0);
        update_option("\155\x6f\x5f\x66\x69\x72\145\x62\x61\x73\145\137\141\165\164\157\137\x72\x65\x67\x69\x73\x74\x65\x72\x5f\165\163\x65\x72\137\x69\x6e\x5f\146\151\162\x65\x62\141\163\x65", isset($_POST["\x6d\157\x5f\146\x69\162\145\142\x61\163\x65\137\x61\x75\164\x6f\137\x72\145\x67\151\x73\164\145\x72\x5f\x75\163\145\x72\137\x69\x6e\137\146\x69\162\145\142\x61\163\145"]) ? $_POST["\155\x6f\x5f\x66\x69\x72\145\x62\141\x73\x65\x5f\x61\165\164\x6f\137\x72\145\147\x69\163\x74\x65\162\137\x75\163\145\x72\x5f\151\x6e\137\146\151\x72\145\142\x61\x73\x65"] : 0);
        update_option("\155\157\137\x66\x69\162\145\x62\141\x73\145\137\x61\x75\164\150\x5f\142\165\144\144\171\160\x72\x65\x73\163\x5f\x69\x6e\x74\x69\147\162\141\164\x69\157\x6e", isset($_POST["\155\x6f\137\146\151\x72\x65\142\141\163\x65\137\141\165\x74\x68\137\x62\165\144\x64\171\x70\162\145\x73\x73\137\x69\156\x74\x69\147\162\x61\164\151\157\x6e"]) ? (int) filter_var($_POST["\x6d\x6f\137\x66\x69\x72\x65\142\141\x73\145\137\x61\x75\x74\150\x5f\x62\x75\144\x64\x79\x70\162\x65\163\163\x5f\151\156\164\x69\147\162\141\164\x69\x6f\156"], FILTER_SANITIZE_NUMBER_INT) : 0);
        rd:
        goto vl;
        AS1:
        update_option("\x6d\157\x5f\145\156\x61\142\x6c\145\137\146\x69\162\145\142\x61\x73\x65\137\141\165\164\157\137\162\x65\147\151\163\164\145\162", isset($_POST["\155\x6f\137\x65\x6e\x61\x62\x6c\x65\x5f\x66\x69\162\145\x62\x61\x73\x65\137\x61\165\x74\x6f\137\x72\145\147\151\163\x74\145\162"]) ? (int) filter_var($_POST["\155\157\x5f\x65\x6e\141\x62\x6c\x65\137\146\x69\x72\145\142\141\163\x65\137\x61\x75\164\x6f\137\x72\145\x67\x69\163\x74\x65\x72"], FILTER_SANITIZE_NUMBER_INT) : 0);
        vl:
        goto Iz;
        id:
        $Tp = isset($_POST["\x63\x75\163\164\x6f\155\x5f\141\146\164\x65\162\137\x6c\157\x67\x69\156\137\x75\x72\x6c"]) ? sanitize_text_field(wp_unslash($_POST["\143\165\163\x74\x6f\155\137\141\x66\164\x65\162\x5f\x6c\157\x67\151\156\137\165\x72\x6c"])) : '';
        $B_ = isset($_POST["\x63\165\x73\164\157\155\137\141\x66\164\x65\x72\137\x6c\x6f\x67\x6f\x75\x74\137\x75\x72\154"]) ? sanitize_text_field(wp_unslash($_POST["\x63\x75\x73\x74\157\x6d\x5f\x61\146\164\x65\x72\x5f\x6c\157\x67\157\165\164\137\x75\162\x6c"])) : '';
        update_option("\x6d\x6f\137\146\x69\x72\145\x62\141\x73\145\137\x61\x75\x74\x68\x5f\x63\x75\163\x74\x6f\155\137\141\146\x74\145\162\x5f\x6c\157\x67\151\156\137\165\x72\x6c", $Tp);
        update_option("\155\157\137\x66\151\162\x65\142\141\x73\x65\x5f\141\x75\164\150\137\143\165\x73\x74\x6f\x6d\137\141\146\164\145\162\x5f\154\157\147\157\165\164\x5f\165\162\154", $B_);
        Iz:
        goto Z6;
        iN:
        update_option("\x6d\157\x5f\x65\156\141\x62\154\x65\x5f\x66\x69\x72\145\x62\141\163\x65\137\x61\x75\164\150", isset($_POST["\155\x6f\137\x65\x6e\x61\x62\x6c\x65\x5f\146\x69\162\145\142\141\x73\145\x5f\141\165\x74\x68"]) ? (int) filter_var($_POST["\155\x6f\137\x65\x6e\141\142\x6c\145\x5f\146\x69\x72\145\x62\x61\x73\x65\137\141\x75\x74\150"], FILTER_SANITIZE_NUMBER_INT) : 0);
        Z6:
        G4:
    }
    function mo_firebase_authentication_get_current_customer()
    {
        $fr = new MO_Firebase_Customer();
        $DF = $fr->mo_firebase_auth_get_customer_key();
        $q4 = json_decode($DF, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            goto WP;
        }
        update_option("\x6d\x6f\x5f\x66\x69\162\145\142\x61\163\x65\x5f\141\x75\x74\x68\145\156\164\x69\143\x61\x74\x69\157\x6e\137\x76\145\x72\x69\x66\x79\137\143\x75\x73\x74\157\x6d\x65\162", "\x74\162\x75\x65");
        goto Gk;
        WP:
        update_option("\x6d\x6f\137\x66\x69\162\145\x62\x61\x73\x65\x5f\x61\x75\164\x68\145\156\x74\151\143\141\x74\151\157\156\137\141\x64\155\x69\x6e\137\x63\165\x73\164\x6f\155\x65\162\137\153\x65\x79", $q4["\x69\144"]);
        update_option("\x6d\157\137\x66\x69\x72\x65\142\141\x73\145\137\x61\x75\x74\x68\145\x6e\164\151\143\x61\x74\151\x6f\x6e\137\141\x64\155\x69\x6e\x5f\141\160\x69\137\153\145\x79", $q4["\141\x70\x69\113\x65\171"]);
        update_option("\x6d\x6f\137\x66\x69\x72\145\142\141\163\x65\x5f\141\165\x74\150\x65\x6e\164\x69\143\x61\164\x69\157\156\x5f\x63\x75\x73\x74\157\x6d\x65\162\137\164\x6f\x6b\145\x6e", $q4["\x74\157\153\145\x6e"]);
        update_option("\x70\x61\163\163\167\157\x72\x64", '');
        update_option("\155\x6f\137\x66\x69\162\x65\142\141\163\x65\137\141\x75\x74\x68\x5f\x6d\145\163\163\141\147\145", "\x43\165\163\164\x6f\155\x65\x72\40\x72\x65\164\x72\x69\x65\x76\145\x64\x20\163\x75\143\x63\x65\x73\163\146\x75\x6c\x6c\171");
        delete_option("\155\157\137\146\151\x72\x65\142\x61\x73\x65\x5f\x61\x75\164\x68\x65\x6e\164\x69\x63\x61\164\151\157\x6e\137\x76\x65\x72\x69\x66\171\x5f\x63\165\x73\x74\x6f\155\x65\162");
        delete_option("\155\157\137\x66\151\162\x65\x62\x61\x73\145\137\141\165\164\150\x65\156\164\x69\143\x61\164\x69\157\156\137\x6e\145\167\137\162\x65\147\x69\x73\164\x72\141\x74\x69\157\x6e");
        $this->mo_firebase_auth_show_success_message();
        Gk:
    }
}
$xe = new mo_firebase_authentication_login();
