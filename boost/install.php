<?php

function sdr_install(&$content, $branchInstall=FALSE)
{
    include PHPWS_SOURCE_DIR . 'mod/sdr/boost/boost.php';

    // Insert a current term for right now, they can change it later
    $term = date('Y');
    $month = date('m');
    $term .= ($month <= 5 ? '10' :
             ($month <= 6 ? '20' :
             ($month <= 7 ? '30' :
                            '40')));
    $db = new PHPWS_DB('sdr_term');
    $db->addValue('term', $term);
    $db->addValue('sdr_version', $version);
    $db->addValue('selectable', 1);
    $result = $db->insert();

    if(PHPWS_Error::logIfError($result)) {
        return $result;
    }

    $content[] = dgettext('sdr', "SDR: Inserted selectable term $term with version $version");

    // Load default settings because for some reason PHPWS_Settings doesn't do it
    include PHPWS_SOURCE_DIR . 'mod/sdr/inc/settings.php';
    PHPWS_Settings::Set('sdr', $settings);

    // Set the current term to the term set above
    PHPWS_Settings::set('sdr', 'current_term', $term);

    // Try to set a reasonable base URI
    $uri = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '/index.php'));
    PHPWS_Settings::set('sdr', 'base_uri', $uri);

    // Save Settings
    $result = PHPWS_Settings::save('sdr');

    if(PHPWS_Error::logIfError($result)) {
        return $result;
    }

    $content[] = dgettext('sdr', "SDR: Current Term set to $term");
    $content[] = dgettext('sdr', "SDR: Base URI set to $uri");

    return TRUE;
}

?>
