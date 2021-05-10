<?php

declare(strict_types=1);

// Generell funktions
require_once __DIR__ . '/../libs/_traits.php';

// CLASS WeatherWarningModule
class WeatherWarningModule extends IPSModule
{
    use ProfileHelper;
    use EventHelper;
    use GeoHelper;
    use DebugHelper;

    /**
     * Create.
     */
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        // Warning properties
        $this->RegisterPropertyString('WarningType', '1');
        $this->RegisterPropertyString('WarningState', 'null');
        $this->RegisterPropertyString('WarningCounty', 'null');
        $this->RegisterPropertyString('WarningCommunity', 'null');
        // Map properties
        $this->RegisterPropertyBoolean('MapCountryActivated', false);
        $this->RegisterPropertyString('MapCountryStyle', 'max-height:100%;max-width:100%;');
        $this->RegisterPropertyBoolean('MapStateActivated', false);
        $this->RegisterPropertyString('MapStateIdent', 'baw');
        $this->RegisterPropertyString('MapStateStyle', 'max-height:100%;max-width:100%;');
        // Image & films
        $this->RegisterPropertyBoolean('ActTempActivated', false);
        $this->RegisterPropertyString('ActTempIdent', 'baw');
        $this->RegisterPropertyString('ActTempStyle', 'height: 100%; width: 100%; object-fit: fill;');
        $this->RegisterPropertyBoolean('ImgRadarActivated', false);
        $this->RegisterPropertyString('ImgRadarIdent', 'baw');
        $this->RegisterPropertyString('ImgRadarStyle', 'height: 100%; width: 100%; object-fit: fill;');
        $this->RegisterPropertyBoolean('MovRadarActivated', false);
        $this->RegisterPropertyString('MovRadarIdent', 'baw');
        $this->RegisterPropertyString('MovRadarStyle', 'height: 100%; width: 100%; object-fit: fill;');
        // Stylesheet
        $this->RegisterPropertyString('table', '{width:100%;border-collapse: collapse;}');
        $this->RegisterPropertyString('tralt', '{background-color: rgba(0, 0, 0, 0.3);}');
        $this->RegisterPropertyString('tdimg', '{width:50px;border:0px;vertical-align: top;text-align:left;padding:10px;border-left: 1px solid rgba(255, 255, 255, 0.2);border-top: 1px solid rgba(255, 255, 255, 0.2);border-bottom: 1px solid rgba(255, 255, 255, 0.2);}');
        $this->RegisterPropertyString('tdtxt', '{vertical-align: top;text-align:left;padding:5px 10px 5px 10px;border-right: 1px solid rgba(255, 255, 255, 0.2);border-top: 1px solid rgba(255, 255, 255, 0.2);border-bottom: 1px solid rgba(255, 255, 255, 0.2);}');
        $this->RegisterPropertyString('title', '{font-weight: bold;}');
        $this->RegisterPropertyString('times', '{font-style: italic; font-size: smaller;}');
        $this->RegisterPropertyString('descr', '{}');
        $this->RegisterPropertyString('lwarn', '{}');
        //$this->RegisterPropertyString('twarn', '{margin-left: -50px; height: 50px; width: 50px;}');
        // Message management
        $this->RegisterPropertyInteger('DashboardMessage', 0);
        $this->RegisterPropertyInteger('DashboardLevel', 1);
        $this->RegisterPropertyInteger('DashboardDuration', 0);
        $this->RegisterPropertyInteger('NotificationMessage', 0);
        $this->RegisterPropertyInteger('NotificationLevel', 1);
        $this->RegisterPropertyString('TextFormat', $this->Translate('%L: %M (%T)'));
        $this->RegisterPropertyInteger('TextVariable', 0);
        $this->RegisterPropertyString('TextSeparator', ', ');
        $this->RegisterPropertyInteger('InstanceWebfront', 0);
        $this->RegisterPropertyInteger('ScriptMessage', 0);
        // Settings
        $this->RegisterPropertyBoolean('IndicatorVariable', false);
        $this->RegisterPropertyInteger('UpdateInterval', 15);
        // Register daily update timer
        $this->RegisterTimer('UpdateWeatherWarning', 0, 'UWW_Update(' . $this->InstanceID . ');');
    }

    /**
     * Destroy.
     */
    public function Destroy()
    {
        parent::Destroy();
    }

    /**
     * Configuration Form.
     *
     * @return JSON configuration string.
     */
    public function GetConfigurationForm()
    {
        // Read setup
        $type = $this->ReadPropertyString('WarningType');
        $state = $this->ReadPropertyString('WarningState');
        $county = $this->ReadPropertyString('WarningCounty');
        $community = $this->ReadPropertyString('WarningCommunity');
        // Debug output
        $this->SendDebug(__FUNCTION__, 'type=' . $type . ', state=' . $state . ', county=' . $county . ', community=' . $community);
        // Check properties
        if ($state == 'null') {
            $county = 'null';
        }
        if ($county == 'null') {
            $community = 'null';
        }
        // Get Form
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        // Options
        $form['elements'][2]['items'][1]['items'][1]['options'] = $this->GetWarningStates($type);
        $form['elements'][2]['items'][2]['items'][0]['options'] = $this->GetWarningCounties($type, $state);
        $form['elements'][2]['items'][2]['items'][1]['options'] = $this->GetWarningCommunities($type, $state, $county);
        // Visible
        $form['elements'][2]['items'][2]['items'][0]['visible'] = ($county != 'null');
        $form['elements'][2]['items'][2]['items'][1]['visible'] = ($community != 'null');
        // Debug output
        //$this->SendDebug('GetConfigurationForm', $form);
        return json_encode($form);
    }

    /**
     * Apply Configuration Changes.
     */
    public function ApplyChanges()
    {
        // Never delete this line!
        parent::ApplyChanges();
        // Properties
        $warnType = $this->ReadPropertyString('WarningType');
        $warnState = $this->ReadPropertyString('WarningState');
        $warnCounty = $this->ReadPropertyString('WarningCounty');
        $warnCommunity = $this->ReadPropertyString('WarningCommunity');
        // Map properties
        $deActiv = $this->ReadPropertyBoolean('MapCountryActivated');
        $deStyle = $this->ReadPropertyString('MapCountryStyle');
        $blActiv = $this->ReadPropertyBoolean('MapStateActivated');
        $blIdent = $this->ReadPropertyString('MapStateIdent');
        $blStyle = $this->ReadPropertyString('MapStateStyle');
        // Image & films
        $tmpActiv = $this->ReadPropertyBoolean('ActTempActivated');
        $tmpIdent = $this->ReadPropertyString('ActTempIdent');
        $tmpStyle = $this->ReadPropertyString('ActTempStyle');
        $imgActiv = $this->ReadPropertyBoolean('ImgRadarActivated');
        $imgIdent = $this->ReadPropertyString('ImgRadarIdent');
        $imgStyle = $this->ReadPropertyString('ImgRadarStyle');
        $movActiv = $this->ReadPropertyBoolean('MovRadarActivated');
        $movIdent = $this->ReadPropertyString('MovRadarIdent');
        $movStyle = $this->ReadPropertyString('MovRadarStyle');
        // Messages
        $varText = $this->ReadPropertyInteger('TextVariable');
        // Settings
        $varWarning = $this->ReadPropertyBoolean('IndicatorVariable');
        $timeUpdate = $this->ReadPropertyInteger('UpdateInterval');
        // Debug
        $this->SendDebug(__FUNCTION__, 'Type=' . $warnType . ', State=' . $warnState . ', County=' . $warnCounty . ', Community=' . $warnCommunity .
                        ', DE-MAP=' . $deActiv . ', BL-MAP=' . $blActiv . ', BL-STATE=' . $blIdent .
                        ', Tmp=' . $tmpActiv . ', Img=' . $imgActiv . ', Mov=' . $movActiv);
        // Profile
        $this->RegisterProfile(vtInteger, 'UWW.Level', 'Warning', '', '', 1, 4, 1, 0, DWD_SEVERITY);
        // Maintain variables
        $this->MaintainVariable('Table', $this->Translate('Warning messages'), vtString, '~HTMLBox', 1, true);
        $this->MaintainVariable('Text', $this->Translate('Warning text'), vtString, '', 2, $varText == 1);
        // - Map
        $this->MaintainVariable('MapCountry', $this->Translate('Storm map country'), vtString, '~HTMLBox', 11, $deActiv);
        if ($deActiv) {
            $src = str_replace('<STATE>', 'de', DWD_LINKS['MAPS']);
            $val = '<img src="' . $src . '" style="' . $deStyle . '" />';
            $this->SetValueString('MapCountry', $val);
        }
        $this->MaintainVariable('MapState', $this->Translate('Storm map state'), vtString, '~HTMLBox', 12, $blActiv);
        if ($deActiv) {
            $src = str_replace('<STATE>', $blIdent, DWD_LINKS['MAPS']);
            $val = '<img src="' . $src . '" style="' . $blStyle . '" />';
            $this->SetValueString('MapState', $val);
        }
        // - Images & Movie
        $this->MaintainVariable('ActTemp', $this->Translate('Current temperatures'), vtString, '~HTMLBox', 21, $tmpActiv);
        if ($tmpActiv) {
            $src = str_replace('<STATE>', $tmpIdent, DWD_LINKS['TEMP']);
            $val = '<img src="' . $src . '" style="' . $tmpStyle . '" />';
            $this->SetValueString('ActTemp', $val);
        }
        $this->MaintainVariable('ImgRadar', $this->Translate('Precipitation radar image'), vtString, '~HTMLBox', 22, $imgActiv);
        if ($imgActiv) {
            $src = str_replace('<STATE>', $imgIdent, DWD_LINKS['RADAR']);
            $val = '<img src="' . $src . '" style="' . $imgStyle . '" />';
            $this->SetValueString('ImgRadar', $val);
        }
        $this->MaintainVariable('MovRadar', $this->Translate('Precipitation radar film'), vtString, '~HTMLBox', 23, $movActiv);
        if ($movActiv) {
            $src = str_replace('<STATE>', $imgIdent, DWD_LINKS['MOVIE']);
            $val = '<img src="' . $src . '" style="' . $movStyle . '" />';
            $this->SetValueString('MovRadar', $val);
        }
        // - Indicator
        $this->MaintainVariable('Level', $this->Translate('Warning level'), vtInteger, 'UWW.Level', 0, $varWarning);
        // Status
        if (($warnState == 'null') || ($warnCounty == 'null') || (($warnType == 8) && ($warnCommunity == 'null'))) {
            $this->SetStatus(104);
            $this->SetTimerInterval('UpdateWeatherWarning', 0);
            return;
        }
        // Timer
        $this->SetTimerInterval('UpdateWeatherWarning', 60 * 1000 * $timeUpdate);
        // All okay
        $this->SetStatus(102);
    }

    /**
     * RequestAction.
     *
     *  @param string $ident Ident.
     *  @param string $value Value.
     */
    public function RequestAction($ident, $value)
    {
        // Debug output
        $this->SendDebug('RequestAction', $ident . ' => ' . $value);
        // Ident == OnXxxxxYyyyy
        switch ($ident) {
            case 'OnWarningType':
                $this->OnWarningType($value);
            break;
            case 'OnWarningState':
                $this->OnWarningState($value);
            break;
            case 'OnWarningCounty':
                $this->OnWarningCounty($value);
            break;
        }
        // return true;
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * UWW_Update($id);
     */
    public function Update()
    {
        // Check instance state
        if ($this->GetStatus() != 102) {
            $this->SendDebug(__FUNCTION__, 'Status: Instance is not active.');
            return;
        }
        // TimeStamp
        $now = time();
        // Request Warning Infos
        $geo = json_decode($this->WarningInfo(), true);
        // Update level
        $this->UpdateLevel($geo, $now);
        // Warnmeldung (Table)
        $this->UpdateTable($geo, $now);
        // Warnmeldung (Text)
        $this->UpdateText($geo, $now);
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * UWW_WarningInfo($id);
     */
    public function WarningInfo()
    {
        // Output array
        $data = [];
        $this->SendDebug(__FUNCTION__, $data);
        // Check instance state
        if ($this->GetStatus() != 102) {
            $this->SendDebug(__FUNCTION__, 'Status: Instance is not active.');
            return json_encode($data);
        }
        // Setup Warning
        $warnType = $this->ReadPropertyString('WarningType');
        $warnCounty = $this->ReadPropertyString('WarningCounty');
        $warnCommunity = $this->ReadPropertyString('WarningCommunity');
        // WarnCellID
        $warnCellID = $warnCounty;
        if ($warnType == 8) {
            $warnCellID = $warnCommunity;
        }
        // Build URL
        $url = $this->BuildURL($warnType, $warnCellID);
        // Request data
        $json = @file_get_contents($url);
        // Error handling
        if ($json === false) {
            $this->SendDebug(__FUNCTION__, 'ERROR LOAD DATA');
            return json_encode($data);
        }
        // Extract data
        $data = $this->PrepareWarnings($json);
        // return data
        return json_encode($data);
    }

    /**
     * Select another warning area type.
     *
     * @param string $value Type (1,2,4,5,8,9).
     */
    protected function OnWarningType($value)
    {
        // State Options
        $this->UpdateFormField('WarningState', 'value', 'null');
        $this->UpdateFormField('WarningState', 'options', json_encode($this->GetWarningStates($value)));
        // Value & Visibility
        $this->UpdateFormField('WarningCounty', 'value', 'null');
        $this->UpdateFormField('WarningCounty', 'visible', false);
        $this->UpdateFormField('WarningCommunity', 'value', 'null');
        $this->UpdateFormField('WarningCommunity', 'visible', false);
    }

    /**
     * Select another state.
     *
     * @param string $value State (01 - 16).
     */
    protected function OnWarningState($value)
    {
        $this->SendDebug(__FUNCTION__, $value);
        $data = unserialize($value);
        // County Options
        $this->UpdateFormField('WarningCounty', 'options', json_encode($this->GetWarningCounties($data['type'], $data['state'])));
        $this->UpdateFormField('WarningCounty', 'value', 'null');
        $this->UpdateFormField('WarningCounty', 'visible', ($data['state'] != 'null'));
        // Value & Visiblity
        $this->UpdateFormField('WarningCommunity', 'value', 'null');
        $this->UpdateFormField('WarningCommunity', 'visible', false);
    }

    /**
     * Select another region/county/county town.
     *
     * @param string $value County (Xxxxxxxxx | xyz).
     */
    protected function OnWarningCounty($value)
    {
        $this->SendDebug(__FUNCTION__, $value);
        $data = unserialize($value);
        // Community Options (only Type 8)
        if ($data['type'] == 8) {
            $this->UpdateFormField('WarningCommunity', 'options', json_encode($this->GetWarningCommunities($data['type'], $data['state'], $data['county'])));
            $this->UpdateFormField('WarningCommunity', 'value', 'null');
            $this->UpdateFormField('WarningCommunity', 'visible', ($data['county'] != 'null'));
        } else {
            $this->UpdateFormField('WarningCommunity', 'value', 'null');
            $this->UpdateFormField('WarningCommunity', 'visible', false);
        }
    }

    /**
     * Returns for the dropdown menu the selectable states for the warning type.
     *
     * @param string $type Warning type Identifier
     * @return array List of states.
     */
    protected function GetWarningStates($type)
    {
        $this->SendDebug(__FUNCTION__, $type);
        // Extract states
        $options = $this->ExtractData($type);
        // Always add the selection prompt
        $prompt = ['caption' => $this->Translate('Please select ...') . str_repeat(' ', 79), 'value' => 'null'];
        array_unshift($options, $prompt);
        return $options;
    }

    /**
     * Returns for the dropdown menu the selectable counties in the state.
     *
     * @param string $type Warning type identifier
     * @param string $state State identifier
     * @return array List of states.
     */
    protected function GetWarningCounties($type, $state)
    {
        $this->SendDebug(__FUNCTION__, $type . ' => ' . $state);
        // Options
        $options = [];
        // Extract counties
        if ($state != 'null') {
            $options = $this->ExtractData($type, $state);
        }
        // Always add the selection prompt
        $prompt = ['caption' => $this->Translate('Please select ...') . str_repeat(' ', 79), 'value' => 'null'];
        array_unshift($options, $prompt);
        return $options;
    }

    /**
     * Returns for the dropdown menu the selectable communities in the county.
     *
     * @param string $type Warning type identifier
     * @param string $state State identifier
     * @param string $county County identifier
     * @return array List of states.
     */
    protected function GetWarningCommunities($type, $state, $county)
    {
        $this->SendDebug(__FUNCTION__, $type . ' => ' . $state . ' => ' . $county);
        // Options
        $options = [];
        // Extract counties
        if ($type == 8 && $state != 'null' && $county != 'null') {
            $options = $this->ExtractData($type, $state, $county);
        }
        // Always add the selection prompt
        $prompt = ['caption' => $this->Translate('Please select ...') . str_repeat(' ', 79), 'value' => 'null'];
        array_unshift($options, $prompt);
        return $options;
    }

    /**
     * Updates the level indicator
     *
     * @param array $warning Array of warning data.
     * @param int $ts Timestamp
     */
    private function UpdateLevel(array $warnings, int $ts)
    {
        $varWarning = $this->ReadPropertyBoolean('IndicatorVariable');
        $this->SendDebug(__FUNCTION__, 'IndicatorVariable: ' . $varWarning);
        if ($varWarning) {
            $level = 0;
            foreach ($warnings as $value) {
                if ($value['LEVEL'] > $level) {
                    $level = $value['LEVEL'];
                }
            }
            $this->SetValueInteger('Level', $level);
        }
    }

    /**
     * Builds the Text variable for the warnings.
     *
     * @param array $warning Array of warning data.
     * @param int $ts Timestamp
     */
    private function UpdateText(array $warnings, int $ts)
    {
        // Selected?
        $isDashboard = $this->ReadPropertyInteger('DashboardMessage');
        $isNotify = $this->ReadPropertyInteger('NotificationMessage');
        $isVariable = $this->ReadPropertyInteger('TextVariable');
        // Check output
        if (!$isDashboard && !$isNotify && !$isVariable) {
            // nothing to do
            return;
        }
        // dashboard
        $levelDashboard = $this->ReadPropertyInteger('DashboardLevel');

        $time = $this->ReadPropertyInteger('DashboardDuration');
        // notification
        $levelNotify = $this->ReadPropertyInteger('NotificationLevel');
        // format
        $format = $this->ReadPropertyString('TextFormat');
        // seperator
        $separator = $this->ReadPropertyString('TextSeparator');
        // webfront id
        $webfront = $this->ReadPropertyInteger('InstanceWebfront');
        // message script
        $script = $this->ReadPropertyInteger('ScriptMessage');

        $length = count($warnings);
        $lines = '';
        $index = 1;
        // iterate
        foreach ($warnings as $value) {
            // format date item
            $output = $this->FormatWarning($value, $format);
            // send to dashboard
            if ($isDashboard && $script != 0) {
                if ($value['LEVEL'] >= $levelDashboard) {
                    if ($time > 0) {
                        $msg = IPS_RunScriptWaitEx($script, ['action' => 'add', 'text' => $output, 'expires' => time() + $time, 'removable' => true, 'type' => 2, 'image' => 'WindSpeed']);
                    } else {
                        $msg = IPS_RunScriptWaitEx($script, ['action' => 'add', 'text' => $output, 'removable' => true, 'type' => 2, 'image' => 'WindSpeed']);
                    }
                }
            }
            // send to webfront
            if ($isNotify && $webfront != 0) {
                if ($value['LEVEL'] >= $levelNotify) {
                    WFC_PushNotification($webfront, $this->Translate('"Weather Warning"'), $output, 'WindSpeed', 0);
                }
            }
            // collect for variable
            if ($index < $length) {
                $lines .= $output . $separator;
            } else {
                $lines .= $output;
            }
            $index++;
        }
        // write to variable
        if ($isVariable) {
            if ($lines == '') {
                $lines = $this->Translate('None');
            }
            $this->SetValueString('Text', $lines);
        }
    }

    /**
     * Builds the HTML Table for the warnings.
     *
     * @param array $warning Array of warning data.
     * @param int $ts Timestamp
     */
    private function UpdateTable(array $warnings, int $ts)
    {
        // Styles
        $style = '';
        $style = $style . '<style type="text/css">';
        $css = $this->ReadPropertyString('table');
        $style = $style . 'table.uww ' . $css;
        $css = $this->ReadPropertyString('tralt');
        $style = $style . 'tr:nth-child(even) ' . $css;
        $css = $this->ReadPropertyString('tdimg');
        $style = $style . '.uww td.img' . $css;
        $css = $this->ReadPropertyString('tdtxt');
        $style = $style . '.uww td.txt ' . $css;
        $css = $this->ReadPropertyString('title');
        $style = $style . '.uww .hl ' . $css;
        $css = $this->ReadPropertyString('times');
        $style = $style . '.uww .ts ' . $css;
        $css = $this->ReadPropertyString('descr');
        $style = $style . '.uww .desc ' . $css;
        $css = $this->ReadPropertyString('lwarn');
        $style = $style . '.uww .warn ' . $css;
        //$css = $this->ReadPropertyString('twarn');
        //$style = $style . '.uww .twarn ' . $css;
        $style = $style . '</style>';
        // Table
        $html = $style;
        $html = $html . '<table class=\'uww\'>';
        // Exist Warnings
        $count = 0;
        foreach ($warnings as $value) {
            $url = '';
            foreach (DWD_EVENT_MAP as $event => $map) {
                if (in_array($value['CODE'], $map)) {
                    $url = str_replace('<EVENT>', $event, DWD_ICONS['LEVEL']);
                    $url = str_replace('<LEVEL>', DWD_SEVERITY[$value['LEVEL']][4], $url);
                }
            }
            $time = strftime('%a, %d.%b, %H:%M - ', strtotime($value['START']));
            if ($value['END'] != '') {
                $time += strftime('%a, %d.%b, %H:%M Uhr', strtotime($value['END']));
            }
            $html .= '<tr>';
            $html .= '<td class=\'img\'><img src=\'' . $url . '\' /></td>';
            $html .= '<td class=\'txt\'><div class=\'hl\'>' . $value['HEADLINE'] . '</div><div class=\'ts\'>' . $time . '</div><div class=\'desc\'>' . $value['DESCRIPTION'] . '</div></td>';
            $html .= '</tr>';
            $count++;
        }
        // Count Warnings
        if ($count == 0) {
            $head = 'Letzte Aktualisierung';
            $time = 'am ' . strftime('%a, %d.%b, %H:%M Uhr', $ts);
            $desc = 'Keine Warnungen vorhanden!';
            $html .= '<tr>';
            $html .= '<td class=\'img\'><div><img src=\'https://api.asmium.de/images/warning_check.png\' alt=\'No warning\'></div></td>';
            $html .= '<td class=\'txt\'><div class=\'hl\'>' . $head . '</div><div class=\'ts\'>' . $time . '</div><div class=\'desc\'>' . $desc . '</div></td>';
            $html .= '</tr>';
        }

        $html = $html . '</table>';
        // HTML ausgeben
        $this->SetValueString('Table', $html);
    }

    /**
     * Format a given array to a string.
     *
     * @param array $value Weather warning data
     * @param string $format Format string
     */
    private function FormatWarning(array $value, $format)
    {
        $output = str_replace('%L', $this->Translate(DWD_SEVERITY[$value['LEVEL']][1]), $format);
        $output = str_replace('%T', $value['TYPE'], $output);
        $output = str_replace('%M', $value['HEADLINE'], $output);
        return $output;
    }

    /**
     * Update a boolean value.
     *
     * @param string $ident Ident of the boolean variable
     * @param bool   $value Value of the boolean variable
     */
    private function SetValueBoolean(string $ident, bool $value)
    {
        $id = $this->GetIDForIdent($ident);
        SetValueBoolean($id, $value);
    }

    /**
     * Update a string value.
     *
     * @param string $ident Ident of the string variable
     * @param string $value Value of the string variable
     */
    private function SetValueString(string $ident, string $value)
    {
        $id = $this->GetIDForIdent($ident);
        SetValueString($id, $value);
    }

    /**
     * Update a integer value.
     *
     * @param string $ident Ident of the integer variable
     * @param int    $value Value of the integer variable
     */
    private function SetValueInteger(string $ident, int $value)
    {
        $id = $this->GetIDForIdent($ident);
        SetValueInteger($id, $value);
    }
}