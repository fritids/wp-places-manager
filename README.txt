=== Plugin Name ===
Contributors: mbamultimedia, wp-maverick
Tags: custom post type, places, maps
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: trunk

TODO Mettre à jour en fonction du projet

A plugin to manage (Create, update, delete) places (eg. Points of interest, shops, etc.) via the Wordpress admin panel.

== Description ==

TODO Mettre à jour en fonction du projet

Ce plugin exemple est une base de code réutilisable comprenant déjà un ensemble de fonctionnalités considérées comme communes à différents projets.

Il permet l'ajout/l'édition/la suppression d'objets customisés (Custom post types).

L'exemple rajoute à wordpress la possibilité de gérer des ou lieux ('places').

Une taxonomie est rajoutée pour classer ces lieux selon des catégories ('places_categories').

La page d'édition d'un lieu permet la gestion d'attributs standards :
* D'une featured image (image à la une)
* D'une description textuelle
* D'une ou plusieurs catégories ('places_categories')

Et aussi d'attributs custom ('custom_fields') :
* Coordonnées géographiques
* Coordonnées postales

Le panel d'admin est customisé pour afficher de nouvelles colonnes et gérer un filtrage additionnel sur les catégories de lieux.

Le plugin ajoute à wordpress un template d'affichage customisé, utilisé en front pour l'affichage des lieux (single-places.php).

TODO A améliorer en proposant par exemple la saisie des coord GPS via une google maps + interface d'admin du plugin pour saisie d'un clé d'API Google maps...

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

= What about foo bar? =

Answer to foo bar dilemma.

== Changelog ==

= 0.1-sample =
JLT : Mise en place du socle de plugin avec fonctionnalités de base
