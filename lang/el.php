<?php
/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

// default language for translations

// strings only used in setup
$langSetup["dbconnectfailed"] = "Αποτυχία σύνδεσης στη Βάση Δεδομένων.";
$langSetup["serversaid"] = " Ο Server είπε: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Παρακαλώ ελέγξτε τις ρυθμίσεις της Βάση Δεδομένων στο 'config.php'";
$langSetup["dbqueryfailed"] = "Αποτυχία αιτήματος στη Βάση Δεδομένων.";
$langSetup["dbtablessuccess"] = "Οι πίνακες στη Βάση Δεδομένων δημιουργήθηκαν επιτυχώς!";
$langSetup["setupuser"] = "Παρακαλώ, τωρα ορίστε τον Διαχειριστή του µlogger.";
$langSetup["congratulations"] = "Συγχαρητήρια!";
$langSetup["setupcomplete"] = "Οι ρυθμίσεις ολοκληρώθηκαν. Τώρα, επισκεφθείτε το <a href=\"../index.php\">main page</a> και συνδεθείτε με τα στοιχεία Χρήστη που ορίσατε.";
$langSetup["disablewarn"] = "ΠΡΟΣΟΧΗ! ΑΠΑΝΕΡΓΟΠΟΙΗΣΤΕ ΤΟ 'setup.php' Ή ΔΙΑΓΡΑΨΤΕ ΤΟ ΑΠΟ ΤΟΝ SERVER.";
$langSetup["disabledesc"] = "Είναι πολύ ριψοκίνδυνο να αφήσετε προσβάσιμο το αρχείο εγκατάστασης. Ο οποιοσδήποτε μπορεί να το τρέξει, να διαγράψει τη Βάση Δεδομένων σας ή και τους Χρήστες σας. Διαγράψτε το ή απενεργοποιήστε το θέτοντας το %s ξανά σε %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Δυστυχώς, κάτι πήγε στραβά. Ίσως βρείτε περισσότερες λεπτομέρειες στα Αρχεία Καταγραφής του server σας.";
$langSetup["welcome"] = "Καλωσήρθατε στο µlogger!";
$langSetup["disabledwarn"] = "Για λόγους ασφαλείας, αυτό το script είναι απενεγοποιημένο εκ των προτέρων. Για να το ενεργοποιήσετε θα πρέπει να τροποποιήσετε το αρχείο 'scripts/setup.php' και να θέσετε την μεταβλητή %s στην αρχή του, σε %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Η γραμμή: %s θα έπρεπε να γράφει: %s";
$langSetup["dorestart"] = "Παρακαλώ επανεκκινήστε όταν τα έχετε όλα έτοιμα.";
$langSetup["createconfig"] = "Παρακαλώ δημιουργήστε ένα αρχείο με όνομα 'config.php' στο root φάκελο. Μπορείτε επίσης να μετονομάσετε το ήδη υπάρχον αρχείο 'config.default.php'. Σιγουρευτείτε οτι ρυθμίσατε καταλλήλως, αναλόγως τις ανάγκες σας και την Βάση Δεδομένων σας.";
$langSetup["nodbsettings"] = "Πρέπει να δώσετε τα στοιχεία σύνδεσης της Βάση Δεδομένων σας στο 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Τώρα θα οριστούν οι απαιτούμενοι για το µlogger (%s) πίνακες. Θα δημιουργηθούν στην Βάση Δεδομένων με όνομα %s. Προσοχή, αν οι πίνακες προϋπάρχουν, θα διαγραφούν και θα δημιουργηθούν εκ νέου, τα περιεχόμενά τους θα χαθούν."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Με τα πέρας των εργασιών εγκατάστασης, θα σας ζητηθεί να ορίσετε τα Στοιχεία Σύνδεσης Διαχειριστή του µlogger.";
$langSetup["startbutton"] = "Πατήστε για Εκκίνηση";
$langSetup["restartbutton"] = "Επανεκκίνηση";
$langSetup["optionwarn"] = "Η ρύθμιση %s της PHP πρέπει να είναι %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Η απιτούμενη επέκταση της PHP %s δεν διατίθεται."; // substitutes extension name
$langSetup["notwritable"] = "Ο φάκελος '%s' πρέπει να είναι εγγράψιμος από την PHP."; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Παρακαλώ δώστε τα Στοιχεία Σύνδεσής σας.";
$lang["authfail"] = "Εσφαλμένα Στοιχεία Σύνδεσης";
$lang["user"] = "Χρήστης";
$lang["track"] = "Διαδρομή";
$lang["latest"] = "τελευταία θέση";
$lang["autoreload"] = "επαναφόρτωση";
$lang["reload"] = "Επαναφόρτωση τώρα";
$lang["export"] = "Εξαγωγή Διαδρομής";
$lang["chart"] = "Γράφημα Υψομετρικών";
$lang["close"] = "κλείσιμο";
$lang["time"] = "Χρόνος";
$lang["speed"] = "Ταχύτητα";
$lang["accuracy"] = "Ακρίβεια";
$lang["position"] = "Θέση";
$lang["altitude"] = "Υψόμετρο";
$lang["bearing"] = "Κατεύθυνση";
$lang["ttime"] = "Συνολικός χρόνος";
$lang["aspeed"] = "Μέση ταχύτης";
$lang["tdistance"] = "Διανυθείσα απόσταση";
$lang["pointof"] = "Σημείο %d από %d"; // e.g. Point 3 of 10
$lang["summary"] = "Σύνοψη ταξιδίου";
$lang["suser"] = "επιλέξτε Χρήστη";
$lang["logout"] = "Αποσύνδεση";
$lang["login"] = "Σύνδεση";
$lang["username"] = "Όνομα Χρήστη";
$lang["password"] = "Κωδικός";
$lang["language"] = "Γλώσσα";
$lang["newinterval"] = "Ορίστε νέα περίοδο (δευτερόλεπτα)";
$lang["api"] = "API χάρτου";
$lang["units"] = "Σύστημα μονάδων";
$lang["metric"] = "Μετρικό";
$lang["imperial"] = "Βρετανικό/US";
$lang["nautical"] = "Ναυτικό";
$lang["admin"] = "Διαχειριστής";
$lang["adminmenu"] = "Διαχείριση";
$lang["passwordrepeat"] = "Επαναλάβατε κωδικό";
$lang["passwordenter"] = "Θέστε κωδικό";
$lang["usernameenter"] = "Θέστε Όνομα Χρήστη";
$lang["adduser"] = "Προσθήκη Χρήστη";
$lang["userexists"] = "Ο Χρήστης υπάρχει ήδη";
$lang["cancel"] ="Ακύρωση";
$lang["submit"] = "Υποβολή";
$lang["oldpassword"] = "Παλαιός κωδικός";
$lang["newpassword"] = "Νέος κωδικός";
$lang["newpasswordrepeat"] = "Επανάληψη νέου κωδικού";
$lang["changepass"] = "Αλλαγή κωδικού";
$lang["gps"] = "GPS";
$lang["network"] = "Δίκτυο";
$lang["deluser"] = "Διαγραφή Χρήστη";
$lang["edituser"] = "Επεξεργασία Χρήστη";
$lang["servererror"] = "Σφάλμα του διακομιστή";
$lang["allrequired"] = "Όλα τα πεδία απαιτούνται";
$lang["passnotmatch"] = "Οι κωδικοί δεν ταιριάζουν";
$lang["oldpassinvalid"] = "Λάθος παλαιός κωδικός";
$lang["passempty"] = "Κενός κωδικός";
$lang["loginempty"] = "Κενή σήνδεση";
$lang["passstrengthwarn"] = "Λάθος βαθμός δυσκολίας κωδικού";
$lang["actionsuccess"] = "Ολοκληρώθηκε επιτυχώς";
$lang["actionfailure"] = "Κάτι πήγε στραβά";
$lang["notauthorized"] = "Μη εξουσιοδοτημένος Χρήστης";
$lang["userunknown"] = "Άγνωστος Χρήστης";
$lang["userdelwarn"] = "Προσοχή!\n\nΠρόκειται να διαγράψετε το Χρήστη %s και τις Διαδρομές/Θέσεις του μονίμως.\n\nΕίστε σίγουροι;"; // substitutes user login
$lang["editinguser"] = "Τροποποιείτε τον Χρήστη %s"; // substitutes user login
$lang["selfeditwarn"] = "Δεν γίνεται ιδία τροποποίηση Χρήστη με αυτό το εργαλείο";
$lang["apifailure"] = "Λυπάμαι, δεν μπορώ να φορτώσω το API του %s"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Προσοχή!\n\nΠρόκειται να διαγράψετε μονίμως τη Διαδρομή %s και όλες τις θέσεις αυτής.\n\nΕίστε σίγουροι;"; // substitutes track name
$lang["editingtrack"] = "Τροποποιείτε τη Διαδρομή %s"; // substitutes track name
$lang["deltrack"] = "Διαγραφή Διαδρομής";
$lang["trackname"] = "Όνομα Διαδρομής";
$lang["edittrack"] = "Τροποποιείστε τη Διαδρομή";
$lang["positiondelwarn"] = "Προσοχή!\n\nΠρόκειται να διαγράψετε μονίμως τη Θέση %d της Διαδρομής %s.\n\nΕίστε σίγουροι;"; // substitutes position index and track name
$lang["editingposition"] = "Τροποποιείτε τη Θέση #%d της Διαδρομής %s"; // substitutes position index and track name
$lang["delposition"] = "Διαγραφή Θέσης";
$lang["delimage"] = "Διαγραφή εικόνας";
$lang["comment"] = "Σχόλιο";
$lang["image"] = "Εικόνα";
$lang["editposition"] = "Τροποποιείστε τη Θέση";
$lang["passlenmin"] = "Ο κωδικός σας πρέπει να έχει τουλάχιστον %d χαρακτήρες"; // substitutes password minimum length
$lang["passrules_1"] = "Θα πρέπει να έχει τουλάχιστον έναν πεζό και έναν κεφαλαίο";
$lang["passrules_2"] = "Θα πρέπει να έχει τουλάχιστον έναν πεζό, έναν κεφαλαίο και έναν αριθμό";
$lang["passrules_3"] = "Θα πρέπει να έχει τουλάχιστον έναν πεζό, έναν κεφαλαίο, έναν αριθμό και έναν ειδικό χαρακτήρα";
$lang["owntrackswarn"] = "Μπορείτε να τροποποίσετε μόνο τις δικές σας Διαδρομές";
$lang["gmauthfailure"] = "Υπάρχει μάλλον πρόβλημα με το κλειδί API του Google Maps σε αυτή τη σελίδα";
$lang["gmapilink"] = "Βρείτε πληροφορίες για τα κλειδιά API keys σε <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">αυτή</a> τη σελίδα της google";
$lang["import"] = "Εισαγωγή Διαδρομής";
$lang["iuploadfailure"] = "Αποτυχία αποστολής";
$lang["iparsefailure"] = "Αποτυχία ανάλυσης";
$lang["idatafailure"] = "Δεν βρέθηκαν στοιχεία Διαδρομής στο εισαχθέν αρχείο";
$lang["isizefailure"] = "Το αρχείο προς αποστολήν δεν θα πρέπει να ξεπερνά τα %d bytes"; // substitutes number of bytes
$lang["imultiple"] = "Πολλαπλές Διαδρομές εισήχθησαν (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Όλοι οι Χρήστες";
$lang["unitday"] = "μέρες"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "χ/ώ"; // kilometer per hour
$lang["unitm"] = "μ"; // meter
$lang["unitamsl"] = "Πάνω από το επίπεδο της θαλάσσης"; // above mean see level
$lang["unitkm"] = "χιλ"; // kilometer
$lang["unitmph"] = "mph"; // mile per hour
$lang["unitft"] = "πόδια"; // feet
$lang["unitmi"] = "μίλια"; // mile
$lang["unitkt"] = "κόμβοι"; // knot
$lang["unitnm"] = "ν.μ."; // nautical mile
$lang["config"] = "Ρυθμίσεις";
$lang["editingconfig"] = "Προεπιλεγμένες Ρυθμίσεις";
$lang["latitude"] = "Θέση Γεωγρ. πλάτους κατά το ξεκίνημα";
$lang["longitude"] = "Θέση Γεωγρ. μήκους κατά το ξεκίνημα";
$lang["interval"] = "Περίοδος (δευτ.)";
$lang["googlekey"] = "Google Maps API key";
$lang["passlength"] = "Ελάχιστο αποδεκτό μήκος Κωδικού";
$lang["passstrength"] = "Ελάχιστος βαθμός δυσκολίας κωδικού";
$lang["requireauth"] = "Απαιτείται ταυτοποίηση";
$lang["publictracks"] = "Δημoσιευμένες Διαδρομές";
$lang["strokeweight"] = "Βάρος μολυβιάς";
$lang["strokeopacity"] = "Διαφάνεια μολυβιάς";
$lang["strokecolor"] = "Χρώμα μολυβιάς";
$lang["colornormal"] = "Χρώμα οριοθέτησης";
$lang["colorstart"] = "Χρώμα αρχής οριοθέτησης";
$lang["colorstop"] = "Χρώμα τέλους οριοθέτησης";
$lang["colorextra"] = "Χρώμα ειδικής οριοθέτησης";
$lang["colorhilite"] = "Χρώμα επισήμανσης";
$lang["uploadmaxsize"] = "Μέγιστο επιτρεπόμενο μέγεθος απεσταλμένου (MB)";
$lang["ollayers"] = "Επικάλυψη OpenLayers";
$lang["layername"] = "Όνομα επικάλυψης";
$lang["layerurl"] = "URL επικάλυψης";
$lang["add"] = "Προσθήκη";
$lang["edit"] = "Τροποποίηση";
$lang["delete"] = "Διαγραφή";
$lang["settings"] = "Ρυθμίσεις";
$lang["trackcolor"] = "Χρώμα Διαδρομής";
?>
