<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Users;
use App\Models\Settings;


class AuthController extends BaseController
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
    public function register()
    {

        // email smtp setting
        $smtp = $this->settings->where('config', 'smtp')->first();
        $dbArraysmtp = json_decode($smtp['var'], true);
        $smtp['var'] = $dbArraysmtp;
        // get title and email from settings database
        $title = $this->settings->where('config', 'title')->first();
        $config = [
            'protocol' => 'smtp',
            'SMTPHost' => $smtp['var']['host'],
            'SMTPUser' => $smtp['var']['user'],
            'SMTPPass' => $smtp['var']['pass'],
            'SMTPPort' => $smtp['var']['port'],
            'SMTPCrypto' => $smtp['var']['security'],
            'mailType' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];


        if ($this->request->getPost()) {
            $rules = [
                'name' => [
                    'rules' => 'required|min_length[3]|max_length[100]',
                    'errors' => [
                        'required' => 'Name is required',
                        'min_length' => 'Name minimum 3 characters',
                        'max_length' => 'Name maximum 100 characters'
                    ]
                ],
                //username not allowed special character
                'username' => [
                    'rules' => 'required|min_length[3]|max_length[30]|is_unique[users.username]|alpha_numeric',
                    'errors' => [
                        'required' => 'Username is required',
                        'min_length' => 'Username minimum 6 characters',
                        'max_length' => 'Username maximum 30 characters',
                        'is_unique' => 'Username already exists',
                        'alpha_numeric' => 'Username not allowed special character'
                    ]
                ],
                'email' => [
                    'rules' => 'required|valid_email|is_unique[users.email]',
                    'errors' => [
                        'required' => 'Email is required',
                        'valid_email' => 'Email is not valid',
                        'is_unique' => 'Email already exists'
                    ]
                ],
                'password' => [
                    'rules' => 'required|min_length[6]|max_length[50]',
                    'errors' => [
                        'required' => 'Password is required',
                        'min_length' => 'Password minimum 6 characters',
                        'max_length' => 'Password maximum 50 characters'
                    ]
                ],
                'confirm_password' => [
                    'rules' => 'required|matches[password]',
                    'errors' => [
                        'required' => 'Confirm password is required',
                        'matches' => 'Confirm password not match'
                    ]
                ],
                'terms' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Terms and conditions is required'
                    ]
                ],
            ];


            if (!$this->validate($rules)) {
                session()->setFlashdata('error', $this->validator->listErrors());
                return redirect()->back()->withInput();
            } else {
                $userActive = $this->settings->where('config', 'user_activate')->first();
                if ($userActive['var'] == 0) {
                    $data = [
                        'name' => htmlentities(ucwords($this->request->getVar('name'))),
                        'username' => htmlentities(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getVar('username')))),
                        'email' => htmlentities($this->request->getVar('email')),
                        'password' => password_hash(htmlentities($this->request->getVar('password')), PASSWORD_BCRYPT),
                        'api_token' => generateRandomString(30),
                    ];
                    $this->users->insert($data);
                    session()->setFlashdata('success', 'Register successful!');
                    return redirect()->to('/auth/login');
                } else {
                    $data = [
                        'name' => htmlentities(ucwords($this->request->getVar('name'))),
                        'username' => htmlentities(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getVar('username')))),
                        'email' => htmlentities($this->request->getVar('email')),
                        'password' => password_hash(htmlentities($this->request->getVar('password')), PASSWORD_BCRYPT),
                        'active' => 'false',
                        'api_token' => generateRandomString(30),
                        'remember_token' => generateRandomString(50),
                    ];
                    $expires = strtotime("+5 hours");
                    // pathactivation encrypt('verifemail'.username.email)
                    $pathActivation = siencrypt('verifemail..' . $data['username'] . '..' . $data['email'] . '..' . $expires, env('encryption.key'));

                    // send email activation with smtp settings
                    $email = \Config\Services::email();
                    $email->initialize($config);
                    $email->setFrom($smtp['var']['user'], $title['var']);
                    $email->setTo($data['email']);
                    $email->setSubject('Please verify your email');
                    $email->setMessage('Activation link: <a href="' . base_url('/auth/activate/' . $data['remember_token'] . '?expires=' . $expires . '&signature=' . $pathActivation) . '">Click here</a>');
                    $email->send();

                    $this->users->insert($data);
                    session()->setFlashdata('success', 'Register successful! Please check your email for activation account. ');
                    return redirect()->to('/auth/login');
                }
            }
        }

        $data = [];
        $data['title'] = 'Register';
        echo view('auth/headerTemplate', $data);
        echo view('auth/register', $data);
        echo view('auth/footerTemplate');
    }

    public function login()
    {

        //login process validate with set session and if remember me is checked set cookie for 15 days
        if ($this->request->getPost()) {
            $rules = [
                'email-username' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Email or username is required'
                    ]
                ],
                'password' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Password is required'
                    ]
                ],
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('error', $this->validator->listErrors());
                return redirect()->back()->withInput();
            } else {
                $emailUsername = htmlentities($this->request->getVar('email-username'));
                $password = htmlentities($this->request->getVar('password'));
                $userDetail = $this->users->where('email', $emailUsername)->orWhere('username', $emailUsername)->first();
                if ($userDetail) {
                    if (password_verify($password, $userDetail['password'])) {
                        $data = [
                            'id' => $userDetail['id'],
                            'name' => $userDetail['name'],
                            'username' => $userDetail['username'],
                            'email' => $userDetail['email'],
                            'isLoggedIn' => true
                        ];
                        session()->set($data);
                        if ($this->request->getVar('remember-me')) {
                            // set cookie remember_me value combination (username.id) in encrypt function
                            $value = encrypt($userDetail['username'] . '.' . $userDetail['id'], env('encryption.key'));
                            setcookie('remember_me', $value, time() + (86400 * 15), "/");
                        }
                        session()->setFlashdata('success', 'Login successful! Redirecting to home page in 3 seconds...');
                        header("Refresh:3; url=" . base_url('/'));
                    } else {
                        session()->setFlashdata('error', 'Password is wrong');
                        return redirect()->back()->withInput();
                    }
                } else {
                    session()->setFlashdata('error', 'Email or username is wrong');
                    return redirect()->back()->withInput();
                }
            }
        }

        $data = [];
        $data['title'] = 'Login';
        echo view('auth/headerTemplate', $data);
        echo view('auth/login');
        echo view('auth/footerTemplate');
    }


    // Useractivate process with decrypt url and update database
    public function userActivate($token)
    {
        $expired = $this->request->getGet('expires');
        $signature = $this->request->getGet('signature');
        //signature sidecrypt
        $signatureDecrypt = sidecrypt($signature, env('encryption.key'));
        if ($signatureDecrypt == null || empty($signatureDecrypt) || !strpos($signatureDecrypt, '..' || count(explode('..', $signatureDecrypt)) != 4)) {
            //throw exception if signature is null or empty show message Invalid signature 
            return view('errors/html/error_403', ['message' => 'Invalid signature']);
        }
        $signature = explode('..', $signatureDecrypt);
        $username = $signature[1];
        $mailto = $signature[2];
        $expires = $signature[3];

        if ($expires < time()) {
            if ($expires != $expired) return view('errors/html/error_403', ['message' => 'Invalid signature']);
            return view('errors/html/error_403', ['message' => 'Expired']);
        }

        $userDetail = $this->users->where('username', $username)->where('email', $mailto)->first();
        // check token and user detail
        if ($userDetail['remember_token'] != $token) {
            return view('errors/html/error_403', ['message' => 'Invalid token']);
        }
        if ($userDetail) {
            if ($userDetail['active'] == 'true') {
                session()->setFlashdata('error', 'Account already activated!');
                return redirect()->to('/auth/login');
            }
            $data = [
                'active' => 'true',
                'email_verified_at' => date('Y-m-d H:i:s'),
            ];
            $this->users->update($userDetail['id'], $data);
            $smtp = $this->settings->where('config', 'smtp')->first();
            $dbArraysmtp = json_decode($smtp['var'], true);
            $smtp['var'] = $dbArraysmtp;
            // get title and email from settings database
            $title = $this->settings->where('config', 'title')->first();
            $config = [
                'protocol' => 'smtp',
                'SMTPHost' => $smtp['var']['host'],
                'SMTPUser' => $smtp['var']['user'],
                'SMTPPass' => $smtp['var']['pass'],
                'SMTPPort' => $smtp['var']['port'],
                'SMTPCrypto' => $smtp['var']['security'],
                'mailType' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n"
            ];
            $email = \Config\Services::email();
            $email->initialize($config);
            $email->setFrom($smtp['var']['user'], $title['var']);
            $email->setTo($mailto);
            $email->setSubject('Your email has been verified');
            $email->setMessage("Hello $username! Your account has been successfully activated at " . $title['var']);
            $email->send();

            session()->setFlashdata('success', 'Account activation successful!');
            header("Refresh:5; url=" . base_url('/auth/login'));

            $data = [];
            $data['title'] = 'Verification Email Account';
            echo view('auth/headerTemplate', $data);
            echo view('auth/emailverify');
            echo view('auth/footerTemplate');
        } else {
            session()->setFlashdata('error', 'Account not registered!');
            header("Refresh:0; url=" . base_url('/auth/login'));
        }
    }

    public function forgotPassword()
    {
        // email smtp setting
        $smtp = $this->settings->where('config', 'smtp')->first();
        $dbArraysmtp = json_decode($smtp['var'], true);
        $smtp['var'] = $dbArraysmtp;
        // get title and email from settings database
        $title = $this->settings->where('config', 'title')->first();
        $config = [
            'protocol' => 'smtp',
            'SMTPHost' => $smtp['var']['host'],
            'SMTPUser' => $smtp['var']['user'],
            'SMTPPass' => $smtp['var']['pass'],
            'SMTPPort' => $smtp['var']['port'],
            'SMTPCrypto' => $smtp['var']['security'],
            'mailType' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];


        if ($this->request->getPost()) {
            $rules = [
                'email' => [
                    'rules' => 'required|valid_email',
                    'errors' => [
                        'required' => 'Email is required',
                        'valid_email' => 'Email is not valid',
                    ]
                ],
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('error', $this->validator->listErrors());
                return redirect()->back()->withInput();
            } else {
                $email = htmlentities($this->request->getVar('email'));
                $userDetail = $this->users->where('email', $email)->first();
                if ($userDetail) {
                    $expires = strtotime("+1 hours");
                    // pathactivation encrypt('verifemail'.username.email)
                    $pathActivation = siencrypt('resetpassword..' . $userDetail['username'] . '..' . $userDetail['email'] . '..' . $expires, env('encryption.key'));

                    // send email activation with smtp settings
                    $email = \Config\Services::email();
                    $email->initialize($config);
                    $email->setFrom($smtp['var']['user'], $title['var']);
                    $email->setTo($userDetail['email']);
                    $email->setSubject('Reset Password');
                    $email->setMessage('Reset password link: <a href="' . base_url('/auth/reset-password/' . $userDetail['remember_token'] . '?expires=' . $expires . '&signature=' . $pathActivation) . '">Click here</a>');
                    $email->send();

                    session()->setFlashdata('success', 'Reset password link has been sent to your email!');
                    return redirect()->to('/auth/login');
                } else {
                    session()->setFlashdata('error', 'Email not registered!');
                    return redirect()->back()->withInput();
                }
            }
        }

        $data = [
            'title' => 'Forgot Password',
        ];
        echo view('auth/headerTemplate', $data);
        echo view('auth/forgotpassword');
        echo view('auth/footerTemplate');
    }
    // logout process with destroy session and cookie
    public function logout()
    {
        session()->destroy();
        if (isset($_COOKIE['remember_me'])) {
            unset($_COOKIE['remember_me']);
            setcookie('remember_me', null, -1, '/');
        }
        return redirect()->to('/auth/login');
    }
}
