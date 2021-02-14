# Almanac (Jahreskalender)

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-5.2-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-1.0.20210313-orange.svg)](https://github.com/Wilkware/IPSymconWeatherWarning)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://github.com/Wilkware/IPSymconWeatherWarning/workflows/Check%20Style/badge.svg)](https://github.com/Wilkware/IPSymconWeatherWarning/actions)

Dieses Modul bietet ...

## Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [Versionshistorie](#8-versionshistorie)

### 1. Funktionsumfang

### 2. Voraussetzungen

* IP-Symcon ab Version 5.2

### 3. Installation

* Über den Modul Store das Modul Almanac installieren.
* Alternativ Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/Wilkware/IPSymconWeatherWarning` oder `git://github.com/Wilkware/IPSymconWeatherWarning.git`

### 4. Einrichten der Instanzen in IP-Symcon

* Unter "Instanz hinzufügen" ist das 'Weather Warning'-Modul (Alias: Unwetterwarnung) unter dem Hersteller '(Sonstige)' aufgeführt.

__Konfigurationsseite__:

### 6. WebFront

Man kann die Statusvariablen direkt im WF verlinken.

### 7. PHP-Befehlsreferenz

```php
void UWW_Update(int $InstanzID):
```

Holt entsprechend der Konfiguration die gewählten Daten.  
Die Funktion liefert keinerlei Rückgabewert.

__Beispiel__: `UWW_Update(12345);`

### 8. Versionshistorie

v1.0.20210313

* _NEU_: Initialversion

## Danksagung

Ich möchte mich für die Unterstützung bei der Entwicklung dieses Moduls bedanken bei ...

* _Fonzo_ : für das beharrliche Nachfragen nach dem Modul ;-)

Vielen Dank für die hervorragende und tolle Arbeit!

## Entwickler

* Heiko Wilknitz ([@wilkware](https://github.com/wilkware))

## Spenden

Die Software ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Entwickler bitte hier:

[![License](https://img.shields.io/badge/Einfach%20spenden%20mit-PayPal-blue.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

### Lizenz

[![Licence](https://licensebuttons.net/i/l/by-nc-sa/transparent/00/00/00/88x31-e.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
