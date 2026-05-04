<?php

return [
  'name' => 'Test Auto',
  'img' => 'img/testautomation.gif',
  'version' => '0.1.0',
  'vendor' => '--',
  'handlers' => [
        'backend_automation_view' => 'cashEventViewTestautomationHandler',
        'backend_automation_handle' => 'cashEventTestautomationHandler'
    ]
];
