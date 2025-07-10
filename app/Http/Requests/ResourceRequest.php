<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Resource;

class ResourceRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
            'category' => 'nullable|string|in:' . implode(',', array_keys(Resource::getCategories())),
        ];

        if ($this->isMethod('post')) {
            $rules['file'] = 'required|file|max:102400'; // 100MB max
        } else {
            $rules['file'] = 'nullable|file|max:102400';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'リソース名を入力してください。',
            'name.string' => 'リソース名は文字列で入力してください。',
            'name.max' => 'リソース名は255文字以内で入力してください。',
            'file.required' => 'ファイルを選択してください。',
            'file.file' => '有効なファイルを選択してください。',
            'file.max' => 'ファイルサイズは100MB以内にしてください。',
            'description.string' => '説明は文字列で入力してください。',
            'is_public.boolean' => '公開設定は真偽値で入力してください。',
            'category.in' => '有効なカテゴリを選択してください。',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'リソース名',
            'file' => 'ファイル',
            'description' => '説明',
            'is_public' => '公開設定',
            'category' => 'カテゴリ',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // チェックボックスの処理（チェックされていない場合は false）
        if (!$this->has('is_public')) {
            $this->merge(['is_public' => false]);
        } else {
            $this->merge(['is_public' => $this->boolean('is_public')]);
        }
    }
}