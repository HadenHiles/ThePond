<?php


class MO_Firebase_Authentication_Loader
{
    protected $actions;
    protected $filters;
    public function __construct()
    {
        $this->actions = array();
        $this->filters = array();
    }
    public function add_action($rk, $Wt, $O1, $jR = 10, $jK = 1)
    {
        $this->actions = $this->add($this->actions, $rk, $Wt, $O1, $jR, $jK);
    }
    public function add_filter($rk, $Wt, $O1, $jR = 10, $jK = 1)
    {
        $this->filters = $this->add($this->filters, $rk, $Wt, $O1, $jR, $jK);
    }
    private function add($Vv, $rk, $Wt, $O1, $jR, $jK)
    {
        $Vv[] = array("\x68\x6f\157\153" => $rk, "\x63\157\x6d\160\x6f\156\145\x6e\x74" => $Wt, "\143\141\x6c\x6c\142\141\x63\153" => $O1, "\x70\x72\151\157\x72\x69\x74\171" => $jR, "\141\x63\143\x65\160\x74\x65\144\137\141\162\x67\x73" => $jK);
        return $Vv;
    }
    public function run()
    {
        foreach ($this->filters as $rk) {
            add_filter($rk["\150\157\157\x6b"], array($rk["\x63\x6f\155\x70\157\156\x65\156\x74"], $rk["\143\141\154\x6c\142\141\x63\x6b"]), $rk["\160\x72\x69\157\162\x69\164\171"], $rk["\141\143\x63\145\160\x74\145\x64\137\141\x72\x67\163"]);
            BN:
        }
        Pc:
        foreach ($this->actions as $rk) {
            add_action($rk["\x68\157\x6f\x6b"], array($rk["\x63\x6f\x6d\x70\157\x6e\145\156\164"], $rk["\x63\x61\x6c\x6c\142\141\143\153"]), $rk["\160\x72\151\x6f\162\151\164\171"], $rk["\x61\143\143\145\160\x74\x65\144\137\141\162\147\x73"]);
            AK:
        }
        SY:
    }
}
