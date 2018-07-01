<?php
$db = parse_url(getenv('CLEARDB_DATABASE_URL'));

//$db = array("host" => "localhost", "port" => null, "user" => "root", "path" => ".depot", "pass" => "", "secret" => "osef");

$container->setParameter('database_driver', 'pdo_mysql');
$container->setParameter('database_host', $db['host']);
$container->setParameter('database_port', $db['port']);
$container->setParameter('database_name', substr($db["path"], 1));
$container->setParameter('database_user', $db['user']);
$container->setParameter('database_password', $db['pass']);
$container->setParameter('secret', getenv('SECRET'));
$container->setParameter('locale', 'en');

//$container->setParameter('mailer_transport', 'smtp');
//$container->setParameter('mailer_host', 'smtp-mail.outlook.com');
//$container->setParameter('mailer_port', '587');
//$container->setParameter('mailer_user', "miage_depot_devoirs@outlook.fr");
//$container->setParameter('mailer_password', getenv('EMAIL_PASSWORD'));
//$container->setParameter('mailer_encryption', 'tls');

$container->setParameter('mailer_transport', 'smtp');
$container->setParameter('mailer_host', 'smtp.gmail.com');
$container->setParameter('mailer_port', '25');
$container->setParameter('mailer_user', "depot.devoir.miage@gmail.com");
$container->setParameter('mailer_password', getenv('EMAIL_PASSWORD'));
$container->setParameter('mailer_encryption', 'tls');