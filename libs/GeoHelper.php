<?php

/**
 * GeoHelper.php
 *
 * Part of the Trait-Libraray for IP-Symcon Modules.
 *
 * @package       traits
 * @author        Heiko Wilknitz <heiko@wilkware.de>
 * @copyright     2020 Heiko Wilknitz
 * @link          https://wilkware.de
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

/**
 * DWD GeoServer URL base prefix
 */
const DWD_GEO_BASEURL = 'https://maps.dwd.de/geoserver/dwd/ows?service=WFS&version=2.0.0&request=GetFeature&srsName=EPSG:4326&outputFormat=application/json';

/**
 * DWD GeoServer MAPS URL base prefix
 */
const DWD_GEO_MAPSURL = 'https://maps.dwd.de/geoserver/dwd/wms';

/**
 * DWD GeoServer URL type parameter
 */
const DWD_GEO_PRAMS = [
    1 => ['&typeName=dwd:Warnungen_Landkreise', '&CQL_FILTER=GC_WARNCELLID%20IN%20(\'<WARNCELLID>\')'],
    2 => ['&typeName=dwd:Warnungen_Binnenseen', '&CQL_FILTER=WARNCELLID%20IN%20(\'<WARNCELLID>\')'],
    4 => ['&typeName=dwd:Warnungen_See', '&CQL_FILTER=WARNCELLID%20IN%20(\'<WARNCELLID>\')'],
    5 => ['&typeName=dwd:Warnungen_Kueste', '&CQL_FILTER=WARNCELLID%20IN%20(\'<WARNCELLID>\')'],
    8 => ['&typeName=dwd:Warnungen_Gemeinden', '&CQL_FILTER=WARNCELLID%20IN%20(\'<WARNCELLID>\')'],
    9 => ['&typeName=dwd:Warnungen_Landkreise', '&CQL_FILTER=GC_WARNCELLID%20IN%20(\'<WARNCELLID>\')'],
];

/**
 * DWD MapServer
 */
const DWD_GEO_MAPS = [
    # NO
    '00' => [500, 500, 0.000000, 0.000000, 0.000000, 0.000000],     // Keine Karte
    # BL
    '01' => [500, 365, 7.868514, 53.359067, 11.313203, 55.057374],  // Schleswig-Holstein
    '02' => [500, 225, 8.421364, 53.394925, 10.324258, 53.964437],  // Hamburg
    '03' => [500, 400, 6.654584, 51.295415, 11.597698, 53.894151],  // Niedersachsen
    '04' => [500, 810, 8.481357, 53.010370, 8.983047, 53.606166],   // Bremen
    '05' => [500, 470, 5.865998, 50.322698, 9.447658, 52.531035],   // Nordrhein-Westfalen
    '06' => [500, 690, 7.773170, 49.394822, 10.234015, 51.654049],  // Hessen
    '07' => [500, 615, 6.117359, 48.966274, 8.508475, 50.940443],   // Rheinland-Pfalz
    '08' => [500, 570, 7.511393, 47.533800, 10.491823, 49.791374],  // Baden-Württemberg
    '09' => [500, 510, 8.977158, 47.270362, 13.835042, 50.564452],  // Bayern
    '10' => [500, 375, 6.358469, 49.113099, 7.403490, 49.639346],   // Saarland
    '11' => [500, 375, 13.088209, 52.341823, 13.760610, 52.669724], // Berlin
    '12' => [500, 470, 11.268166, 51.360662, 14.764710, 53.557950], // Brandenburg
    '13' => [500, 310, 10.593246, 53.115863, 14.412279, 54.684988], // Mecklenburg-Vorpommern
    '14' => [500, 355, 11.872308, 50.171541, 15.037743, 51.683140], // Sachsen
    '15' => [500, 625, 10.561475, 50.937997, 13.186560, 53.042131], // Sachsen-Anhalt
    '16' => [500, 390, 9.877844, 50.204233, 12.653196, 51.649067],  // Thüringen
    # MO
    '17' => [500, 615, 6.117359, 48.966274, 8.508475, 50.940443],   // Rheinland-Pfalz, Saarland
    '21' => [500, 365, 7.868514, 53.359067, 11.313203, 55.057374],  // Schleswig-Holstein, Hamburg
    '23' => [500, 470, 11.268166, 51.360662, 14.764710, 53.557950], // Berlin, Brandenburg
    '34' => [500, 400, 6.654584, 51.295415, 11.597698, 53.894151],  // Niedersachsen, Bremen
    # DE
    '99' => [500, 640, 5.876914, 47.270362, 15.037507, 55.044381],  // Deutschland
];

/**
 * DWD Event Codes
 */
const DWD_EVENT_CODE = [
    '11'  => 'Böen',
    '12'  => 'Wind',
    '13'  => 'Sturm',
    '14'  => 'Starkwind',
    '15'  => 'Sturm',
    '16'  => 'Schwerer Sturm',
    '22'  => 'Frost',
    '24'  => 'Glätte',
    '31'  => 'Gewitter',
    '33'  => 'Starkes Gewitter',
    '34'  => 'Starkes Gewitter',
    '36'  => 'Starkes Gewitter',
    '38'  => 'Starkes Gewitter',
    '40'  => 'Schweres Gewitter mit Orkanböen',
    '41'  => 'Schweres Gewitter mit extremen Orkanböen',
    '42'  => 'Schweres Gewitter mit heftigem Starkregen',
    '44'  => 'Schweres Gewitter mit Orkanböen und heftigem Starkregen',
    '45'  => 'Schweres Gewitter mit extremen Orkanböen und heftigem Starkregen',
    '46'  => 'Schweres Gewitter mit heftigem Starkregen und Hagel',
    '48'  => 'Schweres Gewitter mit Orkanböen, heftigem Starkregen und Hagel',
    '49'  => 'Schweres Gewitter mit extremen Orkanböen, heftigem Starkregen und Hagel',
    '51'  => 'Windböen',
    '52'  => 'Sturmböen',
    '53'  => 'Schwere Sturmböen',
    '54'  => 'Orkanartige Böen',
    '55'  => 'Orkanböen',
    '56'  => 'Extreme Orkanböen',
    '57'  => 'Starkwind',
    '58'  => 'Sturm',
    '59'  => 'Nebel',
    '61'  => 'Starkregen',
    '62'  => 'Heftiger Starkregen',
    '63'  => 'Dauerregen',
    '64'  => 'Ergiebiger Dauerregen',
    '65'  => 'Extrem ergiebiger Dauerregen',
    '66'  => 'Extrem heftiger Starkregen',
    '70'  => 'Leichter Schneefall',
    '71'  => 'Schneefall',
    '72'  => 'Starker Schneefall',
    '73'  => 'Extrem starker Schneefall',
    '74'  => 'Schneeverwehung',
    '75'  => 'Starke Schneeverwehung',
    '76'  => 'Extrem starke Schneeverwehung',
    '79'  => 'Leiterseilschwingungen',
    '82'  => 'Strenger Frost',
    '84'  => 'Glätte',
    '85'  => 'Glatteis',
    '87'  => 'Glatteis',
    '88'  => 'Tauwetter',
    '89'  => 'Starkes Tauwetter',
    '90'  => 'Gewitter',
    '91'  => 'Starkes Gewitter',
    '92'  => 'Schweres Gewitter',
    '93'  => 'Extremes Gewitter',
    '95'  => 'Schweres Gewitter mit extrem heftigem Starkregen und Hagel',
    '96'  => 'Extremes Gewitter mit Orkanböen, extrem heftigem Starkregen und Hagel',
    '98'  => 'Test-Warnung',
    '99'  => 'Test-Unwetterwarnung',
    '246' => 'UV-Index',
    '247' => 'Starke Hitze',
    '248' => 'Extreme Hitze',
];
/*
    '40' => 'VORABINFORMATION SCHWERES GEWITTER',
    '55' => 'VORABINFORMATION ORKANBÖEN',
    '65' => 'VORABINFORMATION HEFTIGER / ERGIEBIGER REGEN',
    '75' => 'VORABINFORMATION STARKER SCHNEEFALL / SCHNEEVERWEHUNG',
    '85' => 'VORABINFORMATION GLATTEIS',
    '89' => 'VORABINFORMATION STARKES TAUWETTER',
    '99' => 'TEST-VORABINFORMATION UNWETTER',
 */

/**
 * DWD Event Mapper
 */
const DWD_EVENT_MAP = [
    'gewitter'    => [31, 33, 34, 36, 38, 40, 41, 42, 44, 45, 46, 48, 49, 90, 91, 92, 93, 95, 96],
    'wind'        => [11, 12, 13, 14, 15, 16, (31), 33, (34), 36, 38, 40, 41, 44, 45, (46), 48, 49, 51, 52, 53, 54, 55, 56, 57, 58, 74, 75, 76, 79, 96],
    'regen'       => [34, 36, 38, (40), (41), 42, 44, 45, 46, 48, 49, 61, 62, 63, 64, 65, 66, 88, 89, 95, 96],
    'schnee'      => [70, 71, 72, 73,  74, 75, 76],
    'nebel'       => [59],
    'frost'       => [22, 82, 83],
    'glaette'     => [24, 84, 85, 87],
    'tauwetter'   => [88, 89],
    'uv'          => [246],
    'hitze'       => [247, 248],
];

/**
 * Status of alert message
 */
const DWD_MSGTYPE = [
    'Alert'  => 'Erstausgabe der Meldung',
    'Update' => 'Aktualisierung der Meldung',
    'Cancel' => 'Stornierung der Meldung',
];

/**
 * Status of alert message
 */
const DWD_STATUS = [
    'Actual' => 'Aktuelle Meldung',
    'Test'   => 'Technischer Test',
];

/**
 * Categorie of alert message
 */
const DWD_CATEGORY = [
    'Met'    => 'Meteorologische Meldung',
    'Health' => 'Medizin-Meteorologische Meldung', // e.g. Hitzewarnung
];

/**
 * Time frame of the message
 */
const DWD_URGENCY = [
    'Immediate' => 'Warnung',
    'Future'    => 'Vorabinformation',
];

/**
 * Images & films
 */
const DWD_LINKS = [
    # Temperatur Image
    'TEMP'      => 'https://www.dwd.de/DWD/wetter/aktuell/deutschland/bilder/wx_<STATE>_akt.jpg',
    # Niederschlag Radar
    'RADAR'     => 'https://www.dwd.de/DWD/wetter/radar/rad_<STATE>_akt.jpg',
    # Niederschlag Radarfilm
    'MOVIE'     => 'https://www.dwd.de/DWD/wetter/radar/radfilm_<STATE>_akt.gif',
    # Karte mit allen Warnungen
    'MAPS'      => 'https://www.dwd.de/DWD/warnungen/warnapp_gemeinden/json/warnungen_gemeinde_map_<STATE>.png',
];

/**
 * Symbole & Icons
 */
const DWD_ICONS = [
    # Check Icon
    '0' => 'https://api.asmium.de/images/warning_check.png',
    # Level Icons
    '1' => 'https://www.wettergefahren.de/stat/warnungen/wetterwarnkriterien/<EVENT>_<LEVEL>.png',
    '2' => 'https://www.wettergefahren.de/stat/warnungen/wetterwarnkriterien/<EVENT>_<LEVEL>.png',
    '3' => 'https://www.wettergefahren.de/stat/warnungen/unwetterkriterien/<EVENT>_<LEVEL>.png',
    '4' => 'https://www.wettergefahren.de/stat/warnungen/unwetterkriterien/<EVENT>_<LEVEL>.png',
];

/**
 * Warning level of the message
 * https://www.wettergefahren.de/warnungen/warnsituation.html
 * https://www.wettergefahren.de/warnungen/wetterwarnkriterien.html
 * https://www.wettergefahren.de/warnungen/unwetterwarnkriterien.html
 * https://www.wettergefahren.de/stat/warnungen/warnapp/img/icn_check.png
 * 'Minor' => 'Wetterwarnung'                // Stufe 1 (Gelb)
 * 'Moderate' => 'Markante Wetterwarnung'    // Stufe 2 (Orange)
 * 'Severe' => 'Unwetterwarnung'             // Stufe 3 (Rot)
 * 'Extreme' => 'Extreme Unwetterwarnung'    // Stufe 4 (Violett)
 */
const DWD_SEVERITY = [
    [0, 'None', '', 0x00FF00, 'gruen'],     // Stufe 0 (Grün)
    [1, 'Minor', '', 0xFFFF00, 'gelb'],     // Stufe 1 (Gelb)
    [2, 'Moderate', '', 0xFF8000, 'ocker'], // Stufe 2 (Orange)
    [3, 'Severe', '', 0xFF0000, 'rot'],     // Stufe 3 (Rot)
    [4, 'Extreme', '', 0xFF00FF, 'lila'],   // Stufe 4 (Violett)
];

/**
 * Typ of message
 */
const DWD_CERTAINTY = [
    'Observed' => 'Beobachtung',
    'Likely'   => 'Vorhersage, Auftreten wahrscheinlich (p > ~50%)',
];

trait GeoHelper
{
    /**
     * JSON API BASE URL!
     * 1st level => LINKS (title, link)
     * 2nd level => LINKS (title, link)
     * 3rd level => LINKS (title, link) || CELLS (cell, name)
     * 4th level => CELLS (cell, name)
     */
    private static $BASEURL = 'https://api.asmium.de/warning/de/';

    /**
     * Get and extract data from json format.
     *
     * @param string $type area type
     * @param string $id Warn Cell ID
     * @return string GeoServer request URL
     */
    private function BuildURL(string $type, string $id): string
    {
        // Debug output
        $this->SendDebug(__FUNCTION__, 'Type: ' . $type . ', WarnCellID: ' . $id);
        // Build URL
        $base = DWD_GEO_BASEURL . DWD_GEO_PRAMS[intval($type)][0];
        $param = str_replace('<WARNCELLID>', $id, DWD_GEO_PRAMS[intval($type)][1]);
        // return the url
        $this->SendDebug(__FUNCTION__, $base . $param);
        return $base . $param;
    }

    /**
     * Get and extract data from json format.
     *
     * @param string $type area type
     * @param string $state State identifier
     * @param string $county County identifier
     * @return array Options array with caption and value.
     */
    private function ExtractData(string $type, string $state = null, string $county = null): array
    {
        // Debug output
        $this->SendDebug(__FUNCTION__, 'Type: ' . $type . ',State: ' . $state . ',County: ' . $county);
        // Build URL
        $url = self::$BASEURL;
        // Add Type
        if ($type != null) {
            $url = $url . $type . '/';
        }
        // Add State
        if ($state != null) {
            $url = $url . $state . '/';
        }
        // Add County
        if ($county != null) {
            $url = $url . $county . '/';
        }
        // Request data
        $json = @file_get_contents($url);
        // error handling
        if ($json === false) {
            $this->LogMessage($this->Translate('Could not load json data!'), KL_ERROR);
            $this->SendDebug(__FUNCTION__, 'ERROR LOAD DATA');
            return [];
        }
        // Json decode
        $data = json_decode($json, true);
        // Transform to options
        $options = [];
        // Cells ?
        if (isset($data['data']['cells'])) {
            foreach ($data['data']['cells'] as $cell) {
                $options[] = ['caption' => $cell['name'], 'value' => $cell['cell']];
            }
        }
        if (isset($data['data']['links'])) {
            foreach ($data['data']['links'] as $link) {
                $path = parse_url($link['link'], PHP_URL_PATH);
                $path = explode('/', $path);
                $path = array_reverse($path);
                $options[] = ['caption' => $link['title'], 'value' => $path[1]];
            }
        }
        // return the options
        return $options;
    }

    /**
     * Extract icon url from data.
     *
     * @param array $value warning data
     * @return string Url for warning icon.
     */
    private function ExtractIcon(array $value): string
    {
        $this->SendDebug(__FUNCTION__, $value);
        $url = '';
        foreach (DWD_EVENT_MAP as $event => $map) {
            if (in_array($value['CODE'], $map)) {
                $url = str_replace('<EVENT>', $event, DWD_ICONS[$value['LEVEL']]);
                if ($event == 'hitze' || $event == 'uv') {
                    $url = str_replace('<LEVEL>', 'lila', $url);
                } else {
                    $url = str_replace('<LEVEL>', DWD_SEVERITY[$value['LEVEL']][4], $url);
                }
            }
        }
        return $url;
    }

    /**
     * Replace capitalization with normal spelling
     *
     * @param string $json Json formated warnings
     * @return array Normalized warnings
     */
    private function PrepareWarnings(string $json): array
    {
        // Json decode
        $geo = json_decode($json, true);
        // Build new array
        $data = [];
        foreach ($geo['features'] as $idx => $feature) {
            $prop = [];
            foreach ($feature['properties'] as $key => $value) {
                $this->SendDebug(__FUNCTION__, $key . ': ' . $value);
                switch ($key) {
                    case 'NAME':
                        $prop['AREA'] = $value;
                        break;
                    case 'AREADESC':
                        if (!isset($prop['AREA'])) {
                            $prop['AREA'] = $value;
                        }
                        break;
                    case 'WARNCELLID':
                    case 'GC_WARNCELLID':
                        $prop['WARNCELLID'] = $value;
                        break;
                    case 'SENT':
                        $ts = new DateTime($value);
                        $prop['SENT'] = $ts->format('Y-m-d H:i:s');
                        break;
                    case 'STATUS':
                        $prop['STATUS'] = DWD_STATUS[$value];
                        break;
                    case 'MSGTYPE':
                        $prop['TYPE'] = DWD_MSGTYPE[$value];
                        break;
                    case 'CATEGORY':
                        $prop['CATEGORY'] = DWD_CATEGORY[$value];
                        break;
                    case 'EVENT':
                        $prop['EVENT'] = $value;
                        break;
                    case 'URGENCY':
                        $prop['URGENCY'] = DWD_URGENCY[$value];
                        break;
                    case 'SEVERITY':
                        $prop['SEVERITY'] = $this->Translate($value);
                        $prop['LEVEL'] = $this->GetKeyFromProfile($value, DWD_SEVERITY);
                        break;
                    case 'CERTAINTY':
                        $prop['CERTAINTY'] = DWD_CERTAINTY[$value];
                        break;
                    case 'EC_II':
                        $prop['CODE'] = $value; // . ':' . DWD_EVENT_CODE[$value];
                        break;
                    case 'EC_GROUP':
                        $prop['GROUP'] = $value;
                        break;
                    case 'EFFECTIVE':
                        $ts = new DateTime($value);
                        $prop['TIMESTAMP'] = $ts->format('Y-m-d H:i:s');
                        // no break is correct
                        // No break. Add additional comment above this line if intentional!
                    case 'SENT':
                        if (!isset($prop['TIMESTAMP'])) {
                            $ts = new DateTime($value);
                            $prop['TIMESTAMP'] = $ts->format('Y-m-d H:i:s');
                        }
                        break;
                    case 'ONSET':
                        $ts = new DateTime($value);
                        $prop['START'] = $ts->format('Y-m-d H:i:s');
                        break;
                    case 'EXPIRES':
                        if ($value != null) {
                            $ts = new DateTime($value);
                            $prop['END'] = $ts->format('Y-m-d H:i:s');
                        } else {
                            $prop['END'] = '';
                        }
                        break;
                    case 'HEADLINE':
                        $prop['HEADLINE'] = $this->ReplaceCaseSensitiveWords($value);
                        break;
                    case 'DESCRIPTION':
                        $prop['DESCRIPTION'] = $value;
                        break;
                    case 'INSTRUCTION':
                        $prop['INSTRUCTION'] = $value == null ? '' : $value;
                        break;
                }
            }
            $data[] = $prop;
        }
        $this->SendDebug(__FUNCTION__, 'Features #' . $geo['totalFeatures']);
        return $data;
    }

    /**
     * Replace capitalization with normal spelling
     *
     * @param string $str Text with capitalization
     * @return string Normal spelling
     */
    private function ReplaceCaseSensitiveWords(string $str): string
    {
        // Exceptions
        $exc = [
            'ergiebigem', 'extrem',
            'heftigem', 'heftigen', 'heftiges',
            'leichtem', 'leichten', 'leichtes',
            'orkanartigen', 'örtlichem',
            'schwerem', 'schweren', 'schweres',
            'starkem', 'starken', 'starkes',
            'strengem', 'strengen', 'strenges',
            'verbreiteter',
            'mit', 'und', 'vor',
        ];
        // Remove linebreaks (Safety check)
        $str = str_replace("\n", '', $str);
        // Remove 'Amtliche '
        $str = str_replace('Amtliche ', '', $str);
        // All to lower case
        $str = strtolower($str);
        // Replace case sensitive words
        $out = '';
        foreach (explode(' ', $str) as $key => $word) {
            $out .= (!in_array($word, $exc) || $key == 0) ? mb_convert_case($word, MB_CASE_TITLE, 'UTF-8') . ' ' : $word . ' ';
        }
        return rtrim($out);
    }

    /**
     * Extract assoziated key for textual value
     *
     * @param string $value Textual expression of the value
     * @param array $profile Profile assoziation array
     * @return integer Associated key
     */
    private function GetKeyFromProfile($value, $profile): int
    {
        if (is_null($profile) || empty($profile)) {
            return 0;
        }
        foreach ($profile as $asso) {
            if ($asso[1] == $value) {
                return $asso[0];
            }
        }
        return 0;
    }
}