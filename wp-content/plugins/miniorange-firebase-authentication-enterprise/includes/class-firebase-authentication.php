<?php


class MO_Firebase_Authentication
{
    protected $loader;
    protected $plugin_name;
    protected $version;
    public function __construct()
    {
        if (defined("\115\117\x5f\106\111\122\x45\102\x41\x53\105\137\101\x55\x54\x48\105\116\124\111\103\x41\x54\111\x4f\x4e\137\x56\105\x52\123\x49\x4f\x4e")) {
            goto SS;
        }
        $this->version = "\x31\x2e\x30\x2e\60";
        goto DY;
        SS:
        $this->version = MO_FIREBASE_AUTHENTICATION_VERSION;
        DY:
        $this->plugin_name = "\146\x69\162\145\142\141\x73\x65\55\141\165\x74\x68\145\156\x74\x69\143\x61\164\x69\157\156";
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }
    private function load_dependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . "\151\x6e\x63\154\x75\144\x65\x73\57\x63\154\141\x73\x73\x2d\x66\x69\x72\x65\x62\141\163\x65\55\141\x75\x74\x68\145\156\x74\x69\143\x61\x74\151\157\x6e\55\x6c\157\141\x64\x65\x72\x2e\x70\150\x70";
        require_once plugin_dir_path(dirname(__FILE__)) . "\151\156\x63\154\165\144\x65\163\57\x63\x6c\141\163\163\55\x66\151\162\x65\142\x61\x73\x65\x2d\141\x75\164\150\x65\156\x74\x69\x63\141\164\151\157\156\55\151\61\70\x6e\x2e\160\150\x70";
        require_once plugin_dir_path(dirname(__FILE__)) . "\141\144\x6d\151\x6e\x2f\x63\154\x61\x73\163\55\x66\x69\162\145\x62\x61\x73\x65\x2d\x61\x75\x74\150\x65\x6e\164\x69\x63\141\164\x69\157\156\55\141\144\155\151\x6e\56\x70\x68\160";
        require_once plugin_dir_path(dirname(__FILE__)) . "\160\x75\142\x6c\151\143\x2f\143\154\141\x73\x73\x2d\x66\x69\162\x65\x62\x61\x73\145\x2d\141\x75\164\x68\145\x6e\164\151\143\141\x74\x69\157\x6e\x2d\160\x75\142\154\151\x63\x2e\160\x68\160";
        $this->loader = new MO_Firebase_Authentication_Loader();
    }
    private function set_locale()
    {
        $j8 = new MO_Firebase_Authentication_i18n();
        $this->loader->add_action("\160\154\x75\x67\151\x6e\x73\x5f\x6c\x6f\141\144\x65\144", $j8, "\x6c\x6f\141\144\x5f\160\x6c\165\x67\151\x6e\x5f\x74\x65\x78\x74\144\157\x6d\141\x69\x6e");
    }
    private function define_admin_hooks()
    {
        $Hg = new MO_Firebase_Authentication_Admin($this->get_plugin_name(), $this->get_version());
        add_action("\141\144\x6d\151\x6e\x5f\155\145\156\x75", array($this, "\155\151\156\151\x6f\162\141\156\x67\x65\x5f\146\151\x72\x65\142\141\163\x65\137\x6d\x65\x6e\x75"));
        $this->loader->add_action("\x61\x64\x6d\151\156\137\x65\x6e\161\165\145\165\145\x5f\163\143\x72\x69\x70\x74\x73", $Hg, "\x65\x6e\x71\x75\145\165\145\137\x73\164\171\x6c\x65\x73");
        $this->loader->add_action("\141\144\155\151\x6e\137\x65\156\161\x75\x65\165\145\x5f\163\143\162\x69\x70\x74\163", $Hg, "\145\x6e\161\165\145\165\145\x5f\x73\x63\162\x69\x70\x74\163");
        $this->loader->add_action("\x77\160\x5f\145\x6e\x71\x75\145\x75\145\137\x73\143\162\151\160\164\163", $Hg, "\145\156\x71\x75\x65\165\x65\137\146\151\x72\145\142\x61\x73\x65\x5f\x73\x63\162\x69\160\164\x73");
        $this->loader->add_action("\154\157\x67\x69\x6e\x5f\x66\x6f\157\164\145\162", $Hg, "\145\x6e\161\165\145\165\145\x5f\146\151\x72\145\142\x61\163\x65\x5f\x77\160\137\x6c\x6f\147\151\156\137\x73\143\x72\151\160\164\x73");
        $this->loader->add_action("\167\x6f\x6f\x63\x6f\x6d\x6d\145\162\143\x65\137\154\x6f\147\x69\156\x5f\146\x6f\x72\x6d\x5f\145\x6e\144", $Hg, "\x65\x6e\x71\x75\x65\165\145\137\x66\151\x72\x65\142\141\x73\145\137\167\x6f\157\x63\157\155\x6d\145\162\x63\145\137\x6c\x6f\147\x69\x6e\137\x73\x63\162\x69\x70\x74\x73");
        $this->loader->add_action("\167\x6f\157\x63\157\155\x6d\x65\x72\143\x65\137\141\146\164\x65\162\137\x6c\x6f\163\164\x5f\160\141\x73\x73\167\x6f\162\144\137\146\157\x72\155", $Hg, "\145\156\161\x75\145\165\x65\x5f\146\x69\x72\x65\x62\x61\163\x65\x5f\167\157\157\x63\157\x6d\155\145\162\143\145\x5f\x6c\157\147\151\156\x5f\x73\x63\162\151\160\x74\163");
        $this->loader->add_action("\x6d\157\137\143\165\163\164\x6f\x6d\137\x6c\x6f\147\x69\156\137\x66\x6f\162\x6d\x5f\x65\x6e\x64", $Hg, "\145\x6e\x71\165\x65\165\x65\137\146\151\162\145\142\x61\163\x65\x5f\x63\x75\163\x74\x6f\155\x5f\154\157\147\151\x6e\x5f\x73\143\x72\x69\x70\x74\x73", 10, 3);
        $this->loader->add_action("\x6d\157\x5f\x63\165\163\x74\157\x6d\137\162\x65\x67\151\x73\x74\x72\141\x74\151\x6f\x6e\x5f\146\x6f\162\155\x5f\145\x6e\144", $Hg, "\145\x6e\x71\165\145\x75\x65\137\x66\151\x72\145\x62\141\x73\145\137\x63\165\x73\x74\x6f\155\137\x72\145\x67\x69\x73\164\162\141\x74\151\157\156\x5f\163\143\162\x69\x70\164\163", 10, 3);
        $this->loader->add_action("\155\157\x5f\x66\x69\162\145\x62\141\163\145\137\145\156\x71\x75\x65\165\145\x5f\151\156\x69\164\151\141\154\x69\x7a\145\137\163\x63\162\151\x70\164\163", $Hg, "\x65\156\x71\165\145\165\x65\137\146\x69\162\x65\142\141\x73\145\137\151\156\151\x74\x69\x61\x6c\151\x7a\145\x5f\163\143\x72\151\x70\x74\163");
        $this->loader->add_action("\x6d\x65\160\162\55\163\x69\147\x6e\165\160\55\163\x63\x72\151\x70\x74\x73", $Hg, "\145\156\x71\x75\145\x75\145\137\x66\x69\162\x65\142\x61\163\145\x5f\155\145\x6d\142\x65\x72\160\162\x65\163\163\137\163\x63\162\x69\160\164\163", 10, 3);
    }
    function miniorange_firebase_menu()
    {
        $Vd = add_menu_page("\x43\x6f\x6e\146\x69\147\x75\x72\x61\164\x69\157\156", "\106\151\162\x65\142\141\163\145\x20\x41\165\164\x68\145\x6e\x74\151\x63\141\164\x69\x6f\156", "\155\141\x6e\141\x67\145\x5f\157\160\164\x69\x6f\156\x73", "\155\x6f\x5f\146\151\x72\145\142\141\x73\x65\x5f\x61\165\x74\x68\x65\x6e\x74\x69\x63\141\x74\x69\157\x6e\137\x73\x65\164\x74\x69\156\x67\163", array($this, "\x6d\157\x5f\x66\151\x72\145\x62\x61\163\145\x5f\x61\165\x74\x68\137\157\160\x74\151\157\156\163"), plugin_dir_url(__FILE__) . "\56\x2e\x2f\x70\x75\x62\x6c\x69\143\x2f\151\155\x61\147\145\163\x2f\x6d\x69\156\x69\x6f\x72\141\156\147\145\56\160\156\147");
    }
    function mo_firebase_auth_options()
    {
        $Hg = new MO_Firebase_Authentication_Admin($this->get_plugin_name(), $this->get_version());
        $Hg->mo_firebase_auth_page();
    }
    public function run()
    {
        $this->loader->run();
    }
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }
    public function get_loader()
    {
        return $this->loader;
    }
    public function get_version()
    {
        return $this->version;
    }
}
