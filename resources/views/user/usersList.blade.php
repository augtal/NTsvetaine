@extends('layouts.app')

@section('content')
<div class="container">
    Users List
    @if($users->count() > 0)
        <div>
            <table style="width:100%">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Verified at</th>
                    <th>Created at</th>
                    <th>Role</th>
                    <th>Make admin</th>
                </tr>
                @foreach ($users as $item)
                <tr>
                    <td>{{ $item['id'] }}</td>
                    <td>{{ $item['user_name'] }}</td>
                    <td>{{ $item['email'] }}</td>
                    <td>{{ $item['email_verified_at'] }}</td>
                    <td>{{ $item['created_at'] }}</td>
                    <td>@if ($item['role'] == 1)
                            <h5>User</h5>
                        @else
                            <h5>Admin</h5>
                        @endif
                    </td>
                    <td><a href='/changeRole/{{$item['id']}}'>Change role</a></td>
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
