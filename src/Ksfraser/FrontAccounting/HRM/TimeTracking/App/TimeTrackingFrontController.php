<?php

declare(strict_types=1);

namespace Ksfraser\FrontAccounting\HRM\TimeTracking\App;

use ksfraser\FrontAccounting\Common\App\AbstractFrontController;

class TimeTrackingFrontController extends AbstractFrontController
{
    public function __construct(
        TimeTrackingAppShell $appShell,
        string $fallbackSecurityArea,
        string $path_to_root
    ) {
        parent::__construct($appShell, $fallbackSecurityArea, $path_to_root);
    }
}
