{{-- resources/views/finance/categories/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Categories</h1>
    
    @if($categories->isEmpty())
        <p>No categories found.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Icon</th>
                    <th>Color</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->type }}</td>
                        <td>{{ $category->icon }}</td>
                        <td><span style="background-color: {{ $category->color }};">&nbsp;&nbsp;&nbsp;</span></td>
                        <td>{{ $category->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
