<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'required', 'exists:customers,id,team_id,' . \Lib\Tenancy\Tenant::current()->id()],
            'status' => ['sometimes', 'required', 'string', 'in:draft,pending,paid,overdue,cancelled'],
            'due_date' => ['sometimes', 'required', 'date', 'after_or_equal:issue_date'],
            'metadata' => ['nullable', 'array'],
            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:invoice_items,id'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.subtitle' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
        ];
    }
}
