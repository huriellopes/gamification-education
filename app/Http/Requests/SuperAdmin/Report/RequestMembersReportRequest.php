<?php

declare(strict_types=1);

namespace App\Http\Requests\SuperAdmin\Report;

use Illuminate\Foundation\Http\FormRequest;

class RequestMembersReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
