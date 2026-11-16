<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\HRM\TimeTracking\App;

use ksfraser\FrontAccounting\Common\App\AbstractAppShell;
use ksfraser\FrontAccounting\Common\Menu\FAModuleMenu;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Controller\TimeTrackingTabController;

class TimeTrackingAppShell extends AbstractAppShell
{
    public function __construct()
    {
        parent::__construct('time_entry', 'index.php', 'view', 'time_entries');

        $this->add_module(_("Time Tracking"));

        $menu = new FAModuleMenu(
            'modules/ksf_FA_HRM_TimeTracking/index.php',
            'view',
            ''
        );

        $menu->addItem('time_entries', _("Time Entries"), MENU_INQUIRY)
             ->registerWithApp($this, 'SA_TIMETRACK_VIEW');

        $this->add_extensions();
    }
}
