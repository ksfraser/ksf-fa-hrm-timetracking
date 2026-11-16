<?php
declare(strict_types=1);
require_once __DIR__ . '/ComposerDependencies.php';
\Ksfraser\FrontAccounting\HRM\TimeTracking\Utils\ComposerDependencies::ensure(__DIR__);
$autoload_path = dirname(__FILE__) . '/vendor/autoload.php';
if (file_exists($autoload_path)) { require_once $autoload_path; }
require_once __DIR__ . '/includes/common.inc';
require_once __DIR__ . '/includes/events.inc';
use ksfraser\RelatedTo\DTO\RelatedRecordDto;
define('SS_ksf_FA_HRM_TimeTracking', 168 << 8);
if (!defined('SA_TIMETRACK_VIEW')) {
    define('SA_TIMETRACK_VIEW', SS_ksf_FA_HRM_TimeTracking | 1);
    define('SA_TIMETRACK_MANAGE', SS_ksf_FA_HRM_TimeTracking | 2);
}
/**
 * @BABOK Related: BR-TT-001
 * @BABOK Related: BR-TT-002
 * @BABOK Related: BR-TT-004
 */
class hooks_ksf_FA_HRM_TimeTracking extends hooks {
    const RESPONDERS = array('RELATED_TO_FIELDS', 'TIMEENTRY_RESOLVE', 'TIMEENTRY_FIELDS', 'EVENTCODE_RESOLVE', 'EVENTCODE_FIELDS');
    var $module_name = 'ksf_FA_HRM_TimeTracking';
    var $version = '1.0.0';
    /**
     * @BABOK Related: BR-TT-001
     * @BABOK Related: BR-TT-002
     */
    function install_tabs($app) { }
    /**
     * @BABOK Related: BR-TT-001
     * @BABOK Related: BR-TT-002
     */
    function install_options($app) { }
    /**
     * @BABOK Related: BR-TT-001
     * @BABOK Related: BR-TT-002
     */
    function install_access() {
        $security_sections[SS_ksf_FA_HRM_TimeTracking] = _("Time Tracking Management");
        $security_areas['SA_TIMETRACK_VIEW'] = array(SA_TIMETRACK_VIEW, _("View time tracking"));
        $security_areas['SA_TIMETRACK_MANAGE'] = array(SA_TIMETRACK_MANAGE, _("Manage time tracking"));
        return array($security_areas, $security_sections);
    }
    /**
     * @BABOK Related: BR-TT-001
     * @BABOK Related: BR-TT-002
     */
    function activate_extension($company, $check_only = true) {
        if ($check_only) { return true; }
        \Ksfraser\FrontAccounting\HRM\TimeTracking\Utils\ComposerDependencies::ensure(__DIR__);
        $this->update_databases($company, array(
            '0_hrm_event_codes.sql' => array('hrm_event_codes'),
            '0_hrm_time_entries.sql' => array('hrm_time_entries'),
        ), false);
        return true;
    }
    private function ensure_composer_dependencies() {
        \Ksfraser\FrontAccounting\HRM\TimeTracking\Utils\ComposerDependencies::ensure(__DIR__);
    }
    /**
     * @BABOK Related: BR-TT-001
     */
    public function getModuleConstants(&$data, $opts = null) {
        $data['constants'] = array('SS_ksf_FA_HRM_TimeTracking' => SS_ksf_FA_HRM_TimeTracking, 'SA_TIMETRACK_VIEW' => SA_TIMETRACK_VIEW, 'SA_TIMETRACK_MANAGE' => SA_TIMETRACK_MANAGE);
        return true;
    }
    /**
     * @BABOK Related: BR-TT-001
     */
    public function getModuleCapabilities(&$data, $opts = null) {
        $data['capabilities'] = self::RESPONDERS;
        return true;
    }
    /**
     * @BABOK Related: BR-TT-001
     */
    public function hasCapability(&$data, $opts) {
        $capability = is_array($opts) && isset($opts['capability']) ? $opts['capability'] : null;
        if ($capability === null && isset($data['capability'])) { $capability = $data['capability']; }
        $data['has_capability'] = in_array($capability, self::RESPONDERS, true);
        return $data['has_capability'];
    }
    /**
     * @BABOK Related: BR-TT-001
     */
    public function respondToCapabilityRequest(&$data, $opts = null) {
        $request = is_array($opts) && isset($opts['request']) ? $opts['request'] : (isset($data['request']) ? $data['request'] : 'capabilities');
        $data['request'] = $request;
        $data['module'] = $this->module_name;
        if (strpos($request, 'has:') === 0) { return $this->hasCapability($data, array('capability' => substr($request, 4))); }
        switch ($request) {
            case 'capabilities': return $this->getModuleCapabilities($data, $opts);
            case 'constants': return $this->getModuleConstants($data, $opts);
            default: $data['error'] = 'Unknown request type: ' . $request; return null;
        }
    }
    /**
     * @BABOK Related: BR-TT-004
     */
    public function RELATED_TO_FIELDS(&$data, $opts = null) {
        if (!isset($data['module']) || !is_string($data['module'])) { $data['error'] = 'RELATED_TO_FIELDS requires data[module]'; return null; }
        if (!isset($data['type']) || !is_string($data['type'])) { $data['error'] = 'RELATED_TO_FIELDS requires data[type]'; return null; }
        if (!in_array($data['type'], array('time_entry', 'event_code'))) { return null; }
        if (!isset($data['fields']) || !is_array($data['fields'])) { $data['fields'] = array(); }
        if ($data['type'] === 'time_entry') {
            $data['fields'] = array_merge($data['fields'], array(
                'employeeId' => _('Employee'),
                'project_id' => _('Project'),
                'activity_code' => _('Activity Code'),
                'hours' => _('Hours'),
                'workDate' => _('Work Date'),
                'payImpact' => _('Pay Impact'),
            ));
        } elseif ($data['type'] === 'event_code') {
            // Event code fields come from CodeMaster; delegate to CodeMaster
            $data['fields'] = array_merge($data['fields'], array(
                'code' => _('Code'),
                'name' => _('Name'),
                'description' => _('Description'),
                'usage_instructions' => _('Usage Instructions'),
            ));
        }
        return null;
    }
    /**
     * @BABOK Related: BR-TT-004
     */
    public function TIMEENTRY_RESOLVE(&$data, $opts = null) {
        if (!isset($data['time_entry_id']) || !is_numeric($data['time_entry_id'])) { $data['error'] = 'TIMEENTRY_RESOLVE requires data[time_entry_id]'; return null; }
        if (!isset($data['dto']) || !$data['dto'] instanceof RelatedRecordDto) { $data['error'] = 'TIMEENTRY_RESOLVE requires data[dto] as RelatedRecordDto'; return null; }
        $service = new \Ksfraser\FrontAccounting\HRM\TimeTracking\Service\TimeTrackingService();
        $data['result'] = $service->resolveTimeEntryById((int) $data['time_entry_id'], $data['dto']);
        return null;
    }
    /**
     * @BABOK Related: BR-TT-004
     */
    public function TIMEENTRY_FIELDS(&$data, $opts = null) {
        $data['fields'] = array(
            'employeeId' => _('Employee'),
            'project_id' => _('Project'),
            'activity_code' => _('Activity Code'),
            'hours' => _('Hours'),
            'workDate' => _('Work Date'),
            'payImpact' => _('Pay Impact'),
        );
        return null;
    }
    /**
     * @BABOK Related: BR-TT-001
     * @BABOK Related: BR-TT-004
     */
    public function EVENTCODE_RESOLVE(&$data, $opts = null) {
        if (!isset($data['event_code_id']) || !is_numeric($data['event_code_id'])) { $data['error'] = 'EVENTCODE_RESOLVE requires data[event_code_id]'; return null; }
        if (!isset($data['dto']) || !$data['dto'] instanceof RelatedRecordDto) { $data['error'] = 'EVENTCODE_RESOLVE requires data[dto] as RelatedRecordDto'; return null; }
        // Delegate event code resolution to CodeMaster
        $payload = array(
            'code_type' => 'hrm.activity_code',
            'code_value' => (string) $data['event_code_id'],
            'dto' => new RelatedRecordDto('code_master', '0', 'code_master', '0', null),
        );
        if (function_exists('hook_invoke_all')) {
            hook_invoke_all('ksf_FA_CodeMaster', 'CODEMASTER_RESOLVE', $payload);
        }
        $result = $payload['result'] ?? null;
        if ($result === null || !is_array($result) || !isset($result['fields'])) {
            $data['error'] = 'EVENTCODE_RESOLVE: no result from CodeMaster for event code ' . (string) $data['event_code_id'];
            return null;
        }
        $data['result'] = array(
            'dto' => $result['dto'] ?? $data['dto'],
            'fields' => $result['fields'],
            'labels' => $result['labels'],
            'url' => $result['url'],
            'label' => $result['label'],
        );
        return null;
    }
    /**
     * @BABOK Related: BR-TT-001
     */
    public function EVENTCODE_FIELDS(&$data, $opts = null) {
        $data['fields'] = array(
            'code' => _('Code'),
            'name' => _('Name'),
            'description' => _('Description'),
            'usage_instructions' => _('Usage Instructions'),
        );
        return null;
    }
}

/**
 * crm_register_tabs - allows other modules to register their tabs with CRM.
 *
 * @param array &$data Modified by reference; 'tabs' array is appended to.
 */
function crm_register_tabs(&$data)
{
    if (!isset($data['tabs']) || !is_array($data['tabs'])) {
        $data['tabs'] = [];
    }
    // Time Tracking module registers its tab here
}
