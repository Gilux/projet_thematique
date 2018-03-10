Projet thématique "Dépôt de devoirs en ligne"
========================

Utilise Symfony 3.3

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

 - dev_mailer_transport: smtp
 - dev_mailer_host: smtp-mail.outlook.com
 - dev_mailer_port: 587
 - dev_mailer_user: miage_depot_devoirs@outlook.fr
 - dev_mailer_password: (voir messenger)
 - dev_mailer_encryption: tls



