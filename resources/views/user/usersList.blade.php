@extends('layouts.app')

@section('content')
<div class="container">
    @if($users->count() > 0)
        <div>
            <table style="width:100%">
                <tr>
                    <th>ID</th>
                    <th>Slapyvardis</th>
                    <th>El. paštas</th>
                    <th>El. paštas patvirtintas</th>
                    <th>Naudotojo sukūrimo data</th>
                    <th>Rolė</th>
                    <th>Pakeisti naudotojo role</th>
                </tr>
                @foreach ($users as $item)
                <tr>
                    <td>{{ $item['id'] }}</td>
                    <td>{{ $item['user_name'] }}</td>
                    <td>{{ $item['email'] }}</td>
                    <td>{{ $item['email_verified_at'] }}</td>
                    <td>{{ $item['created_at'] }}</td>
                    <td>@if ($item['role'] == 1)
                            <h5>Naudotojas</h5>
                        @else
                            <h5>Administratorius</h5>
                        @endif
                    </td>
                    <td>@if ($item['id'] != auth()->user()->id)
                            <a href='/changeRole/{{$item['id']}}' class="btn btn-success">Pakeisti</a>
                            <a href='#' class="btn btn-danger">Pašalinti</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
            <br>
        </div>
    @else
        <div>
            <h2>No users!</h2>
        </div>
    @endif
</div>
@endsection
