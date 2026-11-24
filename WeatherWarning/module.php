<?php

declare(strict_types=1);

/** General functions */
require_once __DIR__ . '/../libs/_traits.php';

/** Namespaced traits */
use Wilkware\WeatherWarning\DebugHelper;
use Wilkware\WeatherWarning\EventHelper;
use Wilkware\WeatherWarning\FormHelper;
use Wilkware\WeatherWarning\GeoHelper;
use Wilkware\WeatherWarning\VariableHelper;
use Wilkware\WeatherWarning\VersionHelper;

/**
 *  CLASS WeatherWarning
 */
class WeatherWarning extends IPSModuleStrict
{
    // -------------------------------------------------------------------------
    // Traits
    // -------------------------------------------------------------------------

    use DebugHelper;
    use EventHelper;
    use FormHelper;
    use GeoHelper;
    use VariableHelper;
    use VersionHelper;

    /** @var string Default sound of push notifications */
    private const NOTIFICATION_SOUND = 'siren';

    /** @var int Instance status if the warning data could not be loaded */
    private const STATUS_FETCH_FAILED = 201;

    /** @var array<int,string> Form fields which are only enabled if a storm map is selected */
    private const MAP_FIELDS = [
        'MapArea', 'MapStyle', 'MapBackground', 'MapHeight', 'MapWidth',
        'MapWest', 'MapSouth', 'MapEast', 'MapNorth', 'MapPinActivated', 'MapPinColor',
    ];

    // -------------------------------------------------------------------------
    // Presentations
    // -------------------------------------------------------------------------

    /**
     * @var array<string,mixed> Html Presentation (Web)
     */
    private const UWW_PRESENTATION_HTML = [
        'PRESENTATION' => VARIABLE_PRESENTATION_WEB_CONTENT,
    ];

    /**
     * @var array<string,mixed> Level Presentation (Value)
     */
    private const UWW_PRESENTATION_LEVEL = [
        'ICON'             => 'triangle-exclamation',
        'INTERVALS'        => '[{"ColorActive":true,"ColorValue":12969318,"ConstantActive":true,"ConstantValue":"None","IntervalMaxValue":0,"IntervalMinValue":0},{"ColorActive":true,"ColorValue":16771899,"ConstantActive":true,"ConstantValue":"Minor","IntervalMaxValue":1,"IntervalMinValue":1},{"ColorActive":true,"ColorValue":16485376,"ConstantActive":true,"ConstantValue":"Moderate","IntervalMaxValue":2,"IntervalMinValue":2},{"ColorActive":true,"ColorValue":15022389,"ConstantActive":true,"ConstantValue":"Severe","IntervalMaxValue":3,"IntervalMinValue":3},{"ColorActive":true,"ColorValue":8916559,"ConstantActive":true,"ConstantValue":"Extreme","IntervalMaxValue":4,"IntervalMinValue":4},{"ColorActive":true,"ColorValue":16673022,"ConstantActive":true,"ConstantValue":"UV","IntervalMaxValue":10,"IntervalMinValue":10},{"ColorActive":true,"ColorValue":13408767,"ConstantActive":true,"ConstantValue":"Heat","IntervalMaxValue":11,"IntervalMinValue":11},{"ColorActive":true,"ColorValue":16673022,"ConstantActive":true,"ConstantValue":"UV","IntervalMaxValue":12,"IntervalMinValue":12},{"ColorActive":true,"ColorValue":10372856,"ConstantActive":true,"ConstantValue":"Heat (extreme)","IntervalMaxValue":13,"IntervalMinValue":13}]',
        'INTERVALS_ACTIVE' => true,
        'PRESENTATION'     => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    ];

    /**
     * @var array<string,mixed> Text Presentation (Value)
     */
    private const UWW_PRESENTATION_TEXT = [
        'ICON'         => 'message-exclamation',
        'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    ];

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    /**
     * In contrast to Construct, this function is called only once when creating the instance and starting Symcon.
     * Therefore, status variables and module properties which the module requires permanently should be created here.
     *
     * @return void
     */
    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        // Warning properties
        $this->RegisterPropertyString('WarningCountry', 'de');
        $this->RegisterPropertyString('WarningType', '1');
        $this->RegisterPropertyString('WarningState', 'null');
        $this->RegisterPropertyString('WarningCounty', 'null');
        $this->RegisterPropertyString('WarningCommunity', 'null');

        // Map properties
        $this->RegisterPropertyString('MapSelected', '00');
        $this->RegisterPropertyString('MapArea', 'Warngebiete_Kreise');
        $this->RegisterPropertyString('MapBackground', 'transparent');
        $this->RegisterPropertyInteger('MapWidth', 500);
        $this->RegisterPropertyInteger('MapHeight', 500);
        $this->RegisterPropertyFloat('MapWest', 0.000000);
        $this->RegisterPropertyFloat('MapSouth', 0.000000);
        $this->RegisterPropertyFloat('MapEast', 0.000000);
        $this->RegisterPropertyFloat('MapNorth', 0.000000);
        $this->RegisterPropertyBoolean('MapPinActivated', false);
        $this->RegisterPropertyInteger('MapPinColor', 16777215);
        $this->RegisterPropertyString('LegendHtml', self::HTML_LEGEND);

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
        $this->RegisterPropertyBoolean('ISOMovForecastActivated', false);
        $this->RegisterPropertyString('ISOMovForecastIdent', 'de');
        $this->RegisterPropertyString('ISOMovForecastStyle', 'height: 225px;');
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
        $this->RegisterPropertyString('MapStyle', self::CSS_STYLES['MapStyle']);
        $this->RegisterPropertyString('WarningStyle', self::CSS_STYLES['WarningStyle']);
        $this->RegisterPropertyString('LegendStyle', self::CSS_STYLES['LegendStyle']);

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
        $this->RegisterPropertyString('NotificationSound', self::NOTIFICATION_SOUND);
        $this->RegisterPropertyInteger('ScriptMessage', 0);
        $this->RegisterPropertyString('MessageKey', '');

        // Settings
        $this->RegisterPropertyBoolean('IndicatorVariable', false);
        $this->RegisterPropertyBoolean('LegendVariable', false);
        $this->RegisterPropertyInteger('UpdateInterval', 15);

        // Tile visualization
        $this->RegisterPropertyString('TileLegendButton', 'bottom-right');
        $this->RegisterPropertyString('TileAreaButton', 'top-left');

        // Register daily update timer
        $this->RegisterTimer('UpdateWeatherWarning', 0, 'UWW_Update(' . $this->InstanceID . ');');

        // Set visualization type to 1, as we want to offer HTML
        $this->SetVisualizationType(1);
    }

    /**
     * The content can be overwritten in order to transfer a self-created configuration page.
     * This way, content can be generated dynamically.
     * In this case, the "form.json" on the file system is completely ignored.
     *
     * @return string Content of the configuration page.
     */
    public function GetConfigurationForm(): string
    {
        // Get Form
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

        // Read setup
        $type = $this->ReadPropertyString('WarningType');
        $state = $this->ReadPropertyString('WarningState');
        $county = $this->ReadPropertyString('WarningCounty');
        $community = $this->ReadPropertyString('WarningCommunity');
        $map = $this->ReadPropertyString('MapSelected');

        // Debug output
        $this->LogDebug(__FUNCTION__, 'type=' . $type . ', state=' . $state . ', county=' . $county . ', community=' . $community);

        // Check properties
        if ($state == 'null') {
            $county = 'null';
        }
        if ($county == 'null') {
            $community = 'null';
        }

        // Options & visibility of the warning area
        $options = $this->GetWarningStates($type);
        $this->ModifyFormElement($form['elements'], 'WarningState', function (array &$element) use ($options): void
        {
            $element['options'] = $options;
        });
        $options = $this->GetWarningCounties($type, $state);
        $visible = ($state != 'null');
        $this->ModifyFormElement($form['elements'], 'WarningCounty', function (array &$element) use ($options, $visible): void
        {
            $element['options'] = $options;
            $element['visible'] = $visible;
        });
        $options = $this->GetWarningCommunities($type, $state, $county);
        $visible = ($type == 8 && $county != 'null');
        $this->ModifyFormElement($form['elements'], 'WarningCommunity', function (array &$element) use ($options, $visible): void
        {
            $element['options'] = $options;
            $element['visible'] = $visible;
        });

        // Enable map settings
        $enabled = ($map != '00');
        foreach (self::MAP_FIELDS as $field) {
            $this->ModifyFormElement($form['elements'], $field, function (array &$element) use ($enabled): void
            {
                $element['enabled'] = $enabled;
            });
        }

        // Version
        $ins = IPS_GetInstance($this->InstanceID);
        $mod = IPS_GetModule($ins['ModuleInfo']['ModuleID']);
        $lib = IPS_GetLibrary($mod['LibraryID']);
        $version = sprintf('v%s.%d', $lib['Version'], $lib['Build']);
        $this->ModifyFormElement($form['actions'], 'Version', function (array &$element) use ($version): void
        {
            $element['caption'] = $version;
        });

        return json_encode($form);
    }

    /**
     * Is executed when "Apply" is pressed on the configuration page and immediately after the instance has been created.
     *
     * @return void
     */
    public function ApplyChanges(): void
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
        $isoMfcActiv = $this->ReadPropertyBoolean('ISOMovForecastActivated');
        $isoMfcIdent = $this->ReadPropertyString('ISOMovForecastIdent');
        $isoMfcStyle = $this->ReadPropertyString('ISOMovForecastStyle');
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
        $varLegend = $this->ReadPropertyBoolean('LegendVariable');
        $timeUpdate = $this->ReadPropertyInteger('UpdateInterval');

        // Debug
        $this->LogDebug(__FUNCTION__, 'Type=' . $warnType . ', State=' . $warnState . ', County=' . $warnCounty . ', Community=' . $warnCommunity .
                        ', Tmp=' . $tmpActiv . ', Img=' . $imgActiv . ', Mov=' . $movActiv);

        // Profile
        $level = $this->TranslatePresentation(self::UWW_PRESENTATION_LEVEL, 'INTERVALS', 'ConstantValue');

        // Maintain variables
        $this->MaintainVariable('Table', $this->Translate('Warning messages'), VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 1, true);
        $this->MaintainVariable('Text', $this->Translate('Warning text'), VARIABLETYPE_STRING, self::UWW_PRESENTATION_TEXT, 2, $varText == 1);

        // - Map
        $this->MaintainVariable('Map', $this->Translate('Storm map'), VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 3, $mapSelected != '00');

        // - Images & Movie
        $this->MaintainVariable('ActTemp', $this->Translate('Current temperatures'), VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 21, $tmpActiv);
        if ($tmpActiv) {
            $src = str_replace('<STATE>', $tmpIdent, self::DWD_LINKS['TEMP']);
            $val = '<div style="' . $tmpStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ActTemp', $val);
        }
        $this->MaintainVariable('ImgRadar', $this->Translate('Precipitation radar image'), VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 22, $imgActiv);
        if ($imgActiv) {
            $src = str_replace('<STATE>', $imgIdent, self::DWD_LINKS['RADAR']);
            $val = '<div style="' . $imgStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ImgRadar', $val);
        }
        $this->MaintainVariable('MovRadar', $this->Translate('Precipitation radar film'), VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 23, $movActiv);
        if ($movActiv) {
            $src = str_replace('<STATE>', $movIdent, self::DWD_LINKS['MOVIE']);
            $val = '<div style="' . $movStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('MovRadar', $val);
        }
        $this->MaintainVariable('ISOActTemp', $this->Translate('Current temperatures') . ' (' . $isoTmpIdent . ')', VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 31, $isoTmpActiv);
        if ($isoTmpActiv) {
            $src = str_replace('<STATE>', 'brd', self::DWD_LINKS['TEMP']);
            $val = '<div style="' . $isoTmpStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ISOActTemp', $val);
        }
        $this->MaintainVariable('ISOImgRadar', $this->Translate('Precipitation radar image') . ' (' . $isoImgIdent . ')', VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 32, $isoImgActiv);
        if ($isoImgActiv) {
            $src = str_replace('<STATE>', 'brd', self::DWD_LINKS['RADAR']);
            $val = '<div style="' . $isoImgStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ISOImgRadar', $val);
        }
        $this->MaintainVariable('ISOMovRadar', $this->Translate('Precipitation radar film') . ' (' . $isoMovIdent . ')', VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 33, $isoMovActiv);
        if ($isoMovActiv) {
            $src = str_replace('<STATE>', 'brd', self::DWD_LINKS['MOVIE']);
            $val = '<div style="' . $isoMovStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ISOMovRadar', $val);
        }
        $this->MaintainVariable('ISOMovForecast', $this->Translate('Precipitation radar forecast') . ' (' . $isoMfcIdent . ')', VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 34, $isoMfcActiv);
        if ($isoMfcActiv) {
            $src = self::DWD_LINKS['FORECAST'];
            $val = '<div style="' . $isoMfcStyle . '"><img src="' . $src . '" style="height: 100%; width: 100%; object-fit: contain" /></div>';
            $this->SetValueString('ISOMovForecast', $val);
        }

        // - Indicator
        $this->MaintainVariable('Level', $this->Translate('Warning level'), VARIABLETYPE_INTEGER, $level, 0, $varWarning);

        // - Legend
        $this->MaintainVariable('Legend', $this->Translate('Legend'), VARIABLETYPE_STRING, self::UWW_PRESENTATION_HTML, 4, $varLegend);
        if ($varLegend) {
            $val = '<style type="text/css">' . $this->ReadPropertyString('LegendStyle') . "</style>\n<body>" . $this->ReadPropertyString('LegendHtml') . '</body>';
            $this->SetValueString('Legend', $val);
        }

        // Status
        if (($warnState == 'null') || ($warnCounty == 'null') || (($warnType == 8) && ($warnCommunity == 'null'))) {
            $this->LogDebug(__FUNCTION__, 'Status 104: Type=' . $warnType . ', State=' . $warnState . ', County=' . $warnCounty . ', Community=' . $warnCommunity);
            $this->SetStatus(104);
            $this->SetTimerInterval('UpdateWeatherWarning', 0);
            $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
            return;
        }

        // Timer
        $this->SetTimerInterval('UpdateWeatherWarning', 60 * 1000 * $timeUpdate);

        // All okay
        $this->SetStatus(102);

        // Update immediately, at system start only after the kernel is ready
        if (IPS_GetKernelRunlevel() == KR_READY) {
            $this->Update();
        } else {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
        }
    }

    /**
     * The content of the function can be overwritten in order to carry out own reactions to certain messages.
     * The function is only called for registered MessageIDs/SenderIDs combinations.
     *
     * @param int $timestamp Continuous counter timestamp
     * @param int $sender Sender ID
     * @param int $message ID of the message
     * @param array<mixed> $data Data of the message
     *
     * @return void
     */
    public function MessageSink(int $timestamp, int $sender, int $message, array $data): void
    {
        if ($message == IPS_KERNELSTARTED) {
            $this->UnregisterMessage(0, IPS_KERNELSTARTED);
            $this->Update();
        }
    }

    /**
     * Is called when, for example, a button is clicked in the visualization.
     *
     * @param string $ident Ident of the variable
     * @param mixed $value The value to be set
     * @return void
     */
    public function RequestAction(string $ident, mixed $value): void
    {
        // Debug output
        $this->LogDebug('RequestAction', $ident . ' => ' . $value);
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
            case 'ResetHtml':
                $this->ResetHtml($value);
                break;
            case 'ResetStyle':
                $this->ResetStyle($value);
                break;
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * UWW_Update($id);
     *
     * @return void
     */
    public function Update(): void
    {
        // Check instance state
        $status = $this->GetStatus();
        if (($status != 102) && ($status != self::STATUS_FETCH_FAILED)) {
            $this->LogDebug(__FUNCTION__, 'Status: Instance is not active.');
            return;
        }
        // TimeStamp
        $now = time();
        // Request Warning Infos
        $warnings = $this->FetchWarnings();
        // A failed request must never be shown as "no warnings"
        if ($warnings === null) {
            $this->UpdateFromCache($now);
            return;
        }
        // Remember the last successful result
        $this->SetBuffer('Warnings', json_encode(['time' => $now, 'data' => $warnings]));
        if ($status == self::STATUS_FETCH_FAILED) {
            $this->LogMessage($this->Translate('Weather data could be loaded again.'), KL_MESSAGE);
            $this->SetStatus(102);
        }
        // Update level
        $this->UpdateLevel($warnings, $now);
        // Warning messages (table)
        $this->UpdateTable($warnings, $now);
        // Warning messages (text & notifications)
        $this->UpdateText($warnings, $now, true);
        // Storm map
        $this->UpdateMap($warnings, $now);
        // Tile visualization
        $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
    }

    /**
     * If the HTML-SDK is to be used, this function must be overwritten in order to return the HTML content.
     *
     * @return string Initial display of a representation via HTML SDK
     */
    public function GetVisualizationTile(): string
    {
        // Set the values when loading, analogous to changes at runtime (json_encode quotes the JSON string for JS)
        $initialHandling = '<script>handleMessage(' . json_encode($this->GetFullUpdateMessage()) . ');</script>';
        // Static HTML from file, handleMessage is defined there
        $module = file_get_contents(__DIR__ . '/module.html');
        return $module . $initialHandling;
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * UWW_WarningInfo($id);
     *
     * @return string all extracted warning information as json
     */
    public function WarningInfo(): string
    {
        // Check instance state
        $status = $this->GetStatus();
        if (($status != 102) && ($status != self::STATUS_FETCH_FAILED)) {
            $this->LogDebug(__FUNCTION__, 'Status: Instance is not active.');
            return json_encode([]);
        }
        // Request data (empty list on error, as before)
        return json_encode($this->FetchWarnings() ?? []);
    }

    /**
     * Select another warning area type.
     *
     * @param string $value Type (1,2,4,5,8,9).
     *
     * @return void
     */
    protected function OnWarningType(string $value): void
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
     *
     * @return void
     */
    protected function OnWarningState(string $value): void
    {
        $this->LogDebug(__FUNCTION__, $value);
        $data = json_decode($value, true);
        if (!is_array($data) || !isset($data['type'], $data['state'])) {
            return;
        }
        // County Options
        $this->UpdateFormField('WarningCounty', 'options', json_encode($this->GetWarningCounties((string) $data['type'], (string) $data['state'])));
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
     *
     * @return void
     */
    protected function OnWarningCounty(string $value): void
    {
        $this->LogDebug(__FUNCTION__, $value);
        $data = json_decode($value, true);
        if (!is_array($data) || !isset($data['type'], $data['state'], $data['county'])) {
            return;
        }
        // Community Options (only Type 8)
        if ($data['type'] == 8) {
            $this->UpdateFormField('WarningCommunity', 'options', json_encode($this->GetWarningCommunities((string) $data['type'], (string) $data['state'], (string) $data['county'])));
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
     *
     * @return void
     */
    protected function OnWarningMap(string $value): void
    {
        $this->LogDebug(__FUNCTION__, $value);
        // Enable?
        $enable = ($value != '00');
        foreach (self::MAP_FIELDS as $field) {
            $this->UpdateFormField($field, 'enabled', $enable);
        }
        // value
        $this->UpdateFormField('MapArea', 'value', 'Warngebiete_Kreise');
        $this->UpdateFormField('MapBackground', 'value', 'transparent');
        $this->UpdateFormField('MapWidth', 'value', self::DWD_GEO_MAPS[$value][0]);
        $this->UpdateFormField('MapHeight', 'value', self::DWD_GEO_MAPS[$value][1]);
        $this->UpdateFormField('MapWest', 'value', self::DWD_GEO_MAPS[$value][2]);
        $this->UpdateFormField('MapSouth', 'value', self::DWD_GEO_MAPS[$value][3]);
        $this->UpdateFormField('MapEast', 'value', self::DWD_GEO_MAPS[$value][4]);
        $this->UpdateFormField('MapNorth', 'value', self::DWD_GEO_MAPS[$value][5]);
    }

    /**
     * Reset to the default html definition.
     *
     * @param string $value Html name.
     *
     * @return void
     */
    protected function ResetHtml(string $value): void
    {
        $this->LogDebug(__FUNCTION__, $value);
        // value
        $this->UpdateFormField($value, 'value', self::HTML_LEGEND);
    }

    /**
     * Reset to the default css definition.
     *
     * @param string $value Syle name.
     *
     * @return void
     */
    protected function ResetStyle(string $value): void
    {
        $this->LogDebug(__FUNCTION__, $value);
        // value
        $this->UpdateFormField($value, 'value', self::CSS_STYLES[$value]);
    }

    /**
     * Returns for the dropdown menu the selectable states for the warning type.
     *
     * @param string $type Warning type Identifier
     *
     * @return array<int,array{caption:string,value:string}> List of states.
     */
    protected function GetWarningStates(string $type): array
    {
        $this->LogDebug(__FUNCTION__, $type);
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
     *
     * @return array<int,array{caption:string,value:string}> List of counties.
     */
    protected function GetWarningCounties(string $type, string $state): array
    {
        $this->LogDebug(__FUNCTION__, $type . ' => ' . $state);
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
     *
     * @return array<int,array{caption:string,value:string}> List of communities.
     */
    protected function GetWarningCommunities(string $type, string $state, string $county): array
    {
        $this->LogDebug(__FUNCTION__, $type . ' => ' . $state . ' => ' . $county);
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
     * Requests the warnings of the configured warn cell from the DWD.
     *
     * @return array<mixed>|null List of warnings, null if the request failed or the response is invalid.
     */
    private function FetchWarnings(): ?array
    {
        // Setup Warning
        $warnType = $this->ReadPropertyString('WarningType');
        $warnCellID = $this->ReadPropertyString('WarningCounty');
        if ($warnType == 8) {
            $warnCellID = $this->ReadPropertyString('WarningCommunity');
        }
        // Request data
        $json = $this->RequestData($this->BuildURL($warnType, $warnCellID));
        if ($json === false) {
            $this->LogDebug(__FUNCTION__, 'ERROR LOAD DATA');
            return null;
        }
        // Validate data
        $geo = json_decode($json, true);
        if (!is_array($geo) || !isset($geo['features']) || !is_array($geo['features'])) {
            $this->LogDebug(__FUNCTION__, 'ERROR INVALID DATA');
            return null;
        }
        // Extract data
        return $this->PrepareWarnings($json);
    }

    /**
     * Shows the last successfully loaded warnings after a failed request.
     * Expired warnings are removed, notifications are not sent.
     *
     * @param int $ts Timestamp
     *
     * @return void
     */
    private function UpdateFromCache(int $ts): void
    {
        // Report the failure only once
        if ($this->GetStatus() != self::STATUS_FETCH_FAILED) {
            $this->LogMessage($this->Translate('Weather data could not be loaded, the last known warnings are displayed.'), KL_WARNING);
            $this->SetStatus(self::STATUS_FETCH_FAILED);
        }
        // Nothing cached yet (e.g. after a restart), keep level and text untouched
        $cache = $this->GetCachedWarnings($ts);
        if ($cache['time'] == 0) {
            $this->LogDebug(__FUNCTION__, 'No cached warnings available.');
            $this->UpdateTable([], $ts, 0);
            $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
            return;
        }
        // Expired warnings are already removed (warnings without end stay)
        $warnings = $cache['data'];
        $this->LogDebug(__FUNCTION__, 'Still valid: ' . count($warnings));
        $this->UpdateLevel($warnings, $ts);
        $this->UpdateTable($warnings, $ts, $cache['time']);
        $this->UpdateText($warnings, $ts, false);
        $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
    }

    /**
     * Returns the cached warnings which are still valid and the time of the last successful request.
     *
     * @param int $ts Timestamp
     *
     * @return array{time:int,data:array<int,array<string,mixed>>} Time of the request (0 = none) and valid warnings
     */
    private function GetCachedWarnings(int $ts): array
    {
        $cache = json_decode($this->GetBuffer('Warnings'), true);
        if (!is_array($cache) || !isset($cache['time'], $cache['data']) || !is_array($cache['data'])) {
            return ['time' => 0, 'data' => []];
        }
        $data = array_values(array_filter($cache['data'], fn ($value) => empty($value['END']) || (strtotime($value['END']) > $ts)));
        return ['time' => (int) $cache['time'], 'data' => $data];
    }

    /**
     * Builds the complete data of the tile visualization.
     *
     * @return string JSON encoded tile data
     */
    private function GetFullUpdateMessage(): string
    {
        $status = $this->GetStatus();
        $data = ['id' => $this->InstanceID];
        if (($status != 102) && ($status != self::STATUS_FETCH_FAILED)) {
            $data['inactive'] = $this->Translate('Please select the warning area in the configuration!');
            return json_encode($data);
        }
        $now = time();
        $cache = $this->GetCachedWarnings($now);
        $level = $this->ComputeLevel($cache['data']);
        [$color, $text] = $this->GetLevelInfo($level);
        $warnings = [];
        foreach ($cache['data'] as $value) {
            $warnings[] = [
                'icon'        => $this->ExtractIcon($value),
                'headline'    => (string) ($value['HEADLINE'] ?? ''),
                'period'      => $this->FormatPeriod($value),
                'description' => (string) ($value['DESCRIPTION'] ?? ''),
            ];
        }
        $count = count($warnings);
        $data['level'] = $level;
        $data['color'] = sprintf('#%06X', $color);
        $data['text'] = $text;
        $data['count'] = $count;
        $data['unit'] = ($count == 1) ? $this->Translate('Message') : $this->Translate('Messages');
        $data['warnings'] = $warnings;
        $data['updated'] = ($cache['time'] > 0) ? sprintf($this->Translate('on %s'), $this->FormatDateTime($cache['time'])) : '';
        $data['none'] = $this->Translate('No warnings available!');
        $data['failed'] = null;
        if ($status == self::STATUS_FETCH_FAILED) {
            $data['failed'] = [
                'title' => $this->Translate('Update failed'),
                'time'  => ($cache['time'] > 0) ? sprintf($this->Translate('Last successful update on %s'), $this->FormatDateTime($cache['time'])) : $this->Translate('No data available yet'),
                'text'  => $this->Translate('The weather data could not be loaded. Expired warnings are hidden automatically.'),
            ];
        }
        $data['map'] = $this->ReadPropertyString('MapSelected');
        $data['maps'] = $this->GetTileMaps($now);
        $data['pinColor'] = sprintf('#%06X', max(0, $this->ReadPropertyInteger('MapPinColor')));
        $data['buttons'] = [
            'legend' => $this->ReadPropertyString('TileLegendButton'),
            'areas'  => $this->ReadPropertyString('TileAreaButton'),
        ];
        return json_encode($data);
    }

    /**
     * Returns color and translated name of a warning level (from the level presentation).
     *
     * @param int $level Warning level
     *
     * @return array{0:int,1:string} Color and name
     */
    private function GetLevelInfo(int $level): array
    {
        $intervals = json_decode(self::UWW_PRESENTATION_LEVEL['INTERVALS'], true);
        foreach ($intervals as $interval) {
            if ($interval['IntervalMinValue'] == $level) {
                return [(int) $interval['ColorValue'], $this->Translate($interval['ConstantValue'])];
            }
        }
        return [(int) $intervals[0]['ColorValue'], $this->Translate($intervals[0]['ConstantValue'])];
    }

    /**
     * Returns all selectable map areas for the tile, the configured area with the user settings.
     *
     * @param int $ts Timestamp
     *
     * @return array<int,array{id:string,name:string,url:string,w:int,h:int,pin:array{x:int,y:int}|null}> Map areas
     */
    private function GetTileMaps(int $ts): array
    {
        $selected = $this->ReadPropertyString('MapSelected');
        if ($selected == '00') {
            return [];
        }
        $pin = $this->ReadPropertyBoolean('MapPinActivated');
        $maps = [];
        foreach ($this->GetMapNames() as $id => $name) {
            if (!isset(self::DWD_GEO_MAPS[$id])) {
                continue;
            }
            if ($id == $selected) {
                $w = $this->ReadPropertyInteger('MapWidth');
                $h = $this->ReadPropertyInteger('MapHeight');
                $box = [$this->ReadPropertyFloat('MapWest'), $this->ReadPropertyFloat('MapSouth'), $this->ReadPropertyFloat('MapEast'), $this->ReadPropertyFloat('MapNorth')];
            } else {
                [$w, $h, $west, $south, $east, $north] = self::DWD_GEO_MAPS[$id];
                $box = [$west, $south, $east, $north];
            }
            $maps[] = [
                'id'   => (string) $id,
                'name' => $name,
                'url'  => $this->BuildMapUrl((string) $id, $w, $h, $box, $ts),
                'w'    => $w,
                'h'    => $h,
                'pin'  => $pin ? $this->CalculatePinPosition($box, $w, $h) : null,
            ];
        }
        return $maps;
    }

    /**
     * Returns the names of the map areas in the order of the configuration form.
     *
     * @return array<string,string> Map area id => name
     */
    private function GetMapNames(): array
    {
        $names = [];
        $form = json_decode((string) file_get_contents(__DIR__ . '/form.json'), true);
        $this->ModifyFormElement($form['elements'], 'MapSelected', function (array &$element) use (&$names): void
        {
            foreach ($element['options'] as $option) {
                if ($option['value'] != '00') {
                    $names[(string) $option['value']] = $this->Translate($option['caption']);
                }
            }
        });
        return $names;
    }

    /**
     * Updates the level indicator
     *
     * @param array<int,array{LEVEL?:int|string,CATEGORY?:string}> $warnings Array of warning data.
     * @param int $ts Timestamp
     *
     * @return void
     */
    private function UpdateLevel(array $warnings, int $ts): void
    {
        $varWarning = $this->ReadPropertyBoolean('IndicatorVariable');
        $this->LogDebug(__FUNCTION__, 'IndicatorVariable: ' . $varWarning);
        if ($varWarning) {
            $this->SetValueInteger('Level', $this->ComputeLevel($warnings));
        }
    }

    /**
     * Determines the highest warning level (health warnings get an offset of 10).
     *
     * @param array<int,array{LEVEL?:int|string,CATEGORY?:string}> $warnings Array of warning data.
     *
     * @return int Warning level
     */
    private function ComputeLevel(array $warnings): int
    {
        $level = 0;
        $offset = false;
        foreach ($warnings as $value) {
            $current = (int) ($value['LEVEL'] ?? 0);
            if ($current > $level) {
                $level = $current;
                // Offset for health warnings (UV, heat)
                $offset = (($value['CATEGORY'] ?? '') === self::DWD_CATEGORY['Health']);
            }
        }
        return $offset ? $level + 10 : $level;
    }

    /**
     * Builds the Text variable for the warnings.
     *
     * @param array<int,array{
     *     AREA?:string,
     *     WARNCELLID?:string,
     *     SENT?:string,
     *     STATUS?:string,
     *     TYPE?:string,
     *     CATEGORY?:string,
     *     EVENT?:string,
     *     URGENCY?:string,
     *     SEVERITY?:string,
     *     LEVEL?:int|string,
     *     CERTAINTY?:string,
     *     CODE?:string,
     *     GROUP?:string,
     *     TIMESTAMP?:string,
     *     START?:string,
     *     END?:string,
     *     HEADLINE?:string,
     *     DESCRIPTION?:string,
     *     INSTRUCTION?:string
     * }> $warnings Array of warning data.
     * @param int $ts Timestamp
     * @param bool $notify Send messages to dashboard and visualization
     *
     * @return void
     */
    private function UpdateText(array $warnings, int $ts, bool $notify): void
    {
        // Selected?
        $isDashboard = $notify ? $this->ReadPropertyInteger('DashboardMessage') : 0;
        $isNotify = $notify ? $this->ReadPropertyInteger('NotificationMessage') : 0;
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
        // separator
        $separator = $this->ReadPropertyString('TextSeparator');
        // webfront id
        $webfront = $this->ReadPropertyInteger('InstanceWebfront');
        // message script
        $script = $this->ReadPropertyInteger('ScriptMessage');
        $hasScript = ($script != 0) && IPS_ScriptExists($script);
        // message key
        $key = $this->ReadPropertyString('MessageKey');
        // warnings and message keys of the last run
        $sent = $this->GetSentWarnings();
        $ids = [];
        $keys = [];

        $lines = [];
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
            // identify the warning, only new warnings are notified
            $id = md5($output . '|' . ($value['START'] ?? '') . '|' . ($value['END'] ?? ''));
            $ids[] = $id;
            $isNew = !in_array($id, $sent['ids']);
            // send to dashboard
            if ($isDashboard && $hasScript && ((int) ($value['LEVEL'] ?? 0) >= $levelDashboard)) {
                $params = ['action' => 'add', 'text' => $output, 'removable' => true, 'type' => 2, 'image' => 'WindSpeed'];
                if ($time > 0) {
                    $params['expires'] = time() + ($time * 60);
                }
                // Older message scripts ignore the key, so it is only passed if configured
                if ($key !== '') {
                    // Each warning gets its own key, so simultaneous warnings do not overwrite each other
                    $params['key'] = $key . '.' . substr($id, 0, 8);
                    $keys[] = $params['key'];
                }
                if ($isNew) {
                    IPS_RunScriptWaitEx($script, $params);
                }
            }
            // send to webfront
            if ($isNotify && $isNew && $webfront != 0) {
                if ((int) ($value['LEVEL'] ?? 0) >= $levelNotify) {
                    $sound = $this->ReadPropertyString('NotificationSound');
                    if ($this->IsWebFrontVisuInstance($webfront)) {
                        //TODO:Update if added
                        /** @phpstan-ignore-next-line */
                        WFC_PushNotification($webfront, $this->Translate('Weather Warning'), $output, $sound, 0);
                    }
                    if ($this->IsTileVisuInstance($webfront)) {
                        //TODO:Update if added
                        /** @phpstan-ignore-next-line */
                        VISU_PostNotificationEx($webfront, $this->Translate('Weather Warning'), $output, 'wind-warning', $sound, 0);
                    }
                }
            }
            // collect for variable
            $lines[] = $output;
        }
        // remember notified warnings and remove messages of ended warnings (not with cached data)
        if ($notify) {
            if ($hasScript) {
                foreach (array_diff($sent['keys'], $keys) as $old) {
                    $this->LogDebug(__FUNCTION__, 'Remove message: ' . $old);
                    IPS_RunScriptWaitEx($script, ['action' => 'remove', 'key' => $old]);
                }
            }
            $this->SetBuffer('SentWarnings', json_encode(['ids' => $ids, 'keys' => $keys]));
        }
        // write to variable
        if ($isVariable) {
            $line = '';
            if (empty($lines)) {
                $line = $this->Translate('None');
            } else {
                $line = implode($separator, $lines);
            }
            $this->SetValueString('Text', $line);
        }
    }

    /**
     * Returns the warnings and message keys of the last notification run.
     *
     * @return array{ids:array<int,string>,keys:array<int,string>} Notified warning ids and used message keys.
     */
    private function GetSentWarnings(): array
    {
        $sent = json_decode($this->GetBuffer('SentWarnings'), true);
        return [
            'ids'  => (is_array($sent) && is_array($sent['ids'] ?? null)) ? $sent['ids'] : [],
            'keys' => (is_array($sent) && is_array($sent['keys'] ?? null)) ? $sent['keys'] : [],
        ];
    }

    /**
     * Builds the HTML Table for the warnings.
     *
     * @param array<int,array{
     *     AREA?:string,
     *     WARNCELLID?:string,
     *     SENT?:string,
     *     STATUS?:string,
     *     TYPE?:string,
     *     CATEGORY?:string,
     *     EVENT?:string,
     *     URGENCY?:string,
     *     SEVERITY?:string,
     *     LEVEL?:int|string,
     *     CERTAINTY?:string,
     *     CODE?:string,
     *     GROUP?:string,
     *     TIMESTAMP?:string,
     *     START?:string,
     *     END?:string,
     *     HEADLINE?:string,
     *     DESCRIPTION?:string,
     *     INSTRUCTION?:string
     * }> $warnings Array of warning data.
     * @param int $ts Timestamp
     * @param int|null $cached Timestamp of the cached data after a failed request (0 = no data), null for current data
     *
     * @return void
     */
    private function UpdateTable(array $warnings, int $ts, ?int $cached = null): void
    {
        // Style
        $html = '<style type="text/css">' . $this->ReadPropertyString('WarningStyle') . "\n</style>";
        // Body
        $html .= "\n<body>";
        $html .= "\n<table class='uww'>";
        // Failed request
        if ($cached !== null) {
            $head = $this->Translate('Update failed');
            $time = ($cached > 0) ? sprintf($this->Translate('Last successful update on %s'), $this->FormatDateTime($cached)) : $this->Translate('No data available yet');
            $desc = $this->Translate('The weather data could not be loaded. Expired warnings are hidden automatically.');
            $html .= '<tr>';
            $html .= '<td class=\'img\'><div style=\'font-size: 2em; text-align: center;\'>⚠️</div></td>';
            $html .= '<td class=\'txt\'><div class=\'hl\'>' . $head . '</div><div class=\'ts\'>' . $time . '</div><div class=\'desc\'>' . $desc . '</div></td>';
            $html .= '</tr>';
        }
        // Exist Warnings
        $count = 0;
        foreach ($warnings as $value) {
            $url = $this->ExtractIcon($value);
            $time = $this->FormatPeriod($value);
            $html .= '<tr>';
            $html .= '<td class=\'img\'>' . (($url !== '') ? '<img class=\'warn\' src=\'' . $url . '\' />' : '') . '</td>';
            $html .= '<td class=\'txt\'><div class=\'hl\'>' . ($value['HEADLINE'] ?? '') . '</div><div class=\'ts\'>' . $time . '</div><div class=\'desc\'>' . ($value['DESCRIPTION'] ?? '') . '</div></td>';
            $html .= '</tr>';
            $count++;
        }
        // Count Warnings (only with current data)
        if (($count == 0) && ($cached === null)) {
            $head = $this->Translate('Last update');
            $time = sprintf($this->Translate('on %s'), $this->FormatDateTime($ts));
            $desc = $this->Translate('No warnings available!');
            $html .= '<tr>';
            $html .= '<td class=\'img\'><div title=\'' . $this->Translate('No warnings') . '\'>' . self::ICON_NO_WARNING . '</div></td>';
            $html .= '<td class=\'txt\'><div class=\'hl\'>' . $head . '</div><div class=\'ts\'>' . $time . '</div><div class=\'desc\'>' . $desc . '</div></td>';
            $html .= '</tr>';
        }
        $html .= "\n</table>\n</body>";
        // Write HTML
        $this->SetValueString('Table', $html);
    }

    /**
     * Updates the storm map
     *
     * @param array<int,array{
     *     AREA?:string,
     *     WARNCELLID?:string,
     *     SENT?:string,
     *     STATUS?:string,
     *     TYPE?:string,
     *     CATEGORY?:string,
     *     EVENT?:string,
     *     URGENCY?:string,
     *     SEVERITY?:string,
     *     LEVEL?:int|string,
     *     CERTAINTY?:string,
     *     CODE?:string,
     *     GROUP?:string,
     *     TIMESTAMP?:string,
     *     START?:string,
     *     END?:string,
     *     HEADLINE?:string,
     *     DESCRIPTION?:string,
     *     INSTRUCTION?:string
     * }> $warnings Array of warning data.
     * @param int $ts Timestamp
     *
     * @return void
     */
    private function UpdateMap(array $warnings, int $ts): void
    {
        $mapSelected = $this->ReadPropertyString('MapSelected');
        $this->LogDebug(__FUNCTION__, 'MAP: ' . $mapSelected);

        if ($mapSelected == '00') {
            // Nothing to do
            return;
        }
        // User selected Values
        $mapWidth = $this->ReadPropertyInteger('MapWidth');
        $mapHeight = $this->ReadPropertyInteger('MapHeight');
        $mapBox = [
            $this->ReadPropertyFloat('MapWest'),
            $this->ReadPropertyFloat('MapSouth'),
            $this->ReadPropertyFloat('MapEast'),
            $this->ReadPropertyFloat('MapNorth'),
        ];
        $url = $this->BuildMapUrl($mapSelected, $mapWidth, $mapHeight, $mapBox, $ts);
        $this->LogDebug(__FUNCTION__, $url);
        // Build css
        $col = $this->ReadPropertyInteger('MapPinColor');
        $css = $this->ReadPropertyString('MapStyle');
        $css = str_replace('{{color}}', sprintf('#%06X', max(0, $col)), $css);
        // Position of the own location (null if deactivated or not determinable)
        $pin = null;
        if ($this->ReadPropertyBoolean('MapPinActivated')) {
            $pin = $this->CalculatePinPosition($mapBox, $mapWidth, $mapHeight);
        }
        // Build html
        $html = '<style type="text/css">' . $css . "\n</style>";
        $html .= "\n<body>";
        $html .= '<div id="uwwMap">';
        $map = $this->Translate('Map');
        $html .= '<img id="uwwImg" src="' . $url . '" alt="' . $map . '" title="' . $map . '" />';
        if ($pin !== null) {
            $html .= '<div id="uwwPin"></div>';
        }
        $html .= '</div>';
        if ($pin !== null) {
            $html .= '<script>';
            $html .= 'function handler() {';
            $html .= 'var ow = document.getElementById("uwwImg").offsetWidth;';
            $html .= 'var oh = document.getElementById("uwwImg").offsetHeight;';
            $html .= 'var width = ' . $mapWidth . ';';
            $html .= 'var height = ' . $mapHeight . ';';
            $html .= 'var x = ' . $pin['x'] . ';';
            $html .= 'var y = ' . $pin['y'] . ';';
            $html .= 'var ox = Math.ceil(ow/width*x);';
            $html .= 'var oy = Math.ceil(oh/height*y);';
            $html .= 'var pin =  document.getElementById("uwwPin");';
            $html .= 'pin.style.left= ox+\'px\';';
            $html .= 'pin.style.top= oy+\'px\';';
            $html .= '}';
            $html .= 'document.getElementById("uwwImg").addEventListener(\'load\', handler, false);';
            $html .= '</script>';
        }
        $html .= "\n</body>";
        $this->SetValueString('Map', $html);
    }

    /**
     * Builds the WMS request URL of the storm map.
     *
     * @param string $map Selected map area (state code, 99 = Germany)
     * @param int $w Image width
     * @param int $h Image height
     * @param array<int,float> $box Bounding box (west, south, east, north)
     * @param int $ts Timestamp (prevents caching)
     *
     * @return string WMS request URL
     */
    private function BuildMapUrl(string $map, int $w, int $h, array $box, int $ts): string
    {
        $mapArea = $this->ReadPropertyString('MapArea');
        $mapBkgd = $this->ReadPropertyString('MapBackground');
        // Prepare GET parameters
        $service = '?service=WMS';
        $version = '&version=1.3';
        $request = '&request=GetMap';
        $layers = '&layers=';
        $transparent = '&transparent=';
        $style = '&style=';
        $height = '&height=' . $h;
        $width = '&width=' . $w;
        $bbox = '&bbox=' . implode(',', array_map(fn ($c) => sprintf('%.6F', $c), $box));
        $srs = '&srs=EPSG:4326';
        $format = '&format=image/png';
        $filter = '&cql_filter=';
        $test = '&test=' . $ts;
        // Prepare layer & filter areas
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
        if ($map == '99') {
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
        } elseif ($map == '17') {
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
        } elseif ($map == '21') {
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
        } elseif ($map == '23') {
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
        } elseif ($map == '34') {
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
                $fa[] = 'WARNCELLID%20LIKE%20%278' . $map . '%25%27';
                $la[] = 'dwd:Warnungen_Gemeinden';
                $fa[] = 'WARNCELLID%20LIKE%20%278' . $map . '%25%27';
            } else {
                $la[] = 'dwd:Warngebiete_Kreise';
                $fa[] = 'WARNCELLID%20LIKE%20%279' . $map . '%25%27%20OR%20WARNCELLID%20LIKE%20%271' . $map . '%25%27';
                $la[] = 'dwd:Warnungen_Landkreise';
                $fa[] = 'GC_WARNCELLID%20LIKE%20%279' . $map . '%25%27%20OR%20GC_WARNCELLID%20LIKE%20%271' . $map . '%25%27';
            }
        }
        $layers .= implode(',', $la);
        $filter .= implode(';', $fa);
        // Build url
        $url = self::DWD_GEO_MAPSURL . $service . $version . $request . $layers . $transparent . $height . $width . $style . $bbox . $srs . $format . $filter . $test;
        return $url;
    }

    /**
     * Calculates the pixel position of the own location (Location Control) on the map.
     *
     * @param array<int,float> $box Bounding box (west, south, east, north)
     * @param int $width Image width
     * @param int $height Image height
     *
     * @return array{x:int,y:int}|null Pixel position, null if the location is unknown or outside the map.
     */
    private function CalculatePinPosition(array $box, int $width, int $height): ?array
    {
        $minLon = (float) $box[0];
        $minLat = (float) $box[1];
        $maxLon = (float) $box[2];
        $maxLat = (float) $box[3];
        if (($width <= 0) || ($height <= 0) || ($maxLon <= $minLon) || ($maxLat <= $minLat)) {
            $this->LogDebug(__FUNCTION__, 'Invalid map size or bounding box!');
            return null;
        }
        // Location Control (system instance, read only)
        $lc = IPS_GetInstanceListByModuleID('{45E97A63-F870-408A-B259-2933F7EABF74}');
        if (empty($lc)) {
            $this->LogDebug(__FUNCTION__, 'No Location Control found!');
            return null;
        }
        $pos = json_decode(IPS_GetProperty($lc[0], 'Location'), true);
        if (!is_array($pos) || !is_numeric($pos['longitude'] ?? null) || !is_numeric($pos['latitude'] ?? null)) {
            $this->LogDebug(__FUNCTION__, 'No valid location set!');
            return null;
        }
        $lon = (float) $pos['longitude'];
        $lat = (float) $pos['latitude'];
        if (($lon < $minLon) || ($lon > $maxLon) || ($lat < $minLat) || ($lat > $maxLat)) {
            $this->LogDebug(__FUNCTION__, 'Location is outside the map!');
            return null;
        }
        return [
            'x' => (int) ceil(($lon - $minLon) * $width / ($maxLon - $minLon)),
            'y' => (int) ceil(($maxLat - $lat) * $height / ($maxLat - $minLat)),
        ];
    }

    /**
     * Format a given warning array to a string.
     *
     * @param array{
     *     AREA?:string,
     *     WARNCELLID?:string,
     *     SENT?:string,
     *     STATUS?:string,
     *     TYPE?:string,
     *     CATEGORY?:string,
     *     EVENT?:string,
     *     URGENCY?:string,
     *     SEVERITY?:string,
     *     LEVEL?:int|string,
     *     CERTAINTY?:string,
     *     CODE?:string,
     *     GROUP?:string,
     *     TIMESTAMP?:string,
     *     START?:string,
     *     END?:string,
     *     HEADLINE?:string,
     *     DESCRIPTION?:string,
     *     INSTRUCTION?:string
     * } $value Weather warning data
     * @param string $format Format string (supports %L, %N, %T, %M, %D)
     *
     * @return string Formatted string
     */
    private function FormatWarning(array $value, string $format): string
    {
        $level = (int) ($value['LEVEL'] ?? 0);
        $output = str_replace('%L', $this->Translate(self::DWD_SEVERITY[$level][1] ?? 'None'), $format);
        $output = str_replace('%N', (string) $level, $output);
        $output = str_replace('%T', (string) ($value['TYPE'] ?? ''), $output);
        $output = str_replace('%M', (string) ($value['HEADLINE'] ?? ''), $output);
        $output = str_replace('%D', (string) ($value['DESCRIPTION'] ?? ''), $output);
        return $output;
    }

    /**
     * Formats the validity period of a warning.
     *
     * @param array{START?:string,END?:string} $value Weather warning data
     *
     * @return string Period like 'Mon, 23.Nov, 17:00 - Tue, 24.Nov, 12:00'
     */
    private function FormatPeriod(array $value): string
    {
        $start = empty($value['START']) ? false : strtotime($value['START']);
        $end = empty($value['END']) ? false : strtotime($value['END']);
        if (($start !== false) && ($end !== false)) {
            return sprintf($this->Translate('%s - %s'), $this->FormatDateTime($start), $this->FormatDateTime($end));
        }
        if ($start !== false) {
            return sprintf($this->Translate('from %s'), $this->FormatDateTime($start));
        }
        return '';
    }

    /**
     * Formats a timestamp like 'Mon, 23.Nov, 14:15' with translated day and month names.
     *
     * @param int $ts Timestamp
     *
     * @return string Formatted date and time
     */
    private function FormatDateTime(int $ts): string
    {
        return $this->Translate(date('D', $ts)) . ', ' . date('d.', $ts) . $this->Translate(date('M', $ts)) . ', ' . date('H:i', $ts);
    }
}