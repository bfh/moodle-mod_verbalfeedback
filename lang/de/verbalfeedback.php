<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_verbalfeedback
 * @category    string
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activity'] = 'Aktivität';
$string['additem'] = 'Kriterium hinzufügen';
$string['addanewquestion'] = 'Eine neue Frage hinzufügen';
$string['allowundodecline'] = 'Teilnehmer/innen erlauben, das Einreichen eines abgelehnten Feedbacks rückgängig zu machen';
$string['allparticipants'] = 'Alle Kursteilnehmer/innen';
$string['anonymous'] = 'Anonym';
$string['averagerating'] = 'Durchschnittsbewertung:';
$string['averagevalue'] = 'Ø-Wert';
$string['backtoverbalfeedbackdashboard'] = 'Zurück zur Übersicht';
$string['calendarend'] = '{$a} schliesst';
$string['calendarstart'] = '{$a} öffnet';
$string['categoryheader'] = 'Kategorie-Titel';
$string['categoryplural'] = 'Kategorien';
$string['categoryweight'] = 'Kategoriegewichtung';
$string['closebeforeopen'] = 'Das Enddatum kann nicht vor dem Startdatum liegen.';
$string['commentfromuser'] = '{$a->comment} ({$a->fromuser})';
$string['comments'] = 'Kommentare';
$string['confirmquestiondeletion'] = 'Wollen Sie diese Frage wirklich löschen?';
$string['confirmfinaliseanonymousfeedback'] = 'Damit werden Ihre Antworten auf Ihr Feedback für {$a->name} anonymisiert. Sie werden Ihre Antworten nicht mehr ändern können, sobald dies geschehen ist. Möchten Sie den Vorgang fortsetzen?';
$string['course'] = 'Kurs';
$string['criteria'] = 'Kriterien';
$string['criterion'] = 'Kriterium';
$string['dataformatinvalid'] = 'Das Dateiformat, das zum Herunterladen dieses Berichts angegeben wurde, ist entweder ungültig oder nicht aktiviert. Bitte wählen Sie ein gültiges Dateiformat.';
$string['decline'] = 'Ablehnen';
$string['declinefeedback'] = 'Feedback ablehnen';
$string['declineheading'] = 'Verbales Feedback für {$a} ablehnen';
$string['declinereason'] = 'Bitte geben Sie einen Grund an, warum Sie dieses Feedback ablehnen.';
$string['declinereasonplaceholdertext'] = 'Geben Sie hier Ihren Grund ein... (Optional)';
$string['deletecategory'] = 'Kategorie löschen';
$string['deletecriterion'] = 'Kriterium löschen';
$string['deleteitem'] = 'Kriterium löschen';
$string['deletelanguage'] = 'Sprache löschen';
$string['deletequestion'] = 'Frage löschen';
$string['deletetemplate'] = 'Template löschen';
$string['detailrating'] = 'Feedbackelemente';
$string['done'] = 'Erledigt';
$string['download'] = 'Download';
$string['downloadreportas'] = 'Feedbackbericht herunterladen als...';
$string['downloadtemplate'] = 'Download template';
$string['editcategory'] = 'Kategorie bearbeiten';
$string['editcriterion'] = 'Kriterium bearbeiten';
$string['edititems'] = 'Kriterien des verbalen Feedbacks bearbeiten';
$string['editlanguage'] = 'Sprache bearbeiten';
$string['editquestion'] = 'Frage bearbeiten';
$string['edittemplate'] = 'Template bearbeiten';
$string['enableselfreview'] = 'Selbstbeurteilung aktivieren';
$string['entercomment'] = 'Geben Sie hier Ihren Kommentar ein.';
$string['enterquestion'] = 'Fragetext eingeben...';
$string['errorverbalfeedbacknotfound'] = 'Verbales Feedback nicht gefunden.';
$string['errorblankquestion'] = 'Erforderlich.';
$string['errorblankdeclinereason'] = 'Erforderlich.';
$string['errorcannotadditem'] = 'Konnte das Kriterium des verbalen Feedbacks nicht hinzufügen.';
$string['errorcannotparticipate'] = 'Sie können an dieser verbalen Feedback-Aktivität nicht teilnehmen.';
$string['errorcannotrespond'] = 'Sie können in dieser verbalen Feedback-Aktivität kein Feedback geben.';
$string['errorcannotupdateitem'] = 'Konnte das Kriterium des verbalen Feedbacks nicht aktualisieren.';
$string['errorcannotviewallreports'] = 'Sie können nicht die Bewertungen anderer Teilnehmer*innen sehen.';
$string['erroritemnotfound'] = 'Das Kriterium des verbalen Feedbacks wurde nicht gefunden.';
$string['errorinvalidstatus'] = 'Ungültiger Status';
$string['errornocaptoedititems'] = 'Sie haben aktuell kein Recht, verbales Feedback-Kriterien zu bearbeiten';
$string['errornotenrolled'] = 'Sie müssen in diesem Kurs eingeschrieben sein, um an dieser verbalen Feedback-Aktivität teilnehmen zu können.';
$string['errornotingroup'] = 'Sie müssen Mitglied einer Gruppe sein, um an dieser verbalen Feedback-Aktivität teilnehmen zu können. Bitte kontaktieren Sie Ihren Kursadministrator.';
$string['errornothingtodecline'] = 'Es gibt kein Feedback zum Ablehnen.';
$string['errorquestionstillinuse'] = 'Diese Frage kann nicht gelöscht werden, da sie noch von mindestens einer verbalen Feedback-Instanz verwendet wird.';
$string['errorreportnotavailable'] = 'Ihr Feedbackbericht ist noch nicht verfügbar.';
$string['errorresponsesavefailed'] = 'Während des Speicherns der Antworten ist ein Fehler aufgetreten. Bitte versuchen Sie es später noch einmal.';
$string['errorroleconflict'] = 'Die/der aktuelle Nutzer/in ist für diese Instanz gleichzeitig Teilnehmer/in und Trainer/in, was nicht erlaubt ist.';
$string['factor'] = 'Faktor';
$string['feedbacksurvey'] = 'Feedback-Umfrage für {$a}';
$string['feedbackgiven'] = 'Feedback gegeben';
$string['feedbackreceived'] = 'Feedback erhalten';
$string['finalize'] = 'Bewertung abschliessen';
$string['finaliseanonymousfeedback'] = 'Anonymes Feedback abschliessen';
$string['finalresult'] = 'Schlussresultat';
$string['gotoquestionbank'] = 'Zur verbales Feedback-Fragensammlung gehen';
$string['id'] = 'ID';
$string['instancealreadyclosed'] = 'Dieses verbale Feedback-Aktivität wurde bereits beendet.';
$string['instancenotready'] = 'Klicken Sie auf die Schaltfläche „Verfügbar machen“, um den Fragebogen nach der Bearbeitung der Kriterien an die Lehrkräfte freizugeben.';
$string['instancenotreadystudents'] = 'Die verbale Feedback-Aktivität ist noch nicht bereit. Bitte versuchen Sie es später erneut.';
$string['instancenotyetopen'] = 'Die verbale Feedback-Aktivität ist noch nicht geöffnet.';
$string['languageplural'] = 'Sprachen';
$string['listcategories'] = 'Liste der Kategorien';
$string['listcriteria'] = 'Liste der Kriterien';
$string['instancenowready'] = 'Der Bewertungsbogen ist nun freigeschaltet.';
$string['managetemplates'] = 'Templates verwalten';
$string['messageafterdecline'] = 'Feedback abgelehnt.';
$string['modulename'] = 'Verbales Feedback';
$string['modulename_help'] = 'Mit dem verbalen Feedback ermöglichen Sie es Teilnehmer/innen, allen anderen Teilnehmer/innen Feedback zu geben.';
$string['modulenameplural'] = 'verbale Feedbacks';
$string['moveitemdown'] = 'Kriterium nach unten verschieben';
$string['moveitemup'] = 'Kriterium nach oben verschieben';
$string['multiplier'] = 'Multiplikator';
$string['negative'] = 'Negativ';
$string['newcategory'] = 'Neue Kategorie';
$string['newcriterion'] = 'Neues Kriterium';
$string['newlanguage'] = 'Neue Sprache';
$string['newtemplate'] = 'Neues Template';
$string['nocriterion'] = 'Kein Kriterium';
$string['noitemsyet'] = 'Die verbale Feedback-Aktivität hat noch keine Kriterien. Fügen Sie Kriterien hinzu, indem Sie auf "Kriterien des verbalen Feedbacks bearbeiten" klicken.';
$string['notapplicableabbr'] = 'N/A';
$string['notemplate'] = 'Kein Template';
$string['numrespondents'] = 'Anzahl Beantwortender';
$string['openafterclose'] = 'Das Enddatum kann nicht vor dem Startdatum liegen';
$string['percentage'] = 'Prozentanteil';
$string['pick'] = 'Auswählen';
$string['pickfromquestionbank'] = 'Eine Frage aus der Fragensammlung auswählen';
$string['placeholderquestion'] = 'Fragetext eingeben';
$string['pluginname'] = 'Verbales Feedback';
$string['pluginadministration'] = 'Verbales Feedback-Administration';
$string['position'] = 'Position';
$string['positive'] = 'Positiv';
$string['preserveids'] = 'Preserve IDs?';
$string['previewinfo'] = 'Vorschau des Fragebogens, klicken Sie <a href="{$a}">hier</a>, um zur vorherigen Ansicht zurückzukehren.';
$string['privacy:metadata:verbalfeedback'] = 'Die ID der Verbales Feedback-Instanz';
$string['privacy:metadata:verbalfeedback_item'] = 'Die ID des Verbales Feedback-Kriteriums';
$string['privacy:metadata:verbalfeedback_response'] = 'Diese Tabelle speichert die Antworten der Feedbackgeber auf die Feedbackfragen an den Feedbackempfänger';
$string['privacy:metadata:verbalfeedback_response:value'] = 'Der Wert der Antwort des Feedbackgebers auf die Feedbackfrage';
$string['privacy:metadata:verbalfeedback_submission'] = 'Diese Tabelle speichert die Informationen über den Status der verbalen Feedback-Abgaben zwischen den Teilnehmer/innen';
$string['privacy:metadata:verbalfeedback_submission:fromuser'] = 'Die Nutzer-ID der Person, die das Feedback gibt';
$string['privacy:metadata:verbalfeedback_submission:remarks'] = 'Der Grund, warum der Befragte es abgelehnt hat, dem Feedback-Empfänger ein Feedback zu geben';
$string['privacy:metadata:verbalfeedback_submission:status'] = 'Der Status der Feedback-Abgabe';
$string['privacy:metadata:verbalfeedback_submission:touser'] = 'Die Nutzer-ID des Feedback-Empfängers';
$string['privatecomment'] = 'Private Bemerkungen';
$string['providefeedback'] = 'Feedback geben';
$string['qtypecomment'] = 'Bemerkung';
$string['qtypeinvalid'] = 'Ungültiger Fragetyp';
$string['qtyperated'] = 'Bewertet';
$string['question'] = 'Frage';
$string['questiontext'] = 'Fragetext';
$string['questiontype'] = 'Fragetyp';
$string['questioncategory'] = 'Kateogrie';
$string['ratingaverage'] = 'Durchschnittliche Bewertung';
$string['ratings'] = 'Bewertungen';
$string['rel_after'] = 'Freigabe nach Abschluss der Aktivität';
$string['rel_closed'] = 'Geschlossen für Teilnehmer/innen';
$string['rel_manual'] = 'Manuelle Freigabe';
$string['rel_open'] = 'Offen für Teilnehmer/innen';
$string['release'] = 'Berichte für Teilnehmer/innen freigeben';
$string['release_close'] = 'Berichte für Teilnehmer/innen schliessen';
$string['releasetype'] = 'Freigabe';
$string['releasetype_help'] = 'Ob die Teilnehmer/innen den Bericht über das gegebene Feedback sehen können.
<ul>
<li>Geschlossen für Teilnehmer/innen. Die Teilnehmer/innen können ihren eigenen Feedbackbericht nicht einsehen. Nur diejenigen, die die Möglichkeit haben, die verbale Feedback-Aktivität zu verwalten (z. B. Trainer/innen, Manager/innen, Administrator/innen), können die Feedback-Berichte der Teilnehmer/innen einsehen.</li>
<li>Offen für Teilnehmer/innen. Die Teilnehmer/innen können ihren eigenen Feedbackbericht jederzeit einsehen.</li>
<li>Manuelle Freigabe. Teilnehmer/innen können ihren eigenen Feedbackbericht einsehen, wenn sie von einer/m Nutzer/in freigegeben werden, die/der die Fähigkeit hat, die Aktivität für verbales Feedback zu verwalten.</li>
<li>Freigabe nach Abschluss der Aktivität. Die Teilnehmer/innen können ihren eigenen Feedbackbericht einsehen, nachdem die Aktivität beendet wurde.</li>
</ul>';
$string['reportimage'] = 'Report PDF header logo';
$string['reportimage_desc'] = 'Das Bild, das als Header-Logo in den heruntergeladenen Feedbackberichten angezeigt werden soll.';
$string['responses'] = 'Antworten';
$string['responsessaved'] = 'Ihre Antworten wurden gespeichert.';
$string['saveandreturn'] = 'Speichern und zurück';
$string['scale'] = 'Skala';
$string['scaleagree'] = 'Erfüllt';
$string['scaledisagree'] = 'Nicht erfüllt';
$string['scalenotapplicable'] = 'Nicht bewertet';
$string['scalesomewhatagree'] = 'Mehrheitlich erfüllt';
$string['scalesomewhatdisagree'] = 'Mehrheitlich nicht erfüllt';
$string['scalestronglyagree'] = 'Erwartungen übertroffen';
$string['scalestronglydisagree'] = 'Nicht vorhanden';
$string['selectparticipants'] = 'Teilnehmer/in auswählen';
$string['startend'] = 'Start - Ende';
$string['status'] = 'Status';
$string['statuscompleted'] = 'Abgeschlossen';
$string['statusdeclined'] = 'Abgelehnt';
$string['statusinprogress'] = 'In Bearbeitung';
$string['statuspending'] = 'Ausstehend';
$string['statusviewonly'] = 'Nur anzeigen';
$string['student'] = 'Teilnehmer/in';
$string['studentcomment'] = 'Bemerkungen für Teilnehmer/in';
$string['submissions'] = 'Einreichungen';
$string['subrating'] = 'Subrating';
$string['subratingplural'] = 'Subratings';
$string['switchtouser'] = 'Zu Teilnehmer/in wechseln...';
$string['teachers'] = 'Dozierende';
$string['template'] = 'Template';
$string['templateplural'] = 'Templates';
$string['templatecategoryplural'] = 'Template-Kategorien';
$string['templatecriteriaplural'] = 'Template-Kriterien';
$string['text'] = 'Text';
$string['title'] = 'Verbales Feedback';
$string['titlelabel'] = 'Titel';
$string['titlemanageitems'] = 'Verbales Feedback-Kriterien bearbeiten';
$string['todo'] = 'To Do';
$string['totalpercentage'] = 'Total';
$string['undodecline'] = 'Ablehnung rückgängig machen';
$string['valuation'] = 'Beurteilung';
$string['value'] = 'Wert';
$string['verbalfeedback:addinstance'] = 'Verbales Feedback hinzufügen';
$string['verbalfeedback:complete'] = 'Verbales Feedback abschliessen';
$string['verbalfeedback:edititems'] = 'Verbales Feedback-Kriterien bearbeiten';
$string['verbalfeedback:editquestions'] = 'Verbales Feedback-Fragen bearbeiten';
$string['verbalfeedback:mapcourse'] = 'Verbales Feedback einem Kurs zuordnen';
$string['verbalfeedback:receivemail'] = 'Verbales Feedback-E-Mails empfangen';
$string['verbalfeedback:receive_rating'] = 'Teilnehmer/in kann bewertet werden';
$string['verbalfeedback:view'] = 'Verbales Feedback anzeigen';
$string['verbalfeedback:viewanalysepage'] = 'Verbales Feedback-Analyse anzeigen';
$string['verbalfeedback:view_all_reports'] = 'Verbales Feedback-Berichte aller Teilnehmer/innen anzeigen';
$string['verbalfeedbacksettings'] = 'Verbales Feedback-Einstellungen';
$string['verbalfeedbacklanguages'] = 'Verbales Feedback-Sprachen';
$string['verbalfeedbacksettings'] = 'Verbales Feedback-Einstellungen';
$string['viewfeedbackforuser'] = 'Verbales Feedback für die/den Teilnehmer/in';
$string['viewfeedbackreport'] = 'Verbales Feedback-Bericht anzeigen';
$string['verbalfeedbackcategories'] = 'Verbales Feedback-Kategorien';
$string['verbalfeedbackcriteria'] = 'Verbales Feedback-Kriterien';
$string['verbalfeedbacktemplates'] = 'Verbales Feedback-Templates';
$string['verynegative'] = 'Sehr negativ';
$string['verypositive'] = 'Sehr positiv';
$string['weight'] = 'Gewichtung';
$string['weightedaverage'] = 'Gewichteter Ø';
