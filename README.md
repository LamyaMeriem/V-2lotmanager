# Module PrestaShop - Gestionnaire de Lots

## Description

Le module Gestionnaire de Lots est une solution complète pour optimiser et tracer le processus d'approvisionnement en produits reconditionnés (smartphones, tablettes, etc.) dans PrestaShop.

## Fonctionnalités Principales

### 🎯 Gestion des Lots d'Achat
- Création et suivi de lots traçables
- Attribution automatique de numéros de lots
- Gestion des statuts (En attente, En cours, Terminé, Archivé)
- Calcul automatique des coûts et marges

### 📁 Import Intelligent
- Support des formats Excel (.xlsx), CSV et PDF
- Interface de mapping pour associer les colonnes
- Sauvegarde des profils de mapping par fournisseur
- Validation et prévisualisation des données

### 🔍 Qualification des Produits
- Interface de test produit par produit
- Classification Fonctionnel/Défectueux
- Association automatique aux SKUs PrestaShop
- Gestion des pannes avec système configurable

### 📊 Statistiques et Rapports
- Tableau de bord avec KPIs en temps réel
- Analyse par fournisseur et par période
- Top des pannes les plus fréquentes
- Calcul de rentabilité par lot

### ⚙️ Configuration Avancée
- Gestion des fournisseurs
- Dictionnaire de correspondances pour normalisation
- Gestion des types de pannes
- Profils de mapping réutilisables

## Installation

1. Téléchargez le module et placez-le dans le dossier `/modules/lotmanager/`
2. Connectez-vous au back-office PrestaShop
3. Allez dans **Modules > Gestionnaire de modules**
4. Recherchez "Gestionnaire de Lots" et cliquez sur **Installer**
5. Configurez le module selon vos besoins

## Configuration

### Paramètres Généraux
- **Auto-incrément des lots** : Génération automatique des numéros
- **Marge par défaut** : Pourcentage appliqué pour le calcul des prix de vente
- **Dossier d'upload** : Répertoire de stockage des fichiers importés

### Fournisseurs
Ajoutez vos fournisseurs avec leurs informations de contact pour faciliter la création des lots.

### Pannes
Configurez la liste des pannes possibles pour la qualification des produits défectueux.

### Dictionnaire
Définissez les règles de normalisation pour l'interprétation automatique des noms de produits.

## Utilisation

### 1. Créer un Nouveau Lot
1. Allez dans **Catalogue > Gestionnaire de Lots > Lots**
2. Cliquez sur **Ajouter un lot**
3. Renseignez les informations du lot
4. Uploadez votre fichier d'import

### 2. Configurer le Mapping
1. Associez les colonnes de votre fichier aux champs du système
2. Sauvegardez le profil de mapping pour réutilisation
3. Validez et procédez à l'import

### 3. Qualifier les Produits
1. Accédez à l'interface de qualification
2. Testez chaque produit individuellement
3. Pour les produits fonctionnels : associez au bon SKU
4. Pour les produits défectueux : sélectionnez les pannes

### 4. Suivre les Statistiques
Consultez le tableau de bord pour suivre :
- Les lots en cours de traitement
- Les performances par fournisseur
- Les tendances de qualité
- La rentabilité globale

## Structure de la Base de Données

Le module crée les tables suivantes :
- `ps_lot_manager_lots` : Informations des lots
- `ps_lot_manager_products` : Produits des lots
- `ps_lot_manager_suppliers` : Fournisseurs
- `ps_lot_manager_defects` : Types de pannes
- `ps_lot_manager_product_defects` : Liaison produits-pannes
- `ps_lot_manager_dictionary` : Règles de normalisation
- `ps_lot_manager_mapping_profiles` : Profils de mapping
- `ps_lot_manager_audit` : Journal d'audit

## API et Hooks

Le module utilise les hooks PrestaShop standards et fournit des méthodes pour :
- Mise à jour automatique des stocks
- Calcul des marges
- Génération de rapports
- Audit des actions

## Compatibilité

- **PrestaShop** : 8.0.0 et supérieur
- **PHP** : 7.4 et supérieur
- **Extensions requises** : php-zip, php-xml pour la lecture des fichiers Excel

## Support

Pour toute question ou problème :
1. Consultez la documentation complète
2. Vérifiez les logs dans `/var/logs/`
3. Contactez le support technique

## Licence

Academic Free License (AFL 3.0)

## Changelog

### Version 1.0.0
- Version initiale
- Gestion complète des lots
- Interface de mapping
- Qualification des produits
- Statistiques et rapports
- Configuration avancée