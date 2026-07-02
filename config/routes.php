<?php

$routes = array(
    '/' => 'home#index',
    '/test' => 'test#index',

    '/task/create' => 'task#create',
    '/task/edit' => 'task#edit',
    '/task/play' => 'task#play',
    '/task/finish' => 'task#finish',
    '/task/delete' => 'task#delete'
);