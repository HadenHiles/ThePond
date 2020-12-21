<?php


class Mo_Firebase_Config
{
    function __construct()
    {
        add_action("\x69\x6e\x69\164", array($this, "\x74\145\163\x74\143\157\x6e\146\151\147"));
        add_action("\167\160\x5f\x6c\x6f\x67\x6f\165\164", array($this, "\141\x66\x74\x65\x72\x5f\165\x73\145\162\137\154\x6f\147\157\165\164"));
    }
    function after_user_logout()
    {
        $B_ = get_option("\155\x6f\x5f\146\151\162\x65\x62\141\x73\x65\x5f\141\165\164\x68\x5f\143\165\163\164\157\155\x5f\141\x66\164\145\162\137\x6c\157\x67\x6f\165\164\x5f\x75\x72\x6c") ? get_option("\155\x6f\137\x66\x69\162\145\x62\141\163\145\137\141\165\164\150\x5f\143\165\163\164\157\x6d\137\141\146\x74\x65\162\x5f\154\157\147\157\165\164\x5f\x75\162\x6c") : home_url();
        wp_redirect($B_);
        die;
    }
    function testconfig()
    {
        if (!isset($_POST["\x66\142\x5f\152\x77\164"])) {
            goto Tw;
        }
        $user = $this->sample();
        Tw:
        if (!isset($_POST["\167\x63\137\145\162\x72\x6f\x72\x5f\x6d\x73\x67"])) {
            goto PB;
        }
        $Wp = sanitize_text_field(wp_unslash($_POST["\x77\x63\137\145\x72\x72\157\162\x5f\x6d\x73\x67"]));
        wc_add_notice($Wp, "\x65\x72\x72\x6f\162");
        PB:
        if (!isset($_POST["\167\143\x5f\x73\x75\x63\143\145\x73\x73\137\155\x73\147"])) {
            goto Qd;
        }
        $iv = sanitize_text_field(wp_unslash($_POST["\x77\x63\x5f\x73\165\x63\143\x65\163\x73\x5f\x6d\163\147"]));
        wc_add_notice($iv, "\163\x75\x63\x63\145\x73\163");
        Qd:
        if (!(isset($_REQUEST["\x6d\x6f\x5f\x61\143\164\x69\157\x6e"]) && "\146\151\x72\x65\x62\x61\163\x65\154\157\147\x69\156" === sanitize_text_field(wp_unslash($_REQUEST["\155\x6f\137\141\143\x74\151\x6f\x6e"])) && isset($_REQUEST["\164\145\163\x74"]) && "\164\162\x75\145" === wp_unslash($_REQUEST["\x74\145\163\x74"]) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST["\155\157\x5f\146\x69\x72\145\142\x61\x73\145\137\141\x75\164\x68\x5f\164\x65\x73\164\x5f\x63\x6f\156\146\x69\147\137\x66\x69\145\154\x64"])), "\x6d\157\x5f\146\151\162\x65\142\x61\163\145\137\141\165\164\150\x5f\164\x65\163\164\x5f\143\157\x6e\146\x69\x67\137\x66\x6f\162\155"))) {
            goto f7;
        }
        $OQ = get_option("\155\157\137\x66\151\x72\145\142\x61\x73\145\x5f\141\x75\x74\x68\137\160\x72\x6f\152\x65\x63\x74\x5f\x69\144");
        $IA = get_option("\x6d\x6f\137\146\x69\x72\x65\x62\x61\163\145\137\141\165\164\x68\137\141\160\151\137\153\145\x79");
        wp_register_script("\155\x6f\137\x66\x69\162\x65\x62\x61\163\145\137\141\x70\x70\x5f\x6d\141\151\x6e\x5f\x73\143\x72\151\160\164", plugins_url("\141\x64\155\151\156\57\152\163\x2f\146\x69\x72\x65\142\x61\x73\145\55\x61\165\164\x68\x2d\x6d\x61\151\156\55\x73\x63\x72\x69\160\164\x2e\152\x73", __FILE__), array("\152\161\x75\x65\162\x79"), false, true);
        wp_enqueue_script("\x6d\157\x5f\146\151\162\145\x62\141\163\x65\137\141\160\160\x5f\x6d\x61\x69\x6e\x5f\163\143\x72\x69\x70\164");
        wp_register_script("\155\x6f\137\146\151\x72\145\x62\141\x73\145\137\x74\145\163\164\x63\157\156\146\151\147\x5f\x73\x63\162\x69\160\164", plugins_url("\x6a\163\57\x66\x69\162\x65\x62\x61\x73\x65\55\x74\145\163\x74\55\x63\x6f\156\146\x69\147\x2e\152\x73", __FILE__), array("\152\x71\x75\145\x72\171"));
        $iB = array();
        $iB["\141\160\x69\137\x6b\x65\171"] = get_option("\155\157\137\x66\x69\x72\145\x62\141\x73\145\x5f\x61\x75\x74\x68\137\x61\x70\151\x5f\153\x65\171");
        $iB["\160\x72\x6f\x6a\x65\143\x74\x5f\x69\144"] = get_option("\155\157\137\x66\151\162\145\142\141\163\x65\x5f\x61\165\x74\150\x5f\x70\x72\157\152\145\143\x74\x5f\151\144");
        $iB["\x74\x65\x73\x74\137\x75\163\145\x72\x6e\x61\x6d\145"] = isset($_POST["\164\x65\x73\164\137\165\163\x65\162\156\141\x6d\x65"]) ? sanitize_text_field($_POST["\x74\x65\163\x74\x5f\165\163\145\x72\156\x61\x6d\x65"]) : '';
        $iB["\x74\145\x73\164\137\160\x61\x73\x73\167\157\x72\144"] = isset($_POST["\164\145\x73\164\x5f\x70\141\x73\x73\167\157\x72\144"]) ? sanitize_text_field($_POST["\164\x65\x73\164\137\x70\x61\163\163\x77\157\x72\x64"]) : '';
        $iB["\164\x65\x73\x74\x5f\143\150\145\x63\x6b\x5f\146\x69\145\x6c\144"] = isset($_POST["\164\145\x73\x74\137\x63\150\x65\143\153\137\146\x69\x65\x6c\x64"]) ? sanitize_text_field($_POST["\164\x65\x73\164\137\x63\150\145\143\153\137\146\151\x65\x6c\144"]) : '';
        wp_localize_script("\x6d\x6f\x5f\x66\x69\x72\x65\x62\141\163\145\137\164\145\x73\164\143\x6f\x6e\x66\151\147\137\163\x63\162\151\160\164", "\x66\x69\162\x65\x62\x61\x73\x65\x5f\x64\141\164\141\137\164\145\x73\x74\x63\x6f\156\146\x69\x67", $iB);
        wp_enqueue_script("\155\x6f\x5f\x66\151\162\x65\x62\x61\x73\x65\x5f\164\145\163\x74\143\157\x6e\x66\151\147\x5f\163\143\162\151\160\164", plugins_url("\x6a\163\x2f\146\x69\x72\145\x62\141\163\x65\55\x74\145\x73\164\55\x63\157\x6e\146\x69\147\56\x6a\163", __FILE__), array("\x6a\x71\x75\x65\x72\171"), false, true);
        f7:
    }
    function sample()
    {
        if (!(isset($_POST["\x66\x62\x5f\152\167\x74"]) && sanitize_text_field(wp_unslash($_POST["\146\142\x5f\152\167\164"])) == "\x65\x6d\x70\x74\171\137\163\x74\162\x69\156\x67")) {
            goto VF;
        }
        if (!(isset($_POST["\146\x62\137\151\x73\137\164\x65\x73\164"]) && sanitize_text_field(wp_unslash($_POST["\x66\142\137\x69\163\137\x74\145\163\164"])) == "\x77\x6f\x6f\143\157\x6d\155\x65\162\143\x65\137\145\162\x72\157\162")) {
            goto b1;
        }
        $Wp = sanitize_text_field(wp_unslash($_POST["\146\x62\137\145\x72\x72\x6f\162\x5f\x6d\163\x67"]));
        if (isset($_POST["\167\x70\137\x65\155\141\x69\154"]) && isset($_POST["\x77\160\137\x73\x65\x63\x72\145\x74"])) {
            goto ZN;
        }
        wc_add_notice($Wp, "\x65\162\x72\157\x72");
        goto mO;
        ZN:
        $bc = sanitize_text_field(wp_unslash($_POST["\167\x70\x5f\145\155\141\x69\154"]));
        $Ma = sanitize_text_field(wp_unslash($_POST["\x77\160\x5f\163\x65\x63\162\145\164"]));
        $this->mo_firebase_check_for_wp_user($Wp, $bc, $Ma);
        mO:
        b1:
        if (!(isset($_POST["\146\x62\137\x69\163\137\x74\145\x73\x74"]) && sanitize_text_field(wp_unslash($_POST["\x66\x62\x5f\x69\163\x5f\164\x65\x73\x74"])) == "\x74\x65\x73\164\137\x63\150\x65\143\x6b\137\164\162\x75\145")) {
            goto Jw;
        }
        echo "\74\144\151\166\x20\x73\x74\x79\154\145\75\x22\x66\157\156\164\55\146\141\155\x69\x6c\x79\72\x43\141\x6c\151\142\x72\151\73\x70\141\x64\x64\x69\156\147\72\x20\x30\40\x33\x30\45\x3b\42\x3e";
        echo "\x3c\150\x31\x20\163\x74\171\x6c\x65\x3d\x22\x63\157\x6c\157\162\72\43\x64\x39\x35\x33\64\146\73\164\x65\170\164\55\x61\154\151\147\x6e\72\x63\145\x6e\x74\x65\x72\73\42\76\x74\x65\163\x74\40\146\x61\151\154\145\x64\x3c\57\150\61\x3e";
        if (!isset($_POST["\146\142\137\x65\x72\x72\x6f\x72\137\155\163\147"])) {
            goto R9;
        }
        echo "\74\x68\64\x20\x73\x74\171\x6c\145\x3d\42\164\145\x78\x74\55\141\x6c\x69\x67\x6e\x3a\x63\x65\x6e\x74\x65\x72\73\x22\x3e\74\x62\76\x45\x52\122\x4f\x52\40\72\x3c\x2f\142\76" . sanitize_text_field(wp_unslash($_POST["\146\142\137\145\162\162\157\x72\137\x6d\x73\x67"])) . "\x3c\57\150\64\76";
        R9:
        echo "\x3c\57\x64\151\x76\x3e";
        echo "\74\x64\x69\x76\x20\x73\164\171\154\x65\75\42\160\141\x64\x64\x69\156\147\72\40\61\60\x70\x78\73\x22\76\x3c\57\x64\151\x76\x3e\x3c\144\x69\x76\40\163\x74\x79\154\x65\75\x22\160\x6f\x73\x69\164\x69\157\156\x3a\141\x62\163\x6f\x6c\x75\x74\145\x3b\x70\141\144\144\151\x6e\147\x3a\60\40\x34\66\x25\73\42\76\74\151\156\x70\165\x74\40\163\x74\171\154\145\75\x22\160\141\144\144\x69\156\x67\x3a\x31\x25\73\x77\151\144\164\150\72\61\60\x30\160\x78\73\x68\x65\151\147\x68\164\x3a\x33\x30\160\170\73\x62\141\x63\x6b\x67\162\157\x75\x6e\x64\x3a\40\x23\60\60\x39\x31\103\x44\x20\x6e\x6f\x6e\x65\40\x72\145\160\x65\141\164\x20\163\143\x72\x6f\x6c\x6c\40\x30\45\x20\x30\x25\73\143\x75\162\x73\157\x72\72\40\160\157\x69\x6e\x74\145\x72\73\146\157\x6e\164\x2d\x73\151\x7a\145\x3a\61\65\x70\170\x3b\x62\157\162\x64\145\x72\x2d\x77\x69\144\x74\150\72\x20\x31\160\x78\x3b\x62\157\162\144\x65\x72\55\x73\x74\x79\154\145\x3a\x20\x73\157\x6c\x69\144\x3b\x62\x6f\x72\144\x65\x72\x2d\162\x61\x64\x69\x75\163\72\40\x33\x70\x78\x3b\x77\x68\151\164\145\x2d\163\160\x61\x63\145\72\x20\x6e\157\x77\162\x61\x70\x3b\x62\157\x78\55\x73\151\x7a\x69\156\x67\72\x20\142\157\x72\x64\145\x72\x2d\x62\157\x78\73\x62\x6f\x72\144\145\162\55\143\x6f\x6c\x6f\162\72\40\43\60\x30\x37\63\x41\101\73\142\x6f\x78\x2d\163\x68\x61\144\157\x77\x3a\x20\x30\x70\x78\x20\x31\160\x78\40\x30\x70\170\40\x72\x67\142\141\x28\61\62\x30\54\x20\x32\x30\60\54\40\62\x33\x30\x2c\40\x30\56\66\x29\40\x69\x6e\x73\x65\x74\73\143\x6f\x6c\157\x72\x3a\x20\43\x46\x46\106\x3b\42\164\x79\x70\145\x3d\42\x62\x75\x74\x74\157\x6e\x22\x20\166\141\x6c\x75\x65\x3d\42\x43\x6c\x6f\163\145\x22\40\x6f\x6e\103\154\151\143\x6b\75\42\163\145\154\146\56\x63\x6c\157\163\145\x28\51\x3b\x22\x3e\74\57\x64\x69\166\76";
        die;
        Jw:
        $Wp = new WP_Error();
        $Wp->add("\x65\x72\x72\x6f\162\137\x66\145\x74\x63\x68\x69\x6e\147\x5f\x75\x73\x65\162", __("\x3c\163\164\162\x6f\x6e\x67\x3e\105\x52\x52\x4f\122\x3c\57\163\164\162\x6f\156\147\x3e\x3a\40\165\163\145\162\x20\x64\x6f\x65\x73\156\x27\x74\x20\145\x78\x69\x73\164\x20\x21\x21\x2e"));
        return $Wp;
        VF:
        if (!(isset($_POST["\146\142\137\152\x77\164"]) && sanitize_text_field(wp_unslash($_POST["\146\x62\x5f\x6a\x77\164"])) != "\145\x6d\160\x74\171\x5f\163\164\x72\x69\156\147")) {
            goto Xb;
        }
        if (!isset($_POST["\x66\x62\137\x75\163\145\162"])) {
            goto Oc;
        }
        $UT = sanitize_text_field(wp_unslash($_POST["\146\142\137\165\163\145\162"]));
        $DF = json_decode($UT, true);
        do_action("\x6d\157\137\x66\x69\x72\x65\142\141\163\x65\137\141\165\x74\150\137\147\145\164\137\163\157\143\151\141\x6c\137\x75\163\145\162", $DF);
        Oc:
        $Gd = $this->decode_jwt(sanitize_text_field(wp_unslash($_POST["\146\142\x5f\x6a\x77\x74"])));
        if (!(isset($_POST["\146\142\137\151\163\x5f\x74\145\x73\x74"]) && sanitize_text_field(wp_unslash($_POST["\146\x62\x5f\x69\x73\x5f\164\x65\163\164"])) == "\x74\145\x73\164\137\x63\x68\x65\143\x6b\x5f\164\162\165\x65")) {
            goto Mt;
        }
        echo "\74\x64\x69\166\x20\x73\x74\171\154\x65\75\42\146\x6f\x6e\x74\x2d\146\x61\155\151\154\x79\x3a\x43\x61\x6c\151\x62\x72\x69\73\155\141\162\147\151\x6e\x3a\x20\141\x75\164\x6f\73\160\141\144\144\x69\x6e\x67\x3a\65\45\73\x22\x3e";
        echo "\x3c\150\x31\x20\163\164\x79\x6c\145\75\x22\143\x6f\x6c\157\162\x3a\x23\x30\x30\103\70\65\61\x3b\x74\x65\x78\164\x2d\x61\x6c\151\147\156\x3a\x63\145\x6e\164\x65\162\73\x22\76\x54\145\163\x74\x20\123\x75\143\143\x65\163\163\x66\x75\x6c\40\x21\74\57\150\61\x3e";
        echo "\x3c\x73\x74\171\x6c\145\76\164\141\x62\154\145\173\142\157\162\x64\145\x72\x2d\143\157\x6c\x6c\x61\160\x73\145\x3a\143\157\x6c\154\141\x70\x73\x65\73\x7d\164\150\x20\x7b\x62\x61\x63\x6b\147\x72\157\x75\156\x64\55\x63\157\154\x6f\162\x3a\40\43\x65\x65\145\73\x20\x74\145\x78\164\x2d\141\154\151\x67\156\72\40\x63\x65\156\164\145\162\x3b\x20\160\141\144\144\151\156\147\72\40\70\160\x78\73\40\142\x6f\162\x64\145\x72\x2d\x77\151\x64\x74\x68\x3a\61\x70\170\73\40\x62\x6f\x72\144\x65\162\55\x73\x74\171\x6c\x65\x3a\163\x6f\154\x69\144\73\40\x62\157\162\144\x65\162\x2d\143\x6f\154\x6f\x72\72\43\x32\x31\x32\61\62\61\x3b\x7d\x74\x72\x3a\x6e\164\x68\55\x63\x68\151\x6c\x64\50\157\x64\x64\x29\x20\173\142\141\x63\x6b\147\162\x6f\165\156\x64\55\143\x6f\x6c\157\162\x3a\x20\43\146\62\x66\x32\x66\62\x3b\175\40\x74\144\173\x70\x61\144\144\151\x6e\147\72\x38\160\x78\x3b\142\157\x72\144\145\162\55\167\x69\144\164\150\x3a\x31\x70\x78\73\x20\142\x6f\162\x64\145\162\55\x73\164\x79\154\145\x3a\x73\157\x6c\151\144\x3b\x20\x62\x6f\x72\x64\x65\x72\55\x63\x6f\154\x6f\162\x3a\43\62\61\62\x31\x32\x31\73\175\x3c\x2f\x73\x74\171\154\x65\76";
        echo "\74\x68\63\x20\163\164\x79\154\145\75\x22\x74\x65\x78\x74\55\x61\x6c\x69\x67\x6e\x3a\143\145\x6e\164\145\162\73\42\x3e\x54\x65\163\x74\x20\x43\x6f\x6e\146\x69\x67\x75\162\x61\x74\x69\x6f\x6e\74\57\x68\x33\76\x3c\164\x61\x62\154\145\x20\x73\164\171\154\x65\x3d\x22\x6d\141\x72\147\151\x6e\x3a\40\x61\x75\164\157\x3b\42\x3e\x3c\164\162\76\74\x74\150\76\101\164\x74\x72\151\x62\165\164\145\x20\x4e\141\x6d\x65\74\57\x74\150\76\74\164\x68\x3e\101\x74\164\x72\x69\x62\165\x74\x65\40\x56\141\x6c\165\x65\74\57\x74\x68\x3e\74\57\x74\162\76";
        $this->testattrmappingconfig('', $Gd);
        echo "\74\57\164\x61\142\154\145\x3e\74\x2f\x64\151\166\x3e";
        echo "\x3c\x64\x69\x76\x20\x73\164\171\x6c\x65\75\x22\155\141\x72\147\x69\x6e\x3a\x20\x61\165\x74\x6f\73\160\x61\144\x64\151\x6e\147\x3a\40\61\x30\160\x78\73\76\x3c\57\x64\x69\x76\x3e\74\x64\x69\x76\x20\x73\164\171\154\x65\x3d\42\155\x61\162\x67\x69\x6e\x3a\40\x61\x75\164\x6f\73\x70\157\163\x69\164\x69\x6f\156\72\x61\x62\x73\157\154\165\x74\145\x3b\x70\x61\144\x64\x69\156\x67\72\60\40\x34\66\45\73\x22\x3e\x3c\x69\x6e\160\x75\x74\x20\x73\x74\171\154\145\x3d\42\x70\x61\144\x64\x69\x6e\147\x3a\x38\160\x78\73\167\151\144\x74\x68\72\61\x30\60\x70\170\73\x62\x61\143\x6b\x67\x72\x6f\165\156\144\x3a\40\x23\x30\60\x39\61\103\x44\40\156\157\156\x65\40\162\145\160\145\141\164\40\163\x63\162\x6f\154\154\x20\60\45\40\x30\45\73\143\x75\162\163\x6f\162\x3a\40\x70\157\151\156\x74\145\x72\73\146\x6f\156\x74\x2d\x73\151\x7a\x65\72\x31\x35\x70\x78\73\x62\x6f\162\x64\x65\162\x2d\167\151\144\x74\150\x3a\x20\61\x70\170\73\x62\157\x72\144\145\x72\55\x73\x74\171\154\x65\x3a\40\x73\157\154\151\144\73\x62\157\162\144\145\x72\55\162\x61\x64\151\165\163\72\40\x33\x70\x78\x3b\167\150\x69\x74\145\55\163\160\x61\x63\145\72\x20\x6e\x6f\167\x72\x61\x70\73\x62\x6f\x78\55\163\x69\172\x69\156\147\x3a\40\142\x6f\162\x64\145\x72\55\x62\157\x78\73\x62\x6f\162\144\145\x72\55\x63\157\154\x6f\x72\x3a\x20\x23\x30\60\67\63\x41\101\73\x62\x6f\x78\x2d\163\x68\141\x64\x6f\x77\x3a\x20\x30\160\170\40\x31\x70\170\40\60\x70\170\40\x72\147\x62\141\50\61\62\x30\54\40\x32\60\x30\54\x20\62\x33\60\54\40\x30\x2e\x36\x29\40\151\x6e\x73\145\x74\x3b\143\x6f\154\157\x72\x3a\40\43\x46\106\x46\73\x22\164\171\x70\x65\x3d\42\142\x75\x74\164\157\156\x22\x20\166\x61\154\165\x65\75\42\x44\157\156\x65\x22\40\x6f\x6e\103\154\x69\143\x6b\75\x22\163\145\154\146\x2e\143\x6c\x6f\x73\x65\50\x29\x3b\42\76\74\57\144\151\166\76";
        die;
        Mt:
        $user = $this->getUser($Gd);
        if (!$user) {
            goto PI;
        }
        $qo = $user->ID;
        wp_set_auth_cookie($qo, true);
        $Tp = get_option("\155\x6f\x5f\x66\x69\162\145\142\141\163\x65\137\x61\165\x74\x68\x5f\143\165\163\164\157\155\137\141\146\x74\145\x72\137\154\x6f\147\151\x6e\x5f\165\162\x6c") ? get_option("\x6d\157\137\x66\151\162\145\142\141\163\x65\137\141\x75\164\x68\137\143\165\x73\x74\x6f\155\x5f\141\x66\164\x65\x72\137\154\x6f\x67\x69\x6e\137\x75\x72\x6c") : home_url();
        wp_redirect($Tp);
        die;
        PI:
        Xb:
    }
    function testattrmappingconfig($YE, $Gd)
    {
        foreach ($Gd as $QB => $z3) {
            if (is_array($z3) || is_object($z3)) {
                goto Xj;
            }
            echo "\x3c\164\162\76\74\164\x64\76";
            if (empty($YE)) {
                goto M7;
            }
            echo $YE . "\56";
            M7:
            echo $QB . "\x3c\57\x74\x64\x3e\x3c\x74\x64\x3e" . $z3 . "\x3c\57\x74\x64\x3e\74\57\x74\162\76";
            goto yr;
            Xj:
            if (empty($YE)) {
                goto lJ;
            }
            $YE .= "\x2e";
            lJ:
            $this->testattrmappingconfig($YE . $QB, $z3);
            yr:
            jQ:
        }
        B5:
    }
    function decode_jwt($lu)
    {
        $c5 = 0;
        $EC = explode("\56", $lu);
        $iG = $EC[0] . "\x2e" . $EC[1];
        $TV = str_replace(array("\55", "\x5f"), array("\x2b", "\x2f"), $EC[2]);
        $TV = base64_decode($TV);
        $jf = json_decode(base64_decode(str_replace(array("\x2d", "\x5f"), array("\53", "\57"), $EC[0])), true);
        $tR = $jf["\141\x6c\147"];
        $N9 = $jf["\153\151\144"];
        if (!(strpos($tR, "\x52\123") !== false)) {
            goto o5;
        }
        $QJ = "\x52\x53\x41";
        $w2 = explode("\122\x53", $tR)[1];
        o5:
        $Oy = $this->mo_firebase_auth_get_cert_from_kid($N9);
        $Bh = '';
        $Bc = explode("\55\x2d\x2d\x2d\55", $Oy);
        if (preg_match("\x2f\134\162\x5c\156\174\x5c\162\x7c\134\x6e\x2f", $Bc[2])) {
            goto gx;
        }
        $yV = "\55\x2d\55\x2d\55" . $Bc[1] . "\x2d\x2d\55\55\55\12";
        $kk = 0;
        n9:
        if (!($wX = substr($Bc[2], $kk, 64))) {
            goto C0;
        }
        $yV .= $wX . "\xa";
        $kk += 64;
        goto n9;
        C0:
        $yV .= "\55\55\x2d\55\x2d" . $Bc[3] . "\x2d\55\55\x2d\55\xa";
        $Bh = $yV;
        goto A2;
        gx:
        $Bh = $Oy;
        A2:
        switch ($w2) {
            case "\62\x35\66":
                $Iq = openssl_verify($iG, $TV, $Bh, OPENSSL_ALGO_SHA256);
                goto qr;
            case "\x33\70\x34":
                $Iq = openssl_verify($iG, $TV, $Bh, OPENSSL_ALGO_SHA384);
                goto qr;
            case "\65\61\x32":
                $Iq = openssl_verify($iG, $TV, $Bh, OPENSSL_ALGO_SHA512);
                goto qr;
            default:
                $Iq = false;
                goto qr;
        }
        kI:
        qr:
        if ($Iq) {
            goto RW;
        }
        echo "\111\x6e\x76\141\x6c\151\144\x20\x54\x6f\153\x65\x6e";
        die;
        RW:
        $wp = json_decode(base64_decode($EC[1]), true);
        return $wp;
    }
    function mo_firebase_auth_get_cert_from_kid($N9)
    {
        $c5 = $this->mo_firebase_auth_get_kid($N9);
        if (!($c5 === 0)) {
            goto xG;
        }
        $jl = new mo_firebase_authentication_login();
        $jl->mo_firebase_auth_store_certificates();
        $c5 = $this->mo_firebase_auth_get_kid($N9);
        xG:
        if ($c5 !== 0) {
            goto fW;
        }
        echo "\x50\x6c\x65\141\x73\145\40\160\x72\157\x76\x69\x64\145\x20\x61\40\x76\x61\x6c\x69\x64\x20\143\x65\x72\164\151\x66\x69\x63\x61\164\x65\x2e\40\x43\157\x6e\x74\141\143\x74\x20\x79\157\165\162\40\x61\144\155\x69\x6e\151\163\164\x72\141\x74\157\162\x2e";
        die;
        goto oP;
        fW:
        if ($c5 === 1) {
            goto BH;
        }
        if ($c5 === 2) {
            goto W_;
        }
        if (!($c5 === 3)) {
            goto eg;
        }
        $Oy = get_option("\x6d\157\137\146\x69\x72\145\142\141\x73\x65\x5f\x61\165\164\150\137\143\145\x72\164\63");
        eg:
        goto Ag;
        W_:
        $Oy = get_option("\x6d\x6f\137\x66\x69\x72\145\142\141\x73\145\x5f\x61\x75\x74\150\137\143\145\162\x74\x32");
        Ag:
        goto zh;
        BH:
        $Oy = get_option("\155\157\x5f\x66\x69\x72\x65\x62\141\x73\145\137\x61\x75\x74\x68\137\x63\145\162\164\61");
        zh:
        oP:
        return $Oy;
    }
    function mo_firebase_auth_get_kid($N9)
    {
        $c5 = 0;
        $p3 = get_option("\155\157\x5f\x66\151\x72\145\142\x61\x73\145\x5f\x61\x75\x74\x68\x5f\x6b\x69\x64\61");
        if ($p3 != $N9) {
            goto D2;
        }
        $c5 = 1;
        goto SQ;
        D2:
        $c5 = 2;
        $p3 = get_option("\x6d\157\137\146\x69\x72\145\142\141\x73\145\x5f\141\x75\x74\150\137\x6b\151\144\x32");
        if (!($p3 != $N9)) {
            goto SW;
        }
        $c5 = 3;
        $p3 = get_option("\x6d\157\x5f\146\x69\162\145\x62\141\163\x65\137\x61\x75\164\x68\x5f\153\x69\144\x33");
        if (!($p3 != $N9)) {
            goto Df;
        }
        $c5 = 0;
        Df:
        SW:
        SQ:
        return $c5;
    }
    function getUser($wp)
    {
        $ga = get_option("\155\157\137\x66\x69\x72\x65\x62\x61\163\145\137\x6d\141\x70\160\x65\144\x5f\x61\x74\164\162");
        $ga = isset($ga) ? json_decode($ga, true) : array();
        $Qz = isset($ga["\155\x6f\137\x66\151\162\x65\142\x61\163\x65\x5f\x75\163\x65\162\x6e\x61\x6d\145\137\141\x74\x74\162"]) ? $ga["\x6d\x6f\137\x66\151\162\x65\142\x61\163\145\x5f\165\x73\145\162\x6e\x61\x6d\145\137\141\164\164\162"] : '';
        $c6 = isset($ga["\x6d\157\137\146\151\x72\145\x62\x61\163\145\137\x65\155\x61\151\154\137\141\x74\164\162"]) ? $ga["\x6d\x6f\137\146\151\162\145\x62\x61\x73\x65\x5f\x65\x6d\x61\x69\x6c\x5f\141\x74\164\x72"] : '';
        $C6 = $this->getnestedattribute($wp, $Qz);
        $Sl = $this->getnestedattribute($wp, $c6);
        if (!(!isset($C6) || empty($C6))) {
            goto eq;
        }
        $C6 = $this->getnestedattribute($wp, "\145\155\141\x69\154");
        eq:
        if (!(!empty($Sl) && false === strpos($Sl, "\x40"))) {
            goto yw;
        }
        echo "\115\x61\160\160\x65\x64\40\105\155\141\x69\154\x20\141\x74\164\x72\x69\x62\165\x74\x65\40\144\157\145\x73\x20\x6e\x6f\164\x20\x63\x6f\156\164\x61\x69\x6e\40\x76\x61\x6c\x69\144\40\x65\x6d\x61\151\x6c\56";
        die;
        yw:
        $user = get_user_by("\x6c\157\147\x69\156", $C6);
        if ($user) {
            goto KY;
        }
        $user = get_user_by("\x65\x6d\141\x69\x6c", $Sl);
        KY:
        $qo = $user ? $user->ID : 0;
        $AW = $qo === 0;
        if (!$AW) {
            goto lc;
        }
        $dS = wp_generate_password(10, false);
        $Gy = array("\165\163\x65\162\137\x6c\157\x67\x69\156" => $C6, "\165\x73\145\162\x5f\160\x61\x73\163" => $dS, "\x75\163\x65\x72\137\145\x6d\141\x69\154" => $Sl);
        $qo = wp_insert_user($Gy);
        lc:
        if (!$AW) {
            goto Mr;
        }
        $qo = get_user_by("\154\157\147\151\156", $C6)->ID;
        Mr:
        $Xu = array("\111\104" => $qo, "\x75\x73\145\162\137\x6c\157\147\x69\x6e" => $C6, "\x75\x73\145\x72\137\x65\x6d\141\151\x6c" => $Sl, "\x75\163\x65\162\x5f\x6e\x69\143\x65\x6e\x61\x6d\x65" => $C6);
        wp_update_user($Xu);
        if (is_wp_error($qo)) {
            goto MI;
        }
        update_user_meta($qo, "\155\157\x5f\146\151\162\145\x62\x61\163\145\x5f\165\x73\145\162\x5f\x64\x6e", false);
        MI:
        $user = get_user_by("\x6c\157\x67\151\156", $C6);
        if ($user) {
            goto eC;
        }
        $user = get_user_by("\x65\155\x61\151\154", $Sl);
        eC:
        do_action("\155\x6f\137\146\151\x72\x65\x62\x61\x73\x65\137\x67\145\164\x5f\152\x77\x74\x5f\x74\157\153\x65\x6e", $user, $wp);
        return $user;
    }
    public function getnestedattribute($tl, $QB)
    {
        if (!($QB == '')) {
            goto IK;
        }
        return '';
        IK:
        $nU = explode("\56", $QB);
        if (count($nU) > 1) {
            goto Rp;
        }
        if (is_array($tl[$QB])) {
            goto Zg;
        }
        $uM = $nU[0];
        if (!isset($tl[$uM])) {
            goto eA;
        }
        if (is_array($tl[$uM])) {
            goto Ug;
        }
        return $tl[$uM];
        goto Ae;
        Ug:
        return $tl[$uM][0];
        Ae:
        eA:
        goto dQ;
        Zg:
        if (!(count($tl[$QB]) > 1)) {
            goto BY;
        }
        return $tl[$QB];
        BY:
        return $tl[$QB][0];
        dQ:
        goto Ku;
        Rp:
        $uM = $nU[0];
        if (!isset($tl[$uM])) {
            goto d1;
        }
        return $this->getnestedattribute($tl[$uM], str_replace($uM . "\56", '', $QB));
        d1:
        Ku:
    }
    function mo_firebase_check_for_wp_user($Wp, $bc, $m1)
    {
        $c5 = 0;
        $user = get_user_by("\x65\155\141\151\154", $bc);
        if ($user) {
            goto zH;
        }
        $user = get_user_by("\154\x6f\x67\x69\156", $bc);
        zH:
        if (!$user) {
            goto Cz;
        }
        if (get_option("\x6d\x6f\x5f\146\x69\x72\x65\142\x61\x73\145\137\x61\x75\x74\x68\x5f\144\x69\163\141\142\x6c\x65\137\x77\157\x72\144\160\162\145\x73\163\x5f\x6c\x6f\147\x69\x6e") == false) {
            goto qN;
        }
        if (!get_option("\155\157\x5f\x66\x69\162\x65\x62\x61\163\145\137\x61\x75\164\x68\x5f\x65\156\141\x62\154\145\137\x61\x64\155\151\156\137\167\x70\x5f\x6c\157\x67\x69\156")) {
            goto cP;
        }
        $jl = new mo_firebase_authentication_login();
        if (!$jl->is_administrator_user($user)) {
            goto Sr;
        }
        if (wp_check_password($m1, $user->data->user_pass, $user->ID)) {
            goto uQ;
        }
        $Wp = "\x54\x68\145\x20\x70\x61\163\163\x77\x6f\x72\x64\x20\171\157\x75\x20\150\x61\x76\145\40\x65\156\164\145\162\145\x64\40\151\x73\40\x69\156\x63\x6f\x72\162\145\x63\x74\x2e";
        wc_add_notice($Wp, "\x65\162\x72\157\x72");
        return;
        goto M0;
        uQ:
        $c5 = 1;
        M0:
        Sr:
        cP:
        goto u0;
        qN:
        if (wp_check_password($m1, $user->data->user_pass, $user->ID)) {
            goto fB;
        }
        $Wp = "\x54\150\145\40\160\x61\163\163\167\157\x72\x64\x20\x79\157\165\x20\150\141\166\x65\40\145\x6e\164\x65\162\x65\144\40\x69\163\40\x69\x6e\143\157\162\162\x65\x63\164\56";
        wc_add_notice($Wp, "\x65\x72\162\157\162");
        return;
        goto tD;
        fB:
        $c5 = 1;
        tD:
        u0:
        if ($c5 == 1) {
            goto NT;
        }
        wc_add_notice($Wp, "\145\162\x72\157\162");
        return;
        goto GP;
        NT:
        $qo = $user->ID;
        wp_set_auth_cookie($qo, true);
        $ZO = '';
        $ZO = apply_filters("\167\x6f\x6f\143\x6f\155\x6d\145\x72\x63\145\x5f\154\157\147\151\x6e\137\162\145\x64\x69\162\145\x63\164", $ZO, $user);
        if (!empty($ZO)) {
            goto qF;
        }
        wp_redirect(wc_get_page_permalink("\155\171\141\143\x63\x6f\x75\x6e\164"));
        goto Ez;
        qF:
        wp_redirect($ZO);
        Ez:
        die;
        GP:
        Cz:
        wc_add_notice($Wp, "\x65\162\162\157\x72");
    }
}
$t2 = new Mo_Firebase_Config();
