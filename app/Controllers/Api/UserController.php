<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Users;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        $db = new Users();
        $users = $db->findAll();
        return $this->respond($users, 200, 'OK');
    }

    public function create()
    {
        $db = new Users();
        // Validate
        if (!$this->validate([
            'name' => 'required',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]'
        ])) {
            return $this->fail($this->validator->getErrors());
        }
        $data = [
            'name' => $this->request->getVar('name'),
            'email' => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT)
        ];
        $db->insert($data);
        $response = [
            'status' => 201,
            'error' => null,
            'messages' => [
                'success' => 'Data Saved'
            ]
        ];
        return $this->respondCreated($response, 201);
    }

    public function show($id = null)
    {
        $db = new Users();
        $data = $db->where('id', $id)->first();
        if ($data) {
            return $this->respond($data, 200, 'OK');
        } else {
            return $this->failNotFound('No Data Found with id ' . $id);
        }
    }

    public function update($id = null)
    {
        $db = new Users();
        $exist = $db->where('id', $id)->first();
        if (!$exist) {
            return $this->failNotFound('No Data Found with id ' . $id);
        }
        // validate update
        if (!$this->validate([
            'name' => 'required',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'password' => 'required|min_length[6]'
        ])) {
            return $this->fail($this->validator->getErrors());
        }
        $input = $this->request->getRawInput();
        // validate data username, email exist or not
        $data = [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ];
        $db->update($id, $data);
        $response = [
            'status' => 200,
            'error' => null,
            'messages' => [
                'success' => 'Data Updated'
            ]
        ];
        return $this->respond($response, 200);
    }

    public function delete($id = null)
    {
        $db = new Users();
        $data = $db->where('id', $id)->first();
        if ($data) {
            $db->delete($id);
            $response = [
                'status' => 200,
                'error' => null,
                'messages' => [
                    'success' => 'Data Deleted'
                ]
            ];
            return $this->respondDeleted($response, 200);
        } else {
            return $this->failNotFound('No Data Found with id ' . $id);
        }
    }
}
