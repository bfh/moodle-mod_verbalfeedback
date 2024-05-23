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

$string['activity'] = 'Activité';
$string['additem'] = 'Ajouter un nouveau élément';
$string['addanewquestion'] = 'Ajouter une nouvelle question';
$string['allowundodecline'] = 'Permettre aux étudiant-e-s d\'annuler les commentaires refusés';
$string['allparticipants'] = 'Tout-e-s les participant-e-s';
$string['anonymous'] = 'Anonyme';
$string['averagerating'] = 'Note moyenne :';
$string['averagevalue'] = 'Moyenne';
$string['backtoverbalfeedbackdashboard'] = 'Retour au sommaire';
$string['calendarend'] = '{$a} ferme';
$string['calendarstart'] = '{$a} ouvre';
$string['categoryheader'] = 'Titre de la catégorie';
$string['categoryplural'] = 'Categories';
$string['categoryweight'] = 'Poids de la catégorie';
$string['closebeforeopen'] = 'Vous avez indiqué une date de fermeture antérieure à la date d\'ouverture.';
$string['commentfromuser'] = '{$a->comment} ({$a->fromuser})';
$string['comments'] = 'Commentaires';
$string['confirmquestiondeletion'] = 'Etes-vous sûr de vouloir supprimer cette question ?';
$string['confirmfinaliseanonymousfeedback'] = 'Ceci rendra vos réponses anonymes sur votre feedback pour {$a->nom}. Vous ne pourrez plus modifier vos réponses une fois que cela aura été fait. Poursuivre ?';
$string['course'] = 'Cours';
$string['criteria'] = 'Critères';
$string['criterion'] = 'Critère';
$string['dataformatinvalid'] = 'Le format de fichier spécifié pour le téléchargement de ce rapport n\'est pas valide ou n\'est pas activé. Veuillez sélectionner un format de fichier valide';
$string['decline'] = 'Refuser';
$string['declinefeedback'] = 'Refuser feedback';
$string['declineheading'] = 'Refuser le feedback verbal pour {$a}';
$string['declinereason'] = 'Veuillez indiquer la raison pour laquelle vous refusez ';
$string['declinereasonplaceholdertext'] = 'Entrez votre raison ici ... (facultatif)';
$string['deletecategory'] = 'Supprimer la catégorie';
$string['deletecriterion'] = 'Supprimer le critère';
$string['deleteitem'] = 'Supprimer l\'élément';
$string['deletelanguage'] = 'Supprimer la langue';
$string['deletequestion'] = 'Supprimer la question';
$string['deletetemplate'] = 'Supprimer le template';
$string['detailrating'] = 'Éléments de feedback';
$string['done'] = 'Terminé';
$string['download'] = 'Download';
$string['downloadreportas'] = 'Télécharger le rapport de feedback verbal sous...';
$string['downloadtemplate'] = 'Download template';
$string['editcategory'] = 'Modifier la catégorie';
$string['editcriterion'] = 'Modifier le critère';
$string['edititems'] = 'Modifier les éléments de feedback verbal';
$string['editlanguage'] = 'Modifier la langue';
$string['editquestion'] = 'Modifier la question';
$string['edittemplate'] = 'Modifier le template';
$string['enableselfreview'] = 'Activer l\'autorévision';
$string['entercomment'] = 'Entrez votre commentaire ici';
$string['enterquestion'] = 'Saisir le texte de la question...';
$string['errorverbalfeedbacknotfound'] = 'Feedback verbal non trouvé';
$string['errorblankquestion'] = 'Requis';
$string['errorblankdeclinereason'] = 'Requis';
$string['errorcannotadditem'] = 'Impossible d\'ajouter l\'élément feedback.';
$string['errorcannotparticipate'] = 'Vous ne pouvez pas participer à cette activité de feedback verbal';
$string['errorcannotrespond'] = 'Vous ne pouvez pas répondre à cette activité de feedback verbal';
$string['errorcannotupdateitem'] = 'Impossible de mettre à jour l\'élément de feedback verbal';
$string['errorcannotviewallreports'] = 'Vous ne pouvez pas afficher les résultats des autres participants.';
$string['erroritemnotfound'] = 'L\'élément n\'a pas été trouvé.';
$string['errorinvalidstatus'] = 'Statut invalide';
$string['errornocaptoedititems'] = 'Désolé, mais vous n\'avez pas la possibilité de modifier les éléments de feedback verbal';
$string['errornotenrolled'] = 'Vous devez être inscrit à ce cours pour pouvoir participer à cette activité de feedback verbal';
$string['errornotingroup'] = 'Vous devez être dans un groupe pour pouvoir participer à cette activité de feedback verbal. Veuillez contacter votre administrateur de cours.';
$string['errornothingtodecline'] = 'Il n\'y a pas de feedback à refuser';
$string['errorquestionstillinuse'] = 'Cette question ne peut pas être supprimée car elle est toujours utilisée par au moins une instance de feedback verbal.';
$string['errorreportnotavailable'] = 'Votre rapport de feedback n\'est pas encore disponible';
$string['errorresponsesavefailed'] = 'Une erreur s\'est produite lors de la sauvegarde des réponses. Veuillez réessayer plus tard.';
$string['errorroleconflict'] = 'L\'utilisateur actuel est à la fois élève et professeur pour cette instance, ce qui n\'est pas autorisé';
$string['factor'] = 'Facteur';
$string['feedbacksurvey'] = 'Questionnaire feedback pour {$a}';
$string['feedbackgiven'] = 'Feedback donné';
$string['feedbackreceived'] = 'Feedback reçu';
$string['finalize'] = 'Terminer évaluation';
$string['finaliseanonymousfeedback'] = 'Terminer feedback anonyme';
$string['finalresult'] = 'Résultat final';
$string['gotoquestionbank'] = 'Aller à la banque de questions du feelback verbal';
$string['id'] = 'ID';
$string['instancealreadyclosed'] = 'L\'activité de feedback verbal est déjà terminée';
$string['instancenotready'] = 'Cliquez sur le bouton «Rendre disponible» pour diffuser le questionnaire aux enseignants après avoir modifié les éléments de rétroaction verbale.';
$string['instancenotreadystudents'] = 'L\'activité de rétroaction verbale n\'est pas encore prête. Veuillez réessayer plus tard. ';
$string['instancenotyetopen'] = 'L\'activité de feedback verbal n\'est pas encore ouverte.';
$string['languageplural'] = 'Langues';
$string['labelettingcategoriesdescription'] = 'Catégories qui peuvent être utilisées pour organiser des questions de feedback verbal';
$string['listcategories'] = 'Liste des catégories';
$string['listcriteria'] = 'Liste des critères';
$string['instancenowready'] = 'L\'outil est prêt';
$string['messageafterdecline'] = 'Feedback refusé.';
$string['modulename'] = 'Feedback verbal';
$string['modulename_help'] = 'Le module d\'activité de feedback verbal permet aux étudiant-e-s de fournir un feedback à tous les autres étudiant-e-s';
$string['modulenameplural'] = 'feedbacks verbals';
$string['moveitemdown'] = 'Déplacer l\'objet vers le bas';
$string['moveitemup'] = 'Déplacer l\'objet vers le haut';
$string['multiplier'] = 'Multiplicateur';
$string['negative'] = 'Négatif';
$string['newcategory'] = 'Nouvelle catégorie';
$string['newcriterion'] = 'Nouveau critère';
$string['newlanguage'] = 'Nouvelle langue';
$string['newtemplate'] = 'Nouveau template';
$string['nocriterion'] = 'Aucun critère';
$string['noitemsyet'] = 'L\'activité de retour verbal n\'a pas encore d\'éléments. Ajoutez des éléments en cliquant sur "Modifier les éléments du feedback verbal".';
$string['notapplicableabbr'] = 'N/A';
$string['notemplate'] = 'Aucun template';
$string['numrespondents'] = 'Nombre de répondants';
$string['openafterclose'] = 'Vous avez indiqué une date d\'ouverture postérieure à la date de fermeture.';
$string['percentage'] = 'Pourcentage';
$string['pick'] = 'Sélectionner';
$string['pickfromquestionbank'] = 'Choisissez une question dans la banque de questions';
$string['placeholderquestion'] = 'Saisissez le texte de la question';
$string['pluginname'] = 'Feedback verbal';
$string['pluginadministration'] = 'Administration des commentaires verbaux';
$string['position'] = 'Position';
$string['positive'] = 'Positif';
$string['preserveids'] = 'Preserve IDs?';
$string['previewinfo'] = 'Prévisualisez le questionnaire, cliquez sur <a href="{$a}">ici</a> pour revenir à la vue précédente.';
$string['privacy:metadata:instanceid'] = 'L\'ID de l\'instance de feedback verbal';
$string['privacy:metadata:verbalfeedback_item'] = 'L\'ID de l\'élément de feedback verbal';
$string['privacy:metadata:verbalfeedback_response'] = 'Ce tableau stocke les réponses du répondant aux questions de feedback au destinataire de la rétroaction';
$string['privacy:metadata:verbalfeedback_response:value'] = 'La valeur de la réponse du répondant à la question de feedback';
$string['privacy:metadata:verbalfeedback_submission'] = 'Ce tableau stocke les informations sur les statuts des réponses aux questions de feedback entre les participants';
$string['privacy:metadata:verbalfeedback_submission:fromuserid'] = 'L\'ID utilisateur de la personne qui donne le feedback';
$string['privacy:metadata:verbalfeedback_submission:remarks'] = 'La raison pour laquelle la personne interrogée a refusé de donner un feedback au destinataire du feedback';
$string['privacy:metadata:verbalfeedback_submission:status'] = 'Le statut de la soumission du feedback';
$string['privacy:metadata:verbalfeedback_submission:touserid'] = 'L\'ID utilisateur du destinataire du feedback';
$string['privatecomment'] = 'Commentaire privé';
$string['providefeedback'] = 'Fournir un feedback';
$string['qtypecomment'] = 'Commentaire';
$string['qtypeinvalid'] = 'Type de question non valable';
$string['qtyperated'] = 'Noté';
$string['question'] = 'Question';
$string['questiontext'] = 'Texte de la question';
$string['questiontype'] = 'Type de question';
$string['questioncategory'] = 'Catégorie';
$string['ratingaverage'] = 'Note moyenne';
$string['ratings'] = 'Notes';
$string['rel_after'] = 'Publication après la fermeture de l\'activité';
$string['rel_closed'] = 'Fermé aux étudiant-e-s';
$string['rel_manual'] = 'Publication manuelle';
$string['rel_open'] = 'Ouvert aux étudiant-e-s';
$string['release'] = 'Publier le rapport aux étudiant-e-s';
$string['release_close'] = 'Fermer le rapport aux étudiant-e-s';
$string['releasetype'] = 'Publication';
$string['releasetype_help'] = 'S\'il faut laisser les étudiant-e-s consulter le rapport des commentaires qui leur ont été donnés.
<ul>
<li>Fermé aux étudiant-e-s. Les étudiant-e-s ne peuvent pas consulter leur propre rapport de feedback. Seuls ceux qui ont la capacité de gérer l\'activité de feedback verbal (par exemple, l\'enseignant, le responsable, l\'administrateur) peuvent voir les rapports de feedback des étudiant-e-s.
<li>Ouvert aux étudiant-e-s. Les étudiant-e-s peuvent consulter leur propre rapport de feedback à tout moment.</li>
<li>Publication manuelle. Les étudiant-e-s peuvent consulter leur propre rapport de rétroaction lorsqu\'il est publié par un utilisateur qui a la capacité de gérer l\'activité de feedback verbal.
<li>Publication après la fermeture de l\'activité. Les étudiant-e-s peuvent consulter leur propre rapport de feedback après la fin de l\'activité.</li>
</ul>';
$string['reportimage'] = 'Logo d\'en-tête rapports PDF';
$string['reportimage_desc'] = 'L\'image à afficher comme logo d\'en-tête dans les rapports de feedback.';
$string['responses'] = 'réponses';
$string['responsessaved'] = 'Vos réponses ont été enregistrées';
$string['saveandreturn'] = 'Sauvegarde et retour';
$string['scale'] = 'Échelle';
$string['scaleagree'] = 'Rempli';
$string['scaledisagree'] = 'Non rempli';
$string['scalenotapplicable'] = 'Non applicable';
$string['scalesomewhatagree'] = 'Plutôt d\'accord';
$string['scalesomewhatdisagree'] = 'Pas vraiment d\'accord';
$string['scalestronglyagree'] = 'Tout à fait d\'accord';
$string['scalestronglydisagree'] = 'Pas du tout d\'accord';
$string['selectparticipants'] = 'Choisir participant-e';
$string['startend'] = 'Début - Fin';
$string['status'] = 'État';
$string['statuscompleted'] = 'Terminé';
$string['statusdeclined'] = 'Refusé';
$string['statusinprogress'] = 'En cours';
$string['statuspending'] = 'En attente';
$string['statusviewonly'] = 'Afficher seulement';
$string['student'] = 'Etudiant-e';
$string['studentcomment'] = 'Remarques pour l\'étudiant-e';
$string['submissions'] = 'Soumissions';
$string['subrating'] = 'Subrating';
$string['subratingplural'] = 'Subratings';
$string['switchtouser'] = 'Changer vers étudiant-e ...';
$string['teachers'] = 'Professerus';
$string['template'] = 'Template';
$string['templateplural'] = 'Templates';
$string['templatecategoryplural'] = 'Catégories pour les templates';
$string['templatecriteriaplural'] = 'Critères pour les templates';
$string['text'] = 'Text';
$string['title'] = 'Feedback verbal';
$string['titlelabel'] = 'Title';
$string['titlemanageitems'] = 'Gérer les éléments de feedback verbal';
$string['todo'] = 'To Do';
$string['totalpercentage'] = 'Total';
$string['undodecline'] = 'Annuler la refusion';
$string['valuation'] = 'Évaluation';
$string['value'] = 'Valeur';
$string['verbalfeedback:addinstance'] = 'Ajouter une nouvelle instance de feedback verbal';
$string['verbalfeedback:complete'] = 'Compléter un feedback verbal';
$string['verbalfeedback:edititems'] = 'Éditer éléments';
$string['verbalfeedback:editquestions'] = 'Modifier les questions de feedback verbal';
$string['verbalfeedback:mapcourse'] = 'Adapter le feedback verbal au cours';
$string['verbalfeedback:receivemail'] = 'Recevoir un courriel de feedback verbal';
$string['verbalfeedback:receive_rating'] = 'L\'utilisateur/utilisatrice peut être évalué-e';
$string['verbalfeedback:view'] = 'Voir les commentaires verbaux';
$string['verbalfeedback:viewanalysepage'] = 'Voir l\'analyse du feedback verbal';
$string['verbalfeedback:view_all_reports'] = 'Voir les rapports de feedback verbal de tous les étudiant-e-s';
$string['verbalfeedbacksettings'] = 'Paramètres du feedback verbal';
$string['verbalfeedbacklanguages'] = 'Langues du feedback verbal';
$string['verbalfeedbacksettings'] = 'Paramètres du feedback verbal';
$string['viewfeedbackforuser'] = 'Voir feedback pour étudiant-e';
$string['viewfeedbackreport'] = 'Voir le rapport du feedback';
$string['verbalfeedbackcategories'] = 'Catégories de feedback verbal';
$string['verbalfeedbackcriteria'] = 'Critères de feedback verbal';
$string['verbalfeedbacktemplates'] = 'Templates de feedback verbal';
$string['verynegative'] = 'Très négatif';
$string['verypositive'] = 'Très positif';
$string['weight'] = 'Poids';
$string['weightedaverage'] = 'Ø pondéré';
