# **Projet mongodb - Cocofé**

## Création d'une application web avec une base de données MongoDB.

### Projet réalisé par:
- Tiago Fernandes
- Thibaut Comte
- Dylan Corroyer
___

### Informations sur le projet:

**Objectif**: Suite à notre cours passé sur les bases de données NOSQL, le but du projet est de réaliser une application web libre à condition qu'elle utilise une base de données proposée par MongoDB.

Pour ce faire, nous avons décidé d'utiliser le langage de programmation PHP(7.3) avec le framework Symfony dans sa version 4.3.
___

### __**Etape 1**__, démarrer le serveur mongodb sur le port 27018 (27017 pris par défaut).

    dcorroyer@dc-lp:~$ mongod --port 27018

### __**Etape 2**__, utilisation du logiciel Robo3T pour se connecter au serveur mongodb initialisé plus tôt, créer la base de données qui va nous servir pour notre application.

![image](./images/robo3t_connection.png)

Maintenant que la connexion au serveur mongodb, il nous faut créer la base de données, intégrer des données dedans puis tester le bon retour d'informations sur Robo3T.

![image](./images/robo3t_creation_database.png)

Pour cet exemple, nous avons créé une base de données sous le nom:

    mongo_database

La collection correspondant à cette base s'appellera:

    project_collection

La commande à lancer pour importer les fixtures dans la bdd (base de données) se fait grâce à l'utilisation de **mongoimport**.

Au préalable, un dossier ***/data/db/*** a été créé pour le stockage des données de notre serveur mongo.

    mongoimport --port 27018 --db mongo_database --collection project_collection ~/Téléchargements/restaurants.json

- ***--port 27018*** ; Indique le port du serveur sur lequel nous souhaitons importer les données.

- ***--db mongo_database*** ; Indique la base de données correspondante.

- ***--collection project_collection*** ; Indique la collection.

- ***~/Téléchargements/restaurants.json*** ; correspond au chemin du fichier **JSON** de fixtures.

L'invite de commandes nous renvoie la bonne transmission des données selon les paramètres que l'on a indiqué.

![image](./images/mongoimport.png)

Ensuite nous passons sur Robo3T pour vérifier qu'on a nos données avec la commande suivante:

    db.getCollection('project_collection').find({})

En voici le résultat, nous avons bien nos données dans notre base de données.

![image](./images/mongoimport_verification.png)

On peut donc en conclure que notre base de données a été initialisée correctement.

## Sécurité et réplication

### __**Etape 3**__, Mise en place du ReplicaSet de notre serveur mongodb.

Pour mettre en place la réplication de notre serveur mongodb, nous allons initialiser le ReplicaSet (replSet).
Nous allons commencer par créer un répertoire par serveur de réplication, chacun sera lié à son répertoire.

![image](./images/repl_folders.png)

Nous allons maintenant initialiser les serveurs du ReplicaSet et les lier à leur folder correspondant grâce à la commande suivante:

    mongod --replSet rs0 --port 27019 --dbpath /data/R0S1

- ***--replSet rs0*** ; Indique le nom de ReplicaSet auquel sera lié le serveur de réplication.

- ***--port 27019*** ; Indique le port du serveur.

- ***--dbpath /data/R0S1*** ; Indique le répertoire qui sera lié au serveur.

La configuration minimum d'un ReplicaSet impose l'utilisation de 3 serveurs minimum, 1 serveur de réplication principal et 2 serveurs de réplications secondaires. Dans le cadre du projet, une configuration minimum répondra parfaitement à nos besoins.

Nous avons donc 3 serveurs à configurer, voici leurs caractéristiques:

- **PRIMARY** : port 27019 / folder R0S1
- **SECONDARY(1)** : port 27020 / folder R0S2
- **SECONDARY(2)** : port 27021 / folder R0S3

À l'aide de la commande présentée ci-dessus, nous avons initialisés les serveurs du ReplicaSet, voici le résultat:

![image](./images/repl1.png)
![image](./images/repl1_listen.png)

![image](./images/repl2.png)
![image](./images/repl2_listen.png)

![image](./images/repl3.png)
![image](./images/repl3_listen.png)

Maintenant que chaque serveur a pu être initialisé correctement, nous allons nous connecter sur le le serveur **R0S1** afin d'effectuer sa configuration, avec la commande:

    mongo --port 27019

Une fois sur le serveur nous allons initialiser sa configuration, avec la commande suivante:

    rs.initiate();

Ci-dessous, nous pouvons voir que l'initialisation s'est déroulée correctement car le champ **"me"** nous retourne son ip ainsi que le port d'écoute:

- "me" : "localhost:**27019**"

![image](./images/repl1_initiate.png)

Nous pouvons également constater que le serveur est automatiquement renseigné comme **PRIMARY** (étant à cette étape le seul qui soit initialisé, il est forcément primaire).

![image](./images/repl1_primary.png)

On va ensuite ajouter les deux serveurs secondaires au ReplicaSet. Pour ce faire, nous allons les ajouter sur le serveur **PRIMARY** avec la commande suivante:

    rs.add("localhost:27020");

Rappelons que les serveurs de réplication secondaires sont configurés sur les ports **27020** et **27021**.
La bonne insertion des serveurs de réplication secondaires dans le ReplicaSet est confirmée par le champ **"ok"** qui retourne **"1"** pour chaque serveur.

![image](./images/repl1_add_2_3.png)

Maintenant que les serveurs secondaires sont insérés dans le ReplicaSet, nous allons nommer un **arbitre**. L'arbitre va servir à orchestrer le ResplicaSet dans le cas où le serveur primaire tombe, il nommera un des deux services de réplication comme nouveau-primaire, afin de limiter la coupure de la base de données à quelques secondes. Ce procédé permet de conserver l'intégrité de l'application en production.

Pour ce faire, nous allons créer le répertoire de stockage auquel il sera lié.

Voici l'arborescence actuelle du dossier de fonctionnement de notre base de données MongoDB incluant le répertoire de l'arbitre.

![image](./images/arb_folder.png)

Nous allons initialiser l'arbitre dans le ReplicaSet avec la commande suivante:

    mongod --port 30000 --dbpath /data/arb --replSet rs0

À noter que la commande est la même que celle pour initialiser un serveur du ReplicaSet. 
Voici le résultat de l'initialisation du serveur, mais n'est pas encore arbitre.

![image](./images/arb.png)
![image](./images/arb_listen.png)

Il faut maintenant indiquer au serveur primaire (qui est le maitre) que le serveur arbitre est configuré sur le port 30000.

    rs.addArb("local:30000")

![image](./images/repl1_add_arb.png)

Désormais, l'arbitre est configuré avec succès, il va donc pouvoir se charger de nommer un nouveau-primaire en cas de panne du serveur de réplication principal.

Nous allons maintenant effectuer un test de notre ReplicaSet.
Notre serveur principal se trouve sur le port 27018, comme nous avons pu le voir sur le logiciel Robo3T, nous avons accès à nos données sans le moindre problème.

Nous souhaitons avoir la possibilité de vérifier le bon fonctionnement du ReplicaSet, pour ce faire, il nous faut insérer les fixtures dans le serveur **PRIMARY** du ReplicatSet, de la même manière que sur la bdd principal.

Sur Robot3T, il faut se connecter à localhost:**27019** (serveur de réplication primaire), puis importer les datas

![image](./images/robo3t_creation_database2.png)

Importons les données avec la commande:

    mongoimport --port 27019 --db mongo_database --collection project_collection ~/Téléchargements/restaurants.json

Vérifions que les données sont bien présentes sur les seveurs de réplication:

![image](./images/mongoimport_verification2.png)

Nous pouvons constater que nos données sont bien importées dans les serveurs du ReplicaSet.

Pour tester le ReplicaSet, on va simuler la perte du serveur principal en le coupant, puis nous irons vérifier que l'arbitre joue correctement son rôle en nommant un des deux serveurs de réplication secondaire en primaire.

Pour simuler la perte du serveur de réplication principal, nous allons tout simplement éteindre le serveur en ligne de commandes (ici Ctrl+C):

![image](./images/repl1_down_cli.png)

Nous pouvons constater dans Robo3T que nous sommes désormais incapable d'accéder aux données du serveur à cause d'un problème de connexion au serveur, le serveur est donc bien tombé (Voir ci-dessous).

![image](./images/repl1_down.png)

Pour finaliser le test des ReplicaSet, il nous faut trouver le serveur qui a été nommé **PRIMARY** par l'arbitre. Pour ce faire, nous allons nous connecter à chaque serveur de réplication afin de connaitre sa position, avec la commande:

    mongo --port 27020

Nous effectuons la même opération pour le serveur 27021. En se connectant au serveur 27021, on constate que c'est le serveur qui a été nommé serveur de réplication principal.

![image](./images/repl1_replaced.png)

Nous allons maintenant nous connecter avec Robo3T sur le serveur afin de vérifier le bon accès aux données.

![image](./images/robo3t_creation_database3.png)

Nous avons bien accès aux données sur le serveur de réplication qui a été nommé **PRIMARY** par l'arbitre suite à la perte du premier serveur primaire.

Le ReplicaSet est donc opérationnel.

___


## L'application web.

### __**Etape 4**__, Présentation de l'application web.

L'application a été installé sur un serveur personnel avec un nom de domaine afin d'avoir un rendu visuel et fonctionnel le plus éloquent possible.

*Voici le lien*: http://fernandes77.myqnapcloud.com/projet-mongo/public/index.php/product/

*Voici le lien du Github qui héberge le code*: https://github.com/thibaut-comte/projet-mongo (le projet a été mis en public afin que vous puissiez consulter le code sans soucis).

Ci-dessous est représentée la vue principale de l'application, la page d'accueil du site. Dans le but de répondre au sujet du projet, nous avons décidé de développer une application de vente de produits en ligne de particulier à particulier (comme Leboncoin par exemple). Les utilisateurs pourront s'inscrire et mettre en vente leurs produits. La création, modification ou encore suppression d'un produit viendra directement modifier les informations enregistrées en base de données.

![image](./images/appli.png)

Voici un schéma de la base de données qui communique avec notre application. C'est un AWS (Amazon Web Services) qui produit notre base de données, nous pouvons constater les 3 tables que nous utilisons dans la partie **Collections**, nous pouvons également constater la présence de nos 3 ReplicaSet afin d'assurer la sauvegarde et l'intégrité de notre application en production.

![image](./images/database.png)

Procédure de connexion à la base de donnée en production avec **Robo3T**:

- New connection
- dans `From SRV` => **mongodb+srv://cocofe:cocofe@cluster0-iog6u.mongodb.net/test?retryWrites=true&w=majority**
- Appuyez sur `From SRV`
- Testez
- Save

___

Revenons à l'application, voici les identifiants de connexion: **admin / admin** (à savoir qu'il n'est pas nécessaire d'être identifié pour voir les produits qui sont enregistrés en base de données et donc affichés sur l'application web).

Sur l'image ci-dessous, nous allons présenter la modification d'un produit présent dans la base de données MongoDB. Ici, le produit nommé **"sit"** (les données sont alimentées par des **fixtures** pour un gain de temps de développement considérable).

![image](./images/appli_article.png)

Voici une vue en **JSON** des données du produit enregistré dans la base de données:

    {
    "_id" : ObjectId("5dbedf3c341b7a20ed59654d"),
    "price" : 3256.0,
    "name" : "sit",
    "category" : {
        "$ref" : "Category",
        "$id" : ObjectId("5dbedf3b341b7a20ed59654a")
    },
    "city" : "Blot",
    "description" : "Et maiores voluptatem neque sit. Dicta cupiditate vero accusantium quis exercitationem. Et debitis et praesentium temporibus alias amet quis sunt.",
    "imageFilename" : "https://lorempixel.com/400/200/technics/?19148",
    "user" : {
        "$ref" : "User",
        "$id" : ObjectId("5dbedf3c341b7a20ed59654c")
    },
    "dateInsert" : ISODate("2019-07-06T18:32:12.000Z")
    }

L'utilisateur qui a déposé ce produit peut aller le modifier avec la vue ci-dessous. Voici les données que l'utilisateur a modifié dans ce test fonctionnel:

- ***Nom du produit***: sit ==> Mon produit.

- ***Prix***: 3256€ ==> 10€.

- ***Description***: Et maiores [...] quis sunt ==> Modification du produit.

![image](./images/appli_article_mod.png)

Voici la vue modifiée en conséquence où nous récupérons correctement les données lorsqu'on les affiche:

![image](./images/appli_article_mod2.png)

On constate également sur la vue en **JSON** des données que les champs en question ont été modifiés.

    {
    "_id" : ObjectId("5dbedf3c341b7a20ed59654d"),
    "price" : 10.0,
    "name" : "Mon produit",
    "category" : {
        "$ref" : "Category",
        "$id" : ObjectId("5dbedf3b341b7a20ed59654a")
    },
    "city" : "Blot",
    "description" : "Modification du produit",
    "imageFilename" : "https://lorempixel.com/400/200/technics/?19148",
    "user" : {
        "$ref" : "User",
        "$id" : ObjectId("5dbedf3c341b7a20ed59654c")
    },
    "dateInsert" : ISODate("2019-07-06T18:32:12.000Z")
    }

___

Structure et rôle: **ADMIN**:

    {
        "_id" : ObjectId("5dbedf3c341b7a20ed59654b"),
        "firstname" : "Gonzalez",
        "lastname" : "Benjamin",
        "email" : "admin@admin.com",
        "password" : "$argon2id$v=19$m=65536,t=4,p=1$b4LZG+ttEJY/QoGcHrF2fg$RsQzcIOWmU/ygIqV3jq0QlJGlIhMWDleHbRVA1ZS4Hw",
        "creation_date" : ISODate("2018-02-27T15:52:05.000Z"),
        "username" : "admin",
        "roles" : [ 
            "ROLE_ADMIN"
        ]
    }

___

Structure et rôle: **USER**:

    {
        "_id" : ObjectId("5dbedf3c341b7a20ed59654c"),
        "firstname" : "Le Gall",
        "lastname" : "Émile",
        "email" : "martineau.gerard@club-internet.fr",
        "password" : "$argon2id$v=19$m=65536,t=4,p=1$JZ4o0/gtDMjeeNFSDL++NQ$WfEMLNFGm/UEABdj+Xqz7ZJm/15RTIhfXjVBwXi5OEg",
        "creation_date" : ISODate("2018-10-17T16:37:38.000Z"),
        "username" : "agnes00",
        "roles" : [ 
            "ROLE_USER"
        ]
    }

Mots de passe chiffrés en Sodium. On peut également voir que les rôles utilisateur et admin sont bien définis, ce qui implique des droits sur l'application plus ou moins développés en fonction du profil de la personne qui souhaite se connecter à l'application.

## **Conclusion**:

Nous avons pu mettre en place une application web qui communique avec une base de données NoSQL de type MongoDB.