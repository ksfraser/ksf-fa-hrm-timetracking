<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Controller;

use Ksfraser\FrontAccounting\Common\App\AbstractTabController;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Service\TimeTrackingService;
use ksfraser\RelatedTo\DTO\RelatedQueryDto;
use ksfraser\RelatedTo\UI\DefaultRelatedRecordsRenderer;

class TimeTrackingTabController extends AbstractTabController
{
    private $service;

    public function __construct($context = null, array $options = [])
    {
        parent::__construct($context, $options);
        $this->service = new TimeTrackingService();
    }

    protected function getPkField(): string { return 'id'; }

    protected function getFieldMetadata(): array
    {
        return [
            'entity'      => 'time_entry',
            'table'       => '0_hrm_time_entries',
            'label'       => 'Time Entry',
            'labelPlural' => 'Time Entries',
            'hookPrefix'  => 'time_entry',
            'pk'          => 'id',
            'fields'      => [],
            'fk_ddls'   => [],
            'ddlHooks'  => [],
            'tableSettings' => [],
        ];
    }

    protected function listRows(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        return array_slice($this->service->listAllTimeEntries(), $offset, $perPage);
    }

    protected function countRows(): int
    {
        return count($this->service->listAllTimeEntries());
    }

    protected function findRecord(string $pk): ?array { return null; }
    protected function createRecord(array $data): int { return 0; }
    protected function updateRecord(string $pk, array $data): void {}
    protected function deleteRecord(string $pk): void {}
    protected function fkOptions(): array { return []; }

    protected function renderSummaryTable(): void
    {
        if (!function_exists('start_table')) return;

        $page = max(1, $this->page());
        $perPage = $this->perPage;
        $rows = $this->listRows($page, $perPage);

        start_table(TABLESTYLE2, "width='95%'");
        $th = function (string $label): void {
            echo '<th>' . htmlspecialchars($this->localise($label), ENT_QUOTES) . '</th>';
        };
        echo '<tr>';
        $th(_('Employee'));
        $th(_('Event Code'));
        $th(_('Hours'));
        $th(_('Work Date'));
        $th(_('Pay Impact'));
        $th(_('Actions'));
        echo '</tr>';

        if (empty($rows)) {
            echo '<tr><td colspan="7"><em>'
                . htmlspecialchars($this->localise('No time entries yet.'), ENT_QUOTES)
                . '</em></td></tr>';
        }

        foreach ($rows as $i => $row) {
            echo '<tr class="' . ($i % 2 ? 'evenrow' : 'oddrow') . '">';
            echo '<td>' . htmlspecialchars($row['fields']['employeeId'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['fields']['eventCode'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['fields']['hours'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['fields']['workDate'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['fields']['payImpact'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td><a href="' . htmlspecialchars($row['url'] ?? '#', ENT_QUOTES, 'UTF-8') . '" target="_blank">'
                . htmlspecialchars($this->localise('View'), ENT_QUOTES, 'UTF-8') . '</a></td>'
            . '</tr>';
        }

        end_table(0);
    }

    protected function renderNotes(): void
    {
        $lhsId = isset($_GET['id']) ? (string) $_GET['id'] : '';
        if ($lhsId === '' || !function_exists('hook_invoke')) return;

        $queryDto = new RelatedQueryDto('time_entry', $lhsId);
        $queryResult = $queryDto;
        hook_invoke('ksf_FA_RelatedTo', 'RELATED_TO', $queryResult, null);

        if (!is_array($queryResult) || !isset($queryResult['related']) || !is_array($queryResult['related'])) {
            return;
        }

        $renderer = new DefaultRelatedRecordsRenderer(array(
            'css_class' => 'related-records',
            'header_class' => 'related-header',
            'table_class' => 'related-table',
            'table_border' => true,
            'type_labels' => array('time_entry' => _('Time Entry')),
        ));
        echo $renderer->renderRelatedRecords('time_entry', $lhsId, $queryResult['related'], array(
            'css_class' => 'related-records',
            'header_class' => 'related-header',
            'table_class' => 'related-table',
            'table_border' => true,
        ));
    }
}
