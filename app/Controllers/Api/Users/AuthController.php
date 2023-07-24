<?php

namespace App\Controllers\Api\Users;

use App\Controllers\BaseController;
use App\Models\Users;
use App\Libraries\JWTCI;

class AuthController extends BaseController
{
    // login
    public function login()
    {
        // validate login
        if (!$this->validate([
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ])) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        $db = new Users();


        $user = $db->where('email', $email)->first();
        if ($user) {
            //password verify
            if (password_verify($password, $user['password'])) {
                $jwt = new JWTCI();
                $token = $jwt->token();
                return $this->response->setJSON([
                    'status' => 200,
                    'error' => null,
                    'messages' => [
                        'success' => 'OK!',
                        'token' => $token
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 401,
                    'error' => 'Unauthorized',
                    'messages' => [
                        'error' => 'Password is wrong'
                    ]
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized',
                'messages' => [
                    'error' => 'Email is wrong'
                ]
            ]);
        }
    }
}
