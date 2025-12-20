<?php

declare(strict_types=1);

return [

    'array' => 'The :attribute field must be an array.',
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute field confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'email' => 'The :attribute field must be a valid email address.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'min' => [
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'max' => [
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'password' => [
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
    ],
    'required' => 'The :attribute field is required.',
    'required_with' => 'The :attribute field is required.',
    'string' => 'The :attribute field must be a string.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The file failed to upload.',

    'attributes' => [
        'current_password' => 'current password',
        'email' => 'email address',
        'name' => 'name',
        'password' => 'password',

        'title' => 'title',
        'description' => 'description',
        'questions.*.question_text' => 'question text',
        'questions.*.options.*' => 'answer option',
        'questions.*.options.*.option_text' => 'answer option',

        'response.*' => ' ',
    ],

];
