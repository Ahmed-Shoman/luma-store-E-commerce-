<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    public function getAllCustomers()
    {
        return Customer::latest()->get();
    }

    public function createCustomer(array $data)
    {
        // تشفير كلمة المرور
        $data['password'] = Hash::make($data['password']);

        return Customer::create($data);
    }

    public function updateCustomer(Customer $customer, array $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $customer->update($data);
        return $customer;
    }

    public function deleteCustomer(Customer $customer)
    {
        return $customer->delete();
    }
}