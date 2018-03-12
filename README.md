Projet thématique "Dépôt de devoirs en ligne"
========================

Utilise Symfony 3.4

## Installation du projet

- git clone
- composer install (rentrer informations adresse mail et password sinon bug)
- npm install
- php bin\console doctrine:database:create
- php bin\console doctrine:schema:update --force --dump-sql
- php bin\console fos:user:create
- php bin\console server:run

##Configuration

à ajouter dans 

app/config/parameters.yml : 

 - mailer_transport: smtp
 - mailer_host: smtp-mail.outlook.com
 - mailer_port: 587
 - mailer_user: miage_depot_devoirs@outlook.fr
 - mailer_password: (voir messenger)
 - mailer_encryption: tls



