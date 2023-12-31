<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Users;
use App\Models\Settings;


class AuthController extends BaseController
{

    private $users;

    private $settings;

    private $config;
    public function __construct()
    {
        $this->users = new Users();
        $this->settings = new Settings();
        $smtp = $this->settings->where('config', 'smtp')->first();
        $dbArraysmtp = json_decode($smtp['var'], true);
        $smtp['var'] = $dbArraysmtp;
        //config mail
        $this->config = [
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

        $timeZone = $this->settings->where('config', 'timezone')->first();
        date_default_timezone_set($timeZone['var']);
        helper('function_helper');
    }
    public function index()
    {
        return redirect()->to('/auth/login');
    }
    public function register()
    {
        $title = $this->settings->where('config', 'title')->first();
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
                    $email->initialize($this->config);
                    $email->setFrom($this->config['SMTPUser'], $title['var']);
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
                        // check if user not active 
                        if ($userDetail['active'] == 'false') {
                            session()->setFlashdata('error', 'Your account is not active. Please check your activation email or contact our administrator');
                            // return redirect to login page
                            return redirect()->back()->withInput();
                        }
                        $data = [
                            'id' => $userDetail['id'],
                            'name' => $userDetail['name'],
                            'username' => $userDetail['username'],
                            'email' => $userDetail['email'],
                            'isLoggedIn' => true
                        ];
                        session()->set($data);
                        if ($this->request->getVar('remember-me')) {
                            // set cookie remember_me value combination (username.id) in signature encrypt
                            $value = siencrypt('cookie.remember_me.' . $userDetail['username'] . '.' . $userDetail['id'], env('encryption.key'));
                            setcookie('remember_me', $value, time() + (86400 * 15), "/");
                        }
                        session()->setFlashdata('success', 'Login successful! Redirecting to home page in 3 seconds...');
                        // redirect with role admin or user
                        if ($userDetail['role'] == 'admin') {
                            header("Refresh:3; url=" . base_url('/admin'));
                        } else {
                            header("Refresh:3; url=" . base_url('/user'));
                        }
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
                'remember' => null,
            ];
            $this->users->update($userDetail['id'], $data);
            $title = $this->settings->where('config', 'title')->first();
            $email = \Config\Services::email();
            $email->initialize($this->config);
            $email->setFrom($this->config['SMTPUser'], $title['var']);
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

        if ($this->request->getPost()) {
            $resetPassword = new \App\Models\PasswordResetToken();

            $title = $this->settings->where('config', 'title')->first();
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
                //check email exist token or not, if ready flashdata token already sent, check your email or wait 1 hour for resend token. And if exist token and expired more than 1 hour delete token and insert new token
                $checkToken = $resetPassword->where('email', $email)->first();
                if ($checkToken) {
                    if ($checkToken['expires_at'] > time()) {
                        session()->setFlashdata('error', 'Token already sent, check your email or wait 1 hour for resend token');
                        return redirect()->back()->withInput();
                    } else {
                        $resetPassword->where('email', $email)->delete();
                    }
                }

                if ($userDetail) {
                    $expires = strtotime("+1 hours");
                    $generateToken = generateRandomString(50);
                    // insert token and expires to database password_reset_tokens
                    $data = [
                        'email' => $email,
                        'token' => $generateToken,
                        'expires_at' => $expires,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];

                    $resetPassword->insert($data);

                    // send email activation with smtp settings
                    $email = \Config\Services::email();
                    $email->initialize($this->config);
                    $email->setFrom($this->config['SMTPUser'], $title['var']);
                    $email->setTo($userDetail['email']);
                    $email->setSubject('Reset Password');
                    $email->setMessage('Reset password link: <a href="' . base_url('/auth/reset-password/' . $data['token']) . '">Click here</a>');
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

    public function resetPassword($token)
    {

        $resetPassword = new \App\Models\PasswordResetToken();
        $title = $this->settings->where('config', 'title')->first();
        // get email from database password_reset_tokens
        $checkToken = $resetPassword->where('token', $token)->first();
        if ($checkToken) {
            if ($checkToken['expires_at'] < time()) {
                // if token expired delete token
                $resetPassword->where('token', $token)->delete();
                // return error 410 expired view
                return view('errors/html/error_410', ['message' => 'Expired']);
            }
            if ($this->request->getPost()) {
                $rules = [
                    'password' => [
                        'rules' => 'required|min_length[6]|max_length[100]',
                        'errors' => [
                            'required' => 'Password is required',
                            'min_length' => 'Password minimum 6 characters',
                            'max_length' => 'Password maximum 100 characters'
                        ]
                    ],
                    'confirm_password' => [
                        'rules' => 'required|matches[password]',
                        'errors' => [
                            'required' => 'Confirm password is required',
                            'matches' => 'Confirm password not match'
                        ]
                    ],
                ];
                if (!$this->validate($rules)) {
                    session()->setFlashdata('error', $this->validator->listErrors());
                    return redirect()->back()->withInput();
                } else {
                    $password = htmlentities($this->request->getVar('password'));
                    $data = [
                        'password' => password_hash($password, PASSWORD_BCRYPT),
                    ];
                    $this->users->where('email', $checkToken['email'])->set($data)->update();
                    $resetPassword->where('token', $token)->delete();

                    // send email activation with smtp settings
                    $email = \Config\Services::email();
                    $email->initialize($this->config);
                    $email->setFrom($this->config['SMTPUser'], $title['var']);
                    $email->setTo($checkToken['email']);
                    $email->setSubject('Password has been reset');
                    $email->setMessage('Your password has been reset at ' . $title['var'] . '');
                    $email->send();

                    session()->setFlashdata('success', 'Reset password successful!');
                    return redirect()->to('/auth/login');
                }
            }
            $data = [
                'title' => 'Reset Password',
                'page' => 'reset',
                'email' => $checkToken['email'],
            ];
            echo view('auth/headerTemplate', $data);
            echo view('auth/forgotpassword');
            echo view('auth/footerTemplate');
        } else {
            // not found 404 throw exception
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
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
