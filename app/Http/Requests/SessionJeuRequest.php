<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionJeuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('player');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ville_id' => 'required|exists:villes,id',
            'mode' => 'required|in:cooperatif,mercenaire',
            'difficulte' => 'nullable|integer|min:1|max:3',
            'moyen_locomotion' => 'nullable|string',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'ville_id.required' => 'Veuillez choisir une ville pour votre aventure.',
            'mode.required' => 'Le mode de jeu est obligatoire.',
            'mode.in' => 'Le mode de jeu sélectionné est invalide.',
        ];
    }
}
