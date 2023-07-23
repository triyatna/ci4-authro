<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function __construct()
    {
        helper('function_helper');
    }

    public function index()
    {
        return view('welcome_message');
    }
}
