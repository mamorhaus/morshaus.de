MORSHAUS PILOT 0.1.1

Nur für LocalWP / Staging verwenden.

Installation:
1. ZIP unter Design > Themes > Theme hinzufügen > Theme hochladen installieren.
2. Theme „Morshaus Pilot“ aktivieren.
3. Unter Design > Menüs ein Menü der Position „Hauptnavigation“ zuweisen.
   Mobil zeigt die Navigation den aktiven Hauptmenüpunkt und nur die Hauptmenüpunkte.
   Mobil am Bestelllisten-Symbol und auf Desktop am Menüpunkt erscheint die
   Gesamtzahl der Produkte und Termine, wenn die Bestellliste nicht leer ist.
4. Das Theme liest Logo, Headerbild und die sieben Startseiten-Panel-IDs aus den bisherigen Twenty-Seventeen-Theme-Mods.

Auf Desktop erscheint das Teeblatt außer auf Produktseiten als dezentes Wasserzeichen im Inhaltsbereich.
Bei reduzierter Bewegung bleibt es möglichst in der Bildschirmmitte.

Seminartermine: Weise dem Termin die passende Veranstaltungskategorie zu, z. B.
„Am Tisch > Grundlagen“ oder „In Stille > Bogen/Zeremonie“. Die Themenauswahl
auf der Seminarseite filtert nach diesen Kategorien.
Die Rückmeldung zum Hinzufügen eines Termins erscheint neben dem
Bestelllisten-Button und blendet sich nach drei Sekunden aus.

Produkte: Die Sencha-Seite nutzt die vorhandenen Produktdaten und Medien aus
WordPress. Unter „Produkte“ können Ausführungen, Modelle, Mengen, Preise,
Extras und Texte bearbeitet werden. Die Produktübersicht zeigt Varianten nach
Ausführung ohne Mengen und Preise. Im Tee-Editor stehen Geschmack und
zusätzlicher Reiter nebeneinander. Ältere Tee-Ausführungen „Einfach“ und
„Besonders“ werden als „Alltag“ und „Erlesen“ angezeigt. Ältere Zubereitungen
im Feld „Wissenswertes“ bleiben gespeichert. Beschreibung und
Merkmale stehen bei Tee und Utensilien unter der Beschreibung oberhalb des
Bestellblocks; Tee hat zusätzlich einen Geschmacksreiter.
Der zusätzliche Reiter kann pro Variante für Tee und Utensilien benannt und
über eine Checkbox ein- oder ausgeblendet werden. Vorhandenes Wissenswertes
ist zunächst aktiviert und heißt standardmäßig „Wissenswertes“.
Im Produkt-Admin lassen sich für jede Teevariante unter „Aufgüsse“ beliebig
viele Aufgüsse mit Temperatur und Ziehzeit als Zahlen eintragen
(z. B. „70“ und „60“). Auf der Produktseite erscheinen automatisch „°“ und
„s“. Die Reihenfolge entspricht der Anzeige;
einzelne Angaben dürfen leer bleiben, vollständig leere Zeilen werden entfernt.
Ältere Angaben mit Bereichen oder Text bleiben erhalten und können durch
Zahlen ersetzt werden; einfache Angaben mit Einheit werden übernommen.
Alte Zubereitungstexte bleiben gespeichert, werden bei Tee aber nicht angezeigt.
Die Piktogramme der Produktmerkmale entsprechen der Auswahl in der Verwaltung.
Lange Reiterinhalte können über „Mehr“ geöffnet und mit „Weniger“ wieder
eingeklappt werden; auf Mobilgeräten umfasst die Vorschau etwa sechs Zeilen.
Produktmerkmale bleiben in der Beschreibung auch eingeklappt sichtbar.
Aktivierte Merkmal-Hinweise erscheinen als Tooltip am Info-Symbol. Bilder für
Dose, Klammer und Brandstempel lassen sich je Variante in der Produktverwaltung
unter „Extras“ auswählen; beim Anklicken erscheint das Bild in der Galerie,
nicht in der Extras-Auswahl.
Sind mehrere Produktfotos vorhanden, können sie bei Produkten ohne Varianten
und ohne angezeigte Variantenauswahl unter dem Hauptbild ausgewählt werden.
In der vergrößerten Bildansicht zeigt die Maus eine Lupe für Bilddetails.
Ab fünf Varianten zeigt die Auswahl unter dem Produktbild ein endloses Karussell.
Bei weniger Varianten wird das ausgewählte Thumbnail größer dargestellt.
Varianten können angeklickt sowie per Wischgeste oder Pfeiltasten gewählt werden.
Utensilien mit dem Shortcode [morshaus_produkt id="..."] erhalten die gemeinsame
Produktansicht mit Stückpreis und den im Produkt aktivierten Brandstempeln.
Teekarten verlinken auf die gleichnamigen Produkte: Sencha nutzt die vorhandene
Sencha-Seite, weitere Tees eine veröffentlichte Unterseite von „Tee“ mit der
Produkt-ID als Titelform und dem Shortcode [morshaus_produkt id="..."].
Die Verfügbarkeit der Teekarte gilt auch auf ihrer Produktseite.
Die Karten im Utensilienverteiler sind den vorhandenen Produktseiten zugeordnet;
für noch nicht angelegte Produkte steht dort „Produktseite folgt“.
Waage und Isolierkanne führen zu bearbeitbaren Empfehlungsseiten ohne Bestellung
und tragen auf dem Verteiler ein grünes Empfehlungsbanner.
Mobil lassen sich die Karten jeder Tee- und Utensilienkategorie seitlich wischen;
auf Desktop gleiten weitere Karten per Pfeil ins Bild; Punkte markieren die
Position. Bei reduzierter Bewegung erfolgt der Wechsel ohne Animation.

Bewusste Grenzen des Piloten:
- Keine Übernahme von styling.php oder landingpage.php.
- Sencha und die vorhandenen Utensilien sind die Produktseiten des Piloten;
  weitere Teeprodukte und fehlende Utensilien folgen.
- Normale Seiten besitzen ein neutrales Standardtemplate.
- Bei Problemen jederzeit wieder Twenty Seventeen Child aktivieren.
