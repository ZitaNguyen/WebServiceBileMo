# WebServiceBileMo
BileMo est une entreprise offrant toute une sélection de téléphones mobiles haut de gamme, avec la vente exclusivement en B2B (business to business). Ce projet est pour créer une API afin de développer leur vitrine de téléphones mobiles.

## Description du projet

Voici les principales fonctionnalités disponibles demandées par le client:

  * consulter la liste des produits BileMo
  * consulter les détails d’un produit BileMo
  * consulter la liste des utilisateurs inscrits liés à un client sur le site web
  * consulter le détail d’un utilisateur inscrit lié à un client
  * ajouter un nouvel utilisateur lié à un client
  * supprimer un utilisateur ajouté par un client
  * les réponses (productList, userList) sont mises en cache afin d’optimiser les performances des requêtes en direction de l’API

## Contraintes

Les clients de l’API doivent être authentifiés via JWT.

## Contrôle du code

La qualité du code a été validé par [Codacy](https://codeclimate.com/). Vous pouvez accéder au rapport de contrôle en cliquant sur [ce lien](https://app.codacy.com/gh/ZitaNguyen/WebServiceBileMo/dashboard).

## Prérequis

Php ainsi que Composer doivent être installés sur votre ordinateur afin de pouvoir correctement lancé l'API.

## Installation
Pour commencer avec ce projet PHP, suivez les étapes ci-dessous
1. Clonez le dépôt
   ```bash
   git clone hhttps://github.com/ZitaNguyen/WebServiceBileMo.git
   ```
2. Accédez au répertoire du projet
   ```bash
   cd <nom du répertoire>
   ```
3. Installez les dépendances requises pour le projet
   ```bash
   composer install
   ```
4. Configurez de la base de données
- Installez MAMP ou XAMPP si besoin
- Modifiez les valeurs dans le fichier `.env.local` pour les adapter à votre configuratione locale.
   ```bash
   DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:8889/SnowTricks?serverVersion=5.7.40"
   ```
5. Exécuter la création de la base de donnée avec la commande:
   ```bash
   symfony console doctrine:database:create
   ```
6. Exécuter la migration en base de donnée:
   ```bash
   symfony console doctrine:migration:migrate
   ```
7. Exécuter les dataFixtures avec la commande:
   ```bash
   php bin/console doctrine:fixtures:load
   ```
8. Générez des clés d'authentification JWT avec des commandes:
    ```bash
    $ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    $ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
    ```
9.  Démarrez le serveur de développement
    ```bash
    symfony server:start
    ```

## Liste des commandes CURL à exécuter dans le terminal ([jq](https://stedolan.github.io/jq/download/) doit être installé):

* Authentification:

    ```bash
    TOKEN=$(curl -s -X POST -H 'Accept: application/json' -H 'Content-Type: application/json' --data '{"username":"YourUsername" ,"password" : "YourPassword"}' https://127.0.0.1:8000/api/login_check | jq -r '.token')
    ```

* Réupérer la liste des produits:

    ```bash
    curl -X GET -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" https://127.0.0.1:8000/api/products
    ```
* Réupérer la liste des produits (par page avec une limitation si besoin):

    ```bash
    curl -X GET -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" https://127.0.0.1:8000/api/products?page=1&limit=2
    ```
* Réupérer un produit:

    ```bash
    curl -X GET -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" https://127.0.0.1:8000/api/products/{productID}
    ```
* Réupérer la liste des utilisateurs:

    ```bash
    curl -X GET -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" https://127.0.0.1:8000/api/users
    ```
* Réupérer la liste des utilisateurs (par page avec une limitation si besoin):

    ```bash
    curl -X GET -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" https://127.0.0.1:8000/api/users?page=1&limit=2
    ```
* Réupérer un utilisateur:

    ```bash
    curl -X GET -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" https://127.0.0.1:8000/api/users/{userID}
    ```

* Ajouter un utilisateur:

    ```bash
    curl -X POST -H "Content-Type: application/json" -d '{"firstName":"Zita","lastName":"Van","email":"zita@test.fr","phone":"0123456789","address":"123 rue de test"}' -H "Authorization: Bearer $TOKEN" https://127.0.0.1:8000/api/users
    ```

* Supprimer un utilisateur:

    ```bash
    curl -X DELETE -H "Authorization: Bearer $TOKEN" https://127.0.0.1:8000/api/users/{userID}
    ```

## Documentation Technique

 * https://127.0.0.1:8000/api/doc

## Licence

Ce projet est sous licence Apache License 2.0.

