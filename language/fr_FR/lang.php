<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/* ---------------------------------
 * fr_FR Language File
 * Contributed by Claude Tompers (RESTENA) and Etienne Meleard (RENATER)
 * ---------------------------------
 */

/**
 * Page names / main links
 */
$lang['upload_page'] = 'Envoyer';
$lang['transfers_page'] = 'Dépôts';
$lang['guests_page'] = 'Invités';
$lang['admin_page'] = 'Admin';
$lang['download_page'] = 'Télécharger';
$lang['unknown_page'] = 'Page inconnue';
$lang['about'] = 'A propos';
$lang['about_page'] = 'A propos';
$lang['help'] = 'Aide';
$lang['help_page'] = 'Aide';
$lang['logoff'] = 'Déconnexion';

$lang['undergoing_maintenance'] = 'Cette application est en cours de maintenance.';
$lang['maintenance_autoresume'] = 'Les opérations en cours redémarreront automatiquement après la fin de la maintenance.';

$lang['authentication_required'] = 'Authentification requise';
$lang['authentication_required_explanation'] = 'Vous devez être authentifié pour effectuer cette opération. Votre session a peut être expiré ? Merci de vous ré-authentifier.';


/**
 * Locale settings (units, formats ...)
 */

// standard date display format
$lang['date_format'] = 'd/m/Y'; // Format for displaying date, use PHP date() format string syntax 
$lang['datetime_format'] = 'd/m/Y H:i:s'; // Format for displaying datetime, use PHP date() format string syntax 
$lang['time_format'] = '{h:H\h} {i:i\m\i\n} {s:s\s}'; // Format for displaying time (elapsed), use PHP date()'s h, i and s components, surrounding parts with {component:...} allow to not display them if zero

// datepicker localization
$lang['dp_close_text'] = 'OK';
$lang['dp_prev_text'] = 'Préc';
$lang['dp_next_text'] = 'Suiv';
$lang['dp_current_text'] = 'Aujourd\'hui';
$lang['dp_month_names'] = 'Janvier,Février,Mars,Avril,Mai,Juin,Juillet,Août,Septembre,Octobre,Novembre,Décembre';
$lang['dp_month_names_short'] = 'Jan,Fev,Mar,Avr,Mai,Jun,Jul,Aou,Sep,Oct,Nov,Dec';
$lang['dp_day_names'] = 'Dimanche,Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi';
$lang['dp_day_names_short'] = 'Dim,Lun,Mar,Mer,Jeu,Ven,Sam';
$lang['dp_day_names_min'] = 'Di,Lu,Ma,Me,Je,Ve,Sa';
$lang['dp_week_header'] = 'Sem';
$lang['dp_date_format'] = 'dd/mm/yy';
$lang['dp_date_format_hint'] = 'Format jj/mm/aa, max. {max} jours';
$lang['dp_first_day'] = '1';
$lang['dp_is_rtl'] = 'false';
$lang['dp_show_month_after_year'] = 'false';
$lang['dp_year_suffix'] = '';

// Sizes and speeds
$lang['size_unit'] = 'o';
$lang['speed_unit_bits'] = 'b/s';
$lang['speed_unit_bytes'] = 'o/s';

/**
 * General terms (used in several places)
 */
$lang['expand_all'] = 'Développer tout';
$lang['expires'] = 'Expire';
$lang['guests'] = 'Invités';
$lang['options'] = 'Options';
$lang['resume'] = 'Re-démarrer';
$lang['see_all'] = 'Voir tout';
$lang['show_details'] = 'Afficher les détails';
$lang['hide_details'] = 'Masquer les détails';
$lang['stop'] = 'Stop';
$lang['uploaded'] = 'Déposé';
$lang['n_more'] = '{n} autres';
$lang['save'] = 'Sauvegarder';
$lang['actions'] = 'Actions';
$lang['done'] = 'Fait';
$lang['retry'] = 'Ré-essayer';
$lang['ignore'] = 'Ignorer';
$lang['never'] = 'jamais';
$lang['none'] = 'aucun';
$lang['cancel'] = 'Annuler';
$lang['close'] = 'Fermer';
$lang['ok'] = 'OK';
$lang['send'] = 'Envoyer';
$lang['delete'] = 'Supprimer';
$lang['yes'] = 'Oui';
$lang['no'] = 'Non';
$lang['clear_all'] = 'Supprimer tout';
$lang['pause'] = 'Pause';
$lang['to'] = 'A';
$lang['from'] = 'De';
$lang['size'] = 'Taille';
$lang['created'] = 'Créé';
$lang['subject'] = 'Sujet';
$lang['message'] = 'Message';
$lang['details'] = 'Details';
$lang['showhide'] = 'Afficher/Cacher';
$lang['downloads'] = 'Téléchargements';
$lang['download'] = 'Téléchargement';
$lang['downloading'] = 'Téléchargement';
$lang['logon'] = 'Connexion';
$lang['files'] = 'Fichiers';
$lang['optional'] = 'optionnel';
$lang['select_file'] = 'Sélectionner votre fichier';
$lang['select_files'] = 'Sélectionner des fichiers';
$lang['send_voucher'] = 'Envoyer l\'invitation';
$lang['me'] = 'Moi';
$lang['noscript'] = 'Cette application utilise Javascript massivement, vous devez l\'activer afin de pouvoir commencer.';
$lang['send_reminder'] = 'Envoyer un rappel';
$lang['confirm_dialog'] = 'Confirmation';
$lang['invalid_recipient'] = 'Destinataire erroné';
$lang['error_dialog'] = 'Erreur';
$lang['info_dialog'] = 'Information';
$lang['success_dialog'] = 'Succès';
$lang['recipient_errors'] = 'Erreurs du destinataire';
$lang['error_type'] = 'Type d\'erreur';
$lang['error_date'] = 'Date';
$lang['error_details'] = 'Détails techniques';
$lang['recipient_error_bounce'] = 'l\'acheminement du message a échoué';
$lang['forward'] = 'Faire suivre';
$lang['enter_to_email'] = 'Saisir les adresses des destinataires';
$lang['expiry_date'] = 'Date d\'expiration';
$lang['email_sent'] = 'Message envoyé';
$lang['email_separator_msg'] = 'Adresses multiples séparées par , ou ;';
$lang['what_to_do'] = 'Quelle action entreprendre ?';
$lang['copy_text'] = 'Copier le texte ci-dessous';
$lang['reason'] = 'Raison';
$lang['anonymous'] = 'Anonyme';
$lang['anonymous_details'] = 'Lien fourni directement';
$lang['guest'] = 'Invité';
$lang['quota_usage'] = '{size:used} sur {size:total} utilisés, {size:available} restants';
$lang['host_quota'] = 'Quota du service';
$lang['user_quota'] = 'Quota utilisateur';
$lang['extend'] = 'Etendre';
$lang['extend_and_remind'] = 'Etendre et envoyer un rappel';
$lang['translate_to'] = 'Traduire en :';

/**
* File Encryption
*/
$lang['encryption'] = 'Chiffrement';
$lang['decrypting'] = 'Déchiffrement';
$lang['file_encryption'] = 'Fichier Chiffré (beta)';
$lang['file_encryption_password'] = 'Mot de passe';
$lang['file_encryption_show_password'] = 'Voir / Cacher le mot de passe';
$lang['file_encryption_wrong_password'] = 'Mot de passe incorrect';
$lang['file_encryption_enter_password'] = 'Entrer un mot de passe';
$lang['file_encryption_need_password'] = 'Vous devez entrer un mot de passe pour télécharger';
$lang['file_encryption_description'] = '<i class="fa fa-exclamation-triangle" style="color:#FFAA00" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;&nbsp; Chiffrement de bout en bout. Vos fichiers sont chiffrés dans votre navigateur. C\'est à vous de communiquer le mot de passe aux destinataires, nous ne stockons pas les mots de passe.<br/><i class="fa fa-exclamation-triangle" style="color:#FFAA00" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;&nbsp; Le chiffrement des fichiers impactera significativement les performances de votre navigateur, tant pour l\'émetteur que pour les destinataires.<br/><i class="fa fa-exclamation-triangle" style="color:#FFAA00" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;&nbsp; Les fichiers chiffrés d\'une taille égale ou supérieure à 4Go peuvent ne pas être téléversé correctement, ceci est dû aux limitations du navigateur.';
$lang['file_encryption_description_disabled'] = '<i class="fa fa-exclamation-triangle" style="color:#FFAA00" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;&nbsp; Fonctionnalité non supportée par ce navigateur. Veuillez réessayer avec une version récente de Firefox, Internet Explorer, Edge, Safari ou Chrome';
$lang['file_encryption_disabled'] = '<i class="fa fa-exclamation-triangle" style="color:#FFAA00" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;&nbsp;Déchiffrement de fichier non supportée par ce navigateur. Veuillez réessayer avec une version récente de Firefox, Internet Explorer, Edge, Safari ou Chrome';
$lang['file_encryption_generate_password'] = 'Générer un mot de passe';

/**
 * Transfer specific
 */
$lang['recipients'] = 'Destinataires';
$lang['number_of_files'] = 'Nombre de fichiers';
$lang['email_daily_statistics'] = 'M\'envoyer des statistiques quotidiennement';
$lang['email_download_complete'] = 'Me notifier à chaque téléchargement';
$lang['email_me_copies'] = 'Me mettre en copie de toutes les notifications';
$lang['email_me_on_expire'] = 'Me notifier à l\'expiration';
$lang['email_report_on_closing'] = 'M\'envoyer un rapport à l\'expiration du dépôt';
$lang['email_upload_complete'] = 'Me notifier de la fin du téléversement';
$lang['enable_recipient_email_download_complete'] = 'Autoriser les destinataires à recevoir des notifications de fin de leurs téléchargements';
$lang['enable_recipient_email_download_complete_warning'] = 'N\'utilisez pas cette option si vous envoyez vos fichiers à une liste de diffusion sinon chaque téléchargement risquera d\'envoyer un mail à l\'ensemble de la liste de diffusion.';
$lang['add_me_to_recipients'] = 'M\'ajouter aux destinataires';
$lang['redirect_url_on_complete'] = 'Rediriger après le téléversement';
$lang['transfer_closed'] = 'Dépôt fermé';
$lang['transfer_deleted'] = 'Dépôt supprimé';
$lang['transfer_expired'] = 'Dépôt expiré';
$lang['get_a_link'] = 'Obtenir un lien au lieu d\'envoyer à des destinataires';


/**
 * Upload page specific
 */
$lang['average_speed'] = 'Vitesse moyenne';
$lang['paused'] = 'En pause';
$lang['restart'] = 'Re-démarrer';
$lang['restart_failed_transfer'] = 'Re-démarrer le dépôt ?';
$lang['failed_transfer_found'] = 'Il semble qu\'un de vos dépôts précédent a été stoppé brutalement, voulez-vous re-démarrer à la dernière progression connue (vous devrez sélectionner vos fichiers à nouveau) ?';
$lang['load'] = 'Re-démarrer';
$lang['forget'] = 'Oublier ce dépôt';
$lang['later'] = 'Me re-demander plus tard';
$lang['need_to_readd_files'] = 'Vous devez sélectionner les fichiers ci-dessous à nouveau afin de pouvoir re-démarrer votre dépôt';
$lang['unexpected_file'] = 'Ce fichier ne fait pas parti du dépôt en cours de re-démarrage';
$lang['missing_files_for_restart'] = 'Certains fichiers sont manquants, impossible de re-démarrer';
$lang['confirm_stop_upload'] = 'Souhaitez-vous vraiment arrêter le téléversement et supprimer les données déjà stockées ?';
$lang['click_to_delete_file'] = 'Supprimer le fichier';
$lang['click_to_delete_recipient'] = 'Supprimer le destinataire';
$lang['done_uploading'] = 'Téléversement effectué';
$lang['done_uploading_guest'] = 'Merci d\'avoir utilisé {cfg:site_name}. Si votre invitation est valable pour plusieurs dépôts vous pouvez réutiliser votre lien afin d\'envoyer d\'autres fichiers.';
$lang['done_uploading_redirect'] = 'Votre téléversement a été effectué, vous allez être redirigé vers <a href="{url}">{url}</a>.';
$lang['stalled_transfer'] = 'Téléversement bloqué';
$lang['retry_later'] = 'Enregistrer la progression pour re-démarrer plus tard';
$lang['transfer_seems_to_be_stalled'] = 'Le téléversement semble bloqué (beaucoup plus lent que prévu), voulez-vous ré-essayer ou l\'arrêter ?';
$lang['advanced_settings'] = 'Paramètres avancés';
$lang['terasender_worker_count'] = 'Nombre de workers';
$lang['drag_and_drop'] = 'Glisser-déposer vos fichiers ici';
$lang['invalid_file'] = 'Fichier non-valide';
$lang['add_recipient'] = 'Ajouter un destinataire';
$lang['confirm_leave_upload_page'] = 'Voulez-vous vraiment quitter cette page et perdre la progression en cours ?';
$lang['download_link'] = 'Lien de téléchargement';
$lang['recipients_notifications_language'] = 'Langue des destinataires';
$lang['disable_terasender'] = 'Désactive l\'envoi en paralèlle (Cochez si votre connexion est limitée)';


/**
 * Guest page spacific
 */
$lang['guest_options'] = 'Options d\'invité';
$lang['email_upload_page_access'] = 'Me notifier lorsque l\'invité accède à la page de dépôt';
$lang['email_upload_started'] = 'Me notifier lorsque le téléversement démarre';
$lang['can_only_send_to_me'] = 'Être seul et unique destinataire';
$lang['valid_only_one_time'] = 'Valable pour un seul dépôt';
$lang['does_not_expire'] = 'N\'expire pas dans le temps';
$lang['email_guest_created'] = 'Notifier l\'invité de la création';
$lang['email_guest_created_receipt'] = 'Me notifier de la création de l\'invité';
$lang['email_guest_expired'] = 'Notifier l\'invité de l\'expiration';
$lang['guest_transfer_options'] = 'Options du dépôt créé';
$lang['guests_transfers'] = 'Dépôts des invités';
$lang['guest_vouchers_sent'] = 'Invitation envoyée';
$lang['no_guests'] = 'Aucun invité';
$lang['forward_guest_voucher'] = 'Faire suivre l\'invitation';
$lang['guest_deleted'] = 'Invité supprimé';
$lang['guest_reminded'] = 'Rappel envoyé à l\'invité';
$lang['confirm_delete_guest'] = 'Souhaitez-vous vraiment supprimer cet invité (il ne pourra plus déposer de fichiers) ?';
$lang['confirm_remind_guest'] = 'Envoyer un rappel à cet invité ?';
$lang['message_can_not_contain_urls'] = 'Le message ne peut pas contenir d\'URLs ou quelque chose qui y ressemble.';


/**
 * Transfer page specific
 */
$lang['no_transfers'] = 'Aucun dépôt';
$lang['with_identity'] = 'Adresse d\'expéditeur';
$lang['transfer_id'] = 'Identifiant';
$lang['auditlog'] = 'Audit du dépôt';
$lang['confirm_close_transfer'] = 'Souhaitez-vous vraiment fermer ce dépôt ? Les fichiers d\'un dépôt ne peuvent plus être téléchargés après la fermeture.';
$lang['confirm_delete_file'] = 'Souhaitez-vous vraiment supprimer ce fichier ? Le dépôt sera fermé s\'il ne comporte plus de fichiers.';
$lang['confirm_delete_recipient'] = 'Souhaitez-vous vraiment supprimer ce destinataire ? Le dépôt sera fermé s\'il ne comporte plus de destinataires.';
$lang['recipient_deleted'] = 'Le destinataire a été supprimé.';
$lang['file_deleted'] = 'Le fichier a été supprimé.';
$lang['no_auditlog'] = 'Aucune information d\'audit n\'a été trouvée';
$lang['recipient_added'] = 'Destinataire ajouté';
$lang['transfer_reminded'] = 'Rappel envoyé aux destinataires';
$lang['recipient_reminded'] = 'Rappel envoyé aux destinataire';
$lang['open_auditlog'] = 'Consulter l\'audit';
$lang['open_recipient_auditlog'] = 'Voir l\'activité de ce destinataire';
$lang['open_file_auditlog'] = 'Voir l\'activité de ce fichier';
$lang['filtered_transfer_log'] = 'Ceci est une vue filtrée de l\'audit.';
$lang['view_full_log'] = 'Voir l\'audit dans son ensemble';
$lang['send_to_my_email'] = 'M\'envoyer ces informations par email';
$lang['confirm_remind_transfer'] = 'Envoyer le rappel aux destinataires ?';
$lang['confirm_remind_recipient'] = 'Envoyer le rappel à ce destinataire ?';
$lang['download_link'] = 'Lien de téléchargement';
$lang['extend_expiry_date'] = 'Etendre la date d\'expiration de {days} jours';
$lang['confirm_extend_expiry'] = 'Voulez-vous étendre la date d\'expiration de {days} jours ?';
$lang['transfer_extended'] = 'Date d\'expiration étendue jusqu\'au {expires}';
$lang['transfer_extended_reminded'] = 'Date d\'expiration étendue jusqu\'au {expires}, un rappel a été envoyé aux destinataires';
$lang['download_link'] = 'Lien de téléchargement';
$lang['pager_more'] = 'Plus...';
$lang['pager_has_no_more'] = 'Rien de plus.';

/**
 * Reports
 */

// Reports
$lang['date'] = 'Date';
$lang['action'] = 'Action';
$lang['ip'] = 'Adresse IP';

$lang['report_event_transfer_started'] = 'Le dépôt a été créé';
$lang['report_event_transfer_available'] = 'Le dépôt est devenu disponible pour les destinataires (temps total {time:time_taken})';
$lang['report_event_transfer_sent'] = 'Les liens de téléchargement ont été envoyés aux destinataires';
$lang['report_event_transfer_expired'] = 'Le dépôt a expiré';
$lang['report_event_transfer_closed'] = 'Le dépôt a été fermé sur demande';
$lang['report_event_transfer_deleted'] = 'Le dépôt a été supprimé';
$lang['report_event_upload_started'] = 'Le téléversement a démarré';
$lang['report_event_upload_resumed'] = 'Le téléversement a repris';
$lang['report_event_upload_ended'] = 'Le téléversement a été terminé';
$lang['report_event_file_uploaded'] = 'Le fichier {file.name} ({size:file.size}) a été téléversé en {time:time_taken}';
$lang['report_event_download_started'] = 'Le destinataire {author.identity} a commencé à télécharger le fichier {file.name} ({size:file.size})';
$lang['report_event_download_resumed'] = 'Le destinataire {author.identity} a repris le téléchargement du fichier {file.name} ({size:file.size})';
$lang['report_event_download_ended'] = 'Le destinataire {author.identity} a fini de télécharger le fichier {file.name} ({size:file.size})';
$lang['report_event_archive_download_started'] = 'Le destinataire {author.identity} a commencé à télécharger un ensemble de fichiers sous forme d\'archive';
$lang['report_event_archive_download_ended'] = 'Le destinataire {author.identity} a fini de télécharger un ensemble de fichiers sous forme d\'archive';

$lang['report_recipient_event_download_started'] = 'Le destinataire a commencé à télécharger le fichier {file.name} ({size:file.size})';
$lang['report_recipient_event_download_resumed'] = 'Le destinataire a repris le téléchargement du fichier {file.name} ({size:file.size})';
$lang['report_recipient_event_download_ended'] = 'Le destinataire a fini de télécharger le fichier {file.name} ({size:file.size})';
$lang['report_recipient_event_archive_download_started'] = 'Le destinataire a commencé à télécharger un ensemble de fichiers sous forme d\'archive';
$lang['report_recipient_event_archive_download_ended'] = 'Le destinataire a fini de télécharger un ensemble de fichiers sous forme d\'archive';

$lang['report_owner_event_download_started'] = 'Le propiétaire a commencé à télécharger le fichier {file.name} ({size:file.size})';
$lang['report_owner_event_download_resumed'] = 'Le propiétaire a repris le téléchargement du fichier {file.name} ({size:file.size})';
$lang['report_owner_event_download_ended'] = 'Le propiétaire a fini de télécharger le fichier {file.name} ({size:file.size})';
$lang['report_owner_event_archive_download_started'] = 'Le propiétaire a commencé à télécharger un ensemble de fichiers sous forme d\'archive';
$lang['report_owner_event_archive_download_ended'] = 'Le propiétaire a fini de télécharger un ensemble de fichiers sous forme d\'archive';

$lang['report_guest_event_transfer_started'] = 'Le dépôt a été créé par l\'invité {author.identity}';
$lang['report_guest_event_transfer_sent'] = 'Les liens de téléchargement ont été envoyés aux destinataires';


/**
 * Download page specific
 */
$lang['archive_download'] = 'Télécharger l\'archive';
$lang['download_disclamer'] = '';
$lang['download_disclamer_nocrypto_message'] = 'Vous pouvez faire un clic droit sur le bouton de téléchargement et "Copier l\'emplacement du lien" pour télécharger le fichier en utilisant un autre outil.';
$lang['download_disclamer_crypto_message'] = 'Cliquez sur un fichier pour télécharger les données et déchiffrer sur votre ordinateur.';
$lang['download_disclamer_archive'] = 'Voici vos fichiers. Vous pouvez les télécharger indépendamment les uns des autres ou rassemblés sous forme d\'archive ZIP.';
$lang['download_file'] = 'Télécharger';
$lang['mac_archive_message'] = 'Si vous utilisez OSX vous pourrez trouver un utilitaire permettant d\'ouvrir l\'archive en suivant le lien suivant : <a href="{cfg:mac_unzip_link}" target="_blank">{cfg:mac_unzip_name}</a>.';
$lang['select_all_for_archive_download'] = 'Sélectionner tous les fichiers';
$lang['select_for_archive_download'] = 'Sélectionner pour le téléchargement groupé';
$lang['archive_message'] = 'Téléchargement en tant qu\'archive ZIP.';
$lang['confirm_download_notify'] = 'Souhaitez-vous recevoir un email de notification lorsque le téléchargement est terminé ?';


/**
 * User profile specifics
 */
$lang['user_page'] = 'Mon profil';
$lang['user_preferences'] = 'Préférences';
$lang['user_lang'] = 'Langue préférée';
$lang['user_remote_authentication'] = 'Authentification distante';
$lang['user_auth_secret'] = 'Secret';
$lang['user_additionnal'] = 'Informations additionnelles';
$lang['user_id'] = 'Identité';
$lang['user_created'] = 'Première connexion';
$lang['get_full_user_remote_config'] = 'Obtenir la configuration complète pour l\'authentification distante';
$lang['preferences_updated'] = 'Préférences utilisateur sauvegardées';
$lang['remote_auth_sync_request'] = '<p><strong>{remote}</strong> requiert vos informations pour l\'authentification distante.</p><p>Pour autoriser l\'accès veuillez donner le code suivant à <strong>{remote}</strong> : <strong>{code}</strong> (ce code est utilisable dans les prochaines 2 minutes seulement).</p><p>Si vous n\'êtes pas à l\'origine de cette demande merci d\'ignorer de message.</p>';


/**
 * Admin page specific
 */
$lang['admin_statistics_section'] = 'Statistiques';
$lang['host_quota_usage'] = 'Utilisation du quota du service';
$lang['admin_transfers_section'] = 'Dépôts';
$lang['admin_guests_section'] = 'Invités';
$lang['admin_config_section'] = 'Configuration';
$lang['global_statistics'] = 'Statistiques générales';
$lang['available_transfers'] = 'Dépôts disponibles';
$lang['uploading_transfers'] = 'Dépôts en cours de téléversement';
$lang['closed_transfers'] = 'Dépôts fermés';
$lang['created_transfers'] = 'Dépôts créés';
$lang['count_from_date_to_date'] = '{count} entre {date:start} et {date:end}';

$lang['storage_usage'] = 'Utilisation du stockage';
$lang['storage_block'] = 'Bloc';
$lang['storage_paths'] = 'Chemins associés';
$lang['storage_total'] = 'Volume total';
$lang['storage_used'] = 'Volume utilisé';
$lang['storage_available'] = 'Volume disponible';
$lang['storage_main'] = 'Principal';

$lang['delete_transfer_nicely'] = 'Supprimer le dépôt et notifier les destinataires';
$lang['delete_transfer_roughly'] = 'Supprimer le dépôt sans notifications';
$lang['stop_transfer_upload'] = 'Stopper le téléversement (entrainera un affichage d\'erreurs coté client) ?';
$lang['transfer_upload_stopped'] = 'Téléversement stoppé';

$lang['is_default'] = 'Valeur par défaut';
$lang['make_default'] = 'Revenir à la valeur par défaut';
$lang['config_overriden'] = 'Configuration sauvegardée';


/**
 * Exceptions and errors
 */
$lang['access_forbidden'] = 'Vous ne disposez pas des droits nécessaires pour accéder à cette page';

$lang['encountered_exception'] = 'L\'application a rencontré une erreur lors du traitement de votre requête';
$lang['you_can_report_exception'] = 'En rapportant cette erreur merci de mentionner le code suivant afin de faciliter la recherche du problème';
$lang['you_can_report_exception_by_email'] = 'Vous pouvez rapporter cette erreur par email';
$lang['report_exception'] = 'Envoyer un rapport';

// AuditLog related exceptions
$lang['auditlog_not_found'] = 'Information d\'audit introuvable';
$lang['auditlog_not_enabled'] = 'Audit désactivé';
$lang['auditlog_unknown_event'] = 'Évènement inconnu';

// Auth related exceptions
$lang['auth_authentication_not_found'] = 'Méthode d\'authentification introuvable';
$lang['auth_user_not_allowed'] = 'Vous n\'êtes pas autorisé à utiliser cette application';

// AuthRemote related exceptions
$lang['auth_remote_unknown_application'] = 'Application distante inconnue';
$lang['auth_remote_too_late'] = 'La requête est arrivée trop tard';
$lang['auth_remote_signature_check_failed'] = 'Signature fournie incorrecte';
$lang['auth_remote_user_rejected'] = 'Cet utilisateur n\'accèpte pas d\'authentification distante';

// AuthSP related exceptions
$lang['auth_sp_missing_delegation_class'] = 'Classe de délégation d\'authentification de type "Fournisseur de Service" manquante';
$lang['auth_sp_authentication_not_found'] = 'Classe de délégation d\'authentification de type "Fournisseur de Service" introuvable';
$lang['auth_sp_missing_attribute'] = 'Attribut manquant pour l\'authentification de type "Fournisseur de Service"';
$lang['auth_sp_bad_attribute'] = 'Attribut erroné pour l\'authentification de type "Fournisseur de Service"';
$lang['serverlog_auth_sp_attribute_not_found'] = 'Il y a eu des problèmes pour trouver un attribut d\'authentification "Fournisseur de Service". Ce sont les attributs disponibles au moment de l\'authentification. Peut-être re-vérifier que l\'orthographe du nom de l\'attribut est correct. Peut-être que la configuration recherche le mauvais attribut ?';
$lang['serverlog_config_directive'] = 'Directive de configuration associée \'{key}\'';
$lang['serverlog_wanted_key_in_array'] = 'Attribut demandé avec la clé \'{key}\'';

// Bad exceptions
$lang['bad_email'] = 'Format d\'adresse email erroné';
$lang['bad_ip_format_ipv4'] = 'Format d\'adresse IPv4 erroné';
$lang['bad_ip_format_ipv6'] = 'Format d\'adresse IPv6 erroné';
$lang['bad_ip_format'] = 'Format d\'adresse IP erroné';
$lang['bad_expire'] = 'Date d\'expiration incorrecte';
$lang['bad_size_format'] = 'Taille incorrecte';
$lang['bad_lang_code'] = 'Code de language incerrect';

// Config related exceptions
$lang['config_file_missing'] = 'Fichier de configuration introuvable';
$lang['config_bad_parameter'] = 'Paramètre de configuration incorrect';
$lang['config_missing_parameter'] = 'Paramètre de configuration introuvable';
$lang['config_override_disabled'] = 'Surcharge de la configuration désactivée';
$lang['config_override_validation_failed'] = 'La validation de la configuration a échoué';
$lang['config_override_not_allowed'] = 'Surcharge de la configuration non-autorisée';
$lang['config_override_cannot_save'] = 'Impossible de sauvegarder la configuration';

// Core related exceptions
$lang['core_file_not_found'] = 'Fichier système introuvable';
$lang['core_class_not_found'] = 'Classe système introuvable';

// DBI related exceptions
$lang['failed_to_connect_to_database'] = 'Impossible de se connecter à la base de données';
$lang['dbi_missing_parameter'] = 'Paramètre de connexion à la base de données manquant';
$lang['database_access_failure'] = 'Erreur lors de l\'accès à la base de données';

// DBO related exceptions
$lang['no_such_property'] = 'Propriété inexistante';

// Download related exceptions
$lang['download_missing_token'] = 'Code de téléchargement manquant';
$lang['download_bad_token_format'] = 'Format du code de téléchargement erroné';
$lang['download_missing_files_ids'] = 'Identifiants des fichiers à télécharger manquants';
$lang['download_bad_files_ids'] = 'Identifiants des fichiers à télécharger erronés';
$lang['download_invalid_range'] = 'Plage de téléchargement erronée';

// File related exceptions
$lang['file_not_found'] = 'Fichier introuvable';
$lang['file_extension_not_allowed'] = 'Extension de fichier non-acceptée';
$lang['file_bad_hash'] = 'Condensat de fichier erroné';
$lang['file_chunk_out_of_bounds'] = 'Portion de fichier au-delà des limites';
$lang['file_integrity_check_failed'] = 'Le test d\'intégrité du fichier à échoué';
$lang['file_size_does_not_match'] = 'La taille du fichier ne correspond pas';
$lang['cannot_open_input_file'] = 'Impossible d\'ouvrir le fichier en entrée';

// GUI related exceptions
$lang['gui_unknown_admin_section'] = 'Section de l\'administration inconnue';
$lang['reader_not_supported'] = 'Vous utilisez un navigateur qui ne supporte pas HTML5.<br />Le glisser-déposer n\'est pas disponible.<br />Votre navigateur de supporte pas le dépôt de fichiers plus gros que {size}.';

// Guest related exceptions
$lang['guest_not_found'] = 'Invité introuvable';
$lang['bad_guest_status'] = 'Statut d\'invité erroné';
$lang['guest_too_many_recipients'] = 'Nombre maximum de destinataires dépassé';

// Mail related exceptions
$lang['invalid_address_format'] = 'Format d\'adresse email erroné';
$lang['no_addresses_found'] = 'Aucune adresse trouvée';

// Recipient related exceptions
$lang['recipient_not_found'] = 'Destinataire introuvable';

// Report related exceptions
$lang['report_cannot_write_file'] = 'Impossible de stocker le rapport';
$lang['report_format_not_available'] = 'Format de rapport non disponible';
$lang['report_nothing_found'] = 'Rien à rapporter';
$lang['report_ownership_required'] = 'Vous devez être propriétaire du sujet du rapport';
$lang['report_unknown_format'] = 'Format de rapport inconnu';
$lang['report_unknown_target_type'] = 'Type de sujet de rapport inconnu';

// Rest related exceptions
$lang['rest_authentication_required'] = 'Authentification REST requise';
$lang['rest_admin_required'] = 'Droits administrateur requis';
$lang['rest_ownership_required'] = 'possession de la ressource REST requise';
$lang['rest_missing_parameter'] = 'Paramètre REST manquant';
$lang['rest_bad_parameter'] = 'Paramètre REST erroné';
$lang['rest_method_not_allowed'] = 'Le serveur REST n\'accepte pas cette méthode';
$lang['rest_endpoint_missing'] = 'Le serveur REST n\'a pas pu déduire le point d\'entrée de l\'URL';
$lang['rest_access_forbidden'] = 'Le serveur REST a refusé l\'accès';
$lang['rest_jsonp_get_only'] = 'Le serveur REST n\'accepte que la méthode GET pour les requêtes de type JSONP';
$lang['rest_updatedsince_bad_format'] = 'Le paramètre REST updatedSince est erroné';
$lang['rest_endpoint_not_implemented'] = 'Point d\'entrée REST introuvable';
$lang['rest_method_not_implemented'] = 'Méthode REST introuvable dans le point d\'entrée';
$lang['rest_sanity_check_failed'] = 'La vérification des données REST a échoué';
$lang['rest_xsrf_token_did_not_match'] = 'Le code de sécurité ne correspond pas';

// StatLog related exceptions
$lang['statlog_not_found'] = 'Entrée de statistique introuvable';
$lang['statlog_unknown_event'] = 'Évènement inconnu';

// Storage related exceptions
$lang['storage_chunk_too_large'] = 'Morceau de fichier trop gros';
$lang['storage_not_enough_space_left'] = 'Pas assez d\'espace restant';

// StorageFilesystem related exceptions
$lang['storage_filesystem_cannot_create_path'] = 'Impossible de créer le chemin dans le stockage';
$lang['storage_filesystem_file_not_found'] = 'Fichier introuvable dans le stockage';
$lang['storage_filesystem_cannot_read'] = 'Impossible de lire le fichier dans le stockage';
$lang['storage_filesystem_cannot_delete'] = 'Impossible de supprimer le fichier du stockage';
$lang['storage_filesystem_cannot_write'] = 'Impossible d\'écrire le fichier dans le stockage';
$lang['storage_filesystem_out_of_space'] = 'Espace manquant dans le stockage';
$lang['storage_filesystem_bad_resolver_target'] = 'Mauvaise cible pour la cartographie du stockage';
$lang['storage_filesystem_bad_usage_output'] = 'Informations sur l\'utilisation du stockage erronées';
$lang['storage_filesystem_cannot_get_usage'] = 'Impossible d\'obtenir les informations sur l\'utilisation du stockage';

// Template related exceptions
$lang['template_not_found'] = 'Modèle introuvable';

// Tracking related exceptions
$lang['tracking_event_not_found'] = 'Évènement de suivi introuvable';
$lang['tracking_unknown_event'] = 'Type d\'évènement de suivi inconnu';

// Transfer related exceptions
$lang['transfer_not_found'] = 'Dépôt introuvable';
$lang['bad_transfer_status'] = 'Statut de dépôt erroné';
$lang['transfer_no_recipients'] = 'Le dépôt n\'a pas de destinataires';
$lang['transfer_no_files'] = 'Le dépôt n\'a pas de fichiers';
$lang['duplicate_recipient'] = 'Un des destinataires existe déjà';
$lang['max_email_recipients_exceeded'] = 'Nombre maximum de destinataires dépassé';
$lang['transfer_maximum_size_exceeded'] = 'Taille maximale de dépôt dépassée';
$lang['transfer_not_availabe'] = 'Dépôt non disponible';
$lang['transfer_too_many_files'] = 'Nombre maximum de fichiers dépassé';
$lang['transfer_too_many_recipients'] = 'Nombre maximum de destinataires dépassé';
$lang['cannot_alter_closed_transfer'] = 'Impossible de modifier un dépôt fermé';
$lang['transfer_rejected'] = 'Dépôt rejeté';
$lang['transfer_host_quota_exceeded'] = 'Quota du service dépassé';
$lang['transfer_user_quota_exceeded'] = 'Quota utilisateur dépassé';
$lang['transfer_expiry_extension_not_allowed'] = 'Extension de la date d\'expiration non-autorisée';
$lang['transfer_expiry_extension_count_exceeded'] = 'Nombre maximum d\'extensions de la date d\'expiration atteint';
$lang['transfer_files_incomplete'] = 'Certains fichiers ne sont pas complets';
$lang['transfer_file_name_invalid'] = 'Nom de fichier contenant des caractères interdits';

// User related exceptions
$lang['user_not_found'] = 'Utilisateur introuvable';
$lang['user_missing_uid'] = 'Identifiant unique d\'utilisateur manquant';

// Utilities related exceptions
$lang['utilities_uid_generator_bad_unicity_checker'] = 'Vérificateur d\'unicité du générateur d\'identifiants uniques erroné';
$lang['utilities_uid_generator_tried_too_much'] = 'Nombre maximal d\'essais pour le générateur d\'identifiants uniques dépassé';
