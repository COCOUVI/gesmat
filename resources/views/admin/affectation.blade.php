@extends('admin.layouts.adminlay')
@section('content')
    @php $hasEquipementDispo = false; @endphp
    @php $optionsHtml = ''; @endphp

    {{-- Construire options groupées pour équipements disponibles --}}
    @foreach ($equipements_groupes as $categorie)
        @if ($categorie->equipement->count() > 0)
            @php $hasEquipementDispo = true; @endphp
            @php
                $optionsHtml .= '<optgroup label="' . e($categorie->nom) . '">';
                foreach ($categorie->equipement as $equipement) {
                    $optionsHtml .= '<option value="' . e($equipement->id) . '">' . e($equipement->nom) . '</option>';
                }
                $optionsHtml .= '</optgroup>';
            @endphp
        @endif
    @endforeach

    @if (!$hasEquipementDispo)
        @php $optionsHtml .= '<option value="">Aucun équipement disponible</option>'; @endphp
    @endif

    <div class="container mt-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4>Affectation d'Équipements</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('handle.affectation') }}" method="POST">
                    @csrf

                    {{-- Sélection employé --}}
                    <div class="mb-3">
                        <label for="employe_id" class="form-label">Employé</label>
                        <select name="employe_id" id="employe_id" class="form-select" required>
                            <option value="">-- Sélectionnez un employé --</option>
                            @foreach ($employes as $employe)
                                <option value="{{ $employe->id }}">{{ $employe->nom }} {{ $employe->prenom }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Motif --}}
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif</label>
                        <textarea name="motif" id="motif" class="form-control" rows="3" required></textarea>
                    </div>

                    <hr>

                    {{-- Liste dynamique équipements + date de retour --}}
                    <div id="equipement-wrapper">
                        <div class="equipement-item row mb-3">
                            <div class="col-md-6">
                                <label>Équipement</label>
                                <select name="equipements[]" class="form-select" required>
                                    <option value="">-- Sélectionner un équipement --</option>
                                    {!! $optionsHtml !!}
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Date de retour</label>
                                <input type="date" name="dates_retour[]" class="form-control" >
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-btn">Supprimer</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary mb-3" id="add-equipement">
                        <i class="mdi mdi-plus"></i> Ajouter un équipement
                    </button>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Affecter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const optionsEquipement = `{!! $optionsHtml !!}`;
            const addButton = document.getElementById('add-equipement');
            const wrapper = document.getElementById('equipement-wrapper');

            function addEquipementField() {
                const newField = document.createElement('div');
                newField.classList.add('equipement-item', 'row', 'mb-3');
                newField.innerHTML = `
                <div class="col-md-6">
                    <label>Équipement</label>
                    <select name="equipements[]" class="form-select" required>
                        <option value="">-- Sélectionner un équipement --</option>
                        ${optionsEquipement}
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Date de retour</label>
                    <input type="date" name="dates_retour[]" class="form-control" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-btn">Supprimer</button>
                </div>
            `;
                wrapper.appendChild(newField);
            }

            // Désactiver bouton si pas d'équipement dispo
            if (!optionsEquipement.includes('<option value=')) {
                addButton.disabled = true;
                addButton.classList.add('disabled');
                addButton.title = 'Aucun équipement disponible à ajouter';
            }

            addButton.addEventListener('click', addEquipementField);

            wrapper.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-btn')) {
                    e.target.closest('.equipement-item').remove();
                }
            });
        });
    </script>
@endpush
@push('scripts')
    @if(session('pdf'))
        <script>
            window.onload = function () {
                const link = document.createElement('a');
                link.href = "{{ session('pdf') }}";
                link.download = "";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
    @endif
@endpush
