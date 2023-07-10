# Unwetterwarnung (Weather Warning)

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-6.0-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-1.7.20230710-orange.svg)](https://github.com/Wilkware/IPSymconWeatherWarning)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://github.com/Wilkware/IPSymconWeatherWarning/workflows/Check%20Style/badge.svg)](https://github.com/Wilkware/IPSymconWeatherWarning/actions)

Dieses Modul dient zum Abrufen der von DWD bereitgestellten Unwetterwarnungen (Gewitter, Stark- und Dauerregen, Schneefall, Wind, Nebel, Frost, Glatteis, Tauwetter, UV-Strahlung, Hitze).

## Inhaltverzeichnis

1. [Funktionsumfang](#user-content-1-funktionsumfang)
2. [Voraussetzungen](#user-content-2-voraussetzungen)
3. [Installation](#user-content-3-installation)
4. [Einrichten der Instanzen in IP-Symcon](#user-content-4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#user-content-5-statusvariablen-und-profile)
6. [WebFront](#user-content-6-webfront)
7. [PHP-Befehlsreferenz](#user-content-7-php-befehlsreferenz)
8. [Versionshistorie](#user-content-8-versionshistorie)

### 1. Funktionsumfang

Das Modul nutzt die vom GeoWebservice des deutschen Wetterdienstes (DWD) bereitgestellten WMS-Dienste um (Un)Wetterwarnungen in IPS zu integrieren.
Je nach ausgewählter Region bzw. Gebietstyp werden die Warnungen gefiltert und in verschiedene Kanäle (Text, HTML, Push-Nachricht) ausgegeben.

Für die Auswahl des Gebietstyps/Region nutzt das Modul eine eigens entwickelte JSON-API (CDN basierend) damit der Nutzer schnell die richtige Gebiets-ID (WARNCELLID) einstellen kann (derzeit 12144).

Darüber hinaus können noch Variablen zur Darstellung verschiedener Wetterbilder und Radarfilme angelegt werden.

### 2. Voraussetzungen

* IP-Symcon ab Version 6.0

### 3. Installation

* Über den Modul Store das Modul Weather Warning installieren.
* Alternativ Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/Wilkware/IPSymconWeatherWarning` oder `git://github.com/Wilkware/IPSymconWeatherWarning.git`

### 4. Einrichten der Instanzen in IP-Symcon

* Unter "Instanz hinzufügen" ist das _'Weather Warning'_-Modul (Alias: _'Unwetterwarnung'_) unter dem Hersteller _'(Geräte)'_ aufgeführt.

__Konfigurationsseite__:

Einstellungsbereich:

> Warngebiet ...

Entsprechend der gewählten Auswahl verändert sich das Formular dynamisch.
Eine komplette Neuauswahl erreicht man durch Auswahl einens anderen "Gebietstyp" oder durch
"Bitte wählen ..." an der gewünschten Stelle.

Name                       | Beschreibung
-------------------------- | ----------------------------------
Land                       | 'Deutschland'
Warngebiet                 | Auswahl von 6 verschiedenen Warngebieten
Bundesland                 | Auswahl des Bundeslandes welches für den Warntyp verfügbar ist
Landkreis/kreisfreie Stadt | Auswahl des Landkreises oder einer kreisfreien Stadt im Bundesgebiet
Gemeinde                   | Auswahl einer Gemeinde, wenn der Typ diese Auflösung unterstützt (Warntyp: Gemeinden)

> Unwetterkarten ...

Hier kann eine Unwetterübersichtskarte aktiviert und konfiguriert werden. Neben der großen Deutschlandkarte kann auch eine
detailiertere Variant auf Basis des Bundeslandes gewählt werden. Das Erscheinungsbild kann durch verschiedene Parameter angepasst werden.  
Der darzustellende Bereich (so g. Bounding Box) wird dabei durch die Längen- und Breitengrade der 4 Himmelsrichtungen begrenzt!

Name                    | Beschreibung
----------------------- | ---------------------------------
Auswahl                 | Aktiviert Statusvariable für Unwetterkarte bei Auswahl eines konkreten Gebietes
Formatvorlage \[img\]   | Sytle für Bild der Unwetterkarte
Detailgrad              | Kreis- oder Gemeindeebene (Gemeinde dauert sehr lange zum Generieren)
Hintergrund             | Auswahl des Hintergrundlayers oder transparent
Bildbreite              | Breite in Pixel des zu generierenden Bildes (vordefiniert 500px und die Ration entsprechend in Richtung Höhe)
Bildhöhe                | Höhe in Pixel des zu generierenden Bildes
West                    | Westlicher Breitengrad (Begrenzung der Bildbox im Westen)
Süd                     | Südlicher Längengrad (Begrenzung der Bildbox im Süden)
Ost                     | Östlicher Breitengrad (Begrenzung der Bildbox im Osten)
Süd                     | Nördlicher Längengrad (Begrenzung der Bildbox im Norden)
Markierung anzeigern?   | Schalter, ob Markierung des eigenen Standortes (Location Control) angezeigt werden soll.
Farbe der Markierung    | Farbauswahl für Marker Pin

> Bilder und Radarfilm ...

Hier können Bildder bzw. der Radarfilm für die aktuellen Temperaturen und Niederschläge je Bundesland und/oder Detuschland aktiviert werden.  
Das Erscheinungsbild kann wieder über die Stylesheet-Angaben beeinflußt werden.

Name                    | Beschreibung
----------------------- | ---------------------------------
Aktuelle Temperatur     | Darstellung der aktuellen Temperatur zum aktuellen Tageszeitpunkt
Niederschlag Radarbild  | Radarbild des aktuellen Niederschlages zum aktuellen Tageszeitpunkt
Niederschlag Radarfim   | Animation des aktuellen Niederschlages zum mitlaufenden Tageszeitpunkt

> Formatvorlagen ...

Name                                                      | Beschreibung
--------------------------------------------------------- | ---------------------------------
Tabelle \[table\]                                         | Allgemeine Tabellenstyle (Schrift, Farbe, Hintergrund)
Alternierende Zeile \[tr:nth-child(even)\]                | Style für gerade Zeile (z.b: Hintergrundfarbe)
Bild-Zelle \[td.img\]                                     | Style für Zellen (1.Spalte) mit Warnbild
Text-Zelle \[td.txt\]                                     | Style für Zellen (2.Spalte) mit Warnmeldungen (Text)
Meldungstitel \[div.hl\]                                  | Style für die Überschrift der Warnmeldung (1. Zeile Text-Zelle)
Meldungszeitspanne \[div.ts\]                             | Style für die Zeitspanne der Warnmeldung (2. Zeile Text-Zelle)
Meldungsbeschreibung \[div.desc\]                         | Style für die Beschreibung der Warnmeldung (3. Zeile Text-Zelle)
Bild Warnstufe \[div.lwarn\]                              | Style für die Warnstufe - dreieckiger farbiger Rahmen (Bild-Zelle)

> Meldungsverwaltung ...

Name                           | Beschreibung
------------------------------ | ---------------------------------
Meldung an Anzeige senden      | Auswahl ob Eintrag in die Meldungsverwaltung erfolgen soll oder nicht (Ja/Nein)
Ab Stufe der Warnmeldung       | Auswahl ab welcher Stufe (1-4) die Nachricht erfolgen soll
Lebensdauer der Nachricht      | Wie lange so die Meldung angezeigt werden?
Nachricht ans Webfront senden  | Auswahl ob Push-Nachricht gesendet werden soll oder nicht (Ja/Nein)
Ab Stufe der Warnmeldung       | Auswahl ab welcher Stufe (1-4) die Meldung erfolgen soll
Text in Variable schreiben     | Auswahl ob Nachricht in Statusvariable geschrieben werden soll
Texttrennzeichen/Zeilenumbruch | Trennzeichen bei mehreren Ereignissen
Format der Textmitteilung      | Frei wählbares Format der zu sendenden Nachricht/Meldung
WebFront Instanz               | ID des Webfronts, an welches die Push-Nachrichten für Geburts-, Hochzeits- und Todestage gesendet werden soll
Meldsungsskript                | Skript ID des Meldungsverwaltungsskripts, weiterführende Infos im Forum: [Meldungsanzeige im Webfront](https://community.symcon.de/t/meldungsanzeige-im-webfront/23473)

> Erweiterte Einstellungen ...

Name                                                            | Beschreibung
--------------------------------------------------------------- | ---------------------------------
Indikatorvariable für aktive Warnungen anlegen (höchste Stufe)! | Schalter, ob eine Statusvariable als Indikator für Warnungen (höste Stufe) angelegt und aktualisiert werden soll.
Aktualisierungsinterval                                         | Auswahl aller wieviel Minuten Informationen abgerufen werden sollen (Standard: 15 min)

Aktionsbereich:

> Wetterwarnungen ...

Aktion         | Beschreibung
-------------- | ------------------------------------------------------------
AKTUALISIEREN  | Ruft die aktuellen Unwetterwarnungen von DWD ab (Update)

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name                          | Typ       | Beschreibung
------------------------------| --------- | ----------------
Warnstufe                     | Integer   | Höchste Warnstufe aller verfügbaren Meldungen
Warnmeldung                   | String    | Darstellung aller Meldungen als HTML Tabelle
Warnnachricht                 | String    | Darstellung aller Meldungen in textuellen Format
Unwetterkarte Land            | String    | HTML-Link auf Unwetterkarte Deutschland
Unwetterkarte Bundesland      | String    | HTML-Link auf Unwetterkarte ausgewähltes Bundesland
Temperaturen aktuell          | String    | HTML-Link auf aktuelle Temperaturübersichtskarte für ausgewähltes Bundesland
Niederschlag Radarbild        | String    | HTML-Link auf aktuelles Radarbild (Niederschlag) für ausgewähltes Bundesland
Niederschlag Radarfilm        | String    | HTML-Link auf aktuellen Radarfilm (Niederschlag) für ausgewähltes Bundesland
Temperaturen aktuell (de)     | String    | HTML-Link auf aktuelle Temperaturübersichtskarte für Deutschland
Niederschlag Radarbild (de)   | String    | HTML-Link auf aktuelles Radarbild (Niederschlag) für Deutschland
Niederschlag Radarfilm (de)   | String    | HTML-Link auf aktuellen Radarfilm (Niederschlag) für Deutschland

Folgendes Profil wird angelegt:

Name                 | Typ       | Beschreibung
-------------------- | --------- | ----------------
UWW.Level            | Integer   | Warnstufen (0 - 4)

> 0: Keine Warnung  
> 1: Wetterwarnung  
> 2: Markante Wetterwarnung  
> 3: Unwetterwarnung  
> 4: Extreme Unwetterwarnung  

### 6. WebFront

Man kann die Statusvariablen direkt im WF verlinken.

### 7. PHP-Befehlsreferenz

```php
void UWW_Update(int $InstanzID):
```

Holt entsprechend der Konfiguration die gewählten Daten vom Deutschen Wetterdienst (DWD).  
Die Funktion liefert keinerlei Rückgabewert.

__Beispiel__: `UWW_Update(12345);`

```php
void UWW_WaringInfo(int $InstanzID):
```

Gibt alle Unwetterwarnungen als multidimensionales assoziatives Array zurück.
__HINWEIS:__ Sollten keine Warnungen vorliegen, wird ein leeres Array geliefert.

__Beispiel__: `UWW_WaringInfo(12345);`

> \[{  
> "AREA": "Chiemsee",  
> "WARNCELLID":209913000,  
> "SENT":"2021-05-10 12:51:00",  
> "STATUS":"Aktuelle Meldung",  
> "TYPE":"Erstausgabe der Meldung",  
> "CATEGORY":"Meteorologische Meldung",  
> "EVENT":"STARKWIND",  
> "URGENCY":"Warnung",  
> "SEVERITY":"Wetterwarnung",  
> "LEVEL":1,  
> "CERTAINTY":"Vorhersage, Auftreten wahrscheinlich (p > ~50%)",  
> "CODE":"57:Starkwind",  
> "GROUP":"WIND",  
> "TIMESTAMP":"2021-05-10 12:51:00",  
> "START":"2021-05-10 12:51:00",  
> "END":"",  
> "HEADLINE": "Warnung vor Starkwind",  
> "DESCRIPTION":"Es treten Windb\u00f6en mit Geschwindigkeiten bis 60 km\/h (17m\/s, 33kn, Bft 7) auf.",  
> "INSTRUCTION":""  
> }\]  

### 8. Versionshistorie

v1.7.20230709

* _NEU_: Anpassungen für IPS 7.0 (PHP 8.2)
* _FIX_: Veraltetet Style-Checks ausgebaut
* _FIX_: Weitere Modulvereinheitlichungen vorgenommen
* _FIX_: Fehler in Auswertung von Warnungen korrigiert
* _FIX_: Fehler bei Umlauten in Meldungsüberschrift (MultiByte)

v1.6.20220114

* _NEU_: Kompatibilität auf IPS 6.0 hoch gesetzt
* _NEU_: Bibliotheks- bzw. Modulinfos vereinheitlicht
* _NEU_: Konfigurationsdialog überarbeitet (v6 Möglichkeiten genutzt)
* _NEU_: Doppelte Warnungen bei Meldungsverwaltung entfernt
* _FIX_: Fehlende Text-Formatierung für Warnungen hinzugefügt
* _FIX_: Fehler in Modulbibliotheken korrigiert

v1.5.20210801

* _FIX_: Fehlende Text-Formatierung für Warnungen hinzugefügt
* _FIX_: Icon wird jetzt bei allen Warnungen korrekt angezeigt
* _FIX_: Weitere Modulvereinheitlichungen vorgenommen
* _FIX_: Dokumentation überarbeitet

v1.4.20210611

* _NEU_: Positions-Marker auf Unwetterkarte hinzugefügt
* _FIX_: Inline Style für Unwetterkarte vereinheitlicht/umgestellt

v1.3.20210609

* _NEU_: Text-Formatierung erweitert
* _NEU_: Inline-Style Angaben für Unwetterkarte
* _FIX_: Warnstufen-Icon für Stufe 3 und 4
* _FIX_: Lebensdauer einer Meldung von Sekunden auf Minuten korrigiert
* _FIX_: URL-Parameter für Umweltkarte vereinheitlicht
* _FIX_: Zuweisung der Formatvorlagen für Meldungstabelle korrigiert
* _FIX_: Dokumentation korrigiert

v1.2.20210518

* _NEU_: Text-Formatierung um Warnstufe(Zahl) und Beschreibung erweitert
* _NEU_: Unwetterkarte auf GeoWebservice umgestellt
* _FIX_: Temperatur- und Niederschlagsbilder jetzt mit umschließendem DIV; IMG fixer Style
* _FIX_: MV korrekt geschrieben

v1.1.20210511

* _NEU_: Temperatur, Niederschlag und Radarfilm auch für ganz Deutschland auswählbar
* _FIX_: Keine Auswahl des Warngebietes möglich, Instanz blieb inaktiv
* _FIX_: Dokumentation vervollständigt

v1.0.20210313

* _NEU_: Initialversion

## Danksagung

Ich möchte mich für die Unterstützung bei der Entwicklung dieses Moduls bedanken bei ...

* _Fonzo_ : für das beharrliche Nachfragen nach dem Modul ;-)
* _Nall-chan_ : für die Hilfe im Channel und seine tollen Lösungen ;-)
* _yansoph_: für den regen Austausch und das Testen :-)
* _richimaint_: für das Testen beim Umlauteproblem :-)

Vielen Dank an Euch!

## Entwickler

Seit nunmehr über 10 Jahren fasziniert mich das Thema Haussteuerung. In den letzten Jahren betätige ich mich auch intensiv in der IP-Symcon Community und steuere dort verschiedenste Skript und Module bei. Ihr findet mich dort unter dem Namen @pitti ;-)

[![GitHub](https://img.shields.io/badge/GitHub-@wilkware-181717.svg?style=for-the-badge&logo=github)](https://wilkware.github.io/)

## Spenden

Die Software ist für die nicht kommzerielle Nutzung kostenlos, über eine Spende bei Gefallen des Moduls würde ich mich freuen.

[![PayPal](https://img.shields.io/badge/PayPal-spenden-00457C.svg?style=for-the-badge&logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

Namensnennung - Nicht-kommerziell - Weitergabe unter gleichen Bedingungen 4.0 International

[![Licence](https://img.shields.io/badge/License-CC_BY--NC--SA_4.0-EF9421.svg?style=for-the-badge&logo=creativecommons)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
