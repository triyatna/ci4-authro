<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Settings;
use App\Models\Users;

class Admin extends BaseController
{

    private $users;

    private $settings;
    public function __construct()
    {
        $this->users = new Users();
        $this->settings = new Settings();

        $timeZone = $this->settings->where('config', 'timezone')->first();
        date_default_timezone_set($timeZone['var']);
        helper('function_helper');
    }
    public function index()
    {
        $user = $this->users->where('id', session()->get('id'))->first();
        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'settings' => $this->settings->findAll()
        ];
        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('admin/dashboard', $data);
        echo view('templates/footer', $data);
    }
}
