@extends('layouts.app')

@section('content')
    <h5>Companies</h5>
    <table class="table">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Code</th>
            <th scope="col">Name</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $company)
            <tr>
                <td>{{$company->id}}</td>
                <td>{{$company->code}}</td>
                <td>{{$company->name}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
