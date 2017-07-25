<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 * French translation by Galixte (http://www.galixte.com)
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	// Manage ads
	'AD_NAME'					=> 'Nom',
	'AD_NAME_EXPLAIN'			=> 'Permet de saisir un nom pour identifier cette publicité.',
	'AD_ENABLED'				=> 'Activation',
	'AD_ENABLED_EXPLAIN'		=> 'Permet d’activer / désactiver l’affichage de cette publicité.',
	'AD_END_DATE'				=> 'Date d’expiration',
	'AD_END_DATE_EXPLAIN'		=> 'Permet de saisir la date d’expiration après laquelle l’affichage de cette publicité sera désactivé. Laisser ce champ vide pour afficher indéfiniment cette publicité. Il est nécessaire d’utiliser le format suivant :<samp>AAAA-MM-JJ</samp>.',
	'AD_VIEWS_LIMIT'			=> 'Nombre de vues',
	'AD_VIEWS_LIMIT_EXPLAIN'	=> 'Permet de saisir le nombre de fois que la publicité sera affichée, après lequel celle-ci ne sera plus affichée. Saisir la valeur 0 pour l’afficher indéfiniment.',
	'AD_CLICKS_LIMIT'			=> 'Nombre de clics',
	'AD_CLICKS_LIMIT_EXPLAIN'	=> 'Permet de saisir le nombre maximum de clics autorisés sur la publicité, après lequel celle-ci ne sera plus affichée. Saisir la valeur 0 pour l’afficher indéfiniment.',
	'AD_PRIORITY'				=> 'Niveau de priorité',
	'AD_PRIORITY_EXPLAIN'		=> 'Permet de saisir une valeur comprise entre 1 & 10 correspondant au niveau de priorité. Les publicités ayant un niveau de priorité plus élevé s’afficheront plus souvent.',
	'AD_NOTE'					=> 'Notes',
	'AD_NOTE_EXPLAIN'			=> 'Permet de saisir des annotations à propos de cette publicité. Ces notes ne seront pas affichées à l’exception de l’administration du forum dans la gestion des publicités.',
	'AD_CODE'					=> 'Code',
	'AD_CODE_EXPLAIN'			=> 'Permet de saisir le code de cette publicité. Le code doit utiliser des balises HTML, les BBCodes ne sont pas pris en charge.',
	'AD_LOCATIONS'				=> 'Emplacements',
	'AD_LOCATIONS_EXPLAIN'		=> 'Permet de sélectionner les emplacements où cette publicité sera affichée. Au survol de la souris sur un emplacement une courte description s’affichera. Si plusieurs publicités utilisent le même emplacement, une seule d’entre elles sera sélectionnée aléatoirement pour être affichée à cet emplacement lors du chargement de la page.<br />Il est nécessaire d’utiliser la combinaison de la touche CTRL et du clic gauche (touche CMD + clic sur Mac) pour sélectionner/désélectionner plusieurs emplacements.',
	'AD_VIEWS'					=> 'Vues',
	'AD_CLICKS'					=> 'Clics',
	'AD_ENABLE_TITLE'			=> array( // Plural rule doesn't apply here! Just translate the values.
		0 => 'Cliquer pour activer',
		1 => 'Cliquer pour désactiver',
	),
	'AD_EXPIRED_EXPLAIN'		=> 'Cette publicité a expiré ou a été désactivée.',
	'AD_OWNER'					=> 'Propriétaire',
	'AD_OWNER_EXPLAIN'			=> 'Permet de définir le membre ayant la permission de voir les statistiques sur le nombre de vues et de clics de la publicité depuis la « Panneau de l’utilisateur ».',
	'BANNER'					=> 'Transférer une bannière',
	'BANNER_EXPLAIN'			=> 'Permet d’envoyer un fichier image au format JPG, GIF ou PNG. L’image sera sauvegardée dans le répertoire <samp>images</samp> de phpBB et une balise HTML IMG pour l’image sera automatiquement insérée dans le champ du code de la publicitaire.',
	'ACP_ADS_EMPTY'				=> 'Il n’a aucune publicité configurée. Pour en ajouter une cliquer sur le bouton ci-dessous.',
	'ACP_ADS_ADD'				=> 'Ajouter une nouvelle publicité',
	'ACP_ADS_EDIT'				=> 'Modifier une publicité',
	'AD_PREVIEW'				=> 'Aperçu de cette publicité',
	'CONFIGURE_AD'				=> 'Configurer cette publicité',
	'BANNER_UPLOAD'				=> 'Transférer une bannière',

	'AD_NAME_REQUIRED'			=> 'Un nom est requis.',
	'AD_NAME_TOO_LONG'			=> 'La longueur du nom est limité à %d caractères.',
	'AD_END_DATE_INVALID'		=> 'La date limite d’expiration est incorrecte ou déjà expiré.',
	'AD_PRIORITY_INVALID'		=> 'Ce niveau de priorité est incorrect. Merci de saisir un nombre compris entre 1 & 10.',
	'AD_VIEWS_LIMIT_INVALID'	=> 'Le nombre limite de vues est incorrect. Merci de saisir une valeur positive.',
	'AD_CLICKS_LIMIT_INVALID'	=> 'Le nombre limite de clics est incorrect. Merci de saisir une valeur positive.',
	'AD_OWNER_INVALID'			=> 'Le propriétaire de la publicité est incorrect. Merci de sélectionner un membre en utilisant le lien « Rechercher un membre ».',
	'NO_FILE_SELECTED'			=> 'Aucun fichier n’a été sélectionné.',
	'CANNOT_CREATE_DIRECTORY'	=> 'Le répertoire <samp>phpbb_ads</samp> ne peut être créé. Merci de contrôler les permissions en écriture sur le répertoire <samp>/images</samp>.',
	'FILE_MOVE_UNSUCCESSFUL'	=> 'Il est impossible de déplacer le fichier dans le répertoire <samp>images/phpbb_ads</samp>.',
	'ACP_AD_DOES_NOT_EXIST'		=> 'Cette publicité n’existe pas.',
	'ACP_AD_ADD_SUCCESS'		=> 'Publicité ajoutée avec succès.',
	'ACP_AD_EDIT_SUCCESS'		=> 'Publicité modifiée avec succès.',
	'ACP_AD_DELETE_SUCCESS'		=> 'Publicité supprimée avec succès.',
	'ACP_AD_DELETE_ERRORED'		=> 'Il y a une erreur durant la suppression de cette publicité.',
	'ACP_AD_ENABLE_SUCCESS'		=> 'Publicité activée avec succès.',
	'ACP_AD_ENABLE_ERRORED'		=> 'Il y a eu une erreur durant l’activation de cette publicité.',
	'ACP_AD_DISABLE_SUCCESS'	=> 'Publicité désactivée avec succès.',
	'ACP_AD_DISABLE_ERRORED'	=> 'Il y a eu une erreur durant la désactivation de cette publicité.',

	// Template locations
	'AD_ABOVE_HEADER'				=> 'Au-dessus de l’entête',
	'AD_ABOVE_HEADER_DESC'			=> 'Permet d’afficher la publicité sur chaque page au-dessus de l’entête du forum.',
	'AD_BELOW_HEADER'				=> 'En dessous de l’entête',
	'AD_BELOW_HEADER_DESC'			=> 'Permet d’afficher la publicité sur chaque page en dessous de l’entête du forum (et au-dessus de la barre de navigation).',
	'AD_BEFORE_POSTS'				=> 'Au-dessus des messages',
	'AD_BEFORE_POSTS_DESC'			=> 'Permet d’afficher la publicité sur la page de la vue du sujet au-dessus du premier message.',
	'AD_AFTER_POSTS'				=> 'En dessous des messages',
	'AD_AFTER_POSTS_DESC'			=> 'Permet d’afficher la publicité sur la page de la vue du sujet en dessous du dernier message.',
	'AD_BELOW_FOOTER'				=> 'En dessous du pied de page',
	'AD_BELOW_FOOTER_DESC'			=> 'Permet d’afficher la publicité sur chaque page en dessous du pied de page du forum.',
	'AD_ABOVE_FOOTER'				=> 'Au-dessus du pied de page',
	'AD_ABOVE_FOOTER_DESC'			=> 'Permet d’afficher la publicité sur chaque page au-dessus du pied de page du forum.',
	'AD_AFTER_FIRST_POST'			=> 'En dessous du premier message',
	'AD_AFTER_FIRST_POST_DESC'		=> 'Permet d’afficher la publicité sur la page de la vue du sujet en dessous du premier message.',
	'AD_AFTER_NOT_FIRST_POST'		=> 'En dessous de chaque message excepté le premier',
	'AD_AFTER_NOT_FIRST_POST_DESC'	=> 'Permet d’afficher la publicité sur la page de la vue du sujet en dessous de chaque message excepté le premier.',
	'AD_BEFORE_PROFILE'				=> 'Au-dessus du profil',
	'AD_BEFORE_PROFILE_DESC'		=> 'Permet d’afficher la publicité sur la page du profil du membre au-dessus du contenu.',
	'AD_AFTER_PROFILE'				=> 'En dessous du profil',
	'AD_AFTER_PROFILE_DESC'			=> 'Permet d’afficher la publicité sur la page du profil du membre en dessous du contenu.',

	// Settings
	'ADBLOCKER_MESSAGE'				=> 'Message d’avertissement lors de la détection d’un bloqueur de publicités',
	'ADBLOCKER_MESSAGE_EXPLAIN'		=> 'Permet de saisir un message de politesse adressé aux visiteurs utilisant un logiciel antipublicitaire, les avertissant de l’importance de désactiver leur bloqueur de publicités sur ce forum.',
	'ENABLE_VIEWS'					=> 'Total de vues',
	'ENABLE_VIEWS_EXPLAIN'			=> 'Permet d’activer le compteur de vues pour chaque publicité affichée. Information : cette fonctionnalité peut augmenter la charge du serveur sur lequel le forum est hébergé, si aucun besoin n’est établi il est préférable de la désactiver.',
	'ENABLE_CLICKS'					=> 'Total de clics',
	'ENABLE_CLICKS_EXPLAIN'			=> 'Permet d’activer le compteur de clics pour chaque publicité ayant reçu des clics. Information : cette fonctionnalité peut augmenter la charge du serveur sur lequel le forum est hébergé, si aucun besoin n’est établi il est préférable de la désactiver.',
	'HIDE_GROUPS'					=> 'Masquer les publicités aux groupes',
	'HIDE_GROUPS_EXPLAIN'			=> 'Permet de sélectionner les groupes de membres qui ne verront pas les publicités.<br />Il est nécessaire d’utiliser la combinaison de la touche CTRL et du clic gauche (touche CMD + clic sur Mac) pour sélectionner/désélectionner plusieurs groupes.',

	'ACP_AD_SETTINGS_SAVED'	=> 'Les paramètres de gestion des publicités ont été sauvegardés.',
));
