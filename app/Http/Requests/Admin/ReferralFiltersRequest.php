<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\ReferralFilters;

class ReferralFiltersRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'source'       => ['nullable','string','max:100'],
            'utm_source'   => ['nullable','string','max:100'],
            'q'            => ['nullable','string','max:200'],
            'from'         => ['nullable','date'],
            'to'           => ['nullable','date'],
            'exclude_bots' => ['nullable','boolean'],
            'per_page'     => ['nullable','integer','min:10','max:200'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'exclude_bots' => filter_var($this->input('exclude_bots', false), FILTER_VALIDATE_BOOLEAN),
            'per_page'     => $this->filled('per_page') ? (int) $this->input('per_page') : null,
        ]);
    }

    public function validatedFilters(): array
    {
        return $this->only(['source','utm_source','q','from','to','exclude_bots','per_page']);
    }


    public function toDto(?int $perPage = null): ReferralFilters
    {
        $data = $this->validatedFilters();

        return new ReferralFilters(
            source:       $data['source']       ?? null,
            utm_source:   $data['utm_source']   ?? null,
            q:            $data['q']            ?? null,
            from:         $data['from']         ?? null,
            to:           $data['to']           ?? null,
            exclude_bots: (bool)($data['exclude_bots'] ?? false),
            per_page:     $perPage ?? (int)($data['per_page'] ?? 50),
        );
    }
}
