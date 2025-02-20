Le déploiement n'ayant pas pu se faire, j'ai simplement lancé en local

Via Xampp (Pour MySql) :

Editer db.php 

Definir $host en tant que 'localhost'
Definir $dbname en tant que '<nom de la db>'
Definir $username en tant que 'root'

Via MongoDBCompass (Pour MongoDB)

Editer contact.db

Definir $client en tant que new MongoDB\Client("mongodb://localhost:<port>")

Par défaut 27017

Dans PHPMyAdmin (Disponible a "http://localhost/phpmyadmin/index.php") :

- Creer une nouvelle base de donnée en tant que <nom de la db>
- Executer la Requete "db.sql" trouvable a la racine du projet

Compte Admin : - Username : admin - Password : admin