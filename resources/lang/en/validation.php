<?php

return [
    'required' => ':attribute is required.',
    'email' => 'The :attribute must be a valid email address.',
    'string' => ':attribute must be a string.',
    'integer' => ':attribute must be an integer.',
    'max' => [
        'string' => ':attribute cannot exceed :max characters.',
        'numeric' => ':attribute cannot be greater than :max.',
    ],
    'min' => [
        'string' => ':attribute cannot be less than :min characters.',
        'numeric' => ':attribute cannot be less than :min.',
    ],
    'in' => 'The selected :attribute is invalid.',
    'attributes' => [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'keyword' => 'Search keyword',
        'type' => 'Content type',
        'sort_by' => 'Sort criteria',
        'sort_direction' => 'Sort direction',
        'per_page' => 'Items per page',
        'page' => 'Page number',
    ],
    'custom' => [
        'keyword' => [
            'min' => 'Search keyword must be at least :min characters',
            'max' => 'Search keyword cannot exceed :max characters',
        ],
        'type' => [
            'in' => 'Content type must be video or article',
        ],
        'sort_by' => [
            'in' => 'Invalid sort criteria',
        ],
        'sort_direction' => [
            'in' => 'Sort direction must be asc or desc',
        ],
        'per_page' => [
            'min' => 'Minimum :min items per page',
            'max' => 'Maximum :max items per page',
        ],
        'page' => [
            'min' => 'Page number must be at least :min',
        ],
        'email' => [
            'required' => 'Email address is required',
            'email' => 'Please enter a valid email address',
        ],
        'password' => [
            'required' => 'Password is required',
            'min' => 'Password must be at least :min characters',
            'max' => 'Password cannot exceed :max characters',
        ],
    ],
];
