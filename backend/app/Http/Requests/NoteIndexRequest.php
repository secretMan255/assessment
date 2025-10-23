<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoteIndexRequest extends FormRequest
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
            'page'     => ['nullable','integer','min:1'],
            'per_page' => ['nullable','integer','min:1','max:100'],
            'search'   => ['nullable','string'],
            'sort'     => ['nullable','in:id,title,created_at,updated_at'],
            'order'    => ['nullable','in:asc,desc'],
        ];
    }

    public function filters(): array
    {
        return [
            'page'      => ($p = (int) $this->input('page')) > 0 ? $p : null,
            'per_page'  => (int) $this->input('per_page', 10),
            'search'    => trim((string) $this->input('search', '')),
            'sort'      => (string) $this->input('sort', 'id'),
            'order'     => strtolower((string) $this->input('order', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];
    }
}
