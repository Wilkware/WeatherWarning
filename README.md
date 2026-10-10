# 🌩️ Unwetterwarnung (Weather Warning)

[![Home](https://img.shields.io/badge/Home-wilkware.de-0b1830.svg?style=flat-square)](https://wilkware.de/module/unwetterwarnung/)
[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg?style=flat-square)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-6.4-blue.svg?style=flat-square)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-3.1.20250802-orange.svg?style=flat-square)](https://github.com/Wilkware/WeatherWarning)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg?style=flat-square)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://img.shields.io/github/actions/workflow/status/wilkware/WeatherWarning/ci.yml?branch=main&label=CI&style=flat-square)](https://github.com/Wilkware/WeatherWarning/actions)

Dieses Modul dient zum Abrufen der von DWD bereitgestellten Unwetterwarnungen (Gewitter, Stark- und Dauerregen, Schneefall, Wind, Nebel, Frost, Glatteis, Tauwetter, UV-Strahlung, Hitze).

## Inhaltverzeichnis

1. [Funktionsumfang](#user-content-1-funktionsumfang)
2. [Voraussetzungen](#user-content-2-voraussetzungen)
3. [Installation](#user-content-3-installation)
4. [Einrichtung](#user-content-4-einrichtung)
5. [Statusvariablen](#user-content-5-statusvariablen)
6. [Darstellungen](#user-content-6-darstellungen)
7. [Visualisierung](#user-content-7-visualisierung)
8. [Befehlsreferenz](#user-content-8-befehlsreferenz)
9. [Versionshistorie](#user-content-9-versionshistorie)

### 1. Funktionsumfang

Das Modul nutzt die vom GeoWebservice des deutschen Wetterdienstes (DWD) bereitgestellten WMS-Dienste um (Un)Wetterwarnungen in IPS zu integrieren.
Je nach ausgewählter Region bzw. Gebietstyp werden die Warnungen gefiltert und in verschiedene Kanäle (Text, HTML, Push-Nachricht) ausgegeben.

Für die Auswahl des Gebietstyps/Region nutzt das Modul eine eigens entwickelte JSON-API (CDN basierend) damit der Nutzer schnell die richtige Gebiets-ID (WARNCELLID) einstellen kann (derzeit 12144).

Darüber hinaus können noch Variablen zur Darstellung verschiedener Wetterbilder und Radarfilme angelegt werden.

### 2. Voraussetzungen

* Symcon ab Version 6.4

### 3. Installation

* Über den Modul Store das Modul _Unwetterwarnung_ installieren.
* Alternativ über das Modul Control folgende URL hinzufügen.  
`https://github.com/Wilkware/WeatherWarning` oder `git://github.com/Wilkware/WeatherWarning.git`

### 4. Einrichtung

* Unter 'Instanz hinzufügen' ist das _Unwetterwarnung_-Modul unter dem Hersteller '(Geräte)' aufgeführt.

__Konfigurationsseite__:

Einstellungsbereich:

> Warngebiet ...

Entsprechend der gewählten Auswahl verändert sich das Formular dynamisch.
Eine komplette Neuauswahl erreicht man durch Auswahl eines anderen "Gebietstyp" oder durch
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
detailliertere Variante auf Basis des Bundeslandes gewählt werden. Das Erscheinungsbild kann durch verschiedene Parameter angepasst werden.  
Der darzustellende Bereich (so g. Bounding Box) wird dabei durch die Längen- und Breitengrade der 4 Himmelsrichtungen begrenzt!

Name                    | Beschreibung
----------------------- | ---------------------------------
Auswahl                 | Aktiviert Statusvariable für Unwetterkarte bei Auswahl eines konkreten Gebietes
Detailgrad              | Kreis- oder Gemeindeebene (Gemeinde dauert sehr lange zum Generieren)
Hintergrund             | Auswahl des Hintergrundlayers oder transparent
Bildbreite              | Breite in Pixel des zu generierenden Bildes (vordefiniert 500px und die Ration entsprechend in Richtung Höhe)
Bildhöhe                | Höhe in Pixel des zu generierenden Bildes
West                    | Westlicher Breitengrad (Begrenzung der Bildbox im Westen)
Süd                     | Südlicher Längengrad (Begrenzung der Bildbox im Süden)
Ost                     | Östlicher Breitengrad (Begrenzung der Bildbox im Osten)
Nord                    | Nördlicher Längengrad (Begrenzung der Bildbox im Norden)
Markierung anzeigen?    | Schalter, ob Markierung des eigenen Standortes (Location Control) angezeigt werden soll.
Farbe der Markierung    | Farbauswahl für Marker Pin, welcher als Platzhalter {{color}} in der CSS-Definition verwendet werden kann!
Legende (Html)          | Html Vorlage für Legende-Tabelle

> Bilder und Radarfilm ...

Hier können Bilder bzw. der Radarfilm für die aktuellen Temperaturen und Niederschläge je Bundesland und/oder Deutschland aktiviert werden.  
Das Erscheinungsbild kann wieder über die Stylesheet-Angaben beeinflußt werden.

Name                    | Beschreibung
----------------------- | ---------------------------------
Aktuelle Temperatur     | Darstellung der aktuellen Temperatur zum aktuellen Tageszeitpunkt
Niederschlag Radarbild  | Radarbild des aktuellen Niederschlages zum aktuellen Tageszeitpunkt
Niederschlag Radarfilm  | Animation des aktuellen Niederschlages zum mitlaufenden Tageszeitpunkt

> Formatvorlagen ...

Name                    | Beschreibung
------------------------| ---------------------------------
Unwetterkarte           | Style für die Unwetterkarte (Deutschlandkarte mit Pin)
Warnmeldung             | Style für die Warnmeldungen (Tabelle, Icon, Überschrift, Beschreibung und Zeitspanne)
Legende                 | Style für die Legende (Farbskala und deren Zuordnung zu Gefahrenstufen)
ZURÜCKSETZEN            | Schalter, um wieder die jeweilig vordefinierten Stylesheet-Angaben einzusetzen

> Meldungsverwaltung ...

Name                           | Beschreibung
------------------------------ | ---------------------------------
Meldung an Anzeige senden      | Auswahl ob Eintrag in die Meldungsverwaltung erfolgen soll oder nicht (Ja/Nein)
Ab Stufe der Warnmeldung       | Auswahl ab welcher Stufe (1-4) die Nachricht erfolgen soll
Lebensdauer der Nachricht      | Wie lange soll die Meldung angezeigt werden?
Nachricht an Visualisierung senden | Auswahl ob Push-Nachricht gesendet werden soll oder nicht (Ja/Nein)
Ab Stufe der Warnmeldung       | Auswahl ab welcher Stufe (1-4) die Meldung erfolgen soll
Text in Variable schreiben     | Auswahl ob Nachricht in Statusvariable geschrieben werden soll
Texttrennzeichen/Zeilenumbruch | Trennzeichen bei mehreren Ereignissen
Format der Textmitteilung      | Frei wählbares Format der zu sendenden Nachricht/Meldung
Visualisierungs-Instanz        | ID der Visualisierung, an welches die Push-Nachrichten für Geburts-, Hochzeits- und Todestage gesendet werden soll (WebFront oder TileVisu Instanz)
Meldungsskript                 | Skript ID des Meldungsverwaltungsskripts, weiterführende Infos im Forum: [Meldungsanzeige im Webfront](https://community.symcon.de/t/meldungsanzeige-im-webfront/23473)

> Erweiterte Einstellungen ...

Name                                                            | Beschreibung
--------------------------------------------------------------- | ---------------------------------
Indikatorvariable für aktive Warnungen anlegen (höchste Stufe)! | Schalter, ob eine Statusvariable als Indikator für Warnungen (höchste Stufe) angelegt und aktualisiert werden soll.
Variable für Bilderklärung (Legende) anlegen!                   | Schalter, ob eine Statusvariable für die Erklärung der Zuordnung Farbe zu Warnstufe angelegt werden soll.
Aktualisierungsinterval                                         | Auswahl aller wieviel Minuten Informationen abgerufen werden sollen (Standard: 15 min)

Aktionsbereich:

> Wetterwarnungen ...

Aktion         | Beschreibung
-------------- | ------------------------------------------------------------
AKTUALISIEREN  | Ruft die aktuellen Unwetterwarnungen von DWD ab (Update)

### 5. Statusvariablen

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name                           | Typ     | Beschreibung
------------------------------ | ------- | ------------------------------------------------------------
Warnstufe                      | Integer | Höchste Warnstufe aller verfügbaren Meldungen
Warnmeldungen                  | String  | Darstellung aller Meldungen als HTML Tabelle
Warnnachricht                  | String  | Darstellung aller Meldungen in textuellen Format
Unwetterkarte                  | String  | Unwetterkarte für Deutschland bzw. das ausgewählte Bundesland
Legende                        | String  | Erklärung der Zuordnung Farbe zu Warnstufe
Temperaturen aktuell           | String  | Aktuelle Temperaturübersichtskarte für ausgewähltes Bundesland
Niederschlag Radarbild         | String  | Aktuelles Radarbild (Niederschlag) für ausgewähltes Bundesland
Niederschlag Radarfilm         | String  | Aktueller Radarfilm (Niederschlag) für ausgewähltes Bundesland
Temperaturen aktuell (de)      | String  | Aktuelle Temperaturübersichtskarte für Deutschland
Niederschlag Radarbild (de)    | String  | Aktuelles Radarbild (Niederschlag) für Deutschland
Niederschlag Radarfilm (de)    | String  | Aktueller Radarfilm (Niederschlag) für Deutschland
Niederschlags Vorhersage (de)  | String  | Niederschlagsvorhersage für Deutschland

_Hinweis:_ Bis auf _Warnmeldungen_ werden die Statusvariablen nur entsprechend der Auswahl in der Konfiguration angelegt.

### 6. Darstellungen

Die Darstellungen werden direkt an den Statusvariablen hinterlegt, es werden keine Profile angelegt.

Variable                       | Darstellung   | Werte
------------------------------ | ------------- | ------------------------------------------------------------
Warnstufe                      | Wertanzeige   | Keine Warnung (0), Wetterwarnung (1), Markante Wetterwarnung (2), Unwetterwarnung (3), Extreme Unwetterwarnung (4), UV-Warnung (10, 12), Hitzewarnung (11), Extreme Hitzewarnung (13)
Warnmeldungen                  | Webinhalt     | HTML
Unwetterkarte                  | Webinhalt     | HTML
Legende                        | Webinhalt     | HTML
Temperaturen aktuell (...)     | Webinhalt     | HTML
Niederschlag Radarbild (...)   | Webinhalt     | HTML
Niederschlag Radarfilm (...)   | Webinhalt     | HTML
Niederschlags Vorhersage (de)  | Webinhalt     | HTML

### 7. Visualisierung

Man kann die Statusvariablen direkt in die Visualisierung verlinken.

### 8. Befehlsreferenz

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

### 9. Versionshistorie

v3.1.20250802

* _NEU_: Niederschlagsradar-Vorhersage hinzugefügt

v3.0.20250801

* _NEU_: Icons werden jetzt via github-Pages geladen (bessere Ausfallsicherheit)
* _NEU_: HTML für Legende in Konfiguration aufgenommen
* _NEU_: Continuous Integration mit Check Style, Static Code Analysis und Unit Tests eingeführt
* _NEU_: Debugging Funktionen komplett überarbeitet
* _FIX_: Update der Event-Tabelle für korrekte Zurodnung von Ereignis zu Icon
* _FIX_: Zeiten korrigiert (TimeZone wird jetzt korrekt gesetzt)
* _FIX_: Meta-Tag für viewport gelöscht, kommt automatisch vom System
* _FIX_: Farben der Profilassoziationen passenend zu Symbolfarbe
* _FIX_: (Warn)Symbole hür Hitze und UV werden jetzt korrekt gesetzt
* _FIX_: Warnungskategorie `Health` korrigiert
* _FIX_: Dokumentation für PHP Static Analysis komplett überarbeitet
* _FIX_: Bibliotheksfunktionen überarbeitet in Vorbereitung auf IPSModuleStrict

v2.0.20240811

* _NEU_: Darstellung der Farbskala der Warnstufen (Legende)
* _NEU_: Medizin-Meteorologische Meldung (Hitzewarnungen) werden speziell behandelt (Level)
* _NEU_: Stylesheets können für alle HTML-Elemente individuell definiert werden
* _NEU_: Farbauswahl für Karten-Pin kann durch Platzhalter {{color}} im Stylesheet verwendet werden
* _NEU_: Kompatibilität auf IPS 6.4 hoch gesetzt
* _FIX_: Icon für extreme Hitze wird jetzt korrekt angezeigt
* _FIX_: HTML-Struktur vereinheitlicht und verbessert
* _FIX_: Unterscheidung der verschiedenen Visualisierungsinstanzen (PushNotification)
* _FIX_: Bibliotheks- bzw. Modulinfos vereinheitlicht
* _FIX_: Namensnennung und Repo vereinheitlicht
* _FIX_: Update Style-Checks
* _FIX_: Übersetzungen überarbeitet und verbessert
* _FIX_: Dokumentation vereinheitlicht 

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

Seit nunmehr über 10 Jahren fasziniert mich das Thema Haussteuerung. In den letzten Jahren betätige ich mich auch intensiv in der Symcon Community und steuere dort verschiedenste Skript und Module bei. Ihr findet mich dort unter dem Namen @pitti ;-)

[![GitHub](https://img.shields.io/badge/GitHub-@wilkware-181717.svg?style=for-the-badge&logo=github)](https://wilkware.github.io/)

## Spenden

Die Software ist für die nicht kommerzielle Nutzung kostenlos, über eine Spende bei Gefallen des Moduls würde ich mich freuen.

[![PayPal](https://img.shields.io/badge/PayPal-spenden-00457C.svg?style=for-the-badge&logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

Namensnennung - Nicht-kommerziell - Weitergabe unter gleichen Bedingungen 4.0 International

[![Licence](https://img.shields.io/badge/License-CC_BY--NC--SA_4.0-EF9421.svg?style=for-the-badge&logo=creativecommons)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
