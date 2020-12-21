<?php


class MO_Firebase_Authentication_Public
{
    private $plugin_name;
    private $version;
    public function __construct($AF, $aM)
    {
        $this->plugin_name = $AF;
        $this->version = $aM;
    }
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . "\x63\x73\x73\57\146\151\162\145\142\141\163\145\x2d\141\165\164\x68\x65\x6e\x74\x69\x63\141\x74\151\x6f\156\x2d\160\165\x62\x6c\151\143\x2e\x63\163\x73", array(), $this->version, "\x61\154\x6c");
    }
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . "\152\x73\x2f\146\x69\x72\145\142\141\x73\x65\x2d\x61\165\x74\x68\x65\x6e\x74\151\143\141\x74\151\x6f\x6e\55\x70\x75\x62\154\151\x63\x2e\152\x73", array("\152\161\x75\x65\x72\171"), $this->version, false);
    }
}
