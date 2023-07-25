<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Users;


class GuestFilter implements FilterInterface
{
    public function __construct()
    {
        helper('function_helper');
    }
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = new Users();
        // if session or cookie is exist redirect to home with role
        if (session()->get('isLoggedIn')) {
            $userSession = $user->where('id', session()->get('id'))->first();
            if ($userSession['role'] == 'admin') {
                return redirect()->to('/admin');
            } else {
                return redirect()->to('/user');
            }
        } else if (isset($_COOKIE['remember_me'])) {

            $valueDecrypt = decrypt($_COOKIE['remember_me'], env('encryption.key'));
            $value = explode('.', $valueDecrypt);
            $id = $value[1];

            $userDetail = $user->where('id', $id)->first();
            if ($userDetail) {
                $data = [
                    'id' => $userDetail['id'],
                    'name' => $userDetail['name'],
                    'username' => $userDetail['username'],
                    'email' => $userDetail['email'],
                    'isLoggedIn' => true
                ];
                session()->set($data);
                if ($userDetail['role'] == 'admin') {
                    return redirect()->to('/admin');
                } else {
                    return redirect()->to('/user');
                }
            }
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
