<?php

declare(strict_types=1);

// Generell funktions
require_once __DIR__ . '/../libs/_traits.php';

// CLASS WeatherWarningModule
class WeatherWarningModule extends IPSModule
{
    use DebugHelper;
    use EventHelper;
    use GeoHelper;
    use ProfileHelper;
    use VariableHelper;

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
        $this->RegisterPropertyString('MapSelected', '00');
        $this->RegisterPropertyString('MapStyle', '{border:none; width:100%; height: 100%;}');
        $this->RegisterPropertyString('MapArea', 'Warngebiete_Kreise');
        $this->RegisterPropertyString('MapBackground', 'transparent');
        $this->RegisterPropertyInteger('MapWidth', 500);
        $this->RegisterPropertyInteger('MapHeight', 500);
        $this->RegisterPropertyFloat('MapWest', 0.000000);
        $this->RegisterPropertyFloat('MapSouth', 0.000000);
        $this->RegisterPropertyFloat('MapEast', 0.000000);
        $this->RegisterPropertyFloat('MapNorth', 0.000000);
        $this->RegisterPropertyBoolean('MapPinActivated', false);
        $this->RegisterPropertyInteger('MapPinColor', 16777215); // Weiß
        // Image & films
        $this->RegisterPropertyBoolean('ISOActTempActivated', false);
        $this->RegisterPropertyString('ISOActTempIdent', 'de');
        $this->RegisterPropertyString('ISOActTempStyle', 'height: 225px;');
        $this->RegisterPropertyBoolean('ISOImgRadarActivated', false);
        $this->RegisterPropertyString('ISOImgRadarIdent', 'de');
        $this->RegisterPropertyString('ISOImgRadarStyle', 'height: 225px;');
        $this->RegisterPropertyBoolean('ISOMovRadarActivated', false);
        $this->RegisterPropertyString('ISOMovRadarIdent', 'de');
        $this->RegisterPropertyString('ISOMovRadarStyle', 'height: 225px;');
        $this->RegisterPropertyBoolean('ActTempActivated', false);
        $this->RegisterPropertyString('ActTempIdent', 'baw');
        $this->RegisterPropertyString('ActTempStyle', 'height: 225px;');
        $this->RegisterPropertyBoolean('ImgRadarActivated', false);
        $this->RegisterPropertyString('ImgRadarIdent', 'baw');
        $this->RegisterPropertyString('ImgRadarStyle', 'height: 225px;');
        $this->RegisterPropertyBoolean('MovRadarActivated', false);
        $this->RegisterPropertyString('MovRadarIdent', 'baw');
        $this->RegisterPropertyString('MovRadarStyle', 'height: 225px;');
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
        $map = $this->ReadPropertyString('MapSelected');
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
        $form['elements'][2]['items'][2]['items'][0]['visible'] = ($state != 'null');
        $form['elements'][2]['items'][2]['items'][1]['visible'] = ($type == 8 && $county != 'null');
        // Enable
        $form['elements'][3]['items'][2]['items'][0]['enabled'] = ($map != '00');
        $form['elements'][3]['items'][2]['items'][1]['enabled'] = ($map != '00');
        $form['elements'][3]['items'][2]['items'][2]['enabled'] = ($map != '00');
        $form['elements'][3]['items'][2]['items'][3]['enabled'] = ($map != '00');
        $form['elements'][3]['items'][4]['items'][0]['enabled'] = ($map != '00');
        $form['elements'][3]['items'][4]['items'][1]['enabled'] = ($map != '00');
        $form['elements'][3]['items'][4]['items'][2]['enabled'] = ($map != '00');
        $form['elements'][3]['items'][4]['items'][3]['enabled'] = ($map != '00');
        $form['elements'][3]['items'][5]['items'][0]['enabled'] = ($map != '00');
        $form['elements'][3]['items'][5]['items'][1]['enabled'] = ($map != '00');
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

        //Delete all references in order to readd them
        foreach ($this->GetReferenceList() as $referenceID) {
            $this->UnregisterReference($referenceID);
        }

        //Register references
        $script = $this->ReadPropertyInteger('ScriptMessage');
        if (IPS_ScriptExists($script)) {
            $this->RegisterReference($script);
        }
        $instance = $this->ReadPropertyInteger('InstanceWebfront');
        if (IPS_InstanceExists($instance)) {
            $this->RegisterReference($instance);
        }

        // Properties
        $warnType = $this->ReadPropertyString('WarningType');
        $warnState = $this->ReadPropertyString('WarningState');
        $warnCounty = $this->ReadPropertyString('WarningCounty');
        $warnCommunity = $this->ReadPropertyString('WarningCommunity');
        // Map properties
        $mapSelected = $this->ReadPropertyString('MapSelected');
        // Image & films
        $isoTmpActiv = $this->ReadPropertyBoolean('ISOActTempActivated');
        $isoTmpIdent = $this->ReadPropertyString('ISOActTempIdent');
        $isoTmpStyle = $this->ReadPropertyString('ISOActTempStyle');
        $isoImgActiv = $this->ReadPropertyBoolean('ISOImgRadarActivated');
        $isoImgIdent = $this->ReadPropertyString('ISOImgRadarIdent');
        $isoImgStyle = $this->ReadPropertyString('ISOImgRadarStyle');
        $isoMovActiv = $this->ReadPropertyBoolean('ISOMovRadarActivated');
        $isoMovIdent = $this->ReadPropertyString('ISOMovRadarIdent');
        $isoMovStyle = $this->ReadPropertyString('ISOMovRadarStyle');
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
                        //', MAP=' . $deActiv . ', BL-MAP=' . $blActiv . ', BL-STATE=' . $blIdent .
                        ', Tmp=' . $tmpActiv . ', Img=' . $imgActiv . ', Mov=' . $movActiv);
        // Profile
        $this->RegisterProfile(vtInteger, 'UWW.Level', 'Warning', '', '', 1, 4, 1, 0, DWD_SEVERITY);
        // Maintain variables
        $this->MaintainVariable('Table', $this->Translate('Warning messages'), vtString, '~HTMLBox', 1, true);
        $this->MaintainVariable('Text', $this->Translate('Warning text'), vtString, '', 2, $varText == 1);
        // - Map
        $this->MaintainVariable('Map', $this->Translate('Storm map'), vtString, '~HTMLBox', 3, $mapSelected != '00');
        // - Images & Movie
        $this->MaintainVariable('ActTemp', $this->Translate('Current temperatures'), vtString, '~HTMLBox', 21, $tmpActiv);
        if ($tmpActiv) {
            $src = str_replace('<STATE>', $tmpIdent, DWD_LINKS['TEMP']);
            $val = '<div style="' . $tmpStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ActTemp', $val);
        }
        $this->MaintainVariable('ImgRadar', $this->Translate('Precipitation radar image'), vtString, '~HTMLBox', 22, $imgActiv);
        if ($imgActiv) {
            $src = str_replace('<STATE>', $imgIdent, DWD_LINKS['RADAR']);
            $val = '<div style="' . $imgStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ImgRadar', $val);
        }
        $this->MaintainVariable('MovRadar', $this->Translate('Precipitation radar film'), vtString, '~HTMLBox', 23, $movActiv);
        if ($movActiv) {
            $src = str_replace('<STATE>', $movIdent, DWD_LINKS['MOVIE']);
            $val = '<div style="' . $movStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('MovRadar', $val);
        }
        $this->MaintainVariable('ISOActTemp', $this->Translate('Current temperatures') . ' (' . $isoTmpIdent . ')', vtString, '~HTMLBox', 31, $isoTmpActiv);
        if ($isoTmpActiv) {
            $src = str_replace('<STATE>', 'brd', DWD_LINKS['TEMP']);
            $val = '<div style="' . $isoTmpStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ISOActTemp', $val);
        }
        $this->MaintainVariable('ISOImgRadar', $this->Translate('Precipitation radar image') . ' (' . $isoImgIdent . ')', vtString, '~HTMLBox', 32, $isoImgActiv);
        if ($isoImgActiv) {
            $src = str_replace('<STATE>', 'brd', DWD_LINKS['RADAR']);
            $val = '<div style="' . $isoImgStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ISOImgRadar', $val);
        }
        $this->MaintainVariable('ISOMovRadar', $this->Translate('Precipitation radar film') . ' (' . $isoMovIdent . ')', vtString, '~HTMLBox', 33, $isoMovActiv);
        if ($isoMovActiv) {
            $src = str_replace('<STATE>', 'brd', DWD_LINKS['MOVIE']);
            $val = '<div style="' . $isoMovStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ISOMovRadar', $val);
        }
        // - Indicator
        $this->MaintainVariable('Level', $this->Translate('Warning level'), vtInteger, 'UWW.Level', 0, $varWarning);
        // Status
        if (($warnState == 'null') || ($warnCounty == 'null') || (($warnType == 8) && ($warnCommunity == 'null'))) {
            $this->SendDebug(__FUNCTION__, '104: Type=' . $warnType . ', State=' . $warnState . ', County=' . $warnCounty . ', Community=' . $warnCommunity);
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
            case 'OnWarningMap':
                $this->OnWarningMap($value);
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
        // Warnmeldung (Map)
        $this->UpdateMap($geo, $now);
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
     * Select another map area.
     *
     * @param string $value Area.
     */
    protected function OnWarningMap($value)
    {
        $this->SendDebug(__FUNCTION__, $value);
        // Enable?
        $enable = ($value != '00');
        $this->UpdateFormField('MapArea', 'enabled', $enable);
        $this->UpdateFormField('MapStyle', 'enabled', $enable);
        $this->UpdateFormField('MapBackground', 'enabled', $enable);
        $this->UpdateFormField('MapHeight', 'enabled', $enable);
        $this->UpdateFormField('MapWidth', 'enabled', $enable);
        $this->UpdateFormField('MapWest', 'enabled', $enable);
        $this->UpdateFormField('MapSouth', 'enabled', $enable);
        $this->UpdateFormField('MapEast', 'enabled', $enable);
        $this->UpdateFormField('MapNorth', 'enabled', $enable);
        $this->UpdateFormField('MapPinActivated', 'enabled', $enable);
        $this->UpdateFormField('MapPinColor', 'enabled', $enable);
        // value
        $this->UpdateFormField('MapArea', 'value', 'Warngebiete_Kreise');
        $this->UpdateFormField('MapBackground', 'value', 'transparent');
        $this->UpdateFormField('MapWidth', 'value', DWD_GEO_MAPS[$value][0]);
        $this->UpdateFormField('MapHeight', 'value', DWD_GEO_MAPS[$value][1]);
        $this->UpdateFormField('MapWest', 'value', DWD_GEO_MAPS[$value][2]);
        $this->UpdateFormField('MapSouth', 'value', DWD_GEO_MAPS[$value][3]);
        $this->UpdateFormField('MapEast', 'value', DWD_GEO_MAPS[$value][4]);
        $this->UpdateFormField('MapNorth', 'value', DWD_GEO_MAPS[$value][5]);
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
        $unique = [];
        // iterate
        foreach ($warnings as $value) {
            // format date item
            $output = $this->FormatWarning($value, $format);
            // filter duplicates
            if (!in_array($output, $unique)) {
                $unique[] = $output;
            } else {
                continue;
            }
            // send to dashboard
            if ($isDashboard && $script != 0) {
                if ($value['LEVEL'] >= $levelDashboard) {
                    if ($time > 0) {
                        $msg = IPS_RunScriptWaitEx($script, ['action' => 'add', 'text' => $output, 'expires' => time() + ($time * 60), 'removable' => true, 'type' => 2, 'image' => 'WindSpeed']);
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
            $url = $this->ExtractIcon($value);
            $time = strftime('%a, %d.%b, %H:%M - ', strtotime($value['START']));
            if ($value['END'] != '') {
                $time = $time . strftime('%a, %d.%b, %H:%M Uhr', strtotime($value['END']));
            }
            $html .= '<tr>';
            $html .= '<td class=\'img\'><img class=\'warn\' src=\'' . $url . '\' /></td>';
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
     * Updates the storm map
     *
     * @param array $warning Array of warning data.
     * @param int $ts Timestamp
     */
    private function UpdateMap(array $warnings, int $ts)
    {
        $mapSelected = $this->ReadPropertyString('MapSelected');
        $this->SendDebug(__FUNCTION__, 'MAP: ' . $mapSelected);

        if ($mapSelected == '00') {
            // Nothing to do
            return;
        }
        // User selected Values
        $mapArea = $this->ReadPropertyString('MapArea');
        $mapStyle = $this->ReadPropertyString('MapStyle');
        $mapBkgd = $this->ReadPropertyString('MapBackground');
        $mapWidth = $this->ReadPropertyInteger('MapWidth');
        $mapHeight = $this->ReadPropertyInteger('MapHeight');
        $mapBox[] = str_replace(',', '.', (string) $this->ReadPropertyFloat('MapWest'));
        $mapBox[] = str_replace(',', '.', (string) $this->ReadPropertyFloat('MapSouth'));
        $mapBox[] = str_replace(',', '.', (string) $this->ReadPropertyFloat('MapEast'));
        $mapBox[] = str_replace(',', '.', (string) $this->ReadPropertyFloat('MapNorth'));
        // Prepear GET parameters
        $service = '?service=WMS';
        $version = '&version=1.3';
        $request = '&request=GetMap';
        $layers = '&layers=';
        $transparent = '&transparent=';
        $style = '&style=';
        $height = '&height=' . $mapHeight;
        $width = '&width=' . $mapWidth;
        $bbox = '&bbox=' . implode(',', $mapBox);
        $srs = '&srs=EPSG:4326';
        $format = '&format=image/png';
        $filter = '&cql_filter=';
        $test = '&test=' . $ts;
        // Prepeare Layer & Filter arreas
        $la = [];
        $fa = [];
        // Background (mismatch layer and parameter)
        if ($mapBkgd == 'bluemarble') {
            $la[] = 'dwd:bluemarble';
            $fa[] = 'INCLUDE';
        } elseif ($mapBkgd == 'transparent') {
            $transparent .= 'true';
        } else {
            $transparent .= 'false';
        }
        // Layers & Filter
        if ($mapSelected == '99') {
            if ($mapArea == 'Warngebiete_Gemeinden') {
                $la[] = 'dwd:Warngebiete_Gemeinden';
                $fa[] = 'INCLUDE';
                $la[] = 'dwd:Warnungen_Gemeinden';
                $fa[] = 'INCLUDE';
            } else {
                $la[] = 'dwd:Warngebiete_Kreise';
                $fa[] = 'INCLUDE';
                $la[] = 'dwd:Warnungen_Landkreise';
                $fa[] = 'INCLUDE';
            }
        } elseif ($mapSelected == '17') {
            if ($mapArea == 'Warngebiete_Gemeinden') {
                $la[] = 'dwd:Warngebiete_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%27810%25%27%20OR%20WARNCELLID%20LIKE%20%27807%25%27';
                $la[] = 'dwd:Warnungen_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%27810%25%27%20OR%20WARNCELLID%20LIKE%20%27807%25%27';
            } else {
                $la[] = 'dwd:Warngebiete_Kreise';
                $fa[] = 'WARNCELLID%20LIKE%20%27910%25%27%20OR%20WARNCELLID%20LIKE%20%27110%25%27%20OR%20WARNCELLID%20LIKE%20%27907%25%27%20OR%20WARNCELLID%20LIKE%20%27107%25%27';
                $la[] = 'dwd:Warnungen_Landkreise';
                $fa[] = 'GC_WARNCELLID%20LIKE%20%27910%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27110%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27907%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27107%25%27';
            }
        } elseif ($mapSelected == '21') {
            if ($mapArea == 'Warngebiete_Gemeinden') {
                $la[] = 'dwd:Warngebiete_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%27801%25%27%20OR%20WARNCELLID%20LIKE%20%27802%25%27';
                $la[] = 'dwd:Warnungen_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%27801%25%27%20OR%20WARNCELLID%20LIKE%20%27802%25%27';
            } else {
                $la[] = 'dwd:Warngebiete_Kreise';
                $fa[] = 'WARNCELLID%20LIKE%20%27901%25%27%20OR%20WARNCELLID%20LIKE%20%27101%25%27%20OR%20WARNCELLID%20LIKE%20%27902%25%27%20OR%20WARNCELLID%20LIKE%20%27102%25%27';
                $la[] = 'dwd:Warnungen_Landkreise';
                $fa[] = 'GC_WARNCELLID%20LIKE%20%27901%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27101%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27902%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27102%25%27';
            }
        } elseif ($mapSelected == '23') {
            if ($mapArea == 'Warngebiete_Gemeinden') {
                $la[] = 'dwd:Warngebiete_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%27811%25%27%20OR%20WARNCELLID%20LIKE%20%27812%25%27';
                $la[] = 'dwd:Warnungen_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%27811%25%27%20OR%20WARNCELLID%20LIKE%20%27812%25%27';
            } else {
                $la[] = 'dwd:Warngebiete_Kreise';
                $fa[] = 'WARNCELLID%20LIKE%20%27911%25%27%20OR%20WARNCELLID%20LIKE%20%27111%25%27%20OR%20WARNCELLID%20LIKE%20%27912%25%27%20OR%20WARNCELLID%20LIKE%20%27112%25%27';
                $la[] = 'dwd:Warnungen_Landkreise';
                $fa[] = 'GC_WARNCELLID%20LIKE%20%27911%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27111%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27912%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27112%25%27';
            }
        } elseif ($mapSelected == '34') {
            if ($mapArea == 'Warngebiete_Gemeinden') {
                $la[] = 'dwd:Warngebiete_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%27803%25%27%20OR%20WARNCELLID%20LIKE%20%27804%25%27';
                $la[] = 'dwd:Warnungen_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%27803%25%27%20OR%20WARNCELLID%20LIKE%20%27804%25%27';
            } else {
                $la[] = 'dwd:Warngebiete_Kreise';
                $fa[] = 'WARNCELLID%20LIKE%20%27903%25%27%20OR%20WARNCELLID%20LIKE%20%27103%25%27%20OR%20WARNCELLID%20LIKE%20%27904%25%27%20OR%20WARNCELLID%20LIKE%20%27104%25%27';
                $la[] = 'dwd:Warnungen_Landkreise';
                $fa[] = 'GC_WARNCELLID%20LIKE%20%27903%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27103%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27904%25%27%20OR%20GC_WARNCELLID%20LIKE%20%27104%25%27';
            }
        } else {
            if ($mapArea == 'Warngebiete_Gemeinden') {
                $la[] = 'dwd:Warngebiete_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%278' . $mapSelected . '%25%27';
                $la[] = 'dwd:Warnungen_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%278' . $mapSelected . '%25%27';
            } else {
                $la[] = 'dwd:Warngebiete_Kreise';
                $fa[] = 'WARNCELLID%20LIKE%20%279' . $mapSelected . '%25%27%20OR%20WARNCELLID%20LIKE%20%271' . $mapSelected . '%25%27';
                $la[] = 'dwd:Warnungen_Landkreise';
                $fa[] = 'GC_WARNCELLID%20LIKE%20%279' . $mapSelected . '%25%27%20OR%20GC_WARNCELLID%20LIKE%20%271' . $mapSelected . '%25%27';
            }
        }
        $layers .= implode(',', $la);
        $filter .= implode(';', $fa);
        // Build url
        $url = DWD_GEO_MAPSURL . $service . $version . $request . $layers . $transparent . $height . $width . $style . $bbox . $srs . $format . $filter . $test;
        $this->SendDebug(__FUNCTION__, $url);
        // Build html
        $pin = $this->ReadPropertyBoolean('MapPinActivated');
        $col = $this->ReadPropertyInteger('MapPinColor');
        $html = '';
        $html .= '<style type="text/css">';
        $html .= '#uwwImg ' . $mapStyle;
        $html .= '#uwwPin {position: absolute; top: -20px; left: -20px; margin: -30px 0 0 -10px;border-radius: 50% 50% 50% 0; border: 4px solid #' . dechex($col) . '; width: 20px; height: 20px; transform: rotate(-45deg);}';
        $html .= '#uwwPin::after {position: absolute; content: \'\'; width: 10px; height: 10px; border-radius: 50%; top: 50%; left: 50%; margin: -5px -5px; background-color: #' . dechex($col) . ';}';
        $html .= '</style>';
        $html .= '<div id="uwwMap">';
        $html .= '<img id="uwwImg" src="' . $url . '" alt="Karte" title="Karte" />';
        if ($pin) {
            $html .= '<div id="uwwPin"></div>';
        }
        $html .= '</div>';
        if ($pin) {
            $lc = IPS_GetInstanceListByModuleID('{45E97A63-F870-408A-B259-2933F7EABF74}');
            if (!empty($lc)) {
                $id = $lc[0];
                $location = IPS_GetProperty($id, 'Location');
                $pos = json_decode($location, true);
                $x = ceil(($pos['longitude'] - $mapBox[0]) * $mapWidth / ($mapBox[2] - $mapBox[0]));
                $y = ceil(($mapBox[3] - $pos['latitude']) * $mapHeight / ($mapBox[3] - $mapBox[1]));
                $html .= '<script>';
                $html .= 'function handler() {';
                $html .= 'var ow = document.getElementById("uwwImg").offsetWidth;';
                $html .= 'var oh = document.getElementById("uwwImg").offsetHeight;';
                $html .= 'var width = ' . $mapWidth . ';';
                $html .= 'var height = ' . $mapHeight . ';';
                $html .= 'var x = ' . $x . ';';
                $html .= 'var y = ' . $y . ';';
                $html .= 'var ox = Math.ceil(ow/width*x);';
                $html .= 'var oy = Math.ceil(oh/height*y);';
                $html .= 'var pin =  document.getElementById("uwwPin");';
                $html .= 'pin.style.left= ox+\'px\';';
                $html .= 'pin.style.top= oy+\'px\';';
                $html .= '}';
                $html .= 'document.getElementById("uwwImg").addEventListener(\'load\', handler, false);';
                $html .= '</script>';
            }
        }
        $this->SetValueString('Map', $html);
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
        $output = str_replace('%N', (string) $value['LEVEL'], $output);
        $output = str_replace('%T', (string) $value['TYPE'], $output);
        $output = str_replace('%M', $value['HEADLINE'], $output);
        $output = str_replace('%D', $value['DESCRIPTION'], $output);
        return $output;
    }
}