<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\UserModel;
use App\Models\TransactionModel;
use CodeIgniter\HTTP\ResponseInterface;

class KasirApi extends BaseController
{
    public function login(): ResponseInterface
    {
        $data = $this->request->getJSON(true);
        $model = new UserModel();
        $user = $model->where('username', $data['username'])
                      ->where('password', md5($data['password'])) // sebaiknya gunakan hashing aman!
                      ->first();

        return $this->response->setJSON(['success' => $user !== null]);
    }

    public function products(): ResponseInterface
    {   
        $model = new ProductModel();
        $products = $model->findAll();

        return $this->response->setJSON($products);
    }

    public function checkout(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        $model = new TransactionModel();
        $model->save([
            'cart'        => json_encode($data['cart']),
            'member_phone'=> $data['memberPhone'],
            'total'       => array_reduce($data['cart'], fn($sum, $item) => $sum + $item['price'] * $item['qty'], 0)
        ]);

        return $this->response->setJSON(['success' => true]);
    }
}
