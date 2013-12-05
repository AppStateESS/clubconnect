<?php

$use_permissions = TRUE;
$item_permissions = TRUE;

$permissions['admin']               = _('Special SDR Administrative Access');

$permissions['club_admin']          = _('Club Originiator');
$permissions['person_admin']        = _('Person Administrator');
$permissions['role_admin']          = _('Club Officer Authority');
$permissions['registration_admin']  = _('Club Registration Authority');
$permissions['transcript_admin']    = _('Transcript Administrator');
$permissions['rollover']            = _('Perform SDR Rollovers');
$permissions['settings']            = _('Change SDR Settings');
$permissions['global_lock']         = _('Able to disable SDR for all users');

$permissions['report_annualreport']           = _('Reports: Annual Report');
$permissions['report_greekgpareport']         = _('Reports: Greek GPA Report');
$permissions['report_transfer']               = _('Reports: Transfer Participation');
$permissions['report_multiculturalgpareport'] = _('Reports: Multicultural GPA Report');
?>
