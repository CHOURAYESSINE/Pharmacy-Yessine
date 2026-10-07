# Pharmacy sur Vercel

Le déploiement garde l'application Laravel et l'interface locale. PHP 8.2 est utilisé avec `vercel-php@0.6.2` pour respecter les contraintes du fichier composer.lock.

Configurer APP_KEY, DATABASE_URL et APP_URL dans Vercel. La connexion PostgreSQL utilise le schéma isolé `pharmacy`. Les migrations et les données initiales sont exécutées une fois depuis un environnement PHP avec PDO PostgreSQL : `php artisan migrate --force --seed`. Le premier compte est `admin@admin.com`; son mot de passe doit être fourni par ADMIN_PASSWORD, jamais publié dans le dépôt.

Les images des achats, des profils, du logo et du favicon restent conservées dans la base et accessibles par /storage. Les images des achats et profils sont limitées à 2 Mo. Les sauvegardes sont des ZIP contenant un export JSON des tables de cette application ; elles sont chiffrées dans la base et téléchargeables par un administrateur (3 Mo maximum par archive). Ce format contient les données, pas un dump SQL natif ni le code source.

Les ventes réservent le stock dans une transaction. Leur modification et suppression ajustent les quantités. Les produits périmés ne peuvent pas être vendus. Les permissions sont vérifiées côté serveur. Les sessions utilisent des cookies chiffrés ; le cache de limitation des connexions est conservé dans PostgreSQL.

Les notifications de stock sont visibles dans l'application. Le mail est journalisé tant qu'un fournisseur SMTP n'est pas configuré. Les comptes inscrits via le formulaire public reçoivent le rôle sales-person prévu par le projet ; seul un administrateur gère les utilisateurs et leurs rôles.

La base créée pour cette publication commence vide, avec le compte administrateur et les rôles. Aucune donnée personnelle issue de la base locale n'est transférée automatiquement. Le dossier local d'origine et son fichier .env sont conservés.
