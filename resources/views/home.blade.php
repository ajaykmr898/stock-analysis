@extends('layouts.app')

@section('content')
    <h5>Companies</h5>
    <button class="btn btn-primary all-new float-right" type="submit">Import New</button>
    <table class="table" style="margin-top: 50px!important;">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Code</th>
            <th scope="col">Name</th>
            <th scope="col">Last Import Date</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $company)
            <tr>
                <td>{{$company->id}}</td>
                <td>{{$company->code}}</td>
                <td>{{$company->name}}</td>
                <td>{{$company->date}}</td>
                <td>
                    <button class="btn btn-primary single-new" data-id="{{$company->id}}" data-code="{{$company->code}}" data-timestamp="{{$company->timestamp}}" type="submit">Import New</button>
                    <button class="btn btn-primary single-old" data-id="{{$company->id}}" data-code="{{$company->code}}" data-timestamp="{{$company->timestamp}}" type="submit">Re-import All</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
