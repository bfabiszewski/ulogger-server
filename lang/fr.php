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
$langSetup["dbconnectfailed"] = "Echec de la connexion à la base de données";
$langSetup["serversaid"] = "Réponse du serveur : %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Veuillez vérifier les paramètres de base de données dans le fichier 'config.php'";
$langSetup["dbqueryfailed"] = "La requête à la base de données a échouée";
$langSetup["dbtablessuccess"] = "La structure de la base de données a été créée avec succès ! ";
$langSetup["setupuser"] = "Créez maintenant votre premier utilisateur de µlogger";
$langSetup["congratulations"] = "Félicitations !";
$langSetup["setupcomplete"] = "L'installation est désormais terminée. Vous pouvez naviguer vers la <a href=\"../index.php\">page principale</a> et vous connecter avec votre nouvel utilisateur.";
$langSetup["disablewarn"] = "IMPORTANT ! Vous devez DESACTIVER le script 'setup.php' ou l'EFFACER de votre serveur.";
$langSetup["disabledesc"] = "Laisser le script accessible depuis le navigateur est un risque de sécurité majeur. N'importe qui pourra l'exécuter, supprimer votre base de données et créer un nouveau compte utilisateur. Supprimez le fichier ou désactivez-le en remettant la valeur %s à %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Aïe ! Quelque chose s'est mal passé. Vous devriez pouvoir trouver plus d'informations dans les journaux du serveur web.";
$langSetup["welcome"] = "Bienvenue dans µlogger !";
$langSetup["disabledwarn"] = "Pour des raisons de sécurité, ce script est désactivé par défaut. Pour l'activer, vous devez éditer le fichier 'scripts/setup.php' dans un éditeur de texte et définir la variable %s au début du fichier à la valeur %s ."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Ligne : %s devrait se lire : %s";
$langSetup["dorestart"] = "Merci de relancer le script lorsque vous avez terminé.";
$langSetup["createconfig"] = "Veuillez créer le fichier 'config.php' au niveau du dossier racine. Vous pouvez commencer par le copier à partir de 'config.default.php'. Veillez à ajuster les valeurs de configuration en fonction de vos besoins et des paramètres de votre base de données.";
$langSetup["nodbsettings"] = "Vous devez fournir les informations d'identification de votre base de données dans le fichier 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Ce script mettra en place les tables nécessaires pour le µlogger (%s). Elles seront créées dans votre base de données nommée %s. Attention, si les tables existent déjà, elles seront supprimées et recréées, leur contenu sera détruit."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Une fois terminé, le script vous demandera de fournir un nom d'utilisateur et un mot de passe pour créer votre premier utilisateur de µlogger.";
$langSetup["startbutton"] = "Appuyer pour démarrer";
$langSetup["restartbutton"] = "Redémarrer";
$langSetup["optionwarn"] = "L'option de configuration PHP : %s doit être définie à %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "L'extension PHP requise : %s n'est pas disponible.  "; // substitutes extension name
$langSetup["notwritable"] = "PHP doit avoir les droits d'écriture dans le répertoire '%s'"; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Il faut un nom d'utilisateur et un mot de passe pour accéder à cette page.";
$lang["authfail"] = "Nom d'utilisateur ou mot de passe erroné.";
$lang["user"] = "Utilisateur";
$lang["track"] = "Trace";
$lang["latest"] = "Dernière position";
$lang["autoreload"] = "Suivi live";
$lang["reload"] = "Actualiser maintenant";
$lang["export"] = "Télécharger les données";
$lang["chart"] = "Courbes d'altitudes";
$lang["close"] = "fermer";
$lang["time"] = "Temps";
$lang["speed"] = "Vitesse";
$lang["accuracy"] = "Précision";
$lang["position"] = "Position";
$lang["altitude"] = "Altitude";
$lang["bearing"] = "Direction";
$lang["ttime"] = "Temps total";
$lang["aspeed"] = "Vitesse moyenne";
$lang["tdistance"] = "Dist. totale";
$lang["pointof"] = "Point %d sur %d"; // e.g. Point 3 of 10
$lang["summary"] = "Résumé du trajet";
$lang["suser"] = "Sélectionner un utilisateur";
$lang["logout"] = "Déconnexion";
$lang["login"] = "Connexion";
$lang["username"] = "Nom d'utilisateur";
$lang["password"] = "Mot de passe";
$lang["language"] = "Langue";
$lang["newinterval"] = "Entrez un intervalle (secondes)";
$lang["api"] = "Groupe de cartes";
$lang["units"] = "Unités";
$lang["metric"] = "Système métrique";
$lang["imperial"] = "Système impérial";
$lang["nautical"] = "Système nautique";
$lang["admin"] = "Administrateur";
$lang["adminmenu"] = "Administration";
$lang["passwordrepeat"] = "Répétez le mot de passe";
$lang["passwordenter"] = "Entrez le mot de passe";
$lang["usernameenter"] = "Entrez le nom d'utilisateur";
$lang["adduser"] = "Ajouter un utilisateur";
$lang["userexists"] = "Cet utilisateur existe déjà";
$lang["cancel"] ="Annuler";
$lang["submit"] = "Envoyer";
$lang["oldpassword"] = "Ancien mot de passe";
$lang["newpassword"] = "Nouveau mot de passe";
$lang["newpasswordrepeat"] = "Répétez le mot de passe";
$lang["changepass"] = "Changer le mot de passe";
$lang["gps"] = "GPS";
$lang["network"] = "Réseau";
$lang["deluser"] = "Supprimer l'utilisateur";
$lang["edituser"] = "Modifier l'utilisateur";
$lang["servererror"] = "Erreur serveur";
$lang["allrequired"] = "Tous les champs sont requis";
$lang["passnotmatch"] = "Les mots de passe sont différents";
$lang["actionsuccess"] = "Action effectuée avec succès";
$lang["actionfailure"] = "Echec de l'action";
$lang["notauthorized"] = "Utilisateur non autorisé";
$lang["userdelwarn"] = "Attention !\n\nVous êtes sur le point de supprimer de manière permanente l'utilisateur %s ainsi que toutes ses traces et positions.\n\nÊtes-vous certain ?"; // substitutes user login
$lang["editinguser"] = "Vous êtes en train d'éditer l'utilisateur %s"; // substitutes user login
$lang["selfeditwarn"] = "Vous ne pouvez pas éditer votre propre utilisateur de cette manière";
$lang["apifailure"] = "Navré, impossible de charger l'API de %s"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Attention !\n\nVous êtes sur le point de supprimer de manière permanente la trace %s et toutes ses positions.\n\nÊtes-vous certain ?"; // substitutes track name
$lang["editingtrack"] = "Vous êtes en train de modifier la trace %s"; // substitutes track name
$lang["deltrack"] = "Supprimer la trace";
$lang["trackname"] = "Nom de la trace";
$lang["edittrack"] = "Modifier la trace";
$lang["positiondelwarn"] = "Attention !\n\nVous êtes en train de supprimer définitivement la position %d de la trace %s.\n\nEtes vous certain ?"; // substitutes position index and track name
$lang["editingposition"] = "Vous éditez la position#%d de la trace %s"; // substitutes position index and track name
$lang["delposition"] = "Supprimer la position";
$lang["delimage"] = "Supprimer l'image";
$lang["comment"] = "Commentaire";
$lang["image"] = "Image";
$lang["editposition"] = "Editer la position";
$lang["passlenmin"] = "Le mot de passe doit être composé d'au moins %d caractères"; // substitutes password minimum length
$lang["passrules_1"] = "Il doit contenir au moins une lettre minuscule et une lettre majuscule";
$lang["passrules_2"] = "Il doit contenir au moins une lettre minuscule, une lettre majuscule et un chiffre";
$lang["passrules_3"] = "Il doit contenir au moins une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial";
$lang["owntrackswarn"] = "Vous pouvez uniquement modifier vos propres traces";
$lang["gmauthfailure"] = "Il peut y avoir un problème avec la clé API de Google Maps sur cette page";
$lang["gmapilink"] = "Vous pouvez trouver plus d'informations sur les clés API sur cette <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">page web Google</a>";
$lang["import"] = "Importer une trace";
$lang["iuploadfailure"] = "Echec du téléversement";
$lang["iparsefailure"] = "Echec de l'analyse du contenu";
$lang["idatafailure"] = "Aucune donnée de trace dans le fichier importé";
$lang["isizefailure"] = "La taille du fichier téléversé ne doit pas dépasser %d octets"; // substitutes number of bytes
$lang["imultiple"] = "Remarque : plusieurs traces importées (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Tous les utilisateurs";
$lang["unitday"] = "j"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "km/h"; // kilometer per hour
$lang["unitm"] = "m"; // meter
$lang["unitamsl"] = " "; // above mean see level
$lang["unitkm"] = "km"; // kilometer
$lang["unitmph"] = "mph"; // mile per hour
$lang["unitft"] = "ft"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "nm"; // nautical mile
$lang["config"] = "Paramètres";
$lang["editingconfig"] = "Paramètres par défaut de l'application";
$lang["latitude"] = "Latitude initiale";
$lang["longitude"] = "Longitude initiale";
$lang["interval"] = "Intervalle (s)";
$lang["googlekey"] = "Clé d'API Google Maps";
$lang["passlength"] = "Longueur minimum du mot de passe";
$lang["passstrength"] = "Force minimale du mot de passe";
$lang["requireauth"] = "Identification obligatoire";
$lang["publictracks"] = "Toutes les traces sont publiques";
$lang["strokeweight"] = "Largeur du tracé";
$lang["strokeopacity"] = "Opacité du tracé";
$lang["strokecolor"] = "Couleur du tracé";
$lang["colornormal"] = "Couleur d'un point de passage";
$lang["colorstart"] = "Couleur du point de démarrage";
$lang["colorstop"] = "Couleur du point d’arrêt";
$lang["colorextra"] = "Couleur d'un point d'intérêt";
$lang["colorhilite"] = "Couleur d'un point sélectionné";
$lang["uploadmaxsize"] = "Taille max. de téléversement (Mo)";
$lang["ollayers"] = "Fonds de carte OpenLayer";
$lang["layername"] = "Nom de la couche";
$lang["layerurl"] = "URL de la couche";
$lang["add"] = "Ajouter";
$lang["edit"] = "Editer";
$lang["delete"] = "Effacer";
$lang["settings"] = "Paramètres";
$lang["trackcolor"] = "Couleur dynamique";
?>
