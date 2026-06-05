<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('products.update') ?? false)
            && $this->route('product')->status === RecordStatus::Active;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('status', 'active')],
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('status', 'active')],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product)],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($product)],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'gte:purchase_price'],
            'critical_stock' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
        ];
    }
}
