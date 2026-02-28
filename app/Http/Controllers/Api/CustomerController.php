<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index()
    {
        $customers = $this->customerService->getAllCustomers();
        return $this->successResponse($customers, 'تم جلب العملاء بنجاح');
    }

    public function store(CustomerRequest $request)
    {
        $customer = $this->customerService->createCustomer($request->validated());
        return $this->successResponse($customer, 'تم إضافة العميل بنجاح', 201);
    }

    public function show(Customer $customer)
    {
        return $this->successResponse($customer, 'تفاصيل العميل');
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $updatedCustomer = $this->customerService->updateCustomer($customer, $request->validated());
        return $this->successResponse($updatedCustomer, 'تم تحديث بيانات العميل بنجاح');
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->deleteCustomer($customer);
        return $this->successResponse(null, 'تم حذف العميل بنجاح');
    }
}
