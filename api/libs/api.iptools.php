<?php

class IPTools {

    /**
     * System yalf.ini config as key=>value
     *
     * @var array
     */
    protected $yalfCfg = array();

    /**
     * ICMP ping path
     *
     * @var string
     */
    protected $pingPath = '/sbin/ping';

    /**
     * Default ping options here
     *
     * @var string
     */
    protected $pingOpts = '-c 10';

    /**
     * Default DNS lookup command path
     *
     * @var string
     */
    protected $nslookupPath = '/usr/bin/host';

    /**
     * Default traceroute path
     *
     * @var string
     */
    protected $traceroutePath = '/usr/sbin/traceroute';

    /**
     * Default MTR path
     *
     * @var string
     */
    protected $mtrPath = '/usr/local/sbin/mtr';

    /**
     * MTR options here
     *
     * @var string
     */
    protected $mtrOpts = '--report --report-cycles 5';

    /**
     * Default whois path
     *
     * @var string
     */
    protected $whoisPath = '/usr/bin/whois';

    /**
     * Remote client IP addr
     *
     * @var string
     */
    protected $remoteIp = '';

    /**
     * Per-client actions timeout in seconds
     *
     * @var int
     */
    protected $timeout = 10;

    /**
     * System caching object placeholder
     *
     * @var object
     */
    protected $cache = '';

    /**
     * System message helper object
     *
     * @var object
     */
    protected $messages = '';

    /**
     * Some routes, urls etc
     */
    const PROUTE_IP = 'lookatthisip';
    const OPT_PING_PATH = 'PING_PATH';
    const OPT_PING_OPTS = 'PING_OPTIONS';
    const OPT_NSLOOK_PATH = 'NSLOOKUP_PATH';
    const OPT_TRACEROUTE_PATH = 'TRACEROUTE_PATH';
    const OPT_MTR_PATH = 'MTR_PATH';
    const OPT_MTR_OPTS = 'MTR_OPTIONS';
    const OPT_WHOIS_PATH = 'WHOIS_PATH';
    const OPT_TIMEOUT = 'REQUESTS_TIMEOUT';

    /**
     * Creates new instance
     */
    public function __construct() {
        $this->loadConfig();
        $this->setOptions();
        $this->initCache();
        $this->initMessages();
        $this->setRemoteIp();
    }

    /**
     * Preloads system config into protected property
     * 
     * @return void
     */
    protected function loadConfig() {
        $this->yalfCfg = parse_ini_file(YALFCore::YALF_CONF_PATH);
    }

    /**
     * Sets config-based options to current instance
     * 
     * @return void
     */
    protected function setOptions() {
        $this->timeout = $this->yalfCfg[self::OPT_TIMEOUT];
        $this->pingPath = $this->yalfCfg[self::OPT_PING_PATH];
        $this->pingOpts = $this->yalfCfg[self::OPT_PING_OPTS];
        $this->nslookupPath = $this->yalfCfg[self::OPT_NSLOOK_PATH];
        $this->traceroutePath = $this->yalfCfg[self::OPT_TRACEROUTE_PATH];
        $this->mtrPath = $this->yalfCfg[self::OPT_MTR_PATH];
        $this->mtrOpts = $this->yalfCfg[self::OPT_MTR_OPTS];
        $this->whoisPath = $this->yalfCfg[self::OPT_WHOIS_PATH];
    }

    /**
     * Inits system caching engine
     * 
     * @return void
     */
    protected function initCache() {
        $this->cache = new UbillingCache();
    }

    /**
     * Inits system message helper
     * 
     * @return void
     */
    protected function initMessages() {
        $this->messages = new UbillingMessageHelper();
    }

    /**
     * Sets remote client IP into protected prop
     * 
     * @return void
     */
    protected function setRemoteIp() {
        $this->remoteIp = $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Filters input IP and returns false or filtered IP addr
     * 
     * @param string $ip
     * 
     * @return bool/string
     */
    public function isIpValid($ip) {
        $result = false;
        $ip = ubRouting::filters($ip, 'nb');
        $ip = trim($ip);
        if ($this->yalfCfg['ALLOW_PRIVATE_NETS']) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE)) {
                $result = $ip;
            }
        } else {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $result = $ip;
            }
        }
        return($result);
    }

    /**
     * Renders default IP setting form
     * 
     * @param string $submitLabel
     * 
     * @return string
     */
    public function renderIpForm($submitLabel = 'Go') {
        $result = '';
        $presetIp = $this->remoteIp;
        if (ubRouting::checkPost(self::PROUTE_IP)) {
            $ipRaw = $this->isIpValid(ubRouting::post(self::PROUTE_IP));
            if ($ipRaw) {
                $presetIp = $ipRaw;
            }
        }

        $inputs = wf_TextInput(self::PROUTE_IP, __('IP'), $presetIp, false, 15, 'ip') . ' ';
        $inputs .= wf_Submit(__($submitLabel));
        $result .= wf_Form('', 'POST', $inputs, 'glamour');

        return($result);
    }

    /**
     * Catches IP action perform and returns filtered IP if its valid
     * 
     * @return string/bool
     */
    public function catchIp() {
        $result = false;
        if (ubRouting::checkPost(self::PROUTE_IP)) {
            $ipRaw = $this->isIpValid(ubRouting::post(self::PROUTE_IP));
            if ($ipRaw) {
                $result = $ipRaw;
            }
        }
        return($result);
    }

    /**
     * Checks timeout for performing actions by some client
     * 
     * @return bool
     */
    protected function checkTimeout() {
        $result = false;
        $keyName = $this->remoteIp . '_TIMEOUT';
        $lastTime = $this->cache->get($keyName, $this->timeout);
        if ($lastTime) {
            $result = false;
        } else {
            $result = true;
            //update timeout
            $this->cache->set($keyName, time(), $this->timeout);
        }

        return($result);
    }

    /**
     * Runs default ICMP ping and returns its results
     * 
     * @return string
     */
    public function runIcmpPing() {
        $result = '';
        $targetIp = $this->catchIp();
        if ($targetIp) {
            if ($this->checkTimeout()) {
                log_register('PING ' . $targetIp);
                $command = $this->pingPath . ' ' . $this->pingOpts . ' ' . $targetIp;
                $result .= wf_tag('pre') . shell_exec($command) . wf_tag('pre', true);
            } else {
                $result .= $this->messages->getStyledMessage(__('Only one request per') . ' ' . $this->timeout . ' ' . __('seconds is allowed'), 'error');
            }
        }
        return($result);
    }

    /**
     * Runs DNS lookup and returns its results
     * 
     * @return string
     */
    public function runDnsLookup() {
        $result = '';
        $targetIp = $this->catchIp();
        if ($targetIp) {
            if ($this->checkTimeout()) {
                log_register('NSLOOKUP ' . $targetIp);
                $command = $this->nslookupPath . ' ' . $targetIp;
                $result .= wf_tag('pre') . shell_exec($command) . wf_tag('pre', true);
            } else {
                $result .= $this->messages->getStyledMessage(__('Only one request per') . ' ' . $this->timeout . ' ' . __('seconds is allowed'), 'error');
            }
        }
        return($result);
    }

    /**
     * Runs traceroute and returns its results
     * 
     * @return string
     */
    public function runTraceroute() {
        $result = '';
        $targetIp = $this->catchIp();
        if ($targetIp) {
            if ($this->checkTimeout()) {
                log_register('TRACEROUTE ' . $targetIp);
                $command = $this->traceroutePath . ' ' . $targetIp;
                $result .= wf_tag('pre') . shell_exec($command) . wf_tag('pre', true);
            } else {
                $result .= $this->messages->getStyledMessage(__('Only one request per') . ' ' . $this->timeout . ' ' . __('seconds is allowed'), 'error');
            }
        }
        return($result);
    }

    /**
     * Runs mtr and returns its results
     * 
     * @return string
     */
    public function runMtr() {
        $result = '';
        $targetIp = $this->catchIp();
        if ($targetIp) {
            if ($this->checkTimeout()) {
                log_register('MTR ' . $targetIp);
                $command = $this->mtrPath . ' ' . $this->mtrOpts . ' ' . $targetIp;
                $result .= wf_tag('pre') . shell_exec($command) . wf_tag('pre', true);
            } else {
                $result .= $this->messages->getStyledMessage(__('Only one request per') . ' ' . $this->timeout . ' ' . __('seconds is allowed'), 'error');
            }
        }
        return($result);
    }

    /**
     * Runs whois and returns its results
     * 
     * @return string
     */
    public function runWhois() {
        $result = '';
        $targetIp = $this->catchIp();
        if ($targetIp) {
            if ($this->checkTimeout()) {
                log_register('WHOIS ' . $targetIp);
                $command = $this->whoisPath . ' ' . $targetIp;
                $result .= wf_tag('pre') . shell_exec($command) . wf_tag('pre', true);
            } else {
                $result .= $this->messages->getStyledMessage(__('Only one request per') . ' ' . $this->timeout . ' ' . __('seconds is allowed'), 'error');
            }
        }
        return($result);
    }

}
