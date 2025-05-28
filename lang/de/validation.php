<?php

declare(strict_types=1);

return [

    'array' => ':Attribute muss ein Array sein.',
    'boolean' => ':Attribute muss entweder true oder false sein.',
    'confirmed' => ':Attribute stimmt nicht mit der Bestätigung überein.',
    'current_password' => 'Das Passwort ist falsch.',
    'email' => ':Attribute muss eine gültige E-Mail-Adresse sein.',
    'lowercase' => ':Attribute muss in Kleinbuchstaben sein.',
    'max' => [
        'string' => ':Attribute darf maximal :max Zeichen haben.',
    ],
    'mimetypes' => 'Ungültiger Dateityp.',
    'password' => [
        'mixed' => ':Attribute muss mindestens einen Großbuchstaben und einen Kleinbuchstaben beinhalten.',
        'numbers' => ':Attribute muss mindestens eine Zahl beinhalten.',
    ],
    'required' => ':Attribute muss ausgefüllt werden.',
    'required_with' => ':Attribute muss ausgefüllt werden.',
    'string' => ':Attribute muss ein String sein.',
    'unique' => ':Attribute ist bereits vergeben.',
    'uploaded' => 'Datei konnte nicht hochgeladen werden.',

    'attributes' => [
        'current_password' => 'Derzeitiges Passwort',
        'email' => 'E-Mail-Adresse',
        'name' => 'Name',
        'password' => 'Passwort',

        'title' => 'Titel',
        'description' => 'Beschreibung',
        'questions.*.question_text' => 'Fragetext',
        'questions.*.options.*' => 'Antwortoption',
        'questions.*.options.*.option_text' => 'Antwortoption',

        'response.*' => 'Feld',
    ],

];
