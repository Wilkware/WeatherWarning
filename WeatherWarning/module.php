<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/_traits.php';  // Generell funktions

// CLASS WeatherWarningModule
class WeatherWarningModule extends IPSModule
{
    use ProfileHelper;
    use EventHelper;
    use DebugHelper;

    /**
     * Create.
     */
    public function Create()
    {
        //Never delete this line!
        parent::Create();
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
        // Get Form
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
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
        // return true;
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * ALMANAC_Update($id);
     */
    public function Update()
    {
        // calculate next update interval
        //$this->UpdateTimerInterval('UpdateTimer', 0, 0, 1);
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