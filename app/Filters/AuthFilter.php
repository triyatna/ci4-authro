<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

new \CodeIgniter\HTTP\URI();

class AuthFilter implements FilterInterface
{
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
        $user = new \App\Models\Users();
        // guest block to access dashboard
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth');
        }
        $session = $user->where('id', session()->get('id'))->first();
        // check this page is admin or user, if role is admin and user try to access user page redirect to admin page
        if ($session['role'] == 'admin') {
            if (service('uri')->getSegment(1) == 'user') {
                return redirect()->to('/admin');
            }
        } else {
            if (service('uri')->getSegment(1) == 'admin') {
                return redirect()->to('/user');
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
