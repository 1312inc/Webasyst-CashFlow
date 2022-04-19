<?php

waFiles::delete(wa()->getAppPath('templates/actions/report/ReportDds.html', 'cash'), true);
waFiles::delete(wa()->getAppPath('lib/actions/report/Dds', 'cash'), true);
waFiles::delete(wa()->getAppPath('lib/class/Report/Dds', 'cash'), true);
waFiles::delete(wa()->getAppPath('lib/class/Report/Dds/cashReportMenuItemListener.class.php', 'cash'), true);
