@php
use App\Enums\EquipementEtat;
use App\Helpers\EquipementEtatHelper;
@endphp

<label for="{{ $name ?? 'etat' }}" class="form-label required-label">État</label>
<select 
    class="form-select @error($name ?? 'etat') is-invalid @enderror" 
    id="{{ $name ?? 'etat' }}" 
    name="{{ $name ?? 'etat' }}"
    {{ $attributes }}
>
    <option value="">-- Sélectionner un état --</option>
    @foreach(EquipementEtatHelper::options() as $value => $label)
        <option 
            value="{{ $value }}" 
            {{ (isset($selected) && $selected === $value) || (isset($model) && $model->etat === $value) ? 'selected' : '' }}
        >
            {{ $label }}
        </option>
    @endforeach
</select>
@error($name ?? 'etat')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
