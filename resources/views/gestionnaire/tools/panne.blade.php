<!-- resources/views/equipements/panne.blade.php -->

@extends('gestionnaire.tools.layouts.gestionlay')

@section('content')
    <h2>Liste des équipements en panne</h2>
    
    <table class="table smart-data-table" data-table-title="les pannes">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Marque</th>
            </tr>
        </thead>
        <tbody>
            @forelse($equipements as $equipement)
                <tr>
                    <td>{{ $equipement->nom }}</td>
                    <td>{{ $equipement->description }}</td>
                    <td>{{ $equipement->marque }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Aucun équipement en panne</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
