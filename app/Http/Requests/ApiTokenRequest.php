<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ApiToken;

class ApiTokenRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:' . implode(',', array_keys(ApiToken::getPermissions())),
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'トークン名を入力してください。',
            'name.string' => 'トークン名は文字列で入力してください。',
            'name.max' => 'トークン名は255文字以内で入力してください。',
            'description.string' => '説明は文字列で入力してください。',
            'permissions.required' => '権限を選択してください。',
            'permissions.array' => '権限は配列形式で入力してください。',
            'permissions.min' => '少なくとも1つの権限を選択してください。',
            'permissions.*.in' => '有効な権限を選択してください。',
            'allowed_ips.array' => '許可IPアドレスは配列形式で入力してください。',
            'allowed_ips.*.ip' => '有効なIPアドレスを入力してください。',
            'expires_at.date' => '有効期限は日付形式で入力してください。',
            'expires_at.after' => '有効期限は現在時刻より後の日時を入力してください。',
            'is_active.boolean' => '有効化設定は真偽値で入力してください。',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'トークン名',
            'description' => '説明',
            'permissions' => '権限',
            'allowed_ips' => '許可IPアドレス',
            'expires_at' => '有効期限',
            'is_active' => '有効化設定',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_public')) {
            $this->merge([
                'is_public' => $this->boolean('is_public'),
            ]);
        }

        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->boolean('is_active'),
            ]);
        }

        // 空の配列要素を除去
        if ($this->has('permissions')) {
            $this->merge([
                'permissions' => array_filter($this->permissions ?? []),
            ]);
        }

        if ($this->has('allowed_ips')) {
            $this->merge([
                'allowed_ips' => array_filter($this->allowed_ips ?? []),
            ]);
        }
    }
}