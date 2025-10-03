<?php

return [
    'required' => ':attribute alanı zorunludur.',
    'email' => ':attribute geçerli bir e-posta olmalıdır.',
    'string' => ':attribute metin olmalıdır.',
    'integer' => ':attribute sayı olmalıdır.',
    'max' => [
        'string' => ':attribute :max karakterden fazla olamaz.',
        'numeric' => ':attribute :max değerinden büyük olamaz.',
    ],
    'min' => [
        'string' => ':attribute :min karakterden az olamaz.',
        'numeric' => ':attribute :min değerinden küçük olamaz.',
    ],
    'in' => 'Seçili :attribute geçerli değil.',
    'attributes' => [
        'name' => 'İsim',
        'email' => 'E-posta',
        'password' => 'Parola',
        'keyword' => 'Arama kelimesi',
        'type' => 'İçerik türü',
        'sort_by' => 'Sıralama kriteri',
        'sort_direction' => 'Sıralama yönü',
        'per_page' => 'Sayfa başına kayıt',
        'page' => 'Sayfa numarası',
    ],
    'custom' => [
        'keyword' => [
            'min' => 'Arama kelimesi en az :min karakter olmalıdır',
            'max' => 'Arama kelimesi en fazla :max karakter olabilir',
        ],
        'type' => [
            'in' => 'İçerik türü video veya article olmalıdır',
        ],
        'sort_by' => [
            'in' => 'Sıralama kriteri geçersiz',
        ],
        'sort_direction' => [
            'in' => 'Sıralama yönü asc veya desc olmalıdır',
        ],
        'per_page' => [
            'min' => 'Sayfa başına minimum :min öğe gösterilebilir',
            'max' => 'Sayfa başına maximum :max öğe gösterilebilir',
        ],
        'page' => [
            'min' => 'Sayfa numarası en az :min olmalıdır',
        ],
        'email' => [
            'required' => 'E-posta adresi zorunludur',
            'email' => 'Geçerli bir e-posta adresi giriniz',
        ],
        'password' => [
            'required' => 'Parola zorunludur',
            'min' => 'Parola en az :min karakter olmalıdır',
            'max' => 'Parola en fazla :max karakter olabilir',
        ],
    ],
];
