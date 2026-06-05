<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('status', 'active')],
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('status', 'active')],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'gte:purchase_price'],
            'critical_stock' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
        ];
    }
}
