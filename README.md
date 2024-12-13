 Documentation du Projet
 
 1. Installer les dépendances
Utilisez Composer pour installer les dépendances du projet :

```bash
composer install
```

2. Configurer la base de données

Dans le fichier `.env`, configurez la variable `DATABASE_URL` comme suit :

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/TpNote?serverVersion=8.0"
```

3. Créer et configurer la base de données

Exécutez les commandes suivantes pour configurer la base de données :

```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

4. Démarrer le serveur local

Démarrez le serveur local Symfony avec la commande suivante :

```bash
symfony server:start
```

Tester les routes avec Postman

Un fichier JSON Postman contenant les routes à tester est inclus dans ce projet. Vous pouvez l'importer dans votre environnement Postman pour tester facilement les endpoints.

Le fichier porte le nom suivant : `tp_note.postman_collection.json`


