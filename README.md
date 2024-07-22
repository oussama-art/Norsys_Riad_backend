# Norsys_Riad_backend

Bienvenue dans le projet Symfony "Norsys_Riad_backend"! Ce document vous guidera à travers l'installation, la configuration et l'utilisation de l'application.

## Table des matières

1. [Introduction](#introduction)
2. [Prérequis](#prérequis)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Utilisation](#utilisation)
6. [Structure du projet](#structure-du-projet)
7. [Contributions](#contributions)
8. [Licence](#licence)

## Introduction
### Objectif du projet

Le but principal de ce projet est de créer un site web permettant aux utilisateurs de réserver des séjours dans un riad, avec la possibilité d’effectuer des paiements en ligne en toute sécurité.
### Contexte
Le site sera destiné aux clients recherchant un hébergement authentique dans un riad, offrant une expérience personnalisée et traditionnelle.

## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine :

- Install PHP 8.1 or higher
- Install Composer 2.7.7 or higher, which is used to install PHP packages.
- MySQL/postgres ou un autre système de gestion de base de données supporté par Doctrine

## Installation

Pour installer les dépendances du projet, suivez ces étapes :

1. Clonez le dépôt depuis GitHub :

   ```bash
   git clone https://github.com/oussama-art/Norsys_Riad_backend/
   cd reservation_riad

2. Installez les dépendances à l'aide de Composer :

   ```bash
   composer install
## Configuration
1. Configurez votre base de données et d'autres paramètres nécessaires dans le fichier .env.
   ```DATABASE_URL="mysql://username:password@127.0.0.1:3306/database_name"```

   Dans cas d'erreur de drive essayez cette configuration :
   ```DATABASE_URL="mysql://username:password@127.0.0.1:3306/database_name?serverVersion=10.11.2-MariaDB&charset=utf8mb4"```

2.  Exécutez les migrations :
```bash
php bin/console doctrine:migrations:migrate
```
3. Installez OpenSSL pour générer vos clés (publiques et privées), cela vous aidera à générer des JWT.

## Utilisation
Pour démarrer l'application Symfony localement, exécutez la commande suivante :
```bash
symfony serve:start
```
ajouter option -d si vous voulez démarrer le projet en Arrière-plan (symfony serve:start -d)

## Structure du projet
```bash
Norsys_Riad_backend/
│
├── config/               # Fichiers de configuration Symfony
├── public/               # Fichiers publics (index.php, assets)
├── src/                  # Code source de l'application
│   ├── Controller/       # Contrôleurs Symfony
│   ├── Entity/           # Entités Doctrine
│   ├── Form/             # Formulaires Symfony
│   ├── Repository/       # Répertoires Doctrine
│   └── ...               # Autres classes PHP
├── templates/            # Templates Twig
├── tests/                # Tests unitaires et fonctionnels
├── var/                  # Fichiers de cache et de logs Symfony
├── vendor/               # Dépendances Composer
├── .env                  # Fichier de configuration des variables d'environnement
├── .env.example          # Exemple de fichier .env
├── composer.json         # Fichier de configuration Composer
└── README.md             # Fichier README du projet
```
## Contributions
1. Fork du projet
2. Créez une nouvelle branche (git checkout -b feature/nouvelle-fonctionnalité)
3. Committez vos modifications (git commit -am 'Ajout de la nouvelle fonctionnalité')
4. Push vers la branche (git push origin feature/nouvelle-fonctionnalité)
5. Créez une Pull Request



   
