<?php
declare(strict_types=1);
use Ksfraser\FrontAccounting\HRM\TimeTracking\Service\TimeTrackingService;
$path_to_root = '../../..';
include_once($path_to_root . '/includes/session.inc');
add_access_extensions();
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) { display_error(_('Time Tracking module dependencies are not installed')); exit; }
require_once $autoload;
$service = new TimeTrackingService();
$action = isset($_GET['action']) ? (string) $_GET['action'] : 'list';
if ($action === 'list') {
    page(_('Time Entries'));
    $entries = $service->listAllTimeEntries();
    echo '<p>' . _('Time Entries') . '</p>';
    echo '<table class="selection">';
    echo '<thead><tr>' . '<th>' . _('Employee') . '</th>' . '<th>' . _('Event Code') . '</th>' . '<th>' . _('Hours') . '</th>' . '<th>' . _('Work Date') . '</th>' . '<th>' . _('Pay Impact') . '</th>' . '<th>' . _('Actions') . '</th>' . '</tr></thead><tbody>';
    foreach ($entries as $e) {
        echo '<tr>' . '<td>' . htmlspecialchars($e['fields']['employeeId'], ENT_QUOTES, 'UTF-8') . '</td>' . '<td>' . htmlspecialchars($e['fields']['eventCode'], ENT_QUOTES, 'UTF-8') . '</td>' . '<td>' . htmlspecialchars($e['fields']['hours'], ENT_QUOTES, 'UTF-8') . '</td>' . '<td>' . htmlspecialchars($e['fields']['workDate'], ENT_QUOTES, 'UTF-8') . '</td>' . '<td>' . htmlspecialchars($e['fields']['payImpact'], ENT_QUOTES, 'UTF-8') . '</td>' . '<td><a href="' . htmlspecialchars($e['url'], ENT_QUOTES, 'UTF-8') . '" target="_blank">' . _('Edit') . '</a></td>' . '</tr>';
    }
    echo '</tbody></table>';
    end_page();
    exit;
}
display_error(_('Unknown action'));
exit;
