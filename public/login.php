<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Yaml\Yaml;

use Twig\Extension\DebugExtension;

// activation du système d'autoloading de Composer
require_once __DIR__.'/../vendor/autoload.php';

// instanciation du chargeur de templates
$loader = new FilesystemLoader(__DIR__.'/../templates');

// instanciation du moteur de template
$twig = new Environment($loader, [
    'debug' => true,
    'strict_variables' => true,
]);

$twig->AddExtension(new DebugExtension());

// traitement des données 
$config = Yaml::parseFile(__DIR__.'/../config/config.yaml');
$data = [
    'login' => '',
    'password' => '',
];
$errors = [];

if ($_POST) {
    foreach ($data as $key => $value){
        if (isset($_POST[$key])) {
            $data[$key] = $_POST[$key];
        }
    }
    if (empty($data['login'])) {
        $errors['login'] = 'Veuillez rentrer votre login';
    } elseif (strlen($data['login']) >= 190) {
        $errors['login'] = 'Le login est trop long (190 caracteres max)';
    } elseif ($data['login'] != $config['login']) {
        $errors['login'] = 'Un ou les deux identifiants sont incorrects';
        $errors['password'] = 'Un ou les deux identifiants sont incorrects';
    }

    if (empty($data['password'])) {
        $errors['password'] = 'Veuillez rentrer votre mot de passe';
    } elseif (preg_match('/[^A-Za-z]/', $data['password']) === 0 || preg_match('/[0-9]/', $data['password']) === 0 || preg_match('/[^A-Za-z0-9]/', $data['password']) === 0){
        $errors['password'] = 'Le mot de passe doit comprendre au moins un caractère latin, un chiffre, et un caractère spécial';   
    } elseif (!password_verify($_POST['password'], $config['password'])) {
        $errors['login'] = 'Un ou les deux identifiants sont incorrects';
        $errors['password'] = 'Un ou les deux identifiants sont incorrects';
    } 

    if (empty($errors)) {

        //redirection vers la page private.php
        $url = 'logged.php';
        header("location: {$url}", true, 301);
        exit();
    } 
}

// affichage du rendu d'un template
echo $twig->render('login.html.twig', [
    // transmission de données au template
    'errors' => $errors,
    'data' => $data,
]);

